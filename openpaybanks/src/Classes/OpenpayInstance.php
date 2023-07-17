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

class OpenpayInstance
{
    private $connection;
    private $adminConfig;

    public function __construct($adminConfig)
    {
        $this->adminConfig = $adminConfig;
        $this->createInstance();
    }

    /**
     * @return OpenpayApi
     */
    public function getConnection(): OpenpayApi
    {
        return $this->connection;
    }

    /**
     * @param mixed $connection
     */
    public function setConnection($connection): void
    {
        $this->connection = $connection;
    }

    /**
     * Create a Connection to Openpay's API depending on the mode (SANDBOX = 0 / LIVE = 1)
     * @return OpenpayInstance
     */
    public function createInstance()
    {
        if ($this->adminConfig->getMode()) {
            $this->loadInstanceSettings(
                $this->adminConfig->getLiveMerchantId(),
                $this->adminConfig->getLiveSK(),
                $this->adminConfig->getCountry(),
                $this->adminConfig->getMode()
            );
        } else {
            $this->loadInstanceSettings(
                $this->adminConfig->getSandboxMerchantId(),
                $this->adminConfig->getSandboxSK(),
                $this->adminConfig->getCountry(),
                $this->adminConfig->getMode()
            );
        }
    }

    /**
     * Load an Openpay connection with the admin settings params. This method calls static methods from Openpay sdk.
     * @param $merchantId
     * @param $sk
     * @param $country
     * @param $mode
     */
    public function loadInstanceSettings($merchantId, $sk, $country, $mode)
    {
        $this->connection = Openpay::getInstance($merchantId, $sk, $country);
        Openpay::setProductionMode($mode);
        $userAgent = "Openpay-PS17MX/v2";
        Openpay::setUserAgent($userAgent);
    }
}
