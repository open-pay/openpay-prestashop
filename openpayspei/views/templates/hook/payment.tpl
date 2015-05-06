<div class="payment_module" >
    <div class="openpay-form-container">
	<h3 class="openpay_title">Pago con transferencia bancaria (SPEI)</h3>
        
        <p>Una vez que des clic en el botón "Generar Clabe de Pago", tu compra quedará en espera de que realices tu pago.</p>

        <p>Sigue las instrucciones para realizar el pago SPEI a través del portal de tu banco.</p>
        
        <div><img src="{$spei|escape:html:'UTF-8'}"></div>
        
        <div><small><a target="_blank" href="http://www.openpay.mx/bancos.html">Bancos que ofrecen el servicio SPEI </a></small></div>
        
        <br>
<!--        <div class="openpay-payment-errors" {if isset($openpay_spei_error)}style="display: block;"{/if}>{if isset($openpay_spei_error)}{$openpay_spei_error|escape:htmlall:'UTF-8'}{/if}</div> -->
        
	<form data-ajax="false" action="{$validation_url|escape:htmlall:'UTF-8'}" method="POST" id="openpay-spei-form">
		<input type="submit" value="{l s='Generar Clabe de Pago' mod='openpayspei'}" class="{if $openpay_ps_version >= '1.5'}openpay-submit-button {/if}exclusive" data-icon="check" data-iconpos="right" data-theme="b" />
	</form>
    </div>
</div>