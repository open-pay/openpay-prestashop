<?php

/*
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2015 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/**
 * @since 1.5.0
 */
class OpenpayPrestashopValidationModuleFrontController extends ModuleFrontController
{

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess() {
        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
            Tools::redirect('index.php?controller=order&step=1');

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module)
            if ($module['name'] == 'openpayprestashop') {
                $authorized = true;
                break;
            }
        if (!$authorized)
            die($this->module->getTranslator()->trans('This payment method is not available.', array(), 'Modules.OpenpayPrestashop.Shop'));

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }                    
        
        $country = Tools::getValue('country'); 
        $save_cc = Tools::getValue('save_cc') ? true : false;

        switch ($country){
            case "MX":
                $installments["val"] =  Tools::getValue('interest_free');
                break;
            case "CO":
                $installments["val"] = Tools::getValue('installment');
                break;
            case "PE":
                $installments["val"] = Tools::getValue('openpay_installments_pe');
                $installments["withInterest"] = Tools::getValue('withInterest');
                Logger::addLog('(444d) $withInterest => '.$installments["withInterest"] , 1);
                break;
        }

        $openpay = new OpenpayPrestashop();        
        $openpay->processPayment(Tools::getValue('openpay_token'), Tools::getValue('device_session_id'), $installments , Tools::getValue('use_card_points'), Tools::getValue('openpay_cc'), $save_cc, Tools::getValue('hidden_card_number'), Tools::getValue('hidden_cvv'));        
    }
}