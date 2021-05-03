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
        <span class="openpay-module-intro">{l s='Realiza transacciones de pago sin margen de error para el depósito.' mod='openpaycodi'}</span>
        <a href="https://sandbox-dashboard.openpay.mx/merchant/production" rel="external" target="_blank" class="openpay-module-create-btn"><span>{l s='Crea una cuenta' mod='openpaycodi'}</span></a>
    </div>
    <div class="openpay-module-wrap">
        <div class="openpay-module-col1 floatRight">
            <div class="openpay-module-wrap-video">
                <h3>{l s='Panel de administración' mod='openpaycodi'}</h3>
                <p>{l s='Contamos con un panel donde podrás visualizar todas tus transacciones.' mod='openpaycodi'}</p>
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
                    <h3>{l s='Beneficios' mod='openpaycodi'}</h3>
                    <p>{l s='Los pagos a través de CoDi® son transacciones seguras, eficientes y sin riesgo de fraude que se reflejan en unos pocos segundos en tu cuenta bancaria.' mod='openpaycodi'}</p>
                </div>
                <div class="col-md-5">
                    <h3>&nbsp;</h3>
                    <ul>
                        <li>{l s='Sin contracargos.' mod='openpaycodi'}</li>
                        <li>{l s='Es fácil de usar para ti y tus clientes.' mod='openpaycodi'}</li>
                        <li>{l s='Velocidad para enviar solicitud de cobro y recibir pagos.' mod='openpaycodi'}</li>
                    </ul>
                </div>
            </div>
            <hr>         
            <div class="openpay-module-col2inner">

                <div class="row">
                    <div class="col-md-7">
                        <h3>{l s='Pagos con CoDi®' mod='openpaycodi'}</h3>
                        <p>CoDi® es una plataforma desarrollada por Banco de México que permite hacer cobros a través de un código QR desde un celular.</p>
                        <div class="codi-info">
                            <h3>{l s='Costos e integración' mod='openpaycodi'}</h3>
                            <a class="comision" href="https://site.openpay.mx/codi/" target="_blank">{l s='Documentación Oficial Openpay' mod='openpaycodi'}</a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="codi-image">
                            <img class="openpay-cc" alt="openpay" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/codi/QR.jpg">
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <fieldset>
        <legend><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/checks-icon.gif" alt="" />{l s='Chequeo técnico' mod='openpaycodi'}</legend>
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
            <legend><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/technical-icon.gif" alt="" />{l s='Configuración' mod='openpaycodi'}</legend>
            <label>Modo</label>
            <input type="radio" name="openpay_mode" value="0" {if $openpay_configuration.OPENPAY_MODE == 0} checked="checked"{/if} /> {l s='Sandbox' mod='openpaycodi'}
            <input type="radio" name="openpay_mode" value="1" {if $openpay_configuration.OPENPAY_MODE == 1} checked="checked"{/if} /> {l s='Producción' mod='openpaycodi'}
            <br /><br />
            <table cellspacing="0" cellpadding="0" class="openpay-settings">
                <tr>
                    <td align="center" valign="middle" colspan="2">
                        <table cellspacing="0" cellpadding="0" class="innerTable">
                            <tr>
                                <td align="left" valign="middle">{l s='Sandbox ID' mod='openpaycodi'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="text" name="openpay_merchant_id_test" value="{if $openpay_configuration.OPENPAY_MERCHANT_ID_TEST}{$openpay_configuration.OPENPAY_MERCHANT_ID_TEST|escape:'htmlall':'UTF-8'}{/if}" /></td>
                                <td width="15"></td>
                                <td width="15" class="vertBorder"></td>
                                <td align="left" valign="middle">{l s='ID' mod='openpaycodi'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="text" name="openpay_merchant_id_live" value="{if $openpay_configuration.OPENPAY_MERCHANT_ID_LIVE}{$openpay_configuration.OPENPAY_MERCHANT_ID_LIVE|escape:'htmlall':'UTF-8'}{/if}" /></td>
                            </tr>
                            <tr>
                                <td align="left" valign="middle">{l s='Sandbox llave privada' mod='openpaycodi'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="password" name="openpay_private_key_test" value="{if $openpay_configuration.OPENPAY_PRIVATE_KEY_TEST}{$openpay_configuration.OPENPAY_PRIVATE_KEY_TEST|escape:'htmlall':'UTF-8'}{/if}" /></td>
                                <td width="15"></td>
                                <td width="15" class="vertBorder"></td>
                                <td align="left" valign="middle">{l s='Llave privada' mod='openpaycodi'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="password" name="openpay_private_key_live" value="{if $openpay_configuration.OPENPAY_PRIVATE_KEY_LIVE}{$openpay_configuration.OPENPAY_PRIVATE_KEY_LIVE|escape:'htmlall':'UTF-8'}{/if}" /></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>                        
                        <label style="">{l s="Definir fecha límite de pago" mod='openpaycodi'}</label>                                          
                        <input type="checkbox" name="openpay_codi_expiration" id="openpay_codi_expiration" {if $openpay_configuration.OPENPAY_CODI_EXPIRATION == true} checked="checked"{/if} style="margin: 0 0 0 5px;">
                        <div><small>La fecha de expiración por defecto es 5 minutos después de la realización del cargo.</small></div>
                    </td>
                </tr>
                <tr>
                    <td>                        
                        <label style="">{l s="Tiempo límite de pago" mod='openpaycodi'}</label>                                          
                        <input type="text" autocomplete="off" name="openpay_expiration_time" id="openpay_expiration_time" value="{if $openpay_configuration.OPENPAY_EXPIRATION_TIME}{$openpay_configuration.OPENPAY_EXPIRATION_TIME|escape:'htmlall':'UTF-8'}{/if}" style="width: 100%; margin: 5px 0 0 0;">
                        <div><small>Define el tiempo que tiene el cliente para realizar el pago.</small></div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">                        
                        <label style="">{l s="Unidad de tiempo" mod='openpaycodi'}</label>                                          
                        <select name="openpay_unit_time" id="openpay_unit_time" style="width: 100%; margin: 10px 0 0 0;">
                            <option value="minutes" {if $openpay_configuration.OPENPAY_UNIT_TIME == 'minutes'} selected="selected"{/if}>Minuto (s)</option>
                            <option value="hours" {if $openpay_configuration.OPENPAY_UNIT_TIME == 'hours'} selected="selected"{/if}>Hora (s)</option>
                            <option value="days" {if $openpay_configuration.OPENPAY_UNIT_TIME == 'days'} selected="selected"{/if}>Dia (s)</option>
                        </select>
                        <small>{l s="Unidad de tiempo para definir la expiración de pago." mod='openpaycodi'}</small>
                    </td>
                </tr> 
                <tr>
                    <td>
                        <h3>{l s='URL de la Tienda' mod='openpaycodi'}</h3>
                        <input type="text" name="openpay_webhook_url" placeholder="{l s='URL' mod='openpaycodi'}" value="{$openpay_configuration.OPENPAY_CODI_WEBHOOK_URL|escape:'htmlall':'UTF-8'}" style="width: 100%;">
                        <small>{l s="Es importante mantener este valor actualizado si cambias de dominio o subdominio." mod='openpaycodi'}</small>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="td-noborder save"><input type="submit" class="button" name="SubmitOpenpay" value="{l s='Guardar configuración' mod='openpaycodi'}" /></td>
                </tr>
            </table>
        </fieldset>        
    </form>
    <div class="clear"></div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    if($('#openpay_codi_expiration').prop('checked')) {
        updateOpenpaySettings(true);
    }else{
        updateOpenpaySettings(false);
    }

    $('#openpay_codi_expiration').change(function() {
        updateOpenpaySettings(this.checked);
    });

    function updateOpenpaySettings(checked) {
        if(checked){
            jQuery("#openpay_expiration_time").closest("tr").show();
            jQuery("#openpay_unit_time").closest("tr").show();
        }else{
            jQuery("#openpay_expiration_time").closest("tr").hide();
            jQuery("#openpay_unit_time").closest("tr").hide();
        }
    }
});
</script>

