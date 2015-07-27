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
    <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'htmlall':'UTF-8'}" title="{l s='Go back to the Checkout' mod='openpayprestashop'}">{l s='Checkout' mod='openpayprestashop'}</a><span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>{l s='Cash payment' mod='openpayprestashop'}
{/capture}

<h2>{l s='Order Summary' mod='openpayprestashop'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.' mod='openpayprestashop'}</p>
{else}

    <div id="store-container" class="payment_module" >
        <div class="openpay-form-container">
            <h3 class="openpay_title mt30">{l s='Cash payment' mod='openpayprestashop'}</h3>
            <div class="row">
                <div class="col-md-8 store-image">
                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/stores.png">
                </div>
            </div>
            <div><small><a target="_blank" href="http://www.openpay.mx/tiendas-de-conveniencia.html">{l s='Affiliated stores' mod='openpayprestashop'}</a></small></div>

            <h4 class="subtitle mt30 mb30">{l s='Steps for your cash payment' mod='openpayprestashop'}</h4>

            <div class="row mb30">
                <div class="col-md-4 center">
                    <div><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/step1.png"></div>
                    <p>{l s='Click on the "Generate payment receipt" and your purchase will be waiting for your payment.' mod='openpayprestashop'}</p>
                </div>
                <div class="col-md-4 center">
                    <div><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/step_store.png"></div>
                    <p>{l s='Print your receipt, take it to your nearest convenience store and make payment.' mod='openpayprestashop'}</p>
                </div>
                <div class="col-md-4 center">
                    <div><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/step3.png"></div>
                    <p>{l s='Immediately after receiving your payment we will send you an email with your payment confirmation.' mod='openpayprestashop'}</p>
                </div>
            </div>

        </div>
    </div>

    <form data-ajax="false" action="{$validation_url|escape:'htmlall':'UTF-8'}" method="POST" id="openpay-cash-form">
        <input type="hidden" name="payment_method" value="store" id="payment_method">
        <p class="cart_navigation mt30">
            <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'htmlall':'UTF-8'}" class="button-exclusive btn btn-default">
                <i class="icon-chevron-left"></i> {l s='Other payment methods' mod='openpayprestashop'}
            </a>

            <button type="submit"  class="button btn btn-default button-medium" {if $total >= '10000'} disabled style="cursor: not-allowed;" {/if}>
                <span>
                    {l s='Generate payment receipt' mod='openpayprestashop'}
                    <i class="icon-chevron-right right"></i>
                </span>
            </button>

        </p>

    </form>

{/if}