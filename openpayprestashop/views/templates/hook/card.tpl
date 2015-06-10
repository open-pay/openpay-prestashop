<div class="payment_module">
    <div class="openpay-form-container" >
        <div class="row mt30 mb10">
            <div class="col-md-3 store-image" style="border-right: 1px solid #ccc;">
                <h3 class="openpay_title">Tarjetas de crédito</h3>
                <img src="{$module_dir}views/img/credit_cards.png">
            </div>
            <div class="col-md-6 store-image">
                <h3 class="openpay_title">Tarjetas de débito</h3>
                <img src="{$module_dir}views/img/debit_cards.png">
            </div>
        </div>

        <div id="openpay-ajax-loader"><img src="{$module_dir}views/img/ajax-loader.gif" alt="" /> Estamos registrando tu pago, por favor espera.</div>
        <form action="{$validation_url}" method="POST" id="openpay-payment-form">
            <input type="hidden" name="payment_method" value="card" id="payment_method">
            <br>
            <div class="openpay-payment-errors">{if isset($openpay_error)}{$openpay_error|escape:htmlall:'UTF-8'}{/if}</div>
            <a name="openpay_error" style="display:none"></a>

            <div class="row">
                <div class="col-md-4">
                    <label>Nombre del tarjetahabiente</label><br />
                    <input type="text" autocomplete="off" id="holder_name" data-openpay-card="holder_name" class="form-control" placeholder="Como aparece en la tarjeta" />
                </div>
                <div class="col-md-4">
                    <label>Número de tarjeta</label><br />
                    <input type="text" autocomplete="off" id="card_number" data-openpay-card="card_number" class="form-control" placeholder="•••• •••• •••• ••••" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">

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
                            <option value="{$i}">20{$i}</option>
                        {/for}
                    </select>

                </div>

                <div class="col-md-2">
                    <label>Código de seguridad</label><br />
                    <input id="cvv2" type="password" size="4" autocomplete="off" data-openpay-card="cvv2" class="form-control" placeholder="CVV" />
                </div>

                <div class="col-md-2">
                    <br />
                    <a href="javascript:void(0)" class="" style="border: none;" data-toggle="popover" data-content="{l s='MasterCard y VISA presentan este código código de tres dígitos en el dorso de la tarjeta.' mod='openpayprestashop'}" >
                        <img src="{$module_dir}views/img/cvc_back.png">
                    </a>
                    <a href="javascript:void(0)" class="openpay-card-cvc-info" style="border: none;" data-toggle="popover" data-content="{l s='American Express presenta este código código de tres dígitos en la parte frontal de la tarjeta.' mod='openpayprestashop'}">
                        <img src="{$module_dir}views/img/cvc_front.png">
                    </a>
                </div>

            </div>
            <div class="row">
                <div class="pull-right">
                    <button type="submit" class="openpay-submit-button">Realizar Pago</button>
                </div>
            </div>
        </form>
    </div>
</div>