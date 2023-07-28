<?php
/**
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
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
 * @author PrestaShop SA <contact@prestashop.com>
 * @copyright  2007-2015 PrestaShop SA
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

require_once(dirname(__FILE__) . '/Configurations.php');

class Webhook
{
    private $module;
    private $openpayInstance;
    private $adminConfig;

    public function __construct($module, $adminConfig, $openpayInstance)
    {
        $this->module = $module;
        $this->adminConfig = $adminConfig;
        $this->openpayInstance = $openpayInstance;
    }

    public function createWebhook($force_host_ssl = false)
    {
        try {
            $url = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'module/openpaybanks/notification';
            $webhooks = $this->openpayInstance->getConnection()->webhooks->getList([]);
            $webhookCreated = $this->isWebHookCreated($webhooks, $url);
            if ($webhookCreated) {
                return $webhookCreated;
            }

            $webhook_data = array(
                'url' => $url,
                'force_host_ssl' => $force_host_ssl,
                'event_types' => array(
                    'verification',
                    'charge.succeeded',
                    'charge.cancelled',
                    'charge.failed',
                    'payout.created',
                    'payout.succeeded',
                    'payout.failed',
                    'spei.received',
                    'chargeback.created',
                    'chargeback.rejected',
                    'chargeback.accepted',
                    'transaction.expired'
                )
            );

            $webhook = $this->openpayInstance->getConnection()->webhooks->add($webhook_data);
            Configuration::updateValue($this->adminConfig->getModeDesc() . '_WEBHOOK_ID', $webhook->id);
            return $webhook;
        } catch (Exception $e) {
            // Si viene con parámtro FALSE, solicito que se force el host SSL
            $force_host_ssl = $force_host_ssl === false;
            return $this->errorWebhook($e, $force_host_ssl);
        }
    }

    public function errorWebhook($e, $force_host_ssl)
    {
        switch ($e->getErrorCode()) {
            // Errores Generales
            case '1000':
            case '1004':
            case '1005':
                $msg = $this->module->l('Service not available.');
                break;

            // ERRORES WEBHOOK
            case '6001':
                $msg = $this->module->l('Webhook already exists.');
                break;

            case '6002':
            case '6003':
                $msg = $this->module->l("Webhook can't be created.");
                if ($force_host_ssl == true) {
                    $this->createWebhook(true);
                }
                break;

            default: // Demás errores 400
                $msg = $e->getMessage();
                break;
        }
        $error = 'ERROR ' . $e->getErrorCode() . '. ' . $msg;
        if ($e->getErrorCode() != '6001') {
            return json_decode(json_encode(array('error' => $e->getErrorCode(), 'msg' => $error)), false);
        }
    }

    private function isWebHookCreated($webhooks, $url)
    {
        foreach ($webhooks as $webhook) {
            if ($webhook->url === $url) {
                $mode = $this->adminConfig->getMode();
                if ($mode){
                    $this->adminConfig->setLiveWebhookId($webhook->id);
                }else{
                    $this->adminConfig->setSandboxWebhookId($webhook->id);
                }
                return $webhook;
            }
        }
        return null;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public static function deleteWebhooks()
    {
        try {
            $sandboxSK = Configuration::get('SANDBOX_SK');
            $sandboxMerchantId = Configuration::get('SANDBOX_MERCHANT_ID');
            $sandboxWebhookid = Configuration::get('SANDBOX_WEBHOOK_ID');
            $sandboxMode = 0;

            $liveSK = Configuration::get('LIVE_SK');
            $liveMerchantId = Configuration::get('LIVE_MERCHANT_ID');
            $liveWebhookid = Configuration::get('LIVE_WEBHOOK_ID');
            $liveMode = 1;

            $country = Configuration::get('COUNTRY');

            if ($sandboxSK != null && $sandboxMerchantId != null && $sandboxWebhookid != null) {
                $sandboxOpenpay = Openpay::getInstance($sandboxMerchantId, $sandboxSK, $country);
                Openpay::setProductionMode($sandboxMode);

                $webhook = $sandboxOpenpay->webhooks->get($sandboxWebhookid);
                $webhook->delete();
            }

            if ($liveSK != null && $liveMerchantId != null && $liveWebhookid != null) {
                $liveOpenpay = Openpay::getInstance($liveMerchantId, $liveSK, $country);
                Openpay::setProductionMode($liveMode);

                $webhook = $liveOpenpay->webhooks->get($liveWebhookid);
                $webhook->delete();
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Delete Webhooks Exception - " . $e);
        }
    }
}
