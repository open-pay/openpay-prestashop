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

class OpenpayCodiFetchStatusModuleFrontController extends ModuleFrontController
{

    /**
     * @see FrontController::initContent()
     */
    public function initContent(){

        $this->ajax = true;
        parent::initContent();
        
    }
    public function displayAjax(){

        $orderId = Tools::getValue('order_id'); 

        $order = new Order($orderId);

        switch($order->current_state){
            case Configuration::get('PS_OS_PAYMENT'):
                $result = array('status_order' => 'completed', 'status_code' => 200);
            break;
            case Configuration::get('PS_OS_CANCELED'):
                $result = array('status_order' => 'cancelled', 'status_code' => 200);
            break;
            case Configuration::get('waiting_cash_payment'):
                $result = array('status_order' => 'waiting_cash_payment', 'status_code' => 200);
            break;
            default:
                $result = array('status_order' => '', 'status_code' => 404);
            break;
        }

        die(Tools::jsonEncode($result));
    }
}

?>