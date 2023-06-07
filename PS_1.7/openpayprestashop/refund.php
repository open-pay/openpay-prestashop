<?php

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__) . '/openpayprestashop.php');

if (!class_exists('Openpay', false)) {
    include_once(dirname(__FILE__).'/lib/Openpay.php');
}

try {    
    $cookie = new Cookie('psAdmin');
    if(!$cookie->id_employee){
        $message = array(
            'msg' => 'fail',
            'response' => 'No se tienen los permisos necesarios para ejcutar la operación.'
        );
        echo json_encode($message);
        exit;
    }

    $transaction_id = pSQL(Tools::getValue('transaction_id'));
    $amount = pSQL(Tools::getValue('amount'));
    $reason = pSQL(Tools::getValue('reason'));
    $order_id = pSQL(Tools::getValue('order_id'));
    $mode = Configuration::get('OPENPAY_MODE') == 'true' ? 'live' : 'test';

    if (!is_numeric($amount) || $amount == 0)  {
        $message = array(
            'msg' => 'fail',
            'response' => 'Invalid Refund'
        );
        echo json_encode($message);
        exit;
    }
   
    $order = new Order((int) $order_id);
        
    $openpay_customer = Db::getInstance()->getRow('
        SELECT openpay_customer_id
        FROM '._DB_PREFIX_.'openpay_customer
        WHERE id_customer = '.(int) $order->id_customer.' AND (mode = "'.$mode.'" OR mode IS NULL)'
    );

    $country = Configuration::get('OPENPAY_COUNTRY');

    if($country == 'CO'){
        $message = array(
            'msg' => 'fail',
            'response' => 'Openpay plugin does not support refunds.'
        );
        echo json_encode($message);
        exit;
    }
    
    $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
    $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');
           
    $openpay = Openpay::getInstance($id, $pk, $country);
    Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));
    
    $customer = $openpay->customers->get($openpay_customer['openpay_customer_id']);
    $charge = $customer->charges->get($transaction_id);
    $charge->refund(array(
        'amount' => floatval($amount),
        'description' => $reason
    ));

    Db::getInstance()->Execute(
        'UPDATE '._DB_PREFIX_.'openpay_transaction SET status = "refund" WHERE id_transaction = "'.pSQL($charge->id).'"'
    );
    
    //success response
    $message = array(
        'msg' => 'success',
        'response' => 'Reembolso realizado exitosamente.'
    );
    echo json_encode($message);
    exit;            
    
} catch (\Exception $e) {    
    $error_msg = !empty($e->getMessage()) ? $e->getMessage() : 'Some error occured. Please try again.';
    $message = array(
        'msg' => 'fail',
        'response' => $error_msg
    );
    echo json_encode($message);
    exit;
}