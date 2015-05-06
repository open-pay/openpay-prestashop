<?php

/*
 * 2007-2013 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2013 PrestaShop SA
 *  @version  Release: $Revision: 7040 $
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *  Further Updates by @olliemcfarlane
 */

if (!defined('_PS_VERSION_'))
    exit;

class OpenpaySpei extends PaymentModule {

    protected $backward = false;

    public function __construct() {
        
        if (!class_exists('Openpay', false)) {
            include_once(dirname(__FILE__) . '/lib/Openpay.php');
        }
        
        
        
        $this->name = 'openpayspei';
        $this->tab = 'payments_gateways';
        $this->version = '1.0';
        $this->author = 'Openpay';
        parent::__construct();
        $this->displayName = $this->l('Openpay SPEI');
        $this->description = $this->l('Accept payments via SPEI');
        $this->confirmUninstall = $this->l('Warning: all the Openpay transaction details  in your database will be deleted. Are you sure you want uninstall this module?');

        /* Backward compatibility */
        if (_PS_VERSION_ < '1.5') {
            $this->backward_error = $this->l('In order to work properly in PrestaShop v1.4, the Openpay module requires the backward compatibility module at least v0.3.') . '<br />' .
                    $this->l('You can download this module for free here: http://addons.prestashop.com/en/modules-prestashop/6222-backwardcompatibility.html');
            if (file_exists(_PS_MODULE_DIR_ . 'backwardcompatibility/backward_compatibility/backward.php')) {
                include(_PS_MODULE_DIR_ . 'backwardcompatibility/backward_compatibility/backward.php');
                $this->backward = true;
            } else
                $this->warning = $this->backward_error;
        } else {
            $this->backward = true;
        }
    }

    /**
     * Openpay's module installation
     *
     * @return boolean Install result
     */
    public function install() {
        if (!$this->backward && _PS_VERSION_ < 1.5) {
            echo '<div class="error">' . Tools::safeOutput($this->backward_error) . '</div>';
            return false;
        }

        /* For 1.4.3 and less compatibility */
        $updateConfig = array(
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

        foreach ($updateConfig as $u => $v){
            if (!Configuration::get($u) || (int) Configuration::get($u) < 1) {
                if (defined('_' . $u . '_') && (int) constant('_' . $u . '_') > 0)
                    Configuration::updateValue($u, constant('_' . $u . '_'));
                else
                    Configuration::updateValue($u, $v);
            }
        }
        
        $ret = parent::install() && $this->_createPendingState() && 
                $this->registerHook('adminOrder') && 
                $this->registerHook('payment') && 
                $this->registerHook('header') &&
                $this->registerHook('backOfficeHeader') && 
                $this->registerHook('paymentReturn') && 
                Configuration::updateValue('OPENPAY_SPEI_MODE', 0) && 
                $this->installDb();        
        
        /* The hook "displayMobileHeader" has been introduced in v1.5.x - Called separately to fail silently if the hook does not exist */
        $this->registerHook('displayMobileHeader');
        
        return $ret;
    }

    private function _createPendingState() {

        $languages = Language::getLanguages();
        $names = array();
        foreach ($languages as $lang)
            $names[$lang['id_lang']] = 'En espera de pago';
        
        $state = new OrderState();
        $state->name = $names;
        $state->color = '#4169E1';
        $state->send_email = true;
        $state->module_name = 'openpayspei';
        $templ = array();
        foreach ($languages as $lang)
            $templ[$lang['id_lang']] = 'openpayspei';
        $state->template = $templ;

        if ($state->save()) {
            Configuration::updateValue('waiting_cash_payment', $state->id);
        } else{
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
        return Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'openpay_customer` (
                    `id_openpay_customer` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `openpay_customer_id` varchar(32) NOT NULL,
                    `id_customer` int(10) unsigned NOT NULL,
                    `date_add` datetime NOT NULL,
                    PRIMARY KEY (`id_openpay_customer`),
                    KEY `id_customer` (`id_customer`)) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1') &&
               Db::getInstance()->Execute('
                CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'openpay_spei_transaction` (
                    `id_openpay_transaction` int(11) NOT NULL AUTO_INCREMENT,
                    `type` enum(\'payment\',\'refund\') NOT NULL, 
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
                    `clabe` varchar(50) NOT NULL,
                    `reference` varchar(50) NOT NULL,
                    PRIMARY KEY (`id_openpay_transaction`), 
                    KEY `idx_transaction` (`type`,`id_order`,`status`)) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1'
                );
    }

    /**
     * Openpay's module uninstallation (Configuration values, database tables...)
     *
     * @return boolean Uninstall result
     */
    public function uninstall() {
        return parent::uninstall() && 
                Configuration::deleteByName('OPENPAY_SPEI_PUBLIC_KEY_TEST') &&
                Configuration::deleteByName('OPENPAY_SPEI_PUBLIC_KEY_LIVE') &&
                Configuration::deleteByName('OPENPAY_SPEI_MERCHANT_ID_LIVE') &&
                Configuration::deleteByName('OPENPAY_SPEI_MERCHANT_ID_TEST') &&
                Configuration::deleteByName('OPENPAY_SPEI_MODE') &&
                Configuration::deleteByName('OPENPAY_SPEI_PRIVATE_KEY_TEST') && 
                Configuration::deleteByName('OPENPAY_SPEI_PRIVATE_KEY_LIVE') &&
                Configuration::deleteByName('OPENPAY_SPEI_DEADLINE_TEST') &&
                Configuration::deleteByName('OPENPAY_SPEI_DEADLINE_LIVE') &&
                Db::getInstance()->Execute('DROP TABLE `' . _DB_PREFIX_ . 'openpay_spei_transaction`');
    }

    public function hookDisplayMobileHeader() {
        return $this->hookHeader();
    }

    /**
     * Load Javascripts and CSS related to the OPENPAY'S module
     * @return string Content
     */
    public function hookHeader() {
        
        /* If 1.4 and no backward, then leave */
        if (!$this->backward)
            return;

        /* Continue only if we are in the checkout process */
        if (Tools::getValue('controller') != 'order-opc' && (!($_SERVER['PHP_SELF'] == __PS_BASE_URI__ . 'order.php' || $_SERVER['PHP_SELF'] == __PS_BASE_URI__ . 'order-opc.php' || Tools::getValue('controller') == 'order' || Tools::getValue('controller') == 'orderopc' || Tools::getValue('step') == 3)))
            return;
        
        if (!$this->checkSettings())
            return;
        
        $this->context->controller->addCSS($this->_path . 'css/openpay-prestashop.css');
    }

    /**
     * @return string Openpay's Smarty template content
     */
    public function hookPayment($params) {
        /* If not compatible abort */
        if (!$this->backward)
            return;
        
        if (!empty($this->context->cookie->openpay_error)) {
            $this->smarty->assign('openpay_spei_error', $this->context->cookie->openpay_error);
            $this->context->cookie->__set('openpay_spei_error', null);
        }
        
        $this->smarty->assign('validation_url', (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . 'index.php?process=validation&fc=module&module=openpayspei&controller=default');
        
        $this->smarty->assign('spei', $this->_path . 'img/spei.png');
        
        $this->smarty->assign('openpay_ps_version', _PS_VERSION_);
        
        return $this->display(__FILE__, './views/templates/hook/payment.tpl');
        
    }

    public function hookAdminOrder($params) {
        if (version_compare(_PS_VERSION_, '1.6', '<'))
            return;

        $id_order = (int) ($params['id_order']);
        if (Db::getInstance()->getValue('SELECT module FROM ' . _DB_PREFIX_ . 'orders WHERE id_order = ' . (int) $id_order) == $this->name) {
            $openpay_transaction_details = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'openpay_spei_transaction WHERE id_order = ' . (int) $id_order . ' AND type = \'payment\'');
            $output = '<div class="col-lg-12"><div class="panel"><h3><i class="icon-money"></i> ' . $this->l('Openpay Payment Details') . '</h3>';
            $output .= '
				<ul class="nav nav-tabs" id="tabOpenpay">
					<li class="active">
						<a href="#openpay_details">
							<i></i> ' . $this->l('Details') . ' <span class="badge">' . $openpay_transaction_details['id_transaction'] . '</span>
						</a>
					</li>
				</ul>';
            $output .= '
				<div class="tab-content panel">
					<div class="tab-pane active" id="openpay_details">';

            if (isset($openpay_transaction_details['id_transaction'])) {
                $output .= '
                         <p>
                         <b>' . $this->l('Status:') . '</b> <span style="font-weight: bold; color: ' . ($openpay_transaction_details['status'] == 'paid' ? 'green;">' . $this->l('Paid') : '#CC0000;">' . $this->l('Unpaid')) . '</span><br>' .
                        '<b>' . $this->l('Amount:') . '</b> ' . Tools::displayPrice($openpay_transaction_details['amount']) . '<br>' .
                        '<b>' . $this->l('Processed on:') . '</b> ' . Tools::safeOutput($openpay_transaction_details['date_add']) . '<br>' .
                        '<b>' . $this->l('Mode:') . '</b> <span style="font-weight: bold; color: ' . ($openpay_transaction_details['mode'] == 'live' ? 'green;">' . $this->l('Live') : '#CC0000;">' . $this->l('Test (No payment has been processed until you enable the "Live" mode)')) . '</span><br>'.
                        '<b>'.  $this->l('Clabe: ').'</b>'.Tools::safeOutput($openpay_transaction_details['clabe']).' <br>'.
                        '<b>'.  $this->l('Referencia: ').'</b>'.Tools::safeOutput($openpay_transaction_details['reference']).' <br> </p>';
            } else
                $output .= '<b style="color: #CC0000;">' . $this->l('Warning:') . '</b> ' . $this->l('The customer paid using Openpay and an error occured (check details at the bottom of this page)');

            $output .= '</div>';
            $output .= '</div></div></div>';
            return $output;
        }
    }

    /**
     * Display the info in the admin
     *
     * @return string admin content
     */
    public function hookBackOfficeHeader() {
        //do not use this function for PS v1.6+
        if (version_compare(_PS_VERSION_, 1.6, '>='))
            return;

        /* If 1.4 and no backward, then leave */
        if (!$this->backward)
            return;

        if (!Tools::getIsset('vieworder') || !Tools::getIsset('id_order'))
            return;

        $id_order = (int) Tools::getValue('id_order');

        if (Db::getInstance()->getValue('SELECT module FROM ' . _DB_PREFIX_ . 'orders WHERE id_order = ' . (int) $id_order) == $this->name) {
            $openpay_transaction_details = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'openpay_spei_transaction WHERE id_order = ' . (int) $id_order . ' AND type = \'payment\' AND status = \'paid\'');
            $output = '
			<script type="text/javascript">
				$(document).ready(function() {
					$(\'<fieldset' . (_PS_VERSION_ < 1.5 ? ' style="width: 400px;"' : '') . '><legend><img src="../img/admin/money.gif" alt="" />' . $this->l('Openpay Payment Details') . '</legend>';

            if (isset($openpay_transaction_details['id_transaction'])){
                $output .= $this->l('Openpay Transaction ID:') . ' ' . Tools::safeOutput($openpay_transaction_details['id_transaction']) . '<br /><br />' .
                        $this->l('Status:') . ' <span style="font-weight: bold; color: ' . ($openpay_transaction_details['status'] == 'paid' ? 'green;">' . $this->l('Paid') : '#CC0000;">' . $this->l('Unpaid')) . '</span><br />' .
                        $this->l('Amount:') . ' ' . Tools::displayPrice($openpay_transaction_details['amount']) . '<br />' .
                        $this->l('Processed on:') . ' ' . Tools::safeOutput($openpay_transaction_details['date_add']) . '<br />' .
                        $this->l('Processing Fee:') . ' ' . Tools::displayPrice($openpay_transaction_details['fee']) . '<br /><br />' .
                        $this->l('Mode:') . ' <span style="font-weight: bold; color: ' . ($openpay_transaction_details['mode'] == 'live' ? 'green;">' . $this->l('Live') : '#CC0000;">' . $this->l('Test (You will not receive any payment, until you enable the "Live" mode)')) . '</span>';
            }else{
                $output .= '<b style="color: #CC0000;">' . $this->l('Warning:') . '</b> ' . $this->l('The customer paid using Openpay and an error occured (check details at the bottom of this page)');
            }
            $order = new Order((int) $id_order);
            $currency = new Currency($order->id_currency);
            return $output;
        }
    }

    /**
     * Display a confirmation message after an order has been placed
     * To Do: add more complete information to show to user
     * @param array Hook parameters
     */
    public function hookPaymentReturn($params) {
        if (!$this->active)
            return;

        $state = $params['objOrder']->getCurrentState();
        $msg = $params['objOrder']->getFirstMessage();
        $id_order = (int) $params['objOrder']->id;

        if ($params['objOrder'] && Validate::isLoadedObject($params['objOrder'])){
            $openpay_transaction_details = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'openpay_spei_transaction WHERE id_order = ' . (int) $id_order . ' AND type = \'payment\'');
        }
        $this->smarty->assign(
                'openpay_order', 
                array(
                    'clabe' => $openpay_transaction_details['clabe'], 
                    'reference' => $openpay_transaction_details['reference'], 
                    'amount' => $openpay_transaction_details['amount'], 
                    'currency'=> $openpay_transaction_details['currency'],
                    'shop_name'=> Configuration::get('PS_SHOP_NAME'),
                    'due_date' => $this->getLongGlobalDateFormat($openpay_transaction_details['due_date']),
                    'email' => Configuration::get('PS_SHOP_EMAIL'),
                    'phone' => Configuration::get('BLOCKCONTACTINFOS_PHONE')
                )
        );
        $currentOrderStatus = (int) $params['objOrder']->getCurrentState();
        $this->smarty->assign('order_pending', false);

        
        $this->context->controller->addCSS($this->_path . 'css/openpay-prestashop.css');
        $this->context->controller->addCSS($this->_path . 'css/receipt.css');
        $this->context->controller->addCSS($this->_path . 'css/print.css', 'print');
        
        return $this->display(__FILE__, './views/templates/hook/order-confirmation.tpl');
    }

    /**
     * Process a payment, where the magic happens
     *
     */
    public function processPayment() {
        /* If not compatible then abort */
        if (!$this->backward)
            return;
        
        //to do: add details and line_items
        try {
            
            $openpay_customer = $this->getOpenpayCustomer($this->context->cookie->id_customer);            
            $cart = $this->context->cart;
            $deadline = (Configuration::get('OPENPAY_SPEI_MODE')) ? Configuration::get('OPENPAY_SPEI_DEADLINE_LIVE') : Configuration::get('OPENPAY_SPEI_DEADLINE_TEST');
            $due_date = date('Y-m-d\TH:i:s', strtotime('+ '.$deadline.' hours'));
            $charge_request = array(
                'method' => 'bank_account',
                'currency' => $this->context->currency->iso_code,
                'amount' => $cart->getOrderTotal(),
                'description' => $this->l('PrestaShop Customer ID:').' ' . (int) $this->context->cookie->id_customer . ' - ' . $this->l('PrestaShop Cart ID:') . ' ' . (int) $cart->id,
                'order_id' => (int) $cart->id,
                'due_date' => $due_date
            );
            
            $result_json = $this->createOpenpayCharge($openpay_customer, $charge_request);
            
            $clabe = $result_json->payment_method->clabe;
            $reference = $result_json->payment_method->name;
            $order_status = (int) Configuration::get('waiting_cash_payment');
            $message = $this->l('Openpay Transaction Details:') . "\n\n" .
                    $this->l('Openpay Transaction ID:') . ' ' . $result_json->id . "\n" .
                    $this->l('Processed on:') . ' ' . date('Y-m-d H:i:s') . "\n" .
                    $this->l('Due date:') . ' ' . date('Y-m-d H:i:s', strtotime('+ '.$deadline.' hours')) . "\n" .
                    $this->l('Amount:') . ' ' . ($result_json->amount) . "\n" .
                    $this->l('Currency:') . ' ' . Tools::strtoupper($result_json->currency) . "\n" .
                    $this->l('Clabe:').' '.$clabe."\n".
                    $this->l('Reference:').' '.$reference."\n".
                    $this->l('Mode:') . ' ' . (Configuration::get('OPENPAY_SPEI_MODE') == 'true' ? $this->l('Live') : $this->l('Test')) . "\n";
            
            $this->copyMailTemplate();
            
            $mail_detail = '<br /><span style="color:#333"><strong>Banco:</strong></span> STP<br /><span style="color:#333"><strong>CLABE:</strong></span> '.$clabe.'<br><span style="color:#333"><strong>Referencia:</strong></span> '.$reference;
            
            $checkout = Module::getInstanceByName('openpayspei');
            $checkout->extra_mail_vars = array('{detail}' => (string) $mail_detail);
            
            /* Create the PrestaShop order in database */
            $this->validateOrder((int) $this->context->cart->id, (int) $order_status, ($result_json->amount), $this->displayName, $message, array(), null, false, $this->context->customer->secure_key);
            
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

            if ($result_json->id){
                
                    Db::getInstance()->insert('openpay_spei_transaction', array(
                        'type'          => 'payment',
                        'id_cart'       => (int)$this->context->cart->id,
                        'id_order'      => (int)$this->currentOrder,
                        'id_transaction'=> pSQL($result_json->id),
                        'amount'        => $result_json->amount,
                        'status'        => ($result_json->status == 'completed' ? 'paid' : 'unpaid'),
                        'fee'           => ($result_json->amount * 0.01),
                        'currency'      => pSQL($result_json->currency),
                        'mode'          => (Configuration::get('OPENPAY_SPEI_MODE') == 'true' ? 'live' : 'test'),
                        'date_add'      => date("Y-m-d H:i:s"),
                        'due_date'      => date('Y-m-d H:i:s', strtotime('+ '.$deadline.' hours')),
                        'clabe'       => $clabe,
                        'reference'     => $reference,
                    ));
                
            }
            
            /* Redirect the user to the order confirmation page / history */
            if (_PS_VERSION_ < 1.5)
                $redirect = __PS_BASE_URI__ . 'order-confirmation.php?id_cart=' . (int) $this->context->cart->id . '&id_module=' . (int) $this->id . '&id_order=' . (int) $this->currentOrder . '&key=' . $this->context->customer->secure_key;
            else
                $redirect = __PS_BASE_URI__ . 'index.php?controller=order-confirmation&id_cart=' . (int) $this->context->cart->id . '&id_module=' . (int) $this->id . '&id_order=' . (int) $this->currentOrder . '&key=' . $this->context->customer->secure_key;

            header('Location: ' . $redirect);
            exit;            
            
        } catch (Exception $e) {
            $message = $e->getMessage();
            
            if (class_exists('Logger')){
                Logger::addLog($this->l('Openpay - Payment transaction failed') . ' ' . $message, 1, null, 'Cart', (int) $this->context->cart->id, true);
            }
            
            $this->context->cookie->__set("openpay_error", $e->getMessage());
            $controller = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc.php' : 'order.php';
            header('Location: ' . $this->context->link->getPageLink($controller) . (strpos($controller, '?') !== false ? '&' : '?') . 'step=3#openpay_error');
            exit;
            
        }
    }

    /**
     * Check settings requirements to make sure the Openpay's module will work properly
     *
     * @return boolean Check result
     */
    public function checkSettings($mode = 'global') {
        if (Configuration::get('OPENPAY_SPEI_MODE')){
            return Configuration::get('OPENPAY_SPEI_PUBLIC_KEY_LIVE') != '' && Configuration::get('OPENPAY_SPEI_PRIVATE_KEY_LIVE') != '';
        }else{
            return Configuration::get('OPENPAY_SPEI_PUBLIC_KEY_TEST') != '' && Configuration::get('OPENPAY_SPEI_PRIVATE_KEY_TEST') != '';
        }
    }

    /**
     * Check technical requirements to make sure the Openpay's module will work properly
     *
     * @return array Requirements tests results
     */
    public function checkRequirements() {
        $tests = array('result' => true);
        $tests['curl'] = array('name' => $this->l('La extensión PHP CURL tiene que estar activada en su servidor'), 'result' => extension_loaded('curl'));
        if (Configuration::get('OPENPAY_SPEI_MODE'))
            $tests['ssl'] = array('name' => $this->l('SSL tiene que ser habilitado en su tienda online ( antes de entrar en el modo activo)'), 'result' => Configuration::get('PS_SSL_ENABLED') || (!empty($_SERVER['HTTPS']) && Tools::strtolower($_SERVER['HTTPS']) != 'off'));
        $tests['php52'] = array('name' => $this->l('Su servidor tiene que soportar minimo PHP 5.2'), 'result' => version_compare(PHP_VERSION, '5.2.0', '>='));
        $tests['configuration'] = array('name' => $this->l('Usted tiene que registarse en Openpay y configurar la cuenta en el módulo (Merchant ID , llave privada, llave pública)'), 'result' => $this->checkSettings());

        if (_PS_VERSION_ < 1.5) {
            $tests['backward'] = array('name' => $this->l('You are using the backward compatibility module'), 'result' => $this->backward, 'resolution' => $this->backward_error);
            $tmp = Module::getInstanceByName('mobile_theme');
            if ($tmp && isset($tmp->version) && !version_compare($tmp->version, '0.3.8', '>='))
                $tests['mobile_version'] = array('name' => $this->l('You are currently using the default mobile template, the minimum version required is v0.3.8') . ' (v' . $tmp->version . ' ' . $this->l('detected') . ' - <a target="_blank" href="http://addons.prestashop.com/en/mobile-iphone/6165-prestashop-mobile-template.html">' . $this->l('Please Upgrade') . '</a>)', 'result' => version_compare($tmp->version, '0.3.8', '>='));
        }

        foreach ($tests as $k => $test)
            if ($k != 'result' && !$test['result'])
                $tests['result'] = false;

        return $tests;
    }

    /**
     * Display the Back-office interface of the Openpay's module
     *
     * @return string HTML/JS Content
     */
    public function getContent() {
        $output = '';
        if (version_compare(_PS_VERSION_, '1.5', '>')){
            $this->context->controller->addJQueryPlugin('fancybox');
        }else{
            $output .= '
			<script type="text/javascript" src="' . __PS_BASE_URI__ . 'js/jquery/jquery.fancybox-1.3.4.js"></script>
		  	<link type="text/css" rel="stylesheet" href="' . __PS_BASE_URI__ . 'css/jquery.fancybox-1.3.4.css" />';
        }

        $requirements = $this->checkRequirements();
        $errors = array();
        /* Update Configuration Values when settings are updated */
        if (Tools::isSubmit('SubmitOpenpay')) {
            if (strpos(Tools::getValue('openpay_public_key_test'), "sk") !== false || strpos(Tools::getValue('openpay_public_key_live'), "sk") !== false) {
                $errors[] = "You've entered your private key in the public key field!";
            }
            if (empty($errors)) {
                $configuration_values = array(
                    'OPENPAY_SPEI_MODE' => Tools::getValue('openpay_mode'),
                    'OPENPAY_SPEI_MERCHANT_ID_TEST' => trim(Tools::getValue('openpay_merchant_id_test')),
                    'OPENPAY_SPEI_MERCHANT_ID_LIVE' => trim(Tools::getValue('openpay_merchant_id_live')),
                    'OPENPAY_SPEI_PUBLIC_KEY_TEST' => trim(Tools::getValue('openpay_public_key_test')),
                    'OPENPAY_SPEI_PUBLIC_KEY_LIVE' => trim(Tools::getValue('openpay_public_key_live')),
                    'OPENPAY_SPEI_PRIVATE_KEY_TEST' => trim(Tools::getValue('openpay_private_key_test')),
                    'OPENPAY_SPEI_PRIVATE_KEY_LIVE' => trim(Tools::getValue('openpay_private_key_live')),
                    'OPENPAY_SPEI_DEADLINE_TEST' => trim(Tools::getValue('openpay_deadline_test')),
                    'OPENPAY_SPEI_DEADLINE_LIVE' => trim(Tools::getValue('openpay_deadline_live'))
                );

                foreach ($configuration_values as $configuration_key => $configuration_value){
                    Configuration::updateValue($configuration_key, $configuration_value);
                }
                
                $webhook_data = array(
                    'url' => (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . 'modules/openpayspei/notification.php',
                    'event_types' => array("verification","charge.succeeded","charge.created","charge.cancelled","charge.failed","payout.created","payout.succeeded","payout.failed","spei.received","chargeback.created","chargeback.rejected","chargeback.accepted")
                );
                
                $this->createWebhook($webhook_data);
                
                
            }
        }



        $output .= '
		<script type="text/javascript">
			/* Fancybox */
			$(\'a.openpay-module-video-btn\').live(\'click\', function(){
			    $.fancybox({\'type\' : \'iframe\', \'href\' : this.href.replace(new RegExp(\'watch\\?v=\', \'i\'), \'embed/\') + \'?rel=0&autoplay=1\',
			    \'swf\': {\'allowfullscreen\':\'true\', \'wmode\':\'transparent\'}, \'overlayShow\' : true, \'centerOnScroll\' : true,
			    \'speedIn\' : 100, \'speedOut\' : 50, \'width\' : 853, \'height\' : 480 });
			    return false;
			});
		</script>
		<link href="' . $this->_path . 'css/openpay-prestashop-admin.css" rel="stylesheet" type="text/css" media="all" />
		<div class="openpay-module-wrapper">
			' . (Tools::isSubmit('SubmitOpenpay') ? '<div class="conf confirmation">' . $this->l('Settings successfully saved') . '<img src="http://www.prestashop.com/modules/' . $this->name . '.png?api_user=' . urlencode($_SERVER['HTTP_HOST']) . '" style="display: none;" /></div>' : '') . '
			<div class="openpay-module-header">
				<a href="http://www.openpay.mx" target="_blank" rel="external"><img src="' . $this->_path . 'img/openpay-logo.png" alt="openpay" class="openpay-logo" /></a>
				<span class="openpay-module-intro">' . $this->l('Comienza a recibir pagos vía SPEI hoy mismo con Openpay.') . '</span>
				<a href="https://sandbox-dashboard.openpay.mx/login/register" rel="external" target="_blank" class="openpay-module-create-btn"><span>' . $this->l('Crea una Cuenta') . '</span></a>
			</div>
			<div class="openpay-module-wrap">
				<div class="openpay-module-col1 floatRight">
					<div class="openpay-module-wrap-video">
						<h3>' . $this->l('Panel de administración') . '</h3>
						<p>' . $this->l('Contamos con un panel de administración donde podrás visualizar las diferentes transacciones que procese tu negocio.') . '</p>
						<a target="_blank" href="http://www.openpay.mx"><img src="' . $this->_path . 'img/openpay-dashboard.png" alt="openpay dashboard" class="openpay-dashboard" /></a>
					</div>
				</div>
				<div class="openpay-module-col2">
					<div class="openpay-module-col1inner">
						<h3>' . $this->l('Amarás usar Openpay') . '</h3>
						<ul>
							<li>' . $this->l('Sin renta mensual') . '</li>
							<li>' . $this->l('Sin costos de integración') . '</li>
							<li>' . $this->l('Sin comisiones de configuración') . '</li>
							<li>' . $this->l('Sin plazos forzosos') . '</li>
							<li>' . $this->l('Sin cargos ocultos ni letras chiquitas') . '</li>
						</ul>
					</div>
					<div class="openpay-module-col1inner floatRight">
						<h3>' . $this->l('Recepción de pagos') . '</h3>
						<p><strong>' . $this->l('$8 por cargo exitoso') . '</strong></p>
						<p>' . $this->l('Openpay ofrece una estructura sencilla de costos todo incluido para todo tipo de soluciones e-commerce o m-commerce.') . '</p>
                                                <p>' . $this->l('Todas nuestras comisiones son mas IVA.') . '</p>
					</div>
					<div class="openpay-module-col2inner">
						<h3>' . $this->l('Desde la comodidad de tu hogar y con tu banca electrónica') . '</h3>
						<p><img src="' . $this->_path . 'img/spei.png" alt="openpay" class="openpay-cc" /></p>
					</div>
				</div>
			</div>
			<fieldset>
				<legend><img src="' . $this->_path . 'img/checks-icon.gif" alt="" />' . $this->l('Chequeos Técnicos') . '</legend>
				<div class="' . ($requirements['result'] ? 'conf">' . $this->l('¡Buenas noticias! Todos los chequeos fueron exitosos. Ahora puedes configurar el módulo y comenzar a usar Openpay.') :
                        'warn">' . $this->l('Desafortunadamente hay al menos un problema que te impdide usar Opepay. Favor de reparar el problema y recarga la página.')) . '</div>
				<table cellspacing="0" cellpadding="0" class="openpay-technical">';
        
        foreach ($requirements as $k => $requirement){
            if ($k != 'result'){
                $output .= '
                    <tr>
                            <td><img src="../img/admin/' . ($requirement['result'] ? 'ok' : 'forbbiden') . '.gif" alt="" /></td>
                            <td>' . $requirement['name'] . (!$requirement['result'] && isset($requirement['resolution']) ? '<br />' . Tools::safeOutput($requirement['resolution'], true) : '') . '</td>
                    </tr>';
            }
        }
        
        $output .= '
				</table>
			</fieldset>
		<br />';

        
        if (!empty($errors)) {
            $output .= '
			<fieldset>
				<legend>Errors</legend>
				<table cellspacing="0" cellpadding="0" class="openpay-technical">
						<tbody>';
            
            foreach ($errors as $error) {
                $output .= '<tr>
                                <td><img src="../img/admin/forbbiden.gif" alt=""></td>
                                <td>' . $error . '</td>
                            </tr>';
            }
            
            $output .= '
				</tbody></table>
			</fieldset>';
            
        }



        /* If 1.4 and no backward, then leave */
        if (!$this->backward)
            return $output;


        $output .= '
		<form action="' . Tools::safeOutput($_SERVER['REQUEST_URI']) . '" method="post">
			<fieldset class="openpay-settings">
				<legend><img src="' . $this->_path . 'img/technical-icon.gif" alt="" />' . $this->l('Configuraciones') . '</legend>
				<label>' . $this->l('Modo') . '</label>
				<input type="radio" name="openpay_mode" value="0"' . (!Configuration::get('OPENPAY_SPEI_MODE') ? ' checked="checked"' : '') . ' /> Sandbox
				<input type="radio" name="openpay_mode" value="1"' . (Configuration::get('OPENPAY_SPEI_MODE') ? ' checked="checked"' : '') . ' /> Producción
				<br /><br />
				<table cellspacing="0" cellpadding="0" class="openpay-settings">
					
					
					<tr>
						<td align="center" valign="middle" colspan="2">
							<table cellspacing="0" cellpadding="0" class="innerTable">
                                                                <tr>
									<td align="right" valign="middle">' . $this->l('Merchant ID sandbox') . '</td>
									<td align="left" valign="middle"><input type="text" name="openpay_merchant_id_test" value="' . Tools::safeOutput(Configuration::get('OPENPAY_SPEI_MERCHANT_ID_TEST')) . '" /></td>
									<td width="15"></td>
									<td width="15" class="vertBorder"></td>
									<td align="left" valign="middle">' . $this->l('Merchant ID producción') . '</td>
									<td align="left" valign="middle"><input type="text" name="openpay_merchant_id_live" value="' . Tools::safeOutput(Configuration::get('OPENPAY_SPEI_MERCHANT_ID_LIVE')) . '" /></td>
								</tr>
								<tr>
									<td align="right" valign="middle">' . $this->l('Llave pública sandbox') . '</td>
									<td align="left" valign="middle"><input type="text" name="openpay_public_key_test" value="' . Tools::safeOutput(Configuration::get('OPENPAY_SPEI_PUBLIC_KEY_TEST')) . '" /></td>
									<td width="15"></td>
									<td width="15" class="vertBorder"></td>
									<td align="left" valign="middle">' . $this->l('Llave pública producción') . '</td>
									<td align="left" valign="middle"><input type="text" name="openpay_public_key_live" value="' . Tools::safeOutput(Configuration::get('OPENPAY_SPEI_PUBLIC_KEY_LIVE')) . '" /></td>
								</tr>
								<tr>
									<td align="right" valign="middle">' . $this->l('Llave privada sandbox') . '</td>
									<td align="left" valign="middle"><input type="password" name="openpay_private_key_test" value="' . Tools::safeOutput(Configuration::get('OPENPAY_SPEI_PRIVATE_KEY_TEST')) . '" /></td>
									<td width="15"></td>
									<td width="15" class="vertBorder"></td>
									<td align="left" valign="middle">' . $this->l('Llave privada producción') . '</td>
									<td align="left" valign="middle"><input type="password" name="openpay_private_key_live" value="' . Tools::safeOutput(Configuration::get('OPENPAY_SPEI_PRIVATE_KEY_LIVE')) . '" /></td>
								</tr>
                                                                <tr>
									<td align="right" valign="middle">' . $this->l('Fecha límite de pago (horas)') . '</td>
									<td align="left" valign="middle"><input type="text" name="openpay_deadline_test" value="' . Tools::safeOutput(Configuration::get('OPENPAY_SPEI_DEADLINE_TEST')) . '" /></td>
									<td width="15"></td>
									<td width="15" class="vertBorder"></td>
									<td align="left" valign="middle">' . $this->l('Fecha límite de pago (horas)') . '</td>
									<td align="left" valign="middle"><input type="text" name="openpay_deadline_live" value="' . Tools::safeOutput(Configuration::get('OPENPAY_SPEI_DEADLINE_LIVE')) . '" /></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="td-noborder save"><input type="submit" class="button" name="SubmitOpenpay" value="' . $this->l('Guardar configuración') . '" /></td>
					</tr>
				</table>
			</fieldset>
			<div class="clear"></div>
		</div>
		</form>
		<script type="text/javascript">
			function updateOpenpaySettings()
			{
				if ($(\'input:radio[name=openpay_mode]:checked\').val() == 1)
					$(\'fieldset.openpay-cc-numbers\').hide();
				else
					$(\'fieldset.openpay-cc-numbers\').show(1000);

				if ($(\'input:radio[name=openpay_save_tokens]:checked\').val() == 1)
					$(\'tr.openpay_save_token_tr\').show(1000);
				else
					$(\'tr.openpay_save_token_tr\').hide();
			}

			$(\'input:radio[name=openpay_mode]\').click(function() { updateOpenpaySettings(); });
			$(\'input:radio[name=openpay_save_tokens]\').click(function() { updateOpenpaySettings(); });
			$(document).ready(function() { updateOpenpaySettings(); });
		</script>';

        return $output;
    }
    
    
    public function getOpenpayCustomer($customer_id){
        
        $cart = $this->context->cart;
        $customer = new Customer((int) $cart->id_customer);
        
        
        $openpay_customer = Db::getInstance()->getRow('
                    SELECT openpay_customer_id
                    FROM ' . _DB_PREFIX_ . 'openpay_customer
                    WHERE id_customer = ' . (int) $customer_id);
        
        if (!isset($openpay_customer['openpay_customer_id'])) {
                try {
                    
                    $customer_data = array(
                        'name' => $customer->firstname,
                        'last_name' => $customer->lastname,
                        'email' => $customer->email,
                        'requires_account' => false
                    );
                    
                    $customer_openpay = $this->createOpenpayCustomer($customer_data);
                    
                    
                    Db::getInstance()->Execute('
                        INSERT INTO ' . _DB_PREFIX_ . 'openpay_customer (id_openpay_customer, openpay_customer_id, id_customer, date_add)
                        VALUES (NULL, \'' . pSQL($customer_openpay->id) . '\', ' . (int) $this->context->cookie->id_customer . ', NOW())');
                    
                    
                    return $customer_openpay;
                } catch (Exception $e) {
                    if (class_exists('Logger'))
                        Logger::addLog($this->l('Openpay - Invalid Credit Card'), 1, null, 'Cart', (int) $this->context->cart->id, true);
                }
        }else{
            
            return $this->getCustomer($openpay_customer['openpay_customer_id']);
            
        }
        
    }
    
    
    public function getCustomer($customer_id){
        
        $pk = Configuration::get('OPENPAY_SPEI_MODE') ? Configuration::get('OPENPAY_SPEI_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_SPEI_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_SPEI_MODE') ? Configuration::get('OPENPAY_SPEI_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_SPEI_MERCHANT_ID_TEST');
        
        $openpay = Openpay::getInstance($id, $pk);
        Openpay::setProductionMode(Configuration::get('OPENPAY_SPEI_MODE'));   
        
        $customer = $openpay->customers->get($customer_id);
        return $customer;
    }
    
    public function createOpenpayCustomer($customer_data){
        
        $pk = Configuration::get('OPENPAY_SPEI_MODE') ? Configuration::get('OPENPAY_SPEI_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_SPEI_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_SPEI_MODE') ? Configuration::get('OPENPAY_SPEI_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_SPEI_MERCHANT_ID_TEST');
        
        $openpay = Openpay::getInstance($id, $pk);
        Openpay::setProductionMode(Configuration::get('OPENPAY_SPEI_MODE'));   
        
        try {

            $customer = $openpay->customers->add($customer_data);
            return $customer;
            
        } catch (OpenpayApiTransactionError $e) {
            $this->error($e);
        } catch (OpenpayApiRequestError $e) {
            $this->error($e);
        } catch (OpenpayApiConnectionError $e) {
            $this->error($e);
        } catch (OpenpayApiAuthError $e) {
            $this->error($e);
        } catch (OpenpayApiError $e) {
            $this->error($e);
        } catch (Exception $e) {
            $this->error($e);
        }
    }
    
    
    public function createOpenpayCharge($customer, $chargeRequest) {
        
        $pk = Configuration::get('OPENPAY_SPEI_MODE') ? Configuration::get('OPENPAY_SPEI_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_SPEI_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_SPEI_MODE') ? Configuration::get('OPENPAY_SPEI_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_SPEI_MERCHANT_ID_TEST');
        
        $openpay = Openpay::getInstance($id, $pk);
        Openpay::setProductionMode(Configuration::get('OPENPAY_SPEI_MODE'));
        
        try {

            $charge = $customer->charges->create($chargeRequest);
            return $charge;
            
        } catch (OpenpayApiTransactionError $e) {
            $this->error($e);
        } catch (OpenpayApiRequestError $e) {
            $this->error($e);
        } catch (OpenpayApiConnectionError $e) {
            $this->error($e);
        } catch (OpenpayApiAuthError $e) {
            $this->error($e);
        } catch (OpenpayApiError $e) {
            $this->error($e);
        } catch (Exception $e) {
            $this->error($e);
        }
    }
    
    public function createWebhook($webhook_data) {
        
        $pk = Configuration::get('OPENPAY_SPEI_MODE') ? Configuration::get('OPENPAY_SPEI_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_SPEI_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_SPEI_MODE') ? Configuration::get('OPENPAY_SPEI_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_SPEI_MERCHANT_ID_TEST');
        
        $openpay = Openpay::getInstance($id, $pk);
        Openpay::setProductionMode(Configuration::get('OPENPAY_SPEI_MODE'));
        
        try {

            $webhook = $openpay->webhooks->add($webhook_data);
            return $webhook;
            
        } catch (OpenpayApiTransactionError $e) {
            $this->error($e);
        } catch (OpenpayApiRequestError $e) {
            $this->error($e);
        } catch (OpenpayApiConnectionError $e) {
            $this->error($e);
        } catch (OpenpayApiAuthError $e) {
            $this->error($e);
        } catch (OpenpayApiError $e) {
            $this->error($e);
        } catch (Exception $e) {
            $this->error($e);
        }
    }
    
    
    public function error($e) {
        
        //el webhook ya existe
        if($e->getErrorCode() == 6001){
            return;
        }
        
        
        switch ($e->getErrorCode()){
            
            //ERRORES GENERALES
            case "1000":
                $msg = "Servicio no disponible.";
                break;
            
            case "1001":
                $msg = "Los campos no tienen el formato correcto, o la petición no tiene campos que son requeridos.";
                break;
            
            case "1004":
                $msg = "Servicio no disponible.";
                break;
            
            case "1005":
                $msg = "Servicio no disponible.";
                break;
            
            //ERRORES ALMACENAMIENTO
            case "2004":
                $msg = "El dígito verificador del número de tarjeta es inválido de acuerdo al algoritmo Luhn.";
                break;    

            case "2005":
                $msg = "La fecha de expiración de la tarjeta es anterior a la fecha actual.";
                break;

            case "2006":
                $msg = "El código de seguridad de la tarjeta (CVV2) no fue proporcionado.";
                break;
            
            //ERRORES TARJETA
            case "3001":
                $msg = "La tarjeta fue rechazada.";
                break;
            
            case "3002":
                $msg = "La tarjeta ha expirado.";
                break;
            
            case "3003":
                $msg = "La tarjeta no tiene fondos suficientes.";
                break;
            
            case "3004":
                $msg = "La tarjeta ha sido identificada como una tarjeta robada.";
                break;
            
            case "3005":
                $msg = "La tarjeta ha sido rechazada por el sistema antifraudes.";
                break;
            
            case "3006":
                $msg = "La operación no esta permitida para este cliente o esta transacción.";
                break;
            
            case "3007":
                $msg = "Deprecado. La tarjeta fue declinada.";
                break;
            
            case "3008":
                $msg = "La tarjeta no es soportada en transacciones en línea.";
                break;
            
            case "3009":
                $msg = "La tarjeta fue reportada como perdida.";
                break;
            
            case "3010":
                $msg = "El banco ha restringido la tarjeta.";
                break;
            
            case "3011":
                $msg = "El banco ha solicitado que la tarjeta sea retenida. Contacte al banco.";
                break;
            
            case "3012":
                $msg = "Se requiere solicitar al banco autorización para realizar este pago.";
                break;
            
            default: //Demás errores 400 
                $msg = "La petición no pudo ser procesada.";
                break;
            
        }
        
        $error = 'ERROR '.$e->getErrorCode().'. '.$msg;
        throw new Exception($error);
        
    }
    
    public function copyMailTemplate(){
        
        $lang = $this->context->language->iso_code;
        
        $html_file_origin = _PS_MODULE_DIR_ .$this->name. '/mails/es/openpayspei.html';
        $html_file_destination = dirname(__FILE__).'/../../mails/'.$lang.'/openpayspei.html';
        if(!copy($html_file_origin, $html_file_destination)){
            throw new Exception;
        }

        $txt_file_origin = _PS_MODULE_DIR_ .$this->name .'/mails/es/openpayspei.txt';
        $txt_file_destination = dirname(__FILE__).'/../../mails/'.$lang.'/openpayspei.txt';
        if(!copy($txt_file_origin, $txt_file_destination)){
            throw new Exception;
        }
    }
    
    public function getLongGlobalDateFormat($input)
    {
            $time = strtotime($input);

            $string_month = $this->getLongStringForMonth(date('n', $time));

            // Formato "12 de Julio de 2014, a las 6:36 PM"
            return date('j', $time).' de '.$string_month.' de '.date('Y', $time).', a las '.date('g:i A',$time);
    }
    
    
    public function getLongStringForMonth($month_number)
    {
            $months_array = array(
                    1 => 'Enero',
                    2 => 'Febrero',
                    3 => 'Marzo',
                    4 => 'Abril',
                    5 => 'Mayo',
                    6 => 'Junio',
                    7 => 'Julio',
                    8 => 'Agosto',
                    9 => 'Septiembre',
                    10 => 'Octubre',
                    11 => 'Noviembre',
                    12 => 'Diciembre'
            );

            return isset($months_array[$month_number]) ? $months_array[$month_number] : '';
    }
    

}
