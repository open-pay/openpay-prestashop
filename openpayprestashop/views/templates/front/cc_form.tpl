{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div id="card-container" class="payment_module">
<div class="ajax-loader"></div>
    <div class="openpay-form-container" >
        <div class="row mt30 mb10">
            <div class="col-md-12 store-image">
                <h3 class="openpay_title">{l s='Tarjetas aceptadas' mod='openpayprestashop'}</h3>
                {if $country == 'MX'}
                    {if $merchant_classification === 'eglobal'}
                        <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/credit_cards_bbva.png" style="max-width: 120px;
                        margin-bottom: 5px;">
                    {else}
                         <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/credit_cards.png">
                    {/if}
                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/debit_cards.png">
                {elseif $country == 'PE'}
                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/credit_cards_pe.png" style="max-width: 220px;">
                {else}
                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/cards_co.png">
                {/if}
            </div>                
        </div>

        <div id="openpay-ajax-loader">
            <p>{l s='Estamos registrando tu pago, por favor espera.' mod='openpayprestashop'}</p>
            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/ajax-loader.gif" alt="" />             
        </div>
        <br>
        <form action="{$action}" id="openpay-payment-form" method="post" class="openpay-payment-form">              
            <input type="hidden" name="use_card_points" id="use_card_points" value="false" />
            <input type="hidden" name="country" id="country" value="{$country}"/>
            
            <h3 class="openpay_title">{l s='Información de pago' mod='openpayprestashop'}</h3>
            
            <div class="openpay-payment-errors" style="display: {if isset($openpay_error)}block{else}none{/if};">
                {if isset($openpay_error)}{$openpay_error|escape:'htmlall':'UTF-8'}{/if}
            </div>           
            
            <div class="row">
                <div class="col-md-12 mt10">
                    <label class="label-form-checkout">Seleccione tarjeta <span class="required-symbol">*</span></label>        
                    <select name="openpay_cc" id="openpay_cc" class="form-control">                        
                        {foreach $cc_options as $cc}
                            <option value="{$cc['value']}">{$cc['name']}</option>
                        {/foreach}
                    </select>
                </div>
            </div>    
            <div id="payment_form_openpay_cards">        
                <div class="row">
                    <div class="col-md-12">
                        <label class="label-form-checkout">{l s='Nombre del títular' mod='openpayprestashop'} <span class="required-symbol">*</span></label>
                        <input type="text" autocomplete="off" id="holder_name" data-openpay-card="holder_name" class="form-control" placeholder="{l s='Como se muestra en la tarjeta' mod='openpayprestashop'}" />
                    </div>
                    <div class="col-md-12">
                        <label class="label-form-checkout">{l s='Número de tarjeta' mod='openpayprestashop'} <span class="required-symbol">*</span></label>
                        <input type="text" autocomplete="off" id="card_number" data-openpay-card="card_number" class="form-control" placeholder="•••• •••• •••• ••••" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">

                        <label class="label-form-checkout">{l s='Fecha de expiración (MM / AA)' mod='openpayprestashop'} <span class="required-symbol">*</span></label>
                        <select id="expiration_month" data-openpay-card="expiration_month" class="openpay-card-expiry-month">
                            <option value="01">{l s='Enero' mod='openpayprestashop'}</option>
                            <option value="02">{l s='Febrero' mod='openpayprestashop'}</option>
                            <option value="03">{l s='Marzo' mod='openpayprestashop'}</option>
                            <option value="04">{l s='Abril' mod='openpayprestashop'}</option>
                            <option value="05">{l s='Mayo' mod='openpayprestashop'}</option>
                            <option value="06">{l s='Junio' mod='openpayprestashop'}</option>
                            <option value="07">{l s='Julio' mod='openpayprestashop'}</option>
                            <option value="08">{l s='Agosto' mod='openpayprestashop'}</option>
                            <option value="09">{l s='Septiembre' mod='openpayprestashop'}</option>
                            <option value="10">{l s='Octubre' mod='openpayprestashop'}</option>
                            <option value="11">{l s='Noviembre' mod='openpayprestashop'}</option>
                            <option value="12">{l s='Diciembre' mod='openpayprestashop'}</option>
                        </select>
                        <span> / </span>
                        <select id="expiration_year" data-openpay-card="expiration_year" class="openpay-card-expiry-year">
                            {assign var='startyear' value=$smarty.now|date_format:"%y"}
                            {assign var='endyear' value=($smarty.now|date_format:"%y" + 10)}

                            {for $i=$startyear to $endyear}
                                <option value="{$i|escape:'htmlall':'UTF-8'}">20{$i|escape:'htmlall':'UTF-8'}</option>
                            {/for}
                        </select>

                    </div>

                    <div class="col-md-6 wrapper_cvv2">
                        <label class="label-form-checkout">{l s='Código de seguridad (CVC / CVV)' mod='openpayprestashop'} <span class="required-symbol">*</span></label>
                        <input id="cvv2" type="password" maxlength="4" autocomplete="off" data-openpay-card="cvv2" class="form-control" placeholder="CVV" />
                        <p id="alert-cvv-error"></p>
                    </div>

                    <div class="col-md-6 wrapper_cvv2 cvv2_example_images">
                        <br />
                        <a href="javascript:void(0)" class="" style="border: none;" data-toggle="popover" data-content="{l s='MasterCard and VISA present this three-digit code on the back of the card.' mod='openpayprestashop'}" >
                            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/cvc_back.png">
                        </a>
                        <a href="javascript:void(0)" class="openpay-card-cvc-info" style="border: none;" data-toggle="popover" data-content="{l s='American Express presents this three-digit code on the front of the card.' mod='openpayprestashop'}">
                            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/cvc_front.png">
                        </a>
                        <p class="label-form-checkout"><span class="required-symbol">*</span> Campos obligatorios</p>
                    </div>
                    {if $can_save_cc}           
                        <div class="col-md-12" style="margin-bottom: 20px;">
                            <label for="save_cc" class="label">
                                <input type="checkbox" name="save_cc" id="save_cc" /> <span style="font-weight: 600;" class="save-card-text">Guardar tarjeta</span>
                                <span class="hover_text"><span class="symbol-circle">?</span><span class="tooltip_text" id="right">Al guardar los datos de tu tarjeta agilizarás tus pagos futuros y podrás usarla como método de pago guardado.</span></span>
                            </label>
                        </div>
                    {/if}       
                </div>
            </div>    
           
            {if $show_months_interest_free }        
                <div class="row installments">
                    <div class="col-md-6">
                        <label>{l s='Meses sin intereses' mod='openpayprestashop'}</label>
                        <select name="interest_free" id="interest-free" style="width: 100%;">
                            <option value="1">{l s="Pago de contado" mod='openpayprestashop'}</option>
                            {foreach $months_interest_free as $interest_free}
                                <option value="{$interest_free}">{$interest_free} meses</option>
                            {/foreach}
                        </select>
                    </div>
                    <div id="total-monthly-payment" class="col-md-6 hidden">        
                        <label>{l s="Pago mensual" mod='openpayprestashop'}</label>
                        <p class="openpay-total">$<span id="monthly-payment">{$total}</span> MXN</p>
                    </div>
                </div>
            {/if}

            {if $show_installments }        
                <div class="row installments">
                    <div class="col-md-6">
                        <label>{l s='Cuotas' mod='openpayprestashop'}</label>
                        <select name="installment" id="installment" style="width: 100%;">
                            <option value="1">{l s="Pago de contado" mod='openpayprestashop'}</option>
                            {foreach $installments as $installment}
                                <option value="{$installment}">{$installment}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div id="total-monthly-payment" class="col-md-6 hidden">        
                        <label>{l s="Pago mensual" mod='openpayprestashop'}</label>
                        <p class="openpay-total">$<span id="monthly-payment">{$total}</span> MXN</p>
                    </div>
                </div>
            {/if}
            {if $cuotas_pe }
            <div class="row installments">
                <div class="col-md-6">
                    <label id="installments_title">{l s='Cuotas' mod='openpayprestashop'}</label>
                    <select name="openpay_installments_pe" id="openpay_installments_pe" style="width: 100%;"></select>
                    <input type="hidden" name="withInterest" id="withInterest"/>
                </div>
                <div id="total-monthly-payment" class="col-md-6 hidden">
                    <label>{l s="Pago mensual" mod='openpayprestashop'}</label>
                    <p class="openpay-total">$<span id="monthly-payment">{$total}</span> MXN</p>
                </div>
            </div>
            {/if}
        </form>
    </div>
    <div class="footer-form-container">
        <div class="logo-secure">
            <div><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/security.png" width="50"></div>
            <div><p>Tus pagos se realizan de forma segura <br/> con encriptación de 256 bits</p></div>
        </div>
        <div class=""><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/atom-brand-logotype.png" width="180"></div>
    </div>
</div>
<hr/>
        <div class="modal fade" role="dialog" id="card-points-dialog"> <div class="modal-dialog modal-sm"> <div class="modal-content" style="padding: 0 !important;"> <div class="modal-header"> <h4 class="modal-title">Pagar con Puntos</h4> </div> <div class="modal-body"> <p>¿Desea usar los puntos de su tarjeta para realizar este pago?</p> </div> <div class="modal-footer"> <button type="button" class="btn btn-success" data-dismiss="modal" id="points-yes-button">Si</button> <button type="button" class="btn btn-default" data-dismiss="modal" id="points-no-button">No</button> </div> </div> </div></div>        

<script>
    var smarty = {
        useCardPoints: "{$use_card_points|escape:'javascript'}",
        total: "{$total|escape:'javascript'}",
        country: "{$country|escape:'javascript'}",
        openpaySaveccOption: "{$openpay_save_cc_option|escape:'javascript'}",
        openpayPublicKey: "{$pk|escape:'javascript'}",
        openpayMerchantId: "{$id|escape:'javascript'}",
        mode: "{$mode|escape:'javascript'}",
        showMonthsInterestFree: "{$show_months_interest_free|escape:'javascript'}",
        cuotasPe: "{$cuotas_pe|escape:'javascript'}",
        showForm: "{$showForm|escape:'javascript'}",
        id: "{$id|escape:'javascript'}",
        urlApi: "{$url_api|escape:'javascript'}",
        urlAjax: "{$url_ajax|escape:'javascript'}"
    };
</script>
{literal}
    <script type="text/javascript" src="/modules/openpayprestashop/views/js/cc_form.js" integrity="sha256-6VOCjK7lGXRkJ2QBvQhrKTUWXZ99/fG2bGmEzSdY4LA=" crossorigin="anonymous"></script>
{/literal}