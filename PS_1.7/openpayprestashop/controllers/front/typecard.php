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

class OpenpayPrestashopTypeCardModuleFrontController extends ModuleFrontController
{

    private $sandbox_url_mx = 'https://sandbox-api.openpay.mx/v1';
    private $url_mx = 'https://api.openpay.mx/v1';

    private $sandbox_url_co = 'https://sandbox-api.openpay.co/v1';
    private $url_co = 'https://api.openpay.co/v1';

    public function initContent()
    {
        parent::initContent();
        $this->ajax = true;
    }

    public function displayAjax()
    {
        
        $cardBin = Tools::getValue('card_bin');
        $cardType = $this->getTypeCard($cardBin);

        Logger::addLog('#cardType => '.$cardType, 1, null, 'Cart', (int) $this->context->cart->id, true);
        
        if ($cardType) {
            $json = array(
                'status' => 'success',
                'card_type' => $cardType
            );
        } else {
            $json = array(
                'status' => 'error',
                'message' => "credit card not found"
            );
        }

        die(Tools::jsonEncode($json));
    }

    private function getTypeCard($cardBin) {
        Logger::addLog('#getTypeCard() => '.$cardBin, 1, null, 'Cart', (int) $this->context->cart->id, true);

        $country = Configuration::get('OPENPAY_COUNTRY');
        $sk = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');

        if ($country == 'MX') {
            $path = sprintf('/%s/bines/man/%s', $id, $cardBin);
            $cardInfo = $this->requestOpenpay(null,$path,"GET",['sk' => $sk]);
            return ($cardBin != null) ? $cardInfo['type'] : false;
        } else {
            $cardInfo = $this->requestOpenpay(null,'/cards/validate-bin?bin='.$cardBin);
            return $cardInfo['card_type'];
        }
    }

    private function requestOpenpay($params, $api, $method = 'GET', $auth = null) {
        $country = Configuration::get('OPENPAY_COUNTRY');
        $url = $country === 'MX' ? $this->url_mx : $this->url_co;
        $sandbox_url = $country === 'MX' ? $this->sandbox_url_mx : $this->sandbox_url_co;

        $absUrl = (Configuration::get('OPENPAY_MODE') ? $url : $sandbox_url);
        $absUrl .= $api;

        $ch = curl_init();
        if ($auth != null) {
            curl_setopt($ch, CURLOPT_USERPWD, $auth['sk'].':'.'');
        }
        curl_setopt($ch, CURLOPT_URL, $absUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        $result = curl_exec($ch);

        if (curl_exec($ch) === false) {
            Logger::addLog('Curl error '.curl_errno($ch).': '.curl_error($ch), 1, null, null, null, true);
        } else {
            $info = curl_getinfo($ch);
            Logger::addLog('HTTP code '.$info['http_code'].' on request to '.$info['url'], 1, null, null, null, true);
        }

        curl_close($ch);

        return Tools::jsonDecode($result, true);
    }
}
 ?>