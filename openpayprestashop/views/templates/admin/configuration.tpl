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

<div class="openpay-module-wrapper">

    <div class="openpay-module-header">
        <a href="http://www.openpay.mx" target="_blank" rel="external"><img src="/modules/openpayprestashop/views/img/openpay-logo.png" alt="openpay" class="openpay-logo" /></a>
        <span class="openpay-module-intro">Comienza a recibir pagos con tarjeta, pagos en efectivo en tiendas y pagos vía SPEI hoy mismo con Openpay.</span>
        <a href="https://sandbox-dashboard.openpay.mx/merchant/production" rel="external" target="_blank" class="openpay-module-create-btn"><span>Crea una Cuenta</span></a>
    </div>
    <div class="openpay-module-wrap">
        <div class="openpay-module-col1 floatRight">
            <div class="openpay-module-wrap-video">
                <h3>Panel de administración</h3>
                <p>Contamos con un panel de administración donde podrás visualizar las diferentes transacciones que procese tu negocio.</p>
                <a target="_blank" href="http://www.openpay.mx"><img src="/modules/openpayprestashop/views/img/openpay-dashboard.png" alt="openpay dashboard" class="openpay-dashboard" /></a>
                <hr>
                <div class="openpay-prestashop-partner mt30">
                    <img src="/modules/openpayprestashop/views/img/prestashop_partner.png" alt="" />
                </div>
            </div>
        </div>
        <div class="openpay-module-col2">
            <div class="row">
                <div class="col-md-6">
                     <h3>Beneficios</h3>
                    <p>Openpay ofrece una estructura sencilla de costos todo incluido para todo tipo de soluciones e-commerce o m-commerce.</p>
                </div>
                <div class="col-md-5">
                    <h3>&nbsp;</h3>
                    <ul>
                        <li>Sin renta mensual</li>
                        <li>Sin costos de integración</li>
                        <li>Sin comisiones de configuración</li>
                        <li>Sin plazos forzosos</li>
                        <li>Sin cargos ocultos ni letras chiquitas</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="openpay-module-col2inner">
                <div class="row">
                    <div class="col-md-7">
                        <h3>Acepta pagos con tarjetas de crédito</h3>
                        <div class="row">
                            {for $i=1 to 4}
                                <div class="col-xs-2 store-image">
                                    <img src="/modules/openpayprestashop/views/img/credit_cards/{sprintf("%02d", $i|escape:'htmlall':'UTF-8')}.png">
                                </div>
                            {/for}
                        </div>
                        <br><br>
                        <h3>Acepta pagos con tarjetas de débito</h3>
                        <div class="row">
                            {for $i=1 to 4}
                                <div class="col-xs-2 store-image">
                                    <img src="/modules/openpayprestashop/views/img/debit_cards/{sprintf("%02d", $i|escape:'htmlall':'UTF-8')}.png">
                                </div>
                            {/for}
                        </div>
                        <div>
                            <strong><a href="http://www.openpay.mx/tarjetas.html" target="_blank" class="openpay-module-btn">Consulta las tarjetas soportadas</a></strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h3>Comisión por cargo exitoso: Visa y MasterCard</h3>
                        <p class="comision">2.9% + $2.5 MXN</p>
                        <br><br>
                        <h3>Comisión por cargo exitoso: American Express</h3>
                        <p class="comision">4.5% + $2.5 MXN</p>


                    </div>
                </div>
            </div>
            <hr>
            <div class="openpay-module-col2inner">
                <div class="row">
                    <div class="col-md-7">
                        <h3>Acepta pagos en efectivo en tiendas</h3>
                        {for $i=1 to 4}
                            <div class="col-xs-2 store-image">
                                <img src="/modules/openpayprestashop/views/img/stores/{sprintf("%02d", $i|escape:'htmlall':'UTF-8')}.png">
                            </div>
                        {/for}
                        <div>
                            <strong>
                                <a href="http://www.openpay.mx/tiendas-de-conveniencia.html" target="_blank" class="openpay-module-btn">
                                    Consulta las tiendas afiliadas
                                </a>
                            </strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h3>Comisión por cargo exitoso</h3>
                        <p class="comision">2.9% + $2.5 MXN</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="openpay-module-col2inner">

                <div class="row">
                    <div class="col-md-7">
                        <h3>Acepta pagos con transferencia electrónica (SPEI)</h3>
                        <div class="row">
                            <div class="col-md-6 store-image">
                                <img class="openpay-cc" alt="openpay" src="/modules/openpayprestashop/views/img/spei.png">
                            </div>
                        </div>
                        <div>
                            <strong>
                                <a href="http://www.openpay.mx/bancos.html" target="_blank" class="openpay-module-btn">
                                    Consulta los bancos soportados
                                </a>
                            </strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h3>Comisión por cargo exitoso</h3>
                        <p class="comision">$8 MXN</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <fieldset>
        <legend><img src="/modules/openpayprestashop/views/img/checks-icon.gif" alt="" />Chequeos Técnicos</legend>
        <div class="conf">{$openpay_validation_title|escape:'htmlall':'UTF-8'}</div>
        <table cellspacing="0" cellpadding="0" class="openpay-technical">
            {if $openpay_validation}
                {foreach from=$openpay_validation item=validation}
                    {html_entity_decode($validation|escape:'htmlall':'UTF-8')}
                {/foreach}

            {/if}
        </table>
    </fieldset>
    <br />

    {if $openpay_error}
        <fieldset>
            <legend>Errors</legend>
            <table cellspacing="0" cellpadding="0" class="openpay-technical">
                <tbody>
                    {foreach from=$openpay_error item=error}
                        <tr>
                            <td><img src="../img/admin/forbbiden.gif" alt=""></td>
                            <td>{$error|escape:'htmlall':'UTF-8'}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </fieldset>
        <br />
    {/if}

    <form action="{$openpay_form_link|escape:'htmlall':'UTF-8'}" method="post">
        <fieldset class="openpay-settings">
            <legend><img src="/modules/openpayprestashop/views/img/technical-icon.gif" alt="" />Configuraciones</legend>
            <label>Modo</label>
            <input type="radio" name="openpay_mode" value="0" {if $openpay_configuration.OPENPAY_MODE == 0} checked="checked"{/if} /> Sandbox
            <input type="radio" name="openpay_mode" value="1" {if $openpay_configuration.OPENPAY_MODE == 1} checked="checked"{/if} /> Producción
            <br /><br />
            <table cellspacing="0" cellpadding="0" class="openpay-settings">
                <tr>
                    <td align="center" valign="middle" colspan="2">
                        <table cellspacing="0" cellpadding="0" class="innerTable">
                            <tr>
                                <td align="left" valign="middle">Merchant ID sandbox</td>
                                <td align="left" valign="middle"><input type="text" name="openpay_merchant_id_test" value="{if $openpay_configuration.OPENPAY_MERCHANT_ID_TEST}{$openpay_configuration.OPENPAY_MERCHANT_ID_TEST|escape:'htmlall':'UTF-8'}{/if}" /></td>
                                <td width="15"></td>
                                <td width="15" class="vertBorder"></td>
                                <td align="left" valign="middle">Merchant ID producción</td>
                                <td align="left" valign="middle"><input type="text" name="openpay_merchant_id_live" value="{if $openpay_configuration.OPENPAY_MERCHANT_ID_LIVE}{$openpay_configuration.OPENPAY_MERCHANT_ID_LIVE|escape:'htmlall':'UTF-8'}{/if}" /></td>
                            </tr>
                            <tr>
                                <td align="left" valign="middle">Llave privada sandbox</td>
                                <td align="left" valign="middle"><input type="password" name="openpay_private_key_test" value="{if $openpay_configuration.OPENPAY_PRIVATE_KEY_TEST}{$openpay_configuration.OPENPAY_PRIVATE_KEY_TEST|escape:'htmlall':'UTF-8'}{/if}" /></td>
                                <td width="15"></td>
                                <td width="15" class="vertBorder"></td>
                                <td align="left" valign="middle">Llave privada producción</td>
                                <td align="left" valign="middle"><input type="password" name="openpay_private_key_live" value="{if $openpay_configuration.OPENPAY_PRIVATE_KEY_LIVE}{$openpay_configuration.OPENPAY_PRIVATE_KEY_LIVE|escape:'htmlall':'UTF-8'}{/if}" /></td>
                            </tr>
                            <tr>
                                <td align="left" valign="middle">Llave pública sandbox</td>
                                <td align="left" valign="middle"><input type="text" name="openpay_public_key_test" value="{if $openpay_configuration.OPENPAY_PUBLIC_KEY_TEST}{$openpay_configuration.OPENPAY_PUBLIC_KEY_TEST|escape:'htmlall':'UTF-8'}{/if}" /></td>
                                <td width="15"></td>
                                <td width="15" class="vertBorder"></td>
                                <td align="left" valign="middle">Llave pública producción</td>
                                <td align="left" valign="middle"><input type="text" name="openpay_public_key_live" value="{if $openpay_configuration.OPENPAY_PUBLIC_KEY_LIVE}{$openpay_configuration.OPENPAY_PUBLIC_KEY_LIVE|escape:'htmlall':'UTF-8'}{/if}" /></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <h3>Formas de pago aceptadas</h3>
                        <table cellspacing="0" cellpadding="0" class="innerTable">
                            <tr>
                                <td align="left" valign="middle">
                                    <input type="checkbox" name="openpay_cards" id="openpay_cards" value="1" {if $openpay_configuration.OPENPAY_CARDS == 1} checked="checked"{/if}> Tarjeta de crédito/débito
                                </td>
                                <td align="left" valign="middle">
                                    <input type="checkbox" name="openpay_stores" id="openpay_stores" value="1" {if $openpay_configuration.OPENPAY_STORES == 1} checked="checked"{/if}> Pago en tiendas
                                </td>
                                <td align="left" valign="middle">
                                    <input type="checkbox" name="openpay_spei" id="openpay_spei" value="1" {if $openpay_configuration.OPENPAY_SPEI == 1} checked="checked" {/if}> Transferencia bancaria
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <h3>Tiempo límite para recibir pagos</h3>
                        <table cellspacing="0" cellpadding="0" class="innerTable">
                            <tr>
                                <td valign="middle" align="left">Pago en tiendas de conveniencia (hrs.)</td>
                                <td valign="middle" align="left">
                                    <input type="text" name="openpay_deadline_stores" placeholder="Horas" value="{if $openpay_configuration.OPENPAY_DEADLINE_STORES}{$openpay_configuration.OPENPAY_DEADLINE_STORES|escape:'htmlall':'UTF-8'}{/if}">
                                </td>
                                <td width="15"></td>
                                <td class="vertBorder" width="15"></td>
                                <td valign="middle" align="left">Transferencias interbancarias (hrs.)</td>
                                <td valign="middle" align="left">
                                    <input type="text" name="openpay_deadline_spei" placeholder="Horas" value="{if $openpay_configuration.OPENPAY_DEADLINE_SPEI}{$openpay_configuration.OPENPAY_DEADLINE_SPEI|escape:'htmlall':'UTF-8'}{/if}">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <h3>Diseño de recibos de pago (<a title="Ver recibo de pago" href="{$receipt|escape:'htmlall':'UTF-8'}" target="_blank"><i class="icon-question"></i></a>)</h3>
                        <table cellspacing="0" cellpadding="0" class="innerTable">
                            <tr>
                                <td valign="middle" align="left">Color de cintillos y fondos</td>
                                <td valign="middle" align="left">
                                    <input type="text" name="openpay_background_color" placeholder="Color en hexadecimal" value="{if $openpay_configuration.OPENPAY_BACKGROUND_COLOR}{$openpay_configuration.OPENPAY_BACKGROUND_COLOR|escape:'htmlall':'UTF-8'}{/if}">
                                </td>
                                <td width="15"></td>
                                <td class="vertBorder" width="15"></td>
                                <td valign="middle" align="left">Color de letra en fondos</td>
                                <td valign="middle" align="left">
                                    <input type="text" name="openpay_font_color" placeholder="Color en hexadecimal" value="{if $openpay_configuration.OPENPAY_FONT_COLOR}{$openpay_configuration.OPENPAY_FONT_COLOR|escape:'htmlall':'UTF-8'}{/if}">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" class="td-noborder save"><input type="submit" class="button" name="SubmitOpenpay" value="Guardar configuración" /></td>
                </tr>
            </table>
        </fieldset>
        <fieldset class="openpay-cc-numbers">
            <legend><img src="/modules/openpayprestashop/views/img/cc-icon.gif" alt="" />Números de tarjetas de prueba</legend>
            <table cellspacing="0" cellpadding="0" class="openpay-cc-numbers">
                <thead>
                    <tr>
                        <th>Numero</th>
                        <th>Tipo de tarjeta</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td class="number"><code>4111111111111111</code></td><td>Visa</td></tr>
                    <tr><td class="number"><code>4242424242424242</code></td><td>Visa</td></tr>
                    <tr><td class="number"><code>5555555555554444</code></td><td>MasterCard</td></tr>
                    <tr><td class="number"><code>5105105105105100</code></td><td>MasterCard</td></tr>
                    <tr><td class="number"><code>345678000000007</code></td><td>American Express</td></tr>
                    <tr><td class="number"><code>343434343434343</code></td><td>American Express</td></tr>
                    <tr><td class="number"><code>4222222222222220</code></td><td>La tarjeta fue rechazada</td></tr>
                    <tr><td class="number"><code>4000000000000069</code></td><td>La tarjeta ha expirado</td></tr>
                    <tr><td class="number"><code>4444444444444448</code></td><td>La tarjeta no tiene fondos suficientes</td></tr>
                </tbody>
            </table>
        </fieldset>
    </form>
    <div class="clear"></div>

</div>

<script type="text/javascript">
$(document).ready(function() {

    $('input:radio[name=openpay_mode]').click(function() {
        updateOpenpaySettings();
    });

});

function updateOpenpaySettings()
{
    if ($('input:radio[name=openpay_mode]:checked').val() == 1)
        $('fieldset.openpay-cc-numbers').hide(1000);
    else
        $('fieldset.openpay-cc-numbers').show(1000);
}

</script>