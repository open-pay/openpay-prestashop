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

class OpenpayPrestashopCardPaymentModuleFrontController extends ModuleFrontController
{

    public $ssl = true;
    public $display_column_left = false;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;
        if (!$this->module->checkCurrency()) {
            Tools::redirect('index.php?controller=order');
        }

        $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PUBLIC_KEY_LIVE') : Configuration::get('OPENPAY_PUBLIC_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        if (!empty($this->context->cookie->openpay_error)) {
            $this->context->smarty->assign('openpay_error', $this->context->cookie->openpay_error);
            $this->context->cookie->__set('openpay_error', null);
        }

        $this->context->smarty->assign(array(
            'validation_url' => './index.php?process=validation&fc=module&module=openpayprestashop&controller=default',
            'pk' => $pk,
            'id' => $id,
            'mode' => Configuration::get('OPENPAY_MODE'),
            'nbProducts' => $cart->nbProducts(),
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'module_dir' => $this->module->getPath()
        ));

        $this->context->controller->addJS('https://openpay.s3.amazonaws.com/openpay.v1.min.js');
        $this->context->controller->addJS('https://openpay.s3.amazonaws.com/openpay-data.v1.min.js');

        $this->context->controller->addCSS($this->module->getPath().'views/css/openpay-prestashop.css');

        $this->setTemplate('card_execution.tpl');
    }
}
