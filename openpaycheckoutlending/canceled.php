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

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
include(dirname(__FILE__) . '/openpaycheckoutlending.php');

/*if (!class_exists('Logger', false)) {
    include(dirname(__FILE__).'/../../classes/PrestaShopLogger.php');
}*/

if (!class_exists('Openpay', false)) {
    include_once(dirname(__FILE__).'/lib/Openpay.php');
}

if(empty($_GET)){
    header('HTTP/1.1 200 OK');
    exit;
}


if($_GET['id']){
    $cart_id = preg_replace('/[^a-z0-9]/', '', $_GET['id']);

    $transaction_id_array = Db::getInstance()->getRow('
            SELECT id_transaction
            FROM '._DB_PREFIX_.'openpay_transaction                    
            WHERE id_cart = '.(int) $cart_id
    );

    $country = Configuration::get('OPENPAY_COUNTRY');
    $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
    $merchant_id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

    $openpay = Openpay::getInstance($merchant_id, $pk, $country);
    Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));

    $charge = $openpay->charges->get($transaction_id_array["id_transaction"]);
    $order = new Order((int)$charge->order_id-620);

    Logger::addLog('Cancel request : '. $charge->order_id, 1, null, null, null, true);

    if($order && $charge->status != 'completed'){

        OpenpayCheckoutLending::updateCanceledOrderStatus($order);

        $products = $order->getProducts();
        $product_details = '';
        foreach ($products as $product){
            $product_details .= '<tr><td>'.$product['product_name'].'</td><td>'.$product['product_quantity'].'</td><td>'.$product['product_price'].'</td></tr>';
        }

        //$this->setTemplate('module:openpaycheckoutlending/views/templates/front/canceled.tpl');

        $template = '
                        <!DOCTYPE html>
                        <html>
                        
                        <head>
                            <style>
                                table {
                                    font-family: arial, sans-serif;
                                    border-collapse: collapse;
                                    width: 100%;
                                }
                        
                                td,
                                th {
                                    border: 1px solid #dddddd;
                                    text-align: left;
                                    padding: 8px;
                                    text-align: center;
                                }
                        
                                tr:nth-child(even) {
                                    background-color: #dddddd;
                                }
                                h2{
                                    text-align: center;
                                }
                                .container{
                                margin:auto;
                                width: 40%;
                                /*border: 5px solid #df0067;*/
                                padding: 10px;
                                text-align: center;
                                }
                                img{
                                width: 100%;
                                }
                                .button {
                                  display: inline-block;
                                  border-radius: 4px;
                                  background-color: #2f8ad4;
                                  border: none;
                                  color: #FFFFFF;
                                  text-align: center;
                                  font-size: 28px;
                                  padding: 20px;
                                  width: 80%;
                                  transition: all 0.5s;
                                  cursor: pointer;
                                  margin: 2rem 0 0 0;
                                  font-family: arial, sans-serif;
                                }
                                
                                .button span {
                                  cursor: pointer;
                                  display: inline-block;
                                  position: relative;
                                  transition: 0.5s;
                                }
                                
                                .button span:after {
                                  content: "\00bb";
                                  position: absolute;
                                  opacity: 0;
                                  top: 0;
                                  right: -20px;
                                  transition: 0.5s;
                                }
                                
                                .button:hover span {
                                  padding-right: 25px;
                                }
                                
                                .button:hover span:after {
                                  opacity: 1;
                                  right: 0;
                                }
                            </style>
                        </head>
                        
                        <body>
                            <div class="container">
                            <img src="'._PS_BASE_URL_.'/modules/openpaycheckoutlending/views/img/cancelled.png" alt="">
                            <table class="order-details">
                                <tr>
                                    <th>Reference</th>
                                    <td>'.$order->reference.'</td>
                                </tr>
                                
                            </table>
                            
                            <br>
                            
                            <table>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                </tr>
                                '.$product_details.'
                                <tr>
                                    <th colspan="2">Shipping Cost</th>
                                    <td>'.$order->total_shipping.'</td>
                                </tr>
                                <tr>
                                    <th colspan="2">Total</th>
                                    <td>'.$order->total_paid.'</td>
                                </tr>
                            </table> 
                            
                            <a href="'._PS_BASE_URL_.'" class="button" role="button"><span>Regresar a la tienda</span></a>
                            
                            </div>
                        </body>
                        </html>
    ';

    }else{
        Logger::addLog('#CANCELATION FAILED id:' . $order->id . 'reference:' . $order->reference , 1, null, null, null, true);
    }

    echo $template;

}
