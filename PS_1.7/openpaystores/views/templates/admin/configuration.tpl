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
        <a href="http://www.openpay.mx" target="_blank" rel="external"><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/openpay-logo.png" alt="Openpay logo" class="openpay-logo" /></a>
        <span class="openpay-module-intro">{l s='Comienza ha aceptar pagos con tarjetas de crédito-débito hoy mismo con Openpay.' mod='openpaystores'}</span>
        <a href="https://sandbox-dashboard.openpay.mx/merchant/production" rel="external" target="_blank" class="openpay-module-create-btn"><span>{l s='Crea una cuenta' mod='openpaystores'}</span></a>
    </div>
    <div class="openpay-module-wrap">
        <div class="openpay-module-col1 floatRight">
            <div class="openpay-module-wrap-video">
                <h3>{l s='Panel de administración' mod='openpaystores'}</h3>
                <p>{l s='Contamos con un panel donde podrás visualizar todas tus transacciones.' mod='openpaystores'}</p>
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
                    <h3>{l s='Beneficios' mod='openpaystores'}</h3>
                    <p>{l s='Avanzada infraestructura para soportar diferentes ecosistemas de pagos. Contamos con herramientas que nos ayudan a detectar y evitar fraudes en tiempo real.' mod='openpaystores'}</p>
                </div>
                <div class="col-md-5">
                    <h3>&nbsp;</h3>
                    <ul>
                        <li>{l s='Sin rentas mensuales' mod='openpaystores'}</li>
                        <li>{l s='Sin costos de integración' mod='openpaystores'}</li>
                        <li>{l s='Sin comisiones por configuración' mod='openpaystores'}</li>
                        <li>{l s='Sin plazos forzosos' mod='openpaystores'}</li>
                        <li>{l s='Sin cargos ocultos ni letras chiquitas' mod='openpaystores'}</li>
                    </ul>
                </div>
            </div>
            <hr>            
            <div class="openpay-module-col2inner">
                <div class="row">
                    <div class="col-md-7">
                        <h3>{l s='Acepta pagos en efectivo' mod='openpaystores'}</h3>
                        <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/stores.png" class="img-responsive">
                        <div>
                            <strong>
                                <a href="http://www.openpay.mx/tiendas-de-conveniencia.html" target="_blank" class="openpay-module-btn">
                                    {l s='Tiendas afiliadas' mod='openpaystores'}
                                </a>
                            </strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h3>{l s='Comisión por transacción exitosa' mod='openpaystores'}</h3>
                        <br>
                        <p class="comision">{l s='2.9% + $2.5 MXN' mod='openpaystores'}</p>
                    </div>
                </div>
            </div>            
        </div>
    </div>
    <fieldset>
        <legend><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/checks-icon.gif" alt="" />{l s='Chequeo técnico' mod='openpaystores'}</legend>
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
            <legend><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/technical-icon.gif" alt="" />{l s='Configuración' mod='openpaystores'}</legend>
            <label>Modo</label>
            <input type="radio" name="openpay_mode" value="0" {if $openpay_configuration.OPENPAY_MODE == 0} checked="checked"{/if} /> {l s='Sandbox' mod='openpaystores'}
            <input type="radio" name="openpay_mode" value="1" {if $openpay_configuration.OPENPAY_MODE == 1} checked="checked"{/if} /> {l s='Producción' mod='openpaystores'}
            <br /><br />
            <table cellspacing="0" cellpadding="0" class="openpay-settings">
                <tr>
                    <td align="center" valign="middle" colspan="2">
                        <table cellspacing="0" cellpadding="0" class="innerTable">
                            <tr>
                                <td align="left" valign="middle">{l s='Sandbox ID' mod='openpaystores'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="text" name="openpay_merchant_id_test" value="{if $openpay_configuration.OPENPAY_MERCHANT_ID_TEST}{$openpay_configuration.OPENPAY_MERCHANT_ID_TEST|escape:'htmlall':'UTF-8'}{/if}" /></td>
                                <td width="15"></td>
                                <td width="15" class="vertBorder"></td>
                                <td align="left" valign="middle">{l s='ID' mod='openpaystores'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="text" name="openpay_merchant_id_live" value="{if $openpay_configuration.OPENPAY_MERCHANT_ID_LIVE}{$openpay_configuration.OPENPAY_MERCHANT_ID_LIVE|escape:'htmlall':'UTF-8'}{/if}" /></td>
                            </tr>
                            <tr>
                                <td align="left" valign="middle">{l s='Sandbox llave privada' mod='openpaystores'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="password" name="openpay_private_key_test" value="{if $openpay_configuration.OPENPAY_PRIVATE_KEY_TEST}{$openpay_configuration.OPENPAY_PRIVATE_KEY_TEST|escape:'htmlall':'UTF-8'}{/if}" /></td>
                                <td width="15"></td>
                                <td width="15" class="vertBorder"></td>
                                <td align="left" valign="middle">{l s='Llave privada' mod='openpaystores'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="password" name="openpay_private_key_live" value="{if $openpay_configuration.OPENPAY_PRIVATE_KEY_LIVE}{$openpay_configuration.OPENPAY_PRIVATE_KEY_LIVE|escape:'htmlall':'UTF-8'}{/if}" /></td>
                            </tr>
                            <tr>
                                <td align="left" valign="middle">{l s='Sandbox llave pública' mod='openpaystores'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="text" name="openpay_public_key_test" value="{if $openpay_configuration.OPENPAY_PUBLIC_KEY_TEST}{$openpay_configuration.OPENPAY_PUBLIC_KEY_TEST|escape:'htmlall':'UTF-8'}{/if}" /></td>
                                <td width="15"></td>
                                <td width="15" class="vertBorder"></td>
                                <td align="left" valign="middle">{l s='Llave pública' mod='openpaystores'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="text" name="openpay_public_key_live" value="{if $openpay_configuration.OPENPAY_PUBLIC_KEY_LIVE}{$openpay_configuration.OPENPAY_PUBLIC_KEY_LIVE|escape:'htmlall':'UTF-8'}{/if}" /></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h3>{l s='URL de la Tienda' mod='openpaystores'}</h3>
                        <input type="text" name="openpay_webhook_url" placeholder="{l s='URL' mod='openpaystores'}" value="{$openpay_configuration.OPENPAY_WEBHOOK_URL|escape:'htmlall':'UTF-8'}" style="width: 100%;">
                        <small>{l s="Es importante mantener este valor actualizado si cambias de dominio o subdominio." mod='openpaystores'}</small>
                    </td>
                </tr> 
                <tr>
                    <td colspan="2">
                        <h3>{l s='Tiempo límite para pago (hrs.)' mod='openpaystores'}</h3>                        
                        <input type="text" name="openpay_deadline_stores" placeholder="{l s='Horas' mod='openpaystores'}" value="{if $openpay_configuration.OPENPAY_DEADLINE_STORES}{$openpay_configuration.OPENPAY_DEADLINE_STORES|escape:'htmlall':'UTF-8'}{/if}" style="width: 100%;">
                        <small>{l s="Ingrear el número de horas en que el usuario tendrá como máximo para realizar su pago una vez generada la orden de compra." mod='openpaystores'}</small>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">                        
                        <label style="">{l s="Mostrar mapa" mod='openpayprestashop'}</label>                                          
                        <select name="show_map" id="show_map" style="width: 100%; margin: 10px 0 0 0;">
                            <option value="0" {if $openpay_configuration.OPENPAY_SHOW_MAP == '0'} selected="selected"{/if}>NO</option>
                            <option value="1" {if $openpay_configuration.OPENPAY_SHOW_MAP == '1'} selected="selected"{/if}>SI</option>                            
                        </select>
                        <div><small>Al selccionar esta opción, un mapa se desplegará mostrando las tiendas más cercanas al momento mostrar el recipo de pago (https://www.openpay.mx/docs/stores-map.html).</small></div>                        
                    </td>
                </tr> 
                <tr>
                    <td colspan="2" class="td-noborder save"><input type="submit" class="button" name="SubmitOpenpay" value="{l s='Guardar configuración' mod='openpaystores'}" /></td>
                </tr>
            </table>
        </fieldset>        
    </form>
    <div class="clear"></div>

</div>
