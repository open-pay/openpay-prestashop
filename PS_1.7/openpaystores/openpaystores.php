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

class OpenpayStores extends PaymentModule
{

    private $error = array();
    private $validation = array();
    private $limited_currencies = array('MXN');

    public function __construct()
    {
        if (!class_exists('Openpay', false)) {
            include_once(dirname(__FILE__).'/lib/Openpay.php');
        }

        $this->sandbox_url_mx = 'https://sandbox-api.openpay.mx/v1';
        $this->url_mx = 'https://api.openpay.mx/v1';
        $this->sandbox_url_co = 'https://sandbox-api.openpay.co/v1';
        $this->url_co = 'https://api.openpay.co/v1';

        $this->name = 'openpaystores';
        $this->tab = 'payments_gateways';
        $this->version = '4.0.3';
        $this->author = 'Openpay SA de CV';
        $this->module_key = '23c1a97b2718ec0aec28bb9b3b2fc6d5';

        parent::__construct();
        $warning = 'Are you sure you want uninstall this module?';
        $this->displayName = $this->l('Openpay Offline');
        $this->description = $this->l('Acepta pagos en efectivo con Openpay');
        $this->confirmUninstall = $this->l($warning);
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);  
        $this->max_amount_allowed_mx = 9999;
        $this->max_amount_allowed_co = 720000;
    }

    /**
     * Openpay's module installation
     *
     * @return boolean Install result
     */
    public function install()
    {
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
                $this->registerHook('paymentOptions') &&                
                $this->registerHook('displayHeader') &&
                $this->registerHook('displayPaymentTop') &&                
                $this->registerHook('paymentReturn') &&
                $this->registerHook('displayMobileHeader') &&
                $this->registerHook('actionEmailSendBefore') &&                
                Configuration::updateValue('OPENPAY_MODE', 0) && 
                Configuration::updateValue('OPENPAY_COUNTRY', 'MX') &&               
                Configuration::updateValue('OPENPAY_WEBHOOK_ID_TEST', null) &&
                Configuration::updateValue('OPENPAY_WEBHOOK_ID_LIVE', null) &&
                Configuration::updateValue('OPENPAY_WEBHOOK_USER', Tools::substr(md5(uniqid(rand(), true)), 0, 10)) &&
                Configuration::updateValue('OPENPAY_WEBHOOK_PASSWORD', Tools::substr(md5(uniqid(rand(), true)), 0, 10)) &&                
                Configuration::updateValue('OPENPAY_WEBHOOK_URL', _PS_BASE_URL_.__PS_BASE_URI__) &&
                Configuration::updateValue('OPENPAY_STORE_IVA', 0) &&                
                $this->installDb();

        return $ret;
    }

    private function createPendingState()
    {
        $state = new OrderState();
        $languages = Language::getLanguages();
        $names = array();

        foreach ($languages as $lang) {
            $names[$lang['id_lang']] = $this->l('Awaiting payment');
        }

        $state->name = $names;
        $state->color = '#4169E1';
        $state->send_email = true;
        $state->module_name = 'openpaystores';
        $templ = array();

        foreach ($languages as $lang) {
            $templ[$lang['id_lang']] = 'openpaystores';
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
    public function installDb()
    {

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
    public function uninstall()
    {
        /* Eliminar webhooks */
        if (Configuration::get('OPENPAY_WEBHOOK_ID_TEST') != null) {
            $this->deleteWebhook(Configuration::get('OPENPAY_WEBHOOK_ID_TEST'), false);
        }

        if (Configuration::get('OPENPAY_WEBHOOK_ID_LIVE') != null) {
            $this->deleteWebhook(Configuration::get('OPENPAY_WEBHOOK_ID_LIVE'), true);
        }

        return parent::uninstall() &&                
            Configuration::deleteByName('OPENPAY_DEADLINE_STORES') &&                
            Configuration::deleteByName('OPENPAY_WEBHOOK_ID_TEST') &&
            Configuration::deleteByName('OPENPAY_WEBHOOK_ID_LIVE') &&
            Configuration::deleteByName('OPENPAY_WEBHOOK_USER') &&
            Configuration::deleteByName('OPENPAY_WEBHOOK_PASSWORD') &&                
            Configuration::deleteByName('OPENPAY_WEBHOOOK_URL');
    }
    
    public function hookActionEmailSendBefore($params) {        
        if ($params['template'] == 'openpaystores') {
            Logger::addLog('#hookActionEmailSendBefore INPUT => '.json_encode($params), 1, null, null, null, true);            
            
            $order_id = $params['templateVars']['{id_order}'];
            $pdf_url = $params['templateVars']['pdf_url'];                        
            
            Logger::addLog('#hookActionEmailSendBefore id_order => '.$order_id, 1, null, null, null, true);              
            Logger::addLog('#hookActionEmailSendBefore $reference => '.$pdf_url, 1, null, null, null, true);  
                                                
            $pdf_file = $this->handlePdfAttachment($pdf_url, $order_id);            
            $params['fileAttachment'] = $pdf_file;                                  
            $params['subject'] = 'Pendiente de pago';
            
            Logger::addLog('#hookActionEmailSendBefore OUTPUT => '.json_encode($params), 1, null, null, null, true);    
        }
        
        return $params;
    }
    
    private function handlePdfAttachment($url, $order_id) {
        //$upload_dir = _PS_UPLOAD_DIR_;                
        $pdf_content = file_get_contents($url);
        $pdf_file_name = "payment_receipt_".$order_id.".pdf";        
        
        $attachment = array();        
        $attachment['content'] = $pdf_content;
        $attachment['name'] = $pdf_file_name;
        $attachment['mime'] = 'application/pdf'; 
        
        return $attachment;
    }
    
    /**
     * Hook to the top a payment page
     *
     * @param array $params Hook parameters
     * @return string Hook HTML
     */
    public function hookDisplayPaymentTop($params)
    {                
        return;
    }
    

    public function hookDisplayMobileHeader()
    {
        return $this->hookHeader();
    }

    /**
     * Load Javascripts and CSS related to the Openpay's module
     * Only loaded during the checkout process
     *
     * @return string HTML/JS Content
     */
    public function hookHeader($params)
    {
        if (!$this->active) {
            return;
        }
        
        if (Tools::getValue('module') === 'onepagecheckoutps' ||
            Tools::getValue('controller') === 'order-opc' ||
            Tools::getValue('controller') === 'orderopc' ||
            Tools::getValue('controller') === 'order') {
            
            $this->context->controller->addCSS($this->_path.'views/css/openpay-prestashop.css');                      
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
    public function hookPaymentOptions($params)
    {          
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            return false;
        }
        /** @var Cart $cart */
        $cart = $params['cart'];
        if (!$this->active) {
            return false;
        }
        
        if (!$this->checkCurrency()) {
            return false;
        }

        $country = Configuration::get('OPENPAY_COUNTRY');
        $max_amount_allowed = ($country === 'MX') ? $this->max_amount_allowed_mx : $this->max_amount_allowed_co;
        //floatval
        if($cart->getOrderTotal() > $max_amount_allowed) {
            return false;
        }

        
        $this->context->smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'total' => $cart->getOrderTotal(),
            'module_dir' => $this->_path,
            'country' => $country
        ));                

        $externalOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $externalOption->setCallToActionText($this->l('Pago en efectivo'))   
            ->setModuleName($this->name)            
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), Tools::usingSecureMode()))           
            ->setAdditionalInformation($this->context->smarty->fetch('module:openpaystores/views/templates/hook/store.tpl'));

        return array($externalOption);
    }
    

    /**
     * Display a confirmation message after an order has been placed
     *
     * @param array Hook parameters
     */
    public function hookPaymentReturn($params)
    {
                        
        if (!isset($params['order']) || ($params['order']->module != $this->name)) {
            Logger::addLog('orden no existe', 1, null, null, null, true);
            return false;
        }
        
        /** @var Order $order */
        $order = $params['order'];

        $id_order = (int) $order->id;
        $reference = isset($order->reference) ? $order->reference : '#'.sprintf('%06d', $id_order);                         
        $address = Db::getInstance()->getRow('
            SELECT *
            FROM '._DB_PREFIX_.'address
            WHERE id_address = '.(int) $order->id_address_delivery
        );

        $this->smarty->assign('openpay_order', array('reference' => $reference, 'valid' => $order->valid));
        $this->context->controller->addCSS($this->_path.'views/css/openpay-prestashop.css');
        $this->smarty->assign('order_pending', false);

        $query = 'SELECT * FROM '._DB_PREFIX_.'openpay_transaction WHERE id_order = '.(int) $id_order.'';
        $transaction = Db::getInstance()->getRow($query);

        $pdf_url = $this->getPdfUrl($transaction['reference']);
        $country = Configuration::get('OPENPAY_COUNTRY');

        if($country == 'MX'){
            $data = array(
                'pdf_url' => $pdf_url,
                'show_map' => Configuration::get('OPENPAY_SHOW_MAP') == '1' ? true : false,
                'postal_code' => $address['postcode'],
                'country' => $country
            );
        }else{
            $address = $address['address1'].' '.$address['address2'].', '.$address['city'];
            $data = array(
                'pdf_url' => $pdf_url,
                'show_map' => Configuration::get('OPENPAY_SHOW_MAP') == '1' ? true : false,
                'address' => $address,
                'country' => $country
            );
        }
        
        $this->smarty->assign('openpay_order', $data);

        $template = './views/templates/hook/store_order_confirmation.tpl';
      
        Logger::addLog($template, 1, null, null, null, true);
        return $this->display(__FILE__, $template);
    }
    
    private function getPdfUrl ($reference) {
        $country = Configuration::get('OPENPAY_COUNTRY');
        $pdf_url_base_mx = Configuration::get('OPENPAY_MODE') ? 'https://dashboard.openpay.mx/paynet-pdf' : 'https://sandbox-dashboard.openpay.mx/paynet-pdf';
        $pdf_url_base_co = Configuration::get('OPENPAY_MODE') ? 'https://dashboard.openpay.co/paynet-pdf' : 'https://sandbox-dashboard.openpay.co/paynet-pdf';
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');        
        $pdf_url_base = $country === 'MX' ? $pdf_url_base_mx : $pdf_url_base_co;
        return  $pdf_url_base.'/'.$id.'/'.$reference;
    }

    /**
     * Process a payment
     *
     * @param string $token Openpay Transaction ID (token)
     */
    public function processPayment()
    {
        if (!$this->active) {
            return;
        }
        
        $clabe = null;
        $mail_detail = '';
        $message_aux = '';
        $payment_method = 'store';

        try {
            $display_name = $this->l('Openpay cash payment');            
            $result_json = $this->offlinePayment($payment_method);
            $order_status = (int) Configuration::get('waiting_cash_payment');

            $barcode_url = $result_json->payment_method->barcode_url;
            $reference = $result_json->payment_method->reference;
            

            $mail_detail = '<br/><img src="'.$barcode_url.'" /><br/><span style="color:#333"><strong>Referencia:</strong></span>'.$reference;

            $message_aux = $this->l('Reference:').' '.$reference."\n";

            $message = $this->l('Openpay Transaction Details:')."\n\n".
                    $this->l('Transaction ID:').' '.$result_json->id."\n".
                    $this->l('Payment method:').' '.Tools::ucfirst($payment_method)."\n".
                    $message_aux.
                    $this->l('Amount:').' $'.number_format($result_json->amount, 2).' '.Tools::strtoupper($result_json->currency)."\n".
                    $this->l('Status:').' '.($result_json->status == 'completed' ? $this->l('Paid') : $this->l('Unpaid'))."\n".
                    $this->l('Processed on:').' '.date('Y-m-d H:i:s')."\n".
                    $this->l('Mode:').' '.(Configuration::get('OPENPAY_MODE') ? $this->l('Live') : $this->l('Test'))."\n";

            /* Create the PrestaShop order in database */
            $detail = array('{detail}' => $mail_detail, 'pdf_url' => $this->getPdfUrl($reference));
            $this->validateOrder(
                (int) $this->context->cart->id,
                (int) $order_status,
                $result_json->amount,
                $display_name,
                $message,
                $detail,
                null,
                false,
                $this->context->customer->secure_key
            );

            
            $new_order = new Order((int) $this->currentOrder);
            if (Validate::isLoadedObject($new_order)) {
                $payment = $new_order->getOrderPaymentCollection();
                if (isset($payment[0])) {
                    $payment[0]->transaction_id = pSQL($result_json->id);
                    $payment[0]->save();
                }
            }
            

            $fee = 0;            
            if($result_json->due_date){
                $due_date = $result_json->due_date;
            }else{
                $due_date = date('Y-m-d H:i:s');
            }
            
            $due_date = date('Y-m-d H:i:s');
            if($result_json->due_date){
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
            $this->updateOpenpayCharge($result_json->id, $this->currentOrder, $new_order->reference);
            
            /** Redirect the user to the order confirmation page history */
            $redirect = __PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.(int) $this->context->cart->id.                    
                    '&id_module='.(int) $this->id.
                    '&id_order='.(int) $this->currentOrder.
                    '&key='.$this->context->customer->secure_key;

            Logger::addLog($redirect, 1, null, null, null, true);
            
            Tools::redirect($redirect);
            exit;

            /** catch the openpay error */
        } catch (Exception $e) {
            $message = $e->getMessage();

            if (class_exists('Logger')) {
                Logger::addLog($this->l('Openpay - Payment transaction failed').' '.$message, 1, null, 'Cart', (int) $this->context->cart->id, true);
                Logger::addLog($this->l('Openpay - Payment transaction failed').' '.$e->getTraceAsString(), 4, null, 'Cart', (int) $this->context->cart->id, true);
            }

            $this->context->cookie->__set('openpay_error', $e->getMessage());

            Tools::redirect('index.php?controller=order&step=1');    
        }
    }
    

    public function offlinePayment($payment_method)
    {
        $country = Configuration::get('OPENPAY_COUNTRY'); 
        $openpay_customer = $this->getOpenpayCustomer($this->context->cookie->id_customer);
        $cart = $this->context->cart;
        $deadline = 720;

        if (Configuration::get('OPENPAY_DEADLINE_STORES') && Configuration::get('OPENPAY_DEADLINE_STORES') > 0) {
            $deadline = Configuration::get('OPENPAY_DEADLINE_STORES');
        }

        $due_date = date('Y-m-d\TH:i:s', strtotime('+ '.$deadline.' hours'));
        $amount = number_format(floatval($cart->getOrderTotal()), 2, '.', '');

        $charge_request = array(
            'method' => $payment_method,
            'currency' => $this->context->currency->iso_code,
            'amount' => $amount,
            'description' => $this->l('PrestaShop Cart ID:').' '.(int) $cart->id,            
            'due_date' => $due_date
        );

        if ($country === 'CO') {
            $charge_request['iva'] = Configuration::get('OPENPAY_STORE_IVA');
        }

        $result_json = $this->createOpenpayCharge($openpay_customer, $charge_request);

        return $result_json;
    }

    /**
     * Check settings requirements to make sure the Openpay's module will work properly
     *
     * @return boolean Check result
     */
    public function checkSettings()
    {
        if (Configuration::get('OPENPAY_MODE')) {
            return Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') != '';
        } else {
            return Configuration::get('OPENPAY_PRIVATE_KEY_TEST') != '';
        }
    }

    /**
     * Check technical requirements to make sure the Openpay's module will work properly
     *
     * @return array Requirements tests results
     */
    public function checkRequirements()
    {
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
    public function getContent()
    {
        $this->context->controller->addCSS(array($this->_path.'views/css/openpay-prestashop-admin.css'));

        $errors = array();

        /** Update Configuration Values when settings are updated */
        if (Tools::isSubmit('SubmitOpenpay')) {

            $configuration_values = array(
                'OPENPAY_MODE' => Tools::getValue('openpay_mode'),
                'OPENPAY_COUNTRY' => Tools::getValue('openpay_country'),
                'OPENPAY_MERCHANT_ID_TEST' => trim(Tools::getValue('openpay_merchant_id_test')),
                'OPENPAY_MERCHANT_ID_LIVE' => trim(Tools::getValue('openpay_merchant_id_live')),
                'OPENPAY_PRIVATE_KEY_TEST' => trim(Tools::getValue('openpay_private_key_test')),
                'OPENPAY_PRIVATE_KEY_LIVE' => trim(Tools::getValue('openpay_private_key_live')),
                'OPENPAY_DEADLINE_STORES' => trim(Tools::getValue('openpay_deadline_stores')),                
                'OPENPAY_WEBHOOK_URL' => trim(Tools::getValue('openpay_webhook_url')),
                'OPENPAY_SHOW_MAP' => Tools::getValue('show_map'),
                'OPENPAY_STORE_IVA' => Tools::getValue('openpay_store_iva')
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
                Configuration::deleteByName('OPENPAY_MERCHANT_ID_'.$mode);
                Configuration::deleteByName('OPENPAY_PRIVATE_KEY_'.$mode);
                Configuration::deleteByName('OPENPAY_WEBHOOK_ID_'.$mode);
                Configuration::deleteByName('OPENPAY_DEADLINE_STORES');                
                Configuration::deleteByName('OPENPAY_SHOW_MAP');                
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

        $dashboard_openpay = '';
        if(Configuration::get('OPENPAY_MODE')){
            $dashboard_openpay = Configuration::get('OPENPAY_COUNTRY') == 'MX' ? 'https://dashboard.openpay.mx' : 'https://dashboard.openpay.co';
        }else{
            $dashboard_openpay = Configuration::get('OPENPAY_COUNTRY') == 'MX' ? 'https://sandbox-dashboard.openpay.mx' : 'https://sandbox-dashboard.openpay.co';
        }
        
        $this->context->smarty->assign(array(
            'receipt' => $this->_path.'views/img/recibo.png',
            'openpay_form_link' => $_SERVER['REQUEST_URI'],
            'openpay_configuration' => Configuration::getMultiple(
                array(
                    'OPENPAY_MODE',
                    'OPENPAY_COUNTRY',
                    'OPENPAY_MERCHANT_ID_TEST',
                    'OPENPAY_MERCHANT_ID_LIVE',
                    'OPENPAY_PRIVATE_KEY_TEST',
                    'OPENPAY_PRIVATE_KEY_LIVE',
                    'OPENPAY_DEADLINE_STORES',                                        
                    'OPENPAY_WEBHOOK_URL',
                    'OPENPAY_SHOW_MAP',
                    'OPENPAY_STORE_IVA'
                )
            ),
            'openpay_ssl' => Configuration::get('PS_SSL_ENABLED'),
            'openpay_validation' => $this->validation,
            'openpay_error' => (empty($this->error) ? false : $this->error),
            'openpay_validation_title' => $validation_title,
            'dashboard_openpay' => $dashboard_openpay            
        ));
        
        return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
    }

    /**
     * Check availables currencies for module
     * 
     * @return boolean
     */
    public function checkCurrency()
    {   
        $country = Configuration::get('OPENPAY_COUNTRY'); 
        if($country === 'MX'){
            return in_array($this->context->currency->iso_code, $this->limited_currencies);
        }elseif($country === 'CO') {
            return $this->context->currency->iso_code === 'COP' ? true : false;
        }
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function getOpenpayCustomer($customer_id)
    {
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

                $address['state'] = $state['name'];

                $customer_data = array(
                    'requires_account' => false,
                    'name' => $customer->firstname,
                    'last_name' => $customer->lastname,
                    'email' => $customer->email,
                    'phone_number' => $address['phone'],
                );

                if ($this->validateAddress($address)) {
                    $customer_data = $this->formatAddress($customer_data, $address);
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
                    Logger::addLog('Openpay - Can not create Openpay Customer => '.$e->getMessage(), 1, null, 'Cart', (int) $this->context->cart->id, true);
                }
            }
        } else {
            return $this->getCustomer($openpay_customer['openpay_customer_id']);
        }
    }

    private function formatAddress($customer_data, $address) {
        $country = Configuration::get('OPENPAY_COUNTRY');
        if ($country === 'MX') {
            $customer_data['address'] = array(
                'line1' => substr($address['address1'], 0, 200),
                'line2' => substr($address['address2'], 0, 50),
                'state' => $address['state'],
                'city' => $address['city'],
                'postal_code' => $address['postcode'],
                'country_code' => $country
            );
        } else if ($country === 'CO') {
            $customer_data['customer_address'] = array(
                'department' => $address['state'],
                'city' => $address['city'],
                'additional' => substr($address['address1'], 0, 200).' '.substr($address['address2'], 0, 50)
            );
        }
        
        return $customer_data;
    }

    public function validateAddress($address) {
        $country = Configuration::get('OPENPAY_COUNTRY');
        if ($country == 'MX' && !$this->isNullOrEmpty($address['address1']) && !$this->isNullOrEmpty($address['city']) && !$this->isNullOrEmpty($address['postcode']) && !$this->isNullOrEmpty($address['state'])) {
            return true;
        } else if ($country == 'CO' && !$this->isNullOrEmpty($address['address1']) && !$this->isNullOrEmpty($address['city']) && !$this->isNullOrEmpty($address['state'])) {
            return true;
        }
        return false;  
    }

    public function getCustomer($customer_id)
    {
        $country = Configuration::get('OPENPAY_COUNTRY');
        $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        $openpay = Openpay::getInstance($id, $pk, $country);
        Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));

        $customer = $openpay->customers->get($customer_id);
        return $customer;
    }

    public function createOpenpayCustomer($customer_data)
    {
        $country = Configuration::get('OPENPAY_COUNTRY');
        $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        $openpay = Openpay::getInstance($id, $pk, $country);
        Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));

        $userAgent = "Openpay-PS17".$country."/v2";
        Openpay::setUserAgent($userAgent);

        try {
            $customer = $openpay->customers->add($customer_data);
            return $customer;
        } catch (Exception $e) {
            $this->error($e);
        }
    }

    public function createOpenpayCharge($customer, $charge_request)
    {
        $country = Configuration::get('OPENPAY_COUNTRY');
        $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        Openpay::getInstance($id, $pk, $country);
        Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));

        $userAgent = "Openpay-PS17".$country."/v2";
        Openpay::setUserAgent($userAgent);

        try {
            $charge = $customer->charges->create($charge_request);
            return $charge;
        } catch (Exception $e) {
            $this->error($e);
        }
    }
    
    public function updateOpenpayCharge($transaction_id, $order_id, $reference)
    {
        $country = Configuration::get('OPENPAY_COUNTRY');
        $customer = $this->getOpenpayCustomer($this->context->cookie->id_customer);
        $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        Openpay::getInstance($id, $pk, $country);
        Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));

        $userAgent = "Openpay-PS17".$country."/v2";
        Openpay::setUserAgent($userAgent);
        
        try {
            $charge = $customer->charges->get($transaction_id);            
            $charge->update(array('order_id' => $order_id, 'description' => 'PrestaShop ORDER #'.$reference));
            return true;
        } catch (Exception $e) {
            $this->error($e);
        }
    }    
    
    public function getOpenpayCharge($customer, $transaction_id)
    {
        $country = Configuration::get('OPENPAY_COUNTRY');
        $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        Openpay::getInstance($id, $pk, $country);
        Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));

        try {
            $charge = $customer->charges->get($transaction_id);
            return $charge;
        } catch (Exception $e) {
            $this->error($e);
        }
    }

    public function createWebhook($force_host_ssl = false)
    {
        
        $domain = rtrim(Configuration::get('OPENPAY_WEBHOOK_URL'), "/");        
        $webhook_data = array(            
            'url' => $domain.'/modules/openpaystores/notification.php',            
            'force_host_ssl' => $force_host_ssl,
            'event_types' => array(
                'verification',
                'charge.succeeded',
                'charge.cancelled',
                'charge.failed',
                'payout.created',
                'payout.succeeded',
                'payout.failed',
                'chargeback.created',
                'chargeback.rejected',
                'chargeback.accepted',
                'transaction.expired'
            )
        );

        $mode = Configuration::get('OPENPAY_MODE') ? 'LIVE' : 'TEST';
        $country = Configuration::get('OPENPAY_COUNTRY'); 
        $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        $openpay = Openpay::getInstance($id, $pk, $country);
        Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));

        $userAgent = "Openpay-PS17".$country."/v2";
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

    public function deleteWebhook($webhook_id, $mode)
    {
        $country = Configuration::get('OPENPAY_COUNTRY');
        $pk = $mode ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $id = $mode ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        $openpay = Openpay::getInstance($id, $pk, $country);
        Openpay::setProductionMode($mode);

        try {
            $webhook = $openpay->webhooks->get($webhook_id);
            $webhook->delete();
            return true;
        } catch (Exception $e) {
            return $this->error($e, true);
        }
    }

    public function getMerchantInfo()
    {
        $country = Configuration::get('OPENPAY_COUNTRY');
        $sk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        $url = $country === 'MX' ? $this->url_mx : $this->url_co;
        $sandbox_url = $country === 'MX' ? $this->sandbox_url_mx : $this->sandbox_url_co;

        $url = (Configuration::get('OPENPAY_MODE') ? $url : $sandbox_url).'/'.$id;

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

    public function error($e, $backend = false)
    {        
        switch ($e->getErrorCode()) {
            /* ERRORES GENERALES */
            case '1000':
            case '1004':
            case '1005':
                $msg = $this->l('Servicio no disponible.');
                break;           

            default: /* Demás errores 400 */
                $msg = $e->getMessage();
                break;
        }

        $error = 'ERROR '.$e->getErrorCode().'. '.$msg;

        if ($backend) {
            return Tools::jsonDecode(Tools::jsonEncode(array('error' => $e->getErrorCode(), 'msg' => $error)), false);
        } else {
            throw new Exception($error);
        }
    }
    
    public function errorWebhook($e, $force_host_ssl)
    {        
        switch ($e->getErrorCode()) {
            /* ERRORES GENERALES */
            case '1000':
            case '1004':
            case '1005':
                $msg = $this->l('Servicio no dsponible.');
                break;
            
            /* ERRORES WEBHOOK */
            case '6001':
                $msg = $this->l('El webhook ya ha sido procesado.');
                break;          
            
            case '6002':
            case '6003';
                $msg = $this->l("Webhook can't be created.");                
                if($force_host_ssl == true){
                    $this->createWebhook(true);
                }                                                
                break;

            default: /* Demás errores 400 */
                $msg = $e->getMessage();
                break;
        }

        $error = 'ERROR '.$e->getErrorCode().'. '.$msg;
        
        if($e->getErrorCode() != '6001'){
            return Tools::jsonDecode(Tools::jsonEncode(array('error' => $e->getErrorCode(), 'msg' => $error)), false);
        }

        return;
    }
    
    
    public function isNullOrEmpty($string)
    {
        return (!isset($string) || trim($string) === '');
    }

    public function copyMailTemplate()
    {
        $directory = _PS_MAIL_DIR_;
        if ($dhvalue = opendir($directory)) {
            while (($file = readdir($dhvalue)) !== false) {
                if (is_dir($directory.$file) && $file[0] != '.') {

                    $html_file_origin = _PS_MODULE_DIR_.$this->name.'/mails/'.$file.'/openpaystores.html';
                    $txt_file_origin = _PS_MODULE_DIR_.$this->name.'/mails/'.$file.'/openpaystores.txt';

                    /*
                     * If origin files does not exist, skip the loop
                     */
                    if (!file_exists($html_file_origin) && !file_exists($txt_file_origin)) {
                        continue;
                    }

                    $html_file_destination = $directory.$file.'/openpaystores.html';
                    $txt_file_destination = $directory.$file.'/openpaystores.txt';

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