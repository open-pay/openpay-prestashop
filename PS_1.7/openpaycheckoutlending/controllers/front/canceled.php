<?php
class OpenpayCheckoutLendingCanceledModuleFrontController extends ModuleFrontController{
    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $test_order_id =0;
        $id = Tools::getValue('id');
        $order = null;

        if(empty($id)){
            header('HTTP/1.1 200 OK');
            Tools::redirect('index.php');
        }

        $cart_id = preg_replace('/[^a-z0-9]/', '', $id);

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

        if(isset($_GET["error_msg"])){
            $order_id = Order::getOrderByCartId((int)($charge->order_id-$test_order_id));
            $order = new Order($order_id);
        }else{
            $order = new Order((int)$charge->order_id-$test_order_id);
        }

        //$fecha = date("d-m-Y",strtotime($order->date_add."+ 4 days"));
        //dump($fecha);

        Logger::addLog('Cancel request : '. $charge->order_id, 1, null, null, null, true);

        if($order && $charge->status != 'completed'){

            OpenpayCheckoutLending::updateCanceledOrderStatus($order);

            $products = $order->getProducts();
            $product_details = '';
            foreach ($products as $product){
                $product_details .= '<tr><td>'.$product['product_name'].'</td><td>'.$product['product_quantity'].'</td><td>'.$product['product_price'].'</td></tr>';
            }

        }else{
            Logger::addLog('#CANCELATION FAILED id:' . $order->id . 'reference:' . $order->reference , 1, null, null, null, true);
        }

        $this->context->smarty->assign(array(
            'module_dir' => "/modules/openpaycheckoutlending",
            'product_details' => $product_details,
            'order_reference' => $order->reference,
            'total_shipping' => $order->total_shipping,
            'total_paid' => $order->total_paid,
            'base_url' => _PS_BASE_URL_,
            'order' => $order
        ));

        $this->setTemplate('module:openpaycheckoutlending/views/templates/front/canceled.tpl');
    }


}