<div class="payment_module" >
    <div class="openpay-form-container">
	<h3 class="openpay_title">Pago en efectivo (7 Eleven, Extra, Farmacias del Ahorro, Farmacias Benavides, etc.)</h3>

        <p>Una vez que des clic en el botón "Generar Ficha de Pago", tu compra quedará en espera de que realices tu pago.</p>

        <p>Reliza tu pago en cualquiera de las tiendas afiliadas.</p>
        
        <div><img src="{$stores|escape:html:'UTF-8'}"></div>
        <div><small><a target="_blank" href="http://www.openpay.mx/tiendas-de-conveniencia.html">Ver más tiendas</a></small></div>
        <br>
<!--                <div class="openpay-payment-errors" {if isset($openpay_error)}style="display: block;"{/if}>{if isset($openpay_error)}{$openpay_error|escape:htmlall:'UTF-8'}{/if}</div> -->
        
	<form data-ajax="false" action="{$validation_url|escape:htmlall:'UTF-8'}" method="POST" id="openpay-cash-form">

		<input type="submit" value="{l s='Generar Ficha de Pago' mod='openpaystores'}" class="{if $openpay_ps_version >= '1.5'}openpay-submit-button {/if}exclusive" data-icon="check" data-iconpos="right" data-theme="b" />

	</form>
   </div>
                
</div>