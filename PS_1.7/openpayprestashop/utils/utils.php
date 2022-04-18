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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
class Utils {
    public static function getCurrencies($countryCode) {
        $currencies = ['USD'];
        $countryCode = strtoupper($countryCode);
        switch ($countryCode) {
            case 'MX':
                $currencies[] = 'MXN';
                return $currencies;
            case 'CO':
                $currencies[] = 'COP';
                return $currencies;
            case 'PE':
                $currencies[] = 'PEN';
                return $currencies;
            case 'AR':
                $currencies[] = 'ARS';
                return $currencies;
            default:
                break;
        }
    }
    public static function getUrlScripts($country) {
        $scripts = [
            'openpay_js' => '',
            'openpay_fraud_js' => ''
        ];
        $routeBaseOpenpayJs = '%s/openpay.v1.min.js';
        $routeBaseOpenpayFraud = '%s/openpay-data.v1.min.js';

        $baseUrl = $this->getBaseUrlByCountry($country);
        $scripts['openpay_js'] = sprintf($routeBaseOpenpayJs, $baseUrl);
        $scripts['openpay_fraud_js'] = sprintf($routeBaseOpenpayFraud, $baseUrl);
        return $scripts;
    }

    private function getBaseUrlByCountry($country) {
        $basesUrl = array(
            "MX" => "https://openpay.s3.amazonaws.com",
            "CO" => "https://resources.openpay.co",
            "PE" => "https://js.openpay.pe",
            "AR" => "");
        
        return array_key_exists($country, $basesUrl) ? $basesUrl[$country] : null;
    }
}