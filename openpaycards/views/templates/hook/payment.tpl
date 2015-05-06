<div class="payment_module">
    <div class="openpay-form-container" >
        <h3 class="openpay_title">Paga con tu tarjeta de crédito o débito <img alt="" src="{$module_dir|escape:htmlall:'UTF-8'}img/secure-icon.png" /></h3>
        <div><img src="{$cards|escape:html:'UTF-8'}"></div>
        {* Classic Credit card form *}
        <div id="openpay-ajax-loader"><img src="{$module_dir|escape:htmlall:'UTF-8'}img/ajax-loader.gif" alt="" /> Estamos registrando tu pago, por favor espera</div>
        <form action="{$validation_url|escape:htmlall:'UTF-8'}" method="POST" id="openpay-payment-form">
            <br>
            <div class="openpay-payment-errors">{if isset($openpay_error)}{$openpay_error|escape:htmlall:'UTF-8'}{/if}</div>
            <a name="openpay_error" style="display:none"></a>

            <div>
                <label>Nombre del tarjetahabiente</label><br />
                <input type="text" autocomplete="off" id="holder_name" data-openpay-card="holder_name" class="openpay-card-number" />
            </div>

            <div>
                <label>Número de tarjeta</label><br />
                <input type="text" size="20" autocomplete="off" id="card_number" data-openpay-card="card_number" class="openpay-card-number" />
            </div>

            <div>
                <label>FECHA DE EXPIRACIÓN</label><br />
                <select id="expiration_month" data-openpay-card="expiration_month" class="openpay-card-expiry-month">
                    <option value="01">Enero</option>
                    <option value="02">Febrero</option>
                    <option value="03">Marzo</option>
                    <option value="04">Abril</option>
                    <option value="05">Mayo</option>
                    <option value="06">Junio</option>
                    <option value="07">Julio</option>
                    <option value="08">Agosto</option>
                    <option value="09">Septiembre</option>
                    <option value="10">Octubre</option>
                    <option value="11">Noviembre</option>
                    <option value="12">Diciembre</option>
                </select>
                <span> / </span>
                <select id="expiration_year" data-openpay-card="expiration_year" class="openpay-card-expiry-year">
                    {assign var='startyear' value=$smarty.now|date_format:"%y"}
                    {assign var='endyear' value=($smarty.now|date_format:"%y" + 10)}

                    {for $i=$startyear to $endyear}
                        <option value="{$i}">{$i}</option>
                    {/for}
                </select>
            </div>
                
            <div>
                <label>CVC</label><br />
                <input id="cvv2" type="text" size="4" autocomplete="off" data-openpay-card="cvv2" class="openpay-card-cvc" />
                <a href="javascript:void(0)" class="openpay-card-cvc-info" style="border: none;">
                    <img src="/modules/openpaycards/img/cvc_back.png">
                    <div class="cvc-info">
                    {l s='MasterCard y VISA presentan este código código de tres dígitos en el dorso de la tarjeta.' mod='openpaycards'}
                    </div>
                </a>
                <a href="javascript:void(0)" class="openpay-card-cvc-info" style="border: none;">
                    <img src="/modules/openpaycards/img/cvc_front.png">
                    <div class="cvc-info">
                    {l s='American Express presenta este código código de tres dígitos en la parte frontal de la tarjeta.' mod='openpaycards'}
                    </div>
                </a>
                
            </div>  
            
            <button type="submit" class="openpay-submit-button">Realizar Pago</button>
        </form>
    </div>
</div>
