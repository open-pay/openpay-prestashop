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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

require_once(dirname(__FILE__) . '/src/Classes/Webhook.php');
require_once(dirname(__FILE__) . '/src/Classes/Configurations.php');
require_once(dirname(__FILE__) . '/src/Classes/OpenpayInstance.php');
require_once(dirname(__FILE__) . '/src/Classes/OpCustomer.php');
require_once(dirname(__FILE__) . '/src/Classes/ChargeTransaction.php');
require_once(dirname(__FILE__) . '/src/Classes/OpException.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

class OpenpayBanks extends PaymentModule
{
    private $adminConfig;
    private $openpayInstance;
    private $error = array();
    private $validation = array();
    private $limited_currencies = array();
    const MX_SANDBOX_URL = 'https://sandbox-api.openpay.mx/v1';
    const MX_URL = 'https://api.openpay.mx/v1';
    const CO_SANDBOX_URL = 'https://sandbox-api.openpay.co/v1';
    const CO_URL = 'https://api.openpay.co/v1';

    public function __construct()
    {
        if (!class_exists('Openpay', false)) {
            include_once(dirname(__FILE__) . '/lib/Openpay.php');
        }

        $this->name = 'openpaybanks';
        $this->tab = 'payments_gateways';
        $this->version = '4.0.0';
        $this->author = 'Openpay SA de CV';
        $this->module_key = '23c1a97b2718ec0aec28bb9b3b2fc6d5';

        parent::__construct();
        $warning = 'Are you sure you want uninstall this module?';
        $this->displayName = $this->l('Openpay Bank Transfer');
        $this->description = $this->l('Acepta transferencias bancarias con Openpay');
        $this->confirmUninstall = $this->l($warning);
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /*#######################################################################################*/
    /*##########################         PS Default Methods          #########################*/
    /*#######################################################################################*/
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
            if (!Configuration::get($u) || (int)Configuration::get($u) < 1) {
                if (defined('_' . $u . '_') && (int)constant('_' . $u . '_') > 0) {
                    Configuration::updateValue($u, constant('_' . $u . '_'));
                } else {
                    Configuration::updateValue($u, $v);
                }
            }
        }

        $ret = parent::install() &&
            $this->registerHook('paymentOptions') &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('paymentReturn') &&
            $this->registerHook('displayMobileHeader') &&
            $this->registerHook('actionEmailSendBefore') &&
            Configurations::registerConfiguration();
        $this->createPendingState() &&
        $this->installDb();

        return $ret;
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
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'openpay_customer` (
                `id_openpay_customer` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `openpay_customer_id` varchar(32) NULL,
                `id_customer` int(10) unsigned NOT NULL,
                `date_add` datetime NOT NULL,
                `mode` enum(\'live\',\'test\') NOT NULL,
                PRIMARY KEY (`id_openpay_customer`),
                KEY `id_customer` (`id_customer`)) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');

        $return &= Db::getInstance()->Execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'openpay_transaction` (
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
                KEY `idx_transaction` (`type`,`id_order`,`status`)) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');

        return $return;
    }

    /**
     * Openpay's module uninstallation (Configuration values, database tables...)
     *
     * @return boolean Uninstall result
     */
    public function uninstall()
    {
        Webhook::deleteWebhooks() &&
        $this->deletePendingState() &&
        Configurations::deleteConfiguration();

        return parent::uninstall();
    }

    /**
     * @ActionMethods
     * Display the Back-office interface of the Openpay's module
     *
     * @return string HTML/JS Content
     */
    public function getContent()
    {
        // 1. Load CSS file
        $this->context->controller->addCSS(array($this->_path . 'views/css/openpay-prestashop-admin.css'));

        if (Tools::isSubmit('SubmitOpenpay')) {
            // 2. Load Configuration Form Values
            $configFormValues = Configurations::loadConfigurationForm();
            // 3. Update Configuration Form Values
            Configurations::updateConfiguration($configFormValues);
            // 2. Get Configuration values
            $this->adminConfig = new Configurations();
            // 3. Create Openpay Instance sending Admin Configuration values as parameter
            $this->openpayInstance = new OpenpayInstance($this->adminConfig);
            // 4. Check if Merchant Info exists
            if ($this->getMerchantInfo()) {
                // 5. If not exist create webhook
                $webhook = new Webhook($this, $this->adminConfig, $this->openpayInstance);
                $webhookResponse = $webhook->createWebhook();
                // 6. If webhookResponse has an error msg, add the error message to show in Admin Config page
                if ($webhookResponse->msg) {
                    $this->error[] = $webhookResponse->msg;
                }
            } else {
                // If Merchant info don't exists load an error message and clear credentials
                $this->error[] = 'Openpay keys are incorrect.';
                Configurations::resetCredentials($this->adminConfig->getModeDesc());
            }
        }

        $validationResult = $this->checkRequirements();

        $this->context->smarty->assign(array(
            'receipt' => $this->_path . 'views/img/recibo.png',
            'openpay_form_link' => $_SERVER['REQUEST_URI'],
            'openpay_configuration' => Configuration::getMultiple(
                array(
                    'OPENPAY_MODE',
                    'SANDBOX_MERCHANT_ID',
                    'LIVE_MERCHANT_ID',
                    'SANDBOX_PK',
                    'LIVE_PK',
                    'SANDBOX_SK',
                    'LIVE_SK',
                    'COUNTRY',
                    'PAYMENT_ORDER_DEADLINE',
                    'PSE_IVA',
                    'WEBHOOK_URL'
                )
            ),
            'openpay_ssl' => Configuration::get('PS_SSL_ENABLED'),
            'openpay_validation' => $this->validation,
            'openpay_error' => (empty($this->error) ? false : $this->error),
            'openpay_validation_title' => $validationResult['testResult']
        ));

        return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
    }

    /**
     * TODO - Pending to review
     * @ActionMethods
     * Process a payment
     * @param string $token Openpay Transaction ID (token)
     */
    public function processPayment()
    {
        if (!$this->active) {
            return;
        }


        $this->adminConfig = new Configurations();
        $this->openpayInstance = new OpenpayInstance($this->adminConfig);
        $exeptionObject = new OpException($this);
        $opCustomerObject = new OpCustomer($this, $exeptionObject, $this->openpayInstance);

        $openpayCustomer = $opCustomerObject->getOpenpayCustomer($this->context->cookie->id_customer);

        $ChargeTrxObject = new ChargeTransaction($this, $exeptionObject, $openpayCustomer, $this->adminConfig);

        $barcode_url = null;
        $mail_detail = '';
        $message_aux = '';


        /* try {*/
        $payment_method = 'bank_account';
        $display_name = $this->l('Openpay Banks SPEI');

        /*########################  Offline Payment #########################*/
        $chargeRequest = $ChargeTrxObject->createChargeRequest($payment_method);
        $chargeResponse = $ChargeTrxObject->createOpenpayCharge($chargeRequest);

        /*Decide which action take depending on country */
        switch ($this->adminConfig->getCountry()) {
            case 'CO':
                $ChargeTrxObject->pseRedirect($chargeResponse);
                break;
            case 'MX':
                break;
        }
        /*###################################################################*/

        $order_status = (int)Configuration::get('PS_OS_WAITING_BANKS_PAYMENT');


        $clabe = $chargeResponse->payment_method->clabe;
        $reference = $chargeResponse->payment_method->name;

        $mail_detail = '<br/><span style="color:#333"><strong>Banco:</strong></span> STP<br />
                    <span style="color:#333"><strong>CLABE:</strong></span> ' . $clabe . '<br>
                    <span style="color:#333"><strong>Referencia:</strong></span> ' . $reference;

        $message_aux = $this->l('CLABE:') . ' ' . $clabe . "\n" .
            $this->l('Reference:') . ' ' . $reference . "\n";


        $message = $this->l('Openpay Transaction Details:') . "\n\n" .
            $this->l('Transaction ID:') . ' ' . $chargeResponse->id . "\n" .
            $this->l('Payment method:') . ' ' . Tools::ucfirst($payment_method) . "\n" .
            $message_aux .
            $this->l('Amount:') . ' $' . number_format($chargeResponse->amount, 2) . ' ' . Tools::strtoupper($chargeResponse->currency) . "\n" .
            $this->l('Status:') . ' ' . ($chargeResponse->status == 'completed' ? $this->l('Paid') : $this->l('Unpaid')) . "\n" .
            $this->l('Processed on:') . ' ' . date('Y-m-d H:i:s') . "\n" .
            $this->l('Mode:') . ' ' . (Configuration::get('OPENPAY_MODE') ? $this->l('Live') : $this->l('Test')) . "\n";

        /* Create the PrestaShop order in database */
        $detail = array('{detail}' => $mail_detail, 'pdf_url' => $this->getPdfUrl($chargeResponse->id));
        $this->validateOrder(
            (int)$this->context->cart->id,
            (int)$order_status,
            $chargeResponse->amount,
            $display_name,
            $message,
            $detail,
            null,
            false,
            $this->context->customer->secure_key
        );

        $new_order = new Order((int)$this->currentOrder);
        if (Validate::isLoadedObject($new_order)) {
            $payment = $new_order->getOrderPaymentCollection();
            if (isset($payment[0])) {
                $payment[0]->transaction_id = pSQL($chargeResponse->id);
                $payment[0]->save();
            }
        }

        $fee = ($chargeResponse->amount * 0.029) + 2.5;  /* DUDAS*/

        if ($chargeResponse->due_date) {
            $due_date = $chargeResponse->due_date;
        } else {
            $due_date = date('Y-m-d H:i:s');
        }

        $due_date = date('Y-m-d H:i:s');
        if ($chargeResponse->due_date) {
            $due_date = date('Y-m-d H:i:s', strtotime($chargeResponse->due_date));
        }

        /** Store the transaction details */
        try {
            Db::getInstance()->insert('openpay_transaction', array(
                'type' => pSQL($payment_method),
                'id_cart' => (int)$this->context->cart->id,
                'id_order' => (int)$this->currentOrder,
                'id_transaction' => pSQL($chargeResponse->id),
                'amount' => (float)$chargeResponse->amount,
                'status' => pSQL($chargeResponse->status == 'completed' ? 'paid' : 'unpaid'),
                'fee' => (float)$fee,
                'currency' => pSQL($chargeResponse->currency),
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
        $ChargeTrxObject->updateOpenpayCharge($chargeResponse->id, $this->currentOrder, $new_order->reference);

        /** Redirect the user to the order confirmation page history */
        $redirect = __PS_BASE_URI__ . 'index.php?controller=order-confirmation&id_cart=' . (int)$this->context->cart->id .
            '&id_module=' . (int)$this->id .
            '&id_order=' . (int)$this->currentOrder .
            '&key=' . $this->context->customer->secure_key;

        Logger::addLog($redirect, 1, null, null, null, true);

        Tools::redirect($redirect);
        exit;

        /** catch the openpay error */
        /*} catch (Exception $e) {
            dump("processPayment 2 Error");
            dump($e);
            $exeptionObject->error($e);
            $message = $e->getMessage();
            if (class_exists('Logger')) {
                Logger::addLog($this->l('Openpay - Payment transaction failed').' '.$message, 1, null, 'Cart', (int) $this->context->cart->id, true);
            }

            $this->context->cookie->__set('openpay_error', $e->getMessage());

            Tools::redirect('index.php?controller=order&step=1');
        }*/
    }


    /*#######################################################################################*/
    /*##########################                 HOOKS             #########################*/
    /*#######################################################################################*/
    /**
     * TODO - Pending to review
     * @param $params
     * @return mixed
     */
    public function hookActionEmailSendBefore($params)
    {
        if ($params['template'] == 'openpaybanks') {
            Logger::addLog('#hookActionEmailSendBefore INPUT => ' . json_encode($params), 1, null, null, null, true);

            $order_id = $params['templateVars']['{id_order}'];
            $pdf_url = $params['templateVars']['pdf_url'];

            Logger::addLog('#hookActionEmailSendBefore id_order => ' . $order_id, 1, null, null, null, true);
            Logger::addLog('#hookActionEmailSendBefore $reference => ' . $pdf_url, 1, null, null, null, true);

            $pdf_file = $this->handlePdfAttachment($pdf_url, $order_id);
            $params['fileAttachment'] = $pdf_file;
            $params['subject'] = 'Pendiente de pago';

            Logger::addLog('#hookActionEmailSendBefore OUTPUT => ' . json_encode($params), 1, null, null, null, true);
        }

        return $params;
    }

    /**
     * TODO - Pending to review
     * @return string
     */
    public function hookDisplayMobileHeader()
    {
        return $this->hookHeader();
    }

    /**
     * TODO - Pending to review
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
            $this->context->controller->addCSS($this->_path . 'views/css/openpay-prestashop.css');
        }
    }

    /**
     * TODO - Pending to review
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


        $this->adminConfig = new Configurations();
        $this->context->smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'total' => $cart->getOrderTotal(),
            'module_dir' => $this->_path,
            'country' => $this->adminConfig->getCountry()
        ));

        $externalOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $externalOption->setCallToActionText(($this->setCheckoutPaymentName()))
            ->setModuleName($this->name)
            ->setLogo('https://img.openpay.mx/plugins/openpay_logo.svg')
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), Tools::usingSecureMode()))
            ->setAdditionalInformation($this->context->smarty->fetch('module:openpaybanks/views/templates/hook/paymentOptions.tpl'));

        return array($externalOption);
    }

    /**
     * TODO - Pending to review
     * Display a confirmation message after an order has been placed
     *
     * @param array Hook parameters
     */
    public function hookPaymentReturn($params)
    {

        Logger::addLog('hookPaymentReturn', 1, null, null, null, true);

        if (!isset($params['order']) || ($params['order']->module != $this->name)) {
            Logger::addLog('orden no existe', 1, null, null, null, true);
            return false;
        }

        /** @var Order $order */
        $order = $params['order'];

        $id_order = (int)$order->id;
        $reference = isset($order->reference) ? $order->reference : '#' . sprintf('%06d', $id_order);

        $this->smarty->assign('openpay_order', array('reference' => $reference, 'valid' => $order->valid));
        $this->context->controller->addCSS($this->_path . 'views/css/openpay-prestashop.css');
        $this->smarty->assign('order_pending', false);

        /*####################################################################################
        ** Send a smarty variable to orderConfirmation.tpl indicating if is a PSE payment ###*/
        $this->adminConfig = new Configurations();
        if ($this->adminConfig->getCountry() == 'CO') {
            $this->smarty->assign('psePayment', true);
        } else {
            $this->smarty->assign('psePayment', false);
        }
        /*##################################################################################*/

        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'openpay_transaction WHERE id_order = ' . (int)$id_order . '';
        $transaction = Db::getInstance()->getRow($query);

        $pdf_url = $this->getPdfUrl($transaction['id_transaction']);

        $data = array('pdf_url' => $pdf_url);

        $this->smarty->assign('openpay_order', $data);

        $this->context->controller->addCSS($this->_path . 'views/css/receipt.css');
        $this->context->controller->addCSS($this->_path . 'views/css/print.css', 'print');

        $template = './views/templates/hook/orderConfirmation.tpl';

        Logger::addLog($template, 1, null, null, null, true);
        return $this->display(__FILE__, $template);
    }


    /*#######################################################################################*/
    /*##########################            Plugin Methods          #########################*/
    /*#######################################################################################*/
    /**
     * Perform tests of technical requirements to make sure the Openpay's module will work properly
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

        foreach ($tests as $key => $requirement) {
            if ($key != 'result') {
                $this->validation[] = $requirement;
            }
        }

        if ($tests['result']) {
            $tests['testResult'] = $this->l('Todos los chequeos fueron exitosos, ahora puedes comenzar a utilizar Openpay.');
        } else {
            $tests['testResult'] = $this->l('Al menos un problema fue encontrado para poder comenzar a utilizar Openpay. Por favor resuelve los problemas y refresca esta página.');
        }
        return $tests;
    }

    /**
     * Create a custom pending state for offline orders.
     * @return bool
     * @throws PrestaShopException
     */
    private function createPendingState()
    {
        $state = new OrderState();
        $languages = Language::getLanguages();
        $names = array();

        foreach ($languages as $lang) {
            $names[$lang['id_lang']] = $this->l('Awaiting Banks payment');
        }

        $state->name = $names;
        $state->color = '#4169E1';
        $state->send_email = true;
        $state->logable = true;
        $state->module_name = 'openpaybanks';
        $templ = array();

        foreach ($languages as $lang) {
            $templ[$lang['id_lang']] = 'openpaybanks';
        }

        $state->template = $templ;

        if ($state->save()) {
            try {
                Configuration::updateValue('PS_OS_WAITING_BANKS_PAYMENT', $state->id);
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
     * Delete the custom pending state for offline orders
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function deletePendingState()
    {
        $order_status = (int)Configuration::get('PS_OS_WAITING_BANKS_PAYMENT');
        $orderState = new OrderState($order_status);
        $orderState->delete();
        Configuration::deleteByName('PS_OS_WAITING_BANKS_PAYMENT');
    }

    /**
     * Copy Mail Template
     * @throws Exception
     */
    public function copyMailTemplate()
    {
        $directory = _PS_MAIL_DIR_;
        if ($dhvalue = opendir($directory)) {
            while (($file = readdir($dhvalue)) !== false) {
                if (is_dir($directory . $file) && $file[0] != '.') {
                    $html_file_origin = _PS_MODULE_DIR_ . $this->name . '/mails/' . $file . '/openpaybanks.html';
                    $txt_file_origin = _PS_MODULE_DIR_ . $this->name . '/mails/' . $file . '/openpaybanks.txt';

                    /*
                     * If origin files does not exist, skip the loop
                     */
                    if (!file_exists($html_file_origin) && !file_exists($txt_file_origin)) {
                        continue;
                    }

                    $html_file_destination = $directory . $file . '/openpaybanks.html';
                    $txt_file_destination = $directory . $file . '/openpaybanks.txt';

                    if (!Tools::copy($html_file_origin, $html_file_destination)) {
                        $errors = error_get_last();
                        throw new Exception('Error: ' . $errors['message']);
                    }

                    if (!Tools::copy($txt_file_origin, $txt_file_destination)) {
                        /*throw new Exception('Can not copy custom "Awaiting payment" email,
                         please recursive write permission for ~/mails/'); */
                        $errors = error_get_last();
                        throw new Exception('Error: ' . $errors['message']);
                    }
                }
            }
            closedir($dhvalue);
        }
    }

    /**
     * Assign currency as corresponding on depending on the country configuration.
     * Check availables currencies for module
     *
     * @return boolean
     */
    public function checkCurrency()
    {
        $this->adminConfig = new Configurations();
        switch ($this->adminConfig->getCountry()) {
            case 'MX':
                $this->limited_currencies = array('MXN');
                break;
            case 'CO':
                $this->limited_currencies = array('COP');
                break;
        }
        return in_array($this->context->currency->iso_code, $this->limited_currencies);
    }
    /*#######################################################################################*/
    /**
     * TODO - Crear la clase Merchant y agregar este metodo
     * Get Merchant information to make sure it exists in Openpay
     * @return Bool
     */
    public function getMerchantInfo(): bool
    {
        $this->adminConfig = new Configurations();
        $sk = $this->adminConfig->getMode() ? $this->adminConfig->getLiveSK() : $this->adminConfig->getSandboxSK();
        $id = $this->adminConfig->getMode() ? $this->adminConfig->getLiveMerchantId() : $this->adminConfig->getSandboxMerchantId();
        $country = $this->adminConfig->getCountry();

        switch ($country) {
            case 'MX':
                $url = ($this->adminConfig->getMode() ? self::MX_URL : self::MX_SANDBOX_URL) . '/' . $id;
                break;
            case 'CO':
                $url = ($this->adminConfig->getMode() ? self::CO_URL : self::CO_SANDBOX_URL) . '/' . $id;
                break;
        }

        $username = $sk;
        $password = '';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CAINFO, _PS_MODULE_DIR_ . $this->name . '/lib/data/cacert.pem');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        $result = curl_exec($ch);

        if (curl_exec($ch) === false) {
            Logger::addLog('Curl error ' . curl_errno($ch) . ': ' . curl_error($ch), 1, null, null, null, true);
        } else {
            $info = curl_getinfo($ch);
            Logger::addLog('HTTP code ' . $info['http_code'] . ' on request to ' . $info['url'], 1, null, null, null, true);
        }

        curl_close($ch);

        $array = Tools::jsonDecode($result, true);

        if (array_key_exists('id', $array)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * TODO - Pending to review
     * Not used for PSE payments
     * @param $url
     * @param $order_id
     * @return array
     */
    private function handlePdfAttachment($url, $order_id)
    {
        $pdf_content = Tools::file_get_contents($url);
        $pdf_file_name = "payment_instructions_" . $order_id . ".pdf";

        $attachment = array();
        $attachment['content'] = $pdf_content;
        $attachment['name'] = $pdf_file_name;
        $attachment['mime'] = 'application/pdf';

        return $attachment;
    }

    /**
     * TODO - Pending to review
     * Not used for PSE payments
     * @param $transaction_id
     * @return string
     */
    private function getPdfUrl($transaction_id)
    {
        $pdf_url_base = Configuration::get('OPENPAY_MODE') ? 'https://dashboard.openpay.mx/spei-pdf' : 'https://sandbox-dashboard.openpay.mx/spei-pdf';
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('LIVE_MERCHANT_ID') : Configuration::get('SANDBOX_MERCHANT_ID');
        return $pdf_url_base . '/' . $id . '/' . $transaction_id;
    }

    public function setCheckoutPaymentName()
    {
        $paymentName = '';
        $this->adminConfig = new Configurations();
        switch ($this->adminConfig->getCountry()) {
            case 'MX':
                $paymentName = $this->l('Transferencia Interbancaria');
                return $paymentName;
            case 'CO':
                $paymentName = $this->l('Pago PSE');
                return $paymentName;
        }
    }

    /**
     * TODO - Pending to review
     * @return string|null
     */
    public function getPath()
    {
        return $this->_path;
    }
}
