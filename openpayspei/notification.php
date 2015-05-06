<?php

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');

// To configure, add webhook in account storename.com/modules/openpayspei/notification.php

$objeto = file_get_contents('php://input');
$json = json_decode($objeto);

if(!count($json)>0){
    return true;
}

if ($json->type == 'charge.succeeded' && $json->transaction->method == 'bank_account') {
    
    $order = Order::getOrderByCartId(intval($json->transaction->order_id));
    if ($order) {
        $orderHistory = new OrderHistory();
        $orderHistory->id_order = $order;
        $orderHistory->changeIdOrderState(Configuration::get('PS_OS_PAYMENT'), $order);
        $orderHistory->addWithemail();
        Db::getInstance()->Execute('UPDATE ' . _DB_PREFIX_ . 'openpay_spei_transaction SET status = "paid" WHERE id_transaction = ' . $json->transaction->id . '');
    }
}

