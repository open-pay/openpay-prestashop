<?php

/**
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2015 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class OpenpayPrestashop extends PaymentModule
{

    private $error = array();
    private $validation = array();
    private $limited_currencies = array('MXN');
    private $merchant_id;
    private $secret_key;
    private $is_sandbox;
    private $spei_base_url;
    private $stores_base_url;

    public function __construct() {
        if (!class_exists('Openpay', false)) {
            include_once(dirname(__FILE__).'/lib/Openpay.php');
        }

        $this->sandbox_url = 'https://sandbox-api.openpay.mx/v1';
        $this->url = 'https://api.openpay.mx/v1';

        $this->name = 'openpayprestashop';
        $this->tab = 'payments_gateways';
        $this->version = '2.3.0';
        $this->author = 'Openpay SAPI de CV';
        $this->module_key = '23c1a97b2718ec0aec28bb9b3b2fc6d5';

        parent::__construct();
        $warning = 'All the Openpay transaction details saved in your database will be deleted. Are you sure you want uninstall this module?';
        $this->displayName = $this->l('Openpay');
        $this->description = $this->l('Accept payments by credit-debit card, cash payments and via SPEI with Openpay');
        $this->confirmUninstall = $this->l($warning);
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->months_interest_free = array('3' => '3 meses', '6' => '6 meses', '9' => '9 meses', '12' => '12 meses', '18' => '18 meses');

        $this->is_sandbox = Configuration::get('OPENPAY_MODE') ? false : true;
        $this->spei_base_url = Configuration::get('OPENPAY_MODE') ? 'https://dashboard.openpay.mx/spei-pdf' : 'https://sandbox-dashboard.openpay.mx/spei-pdf';
        $this->stores_base_url = Configuration::get('OPENPAY_MODE') ? 'https://dashboard.openpay.mx/paynet-pdf' : 'https://sandbox-dashboard.openpay.mx/paynet-pdf';
        $this->secret_key = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $this->merchant_id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');
    }

    /**
     * Openpay's module installation
     *
     * @return boolean Install result
     */
    public function install() {
        /* For 1.4.3 and less compatibility */
        $update_config = array(
            'PS_OS_CHEQUE' => 1,
            'PS_OS_PAYMENT' => 2,
            'PS_OS_PREPARATION' => 3,
            'PS_OS_SHIPPING' => 4,
            'PS_OS_DELIVERED' => 5,
            'PS_OS_CANCELED' => 6,
            'PS_OS_REFUND' => 7,
            'PS_OS_ERROR' => 8,
            'PS_OS_OUTOFSTOCK' => 9,
            'PS_OS_BANKWIRE' => 10,
            'PS_OS_PAYPAL' => 11,
            'PS_OS_WS_PAYMENT' => 12);

        foreach ($update_config as $u => $v) {
            if (!Configuration::get($u) || (int) Configuration::get($u) < 1) {
                if (defined('_'.$u.'_') && (int) constant('_'.$u.'_') > 0) {
                    Configuration::updateValue($u, constant('_'.$u.'_'));
                } else {
                    Configuration::updateValue($u, $v);
                }
            }
        }

        $ret = parent::install() && $this->createPendingState() &&
                $this->registerHook('payment') &&
                $this->registerHook('header') &&
                $this->registerHook('paymentReturn') &&
                Configuration::updateValue('OPENPAY_MODE', 0) &&
                Configuration::updateValue('OPENPAY_CARDS', 1) &&
                Configuration::updateValue('OPENPAY_STORES', 1) &&
                Configuration::updateValue('OPENPAY_SPEI', 1) &&
                Configuration::updateValue('OPENPAY_WEBHOOK_ID_TEST', null) &&
                Configuration::updateValue('OPENPAY_WEBHOOK_ID_LIVE', null) &&
                Configuration::updateValue('OPENPAY_WEBHOOK_USER', Tools::substr(md5(uniqid(rand(), true)), 0, 10)) &&
                Configuration::updateValue('OPENPAY_WEBHOOK_PASSWORD', Tools::substr(md5(uniqid(rand(), true)), 0, 10)) &&
                Configuration::updateValue('OPENPAY_BACKGROUND_COLOR', '#003A5B') &&
                Configuration::updateValue('OPENPAY_FONT_COLOR', '#ffffff') &&
                Configuration::updateValue('OPENPAY_WEBHOOK_URL', _PS_BASE_URL_.__PS_BASE_URI__) &&
                Configuration::updateValue('OPENPAY_MONTHS_INTEREST_FREE', '') &&
                Configuration::updateValue('OPENPAY_MINIMUM_AMOUNT', '') &&
                $this->installDb();

        /* The hook "displayMobileHeader" has been introduced in v1.5.x - Called separately to fail silently if the hook does not exist */
        $this->registerHook('displayMobileHeader');

        return $ret;
    }

    private function createPendingState() {
        $state = new OrderState();
        $languages = Language::getLanguages();
        $names = array();

        foreach ($languages as $lang) {
            $names[$lang['id_lang']] = $this->l('Awaiting payment');
        }

        $state->name = $names;
        $state->color = '#4169E1';
        $state->send_email = true;
        $state->module_name = 'openpayprestashop';
        $templ = array();

        foreach ($languages as $lang) {
            $templ[$lang['id_lang']] = 'openpayprestashop';
        }

        $state->template = $templ;

        if ($state->save()) {
            try {
                Configuration::updateValue('waiting_cash_payment', $state->id);
                $this->copyMailTemplate();
            } catch (Exception $e) {
                if (class_exists('Logger')) {
                    Logger::addLog($e->getMessage(), 1, null, null, null, true);
                }
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * Openpay's module database tables installation
     *
     * @return boolean Database tables installation result
     */
    public function installDb() {

        $return = true;

        $return &= Db::getInstance()->Execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'openpay_customer` (
                `id_openpay_customer` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `openpay_customer_id` varchar(32) NULL,
                `id_customer` int(10) unsigned NOT NULL,
                `date_add` datetime NOT NULL,
                PRIMARY KEY (`id_openpay_customer`),
                KEY `id_customer` (`id_customer`)) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');

        $return &= Db::getInstance()->Execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'openpay_transaction` (
                `id_openpay_transaction` int(11) NOT NULL AUTO_INCREMENT,
                `type` enum(\'card\',\'store\',\'bank_account\') NOT NULL,
                `id_cart` int(10) unsigned NOT NULL,
                `id_order` int(10) unsigned NOT NULL,
                `id_transaction` varchar(32) NOT NULL,
                `amount` decimal(10,2) NOT NULL,
                `status` enum(\'paid\',\'unpaid\') NOT NULL,
                `currency` varchar(3) NOT NULL,
                `fee` decimal(10,2) NOT NULL,
                `mode` enum(\'live\',\'test\') NOT NULL,
                `date_add` datetime NOT NULL,
                `due_date` datetime NULL,
                `reference` varchar(50) NULL,
                `barcode` varchar(250) NULL,
                `clabe` varchar(50) NULL,
                PRIMARY KEY (`id_openpay_transaction`),
                KEY `idx_transaction` (`type`,`id_order`,`status`)) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');

        return $return;
    }

    /**
     * Openpay's module uninstallation (Configuration values, database tables...)
     *
     * @return boolean Uninstall result
     */
    public function uninstall() {
        /* Eliminar webhooks */
        if (Configuration::get('OPENPAY_WEBHOOK_ID_TEST') != null) {
            $this->deleteWebhook(Configuration::get('OPENPAY_WEBHOOK_ID_TEST'), false);
        }

        if (Configuration::get('OPENPAY_WEBHOOK_ID_LIVE') != null) {
            $this->deleteWebhook(Configuration::get('OPENPAY_WEBHOOK_ID_LIVE'), true);
        }

        return parent::uninstall() &&
                Configuration::deleteByName('OPENPAY_PUBLIC_KEY_TEST') &&
                Configuration::deleteByName('OPENPAY_PUBLIC_KEY_LIVE') &&
                Configuration::deleteByName('OPENPAY_MERCHANT_ID_LIVE') &&
                Configuration::deleteByName('OPENPAY_MERCHANT_ID_TEST') &&
                Configuration::deleteByName('OPENPAY_MODE') &&
                Configuration::deleteByName('OPENPAY_PRIVATE_KEY_TEST') &&
                Configuration::deleteByName('OPENPAY_PRIVATE_KEY_LIVE') &&
                Configuration::deleteByName('OPENPAY_DEADLINE_STORES') &&
                Configuration::deleteByName('OPENPAY_DEADLINE_SPEI') &&
                Configuration::deleteByName('OPENPAY_WEBHOOK_ID_TEST') &&
                Configuration::deleteByName('OPENPAY_WEBHOOK_ID_LIVE') &&
                Configuration::deleteByName('OPENPAY_WEBHOOK_USER') &&
                Configuration::deleteByName('OPENPAY_WEBHOOK_PASSWORD') &&
                Configuration::deleteByName('OPENPAY_CARDS') &&
                Configuration::deleteByName('OPENPAY_STORES') &&
                Configuration::deleteByName('OPENPAY_SPEI') &&
                Configuration::deleteByName('OPENPAY_BACKGROUND_COLOR') &&
                Configuration::deleteByName('OPENPAY_FONT_COLOR') &&
                Configuration::deleteByName('OPENPAY_WEBHOOOK_URL') &&
                Configuration::deleteByName('OPENPAY_MONTHS_INTEREST_FREE') &&
                Configuration::deleteByName('OPENPAY_MINIMUM_AMOUNT') &&
                Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'openpay_customer`') &&
                Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'openpay_transaction`');
    }

    public function hookDisplayMobileHeader() {
        return $this->hookHeader();
    }

    /**
     * Load Javascripts and CSS related to the Openpay's module
     * Only loaded during the checkout process
     *
     * @return string HTML/JS Content
     */
    public function hookHeader() {
        if (!$this->active) {
            return;
        }

        /* Continue only if we are in the checkout process */
        if (Tools::getValue('controller') != 'order-opc' && (!($_SERVER['PHP_SELF'] == __PS_BASE_URI__.'order.php' ||
                $_SERVER['PHP_SELF'] == __PS_BASE_URI__.'order-opc.php' || Tools::getValue('controller') == 'order' ||
                Tools::getValue('controller') == 'orderopc' || Tools::getValue('step') == 3))) {
            return;
        }

        /* Load CSS files through CCC */
        $this->context->controller->addCSS($this->_path.'views/css/openpay-prestashop.css');
    }

    /**
     * Display the Openpay's payment methods
     *
     * @return string Openpay's Smarty template content
     */
    public function hookPayment() {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency()) {
            return;
        }

        if (!empty($this->context->cookie->openpay_error)) {
            $this->smarty->assign('openpay_error', $this->context->cookie->openpay_error);
            $this->context->cookie->__set('openpay_error', null);
        }

        $this->smarty->assign('module_configured', $this->getMerchantInfo());
        $this->smarty->assign('amount', $this->context->cart->getOrderTotal());
        $this->smarty->assign('card', Configuration::get('OPENPAY_CARDS'));
        $this->smarty->assign('store', Configuration::get('OPENPAY_STORES'));
        $this->smarty->assign('spei', Configuration::get('OPENPAY_SPEI'));
        $this->smarty->assign('module_dir', $this->_path);
        $this->smarty->assign('openpay_ps_version', _PS_VERSION_);

        return $this->display(__FILE__, './views/templates/hook/payment.tpl');
    }

    /**
     * Display a confirmation message after an order has been placed
     *
     * @param array Hook parameters
     */
    public function hookPaymentReturn($params) {
        if (!isset($params['objOrder']) || ($params['objOrder']->module != $this->name)) {
            return false;
        }

        if ($params['objOrder'] && Validate::isLoadedObject($params['objOrder'])) {

            $id_order = (int) $params['objOrder']->id;
            $reference = isset($params['objOrder']->reference) ? $params['objOrder']->reference : '#'.sprintf('%06d', $id_order);

            $this->smarty->assign('openpay_order', array('reference' => $reference, 'valid' => $params['objOrder']->valid));
            $this->context->controller->addCSS($this->_path.'views/css/openpay-prestashop.css');
            $this->smarty->assign('order_pending', false);

            $query = 'SELECT * FROM '._DB_PREFIX_.'openpay_transaction WHERE id_order = '.(int) $id_order.'';
            $transaction = Db::getInstance()->getRow($query);
        }

        switch ($transaction['type']) {
            case 'store':                
                $data = array(                    
                    'pdf' => $this->stores_base_url.'/'.$this->merchant_id.'/'.$transaction['reference']
                );

                $this->smarty->assign('openpay_order', $data);
                $template = './views/templates/hook/store_order_confirmation.tpl';

                break;

            case 'bank_account':                
                $data = array(                    
                    'pdf' => $this->spei_base_url.'/'.$this->merchant_id.'/'.$transaction['id_transaction']
                );

                $this->smarty->assign('openpay_order', $data);
                $template = './views/templates/hook/spei_order_confirmation.tpl';

                break;

            case 'card':
                $template = './views/templates/hook/card_order_confirmation.tpl';
                break;
        }

        return $this->display(__FILE__, $template);
    }

    /**
     * Process a payment
     *
     * @param string $token Openpay Transaction ID (token)
     */
    public function processPayment($payment_method, $token = null, $device_session_id = null, $transaction = null, $interest_free = null) {
        if (!$this->active) {
            return;
        }

        $barcode_url = null;
        $reference = null;
        $clabe = null;
        $content = '';
        $mail_detail = '';
        $message_aux = '';

        try {
            switch ($payment_method) {
                case 'card':
                    $display_name = $this->l('Openpay card payment');
                    $result_json = $this->cardPayment($token, $device_session_id, $interest_free);
                    $order_status = (int) Configuration::get('PS_OS_PAYMENT');

                    $message_aux = $this->l(Tools::ucfirst($result_json->card->type).' card:').' '.
                            Tools::ucfirst($result_json->card->brand).' (Exp: '.$result_json->card->expiration_month.'/'.$result_json->card->expiration_year.')'."\n".
                            $this->l('Card number:').' '.$result_json->card->card_number."\n";

                    break;

                case 'store':
                    $display_name = $this->l('Openpay cash payment');
                    $content = '&content_only=1';
                    $result_json = $this->othersPayment($payment_method);
                    $order_status = (int) Configuration::get('waiting_cash_payment');

                    $barcode_url = $result_json->payment_method->barcode_url;
                    $reference = $result_json->payment_method->reference;

                    $mail_detail = '<br/><img src="'.$barcode_url.'" /><br/><span style="color:#333"><strong>Referencia:</strong></span>'.$reference;

                    $message_aux = $this->l('Reference:').' '.$reference."\n";

                    break;

                case 'bank_account':
                    $display_name = $this->l('Openpay bank payment');
                    $content = '&content_only=1';
                    $result_json = $this->othersPayment($payment_method);
                    $order_status = (int) Configuration::get('waiting_cash_payment');

                    $clabe = $result_json->payment_method->clabe;
                    $reference = $result_json->payment_method->name;

                    $mail_detail = '<br/><span style="color:#333"><strong>Banco:</strong></span> STP<br />
                            <span style="color:#333"><strong>CLABE:</strong></span> '.$clabe.'<br>
                            <span style="color:#333"><strong>Referencia:</strong></span> '.$reference;

                    $message_aux = $this->l('CLABE:').' '.$clabe."\n".
                            $this->l('Reference:').' '.$reference."\n";

                    break;
            }

            $message = $this->l('Openpay Transaction Details:')."\n\n".
                    $this->l('Transaction ID:').' '.$result_json->id."\n".
                    $this->l('Payment method:').' '.Tools::ucfirst($payment_method)."\n".
                    $message_aux.
                    $this->l('Amount:').' $'.number_format($result_json->amount, 2).' '.Tools::strtoupper($result_json->currency)."\n".
                    $this->l('Status:').' '.($result_json->status == 'completed' ? $this->l('Paid') : $this->l('Unpaid'))."\n".
                    $this->l('Processed on:').' '.date('Y-m-d H:i:s')."\n".
                    $this->l('Mode:').' '.(Configuration::get('OPENPAY_MODE') ? $this->l('Live') : $this->l('Test'))."\n";

            /* Create the PrestaShop order in database */
            $detail = array('{detail}' => $mail_detail);
            $this->validateOrder(
                    (int) $this->context->cart->id, (int) $order_status, $result_json->amount, $display_name, $message, $detail, null, false, $this->context->customer->secure_key
            );

            /** @since 1.5.0 Attach the Openpay Transaction ID to this Order */
            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                $new_order = new Order((int) $this->currentOrder);
                if (Validate::isLoadedObject($new_order)) {
                    $payment = $new_order->getOrderPaymentCollection();
                    if (isset($payment[0])) {
                        $payment[0]->transaction_id = pSQL($result_json->id);
                        $payment[0]->save();
                    }
                }
            }

            $fee = ($result_json->amount * 0.029) + 2.5;

            if ($result_json->due_date) {
                $due_date = $result_json->due_date;
            } else {
                $due_date = date('Y-m-d H:i:s');
            }

            $due_date = date('Y-m-d H:i:s');
            if ($result_json->due_date) {
                $due_date = date('Y-m-d H:i:s', strtotime($result_json->due_date));
            }

            /** Store the transaction details */
            try {
                Db::getInstance()->insert('openpay_transaction', array(
                    'type' => pSQL($payment_method),
                    'id_cart' => (int) $this->context->cart->id,
                    'id_order' => (int) $this->currentOrder,
                    'id_transaction' => pSQL($result_json->id),
                    'amount' => (float) $result_json->amount,
                    'status' => pSQL($result_json->status == 'completed' ? 'paid' : 'unpaid'),
                    'fee' => (float) $fee,
                    'currency' => pSQL($result_json->currency),
                    'mode' => pSQL(Configuration::get('OPENPAY_MODE') == 'true' ? 'live' : 'test'),
                    'date_add' => date('Y-m-d H:i:s'),
                    'due_date' => $due_date,
                    'barcode' => pSQL($barcode_url),
                    'reference' => pSQL($reference),
                    'clabe' => pSQL($clabe)
                ));
            } catch (Exception $e) {
                if (class_exists('Logger')) {
                    Logger::addLog($e->getMessage(), 1, null, null, null, true);
                }
            }

            // Update order_id from Openpay Charge
            if ($payment_method != 'bitcoin') {
                $this->updateOpenpayCharge($result_json->id, $this->currentOrder, $new_order->reference);
            }

            /** Redirect the user to the order confirmation page history */
            $redirect = __PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.(int) $this->context->cart->id.
                    $content.
                    '&id_module='.(int) $this->id.
                    '&id_order='.(int) $this->currentOrder.
                    '&key='.$this->context->customer->secure_key;

            Tools::redirect($redirect);
            exit;

            /** catch the openpay error */
        } catch (Exception $e) {
            $message = $e->getMessage();

            if (class_exists('Logger')) {
                Logger::addLog($this->l('Openpay - Payment transaction failed').' '.$message, 1, null, 'Cart', (int) $this->context->cart->id, true);
            }

            $this->context->cookie->__set('openpay_error', $e->getMessage());

            $redirect = $this->context->link->getModuleLink('openpayprestashop', 'cardpayment');
            Tools::redirect($redirect);
            exit;
        }
    }

    public function cardPayment($token, $device_session_id, $interest_free) {
        $cart = $this->context->cart;
        $openpay_customer = $this->getOpenpayCustomer($this->context->cookie->id_customer);

        $charge_request = array(
            'method' => 'card',
            'currency' => $this->context->currency->iso_code,
            'source_id' => $token,
            'device_session_id' => $device_session_id,
            'amount' => $cart->getOrderTotal(),
            'description' => $this->l('PrestaShop Cart ID:').' '.(int) $cart->id,
        );

        if ($interest_free > 1) {
            $charge_request['payment_plan'] = array('payments' => (int) $interest_free);
        }

        $result_json = $this->createOpenpayCharge($openpay_customer, $charge_request);

        return $result_json;
    }

    public function othersPayment($payment_method) {
        $openpay_customer = $this->getOpenpayCustomer($this->context->cookie->id_customer);
        $cart = $this->context->cart;
        $deadline = 720;

        if ($payment_method == 'store') {
            if (Configuration::get('OPENPAY_DEADLINE_STORES') && Configuration::get('OPENPAY_DEADLINE_STORES') > 0) {
                $deadline = Configuration::get('OPENPAY_DEADLINE_STORES');
            }
        } else {
            if (Configuration::get('OPENPAY_DEADLINE_SPEI') && Configuration::get('OPENPAY_DEADLINE_SPEI') > 0) {
                $deadline = Configuration::get('OPENPAY_DEADLINE_SPEI');
            }
        }

        $due_date = date('Y-m-d\TH:i:s', strtotime('+ '.$deadline.' hours'));

        $charge_request = array(
            'method' => $payment_method,
            'amount' => $cart->getOrderTotal(),
            'description' => $this->l('PrestaShop Cart ID:').' '.(int) $cart->id,
            'due_date' => $due_date
        );

        $result_json = $this->createOpenpayCharge($openpay_customer, $charge_request);

        return $result_json;
    }

    /**
     * Check settings requirements to make sure the Openpay's module will work properly
     *
     * @return boolean Check result
     */
    public function checkSettings() {
        if (Configuration::get('OPENPAY_MODE')) {
            return Configuration::get('OPENPAY_PUBLIC_KEY_LIVE') != '' && Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') != '';
        } else {
            return Configuration::get('OPENPAY_PUBLIC_KEY_TEST') != '' && Configuration::get('OPENPAY_PRIVATE_KEY_TEST') != '';
        }
    }

    /**
     * Check technical requirements to make sure the Openpay's module will work properly
     *
     * @return array Requirements tests results
     */
    public function checkRequirements() {
        $tests = array('result' => true);

        $tests['curl'] = array(
            'name' => $this->l('PHP cURL extension must be enabled on your server'),
            'result' => extension_loaded('curl'));

        if (Configuration::get('OPENPAY_MODE')) {
            $tests['ssl'] = array(
                'name' => $this->l('SSL must be enabled on your store (before entering Live mode)'),
                'result' => Configuration::get('PS_SSL_ENABLED') || (!empty($_SERVER['HTTPS']) && Tools::strtolower($_SERVER['HTTPS']) != 'off'));
        }

        $tests['php52'] = array(
            'name' => $this->l('Your server must run PHP 5.2 or greater'),
            'result' => version_compare(PHP_VERSION, '5.2.0', '>='));

        $tests['configuration'] = array(
            'name' => $this->l('You must sign-up for Openpay and configure your account settings in the module (publishable key, secret key...etc.)'),
            'result' => $this->getMerchantInfo());

        foreach ($tests as $k => $test) {
            if ($k != 'result' && !$test['result']) {
                $tests['result'] = false;
            }
        }

        return $tests;
    }

    /**
     * Display the Back-office interface of the Openpay's module
     *
     * @return string HTML/JS Content
     */
    public function getContent() {
        $this->context->controller->addCSS(array($this->_path.'views/css/openpay-prestashop-admin.css'));

        $errors = array();

        /** Update Configuration Values when settings are updated */
        if (Tools::isSubmit('SubmitOpenpay')) {

            $configuration_values = array(
                'OPENPAY_MODE' => Tools::getValue('openpay_mode'),
                'OPENPAY_MERCHANT_ID_TEST' => trim(Tools::getValue('openpay_merchant_id_test')),
                'OPENPAY_MERCHANT_ID_LIVE' => trim(Tools::getValue('openpay_merchant_id_live')),
                'OPENPAY_PUBLIC_KEY_TEST' => trim(Tools::getValue('openpay_public_key_test')),
                'OPENPAY_PUBLIC_KEY_LIVE' => trim(Tools::getValue('openpay_public_key_live')),
                'OPENPAY_PRIVATE_KEY_TEST' => trim(Tools::getValue('openpay_private_key_test')),
                'OPENPAY_PRIVATE_KEY_LIVE' => trim(Tools::getValue('openpay_private_key_live')),
                'OPENPAY_DEADLINE_STORES' => trim(Tools::getValue('openpay_deadline_stores')),
                'OPENPAY_DEADLINE_SPEI' => trim(Tools::getValue('openpay_deadline_spei')),
                'OPENPAY_CARDS' => Tools::getValue('openpay_cards'),
                'OPENPAY_STORES' => Tools::getValue('openpay_stores'),
                'OPENPAY_SPEI' => Tools::getValue('openpay_spei'),
                'OPENPAY_BACKGROUND_COLOR' => Tools::getValue('openpay_background_color'),
                'OPENPAY_FONT_COLOR' => Tools::getValue('openpay_font_color'),
                'OPENPAY_WEBHOOK_URL' => trim(Tools::getValue('openpay_webhook_url')),
                'OPENPAY_MONTHS_INTEREST_FREE' => implode(',', Tools::getValue('months_interest_free')),
                'OPENPAY_MINIMUM_AMOUNT' => Tools::getValue('openpay_minimum_amount_interest_free')
            );

            foreach ($configuration_values as $configuration_key => $configuration_value) {
                Configuration::updateValue($configuration_key, $configuration_value);
            }

            $mode = Configuration::get('OPENPAY_MODE') ? 'LIVE' : 'TEST';

            // Creates Webhook
            $webhook = $this->createWebhook();
            if ($webhook->error) {
                $errors[] = $webhook->msg;
            }

            if (!$this->getMerchantInfo()) {
                $errors[] = 'Openpay keys are incorrect.';
                Configuration::deleteByName('OPENPAY_PUBLIC_KEY_'.$mode);
                Configuration::deleteByName('OPENPAY_MERCHANT_ID_'.$mode);
                Configuration::deleteByName('OPENPAY_PRIVATE_KEY_'.$mode);
                Configuration::deleteByName('OPENPAY_WEBHOOK_ID_'.$mode);
                Configuration::deleteByName('OPENPAY_DEADLINE_STORES');
                Configuration::deleteByName('OPENPAY_DEADLINE_SPEI');
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->error[] = $error;
            }
        }

        $requirements = $this->checkRequirements();

        foreach ($requirements as $key => $requirement) {
            if ($key != 'result') {
                $this->validation[] = $requirement;
            }
        }

        if ($requirements['result']) {
            $validation_title = $this->l('All the checks were successfully performed. You can now configure your module and start using Openpay.');
        } else {
            $validation_title = $this->l('At least one issue is preventing you from using Openpay. Please fix the issue and reload this page.');
        }

        $selected_months_interest_free = array();
        if (Configuration::get('OPENPAY_MONTHS_INTEREST_FREE') != null) {
            $selected_months_interest_free = explode(',', Configuration::get('OPENPAY_MONTHS_INTEREST_FREE'));
        }

        $this->context->smarty->assign(array(
            'receipt' => $this->_path.'views/img/recibo.png',
            'openpay_form_link' => $_SERVER['REQUEST_URI'],
            'openpay_configuration' => Configuration::getMultiple(
                    array(
                        'OPENPAY_MODE',
                        'OPENPAY_MERCHANT_ID_TEST',
                        'OPENPAY_MERCHANT_ID_LIVE',
                        'OPENPAY_PUBLIC_KEY_TEST',
                        'OPENPAY_PUBLIC_KEY_LIVE',
                        'OPENPAY_PRIVATE_KEY_TEST',
                        'OPENPAY_PRIVATE_KEY_LIVE',
                        'OPENPAY_DEADLINE_STORES',
                        'OPENPAY_DEADLINE_SPEI',
                        'OPENPAY_CARDS',
                        'OPENPAY_STORES',
                        'OPENPAY_SPEI',
                        'OPENPAY_BACKGROUND_COLOR',
                        'OPENPAY_FONT_COLOR',
                        'OPENPAY_WEBHOOK_URL',
                        'OPENPAY_MINIMUM_AMOUNT'
                    )
            ),
            'openpay_ssl' => Configuration::get('PS_SSL_ENABLED'),
            'openpay_validation' => $this->validation,
            'openpay_error' => (empty($this->error) ? false : $this->error),
            'openpay_validation_title' => $validation_title,
            'months_interest_free' => $this->months_interest_free,
            'selected_months_interest_free' => $selected_months_interest_free
        ));

        return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
    }

    /**
     * Check availables currencies for module
     * 
     * @return boolean
     */
    public function checkCurrency() {
        return in_array($this->context->currency->iso_code, $this->limited_currencies);
    }

    public function getPath() {
        return $this->_path;
    }

    public function getOpenpayCustomer($customer_id) {
        $cart = $this->context->cart;
        $customer = new Customer((int) $cart->id_customer);

        $openpay_customer = Db::getInstance()->getRow('
                    SELECT openpay_customer_id
                    FROM '._DB_PREFIX_.'openpay_customer
                    WHERE id_customer = '.(int) $customer_id);

        if (!isset($openpay_customer['openpay_customer_id'])) {
            try {

                $address = Db::getInstance()->getRow('
                    SELECT *
                    FROM '._DB_PREFIX_.'address
                    WHERE id_customer = '.(int) $customer_id);

                $state = Db::getInstance()->getRow('
                    SELECT id_country, name
                    FROM '._DB_PREFIX_.'state
                    WHERE id_state = '.(int) $address['id_state']);


                $customer_data = array(
                    'requires_account' => false,
                    'name' => $customer->firstname,
                    'last_name' => $customer->lastname,
                    'email' => $customer->email,
                    'phone_number' => $address['phone'],
                );

                if (!$this->isNullOrEmpty($address['address1']) && !$this->isNullOrEmpty($address['postcode']) && !$this->isNullOrEmpty($address['city']) && !$this->isNullOrEmpty($state['name'])) {
                    $customer_data['address'] = array(
                        'line1' => $address['address1'],
                        'line2' => $address['address2'],
                        'postal_code' => $address['postcode'],
                        'city' => $address['city'],
                        'state' => $state['name'],
                        'country_code' => 'MX'
                    );
                    $string_array = http_build_query($customer_data, '', ', ');
                    Logger::addLog($this->l('Customer Address: ').$string_array, 1, null, 'Cart', (int) $this->context->cart->id, true);
                }

                $customer_openpay = $this->createOpenpayCustomer($customer_data);

                Db::getInstance()->Execute('
                        INSERT INTO '._DB_PREFIX_.'openpay_customer (id_openpay_customer, openpay_customer_id, id_customer, date_add)
                        VALUES (null, \''.pSQL($customer_openpay->id).'\', '.(int) $this->context->cookie->id_customer.', NOW())');

                return $customer_openpay;
            } catch (Exception $e) {
                if (class_exists('Logger')) {
                    Logger::addLog($this->l('Openpay - Can not create Openpay Customer'), 1, null, 'Cart', (int) $this->context->cart->id, true);
                }
            }
        } else {
            return $this->getCustomer($openpay_customer['openpay_customer_id']);
        }
    }

    public function getCustomer($customer_id) {
        $openpay = Openpay::getInstance($this->merchant_id, $this->secret_key);
        Openpay::setSandboxMode($this->is_sandbox);

        $customer = $openpay->customers->get($customer_id);
        return $customer;
    }

    public function createOpenpayCustomer($customer_data) {
        $openpay = Openpay::getInstance($this->merchant_id, $this->secret_key);
        Openpay::setSandboxMode($this->is_sandbox);

        $userAgent = "Openpay-PS16MX/v2";
        Openpay::setUserAgent($userAgent);

        try {
            $customer = $openpay->customers->add($customer_data);
            return $customer;
        } catch (Exception $e) {
            $this->error($e);
        }
    }

    public function createOpenpayCharge($customer, $charge_request) {
        Openpay::getInstance($this->merchant_id, $this->secret_key);
        Openpay::setSandboxMode($this->is_sandbox);
        
        $userAgent = "Openpay-PS16MX/v2";
        Openpay::setUserAgent($userAgent);

        try {
            $charge = $customer->charges->create($charge_request);
            return $charge;
        } catch (Exception $e) {
            $this->error($e);
        }
    }

    public function updateOpenpayCharge($transaction_id, $order_id, $reference) {
        $customer = $this->getOpenpayCustomer($this->context->cookie->id_customer);
        Openpay::getInstance($this->merchant_id, $this->secret_key);
        Openpay::setSandboxMode($this->is_sandbox);

        $userAgent = "Openpay-PS16MX/v2";
        Openpay::setUserAgent($userAgent);

        try {
            $charge = $customer->charges->get($transaction_id);
            $charge->update(array('order_id' => $order_id, 'description' => 'PrestaShop ORDER #'.$reference));
            return true;
        } catch (Exception $e) {
            $this->error($e);
        }
    }

    public function getOpenpayCharge($customer, $transaction_id) {
        Openpay::getInstance($this->merchant_id, $this->secret_key);
        Openpay::setSandboxMode($this->is_sandbox);

        try {
            $charge = $customer->charges->get($transaction_id);
            return $charge;
        } catch (Exception $e) {
            $this->error($e);
        }
    }

    public function createWebhook($force_host_ssl = false) {

        $domain = rtrim(Configuration::get('OPENPAY_WEBHOOK_URL'), "/");
        $webhook_data = array(
            'url' => $domain.'/modules/openpayprestashop/notification.php',
            'force_host_ssl' => $force_host_ssl,
            'event_types' => array(
                'verification',
                'charge.succeeded',
                'charge.cancelled',
                'charge.failed',
                'payout.created',
                'payout.succeeded',
                'payout.failed',
                'spei.received',
                'chargeback.created',
                'chargeback.rejected',
                'chargeback.accepted',
                'transaction.expired'
            )
        );

        $mode = Configuration::get('OPENPAY_MODE') ? 'LIVE' : 'TEST';

        $openpay = Openpay::getInstance($this->merchant_id, $this->secret_key);
        Openpay::setSandboxMode($this->is_sandbox);

        $userAgent = "Openpay-PS16MX/v2";
        Openpay::setUserAgent($userAgent);

        try {
            $webhook = $openpay->webhooks->add($webhook_data);
            Configuration::updateValue('OPENPAY_WEBHOOK_ID_'.$mode, $webhook->id);
            return $webhook;
        } catch (Exception $e) {
            $force_host_ssl = ($force_host_ssl === false) ? true : false; // Si viene con parámtro FALSE, solicito que se force el host SSL
            return $this->errorWebhook($e, $force_host_ssl);
        }
    }

    public function deleteWebhook($webhook_id, $mode) {
        $openpay = Openpay::getInstance($this->merchant_id, $this->secret_key);
        Openpay::setSandboxMode($this->is_sandbox);

        try {
            $webhook = $openpay->webhooks->get($webhook_id);
            $webhook->delete();
            return true;
        } catch (Exception $e) {
            return $this->error($e, true);
        }
    }

    public function getMerchantInfo() {
        
        $sk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        $url = (Configuration::get('OPENPAY_MODE') ? $this->url : $this->sandbox_url).'/'.$id;
        $username = $sk;
        $password = '';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CAINFO, _PS_MODULE_DIR_.$this->name.'/lib/data/cacert.pem');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        $result = curl_exec($ch);

        if (curl_exec($ch) === false) {
            Logger::addLog('Curl error '.curl_errno($ch).': '.curl_error($ch), 1, null, null, null, true);
        } else {
            $info = curl_getinfo($ch);
            Logger::addLog('HTTP code '.$info['http_code'].' on request to '.$info['url'], 1, null, null, null, true);
        }

        curl_close($ch);

        $array = Tools::jsonDecode($result, true);

        if (array_key_exists('id', $array)) {
            return true;
        } else {
            return false;
        }
    }

    public function error($e, $backend = false) {
        switch ($e->getErrorCode()) {
            /* ERRORES GENERALES */
            case '1000':
            case '1004':
            case '1005':
                $msg = $this->l('Service not available.');
                break;

            /* ERRORES TARJETA */
            case '3001':
            case '3004':
            case '3005':
            case '3007':
                $msg = $this->l('The card was declined.');
                break;

            case '3002':
                $msg = $this->l('The card has expired.');
                break;

            case '3003':
                $msg = $this->l('The card does not have sufficient funds');
                break;

            case '3006':
                $msg = $this->l('The operation is not permitted for this client or this transaction.');
                break;

            case '3008':
                $msg = $this->l('The card is not supported on online transactions.');
                break;

            case '3009':
                $msg = $this->l('The card was reported lost.');
                break;

            case '3010':
                $msg = $this->l('The bank has restricted the card.');
                break;

            case '3011':
                $msg = $this->l('The bank has requested that the card is retained. Contact the bank.');
                break;

            case '3012':
                $msg = $this->l('Ask the bank is required to make this payment authorization.');
                break;

            default: /* Demás errores 400 */
                $msg = $this->l('The request could not be processed.');
                break;
        }

        $error = 'ERROR '.$e->getErrorCode().'. '.$msg;

        if ($backend) {
            return Tools::jsonDecode(Tools::jsonEncode(array('error' => $e->getErrorCode(), 'msg' => $error)), false);
        } else {
            throw new Exception($error);
        }
    }

    public function errorWebhook($e, $force_host_ssl) {
        switch ($e->getErrorCode()) {
            /* ERRORES GENERALES */
            case '1000':
            case '1004':
            case '1005':
                $msg = $this->l('Service not available.');
                break;

            /* ERRORES WEBHOOK */
            case '6001':
                $msg = $this->l('Webhook already exists.');
                ;
                break;

            case '6002':
            case '6003';
                $msg = $this->l('Webhook can\'t be created.');
                if ($force_host_ssl == true) {
                    $this->createWebhook(true);
                }
                break;

            default: /* Demás errores 400 */
                $msg = $this->l('The request could not be processed.');
                break;
        }

        $error = 'ERROR '.$e->getErrorCode().'. '.$msg;

        if ($e->getErrorCode() != '6001') {
            return Tools::jsonDecode(Tools::jsonEncode(array('error' => $e->getErrorCode(), 'msg' => $error)), false);
        }

        return;
    }

    public function isNullOrEmpty($string) {
        return (!isset($string) || trim($string) === '');
    }

    public function copyMailTemplate() {
        $directory = _PS_MAIL_DIR_;
        if ($dhvalue = opendir($directory)) {
            while (($file = readdir($dhvalue)) !== false) {
                if (is_dir($directory.$file) && $file[0] != '.') {

                    $html_file_origin = _PS_MODULE_DIR_.$this->name.'/mails/'.$file.'/openpayprestashop.html';
                    $txt_file_origin = _PS_MODULE_DIR_.$this->name.'/mails/'.$file.'/openpayprestashop.txt';

                    /*
                     * If origin files does not exist, skip the loop
                     */
                    if (!file_exists($html_file_origin) && !file_exists($txt_file_origin)) {
                        continue;
                    }

                    $html_file_destination = $directory.$file.'/openpayprestashop.html';
                    $txt_file_destination = $directory.$file.'/openpayprestashop.txt';

                    if (!Tools::copy($html_file_origin, $html_file_destination)) {
                        $errors = error_get_last();
                        throw new Exception('Error: '.$errors['message']);
                    }

                    if (!Tools::copy($txt_file_origin, $txt_file_destination)) {
                        //throw new Exception('Can not copy custom "Awaiting payment" email, please recursive write permission for ~/mails/');
                        $errors = error_get_last();
                        throw new Exception('Error: '.$errors['message']);
                    }
                }
            }
            closedir($dhvalue);
        }
    }

}
