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

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OpenpayPrestashop extends PaymentModule
{

    private $error = array();
    private $validation = array();
    private $limited_currencies = array('MXN', 'USD');

    public function __construct() {
        if (!class_exists('Openpay', false)) {
            include_once(dirname(__FILE__).'/lib/Openpay.php');
        }

        $this->sandbox_url = 'https://sandbox-api.openpay.mx/v1';
        $this->url = 'https://api.openpay.mx/v1';

        $this->name = 'openpayprestashop';
        $this->tab = 'payments_gateways';
        $this->version = '3.0.3';
        $this->author = 'Openpay SAPI de CV';
        $this->module_key = '23c1a97b2718ec0aec28bb9b3b2fc6d5';               

        parent::__construct();
        $warning = '¿Estás seguro de querer desinstalar este módulo?';
        $this->displayName = $this->l('Openpay');
        $this->description = $this->l('Acepta pagos con tarjeta de crédito con Openpay.');
        $this->confirmUninstall = $this->l($warning);
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->months_interest_free = array('3' => '3 meses', '6' => '6 meses', '9' => '9 meses', '12' => '12 meses', '18' => '18 meses');        
    }

    /**
     * Openpay's module installation
     *
     * @return boolean Install result
     */
    public function install() {
        $ret = parent::install() && $this->createPendingState() && 
                $this->registerHook('payment') &&
                $this->registerHook('paymentOptions') &&
                $this->registerHook('displayHeader') &&
                $this->registerHook('displayPaymentTop') &&
                $this->registerHook('paymentReturn') &&
                $this->registerHook('displayMobileHeader') &&
                $this->registerHook('displayAdminOrder') &&
                $this->registerHook('actionOrderStatusPostUpdate') &&
                Configuration::updateValue('OPENPAY_MODE', 0) &&
                Configuration::updateValue('OPENPAY_MONTHS_INTEREST_FREE', '') &&                
                Configuration::updateValue('OPENPAY_CHARGE_TYPE', 'direct') &&      
                Configuration::updateValue('USE_CARD_POINTS', '0') &&                      
                Configuration::updateValue('OPENPAY_CAPTURE', 'true') &&                      
                Configuration::updateValue('OPENPAY_SAVE_CC', '0') &&                      
                $this->installDb();

        return $ret;
    }
    
    private function createPendingState() {
        $state = new OrderState();
        $languages = Language::getLanguages();
        $names = array();

        foreach ($languages as $lang) {
            $names[$lang['id_lang']] = $this->l('Pre-autorizado');
        }

        $state->name = $names;
        $state->color = '#ECB033';
        $state->send_email = false;
        $state->module_name = 'openpayprestashop';
        $templ = array();

        foreach ($languages as $lang) {
            $templ[$lang['id_lang']] = 'openpayprestashop';
        }

        $state->template = $templ;

        if ($state->save()) {
            try {
                Configuration::updateValue('OPENPAY_OS_HOLD', $state->id);
                //$this->copyMailTemplate();
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
                `mode` enum(\'live\',\'test\') NOT NULL,
                PRIMARY KEY (`id_openpay_customer`),
                KEY `id_customer` (`id_customer`)) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');

        $return &= Db::getInstance()->Execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'openpay_transaction` (
                `id_openpay_transaction` int(11) NOT NULL AUTO_INCREMENT,
                `type` enum(\'card\',\'store\',\'bank_account\', \'bitcoin\') NOT NULL,
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
        return parent::uninstall() &&                
            Configuration::deleteByName('OPENPAY_MONTHS_INTEREST_FREE');
    }
    
    /**
     * Capturar pago de una pre-autorización
     * 
     * @link https://devdocs.prestashop.com/1.7/modules/concepts/hooks/list-of-hooks/
     * @param type $params
     * @return type
     */
    public function hookActionOrderStatusPostUpdate($params) {
        Logger::addLog('hookActionOrderStatusPostUpdate => '.$params['id_order'], 1, null, null, null, true);
        
        $order_id =  $params['id_order'];
        
        $order = new Order((int) $order_id);        
        
        $openpay_transaction = Db::getInstance()->getRow('
            SELECT *
            FROM '._DB_PREFIX_.'openpay_transaction
            WHERE id_order = '.(int) $order_id
        );
        
        if (!$openpay_transaction) {
            Logger::addLog('There is no Openpay transaction for Order ID => '.$order_id, 2, null, null, null, true);  
            return;
        }
        
        $openpay_customer = Db::getInstance()->getRow('
            SELECT openpay_customer_id
            FROM '._DB_PREFIX_.'openpay_customer
            WHERE id_customer = '.(int) $order->id_customer
        );               
        
        Logger::addLog('amount_capture => '.$order->total_paid, 1, null, null, null, true);
        Logger::addLog('order_estatus => '.$order->getCurrentState(), 1, null, null, null, true);
        Logger::addLog('PS_OS_PAYMENT => '.Configuration::get('PS_OS_PAYMENT'), 1, null, null, null, true);
        Logger::addLog('id_transaction => '.$openpay_transaction['id_transaction'], 1, null, null, null, true);        
        
        try {
            if (Configuration::get('PS_OS_PAYMENT') == $order->getCurrentState() && $openpay_transaction['status'] == 'unpaid' && $openpay_transaction['type'] == 'card') {
                $charge = $this->capture($order->total_paid, $openpay_transaction['id_transaction'], $openpay_customer['openpay_customer_id']);                             
                
                $payment = $order->getOrderPaymentCollection();
                if (isset($payment[0])) {
                    $payment[0]->transaction_id = pSQL($charge->id);
                    $payment[0]->card_holder = pSQL($charge->card->holder_name);
                    $payment[0]->card_number = pSQL($charge->card->card_number);
                    $payment[0]->card_brand = pSQL(strtoupper($charge->card->brand));
                    $payment[0]->card_expiration = pSQL($charge->card->expiration_month.'/'.$charge->card->expiration_year);
                    $payment[0]->save();
                }
            }
        } catch (Exception $e) {
            if (class_exists('Logger')) {
                Logger::addLog($this->l('Openpay - Capture failed').' '.$e->getMessage(), 1, null, 'Cart', (int) $this->context->cart->id, true);
                Logger::addLog($this->l('Openpay - Capture failed').' '.$e->getTraceAsString(), 4, $e->getCode(), 'Cart', (int) $this->context->cart->id, true);
            }
        }
                
        return;
    }
    
    private function capture($amount, $transaction_id, $customer_id) {
        try {
            $openpay = $this->getOpenpayInstance();
            
            $customer = $openpay->customers->get($customer_id);
            $charge = $customer->charges->get($transaction_id);
            $charge->capture(array('amount' => floatval($amount)));
            
            return $charge;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function hookDisplayAdminOrder($params) {        
        Logger::addLog('hookDisplayAdminOrder', 1, null, null, null, true);
        
        $refund_url = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/openpayprestashop/refund.php';
        $order = new Order((int) $params['id_order']);
        
        $order_state_query = 'SELECT * FROM `' . _DB_PREFIX_ . 'openpay_transaction` WHERE `id_order`= "' . $params['id_order'] . '"';
        $order_state = Db::getInstance()->ExecuteS($order_state_query);
        
        Logger::addLog('order_estatus => '.$order->getCurrentState(), 1, null, null, null, true);
        Logger::addLog('PS_OS_REFUND => '.Configuration::get('PS_OS_REFUND'), 1, null, null, null, true);
        Logger::addLog('id_transaction => '.$order_state[0]['id_transaction'], 1, null, null, null, true);
        
        
        $show_refund = Configuration::get('PS_OS_REFUND') == $order->getCurrentState();
        
        $this->smarty->assign(array(
            'refund_url' => $refund_url,
            'show_refund' => $show_refund,
            'order_id' => $params['id_order'],            
            'transaction_id' => $order_state[0]['id_transaction'],
            'form_action' => '',
            'error' => ''
        ));
        
        return $this->display(__FILE__, 'order_detail.tpl');        
    }

    /**
     * Hook to the top a payment page
     *
     * @param array $params Hook parameters
     * @return string Hook HTML
     */
    public function hookDisplayPaymentTop($params) {
        Logger::addLog('hookDisplayPaymentTop', 1, null, null, null, true);
        return;
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
    public function hookHeader($params) {
        if (!$this->active) {
            return;
        }

        if (Tools::getValue('module') === 'onepagecheckoutps' ||
                Tools::getValue('controller') === 'order-opc' ||
                Tools::getValue('controller') === 'orderopc' ||
                Tools::getValue('controller') === 'order') {

            $this->context->controller->addCSS($this->_path.'views/css/openpay-prestashop.css');

            $this->context->controller->registerJavascript(
                    'remote-openpay-js', 'https://openpay.s3.amazonaws.com/openpay.v1.min.js', ['position' => 'bottom', 'server' => 'remote']
            );

            $this->context->controller->registerJavascript(
                    'remote-openpaydata-js', 'https://openpay.s3.amazonaws.com/openpay-data.v1.min.js', ['position' => 'bottom', 'server' => 'remote']
            );
        } else {
            Logger::addLog('NO hookHeader, Controller => '.Tools::getValue('controller'), 1, null, null, null, true);
        }
    }

    /**
     * Hook to the new PS 1.7 payment options hook
     *
     * @param array $params Hook parameters
     * @return array|bool
     * @throws Exception
     * @throws SmartyException
     */
    public function hookPaymentOptions($params) {

        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            return false;
        }
        /** @var Cart $cart */
        $cart = $params['cart'];
        if (!$this->active) {
            return false;
        }

        if (!$this->checkCurrency()) {
            return;
        }

        $externalOption = new PaymentOption();
        $externalOption->setCallToActionText($this->l('Tarjeta de crédito-débito'))                    
                ->setForm($this->generateForm($cart))
                ->setModuleName($this->name)
                ->setAdditionalInformation($this->context->smarty->fetch('module:openpayprestashop/views/templates/front/payment_infos.tpl'));

        return array($externalOption);
    }

    protected function generateForm($cart) {
        $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PUBLIC_KEY_LIVE') : Configuration::get('OPENPAY_PUBLIC_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        $selected_months_interest_free = array();
        if (Configuration::get('OPENPAY_MONTHS_INTEREST_FREE') != null) {
            $selected_months_interest_free = explode(',', Configuration::get('OPENPAY_MONTHS_INTEREST_FREE'));
        }

        $show_months_interest_free = false;
        if (count($selected_months_interest_free) > 0) {
            $show_months_interest_free = true;
        }

        Logger::addLog('generateForm => '.$this->context->cookie->openpay_error, 1, null, null, null, true);

        if (!empty($this->context->cookie->openpay_error)) {
            $this->context->smarty->assign('openpay_error', $this->context->cookie->openpay_error);
            $this->context->cookie->__set('openpay_error', null);
        }

        $this->context->smarty->assign(array(
            'js_dir' => _PS_JS_DIR_,
            'pk' => $pk,
            'id' => $id,
            'mode' => Configuration::get('OPENPAY_MODE'),
            'nbProducts' => $cart->nbProducts(),
            'total' => $cart->getOrderTotal(),
            'module_dir' => $this->_path,
            'months_interest_free' => $selected_months_interest_free,
            'show_months_interest_free' => $show_months_interest_free,
            'use_card_points' => Configuration::get('USE_CARD_POINTS'),
            'can_save_cc' => Configuration::get('OPENPAY_SAVE_CC') == '1' && (bool)$this->context->customer->isLogged() ? true : false,
            'cc_options' => $this->getCreditCardList(),
            'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), Tools::usingSecureMode()),
        ));

        return $this->context->smarty->fetch('module:openpayprestashop/views/templates/front/cc_form.tpl');
    }

    /**
     * Process a payment
     *
     * @param string $token Openpay Transaction ID (token)
     */
    public function processPayment($token = null, $device_session_id = null, $interest_free = null, $use_card_points = null, $openpay_cc = null, $save_cc = false) {
        if (!$this->active) {
            return;
        }
        
        $mail_detail = '';
        $message_aux = '';
        $payment_method = 'card';
        $charge_type = Configuration::get('OPENPAY_CHARGE_TYPE');
        $cart = $this->context->cart;
        $display_name = $this->l('Openpay card payment');
        $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');
        $capture = Configuration::get('OPENPAY_CAPTURE') == 'true' ? true : false;

        Openpay::getInstance($id, $pk);
        Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));
        
        try {   
            Logger::addLog('$save_cc => '. json_encode($save_cc), 1, null, null, null, true);
            $openpay_customer = $this->getOpenpayCustomer($this->context->cookie->id_customer);
                        
            $charge_request = array(
                'method' => 'card',
                'currency' => $this->context->currency->iso_code,
                'source_id' => $token,
                'device_session_id' => $device_session_id,
                'amount' => round($cart->getOrderTotal(), 2),
                'description' => $this->l('PrestaShop Cart ID:').' '.(int) $cart->id,
                'use_card_points' => $use_card_points,
                'capture' => $capture
            );

            if ($interest_free > 1) {
                $charge_request['payment_plan'] = array('payments' => (int) $interest_free);
            }

            if ($charge_type == '3d') {
                $charge_request['use_3d_secure'] = true;
                $charge_request['redirect_url'] = _PS_BASE_URL_.__PS_BASE_URI__.'module/openpayprestashop/confirm';
            }       
            
            // Si desea guardar la TC se crea y se asigna a los parámetros de la transacción
            if ($save_cc === true && $openpay_cc == 'new') {
                $card_data = array(            
                    'token_id' => $token,            
                    'device_session_id' => $device_session_id
                );
                $card = $this->createCreditCard($openpay_customer, $card_data);

                // Se reemplaza el "source_id" por el ID de la tarjeta
                $charge_request['source_id'] = $card->id;                                                            
            }  
                             
            $charge = $openpay_customer->charges->create($charge_request);
               

            // Si tiene habilitado el 3D SECURE 
            if ($charge->payment_method && $charge->payment_method->type == 'redirect') {
                Tools::redirect($charge->payment_method->url);
            }

            $order_status = $capture ? (int) Configuration::get('PS_OS_PAYMENT') : (int) Configuration::get('OPENPAY_OS_HOLD');

            $message_aux = $this->l(Tools::ucfirst($charge->card->type).' card:').' '.
                    Tools::ucfirst($charge->card->brand).' (Exp: '.$charge->card->expiration_month.'/'.$charge->card->expiration_year.')'."\n".
                    $this->l('Card number:').' '.$charge->card->card_number."\n";

            $message = $this->l('Openpay Transaction Details:')."\n\n".
                    $this->l('Transaction ID:').' '.$charge->id."\n".
                    $this->l('Payment method:').' '.Tools::ucfirst($payment_method)."\n".
                    $message_aux.
                    $this->l('Amount:').' $'.number_format($charge->amount, 2).' '.Tools::strtoupper($charge->currency)."\n".
                    $this->l('Status:').' '.($charge->status == 'completed' ? $this->l('Paid') : $this->l('Unpaid'))."\n".
                    $this->l('Processed on:').' '.date('Y-m-d H:i:s')."\n".
                    $this->l('Mode:').' '.(Configuration::get('OPENPAY_MODE') ? $this->l('Live') : $this->l('Test'))."\n";

            /* Create the PrestaShop order in database */
            $detail = array('{detail}' => $mail_detail);
            
            Logger::addLog('OrderStatus => '.$order_status, 1, null, null, null, true);
            
            $this->validateOrder(
                    (int) $this->context->cart->id, (int) $order_status, $charge->amount, $display_name, $message, $detail, null, false, $this->context->customer->secure_key
            );

            
            $new_order = new Order((int) $this->currentOrder);
            if (Validate::isLoadedObject($new_order)) {
                $payment = $new_order->getOrderPaymentCollection();
                if (isset($payment[0])) {
                    $payment[0]->transaction_id = pSQL($charge->id);
                    $payment[0]->card_holder = pSQL($charge->card->holder_name);
                    $payment[0]->card_number = pSQL($charge->card->card_number);
                    $payment[0]->card_brand = pSQL(strtoupper($charge->card->brand));
                    $payment[0]->card_expiration = pSQL($charge->card->expiration_month.'/'.$charge->card->expiration_year);
                    $payment[0]->save();
                }
            }            

            /** Store the transaction details */            
            $fee = $charge->fee ? ($charge->fee->amount + $charge->fee->tax) : 0;
            Db::getInstance()->insert('openpay_transaction', array(
                'type' => pSQL($payment_method),
                'id_cart' => (int) $this->context->cart->id,
                'id_order' => (int) $this->currentOrder,
                'id_transaction' => pSQL($charge->id),
                'amount' => (float) $charge->amount,
                'status' => pSQL($charge->status == 'completed' ? 'paid' : 'unpaid'),
                'fee' => (float) $fee,
                'currency' => pSQL($charge->currency),
                'mode' => pSQL(Configuration::get('OPENPAY_MODE') == 'true' ? 'live' : 'test'),
                'date_add' => date('Y-m-d H:i:s'),
                'due_date' => date('Y-m-d H:i:s'),
                'barcode' => null,
                'reference' => null,
                'clabe' => null
            ));
            

            // Update order_id from Openpay Charge
            $this->updateOpenpayCharge($charge->id, $this->currentOrder, $new_order->reference);

            /** Redirect the user to the order confirmation page history */
            $redirect = __PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.(int) $this->context->cart->id.
                    '&id_module='.(int) $this->id.
                    '&id_order='.(int) $this->currentOrder.
                    '&key='.$this->context->customer->secure_key;

            Logger::addLog($redirect, 1, null, null, null, true);

            Tools::redirect($redirect);

            /** catch the openpay error */
        } catch (Exception $e) {
            // Si tiene habilitada la autenticación selectiva
            if ($charge_type == 'auth' && $e->getCode() == '3005') {
                $charge_request['use_3d_secure'] = true;
                $charge_request['redirect_url'] = _PS_BASE_URL_.__PS_BASE_URI__.'module/openpayprestashop/confirm';
                
                $openpay_customer = $this->getOpenpayCustomer($this->context->cookie->id_customer);
                $charge = $openpay_customer->charges->create($charge_request);
                
                Tools::redirect($charge->payment_method->url);
            }
            
            if (class_exists('Logger')) {
                Logger::addLog($this->l('Openpay - Payment transaction failed').' '.$e->getMessage(), 1, null, 'Cart', (int) $this->context->cart->id, true);
                Logger::addLog($this->l('Openpay - Payment transaction failed').' '.$e->getTraceAsString(), 4, $e->getCode(), 'Cart', (int) $this->context->cart->id, true);
            }

            $this->error($e);
            //$this->context->cookie->__set('openpay_error', $e->getMessage());
            
            Tools::redirect('index.php?controller=order&step=1');
        }
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
            'name' => $this->l('La extensión PHP cURL debe estar habilitada en tu servidor.'),
            'result' => extension_loaded('curl'));

        if (Configuration::get('OPENPAY_MODE')) {
            $tests['ssl'] = array(
                'name' => $this->l('SSL debe estar habilitado (antes de pasar a modo productivo)'),
                'result' => Configuration::get('PS_SSL_ENABLED') || (!empty($_SERVER['HTTPS']) && Tools::strtolower($_SERVER['HTTPS']) != 'off'));
        }

        $tests['php54'] = array(
            'name' => $this->l('Tu servidor debe de contar con PHP 5.4 o posterior.'),
            'result' => version_compare(PHP_VERSION, '5.4.0', '>='));

        $tests['configuration'] = array(
            'name' => $this->l('Deberás de registrarte en Openpay y configurar las credenciales (ID, llave privada y llave pública.)'),
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

            $months = is_array(Tools::getValue('months_interest_free')) ? implode(',', Tools::getValue('months_interest_free')) : array();
            
            $configuration_values = array(
                'OPENPAY_MODE' => Tools::getValue('openpay_mode'),
                'OPENPAY_MERCHANT_ID_TEST' => trim(Tools::getValue('openpay_merchant_id_test')),
                'OPENPAY_MERCHANT_ID_LIVE' => trim(Tools::getValue('openpay_merchant_id_live')),
                'OPENPAY_PUBLIC_KEY_TEST' => trim(Tools::getValue('openpay_public_key_test')),
                'OPENPAY_PUBLIC_KEY_LIVE' => trim(Tools::getValue('openpay_public_key_live')),
                'OPENPAY_PRIVATE_KEY_TEST' => trim(Tools::getValue('openpay_private_key_test')),
                'OPENPAY_PRIVATE_KEY_LIVE' => trim(Tools::getValue('openpay_private_key_live')),
                'OPENPAY_MONTHS_INTEREST_FREE' => $months,
                'OPENPAY_CHARGE_TYPE' => Tools::getValue('openpay_charge_type'),
                'USE_CARD_POINTS' => Tools::getValue('use_card_points'),
                'OPENPAY_SAVE_CC' => Tools::getValue('save_cc'),
                'OPENPAY_CAPTURE' => Tools::getValue('capture')
            );

            foreach ($configuration_values as $configuration_key => $configuration_value) {
                Configuration::updateValue($configuration_key, $configuration_value);
            }

            $mode = Configuration::get('OPENPAY_MODE') ? 'LIVE' : 'TEST';

            if (!$this->getMerchantInfo()) {
                $errors[] = 'Openpay keys are incorrect.';
                Configuration::deleteByName('OPENPAY_PUBLIC_KEY_'.$mode);
                Configuration::deleteByName('OPENPAY_MERCHANT_ID_'.$mode);
                Configuration::deleteByName('OPENPAY_PRIVATE_KEY_'.$mode);
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
            $validation_title = $this->l('Todos los chequeos fueron exitosos, ahora puedes comenzar a utilizar Openpay.');
        } else {
            $validation_title = $this->l('Al menos un problema fue encontrado para poder comenzar a utilizar Openpay. Por favor resuelve los problemas y refresca esta página.');
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
                        'OPENPAY_CHARGE_TYPE',
                        'USE_CARD_POINTS',
                        'OPENPAY_CAPTURE',
                        'OPENPAY_SAVE_CC'
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
        $mode = Configuration::get('OPENPAY_MODE') == 'true' ? 'live' : 'test';

        $openpay_customer = Db::getInstance()->getRow('
            SELECT openpay_customer_id
            FROM '._DB_PREFIX_.'openpay_customer
            WHERE id_customer = '.(int) $customer_id.' AND (mode = "'.$mode.'" OR mode IS NULL)'
        );

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

                Db::getInstance()->insert('openpay_customer', array(
                    'openpay_customer_id' => pSQL($customer_openpay->id),
                    'id_customer' => (int) $this->context->cookie->id_customer,
                    'date_add' => date('Y-m-d H:i:s'),
                    'mode' => pSQL($mode)
                ));

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
        $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        $openpay = Openpay::getInstance($id, $pk);
        Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));

        $customer = $openpay->customers->get($customer_id);
        return $customer;
    }

    public function createOpenpayCustomer($customer_data) {
        $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        $openpay = Openpay::getInstance($id, $pk);
        Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));

        try {
            $customer = $openpay->customers->add($customer_data);
            return $customer;
        } catch (Exception $e) {
            $this->error($e);
        }
    }

//    public function createOpenpayCharge($customer, $charge_request) {
//        $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
//        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');
//
//        Openpay::getInstance($id, $pk);
//        Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));
//
//        try {
//            $charge = $customer->charges->create($charge_request);
//            return $charge;
//        } catch (Exception $e) {                        
//            if (Configuration::get('OPENPAY_CHARGE_TYPE') == 'auth' && $e->getCode() == '3005') {
//                
//            }
//            
//            return $this->error($e);
//        }
//    }

    public function updateOpenpayCharge($transaction_id, $order_id, $reference) {
        $customer = $this->getOpenpayCustomer($this->context->cookie->id_customer);
        $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        Openpay::getInstance($id, $pk);
        Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));

        try {
            $charge = $customer->charges->get($transaction_id);
            $charge->update(array('order_id' => $order_id, 'description' => 'PrestaShop ORDER #'.$reference));
            return true;
        } catch (Exception $e) {
            $this->error($e);
        }
    }

    public function getOpenpayCharge($customer, $transaction_id) {
        $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        Openpay::getInstance($id, $pk);
        Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));

        try {
            $charge = $customer->charges->get($transaction_id);
            return $charge;
        } catch (Exception $e) {
            $this->error($e);
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
    
    private function getCreditCardList() {
        if (!(bool)$this->context->customer->isLogged()) {                    
            return array(array('value' => 'new', 'name' => 'Nueva tarjeta'));
        }        
        
        $mode = Configuration::get('OPENPAY_MODE') == 'true' ? 'live' : 'test';
        $openpay_customer = Db::getInstance()->getRow('
            SELECT openpay_customer_id
            FROM '._DB_PREFIX_.'openpay_customer
            WHERE id_customer = '.(int) $this->context->cookie->id_customer.' AND (mode = "'.$mode.'" OR mode IS NULL)'
        );
                
        
        if (!isset($openpay_customer['openpay_customer_id'])) {
            return array(array('value' => 'new', 'name' => 'Nueva tarjeta'));
        } 
        
        $list = array(array('value' => 'new', 'name' => 'Nueva tarjeta'));        
        try {            
            $openpay = $this->getOpenpayInstance();            
            $customer = $openpay->customers->get($openpay_customer['openpay_customer_id']);
            
            $cards = $this->getCreditCards($customer);            
            foreach ($cards as $card) {                
                array_push($list, array('value' => $card->id, 'name' => strtoupper($card->brand).' '.$card->card_number));
            }
            
            return $list;            
        } catch (Exception $e) {
            Logger::addLog('#getCreditCardList() => '.$e->getMessage(), 3, null, 'Cart', (int) $this->context->cart->id, true);            
            return $list;
        }        
    }
    
    private function getCreditCards($customer) {        
        try {
            return $customer->cards->getList(array(                
                'offset' => 0,
                'limit' => 10
            ));            
        } catch (Exception $e) {
            Logger::addLog('#getCreditCards() => '.$e->getMessage(), 3, null, 'Cart', (int) $this->context->cart->id, true);            
            throw $e;
        }        
    }
    
    private function createCreditCard($customer, $data) {
        try {
            return $customer->cards->add($data);            
        } catch (Exception $e) {
            Logger::addLog('#createCreditCard() => '.$e->getMessage(), 3, null, 'Cart', (int) $this->context->cart->id, true);                    
            throw $e;
        }        
    }

    public function error($e, $backend = false) {
        switch ($e->getErrorCode()) {
            /* ERRORES GENERALES */
            case '1000':
            case '1004':
            case '1005':
                $msg = $this->l('Servicio no disponible.');
                break;

            /* ERRORES TARJETA */
            case '3001':                
                $msg = $this->l('La tarjeta fue declinada.');
                break;
            
            case '3004':
                $msg = $this->l('La tarjeta ha sido identificada como una tarjeta robada.');
                break;
            
            case '3005':
                $msg = $this->l('La tarjeta ha sido rechazada por el sistema antifraudes.');
                break;
            
            case '3007':
                $msg = $this->l('La tarjeta fue declinada.');
                break;

            case '3002':
                $msg = $this->l('La tarjeta ha expirado.');
                break;

            case '3003':
                $msg = $this->l('La tarjeta no tiene fondos suficientes.');
                break;

            case '3006':
                $msg = $this->l('La operación no esta permitida para este cliente o esta transacción.');
                break;

            case '3008':
                $msg = $this->l('La tarjeta no es soportada en transacciones en línea..');
                break;

            case '3009':
                $msg = $this->l('La tarjeta fue reportada como perdida.');
                break;

            case '3010':
                $msg = $this->l('El banco ha restringido la tarjeta.');
                break;

            case '3011':
                $msg = $this->l('El banco ha solicitado que la tarjeta sea retenida. Contacte al banco.');
                break;

            case '3012':
                $msg = $this->l('Se requiere solicitar al banco autorización para realizar este pago.');
                break;

            default: /* Demás errores 400 */
                $msg = $e->getMessage();
                break;
        }

        $error = 'ERROR '.$e->getErrorCode().'. '.$msg;
        $this->context->cookie->__set('openpay_error', $error);
        $this->context->cookie->__set('openpay_error_code', $e->getErrorCode());

        if ($backend) {
            return Tools::jsonDecode(Tools::jsonEncode(array('error' => $e->getErrorCode(), 'msg' => $error)), false);
        } else {
            if (class_exists('Logger')) {
                Logger::addLog($this->l('#Openpay - Payment transaction failed').' '.$error, 1, null, 'Cart', (int) $this->context->cart->id, true);
            }
            return false;
            //Tools::redirect('index.php?controller=order&step=1');      
        }
    }

    public function isNullOrEmpty($string) {
        return (!isset($string) || trim($string) === '');
    }
    
    protected function getOpenpayInstance() {        
        $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        $openpay = Openpay::getInstance($id, $pk);
        Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));
        
        return $openpay;
    }

}
