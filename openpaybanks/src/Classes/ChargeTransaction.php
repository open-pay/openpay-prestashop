<?php
/**
 * 2007-2015 PrestaShop
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
 * @author PrestaShop SA <contact@prestashop.com>
 * @copyright  2007-2015 PrestaShop SA
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class ChargeTransaction
{
    private $exception;
    private $context;
    private $openpayCustomer;
    private $adminConfig;

    public function __construct($module, $exceptionObject, $openpayCustomer, $adminConfig)
    {
        $this->exception = $exceptionObject;
        $this->openpayCustomer = $openpayCustomer;
        $this->context = Context::getContext();
        $this->module = $module;
        $this->adminConfig = $adminConfig;
    }

    public function createOpenpayCharge($charge_request)
    {
        try {
            $charge = $this->openpayCustomer->charges->create($charge_request);
        } catch (OpenpayApiRequestError $e) {
            $this->exception->error($e);
        }
        return $charge;
    }

    public function updateOpenpayCharge($transaction_id, $order_id, $reference)
    {
        try {
            $charge = $this->openpayCustomer->charges->get($transaction_id);
            $charge->update(array('order_id' => $order_id, 'description' => 'PrestaShop ORDER #' . $reference));
        } catch (OpenpayApiRequestError $e) {
            $this->exception->error($e);
        }
        return true;
    }

    public function getOpenpayCharge($transaction_id)
    {
        try {
            $charge = $this->openpayCustomer->charges->get($transaction_id);
        } catch (OpenpayApiRequestError $e) {
            $this->exception->error($e);
        }
        return $charge;
    }

    public function createChargeRequest($payment_method)
    {
        $cart = $this->context->cart;
        $amount = number_format((float)$cart->getOrderTotal(), 2, '.', '');
        $origin_channel = 'PLUGIN_PRESTASHOP';

        $charge_request = array(
            'method' => $payment_method,
            'amount' => $amount,
            'description' => $this->module->l('PrestaShop Cart ID:') . ' ' . (int)$cart->id,
            'origin_channel' => $origin_channel
        );

        if ($this->adminConfig->getCountry() == 'MX') {
            $deadline = $this->adminConfig->getPaymentOrderDeadline();
            $charge_request['due_date'] = date('Y-m-d\TH:i:s', strtotime('+ ' . $deadline . ' hours'));
        } elseif ($this->adminConfig->getCountry() == 'CO') {
            $redirect_url = _PS_BASE_URL_ . __PS_BASE_URI__ . 'module/openpaybanks/pseconfirm';
            $charge_request['currency'] = $this->context->currency->iso_code;
            $charge_request['iva'] = $this->adminConfig->getpseIva();
            $charge_request['redirect_url'] = $redirect_url;
        }
        return $charge_request;
    }

    public function pseRedirect($chargeResponse)
    {
        if ($chargeResponse->payment_method && $chargeResponse->payment_method->type == 'redirect') {
            Logger::addLog($chargeResponse->payment_method->url, 1, null, null, null, true);
            Tools::redirect($chargeResponse->payment_method->url);
        }
    }
}
