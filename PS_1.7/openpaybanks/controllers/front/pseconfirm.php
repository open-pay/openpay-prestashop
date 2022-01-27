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

class OpenpayBanksPseConfirmModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        if (!$this->module->checkCurrency()) {
            Tools::redirect('index.php?controller=order');
        }

        $this->adminConfig = new Configurations();
        $this->openpayInstance = new OpenpayInstance($this->adminConfig);
        $exeptionObject = new OpException($this);
        $opCustomerObject = new OpCustomer($this, $exeptionObject, $this->openpayInstance);
        $openpayCustomer = $opCustomerObject->getOpenpayCustomer($this->context->cookie->id_customer);
        $ChargeTrxObject = new ChargeTransaction($this, $exeptionObject, $openpayCustomer, $this->adminConfig);
        $charge = $ChargeTrxObject->getOpenpayCharge(Tools::getValue('id'));

        // Ocurrió un error
        if ($charge->status == 'cancelled' || $charge->status == 'failed') {
            $this->setTemplate('module:openpaybanks/views/templates/front/pseConfirmationError.tpl');
            return;
        }

        //$order_status = (int) Configuration::get('pending_payment');
        $order_status = (int)Configuration::get('PS_OS_PAYMENT');

        $payment_method = 'bank_account';
        $mail_detail = '';
        $message_aux = '';

        $message = $this->module->l('Openpay Transaction Details:') . "\n\n" .
            $this->module->l('Transaction ID:') . ' ' . $charge->id . "\n" .
            $this->module->l('Payment method:') . ' ' . Tools::ucfirst($payment_method) . "\n" .
            $message_aux .
            $this->module->l('Amount:') .
            ' $' . number_format($charge->amount, 2) . ' ' . Tools::strtoupper($charge->currency) . "\n" .
            $this->module->l('Status:') . ' ' .
            ($charge->status == 'completed' ? $this->module->l('Paid') : $this->module->l('Unpaid')) . "\n" .
            $this->module->l('Processed on:') . ' ' . date('Y-m-d H:i:s') . "\n" .
            $this->module->l('Mode:') . ' ' .
            (Configuration::get('OPENPAY_MODE') ? $this->module->l('Live') : $this->module->l('Test')) . "\n";

        /* Create the PrestaShop order in database */
        $detail = array('{detail}' => $mail_detail);

        $payment_module = Module::getInstanceByName($this->module->name);
        $payment_module->validateOrder(
            (int)$this->context->cart->id,
            (int)$order_status,
            $charge->amount,
            'Openpay Banks PSE',
            $message,
            $detail,
            null,
            false,
            $this->context->customer->secure_key
        );

        /** @since 1.5.0 Attach the Openpay Transaction ID to this Order */
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $new_order = new Order((int)$payment_module->currentOrder);
            if (Validate::isLoadedObject($new_order)) {
                $payment = $new_order->getOrderPaymentCollection();
                if (isset($payment[0])) {
                    $payment[0]->transaction_id = pSQL($charge->id);
                    $payment[0]->save();
                }
            }
        }

        /** Store the transaction details */
        try {
            Db::getInstance()->insert('openpay_transaction', array(
                'type' => pSQL('bank_account'),
                'id_cart' => (int)$this->context->cart->id,
                'id_order' => (int)$payment_module->currentOrder,
                'id_transaction' => pSQL($charge->id),
                'amount' => (float)$charge->amount,
                'status' => pSQL($charge->status == 'completed' ? 'paid' : 'unpaid'),
                'fee' => 0.0,
                'currency' => pSQL($charge->currency),
                'mode' => pSQL(Configuration::get('OPENPAY_MODE') == 'true' ? 'live' : 'test'),
                'date_add' => date('Y-m-d H:i:s'),
                'due_date' => date('Y-m-d H:i:s'),
                'barcode' => null,
                'reference' => null,
                'clabe' => null
            ));
        } catch (Exception $e) {
            if (class_exists('Logger')) {
                Logger::addLog($e->getMessage(), 1, null, null, null, true);
            }
        }

        // Update order_id from Openpay Charge
        $ChargeTrxObject->updateOpenpayCharge($charge->id, $payment_module->currentOrder, $new_order->reference);

        /** Redirect the user to the order confirmation page history */
        $redirect = __PS_BASE_URI__ . 'index.php?controller=order-confirmation&id_cart=' .
            (int)$this->context->cart->id .
            '&id_module=' . (int)$this->module->id .
            '&id_order=' . (int)$payment_module->currentOrder .
            '&key=' . $this->context->customer->secure_key;

        Tools::redirect($redirect);
    }
}
