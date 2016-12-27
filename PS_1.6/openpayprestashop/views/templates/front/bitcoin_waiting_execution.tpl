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


{capture name=path}
    <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'htmlall':'UTF-8'}" title="{l s='Go back to the Checkout' mod='openpayprestashop'}">{l s='Checkout' mod='openpayprestashop'}</a><span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>{l s='Bitcoin payment' mod='openpayprestashop'}
{/capture}

<h2>{l s='Order Summary' mod='openpayprestashop'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.' mod='openpayprestashop'}</p>
{else}
    <div id="store-container" class="payment_module" >
        <div class="openpay-bitcoin-container">
            <div id="bitcoin_div"></div>
        </div>
    </div>

    <p class="cart_navigation mt30">
        <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'htmlall':'UTF-8'}" class="button-exclusive btn btn-default">
            <i class="icon-chevron-left"></i> {l s='Other payment methods' mod='openpayprestashop'}
        </a>
    </p>
    
    <form action="{$validation_url|escape:'htmlall':'UTF-8'}" method="POST" id="openpay-payment-form">
        <input type="hidden" name="transaction" value="{$transaction_id|escape:'htmlall':'UTF-8'}" id="transaction">
        <input type="hidden" name="payment_method" value="bitcoin" id="payment_method">
    </form>

    <script type="text/javascript">
      $(document).ready(function() {
            var merchant_id = "{$merchant_id|escape:'htmlall':'UTF-8'}";
            var transaction_id = "{$transaction_id|escape:'htmlall':'UTF-8'}";
            var mode = "{$mode|escape:'htmlall':'UTF-8'}";

            OpenPayBitcoin.setId(merchant_id);

            if(mode == "0"){
                OpenPayBitcoin.setSandboxMode(true);
            }

            OpenPayBitcoin.setupIframe('bitcoin_div', transaction_id, function(status){ 
                if(status == 'in_progress'){ 
                    $('#openpay-payment-form').submit();
                } 
            });
      });
    </script>

{/if}