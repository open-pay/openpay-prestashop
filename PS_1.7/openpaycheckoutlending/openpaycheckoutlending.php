<?php
/**
 * 2007-2021 PrestaShop
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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2021 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class OpenpayCheckoutLending extends PaymentModule {

    private $validation = array();
    private $limited_currencies = array('MXN');
    private $error = array();
    public $test_order_id = 0;

    public function __construct()
    {
        /* Module configuration data  */
        $this->name = 'openpaycheckoutlending';
        $this->displayName = $this->l('Openpay Checkout Lending');
        $this->version = '1.0.2';
        $this->author = 'Openpay SA de CV';
        $this->tab = 'payments_gateways';
        $this->description = $this->l('Compra ahora, paga después');
        /* Uninstall data */
        $warning = 'Are you sure you want uninstall this module?';
        $this->confirmUninstall = $this->l($warning);

        /* Load Openpay SDK */
        if (!class_exists('Openpay', false)) {
            include_once(dirname(__FILE__).'/lib/Openpay.php');
        }

        /* Other configurations */
        $this->sandbox_url_mx = 'https://sandbox-api.openpay.mx/v1';
        $this->url_mx = 'https://api.openpay.mx/v1';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->max_amount_allowed_mx = 18000;

        parent::__construct();
    }

    public function install(){

        $install_config = parent::install() &&
            $this->createPendingState() &&
            $this->registerHook('paymentOptions') &&
            $this->registerHook('paymentReturn') &&
            $this->registerHook('ActionEmailSendBefore') &&
            $this->registerHook('header') &&
            $this->registerHook('displayHeader') &&
            Configuration::updateValue('OPENPAY_MODE', 0) &&
            Configuration::updateValue('OPENPAY_COUNTRY', 'MX') &&
            Configuration::updateValue('OPENPAY_WEBHOOK_ID_TEST', null) &&
            Configuration::updateValue('OPENPAY_WEBHOOK_ID_LIVE', null) &&
            Configuration::updateValue('OPENPAY_WEBHOOK_USER', Tools::substr(md5(uniqid(rand(), true)), 0, 10)) &&
            Configuration::updateValue('OPENPAY_WEBHOOK_PASSWORD', Tools::substr(md5(uniqid(rand(), true)), 0, 10)) &&
            Configuration::updateValue('OPENPAY_WEBHOOK_URL', _PS_BASE_URL_.__PS_BASE_URI__) &&
            $this->installDb();

        return $install_config;
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
            Tools::getValue('module') === 'openpaycheckoutlending' ||
            Tools::getValue('controller') === 'order-opc' ||
            Tools::getValue('controller') === 'orderopc' ||
            Tools::getValue('controller') === 'order') {

            $this->context->controller->addCSS($this->_path.'views/css/openpay-prestashop.css');
        }else {
            Logger::addLog('NO hookHeader, Controller => '.Tools::getValue('controller'), 1, null, null, null, true);
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

    public function updateOpenpayCharge($transaction_id, $order_id, $reference, $cart_id)
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
            if($cart_id > $order_id){
                $charge->update(array('order_id' => $order_id, 'description' => 'PrestaShop ORDER #'.$reference));
            }else{
            $charge->update(array('description' => 'PrestaShop ORDER #'.$reference));
            }
            return true;
        } catch (Exception $e) {
            $this->error($e,null,$this->context->cart->id);
        }
    }

    public function getContent()
    {
        $this->context->controller->addCSS(array($this->_path.'views/css/openpay-prestashop-admin.css'));

        $errors = array();

        /** Update Configuration Values when settings are updated */
        if (Tools::isSubmit('SubmitOpenpay')) {

            $configuration_values = array(
                'OPENPAY_MODE' => Tools::getValue('openpay_mode'),
                'OPENPAY_MERCHANT_ID_TEST' => trim(Tools::getValue('openpay_merchant_id_test')),
                'OPENPAY_MERCHANT_ID_LIVE' => trim(Tools::getValue('openpay_merchant_id_live')),
                'OPENPAY_PRIVATE_KEY_TEST' => trim(Tools::getValue('openpay_private_key_test')),
                'OPENPAY_PRIVATE_KEY_LIVE' => trim(Tools::getValue('openpay_private_key_live')),
                'OPENPAY_WEBHOOK_URL' => trim(Tools::getValue('openpay_webhook_url'))
            );

            foreach ($configuration_values as $configuration_key => $configuration_value) {
                Configuration::updateValue($configuration_key, $configuration_value);
            }

            $mode = Configuration::get('OPENPAY_MODE') ? 'LIVE' : 'TEST';


            // Creates Webhook
            $webhook = $this->createWebhook();

            if ($webhook != null){
                if ($webhook->error) {
                    $errors[] = $webhook->msg;
                }
            }


            if (!$this->getMerchantInfo()) {
                $errors[] = 'Openpay keys are incorrect.';
                Configuration::deleteByName('OPENPAY_MERCHANT_ID_'.$mode);
                Configuration::deleteByName('OPENPAY_PRIVATE_KEY_'.$mode);
                Configuration::deleteByName('OPENPAY_WEBHOOK_ID_'.$mode);
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->error[] = $error;
            }
        }

        $validation_title = $this->checkRequirements();

        $dashboard_openpay = '';
        if(Configuration::get('OPENPAY_MODE')){
            $dashboard_openpay = 'https://dashboard.openpay.mx';
        }else{
            $dashboard_openpay = 'https://sandbox-dashboard.openpay.mx';
        }

        $this->context->smarty->assign(array(
            'openpay_form_link' => $_SERVER['REQUEST_URI'],
            'openpay_configuration' => Configuration::getMultiple(
                array(
                    'OPENPAY_MODE',
                    'OPENPAY_COUNTRY',
                    'OPENPAY_MERCHANT_ID_TEST',
                    'OPENPAY_MERCHANT_ID_LIVE',
                    'OPENPAY_PRIVATE_KEY_TEST',
                    'OPENPAY_PRIVATE_KEY_LIVE',
                    'OPENPAY_WEBHOOK_URL',
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

    private function createPendingState()
    {
        $state = new OrderState();
        $languages = Language::getLanguages();
        $names = array();

        foreach ($languages as $lang) {
            $names[$lang['id_lang']] = $this->l('En espera de aprobación');
        }

        $state->name = $names;
        $state->color = '#4169E1';  // '#62a2c4';
        $state->send_email = false;
        $state->logable = true;
        $state->module_name = 'openpaycheckoutlending';
        $templ = array();

        foreach ($languages as $lang) {
            $templ[$lang['id_lang']] = 'openpaycheckoutlending';
        }

        $state->template = $templ;

        if ($state->save()) {
            try {
                Configuration::updateValue('PS_OS_WAITING_PAYMENT', $state->id);

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
                `type` enum(\'card\',\'store\',\'bank_account\', \'bitcoin\',\'lending\') NOT NULL,
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

         $tests['TOS'] = array(
             'name' => $this->l('Habilitar términos de servicio de pago  (Parámetros de tienda - Configuración de Pedidos - Términos del servicio)'),
             'result' => (bool)Configuration::get('PS_CONDITIONS'));

        /*$tests['webhoook'] = array(
            'name' => $this->l('El Webhook debe ser creado y registrado en su dashboard de Openpay.'),
            'result' => (bool)Configuration::get('OPENPAY_WEBHOOK_URL'));*/

        foreach ($tests as $key_test => $value_test) {
            if ($key_test != 'result' && !$value_test['result']) {
                $tests['result'] = false;
            }
            if($key_test != 'result' && !$value_test['result']){
                $this->error[] = $value_test['name'];
            }
            if ($key_test != 'result') {
                $this->validation[] = $value_test;
            }
        }

        if ($tests['result']) {
            $validation_title = $this->l('Todos los chequeos fueron exitosos, ahora puedes comenzar a utilizar Openpay.');
        } else {
            $validation_title = $this->l('Al menos un problema fue encontrado para poder comenzar a utilizar Openpay. Por favor resuelve los problemas y refresca esta página.');
        }

        return $validation_title;
    }

    public function getMerchantInfo()
    {
        $country = Configuration::get('OPENPAY_COUNTRY');
        $sk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        $url = $this->url_mx;
        $sandbox_url = $this->sandbox_url_mx;

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

    public function copyMailTemplate()
    {
        $directory = _PS_MAIL_DIR_;
        if ($dhvalue = opendir($directory)) {
            while (($file = readdir($dhvalue)) !== false) {
                if (is_dir($directory.$file) && $file[0] != '.') {

                    $html_file_origin = _PS_MODULE_DIR_.$this->name.'/mails/'.$file.'/openpaycheckoutlending.html';
                    $txt_file_origin = _PS_MODULE_DIR_.$this->name.'/mails/'.$file.'/openpaycheckoutlending.txt';

                    /*
                     * If origin files does not exist, skip the loop
                     */
                    if (!file_exists($html_file_origin) && !file_exists($txt_file_origin)) {
                        continue;
                    }

                    $html_file_destination = $directory.$file.'/openpaycheckoutlending.html';
                    $txt_file_destination = $directory.$file.'/openpaycheckoutlending.txt';

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

    public function uninstall()
    {
        /* Eliminar webhooks */
        if (Configuration::get('OPENPAY_WEBHOOK_ID_TEST') != null) {
            $this->deleteWebhook(Configuration::get('OPENPAY_WEBHOOK_ID_TEST'), false);
        }

        if (Configuration::get('OPENPAY_WEBHOOK_ID_LIVE') != null) {
            $this->deleteWebhook(Configuration::get('OPENPAY_WEBHOOK_ID_LIVE'), true);
        }

        $order_status = (int) Configuration::get('PS_OS_WAITING_PAYMENT');
        $orderState = new OrderState($order_status);
        $orderState->delete();


        return parent::uninstall() &&
            Configuration::deleteByName('OPENPAY_WEBHOOK_ID_TEST') &&
            Configuration::deleteByName('OPENPAY_WEBHOOK_ID_LIVE') &&
            Configuration::deleteByName('OPENPAY_WEBHOOK_USER') &&
            Configuration::deleteByName('OPENPAY_WEBHOOK_PASSWORD') &&
            Configuration::deleteByName('OPENPAY_WEBHOOOK_URL') &&
            Configuration::deleteByName('PS_OS_WAITING_PAYMENT');
    }

    public function createWebhook($force_host_ssl = false)
    {
        $mode = Configuration::get('OPENPAY_MODE') ? 'LIVE' : 'TEST';
        $country = Configuration::get('OPENPAY_COUNTRY');
        $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        $openpay = Openpay::getInstance($id, $pk, $country);
        Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));

        $userAgent = "Openpay-PS17".$country."/v2";
        Openpay::setUserAgent($userAgent);

        $url = Tools::getHttpHost(true).__PS_BASE_URI__.'modules/openpaycheckoutlending/notification.php';
        $webhooks = $openpay->webhooks->getList([]);
        $webhookCreated = $this->isWebHookCreated($webhooks, $url);
        if ($webhookCreated) { 
            return $webhookCreated;
        }

        $webhook_data = array(
            'url' => $url,
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

        try {
            $webhook = $openpay->webhooks->add($webhook_data);
            Configuration::updateValue('OPENPAY_WEBHOOK_ID_'.$mode, $webhook->id);
            return $webhook;
        } catch (Exception $e) {
            $force_host_ssl = ($force_host_ssl === false) ? true : false; // Si viene con parámtro FALSE, solicito que se force el host SSL
            return $this->errorWebhook($e, $force_host_ssl);
        }
    }

    public function errorWebhook($e, $force_host_ssl)
    {
        switch ($e->getCode()) {
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

        $error = 'ERROR '.$e->getCode().'. '.$msg;

        if($e->getCode() != '6001'){
            return Tools::jsonDecode(Tools::jsonEncode(array('error' => $e->getCode(), 'msg' => $error)), false);
        }

        return;
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
        $max_amount_allowed = $this->max_amount_allowed_mx;
        //floatval
        if($cart->getOrderTotal() > $max_amount_allowed) {
            return false;
        }

        /** Maybe some @Unused */
        $this->context->smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'total' => $cart->getOrderTotal(),
            'module_dir' => $this->_path,
            'country' => $country
        ));

        $externalOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $externalOption->setCallToActionText($this->l('Compra ahora, paga después'))
            ->setModuleName($this->name)
            ->setLogo(_MODULE_DIR_.'openpaycheckoutlending/views/img/openpay-logo.svg')
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), Tools::usingSecureMode()))
            ->setAdditionalInformation($this->context->smarty->fetch('module:openpaycheckoutlending/views/templates/hook/lending.tpl'));

        return array($externalOption);
    }

    public function hookPaymentReturn($params)
    {
        if (!isset($params['order']) || ($params['order']->module != $this->name)) {
            Logger::addLog('orden no existe', 1, null, null, null, true);
            return false;
        }

        $order = $params['order'];
        $query = 'SELECT id_transaction FROM '._DB_PREFIX_.'openpay_transaction WHERE id_order = '.(int)$order->id;
        $transaction_id_array = Db::getInstance()->getRow($query);

         $charge = $this->getOpenpayCharge($transaction_id_array["id_transaction"]);

         if($order && $charge->status == 'completed') {
            $this->updatePaidOrderStatus($order, $charge);
        }

    }

    public function hookActionEmailSendBefore($params){
        //Logger::addLog("EMAIL SEND TEMPLATE" . $params['template'] . " --- " . json_encode($params), 1, null, null, null, true);
        if($params['template'] === 'order_conf'){
            return false;
        }
        return true;
    }

    public function getOpenpayCharge($transaction_id)
    {
        $country = Configuration::get('OPENPAY_COUNTRY');
        $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        $openpay = Openpay::getInstance($id, $pk, $country);
        Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));

        try {
            $charge = $openpay->charges->get($transaction_id);
            return $charge;
        } catch (Exception $e) {
            $this->error($e);
        }
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
        }
    }

    public function validateAddress($address) {
        $country = Configuration::get('OPENPAY_COUNTRY');
        if ($country == 'MX' && !$this->isNullOrEmpty($address['address1']) && !$this->isNullOrEmpty($address['city']) && !$this->isNullOrEmpty($address['postcode']) && !$this->isNullOrEmpty($address['state'])) {
            return true;
        }
        return false;
    }

    public function isNullOrEmpty($string)
    {
        return (!isset($string) || trim($string) === '');
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
        }

        return $customer_data;
    }

    static function updatePaidOrderStatus($order, $charge)
    {
        if ($order->current_state != Configuration::get('PS_OS_PAYMENT')) {
            $order_history = new OrderHistory();
            $order_history->id_order = (int)$order->id;
            $order_history->changeIdOrderState(Configuration::get('PS_OS_PAYMENT'), (int)$order->id);
            $order_history->addWithemail();
            Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'openpay_transaction SET status = "paid" WHERE id_transaction = "'.pSQL($charge->id).'"'
                );
            }
    }

    static function updateCanceledOrderStatus($order)
    {
        if($order->current_state != Configuration::get('PS_OS_CANCELED')){
            $order_history = new OrderHistory();
            $order_history->id_order = (int) $order->id;
            $order_history->changeIdOrderState(Configuration::get('PS_OS_CANCELED'), (int) $order->id);
            $order_history->addWithemail();
        }

    }

    /**
     * retorna la respuesta de la creación del cargo
     */
    public function offlinePayment($payment_method,$order_id)
    {
        $openpay_customer = $this->getOpenpayCustomer($this->context->cookie->id_customer);
        $shipping_address = new Address($this->context->cart->id_address_delivery);
        $billing_address = new Address($this->context->cart->id_address_invoice);
        $cart = $this->context->cart;
        $amount = number_format(floatval($cart->getOrderTotal()), 2, '.', '');

        $on_success_callback = _PS_BASE_URL_.'/mx/confirmacion-pedido?id_cart='.(int) $this->context->cart->id.
            '&id_module='.(int) $this->id.
            '&id_order='.(int) $this->currentOrder.
            '&key='.$this->context->customer->secure_key;


        $charge_request = array(
            "method" => $payment_method,
            "currency" => $this->context->currency->iso_code,
            "amount" => $amount,
            "description" => $this->l('PrestaShop Cart ID:').' '.(int) $cart->id,
            "order_id" => $order_id+$this->test_order_id,
            "lending_data" => Array(
                "is_privacy_terms_accepted" => (bool)Configuration::get('PS_CONDITIONS') ,   //$this->is_privacy_terms_accepted,
                "callbacks" => Array(
                    "on_success" =>  $on_success_callback,  //_PS_BASE_URL_.__PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.(int) $this->context->cart->id.'&id_module='.(int) $this->id.'&id_order='.(int) $this->currentOrder.'&key='.$this->context->customer->secure_key,       //html_entity_decode( $this->order->get_checkout_order_received_url()),
                    "on_reject" =>   _PS_BASE_URL_."/module/openpaycheckoutlending/canceled?id=".$order_id.'&key='.$this->context->customer->secure_key,  //_PS_BASE_URL_.__PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.(int) $this->context->cart->id,
                    "on_canceled" => _PS_BASE_URL_."/module/openpaycheckoutlending/canceled?id=".$order_id.'&key='.$this->context->customer->secure_key,  //_PS_BASE_URL_.__PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.(int) $this->context->cart->id,
                    "on_failed" =>   _PS_BASE_URL_."/module/openpaycheckoutlending/canceled?id=".$order_id.'&key='.$this->context->customer->secure_key, //_PS_BASE_URL_.__PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.(int) $this->context->cart->id,
                ),
                "shipping" => Array(
                    "name" =>          $shipping_address->firstname,
                    "last_name" =>     $shipping_address->lastname,
                    "address" => Array(
                        "address" =>   $shipping_address->address1 . " " . $shipping_address->address2 ,
                        "state" =>     State::getNameById($shipping_address->id_state) ,
                        "city" =>      $shipping_address->city,
                        "zipcode" =>   $shipping_address->postcode,
                        "country" =>   ($shipping_address->country === 'Mexico' || $shipping_address->country === "México" ) ? "MX" : false
                    ),
                    "email" =>         $openpay_customer->email,
                ),
                "billing" => Array(
                    "name" => $billing_address->firstname,
                    "last name" => $billing_address->lastname,
                    "address" => Array(
                        "address" => $billing_address->address1 . " " . $billing_address->address2 ,
                        "state" => State::getNameById($billing_address->id_state),
                        "city" => $billing_address->city,
                        "zipcode" => $billing_address->postcode,
                        "country" => ($billing_address->country === 'Mexico' || $billing_address->country === "México") ? "MX" : false
                    ),
                    "phone_number" => $billing_address->phone,
                    "email" => $openpay_customer->email
                )
            )
        );

        $result_json = $this->createOpenpayCharge($openpay_customer, $charge_request);
        return $result_json;
    }

    public function error($e, $backend = false, $order_id = false, $key=false)
    {
        switch ($e->getCode()) {
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

        $error = 'ERROR '.$e->getCode().'. '.$msg;
        //$this->context->cookie->__set('openpay_cl_error', $error);
        //$this->context->cookie->__set('openpay_cl_error_code', $e->getCode());
        //$this->error[] = $error;

        if ($backend) {
            return Tools::jsonDecode(Tools::jsonEncode(array('error' => $e->getCode(), 'msg' => $error)), false);
        } else {

            if (class_exists('Logger')) {
                Logger::addLog($this->l('#Openpay - Payment transaction failed').' '.$error, 1, null, 'Cart', (int) $this->context->cart->id, true);
                Logger::addLog(_PS_BASE_URL_."/module/openpaycheckoutlending/canceled?id=".$order_id."&key=".$key."&error_msg=".$error, 1, null, 'Cart', (int) $this->context->cart->id, true);
            }

            if($order_id){
                Tools::redirect(_PS_BASE_URL_."/module/openpaycheckoutlending/canceled?id=".$order_id."&key=".$key."&error_msg=".$error);
            }else{
                Tools::redirect('index.php?controller=order&step=1&error_msg='.$error);
            }

        }

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
        $payment_method = 'lending';

        try {

            $display_name = $this->l('Openpay checkout lending payment');
            $order_status = (int) Configuration::get('PS_OS_WAITING_PAYMENT');

            /*$message = $this->l('Openpay Transaction Details:')."\n\n".
                $this->l('Transaction ID:').' '.$result_json->id."\n".
                $this->l('Payment method:').' '.Tools::ucfirst($payment_method)."\n".
                $this->l('Amount:').' $'.number_format($result_json->amount, 2).' '.Tools::strtoupper($result_json->currency)."\n".
                $this->l('Status:').' '.($result_json->status == 'completed' ? $this->l('Paid') : $this->l('Unpaid'))."\n".
                $this->l('Processed on:').' '.date('Y-m-d H:i:s')."\n".
                $this->l('Mode:').' '.(Configuration::get('OPENPAY_MODE') ? $this->l('Live') : $this->l('Test'))."\n";*/

            $message = "";
            $detail = array('{detail}' => '');

            try {
                $this->validateOrder(
                    (int)$this->context->cart->id,
                    (int)$order_status,
                    number_format(floatval($this->context->cart->getOrderTotal()), 2, '.', ''),
                    $display_name,
                    $message,
                    $detail,
                    null,
                    false,
                    $this->context->customer->secure_key
                );
            }catch (Exception $e){
                Logger::addLog($e->getMessage(), 1, null, null, null, true);
            }

            $new_order = new Order((int) $this->currentOrder);

            $result_json = $this->offlinePayment($payment_method,$this->currentOrder);
            Logger::addLog("DESPUES DE LA TRX", 1, null, null, null, true);


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
                    //'barcode' => pSQL($barcode_url),
                    //'reference' => pSQL($reference),
                    'clabe' => pSQL($clabe)
                ));
            } catch (Exception $e) {
                if (class_exists('Logger')) {
                    Logger::addLog($e->getMessage(), 1, null, null, null, true);
                }
            }

            if ($result_json->status == 'failed' ){
                throw new Exception('Error: ' . $result_json->error_message);

            }else if($result_json->error_code){
                throw new Exception('Error: ' . $result_json->description);
            }

            $redirect = $result_json->payment_method->callbackUrl;

            Logger::addLog($redirect, 1, null, null, null, true);

            Tools::redirect($redirect);
            exit;

            /** FINISH TRY */
        }catch (Exception $e) {

            $message = $e->getMessage();

            if (class_exists('Logger')) {
                Logger::addLog($this->l('Openpay - Payment transaction failed').' '.$message, 1, null, 'Cart', (int) $this->context->cart->id, true);
                Logger::addLog($this->l('Openpay - Payment transaction failed').' '.$e->getTraceAsString(), 4, null, 'Cart', (int) $this->context->cart->id, true);
            }
            $this->error($e,false,$this->currentOrder,$this->context->customer->secure_key);
        }
    }

    private function isWebHookCreated($webhooks, $url) {
        foreach ($webhooks as $webhook) {
            if ($webhook->url === $url) {
                return $webhook;
            }
        }
        return null;
    }
}