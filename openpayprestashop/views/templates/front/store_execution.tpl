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
    <a href="{$link->getPageLink('order', true, NULL, "step=3")}" title="{l s='Go back to the Checkout' mod='openpayprestashop'}">{l s='Checkout' mod='openpayprestashop'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Pago con tarjeta de cŕedito/débito' mod='openpayprestashop'}
{/capture}

<h2>{l s='Resumen del pedido ' mod='openpayprestashop'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
    <p class="warning">Tu carrito esta vacío.</p>
{else}

    <div id="store-container" class="payment_module" >
        <div class="openpay-form-container">
            <h3 class="openpay_title mt30">Pago en efectivo en tiendas de conveniencia</h3>
            <div class="row">
                <div class="col-md-8 store-image">
                    <img src="/modules/openpayprestashop/views/img/stores.png">
                </div>
            </div>
            <div><small><a target="_blank" href="http://www.openpay.mx/tiendas-de-conveniencia.html">Consulta las tiendas afiliadas</a></small></div>

            <h4 class="subtitle mt30 mb30">Pasos para tu pago por tienda</h4>

            <div class="row mb30">
                <div class="col-md-4 center">
                    <div><img src="/modules/openpayprestashop/views/img/step1.png"></div>
                    <p>Haz clic en el botón "Generar ficha de pago", donde tu compra quedará en espera de que realices tu pago.</p>
                </div>
                <div class="col-md-4 center">
                    <div><img src="/modules/openpayprestashop/views/img/step_store.png"></div>
                    <p>Imprime tu recibo, llévalo a tu tienda de conveniencia más cercana y realiza el pago.</p>
                </div>
                <div class="col-md-4 center">
                    <div><img src="/modules/openpayprestashop/views/img/step3.png"></div>
                    <p>Inmediatamente después de recibir tu pago te enviaremos un correo electrónico con la confirmación de pago.</p>
                </div>
            </div>

            <br>
            <form data-ajax="false" action="{$validation_url}" method="POST" id="openpay-cash-form">
                <input type="hidden" name="payment_method" value="store" id="payment_method">
                <p class="cart_navigation" id="cart_navigation">
                    <button type="submit"  class="button btn btn-default button-medium" {if $total >= '10000'} disabled style="cursor: not-allowed;" {/if}>
                        <span>
                            {l s='Generar ficha de pago' mod='openpayprestashop'}
                            <i class="icon-chevron-right right"></i>
                        </span>
                    </button>
                </p>

            </form>
        </div>
    </div>

    <p class="cart_navigation" id="cart_navigation">
        <a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button-exclusive btn btn-default"><i class="icon-chevron-left"></i> Otros modos de pago </a>
    </p>

{/if}