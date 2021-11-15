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
        {if $openpay_configuration.OPENPAY_CLASSIFICATION != 'eglobal' }
            <a href="{if $openpay_configuration.OPENPAY_COUNTRY == 'MX' } http://www.openpay.mx {else} http://www.openpay.co {/if}" target="_blank" rel="external"><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/openpay-logo.png" alt="Openpay logo" class="openpay-logo" /></a>
            <span class="openpay-module-intro">{l s='Comienza ha aceptar pagos con tarjetas de crédito-débito hoy mismo con Openpay.' mod='openpayprestashop'}</span>
            <a href="{if $openpay_configuration.OPENPAY_COUNTRY == 'MX' }
            https://sandbox-dashboard.openpay.mx/login/register
            {elseif $openpay_configuration.OPENPAY_COUNTRY == 'CO'} https://sandbox-dashboard.openpay.co/login/register
            {else} https://sandbox-dashboard.openpay.pe/login/register
            {/if}  " rel="external" target="_blank" class="openpay-module-create-btn"><span>{l s='Crea una cuenta' mod='openpayprestashop'}</span></a>
        {else}
            <a href="https://docs.ecommercebbva.com/index.html#cargos" target="_blank" rel="external"><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/bbva-logo.png" alt="Openpay logo" class="openpay-logo" /></a>
            <span class="openpay-module-intro">{l s='Comienza ha aceptar pagos con tarjetas de crédito-débito hoy mismo con BBVA.' mod='openpayprestashop'}</span>
            <a href="https://sand-portal.ecommercebbva.com/login/register/bbva" rel="external" target="_blank" class="openpay-module-create-btn"><span>{l s='Crea una cuenta' mod='openpayprestashop'}</span></a>
        {/if}
    </div>
    <div class="openpay-module-wrap">
        <div class="openpay-module-col1 floatRight">
            <div class="openpay-module-wrap-video">
                <h3>{l s='Panel de administración' mod='openpayprestashop'}</h3>
                <p>{l s='Contamos con un panel donde podrás visualizar todas tus transacciones.' mod='openpayprestashop'}</p>
                <a target="_blank" href="{$dashboard_openpay}">
                    {if $openpay_configuration.OPENPAY_CLASSIFICATION != 'eglobal' }
                        <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/openpay_dashboard.png" alt="openpay dashboard" class="openpay-dashboard" /></a>
                    {else}
                        <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/bbva-dashboard.png" alt="BBVA dashboard" class="openpay-dashboard" /></a>
                    {/if}
                <hr>
                <div class="openpay-prestashop-partner mt30">
                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/prestashop_partner.png" alt="" />
                </div>
            </div>
        </div>
        <div class="openpay-module-col2">
            <div class="row">
                <div class="col-md-6">
                    <h3>{l s='Beneficios' mod='openpayprestashop'}</h3>
                    <p>{l s='Avanzada infraestructura para soportar diferentes ecosistemas de pagos. Contamos con herramientas que nos ayudan a detectar y evitar fraudes en tiempo real.' mod='openpayprestashop'}</p>
                </div>
                <div class="col-md-5">
                    <h3>&nbsp;</h3>
                    <ul>
                        <li>{l s='Sin rentas mensuales' mod='openpayprestashop'}</li>
                        <li>{l s='Sin costos de integración' mod='openpayprestashop'}</li>
                        <li>{l s='Sin comisiones por configuración' mod='openpayprestashop'}</li>
                        <li>{l s='Sin plazos forzosos' mod='openpayprestashop'}</li>
                        <li>{l s='Sin cargos ocultos ni letras chiquitas' mod='openpayprestashop'}</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="openpay-module-col2inner">
                <div class="row">
                    <div class="col-md-7">
                        <h3>{l s='Acepta pagos con tarjetas de crédito' mod='openpayprestashop'}</h3>
                        <div class="row">
                        {if $openpay_configuration.OPENPAY_COUNTRY == 'MX' }
                            {if $openpay_configuration.OPENPAY_CLASSIFICATION != 'eglobal' }
                                {for $i=1 to 4}
                                    <div class="col-xs-2 store-image">
                                        <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/credit_cards_mx/{sprintf("%02d", $i|escape:'htmlall':'UTF-8')}.png">
                                    </div>
                                {/for}
                            {else}
                                {for $i=1 to 3}
                                    <div class="col-xs-2 store-image">
                                        <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/credit_cards_bbva/{sprintf("%02d", $i|escape:'htmlall':'UTF-8')}.png">
                                    </div>
                                {/for}
                            {/if}
                        {elseif $openpay_configuration.OPENPAY_COUNTRY == 'PE' }
                            {for $i=1 to 4}
                                <div class="col-xs-2 store-image">
                                    <img {if $i==4} style="max-width:65%;" {/if} src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/credit_cards_pe/{sprintf("%02d", $i|escape:'htmlall':'UTF-8')}.png">
                                </div>
                            {/for}
                        {else}
                            {for $i=1 to 2}
                                <div class="col-xs-2 store-image">
                                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/credit_cards_co/{sprintf("%02d", $i|escape:'htmlall':'UTF-8')}.png">
                                </div>
                            {/for}
                        {/if}
                        </div>
                        <br><br>
                        <h3>{l s='Acepta pagos con tarjetas de débito' mod='openpayprestashop'}</h3>
                        <div class="row">
                        {if $openpay_configuration.OPENPAY_COUNTRY == 'MX' }
                            {for $i=1 to 4}
                                <div class="col-xs-2 store-image">
                                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/debit_cards_mx/{sprintf("%02d", $i|escape:'htmlall':'UTF-8')}.png">
                                </div>
                            {/for}
                        {elseif $openpay_configuration.OPENPAY_COUNTRY == 'PE' }
                            {for $i=1 to 4}
                                <div class="col-xs-2 store-image">
                                    <img {if $i==4} style="max-width:65%;" {/if} src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/debit_cards_pe/{sprintf("%02d", $i|escape:'htmlall':'UTF-8')}.png">
                                </div>
                            {/for}
                        {else}
                            {for $i=1 to 2}
                                <div class="col-xs-2 store-image">
                                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/debit_cards_co/{sprintf("%02d", $i|escape:'htmlall':'UTF-8')}.png">
                                </div>
                            {/for}
                        {/if}
                        </div>
                        {if $openpay_configuration.OPENPAY_CLASSIFICATION != 'eglobal' }
                        <div>
                            <strong><a href="{if $openpay_configuration.OPENPAY_COUNTRY == 'MX' } http://www.openpay.mx/tarjetas.html
                                             {elseif $openpay_configuration.OPENPAY_COUNTRY == 'CO'} https://www.openpay.co/tdc-tdd
                                             {else} https://www.openpay.pe/
                                             {/if}" target="_blank" class="openpay-module-btn">{l s='Tarjetas soportadas' mod='openpayprestashop'}</a></strong>
                        </div>
                        {/if}
                    </div>
                    {if $openpay_configuration.OPENPAY_CLASSIFICATION != 'eglobal' }
                    <div style="text-align: center" class="col-md-4">
                        {if $openpay_configuration.OPENPAY_COUNTRY == 'MX' }
                            <h3>{l s='Comisión por transacción exitosa' mod='openpayprestashop'}</h3>
                            <p class="comision">{l s='2.9% + $2.5 MXN' mod='openpayprestashop'}</p>
                        {elseif $openpay_configuration.OPENPAY_COUNTRY == 'CO'}
                            <h3>{l s='Costos para clientes con cuentas de otros bancos' mod='openpayprestashop'}</h3>
                            <p class="comision">{l s='2.90% + $900 COP' mod='openpayprestashop'}</p>
                            <br><br>
                            <h3>{l s='Costos para clientes BBVA' mod='openpayprestashop'}</h3>
                            <p class="comision">{l s='2.70% + $900 COP' mod='openpayprestashop'}</p>
                        {else}
                            <h3>{l s='Costos para clientes con cuentas de otros bancos' mod='openpayprestashop'}</h3>
                            <p class="comision">{l s='3.79% + S/1.00' mod='openpayprestashop'}</p>
                            <p>{l s='Y tendrás tu dinero disponible en 48 hrs. Además recibirás notificaciones en tiempo real' mod='openpayprestashop'}</p>

                            <br><br>
                            <h3>{l s='Costos para clientes BBVA' mod='openpayprestashop'}</h3>
                            <p class="comision">{l s='3.49%' mod='openpayprestashop'}</p>
                            <p>{l s='Y tendrás tu dinero disponible en 24 hrs. Además recibirás notificaciones en tiempo real' mod='openpayprestashop'}</p>
                        {/if}
                    </div>
                    {/if}
                </div>
            </div>            
        </div>
    </div>
    <fieldset>
        <legend><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/checks-icon.gif" alt="" />{l s='Chequeo técnico' mod='openpayprestashop'}</legend>
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
            <legend><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/technical-icon.gif" alt="" />{l s='Configuración' mod='openpayprestashop'}</legend>
            <label>Modo</label>
            <input type="radio" name="openpay_mode" value="0" {if $openpay_configuration.OPENPAY_MODE == 0} checked="checked"{/if} /> {l s='Sandbox' mod='openpayprestashop'}
            <input type="radio" name="openpay_mode" value="1" {if $openpay_configuration.OPENPAY_MODE == 1} checked="checked"{/if} /> {l s='Producción' mod='openpayprestashop'}
            <br /><br />
            <table cellspacing="0" cellpadding="0" class="openpay-settings">
                <tr>
                    <td align="center" valign="middle" colspan="2">
                        <table cellspacing="0" cellpadding="0" class="innerTable">
                            <tr>
                                <td align="left" valign="middle">{l s='Sandbox ID' mod='openpayprestashop'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="text" name="openpay_merchant_id_test" value="{if $openpay_configuration.OPENPAY_MERCHANT_ID_TEST}{$openpay_configuration.OPENPAY_MERCHANT_ID_TEST|escape:'htmlall':'UTF-8'}{/if}" /></td>
                                <td width="15"></td>
                                <td width="15" class="vertBorder"></td>
                                <td align="left" valign="middle">{l s='ID' mod='openpayprestashop'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="text" name="openpay_merchant_id_live" value="{if $openpay_configuration.OPENPAY_MERCHANT_ID_LIVE}{$openpay_configuration.OPENPAY_MERCHANT_ID_LIVE|escape:'htmlall':'UTF-8'}{/if}" /></td>
                            </tr>
                            <tr>
                                <td align="left" valign="middle">{l s='Sandbox llave privada' mod='openpayprestashop'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="password" name="openpay_private_key_test" value="{if $openpay_configuration.OPENPAY_PRIVATE_KEY_TEST}{$openpay_configuration.OPENPAY_PRIVATE_KEY_TEST|escape:'htmlall':'UTF-8'}{/if}" /></td>
                                <td width="15"></td>
                                <td width="15" class="vertBorder"></td>
                                <td align="left" valign="middle">{l s='Llave privada' mod='openpayprestashop'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="password" name="openpay_private_key_live" value="{if $openpay_configuration.OPENPAY_PRIVATE_KEY_LIVE}{$openpay_configuration.OPENPAY_PRIVATE_KEY_LIVE|escape:'htmlall':'UTF-8'}{/if}" /></td>
                            </tr>
                            <tr>
                                <td align="left" valign="middle">{l s='Sandbox llave pública' mod='openpayprestashop'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="text" name="openpay_public_key_test" value="{if $openpay_configuration.OPENPAY_PUBLIC_KEY_TEST}{$openpay_configuration.OPENPAY_PUBLIC_KEY_TEST|escape:'htmlall':'UTF-8'}{/if}" /></td>
                                <td width="15"></td>
                                <td width="15" class="vertBorder"></td>
                                <td align="left" valign="middle">{l s='Llave pública' mod='openpayprestashop'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="text" name="openpay_public_key_live" value="{if $openpay_configuration.OPENPAY_PUBLIC_KEY_LIVE}{$openpay_configuration.OPENPAY_PUBLIC_KEY_LIVE|escape:'htmlall':'UTF-8'}{/if}" /></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>                                                     
                        <input type="text" autocomplete="off" type="text" name="openpay_classification" id="openpay_classification" value="{if $openpay_configuration.OPENPAY_CLASSIFICATION}{$openpay_configuration.OPENPAY_CLASSIFICATION|escape:'htmlall':'UTF-8'}{/if}" style="width: 100%; margin: 5px 0 0 0;">
                    </td>
                </tr>
                <tr>
                    <td colspan="2">                        
                        <label style="">{l s="País" mod='openpayprestashop'}</label>                                          
                        <select name="openpay_country" id="country" style="width: 100%; margin: 10px 0 0 0;">
                            <option value="MX" {if $openpay_configuration.OPENPAY_COUNTRY == 'MX'} selected="selected"{/if}>México</option>
                            <option value="CO" {if $openpay_configuration.OPENPAY_COUNTRY == 'CO'} selected="selected"{/if}>Colombia</option>
                            <option value="PE" {if $openpay_configuration.OPENPAY_COUNTRY == 'PE'} selected="selected"{/if}>Perú</option>
                        </select>
                        <div><small>* Seleccionar el país </small></div>
                    </td>
                </tr>
                <tr>
                    <td>                        
                        <label style="">{l s="Número de afiliación" mod='openpayprestashop'}</label>                                          
                        <input type="text" autocomplete="off" type="text" name="openpay_affiliation_bbva" id="openpay_affiliation_bbva" value="{if $openpay_configuration.OPENPAY_AFFILIATION}{$openpay_configuration.OPENPAY_AFFILIATION|escape:'htmlall':'UTF-8'}{/if}" style="width: 100%; margin: 5px 0 0 0;">
                        <div><small>* Número de afiliación BBVA.</small></div>
                    </td>
                </tr>                         
                <tr>
                    <td colspan="2">                        
                        <label style="">{l s="¿Cómo procesar el cargo?" mod='openpayprestashop'}</label>                                          
                        <select name="openpay_charge_type" id="openpay_charge_type" style="width: 100%; margin: 10px 0 0 0;">
                            <option value="direct" {if $openpay_configuration.OPENPAY_CHARGE_TYPE == 'direct'} selected="selected"{/if}>Directo</option>
                            <option value="auth" {if $openpay_configuration.OPENPAY_CHARGE_TYPE == 'auth'} selected="selected"{/if}>Autenticación Selectiva</option>
                            <option value="3d" {if $openpay_configuration.OPENPAY_CHARGE_TYPE == '3d'} selected="selected"{/if}>3D Secure</option>
                        </select>
                        <div><small>* ¿Qué es cargo directo? Openpay se encarga de validar la operación y recharzarla cuando detecta riesgo.</small></div>
                        <div><small>* ¿Qué es la autenticación selectiva? Es cuando el banco se encarga de validar la autenticidad del cuentahabiente, solo si Openpay detecta riesta en la operación.</small></div>
                        <div><small>* ¿Qué es 3D Secure? El banco se encargará de validar su autenticidad del cuentahabiente en todas las operaciones.</small></div>
                    </td>
                </tr> 
                <tr>
                    <td colspan="2">                        
                        <label style="">{l s="Configuración del cargo" mod='openpayprestashop'}</label>                                          
                        <select name="capture" id="capture" style="width: 100%; margin: 10px 0 0 0;">
                            <option value="true" {if $openpay_configuration.OPENPAY_CAPTURE == 'true'} selected="selected"{/if}>Cargo inmediato</option>
                            <option value="false" {if $openpay_configuration.OPENPAY_CAPTURE == 'false'} selected="selected"{/if}>Pre-autorizar únicamente</option>                            
                        </select>
                        <div><small>* Indica si el cargo se hace o no inmediatamente, con la pre-autorización solo se reserva el monto para ser confirmado o cancelado posteriormente. Las pre-autorizaciones no pueden ser utilizadas en combinación con pago con puntos Bancomer.</small></div>                        
                    </td>
                </tr> 
                <tr>
                    <td colspan="2">                        
                        <label style="">{l s="Pago con puntos" mod='openpayprestashop'}</label>                                          
                        <select name="use_card_points" id="use_card_points" style="width: 100%; margin: 10px 0 0 0;">
                            <option value="0" {if $openpay_configuration.USE_CARD_POINTS == '0'} selected="selected"{/if}>NO</option>
                            <option value="1" {if $openpay_configuration.USE_CARD_POINTS == '1'} selected="selected"{/if}>SI</option>                            
                        </select>
                        <div><small>Recibe pagos con puntos Bancomer habilitando esta opción. Esta opción no se puede combinar con pre-autorizaciones o MSI.</small></div>                        
                    </td>
                </tr> 
                <tr>
                    <td colspan="2">                        
                        <label style="">{l s="Guardar tarjetas" mod='openpayprestashop'}</label>                                          
                        <select name="save_cc" id="openpay_save_cc" style="width: 100%; margin: 10px 0 0 0;">
                            <option value="0" {if $openpay_configuration.OPENPAY_SAVE_CC == '0'} selected="selected"{/if}>NO</option>
                            <option value="1" {if $openpay_configuration.OPENPAY_SAVE_CC == '1'} selected="selected"{/if}>SI</option>                            
                        </select>
                        <div><small>Permite a los usuarios registrados guardar sus tarjetas de crédito para agilizar sus futuras compras.</small></div>                        
                    </td>
                </tr>                 
                <tr>
                    <td colspan="2">
                        <h3>{l s='Meses sin intereses' mod='openpayprestashop'}</h3>
                        <label>{l s="Si vas a utilizar MSI deberás de seleccionar al menos una de las siguiente opciones." mod='openpayprestashop'}</label>                                      
                        <select name="months_interest_free[]" id="months_interest_free" style="width: 100%; margin: 10px 0 0 0;" multiple>
                             {foreach $months_interest_free as $key => $interest_free}
                                <option value="{$key}" {if $key|in_array:$selected_months_interest_free } selected="selected"{/if}>{$interest_free}</option>
                            {/foreach}
                        </select>
                        <div><smal>* Presione ctrl y clic para seleccionar más de una opción.</smal></div>                                                  
                    </td>
                </tr>
                <tr>
                    <td>                        
                        <label style="">{l s="IVA" mod='openpayprestashop'}</label>                                          
                        <input type="text" autocomplete="off" type="text" name="openpay_iva" id="openpay_iva" value="{if $openpay_configuration.OPENPAY_IVA}{$openpay_configuration.OPENPAY_IVA|escape:'htmlall':'UTF-8'}{/if}" style="width: 100%; margin: 5px 0 0 0;">
                        <div><small>* Debe contener el valor de IVA, es campo solo informativo, no tiene ningún efecto sobre el campo amount.</small></div>
                    </td>
                </tr>     
                <tr>
                    <td colspan="2" class="td-noborder save"><input type="submit" class="button" name="SubmitOpenpay" value="{l s='Guardar configuración' mod='openpayprestashop'}" /></td>
                </tr>
            </table>
        </fieldset>
        <fieldset class="openpay-cc-numbers">
            <legend><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/cc-icon.gif" alt="" />{l s='Números de tarjetas de prueba' mod='openpayprestashop'}</legend>
            <table cellspacing="0" cellpadding="0" class="openpay-cc-numbers">
                <thead>
                    <tr>
                        <th>{l s='Número' mod='openpayprestashop'}</th>
                        <th>{l s='Tipo' mod='openpayprestashop'}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td class="number"><code>4111111111111111</code></td><td>Visa</td></tr>
                    <tr><td class="number"><code>4242424242424242</code></td><td>Visa</td></tr>
                    <tr><td class="number"><code>5555555555554444</code></td><td>MasterCard</td></tr>
                    <tr><td class="number"><code>5105105105105100</code></td><td>MasterCard</td></tr>
                    <tr><td class="number"><code>345678000000007</code></td><td>American Express</td></tr>
                    <tr><td class="number"><code>343434343434343</code></td><td>American Express</td></tr>
                    <tr><td class="number"><code>4222222222222220</code></td><td>{l s='Card was declined' mod='openpayprestashop'}</td></tr>
                    <tr><td class="number"><code>4000000000000069</code></td><td>{l s='Card was expired' mod='openpayprestashop'}</td></tr>
                    <tr><td class="number"><code>4444444444444448</code></td><td>{l s='Card has insufficient funds' mod='openpayprestashop'}</td></tr>
                </tbody>
            </table>
        </fieldset>
    </form>
    <div class="clear"></div>

</div>

<script type="text/javascript">
$(document).ready(function() {
    $("#openpay_classification").closest("tr").hide();

    var country = $('#country').val();
    var merchantClassification = $('#openpay_classification').val();
    showOrHideElements(country, merchantClassification);

    $('input:radio[name=openpay_mode]').click(function() {
        updateOpenpaySettings();
    });

    $('#country').change(function () {
        var country = $(this).val();
        console.log('openpay_cards_country', country);        

        showOrHideElements(country)
    });

    function updateOpenpaySettings() {
        if ($('input:radio[name=openpay_mode]:checked').val() === 1) {
            $('fieldset.openpay-cc-numbers').hide(1000);
        } else {
            $('fieldset.openpay-cc-numbers').show(1000);
        }
    }

    function showOrHideElements(country, merchantClassification){
        if (country === 'CO') {            
            $("#openpay_iva").closest("tr").show();
            
            $("#openpay_affiliation_bbva").closest("tr").hide();
            $("#use_card_points").closest("tr").hide();
            $("#months_interest_free").closest("tr").hide(); 
            $("#openpay_charge_type").closest("tr").hide();
            $("#capture").closest("tr").hide();        
        } else if(country === 'PE'){
            $("#openpay_iva").closest("tr").hide();
            $("#openpay_affiliation_bbva").closest("tr").hide();
            $("#use_card_points").closest("tr").hide();
            $("#months_interest_free").closest("tr").hide();
            $("#openpay_charge_type").closest("tr").hide();
            $("#capture").closest("tr").hide();
        } else if (country === 'MX') {
            $("#openpay_iva").closest("tr").hide();

            $("#use_card_points").closest("tr").show();
            $("#months_interest_free").closest("tr").show();      

            if(merchantClassification === 'eglobal'){
                $("#openpay_affiliation_bbva").closest("tr").show();

                $("#openpay_charge_type").closest("tr").hide();
                $("#capture").closest("tr").hide();
                $("#country").closest("tr").hide();
            }else{
                $("#openpay_affiliation_bbva").closest("tr").hide();

                $("#openpay_charge_type").closest("tr").show();
                $("#capture").closest("tr").show();
                $("#country").closest("tr").show();
            }                          
        }
    }
});
</script>