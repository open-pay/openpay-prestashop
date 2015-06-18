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

if (!defined('_PS_VERSION_'))
	exit;

class OpenpayPrestashop extends PaymentModule
{
	private $error = array();
	private $validation = array();
	protected $backward = false;

	public function __construct()
	{
		if (!class_exists('Openpay', false))
			include_once(dirname(__FILE__).'/lib/Openpay.php');

		$this->sandbox_url = 'https://sandbox-api.openpay.mx/v1';
		$this->url = 'https://api.openpay.mx/v1';

		$this->name = 'openpayprestashop';
		$this->tab = 'payments_gateways';
		$this->version = '1.6.1';
		$this->author = 'Openpay SAPI de C.V.';

		parent::__construct();
		$backward_compatibility_url = 'http://addons.prestashop.com/en/modules-prestashop/6222-backwardcompatibility.html';
		$warning = 'All the Openpay transaction details saved in your database will be deleted. Are you sure you want uninstall this module?';
		$this->displayName = $this->l('Openpay');
		$this->description = $this->l('Accept payments by credit-debit card, cash payments and via SPEI with Openpay');
		$this->confirmUninstall = $this->l($warning);

		/* Backward compatibility */
		if (_PS_VERSION_ < '1.5')
		{
			$this->backward_error = $this->l('In order to work properly in PrestaShop v1.4, the Openpay module requires the backward compatibility')
					.'<br />'.$this->l('You can download this module for free here: '.$backward_compatibility_url);
			if (file_exists(_PS_MODULE_DIR_.'backwardcompatibility/backward_compatibility/backward.php'))
			{
				include(_PS_MODULE_DIR_.'backwardcompatibility/backward_compatibility/backward.php');
				$this->backward = true;
			}
			else
				$this->warning = $this->backward_error;
		}
		else
			$this->backward = true;
	}

	/**
	 * Openpay's module installation
	 *
	 * @return boolean Install result
	 */
	public function install()
	{
		if (!$this->backward && _PS_VERSION_ < 1.5)
		{
			echo '<div class="error">'.Tools::safeOutput($this->backward_error).'</div>';
			return false;
		}

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

		foreach ($update_config as $u => $v)
		{
			if (!Configuration::get($u) || (int)Configuration::get($u) < 1)
			{
				if (defined('_'.$u.'_') && (int)constant('_'.$u.'_') > 0)
					Configuration::updateValue($u, constant('_'.$u.'_'));
				else
					Configuration::updateValue($u, $v);
			}
		}

		$ret = parent::install() && $this->createPendingState() &&
				$this->registerHook('payment') &&
				$this->registerHook('header') &&
				$this->registerHook('backOfficeHeader') &&
				$this->registerHook('paymentReturn') &&
				Configuration::updateValue('OPENPAY_MODE', 0) &&
				Configuration::updateValue('OPENPAY_CARDS', 1) &&
				Configuration::updateValue('OPENPAY_STORES', 1) &&
				Configuration::updateValue('OPENPAY_SPEI', 1) &&
				Configuration::updateValue('OPENPAY_BACKGROUND_COLOR', '#003A5B') &&
				Configuration::updateValue('OPENPAY_FONT_COLOR', '#ffffff') &&
				$this->installDb();

		/* The hook "displayMobileHeader" has been introduced in v1.5.x - Called separately to fail silently if the hook does not exist */
		$this->registerHook('displayMobileHeader');

		return $ret;
	}

	private function createPendingState()
	{
		$state = new OrderState();
		$languages = Language::getLanguages();
		$names = array();

		foreach ($languages as $lang)
			$names[$lang['id_lang']] = 'En espera de pago';

		$state->name = $names;
		$state->color = '#4169E1';
		$state->send_email = true;
		$state->module_name = 'openpayprestashop';
		$templ = array();

		foreach ($languages as $lang)
			$templ[$lang['id_lang']] = 'openpayprestashop';

		$state->template = $templ;

		if ($state->save())
		{
			Configuration::updateValue('waiting_cash_payment', $state->id);
			$this->copyMailTemplate();
		}
		else
			return false;

		return true;
	}

	/**
	 * Openpay's module database tables installation
	 *
	 * @return boolean Database tables installation result
	 */
	public function installDb()
	{
		return Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'openpay_customer` (
                    `id_openpay_customer` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `openpay_customer_id` varchar(32) NOT NULL,
                    `id_customer` int(10) unsigned NOT NULL,
                    `date_add` datetime NOT NULL,
                    PRIMARY KEY (`id_openpay_customer`),
                    KEY `id_customer` (`id_customer`)) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1') &&
				Db::getInstance()->Execute('
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
                    KEY `idx_transaction` (`type`,`id_order`,`status`)) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1'
		);
	}

	/**
	 * Openpay's module uninstallation (Configuration values, database tables...)
	 *
	 * @return boolean Uninstall result
	 */
	public function uninstall()
	{
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
				Configuration::deleteByName('OPENPAY_CARDS') &&
				Configuration::deleteByName('OPENPAY_STORES') &&
				Configuration::deleteByName('OPENPAY_SPEI') &&
				Configuration::deleteByName('OPENPAY_BACKGROUND_COLOR') &&
				Configuration::deleteByName('OPENPAY_FONT_COLOR') &&
				Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'openpay_customer`') &&
				Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'openpay_transaction`');
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
	public function hookHeader()
	{
		/* If 1.4 and no backward, then leave */
		if (!$this->backward)
			return;

		/* Continue only if we are in the checkout process */
		if (Tools::getValue('controller') != 'order-opc' && (!($_SERVER['PHP_SELF'] == __PS_BASE_URI__.'order.php' ||
				$_SERVER['PHP_SELF'] == __PS_BASE_URI__.'order-opc.php' ||	Tools::getValue('controller') == 'order' ||
				Tools::getValue('controller') == 'orderopc' ||	Tools::getValue('step') == 3)))
			return;

		/* Load CSS files through CCC */
		$this->context->controller->addCSS($this->_path.'views/css/openpay-prestashop.css');

	}

	/**
	 * Display the Openpay's payment form
	 *
	 * @return string Openpay's Smarty template content
	 */
	public function hookPayment()
	{
		/* If 1.4 and no backward then leave */
		if (!$this->backward)
			return;

		if (!empty($this->context->cookie->openpay_error))
		{
			$this->smarty->assign('openpay_error', $this->context->cookie->openpay_error);
			$this->context->cookie->__set('openpay_error', null);
		}

		$domain = (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];
		$this->smarty->assign(
				'validation_url', $domain.__PS_BASE_URI__.'index.php?process=validation&fc=module&module=openpayprestashop&controller=default'
		);

		$this->smarty->assign('amount', $this->context->cart->getOrderTotal());
		$this->smarty->assign('card', Configuration::get('OPENPAY_CARDS'));
		$this->smarty->assign('store', Configuration::get('OPENPAY_STORES'));
		$this->smarty->assign('spei', Configuration::get('OPENPAY_SPEI'));
		$this->smarty->assign('openpay_ps_version', _PS_VERSION_);

		return $this->display(__FILE__, './views/templates/hook/payment.tpl');
	}

	/**
	 * Display the two fieldsets containing Openpay's transactions details
	 * Visible on the Order's detail page in the Back-office only
	 *
	 * @return string HTML/JS Content
	 */
	public function hookBackOfficeHeader()
	{
		//do not use this function for PS v1.6+
		if (version_compare(_PS_VERSION_, 1.6, '>='))
			return;

		/* If 1.4 and no backward, then leave */
		if (!$this->backward)
			return;

		if (!Tools::getIsset('vieworder') || !Tools::getIsset('id_order'))
			return;

		$id_order = (int)Tools::getValue('id_order');

		if (Db::getInstance()->getValue('SELECT module FROM '._DB_PREFIX_.'orders WHERE id_order = '.(int)$id_order) == $this->name)
		{
			$query = 'SELECT * FROM '._DB_PREFIX_.'openpay_transaction WHERE id_order = '.(int)$id_order.'';
			$transaction_details = Db::getInstance()->getRow($query);
			$img = '<img src="../img/admin/money.gif" alt="" />';
			$output = '
                <script type="text/javascript">
                $(document).ready(function() {
                    $(\'<fieldset'.(_PS_VERSION_ < 1.5 ? 'style="width: 400px;"' : '').'>'.$img.'<legend>'.$this->l('Payment Details').'</legend>';

			if (isset($transaction_details['id_transaction']))
			{

				$color = $transaction_details['status'] == 'paid' ? 'green' : '#CC0000';
				$label = $transaction_details['status'] == 'paid' ? $this->l('Paid') : $this->l('Unpaid');

				$mode_color = $transaction_details['mode'] == 'live' ? 'green' : '#CC0000';
				$mode_label = $transaction_details['mode'] == 'live' ? $this->l('Live') : $this->l('Test');

				$output .=
						$this->l('Openpay Transaction ID:').' '.Tools::safeOutput($transaction_details['id_transaction']).'<br /><br />'.
						$this->l('Status:').'</b> <span style="font-weight: bold; color: '.$color.'">'.$label.'</span><br>'.
						$this->l('Amount:').' '.Tools::displayPrice($transaction_details['amount']).'<br />'.
						$this->l('Processed on:').' '.Tools::safeOutput($transaction_details['date_add']).'<br />'.
						$this->l('Processing Fee:').' '.Tools::displayPrice($transaction_details['fee']).'<br /><br />'.
						$this->l('Mode:').'</b> <span style="font-weight: bold; color: '.$mode_color.'">'.$mode_label.'</span>';
			}
			else
			{
				$text = 'An error occured (check details at the bottom of this page)';
				$output .= '<b style="color: #CC0000;">'.$this->l('Warning:').'</b> '.$this->l($text);
			}
			return $output;
		}
	}

	/**
	 * Display a confirmation message after an order has been placed
	 *
	 * @param array Hook parameters
	 */
	public function hookPaymentReturn($params)
	{
		if (!isset($params['objOrder']) || ($params['objOrder']->module != $this->name))
			return false;

		if ($params['objOrder'] && Validate::isLoadedObject($params['objOrder']))
		{

			$id_order = (int)$params['objOrder']->id;

			$this->smarty->assign('openpay_order', array(
				'reference' => isset($params['objOrder']->reference) ? $params['objOrder']->reference : '#'.sprintf('%06d', $id_order),
				'valid' => $params['objOrder']->valid
					)
			);
			$this->context->controller->addCSS($this->_path.'views/css/openpay-prestashop.css');
			$this->smarty->assign('order_pending', false);

			$query = 'SELECT * FROM '._DB_PREFIX_.'openpay_transaction WHERE id_order = '.(int)$id_order.'';
			$transaction = Db::getInstance()->getRow($query);
		}

		switch ($transaction['type'])
		{
			case 'store':

				$customer = new Customer((int)$this->context->cookie->id_customer);

				$this->smarty->assign(
						'openpay_order', array(
							'barcode' => $transaction['reference'],
							'barcode_url' => $transaction['barcode'],
							'amount' => number_format($transaction['amount'], 2),
							'currency' => $transaction['currency'],
							'email' => $customer->email,
							'date' => $this->getLongGlobalDateFormat($transaction['date_add']),
							'due_date' => $this->getLongGlobalDateFormat($transaction['due_date']),
							'logo' => '/img/'.Configuration::get('PS_LOGO'),
							'email' => Configuration::get('PS_SHOP_EMAIL'),
							'phone' => Configuration::get('BLOCKCONTACTINFOS_PHONE'),
							'shop_name' => Configuration::get('PS_SHOP_NAME'),
							'bg_color' => Configuration::get('OPENPAY_BACKGROUND_COLOR'),
							'font_color' => Configuration::get('OPENPAY_FONT_COLOR')
						)
				);

				$this->context->controller->addCSS($this->_path.'views/css/receipt.css');
				$this->context->controller->addCSS($this->_path.'views/css/print.css', 'print');

				$template = './views/templates/hook/store_order_confirmation.tpl';

				break;

			case 'bank_account':

				$this->smarty->assign(
						'openpay_order', array(
							'clabe' => $transaction['clabe'],
							'reference' => $transaction['reference'],
							'amount' => number_format($transaction['amount'], 2),
							'currency' => $transaction['currency'],
							'shop_name' => Configuration::get('PS_SHOP_NAME'),
							'due_date' => $this->getLongGlobalDateFormat($transaction['due_date']),
							'email' => Configuration::get('PS_SHOP_EMAIL'),
							'phone' => Configuration::get('BLOCKCONTACTINFOS_PHONE'),
							'bg_color' => Configuration::get('OPENPAY_BACKGROUND_COLOR'),
							'font_color' => Configuration::get('OPENPAY_FONT_COLOR')
						)
				);

				$this->context->controller->addCSS($this->_path.'views/css/receipt.css');
				$this->context->controller->addCSS($this->_path.'views/css/print.css', 'print');

				$template = './views/templates/hook/spei_order_confirmation.tpl';

				break;

			default :
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
	public function processPayment($payment_method, $token = null, $device_session_id = null)
	{
		/* If 1.4 and no backward, then leave */
		if (!$this->backward)
			return;

		$barcode_url = null;
		$reference = null;
		$clabe = null;
		$content = '';
		$mail_detail = '';
		$message_aux = '';

		try
		{
			switch ($payment_method)
			{
				case 'card':
					$display_name = 'Openpay pago con tarjeta';
					$result_json = $this->cardPayment($token, $device_session_id);
					$order_status = (int)Configuration::get('PS_OS_PAYMENT');

					$message_aux = $this->l(Tools::ucfirst($result_json->card->type).' card:').' '.
						Tools::ucfirst($result_json->card->brand).' (Exp: '.$result_json->card->expiration_month.'/'.$result_json->card->expiration_year.')'."\n".
						$this->l('Card number:').' '.$result_json->card->card_number."\n";

					break;

				case 'store':
					$display_name = 'Openpay pago en tiendas';
					$content = '&content_only=1';
					$result_json = $this->othersPayment($payment_method);
					$order_status = (int)Configuration::get('waiting_cash_payment');

					$barcode_url = $result_json->payment_method->barcode_url;
					$reference = $result_json->payment_method->reference;

					$mail_detail = '<br/><img src="'.$barcode_url.'" /><br/><span style="color:#333"><strong>Referencia:</strong></span>'.$reference;

					$message_aux = $this->l('Reference:').' '.$reference."\n";

					break;

				case 'bank_account':
					$display_name = 'Openpay pago vía SPEI';
					$content = '&content_only=1';
					$result_json = $this->othersPayment($payment_method);
					$order_status = (int)Configuration::get('waiting_cash_payment');

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
					//$this->l('Status:').' '.($result_json->status == 'completed' ? $this->l('Paid') : $this->l('Unpaid'))."\n".
					$this->l('Processed on:').' '.date('Y-m-d H:i:s')."\n".
					$this->l('Mode:').' '.(Configuration::get('OPENPAY_MODE') == 'true' ? $this->l('Live') : $this->l('Test'))."\n";

			/* Create the PrestaShop order in database */
			$this->validateOrder(
					(int)$this->context->cart->id,
					(int)$order_status, $result_json->amount,
					$display_name, $message, array('{detail}' => $mail_detail), null,
					false, $this->context->customer->secure_key
			);

			/** @since 1.5.0 Attach the Openpay Transaction ID to this Order */
			if (version_compare(_PS_VERSION_, '1.5', '>='))
			{
				$new_order = new Order((int)$this->currentOrder);
				if (Validate::isLoadedObject($new_order))
				{
					$payment = $new_order->getOrderPaymentCollection();
					if (isset($payment[0]))
					{
						$payment[0]->transaction_id = pSQL($result_json->id);
						$payment[0]->save();
					}
				}
			}

			/** Store the transaction details */
			if ($result_json->id)
				Db::getInstance()->insert('openpay_transaction', array(
					'type' => $payment_method,
					'id_cart' => (int)$this->context->cart->id,
					'id_order' => (int)$this->currentOrder,
					'id_transaction' => pSQL($result_json->id),
					'amount' => $result_json->amount,
					'status' => ($result_json->status == 'completed' ? 'paid' : 'unpaid'),
					'fee' => (($result_json->amount * 0.029) + 2.5),
					'currency' => pSQL($result_json->currency),
					'mode' => (Configuration::get('OPENPAY_MODE') == 'true' ? 'live' : 'test'),
					'date_add' => date('Y-m-d H:i:s'),
					'due_date' => ($result_json->due_date) ? $result_json->due_date : null,
					'barcode' => $barcode_url,
					'reference' => $reference,
					'clabe' => $clabe
				));

			/** Redirect the user to the order confirmation page history */
			if (_PS_VERSION_ < 1.5)
				$redirect = __PS_BASE_URI__.'order-confirmation.php?id_cart='.(int)$this->context->cart->id.
						$content.
						'&id_module='.(int)$this->id.
						'&id_order='.(int)$this->currentOrder.
						'&key='.$this->context->customer->secure_key;
			else
				$redirect = __PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.(int)$this->context->cart->id.
						$content.
						'&id_module='.(int)$this->id.
						'&id_order='.(int)$this->currentOrder.
						'&key='.$this->context->customer->secure_key;

			Tools::redirect($redirect);
			exit;

			/** catch the openpay error */
		}
		catch (Exception $e)
		{
			$message = $e->getMessage();

			if (class_exists('Logger'))
				Logger::addLog(
					$this->l('Openpay - Payment transaction failed').' '.$message, 1, null, 'Cart', (int)$this->context->cart->id, true
				);

			$this->context->cookie->__set('openpay_error', $e->getMessage());

			$redirect = $this->context->link->getModuleLink('openpayprestashop', 'cardpayment');
			Tools::redirect($redirect);
			exit;
		}
	}

	public function cardPayment($token, $device_session_id)
	{
		$cart = $this->context->cart;
		$openpay_customer = $this->getOpenpayCustomer($this->context->cookie->id_customer);

		$charge_request = array(
			'method' => 'card',
			'currency' => $this->context->currency->iso_code,
			'source_id' => $token,
			'device_session_id' => $device_session_id,
			'amount' => $cart->getOrderTotal(),
			'description' => $this->l('PrestaShop Cart ID:').' '.(int)$cart->id,
			'order_id' => 'Cart ID: '.(int)$cart->id
		);

		$result_json = $this->createOpenpayCharge($openpay_customer, $charge_request);

		return $result_json;
	}

	public function othersPayment($payment_method)
	{
		$openpay_customer = $this->getOpenpayCustomer($this->context->cookie->id_customer);
		$cart = $this->context->cart;
		$deadline = 720;

		if ($payment_method == 'store')
		{
			if (Configuration::get('OPENPAY_DEADLINE_STORES') && Configuration::get('OPENPAY_DEADLINE_STORES') > 0)
				$deadline = Configuration::get('OPENPAY_DEADLINE_STORES');
		}
		else
		{
			if (Configuration::get('OPENPAY_DEADLINE_SPEI') && Configuration::get('OPENPAY_DEADLINE_SPEI') > 0)
				$deadline = Configuration::get('OPENPAY_DEADLINE_SPEI');
		}

		$due_date = date('Y-m-d\TH:i:s', strtotime('+ '.$deadline.' hours'));

		$charge_request = array(
			'method' => $payment_method,
			'currency' => $this->context->currency->iso_code,
			'amount' => $cart->getOrderTotal(),
			'description' => $this->l('PrestaShop Cart ID:').' '.(int)$cart->id,
			'order_id' => (int)$cart->id,
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
	public function checkSettings()
	{
		if (Configuration::get('OPENPAY_MODE'))
			return Configuration::get('OPENPAY_PUBLIC_KEY_LIVE') != '' && Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') != '';
		else
			return Configuration::get('OPENPAY_PUBLIC_KEY_TEST') != '' && Configuration::get('OPENPAY_PRIVATE_KEY_TEST') != '';
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
			'name' => $this->l('La extensión PHP CURL tiene que estar activada en su servidor'),
			'result' => extension_loaded('curl'));

		if (Configuration::get('OPENPAY_MODE'))
			$tests['ssl'] = array(
				'name' => $this->l('SSL tiene que ser habilitado en su tienda online ( antes de entrar en el modo activo)'),
				'result' => Configuration::get('PS_SSL_ENABLED') || (!empty($_SERVER['HTTPS']) && Tools::strtolower($_SERVER['HTTPS']) != 'off'));

		$tests['php52'] = array(
			'name' => $this->l('Su servidor tiene que soportar minimo PHP 5.2'),
			'result' => version_compare(PHP_VERSION, '5.2.0', '>='));

		$tests['configuration'] = array(
			'name' => $this->l('Configurar las credenciales de Openpay en este módulo (Merchant ID , llave privada, llave pública)'),
			'result' => $this->getMerchantInfo());

		if (_PS_VERSION_ < 1.5)
			$tests['backward'] = array(
				'name' => $this->l('You are using the backward compatibility module'),
				'result' => $this->backward, 'resolution' => $this->backward_error);

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
	public function getContent()
	{
		$this->context->controller->addCSS(array($this->_path.'views/css/openpay-prestashop-admin.css'));

		$errors = array();

		/** Update Configuration Values when settings are updated */
		if (Tools::isSubmit('SubmitOpenpay'))
		{
			if (empty($errors))
			{
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
					'OPENPAY_FONT_COLOR' => Tools::getValue('openpay_font_color')
				);

				foreach ($configuration_values as $configuration_key => $configuration_value)
					Configuration::updateValue($configuration_key, $configuration_value);

				$webhook = $this->createWebhook();
				if ($webhook->error && $webhook->error != 6001)
					$errors[] = $webhook->msg;

				if (!$this->getMerchantInfo())
				{
					$errors[] = 'Las credenciales de Openpay son incorrectas.';
					$mode = Tools::getValue('openpay_mode') ? 'LIVE' : 'TEST';
					Configuration::deleteByName('OPENPAY_PUBLIC_KEY_'.$mode);
					Configuration::deleteByName('OPENPAY_MERCHANT_ID_'.$mode);
					Configuration::deleteByName('OPENPAY_PRIVATE_KEY_'.$mode);
					Configuration::deleteByName('OPENPAY_DEADLINE_STORES');
					Configuration::deleteByName('OPENPAY_DEADLINE_SPEI');
					Configuration::deleteByName('OPENPAY_CARDS');
					Configuration::deleteByName('OPENPAY_STORES');
					Configuration::deleteByName('OPENPAY_SPEI');
					Configuration::deleteByName('OPENPAY_BACKGROUND_COLOR');
					Configuration::deleteByName('OPENPAY_FONT_COLOR');
				}
			}
		}

		if (!empty($errors))
			foreach ($errors as $error)
				$this->error[] = $error;

		$requirements = $this->checkRequirements();

		foreach ($requirements as $k => $requirement)
		{
			if ($k != 'result')
			{
				/**
				 * Formato para el arreglo de validaciones
				 */
				$this->validation[] = '
                <tr>
                    <td><img src="../img/admin/'.($requirement['result'] ? 'ok' : 'forbbiden').'.gif" alt="" /></td>
                    <td>'.
						$requirement['name'].(!$requirement['result'] &&
						isset($requirement['resolution']) ? '<br />'.Tools::safeOutput($requirement['resolution'], true) : '')
						.'</td>
                </tr>';
			}
		}

		if ($requirements['result'])
			$validation_title = $this->l('Todos los chequeos fueron exitosos. Ahora puedes configurar el módulo y comenzar a usar Openpay.');
		else
			$validation_title = $this->l('Hay al menos un problema que te impdide usar Opepay. Favor de reparar el problema y recarga la página.');

		$this->context->smarty->assign(array(
			'receipt' => $this->_path.'views/img/recibo.png',
			'openpay_form_link' => Tools::safeOutput($_SERVER['REQUEST_URI']),
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
						'OPENPAY_FONT_COLOR'
					)
			),
			'openpay_ssl' => Configuration::get('PS_SSL_ENABLED'),
			'openpay_validation' => (empty($this->validation) ? false : $this->validation),
			'openpay_error' => (empty($this->error) ? false : $this->error),
			'openpay_validation_title' => $validation_title
		));

		return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
	}

	public function checkCurrency($cart)
	{
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
	}

	public function getPath()
	{
		return $this->_path;
	}

	public function getOpenpayCustomer($customer_id)
	{
		$cart = $this->context->cart;
		$customer = new Customer((int)$cart->id_customer);

		$openpay_customer = Db::getInstance()->getRow('
                    SELECT openpay_customer_id
                    FROM '._DB_PREFIX_.'openpay_customer
                    WHERE id_customer = '.(int)$customer_id);

		if (!isset($openpay_customer['openpay_customer_id']))
		{
			try
			{
				$customer_data = array(
					'name' => $customer->firstname,
					'last_name' => $customer->lastname,
					'email' => $customer->email,
					'requires_account' => false
				);

				$customer_openpay = $this->createOpenpayCustomer($customer_data);

				Db::getInstance()->Execute('
                        INSERT INTO '._DB_PREFIX_.'openpay_customer (id_openpay_customer, openpay_customer_id, id_customer, date_add)
                        VALUES (null, \''.pSQL($customer_openpay->id).'\', '.(int)$this->context->cookie->id_customer.', NOW())');

				return $customer_openpay;
			}
			catch (Exception $e)
			{
				if (class_exists('Logger'))
					Logger::addLog($this->l('Openpay - Invalid Credit Card'), 1, null, 'Cart', (int)$this->context->cart->id, true);
			}
		}
		else
			return $this->getCustomer($openpay_customer['openpay_customer_id']);
	}

	public function getCustomer($customer_id)
	{
		$pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
		$id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

		$openpay = Openpay::getInstance($id, $pk);
		Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));

		$customer = $openpay->customers->get($customer_id);
		return $customer;
	}

	public function createOpenpayCustomer($customer_data)
	{
		$pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
		$id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

		$openpay = Openpay::getInstance($id, $pk);
		Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));

		try
		{
			$customer = $openpay->customers->add($customer_data);
			return $customer;
		}
		catch (OpenpayApiTransactionError $e)
		{
			$this->error($e);
		}
		catch (OpenpayApiRequestError $e)
		{
			$this->error($e);
		}
		catch (OpenpayApiConnectionError $e)
		{
			$this->error($e);
		}
		catch (OpenpayApiAuthError $e)
		{
			$this->error($e);
		}
		catch (OpenpayApiError $e)
		{
			$this->error($e);
		}
		catch (Exception $e)
		{
			$this->error($e);
		}
	}

	public function createOpenpayCharge($customer, $charge_request)
	{
		$pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
		$id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

		Openpay::getInstance($id, $pk);
		Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));

		try
		{
			$charge = $customer->charges->create($charge_request);
			return $charge;
		}
		catch (OpenpayApiTransactionError $e)
		{
			$this->error($e);
		}
		catch (OpenpayApiRequestError $e)
		{
			$this->error($e);
		}
		catch (OpenpayApiConnectionError $e)
		{
			$this->error($e);
		}
		catch (OpenpayApiAuthError $e)
		{
			$this->error($e);
		}
		catch (OpenpayApiError $e)
		{
			$this->error($e);
		}
		catch (Exception $e)
		{
			$this->error($e);
		}
	}

	public function createWebhook()
	{
		$domain = (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];
		$webhook_data = array(
			'url' => $domain.__PS_BASE_URI__.'modules/openpayprestashop/notification.php',
			'event_types' => array(
				'verification',
				'charge.succeeded',
				'charge.created',
				'charge.cancelled',
				'charge.failed',
				'payout.created',
				'payout.succeeded',
				'payout.failed',
				'spei.received',
				'chargeback.created',
				'chargeback.rejected',
				'chargeback.accepted'
			)
		);

		$pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
		$id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

		$openpay = Openpay::getInstance($id, $pk);
		Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));

		try
		{
			$webhook = $openpay->webhooks->add($webhook_data);
			return $webhook;
		}
		catch (OpenpayApiTransactionError $e)
		{
			return $this->error($e, true);
		}
		catch (OpenpayApiRequestError $e)
		{
			return $this->error($e, true);
		}
		catch (OpenpayApiConnectionError $e)
		{
			return $this->error($e, true);
		}
		catch (OpenpayApiAuthError $e)
		{
			return $this->error($e, true);
		}
		catch (OpenpayApiError $e)
		{
			return $this->error($e, true);
		}
		catch (Exception $e)
		{
			return $this->error($e, true);
		}
	}

	public function getMerchantInfo()
	{
		$sk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
		$id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

		$url = (Configuration::get('OPENPAY_MODE') ? $this->url : $this->sandbox_url).'/'.$id;

		$username = $sk;
		$password = '';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
		$result = curl_exec($ch);
		curl_close($ch);

		$array = Tools::jsonDecode($result, true);

		if (array_key_exists('id', $array))
			return true;
		else
			return false;
	}

	public function error($e, $backend = false)
	{
		/* 6001 el webhook ya existe */
		switch ($e->getErrorCode())
		{

			/* ERRORES GENERALES */
			case '1000':
				$msg = 'Servicio no disponible.';
				break;

			case '1001':
				$msg = 'Los campos no tienen el formato correcto, o la petición no tiene campos que son requeridos.';
				break;

			case '1004':
				$msg = 'Servicio no disponible.';
				break;

			case '1005':
				$msg = 'Servicio no disponible.';
				break;

			/* ERRORES ALMACENAMIENTO */
			case '2004':
				$msg = 'El dígito verificador del número de tarjeta es inválido de acuerdo al algoritmo Luhn.';
				break;

			case '2005':
				$msg = 'La fecha de expiración de la tarjeta es anterior a la fecha actual.';
				break;

			case '2006':
				$msg = 'El código de seguridad de la tarjeta (CVV2) no fue proporcionado.';
				break;

			/* ERRORES TARJETA */
			case '3001':
			case '3004':
			case '3005':
				$msg = 'La tarjeta fue rechazada.';
				break;

			case '3002':
				$msg = 'La tarjeta ha expirado.';
				break;

			case '3003':
				$msg = 'La tarjeta no tiene fondos suficientes.';
				break;

			case '3006':
				$msg = 'La operación no esta permitida para este cliente o esta transacción.';
				break;

			case '3007':
				$msg = 'La tarjeta fue declinada.';
				break;

			case '3008':
				$msg = 'La tarjeta no es soportada en transacciones en línea.';
				break;

			case '3009':
				$msg = 'La tarjeta fue reportada como perdida.';
				break;

			case '3010':
				$msg = 'El banco ha restringido la tarjeta.';
				break;

			case '3011':
				$msg = 'El banco ha solicitado que la tarjeta sea retenida. Contacte al banco.';
				break;

			case '3012':
				$msg = 'Se requiere solicitar al banco autorización para realizar este pago.';
				break;

			case '6002':
			case '6002':
				$domain = (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];
				$msg = 'Ha ocurrido un error al crear el webhook.';
				$msg .= 'Verifica en tu panel de Openpay que este haya sido creado, es necesario instalarlo para recibir notificaciones de pago.';
				$msg .= '<br>El webhook deberá ser: '.$domain.__PS_BASE_URI__.'modules/openpayprestashop/notification.php';
				break;

			default: /* Demás errores 400 */
				$msg = 'La petición no pudo ser procesada.';
				break;
		}

		$error = 'ERROR '.$e->getErrorCode().'. '.$msg;

		if ($backend)
			return Tools::jsonDecode(Tools::jsonEncode(array('error' => $e->getErrorCode(), 'msg' => $error)), false);
		else
			throw new Exception($error);
	}

	public function copyMailTemplate()
	{
		$html_file_origin = _PS_MODULE_DIR_.$this->name.'/mails/es/openpayprestashop.html';
		$txt_file_origin = _PS_MODULE_DIR_.$this->name.'/mails/es/openpayprestashop.txt';
		$directory = _PS_MAIL_DIR_;
		if ($dhvalue = opendir($directory))
		{
			while (($file = readdir($dhvalue)) !== false)
			{
				if (is_dir($directory.$file) && $file[0] != '.')
				{
					$html_file_destination = $directory.$file.'/openpayprestashop.html';
					if (!Tools::copy($html_file_origin, $html_file_destination))
						throw new Exception;

					$txt_file_destination = $directory.$file.'/openpayprestashop.txt';
					if (!Tools::copy($txt_file_origin, $txt_file_destination))
						throw new Exception;

				}
			}
			closedir($dhvalue);
		}

	}

	public function getLongGlobalDateFormat($input)
	{
		$time = strtotime($input);

		$string_month = $this->getLongStringForMonth(date('n', $time));

		// Formato "12 de Julio de 2014, a las 6:36 PM"
		return date('j', $time).' de '.$string_month.' de '.date('Y', $time).', a las '.date('g:i A', $time);
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
