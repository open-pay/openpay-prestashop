{*
* Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

{if $country == "MX"}
<div id="spei-container" class="payment_module" >
    <div class="openpay-form-container">
        <div class="row mt30">
            <div class="col-md-4 store-image">
                <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/spei.png">
            </div>
            <div class="col-md-8">
                <h4 class="subtitle">{l s='¿Qué es SPEI?' mod='openpaybanks'}</h4>
                <p style="font-size: 14px; color:#000; font-weight:normal;">
                    {l s='El SPEI es un sistema de pagos para permitir a los clientes de los bancos enviar y recibir transferencias electrónicas de dinero en cuestión de segundos.' mod='openpaybanks'}
                    <small><a class="blue_link" target="_blank" href="http://www.openpay.mx/bancos.html">{l s='Bancos soportados.' mod='openpaybanks'}</a></small>
                </p>
            </div>
        </div>

        <h4 class="subtitle blue_subtitle mt30 mb30">{l s='Pasos para tu pago por transferencia interbancaria' mod='openpaybanks'}</h4>

        <div class="row mb30 steps">
            <div class="col-md-4 center">
                <img src="https://img.openpay.mx/plugins/file.svg">
                <p>{l s='Haz clic en el botón de "Realizar Pedido", donde tu compra quedará en espera de que realices tu pago..' mod='openpaybanks'}</p>
            </div>
            <div class="col-md-4 center">
                <img src="https://img.openpay.mx/plugins/spei.svg">
                <p>{l s='Sigue la guía para realizar el pago SPEI a través del portal de tu banco.' mod='openpaybanks'}</p>
            </div>
            <div class="col-md-4 center">
                <img src="https://img.openpay.mx/plugins/mail.svg">
                <p>{l s='Inmediatamente después de recibir tu pago te enviaremos un correo electrónico con la confirmación de pago.' mod='openpaybanks'}</p>
            </div>
        </div>
    </div>
</div>
{else}
<div id="spei-container" class="payment_module" >
    <div class="openpay-form-container">
        <div class="row mt30">
            <div class="col-md-4 store-image pse_logo">
                <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/logo_pse.png">
            </div>
            <div class="col-md-8">
                <h4 class="subtitle">{l s='¿Qué es PSE?' mod='openpaybanks'}</h4>
                <p style="font-size: 14px;">
                    {l s='PSE permite a los clientes realizar pagos referenciados desde su banco e identifícalos en tiempo real.' mod='openpaybanks'}
                </p>
            </div>
        </div>

    </div>
</div>
{/if}