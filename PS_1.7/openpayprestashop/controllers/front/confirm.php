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

class OpenpayPrestashopConfirmModuleFrontController extends ModuleFrontController
{

    public $ssl = true;
    public $display_column_left = false;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        
        $charge_type = Configuration::get('OPENPAY_CHARGE_TYPE');
        
        if (!$this->module->checkCurrency()) {
            Tools::redirect('index.php?controller=order');
        }
                
        if (!in_array($charge_type, array('auth', '3d'))) {                  
            $this->setTemplate('module:openpayprestashop/views/templates/front/3d_secure_disabled.tpl');               
            return;
        }
        
        $openpay_prestashop = new OpenpayPrestashop();            
        $openpay_customer = $openpay_prestashop->getOpenpayCustomer($this->context->cookie->id_customer);                    
        $charge = $openpay_prestashop->getOpenpayCharge($openpay_customer, Tools::getValue('id'));

        // Ocurrió un error
        if ($charge->status !== 'completed') {           
            $this->setTemplate('module:openpayprestashop/views/templates/front/3d_secure_confirm_error.tpl');
            return;
        }

        //$order_status = (int) Configuration::get('pending_payment');
        $order_status = (int) Configuration::get('PS_OS_PAYMENT');

        $message_aux = $this->l(Tools::ucfirst($charge->card->type).' card:').' '.
                Tools::ucfirst($charge->card->brand).' (Exp: '.$charge->card->expiration_month.'/'.$charge->card->expiration_year.')'."\n".
                $this->l('Card number:').' '.$charge->card->card_number."\n";

        $message = $this->l('Openpay Transaction Details:')."\n\n".
                $this->l('Transaction ID:').' '.$charge->id."\n".
                $this->l('Payment method:').' Card\n'.
                $message_aux.
                $this->l('Amount:').' $'.number_format($charge->amount, 2).' '.Tools::strtoupper($charge->currency)."\n".
                $this->l('Status:').' '.($charge->status == 'completed' ? $this->l('Paid') : $this->l('Unpaid'))."\n".
                $this->l('Processed on:').' '.date('Y-m-d H:i:s')."\n".
                $this->l('Mode:').' '.(Configuration::get('OPENPAY_MODE') ? $this->l('Live') : $this->l('Test'))."\n";

        /* Create the PrestaShop order in database */
        $detail = array('{detail}' => '');

        $payment_module = Module::getInstanceByName($this->module->name);
        $payment_module->validateOrder(
                (int) $this->context->cart->id, (int) $order_status, $charge->amount, 'Openpay card payment', $message, $detail, null, false, $this->context->customer->secure_key
        );

        /** @since 1.5.0 Attach the Openpay Transaction ID to this Order */
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $new_order = new Order((int) $payment_module->currentOrder);
            if (Validate::isLoadedObject($new_order)) {
                $payment = $new_order->getOrderPaymentCollection();
                if (isset($payment[0])) {
                    $payment[0]->transaction_id = pSQL($charge->id);
                    $payment[0]->save();
                }
            }
        }
                
        /** Store the transaction details */
        $fee = $charge->fee->amount + $charge->fee->tax;
        try {
            Db::getInstance()->insert('openpay_transaction', array(
                'type' => pSQL('card'),
                'id_cart' => (int) $this->context->cart->id,
                'id_order' => (int) $payment_module->currentOrder,
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
        } catch (Exception $e) {
            if (class_exists('Logger')) {
                Logger::addLog($e->getMessage(), 1, null, null, null, true);
            }
        }

        // Update order_id from Openpay Charge        
        $openpay_prestashop->updateOpenpayCharge($charge->id, $payment_module->currentOrder, $new_order->reference);
        
        /** Redirect the user to the order confirmation page history */
        $redirect = __PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.(int) $this->context->cart->id.
                '&id_module='.(int) $this->module->id.
                '&id_order='.(int) $payment_module->currentOrder.
                '&key='.$this->context->customer->secure_key;        

        Tools::redirect($redirect);
    }
}
