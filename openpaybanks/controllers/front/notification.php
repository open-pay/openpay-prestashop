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

class OpenpayBanksNotificationModuleFrontController extends ModuleFrontController
{
    private $adminConfig;
    private $openpayInstance;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        $objeto = Tools::file_get_contents('php://input');
        $json = Tools::jsonDecode($objeto);

        if (empty($json->type)) {
            http_response_code(200);
            exit;
        }

        if (class_exists('Logger')) {
            Logger::addLog('Request type: '.$json->type, 1, null, null, null, true);
        }

        try {
            if ($json->transaction->method == 'bank_account') {
                // 2. Get Configuration values
                $this->adminConfig = new Configurations();
                // 3. Create Openpay Instance sending Admin Configuration values as parameter
                $this->openpayInstance = new OpenpayInstance($this->adminConfig);

                $charge = $this->openpayInstance->getConnection()->charges->get($json->transaction->id);
                $order = new Order((int) $charge->order_id);

                if ($order) {
                    Logger::addLog('#Webhook Order ID: '.$charge->order_id, 1, null, null, null, true);
                    Logger::addLog('#Webhook Transaction ID: '.$charge->id, 1, null, null, null, true);
                    if ($json->type == 'charge.succeeded' && $charge->status == 'completed') {
                        $order_history = new OrderHistory();
                        $order_history->id_order = (int) $order->id;
                        $order_history->changeIdOrderState(Configuration::get('PS_OS_PAYMENT'), (int) $order->id);
                        $order_history->addWithemail();

                        Db::getInstance()->Execute(
                            'UPDATE '._DB_PREFIX_.'openpay_transaction SET status = "paid" WHERE id_transaction = "'
                            .pSQL($charge->id).'"'
                        );
                    } elseif ($json->type == 'transaction.expired') {
                        $order_history = new OrderHistory();
                        $order_history->id_order = (int) $order->id;
                        $order_history->changeIdOrderState(Configuration::get('PS_OS_CANCELED'), (int) $order->id);
                        $order_history->addWithemail();
                    }
                } else {
                    Logger::addLog('#Webhook NO ORDER', 1, null, null, null, true);
                }
            }
        } catch (Exception $e) {
            http_response_code(400);
            if (class_exists('Logger')) {
                Logger::addLog('Webhook Notification - BAD REQUEST 400 - Request Body: '
                    .$e->getMessage(), 1, null, null, null, true);
            }
            exit;
        }
        http_response_code(200);
        exit;
    }
}
