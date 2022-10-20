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
    public function initContent()
    {
        parent::initContent();
        $this->ajax = true;
    }

    public function displayAjax()
    {
        
        $cardBin = Tools::getValue('card_bin');
        $binRequestResponse = $this->getTypeCard($cardBin);

        Logger::addLog('#cardType => '.json_encode($binRequestResponse), 1, null, 'Cart', (int) $this->context->cart->id, true);
        
        if (!$binRequestResponse){
            $binRequestResponse = array(
                'status' => 'error',
                'message' => "credit card not found"
            );
        }

        die(Tools::jsonEncode($binRequestResponse));
    }

    private function getTypeCard($cardBin) {
        Logger::addLog('#getTypeCard() => '.$cardBin, 1, null, 'Cart', (int) $this->context->cart->id, true);

        $country = Configuration::get('OPENPAY_COUNTRY');
        $is_sandbox = Configuration::get('OPENPAY_MODE');
        $id = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_MERCHANT_ID_LIVE') : Configuration::get('OPENPAY_MERCHANT_ID_TEST');
        $auth = Configuration::get('OPENPAY_MODE') ? Configuration::get('OPENPAY_PRIVATE_KEY_LIVE') : Configuration::get('OPENPAY_PRIVATE_KEY_TEST');

        Logger::addLog('(444d) $is_sandbox => '.$is_sandbox , 1);

        switch ($country) {
            case 'MX':
                $path = sprintf('/%s/bines/man/%s', $id, $cardBin);
                $cardInfo = $this->requestOpenpay($path, $country, $is_sandbox, "GET",null,$auth);
                $binRequestResponse = array(
                    'status' => 'success',
                    'card_type' => $cardInfo['type'],
                );
                return ($cardBin != null) ? $binRequestResponse : false;

            case 'PE':
                $path = sprintf('/%s/bines/%s/promotions', $id, $cardBin);
                $cardInfo = $this->requestOpenpay($path, $country, $is_sandbox);
                $binRequestResponse = array(
                    'status' => 'success',
                    'card_type' => $cardInfo['cardType'],
                    'installments'  => $cardInfo['installments'],
                    'withInterest' => $cardInfo['withInterest']
                );
                return ($cardBin != null) ? $binRequestResponse : false;

            default:
                $path = sprintf('/cards/validate-bin?bin=%s', $cardBin);
                $cardInfo = $this->requestOpenpay($path, $country, $is_sandbox);
                $binRequestResponse = array(
                    'status' => 'success',
                    'card_type' => $cardInfo['card_type'],
                );
                return ($cardBin != null) ? $binRequestResponse : false;
        }
    }

    private function requestOpenpay($api, $country, $is_sandbox, $method = 'GET', $params = [], $auth = null){

        $country_tld    = strtolower($country);
        $sandbox_url    = 'https://sandbox-api.openpay.'.$country_tld.'/v1';

        $url            = 'https://api.openpay.'.$country_tld.'/v1';
        $absUrl         = $is_sandbox == 0 ? $sandbox_url : $url;
        $absUrl        .= $api;
        $headers        = Array();
        $ch             = curl_init();

        if(!empty($params) && $method == 'POST'){
            $data = json_encode($params);
            Logger::addLog('PARAMS: ' . $data, 1, null, null, null, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $headers[] = 'Content-Type:application/json';
        }

        if (!empty($params) && $method == 'GET') {
            $info = http_build_query($params);
            $absUrl = $absUrl."?".$info;
        }

        if(!empty($auth)){
            $auth = base64_encode($auth.":");
            $headers[] = 'Authorization: Basic '.$auth;
        }


        curl_setopt($ch, CURLOPT_URL, $absUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        Logger::addLog('(444d) $result => '.$result , 1);

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