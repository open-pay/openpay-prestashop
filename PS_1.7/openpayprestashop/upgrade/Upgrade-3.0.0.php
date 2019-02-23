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
if (!defined('_PS_VERSION_')) {
    exit;
}

if (!class_exists('Logger', false)) {
    include(dirname(__FILE__).'/../../../classes/PrestaShopLogger.php');
}

/**
 * @link https://devdocs.prestashop.com/1.7/modules/creation/enabling-auto-update/
 * @param type $object
 * @return type
 */
function upgrade_module_3_0_0($object) {
    try {
        return ($object->registerHook('displayAdminOrder') &&
            $object->registerHook('actionOrderStatusPostUpdate') &&
            Configuration::updateValue('USE_CARD_POINTS', '0') &&
            Configuration::updateValue('OPENPAY_CAPTURE', 'true') &&                      
            Configuration::updateValue('OPENPAY_SAVE_CC', '0') &&                      
            Db::getInstance()->Execute(
                'ALTER TABLE `' . _DB_PREFIX_ . 'openpay_customer` ADD `mode` ENUM(\'live\', \'test\') NULL'
            )
        );
    } catch (Exception $e) {
        Logger::addLog('#upgrade_module_3_0_0 Cards => '.$e->getMessage(), 2, null, null, null, true);
        return true;
    }    
}