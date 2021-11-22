<?php
class OpenpayCheckoutLendingCanceledModuleFrontController extends ModuleFrontController{
    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $test_order_id = 0;
        $id = Tools::getValue('id');
        $key = Tools::getValue('key');

        $product_details ='';
        $order = new Order();
        $order->reference ='';
        $order->total_shipping ='';
        $order->total_paid ='';
        $valid_cancel = true;

        if (empty($id) || empty($key)) {
            header('HTTP/1.1 200 OK');
            Tools::redirect('index.php');
        }

        $id_order = preg_replace('/[^a-z0-9]/', '', $id);
        $secure_key = preg_replace('/[^a-z0-9]/', '', $key);


        $secure_key_array = Db::getInstance()->getRow('
            SELECT secure_key
            FROM ' . _DB_PREFIX_ . 'orders                    
            WHERE id_order = ' . (int)$id_order
        );

        if($secure_key_array["secure_key"] == $secure_key){

        $transaction_id_array = Db::getInstance()->getRow('
            SELECT id_transaction
            FROM ' . _DB_PREFIX_ . 'openpay_transaction                    
            WHERE id_order = ' . (int)$id_order
        );

        Logger::addLog('Cancel request : '.$transaction_id_array["id_transaction"], 1, null, null, null, true);

        $country = Configuration::get('OPENPAY_COUNTRY');
        $pk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $merchant_id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        $openpay = Openpay::getInstance($merchant_id, $pk, $country);
        Openpay::setProductionMode(Configuration::get('OPENPAY_MODE'));

        $charge = $openpay->charges->get($transaction_id_array["id_transaction"]);
        $order = new Order((int)$charge->order_id - $test_order_id);


        //$fecha = date("d-m-Y",strtotime($order->date_add."+ 4 days"));
        //dump($fecha);

        Logger::addLog('Cancel request : ' . $charge->order_id, 1, null, null, null, true);

        if ($order && $charge && $charge->status != 'completed') {

            OpenpayCheckoutLending::updateCanceledOrderStatus($order);

            $products = $order->getProducts();
            $product_details = '';
            foreach ($products as $product) {
                $product_details .= '<tr><td>' . $product['product_name'] . '</td><td>' . $product['product_quantity'] . '</td><td>' . $product['product_price'] . '</td></tr>';
            }
        } else {
            Logger::addLog('#CANCELATION FAILED id:' . $order->id . 'reference:' . $order->reference, 1, null, null, null, true);
        }
    }else{
        Logger::addLog('ATENCIÓN! - INTENTO DE CANCELACIÓN INVALIDA',1, null, null, null, true);
        $valid_cancel = false;
    }

        $this->context->smarty->assign(array(
            'module_dir' => "/modules/openpaycheckoutlending",
            'product_details' => $product_details,
            'order_reference' => $order->reference,
            'total_shipping' => $order->total_shipping,
            'total_paid' => $order->total_paid,
            'base_url' => _PS_BASE_URL_,
            'order' => $order,
            'valid_cancel'=>$valid_cancel
        ));

        $this->setTemplate('module:openpaycheckoutlending/views/templates/front/canceled.tpl');
    }


}