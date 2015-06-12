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

<div id="store-container" class="payment_module {if $card == 1} hidden {/if}" >
    <div class="openpay-form-container">
        <h3 class="openpay_title mt30">Pago en efectivo en tiendas de conveniencia</h3>
        <div class="row">
            <div class="col-md-8 store-image">
                <img src="{$module_dir}views/img/stores.png">
            </div>
        </div>
        <div><small><a target="_blank" href="http://www.openpay.mx/tiendas-de-conveniencia.html">Consulta las tiendas afiliadas</a></small></div>

        <h4 class="subtitle mt30 mb30">Pasos para tu pago por tienda</h4>

        <div class="row mb30">
            <div class="col-md-4 center">
                <div><img src="{$module_dir}views/img/step1.png"></div>
                <p>Haz clic en el botón "Generar ficha de pago", donde tu compra quedará en espera de que realices tu pago.</p>
            </div>
            <div class="col-md-4 center">
                <div><img src="{$module_dir}views/img/step_store.png"></div>
                <p>Imprime tu recibo, llévalo a tu tienda de conveniencia más cercana y realiza el pago.</p>
            </div>
            <div class="col-md-4 center">
                <div><img src="{$module_dir}views/img/step3.png"></div>
                <p>Inmediatamente después de recibir tu pago te enviaremos un correo electrónico con la confirmación de pago.</p>
            </div>
        </div>

        <br>
        <form data-ajax="false" action="{$validation_url}" method="POST" id="openpay-cash-form">
            <input type="hidden" name="payment_method" value="store" id="payment_method">
            <div class="row">
                <div class="pull-right">
                    <span style="font-weight: normal; font-size: 14px; margin-right: 10px;">
                        {if $amount >= '10000'} Forma de pago no permitida para montos superiores a los $10,000 {/if}
                    </span>
                    <input type="submit" value="{l s='Generar ficha de pago' mod='openpayprestashop'}" class="{if $openpay_ps_version >= '1.5'}openpay-submit-button {/if}exclusive" data-icon="check" data-iconpos="right" data-theme="b" {if $amount >= '10000'} disabled style="cursor: not-allowed;" {/if} />
                </div>
            </div>
        </form>
    </div>
</div>