<?php

class OpenpayCardsDefaultModuleFrontController extends ModuleFrontController {

    public function __construct() {
        $this->auth = false;
        parent::__construct();
        $this->context = Context::getContext();
        include_once($this->module->getLocalPath() . 'openpaycards.php');
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent() {
        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::initContent();
        if (Tools::getValue('process') == 'validation') {
            $this->validation();
        }
    }

    public function validation() {
        $openpay = new OpenpayCards();
        if ($openpay->active && Tools::getIsset('openpayToken')) {
            $openpay->processPayment(Tools::getValue('openpayToken'), Tools::getValue('device_session_id'));
        } else {
            $this->context->cookie->__set("openpay_error", 'There was a problem with your payment');
            $controller = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc.php' : 'order.php';
            $location = $this->context->link->getPageLink($controller) . (strpos($controller, '?') !== false ? '&' : '?') . 'step=3#openpay_error';
            header('Location: ' . $location);
        }
    }

}
