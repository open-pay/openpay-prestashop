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

<div class="openpay-module-wrapper">
    {*################################ SPEI INFORMATION PANEL ###############################*}
    {if $openpay_configuration.COUNTRY == 'MX'}
    <div class="openpay-module-header">
        <a href="http://www.openpay.mx" target="_blank" rel="external"><img src="https://img.openpay.mx/plugins/openpay_logo.svg" alt="Openpay logo" class="openpay-logo" /></a>
        <span class="openpay-module-intro">{l s='Comienza ha aceptar pagos con tarjetas de crédito-débito hoy mismo con Openpay.' mod='openpaybanks'}</span>
        <a href="https://sandbox-dashboard.openpay.mx/merchant/production" rel="external" target="_blank" class="openpay-module-create-btn"><span>{l s='Crea una cuenta' mod='openpaybanks'}</span></a>
    </div>
    <div class="openpay-module-wrap">
        <div class="openpay-module-col1 floatRight">
            <div class="openpay-module-wrap-video">
                <h3>{l s='Panel de administración' mod='openpaybanks'}</h3>
                <p>{l s='Contamos con un panel donde podrás visualizar todas tus transacciones.' mod='openpaybanks'}</p>
                <a target="_blank" href="http://www.openpay.mx"><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/openpay-dashboard.png" alt="openpay dashboard" class="openpay-dashboard" /></a>
                <hr>
                <div class="openpay-prestashop-partner mt30">
                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/prestashop_partner.png" alt="" />
                </div>
            </div>
        </div>
        <div class="openpay-module-col2">
            <div class="row">
                <div class="col-md-6">
                    <h3>{l s='Beneficios' mod='openpaybanks'}</h3>
                    <p>{l s='Avanzada infraestructura para soportar diferentes ecosistemas de pagos. Contamos con herramientas que nos ayudan a detectar y evitar fraudes en tiempo real.' mod='openpaybanks'}</p>
                </div>
                <div class="col-md-5">
                    <h3>&nbsp;</h3>
                    <ul>
                        <li>{l s='Sin rentas mensuales' mod='openpaybanks'}</li>
                        <li>{l s='Sin costos de integración' mod='openpaybanks'}</li>
                        <li>{l s='Sin comisiones por configuración' mod='openpaybanks'}</li>
                        <li>{l s='Sin plazos forzosos' mod='openpaybanks'}</li>
                        <li>{l s='Sin cargos ocultos ni letras chiquitas' mod='openpaybanks'}</li>
                    </ul>
                </div>
            </div>
            <hr>         
            <div class="openpay-module-col2inner">

                <div class="row">
                    <div class="col-md-7">
                        <h3>{l s='Accept bank payments (SPEI)' mod='openpaybanks'}</h3>
                        <div class="row">
                            <div class="col-md-6 store-image">
                                <img class="openpay-cc" alt="openpay" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/spei.png">
                            </div>
                        </div>
                        <div>
                            <strong>
                                <a href="http://www.openpay.mx/bancos.html" target="_blank" class="openpay-module-btn">
                                    {l s='Bancos soportados' mod='openpaybanks'}
                                </a>
                            </strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h3>{l s='Comisión por transacción exitosa' mod='openpaybanks'}</h3>
                        <p class="comision">{l s='$8 MXN' mod='openpaybanks'}</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
    {/if}
    {*################################ END SPEI INFORMATION PANEL ###############################*}

    {*################################ PSE INFORMATION PANEL ###############################*}
    {if $openpay_configuration.COUNTRY == 'CO'}
    <div class="openpay-module-header">
        <a href="http://www.openpay.co" target="_blank" rel="external"><img src="https://img.openpay.mx/plugins/openpay_logo.svg" alt="Openpay logo" class="openpay-logo" /></a>
        <span class="openpay-module-intro">{l s='Comienza ha aceptar pagos con tarjetas de crédito-débito hoy mismo con Openpay.' mod='openpaybanks'}</span>
        <a href="https://sandbox-dashboard.openpay.co/login/register" rel="external" target="_blank" class="openpay-module-create-btn"><span>{l s='Crea una cuenta' mod='openpaybanks'}</span></a>
    </div>
    <div class="openpay-module-wrap">
        <div class="openpay-module-col1 floatRight">
            <div class="openpay-module-wrap-video">
                <h3>{l s='Panel de administración' mod='openpaybanks'}</h3>
                <p>{l s='Contamos con un panel donde podrás visualizar todas tus transacciones.' mod='openpaybanks'}</p>
                <a target="_blank" href="http://www.openpay.mx"><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/openpay-dashboard.png" alt="openpay dashboard" class="openpay-dashboard" /></a>
                <hr>
                <div class="openpay-prestashop-partner mt30">
                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/prestashop_partner.png" alt="" />
                </div>
            </div>
        </div>
        <div class="openpay-module-col2">
            <div class="row">
                <div class="col-md-6">
                    <h3>{l s='Beneficios' mod='openpaybanks'}</h3>
                    <p>{l s='Avanzada infraestructura para soportar diferentes ecosistemas de pagos. Contamos con herramientas que nos ayudan a detectar y evitar fraudes en tiempo real.' mod='openpaybanks'}</p>
                </div>
                <div class="col-md-5">
                    <h3>&nbsp;</h3>
                    <ul>
                        <li>{l s='Sin rentas mensuales' mod='openpaybanks'}</li>
                        <li>{l s='Sin costos de integración' mod='openpaybanks'}</li>
                        <li>{l s='Sin comisiones por configuración' mod='openpaybanks'}</li>
                        <li>{l s='Sin plazos forzosos' mod='openpaybanks'}</li>
                        <li>{l s='Sin cargos ocultos ni letras chiquitas' mod='openpaybanks'}</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="openpay-module-col2inner">

                <div class="row">
                    <div class="col-md-7">
                        <h3>{l s='Accept bank payments (PSE)' mod='openpaybanks'}</h3>
                        <div class="row">
                            <div class="col-md-6 pse-image">
                                <img class="openpay-cc" alt="openpay" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/logo_pse.png">
                            </div>
                        </div>
                        <div>
                            <strong>
                                <a href="https://www.openpay.co/boton-de-pago-pse/" target="_blank" class="openpay-module-btn">
                                    {l s='Información PSE' mod='openpaybanks'}
                                </a>
                            </strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h3>{l s='Costos para clientes con cuentas de otros bancos' mod='openpaybanks'}</h3>
                        <p class="comision">{l s='2.90% + $900 COP' mod='openpaybanks'}</p>
                        <br><br>
                        <h3>{l s='Costos para clientes BBVA' mod='openpaybanks'}</h3>
                        <p class="comision">{l s='2.70% + $900 COP' mod='openpaybanks'}</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
    {/if}
    {*################################ END PSE INFORMATION PANEL ###############################*}
    <fieldset>
        <legend><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/checks-icon.gif" alt="" />{l s='Chequeo técnico' mod='openpaybanks'}</legend>
        <div class="conf">{$openpay_validation_title|escape:'htmlall':'UTF-8'}</div>
        <table cellspacing="0" cellpadding="0" class="openpay-technical">
            {if $openpay_validation}
                {foreach from=$openpay_validation item=validation}
                    <tr>
                        <td>
                            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/{($validation['result']|escape:'htmlall':'UTF-8') ? 'tick' : 'close'}.png" alt="" style="height: 25px;" />
                        </td>
                        <td>
                            {$validation['name']|escape:'htmlall':'UTF-8'}
                        </td>
                    </tr>
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
                            <td><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/close.png" alt="" style="height: 25px;"></td>
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
            <legend><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/technical-icon.gif" alt="" />{l s='Configuración' mod='openpaybanks'}</legend>
            <label>Modo</label>
            <input type="radio" name="openpay_mode" value="0" {if $openpay_configuration.OPENPAY_MODE == 0} checked="checked"{/if} /> {l s='Sandbox' mod='openpaybanks'}
            <input type="radio" name="openpay_mode" value="1" {if $openpay_configuration.OPENPAY_MODE == 1} checked="checked"{/if} /> {l s='Producción' mod='openpaybanks'}
            <br /><br />
            <table cellspacing="0" cellpadding="0" class="openpay-settings">
                <tr>
                    <td align="center" valign="middle" colspan="2">
                        <table cellspacing="0" cellpadding="0" class="innerTable">
                            <tr>
                                <td align="left" valign="middle">{l s='Sandbox ID' mod='openpaybanks'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="text" name="openpay_merchant_id_test" value="{if $openpay_configuration.SANDBOX_MERCHANT_ID}{$openpay_configuration.SANDBOX_MERCHANT_ID|escape:'htmlall':'UTF-8'}{/if}" /></td>
                                <td width="15"></td>
                                <td width="15" class="vertBorder"></td>
                                <td align="left" valign="middle">{l s='ID' mod='openpaybanks'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="text" name="openpay_merchant_id_live" value="{if $openpay_configuration.LIVE_MERCHANT_ID}{$openpay_configuration.LIVE_MERCHANT_ID|escape:'htmlall':'UTF-8'}{/if}" /></td>
                            </tr>
                            <tr>
                                <td align="left" valign="middle">{l s='Sandbox llave privada' mod='openpaybanks'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="password" name="openpay_private_key_test" value="{if $openpay_configuration.SANDBOX_SK}{$openpay_configuration.SANDBOX_SK|escape:'htmlall':'UTF-8'}{/if}" /></td>
                                <td width="15"></td>
                                <td width="15" class="vertBorder"></td>
                                <td align="left" valign="middle">{l s='Llave privada' mod='openpaybanks'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="password" name="openpay_private_key_live" value="{if $openpay_configuration.LIVE_SK}{$openpay_configuration.LIVE_SK|escape:'htmlall':'UTF-8'}{/if}" /></td>
                            </tr>
                            <tr>
                                <td align="left" valign="middle">{l s='Sandbox llave pública' mod='openpaybanks'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="text" name="openpay_public_key_test" value="{if $openpay_configuration.SANDBOX_PK}{$openpay_configuration.SANDBOX_PK|escape:'htmlall':'UTF-8'}{/if}" /></td>
                                <td width="15"></td>
                                <td width="15" class="vertBorder"></td>
                                <td align="left" valign="middle">{l s='Llave pública' mod='openpaybanks'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="text" name="openpay_public_key_live" value="{if $openpay_configuration.LIVE_PK}{$openpay_configuration.LIVE_PK|escape:'htmlall':'UTF-8'}{/if}" /></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <label style="">{l s="País" mod='openpaybanks'}</label>
                        <select name="openpay_country" id="country" style="width: 100%; margin: 10px 0 0 0;">
                            <option value="MX" {if $openpay_configuration.COUNTRY == 'MX'} selected="selected"{/if}>México</option>
                            <option value="CO" {if $openpay_configuration.COUNTRY == 'CO'} selected="selected"{/if}>Colombia</option>
                        </select>
                        <div><small>* Seleccionar el país </small></div>
                    </td>
                </tr>
                {if $openpay_configuration.COUNTRY == 'CO'}
                <tr>
                    <td>
                        <label style="">{l s="IVA" mod='openpaybanks'}</label>
                        <input type="text" autocomplete="off" type="text" name="openpay_pse_iva" id="openpay_pse_iva" value="{if $openpay_configuration.PSE_IVA}{$openpay_configuration.PSE_IVA|escape:'htmlall':'UTF-8'}{/if}" style="width: 100%; margin: 5px 0 0 0;">
                        <div><small>* Debe contener el valor de IVA, es campo solo informativo, no tiene ningún efecto sobre el campo amount.</small></div>
                    </td>
                </tr>
                {/if}
                <tr>
                    <td>
                        <h3>{l s='URL de la Tienda' mod='openpaybanks'}</h3>
                        <input type="text" name="openpay_webhook_url" placeholder="{l s='URL' mod='openpaybanks'}" value="{$openpay_configuration.WEBHOOK_URL|escape:'htmlall':'UTF-8'}" style="width: 100%;">
                        <small>{l s="Es importante mantener este valor actualizado si cambias de dominio o subdominio." mod='openpaybanks'}</small>
                    </td>
                </tr>
                {if $openpay_configuration.COUNTRY == 'MX'}
                <tr>
                    <td colspan="2">
                        <h3>{l s='Tiempo límite para pago (hrs.)' mod='openpaybanks'}</h3>
                        <input type="text" name="openpay_deadline_spei" placeholder="{l s='Hours' mod='openpaybanks'}" value="{if $openpay_configuration.PAYMENT_ORDER_DEADLINE}{$openpay_configuration.PAYMENT_ORDER_DEADLINE|escape:'htmlall':'UTF-8'}{/if}" style="width: 100%;">
                        <small>{l s="Ingrear el número de horas en que el usuario tendrá como máximo para realizar su pago una vez generada la orden de compra. Valor predeterminado: 72 horas." mod='openpaybanks'}</small>
                    </td>
                </tr>
                {/if}
                <tr>
                    <td colspan="2" class="td-noborder save"><input type="submit" class="button" name="SubmitOpenpay" value="{l s='Guardar configuración' mod='openpaybanks'}" /></td>
                </tr>
            </table>
        </fieldset>        
    </form>
    <div class="clear"></div>

</div>
