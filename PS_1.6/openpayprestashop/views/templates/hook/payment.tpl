{*
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
*}

{if $card == 1 && $module_configured}
    <div class="openpay-payment-module mb10">
        <a class="openpay" href="{$link->getModuleLink('openpayprestashop', 'cardpayment')|escape:'htmlall':'UTF-8'}">
            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/card.png" style="height: 50px;"> {l s='Credit-debit card payment' mod='openpayprestashop'}
        </a>
    </div>
{/if}

{if $store == 1 && $module_configured}
    <div class="openpay-payment-module mb10">
        <a {if $amount >= '10000'} style="pointer-events: none;" {/if} class="openpay" href="{$link->getModuleLink('openpayprestashop', 'storepayment')|escape:'htmlall':'UTF-8'}">
            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/cash.png" style="height: 50px;"> {l s='Cash payment' mod='openpayprestashop'} {if $amount >= '10000'} <span> {l s='Payment not permitted for amounts over $10,000' mod='openpayprestashop'} </span> {/if}
        </a>
    </div>
{/if}

{if $spei == 1 && $module_configured}
    <div class="openpay-payment-module mb10">
        <a class="openpay" href="{$link->getModuleLink('openpayprestashop', 'speipayment')|escape:'htmlall':'UTF-8'}">
            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/laptop.png" style="height: 50px;"> {l s='Bank payment (SPEI)' mod='openpayprestashop'}
        </a>
    </div>
{/if}


{if $bitcoins == 1 && $module_configured}
    <div class="openpay-payment-module mb10">
        <a class="openpay" href="{$link->getModuleLink('openpayprestashop', 'bitcoinpayment')|escape:'htmlall':'UTF-8'}" style="padding: 15px 40px 15px 20px;">
            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/bitcoins.png" style="height: 50px;"> {l s='Bitcoin payment' mod='openpayprestashop'}
        </a>
    </div>
{/if}