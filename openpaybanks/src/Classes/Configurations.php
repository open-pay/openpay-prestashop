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

class Configurations
{
    private $mode;
    private $modeDesc;
    private $sandboxMerchantId;
    private $liveMerchantId;
    private $sandboxSK;
    private $liveSK;
    private $sandboxPK;
    private $livePK;
    private $country;
    private $paymentOrderDeadline;
    private $pseIva;
    private $webhookUrl;
    private $sandboxWebhookId;
    private $liveWebhookId;
    private $webhookUser;
    private $webhookPassword;

    public function __construct()
    {
        $this->mode = Configuration::get('OPENPAY_MODE');
        $this->modeDesc = Configuration::get('OPENPAY_MODE_DESC');
        $this->sandboxMerchantId = Configuration::get('SANDBOX_MERCHANT_ID');
        $this->liveMerchantId = Configuration::get('LIVE_MERCHANT_ID');
        $this->sandboxSK = Configuration::get('SANDBOX_SK');
        $this->liveSK = Configuration::get('LIVE_SK');
        $this->sandboxPK = Configuration::get('SANDBOX_PK');
        $this->livePK = Configuration::get('LIVE_PK');
        $this->country = Configuration::get('COUNTRY');
        $this->paymentOrderDeadline = Configuration::get('PAYMENT_ORDER_DEADLINE');
        $this->pseIva = Configuration::get('PSE_IVA');
        $this->webhookUrl = Configuration::get('WEBHOOK_URL');
        $this->sandboxWebhookId = Configuration::get('SANDBOX_WEBHOOK_ID');
        $this->liveWebhookId = Configuration::get('LIVE_WEBHOOK_ID');
        $this->webhookUser = Configuration::get('WEBHOOK_USER');
        $this->webhookPassword = Configuration::get('WEBHOOK_PASSWORD');
    }

    public static function registerConfiguration()
    {
        Configuration::updateValue('OPENPAY_MODE', 0);
        Configuration::updateValue('OPENPAY_MODE_DESC', 'SANDBOX');
        Configuration::updateValue('SANDBOX_MERCHANT_ID', null);
        Configuration::updateValue('LIVE_MERCHANT_ID', null);
        Configuration::updateValue('SANDBOX_SK', null);
        Configuration::updateValue('LIVE_SK', null);
        Configuration::updateValue('SANDBOX_PK', null);
        Configuration::updateValue('LIVE_PK', null);
        Configuration::updateValue('COUNTRY', 'MX');
        Configuration::updateValue('PAYMENT_ORDER_DEADLINE', null);
        Configuration::updateValue('PSE_IVA', null);
        Configuration::updateValue('WEBHOOK_URL', _PS_BASE_URL_ . __PS_BASE_URI__);
        Configuration::updateValue('SANDBOX_WEBHOOK_ID', null);
        Configuration::updateValue('LIVE_WEBHOOK_ID', null);
        Configuration::updateValue('WEBHOOK_USER', Tools::substr(md5(uniqid(rand(), true)), 0, 10));
        Configuration::updateValue('WEBHOOK_PASSWORD', Tools::substr(md5(uniqid(rand(), true)), 0, 10));
    }

    public static function deleteConfiguration()
    {
        Configuration::deleteByName('OPENPAY_MODE');
        Configuration::deleteByName('OPENPAY_MODE_DESC');
        Configuration::deleteByName('SANDBOX_MERCHANT_ID');
        Configuration::deleteByName('LIVE_MERCHANT_ID');
        Configuration::deleteByName('SANDBOX_SK');
        Configuration::deleteByName('LIVE_SK');
        Configuration::deleteByName('SANDBOX_PK');
        Configuration::deleteByName('LIVE_PK');
        Configuration::deleteByName('COUNTRY');
        Configuration::deleteByName('PAYMENT_ORDER_DEADLINE');
        Configuration::deleteByName('PSE_IVA');
        Configuration::deleteByName('WEBHOOK_URL');
        Configuration::deleteByName('SANDBOX_WEBHOOK_ID');
        Configuration::deleteByName('LIVE_WEBHOOK_ID');
        Configuration::deleteByName('WEBHOOK_USER');
        Configuration::deleteByName('WEBHOOK_PASSWORD');
    }

    public static function loadConfigurationForm(): array
    {
        $configuration_values = array(
            'OPENPAY_MODE' => Tools::getValue('openpay_mode'),
            'OPENPAY_MODE_DESC' => Tools::getValue('openpay_mode') ? "LIVE" : "SANDBOX",
            'SANDBOX_MERCHANT_ID' => trim(Tools::getValue('openpay_merchant_id_test')),
            'LIVE_MERCHANT_ID' => trim(Tools::getValue('openpay_merchant_id_live')),
            'SANDBOX_PK' => trim(Tools::getValue('openpay_public_key_test')),
            'LIVE_PK' => trim(Tools::getValue('openpay_public_key_live')),
            'SANDBOX_SK' => trim(Tools::getValue('openpay_private_key_test')),
            'LIVE_SK' => trim(Tools::getValue('openpay_private_key_live')),
            'COUNTRY' => trim(Tools::getValue('openpay_country')),
            'PAYMENT_ORDER_DEADLINE' => trim(Tools::getValue('openpay_deadline_spei')),
            'PSE_IVA' => trim(Tools::getValue('openpay_pse_iva')),
            'WEBHOOK_URL' => trim(Tools::getValue('openpay_webhook_url'))
        );
        return $configuration_values;
    }

    public static function resetCredentials($openpayModeDesc)
    {
        Configuration::updateValue($openpayModeDesc . '_MERCHANT_ID', '');
        Configuration::updateValue($openpayModeDesc . '_SK', '');
        Configuration::updateValue($openpayModeDesc . '_PK', '');
        Configuration::updateValue($openpayModeDesc . '_WEBHOOK_ID', '');
    }

    public static function updateConfiguration($configFormValues)
    {
        foreach ($configFormValues as $configuration_key => $configuration_value) {
            Configuration::updateValue($configuration_key, $configuration_value);
        }
    }


    /**
     * @param string $mode
     */
    public function setMode(string $mode): void
    {
        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @return string
     */
    public function getModeDesc(): string
    {
        return $this->modeDesc;
    }

    /**
     * @param string $modeDesc
     */
    public function setModeDesc(string $modeDesc): void
    {
        $this->modeDesc = $modeDesc;
    }

    /**
     * @param string $sandboxMerchantId
     */
    public function setSandboxMerchantId(string $sandboxMerchantId): void
    {
        $this->sandboxMerchantId = $sandboxMerchantId;
    }

    /**
     * @return string
     */
    public function getSandboxMerchantId(): string
    {
        return $this->sandboxMerchantId;
    }

    /**
     * @param string $liveMerchantId
     */
    public function setLiveMerchantId(string $liveMerchantId): void
    {
        $this->liveMerchantId = $liveMerchantId;
    }

    /**
     * @return string
     */
    public function getLiveMerchantId(): string
    {
        return $this->liveMerchantId;
    }

    /**
     * @return string
     */
    public function getSandboxSK(): string
    {
        return $this->sandboxSK;
    }

    /**
     * @param string $sandboxSK
     */
    public function setSandboxSK(string $sandboxSK): void
    {
        $this->sandboxSK = $sandboxSK;
    }

    /**
     * @return string
     */
    public function getLiveSK(): string
    {
        return $this->liveSK;
    }

    /**
     * @param string $liveSK
     */
    public function setLiveSK(string $liveSK): void
    {
        $this->liveSK = $liveSK;
    }

    /**
     * @return string
     */
    public function getSandboxPK(): string
    {
        return $this->sandboxPK;
    }

    /**
     * @param string $sandboxPK
     */
    public function setSandboxPK(string $sandboxPK): void
    {
        $this->sandboxPK = $sandboxPK;
    }

    /**
     * @return string
     */
    public function getLivePK(): string
    {
        return $this->livePK;
    }

    /**
     * @param string $livePK
     */
    public function setLivePK(string $livePK): void
    {
        $this->livePK = $livePK;
    }

    /**
     * @return int
     */
    public function getPaymentOrderDeadline(): int
    {
        $defaultOrderDeadline = 72;
        if ($this->paymentOrderDeadline > 0) {
            return $this->paymentOrderDeadline;
        } else {
            return $defaultOrderDeadline;
        }
    }

    /**
     * @param int $paymentOrderDeadline
     */
    public function setPaymentOrderDeadline(int $paymentOrderDeadline): void
    {
        $this->paymentOrderDeadline = $paymentOrderDeadline;
    }

    /**
     * @return string
     */
    public function getWebhookUrl(): string
    {
        return $this->webhookUrl;
    }

    /**
     * @param string $webhookUrl
     */
    public function setWebhookUrl(string $webhookUrl): void
    {
        $this->webhookUrl = $webhookUrl;
    }

    /**
     * @return string
     */
    public function getSandboxWebhookId(): string
    {
        return $this->sandboxWebhookId;
    }

    /**
     * @param string $sandboxWebhookId
     */
    public function setSandboxWebhookId(string $sandboxWebhookId): void
    {
        Configuration::updateValue('SANDBOX_WEBHOOK_ID', $sandboxWebhookId);
        $this->sandboxWebhookId = $sandboxWebhookId;
    }

    /**
     * @return string
     */
    public function getLiveWebhookId(): string
    {
        return $this->liveWebhookId;
    }

    /**
     * @param string $liveWebhookId
     */
    public function setLiveWebhookId(string $liveWebhookId): void
    {
        Configuration::updateValue('LIVE_WEBHOOK_ID', $liveWebhookId);
        $this->liveWebhookId = $liveWebhookId;
    }

    /**
     * @return string
     */
    public function getWebhookUser(): string
    {
        return $this->webhookUser;
    }

    /**
     * @param string $webhookUser
     */
    public function setWebhookUser(string $webhookUser): void
    {
        $this->webhookUser = $webhookUser;
    }

    /**
     * @return string
     */
    public function getWebhookPassword(): string
    {
        return $this->webhookPassword;
    }

    /**
     * @param string $webhookPassword
     */
    public function setWebhookPassword(string $webhookPassword): void
    {
        $this->webhookPassword = $webhookPassword;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getPseIva(): string
    {
        return $this->pseIva;
    }

    /**
     * @param string $pseIva
     */
    public function setPseIva(string $pseIva): void
    {
        $this->pseIva = $pseIva;
    }
}
