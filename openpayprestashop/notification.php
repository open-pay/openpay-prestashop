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
include(dirname(__FILE__).'/../../classes/PrestaShopLogger.php');

/* To configure, add webhook in account storename.com/modules/openpayprestahsop/notification.php */

$auth_user = Configuration::get('OPENPAY_WEBHOOK_USER');
$auth_pwd = Configuration::get('OPENPAY_WEBHOOK_PASSWORD');

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

if ($_SERVER['PHP_AUTH_USER'] == $auth_user && $_SERVER['PHP_AUTH_PW'] == $auth_pwd) {
    $objeto = Tools::file_get_contents('php://input');
    $json = Tools::jsonDecode($objeto);

    if ($json->type == 'verification') {
        header('HTTP/1.1 200 OK');
        exit;
    }
    
    if (class_exists('Logger')) {
        Logger::addLog('Request type: '.$json->type, 1, null, null, null, true);
    }

    if ($json->type == 'charge.succeeded' && ($json->transaction->method == 'bitcoin' || $json->transaction->method == 'store' || $json->transaction->method == 'bank_account')) {

        Logger::addLog('Cart ID: '.$json->transaction->order_id, 1, null, null, null, true);
        Logger::addLog('Trans ID: '.$json->transaction->id, 1, null, null, null, true);
        
        if ($json->transaction->method == 'bitcoin') {
            $cart_id = (int) $json->transaction->description;
        } else {
            $cart_id = (int) $json->transaction->order_id;
        }

        $order_id = Order::getOrderByCartId($cart_id);
        
        if ($order_id) {
            Logger::addLog('IF ORDER: '.$order_id, 1, null, null, null, true);
            $order_history = new OrderHistory();
            $order_history->id_order = (int) $order_id;
            $order_history->changeIdOrderState(Configuration::get('PS_OS_PAYMENT'), (int) $order_id);
            $order_history->addWithemail();

            Db::getInstance()->Execute(
                'UPDATE '._DB_PREFIX_.'openpay_transaction SET status = "paid" WHERE id_transaction = "'.pSQL($json->transaction->id).'"'
            );
        } else {
            Logger::addLog('NO ORDER', 1, null, null, null, true);
        }
        header('HTTP/1.1 200 OK');
        exit;
    }

} else {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}
