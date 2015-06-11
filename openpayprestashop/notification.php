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

/* To configure, add webhook in account storename.com/modules/openpayprestahsop/notification.php */
$objeto = Tools::file_get_contents('php://input');
$json = Tools::jsonDecode($objeto);

if (!count($json) > 0)
	return true;

if ($json->type == 'charge.succeeded' && ($json->transaction->method == 'store' || $json->transaction->method == 'bank_account'))
{
	$order_id = (int)$json->transaction->order_id;
	$order = Order::getOrderByCartId($order_id);
	if ($order)
	{
		$order_history = new OrderHistory();
		$order_history->id_order = $order;
		$order_history->changeIdOrderState(Configuration::get('PS_OS_PAYMENT'), $order);
		$order_history->addWithemail();

		Db::getInstance()->Execute(
				'UPDATE '._DB_PREFIX_.'openpay_transaction SET status = "paid" WHERE id_transaction = "'.$json->transaction->id.'"'
		);
	}
}