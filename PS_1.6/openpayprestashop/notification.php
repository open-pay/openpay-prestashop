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

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/openpayprestashop.php');

if (!class_exists('Logger', false)) {
    include(dirname(__FILE__).'/../../classes/PrestaShopLogger.php');
}

if (!class_exists('Openpay', false)) {
    include_once(dirname(__FILE__).'/lib/Openpay.php');
}

/* To configure, add webhook in account storename.com/modules/openpayprestahsop/notification.php */
$objeto = Tools::file_get_contents('php://input');
$json = Tools::jsonDecode($objeto);

if(empty($json->type)){
    header('HTTP/1.1 200 OK');
    exit;
}

Logger::addLog('Request type: '.$json->type, 1, null, null, null, true);

if($json->transaction->method == 'store' || $json->transaction->method == 'bank_account'){
    $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
    $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');
    $openpay = Openpay::getInstance($id, $pk);
    Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));

    $charge = $openpay->charges->get($json->transaction->id);
    $order = new Order((int) $json->transaction->order_id);
    $order_id = $order->id;

    Logger::addLog('IF ORDER: '.$order_id, 1, null, null, null, true);

    if ($order_id) {
        Logger::addLog('ORDER ID: '.$order_id, 1, null, null, null, true);

        if ($json->type == 'charge.succeeded' &&   $charge->status == 'completed') {
            Logger::addLog('Cart ID: '.$json->transaction->order_id, 1, null, null, null, true);
            Logger::addLog('Trans ID: '.$json->transaction->id, 1, null, null, null, true);

            $order_history = new OrderHistory();
            $order_history->id_order = (int) $order_id;
            $order_history->changeIdOrderState(Configuration::get('PS_OS_PAYMENT'), (int) $order_id);        
            $order_history->addWithemail();

            Db::getInstance()->Execute(
                'UPDATE '._DB_PREFIX_.'openpay_transaction SET status = "paid" WHERE id_transaction = "'.pSQL($json->transaction->id).'"'
            );
        }else if($json->type == 'transaction.expired'){

            $order_history = new OrderHistory();
            $order_history->id_order = (int) $order_id;
            $order_history->changeIdOrderState(Configuration::get('PS_OS_CANCELED'), (int) $order_id);        
            $order_history->addWithemail();
        }
    } else {
        Logger::addLog('NO ORDER', 1, null, null, null, true);
    }
}
header('HTTP/1.1 200 OK');
exit;