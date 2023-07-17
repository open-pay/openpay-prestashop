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

class OpCustomer
{
    private $exception;
    private $openpayInstance;
    private $context;
    private $module;

    public function __construct($module, $exceptionObject, $openpayInstance)
    {
        $this->exception = $exceptionObject;
        $this->openpayInstance = $openpayInstance;
        $this->context = Context::getContext();
        $this->module = $module;
    }

    public function getOpenpayCustomer($customer_id)
    {
        $cart = $this->context->cart;
        $customer = new Customer((int)$cart->id_customer);
        $mode = Configuration::get('OPENPAY_MODE') == 'true' ? 'live' : 'test';

        $openpay_customer = Db::getInstance()->getRow('
            SELECT openpay_customer_id
            FROM ' . _DB_PREFIX_ . 'openpay_customer                    
            WHERE id_customer = ' . (int)$customer_id . ' AND (mode = "' . $mode . '" OR mode IS NULL)');

        if (!isset($openpay_customer['openpay_customer_id'])) {
            $address = Db::getInstance()->getRow('
                    SELECT *
                    FROM ' . _DB_PREFIX_ . 'address
                    WHERE id_customer = ' . (int)$customer_id);

            $state = Db::getInstance()->getRow('
                    SELECT id_country, name
                    FROM ' . _DB_PREFIX_ . 'state
                    WHERE id_state = ' . (int)$address['id_state']);

            $address['state'] = $state['name'];

            $customer_data = array(
                'requires_account' => false,
                'name' => $customer->firstname,
                'last_name' => $customer->lastname,
                'email' => $customer->email,
                'phone_number' => $address['phone'],
            );

            if ($this->validateAddress($address)) {
                $customer_data = $this->formatAddress($customer_data, $address);
                $string_array = http_build_query($customer_data, '', ', ');
                Logger::addLog($this->module->l('Customer Address: ') . $string_array, 1, null, 'Cart', (int)$this->context->cart->id, true);
            }

            $customer_openpay = $this->createOpenpayCustomer($customer_data);

            Db::getInstance()->insert('openpay_customer', array(
                'openpay_customer_id' => pSQL($customer_openpay->id),
                'id_customer' => (int)$this->context->cookie->id_customer,
                'date_add' => date('Y-m-d H:i:s'),
                'mode' => pSQL($mode)
            ));

            return $customer_openpay;
        } else {
            return $this->getCustomer($openpay_customer['openpay_customer_id']);
        }
    }

    public function getCustomer($customer_id)
    {
        try {
            $customer = $this->openpayInstance->getConnection()->customers->get($customer_id);
        } catch (OpenpayApiRequestError $e) {
            $this->exception->error($e);
        }
        return $customer;
    }

    public function createOpenpayCustomer($customer_data)
    {
        try {
            $customer = $this->openpayInstance->getConnection()->customers->add($customer_data);
        } catch (OpenpayApiRequestError $e) {
            if (class_exists('Logger')) {
                Logger::addLog($this->module->l('Openpay - Can not create Openpay Customer'), 1, null, 'Cart', (int)$this->context->cart->id, true);
            }
            $this->exception->error($e);
        }
        return $customer;
    }

    public function isNullOrEmpty($string)
    {
        return (!isset($string) || trim($string) === '');
    }

    /**
     * TODO - Pending to review
     * @param $customer_data
     * @param $address
     * @return mixed
     */
    private function formatAddress($customer_data, $address)
    {
        $country = Configuration::get('COUNTRY');
        if ($country === 'MX') {
            $customer_data['address'] = array(
                'line1' => Tools::substr($address['address1'], 0, 200),
                'line2' => Tools::substr($address['address2'], 0, 50),
                'state' => $address['state'],
                'city' => $address['city'],
                'postal_code' => $address['postcode'],
                'country_code' => $country
            );
        } elseif ($country === 'CO') {
            $customer_data['customer_address'] = array(
                'department' => $address['state'],
                'city' => $address['city'],
                'additional' => Tools::substr($address['address1'], 0, 200) .
                    ' ' . Tools::substr($address['address2'], 0, 50)
            );
        }

        return $customer_data;
    }

    /**
     * TODO - Pending to review
     * @param $address
     * @return bool
     */
    public function validateAddress($address)
    {
        $country = Configuration::get('COUNTRY');
        if ($country == 'MX' &&
            !$this->isNullOrEmpty($address['address1']) &&
            !$this->isNullOrEmpty($address['city']) &&
            !$this->isNullOrEmpty($address['postcode']) &&
            !$this->isNullOrEmpty($address['state'])) {
            return true;
        } elseif ($country == 'CO' &&
            !$this->isNullOrEmpty($address['address1']) &&
            !$this->isNullOrEmpty($address['city']) &&
            !$this->isNullOrEmpty($address['state'])) {
            return true;
        }
        return false;
    }
}
