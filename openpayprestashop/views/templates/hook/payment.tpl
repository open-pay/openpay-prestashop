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

<div class="payment_module">
    <div id="payment-method-container">
        <div class="row">
            {if $card == 1}
                <div class="col-md-4 payment-method">
                    <h4>
                        <input {if $card == 1}checked{/if} class="radio-button" type="radio" name="payment-method" id="radio-card" value="radio-card">
                        <i class="icon-credit-card"></i> Pago con tarjetas de crédito/débito
                    </h4>
                </div>
            {/if}

            {if $store == 1}
                <div class="col-md-4 payment-method">
                    <h4>
                        <input {if $card == 0} checked {/if} class="radio-button" type="radio" name="payment-method" id="radio-store" value="radio-store">
                        <i class="icon-money"></i> Pago en efectivo en tiendas de conveniencia
                    </h4>
                </div>
            {/if}

            {if $spei == 1}
                <div class="col-md-4 payment-method">
                    <h4>
                        <input {if $card == 0 && $store == 0} checked {/if} class="radio-button" type="radio" name="payment-method" id="radio-spei" value="radio-spei">
                        <i class="icon-laptop"></i> Pago con transferencia electrónica (SPEI)
                    </h4>
                </div>
            {/if}
        </div>
    </div>
</div>


{if $card == 1}
    {include file="$tpl_path/card.tpl"}
{/if}

{if $store == 1}
    {include file="$tpl_path/store.tpl"}
{/if}

{if $spei == 1}
    {include file="$tpl_path/spei.tpl"}
{/if}