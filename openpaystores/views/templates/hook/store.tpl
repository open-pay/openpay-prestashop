<div id="store-container" class="payment_module" >
    <div class="openpay-form-container">
        <div class="row mt20">                
            <div class="col-md-4"></div>
            <div class="col-md-4 store-image">
                {if $country == 'MX' }
                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/paynet.png">
                {/if}
            </div>
        </div>    

        <div class="row">    
            {if $country == 'MX' }
                <div class="col-md-2"></div>
                    <div class="store-image">
                        <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/stores/stores_mx.png">
                    </div>
                <div class="col-md-2"></div>   
            {elseif $country == 'CO'}
                <div class="col-md-12 store-image">
                    <div>
                        <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/stores/puntored_via_baloto_logo.png">
                    </div>
                </div>
            {elseif $country == 'PE'}
                <div class="store-logos">
                    <div class="store-logos-pe">
                        <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/stores-pe.png">
                    </div>
                </div>
            {/if}      
        </div>            

        <h4 class="subtitle blue_subtitle mt30 mb30">{l s='Pasos para tu pago por tienda' mod='openpaystores'}
            (<small>
            {if $country == 'MX' }
                <a target="_blank" href="http://www.openpay.mx/tiendas-de-conveniencia.html">{l s='Tienda afiliadas' mod='openpaystores'}</a>
            {elseif $country == 'CO'}
                <a target="_blank" href="https://www.openpay.co/tiendas/">{l s='Puntos de recaudo' mod='openpaystores'}</a>
            {else}
                 <a target="_blank" href="https://www.openpay.pe/documentacion/tiendas-en-el-mapa/">{l s='Agencias' mod='openpaystores'}</a>
            {/if}
            </small>)
        </h4>

        <div class="row mb30 steps">
            <div class="col-md-4 center">
                <img src="https://img.openpay.mx/plugins/file.svg">
                <p>{l s='Haz clic en el botón de "Realizar Pedido", donde tu compra quedará en espera de que realices tu pago.' mod='openpaystores'}</p>
            </div>
            <div class="col-md-4 center">
                <img src="https://img.openpay.mx/plugins/printer.svg">
                <p>{l s='Imprime tu recibo, llévalo a tu tienda de conveniencia más cercana y realiza el pago.' mod='openpaystores'}</p>
            </div>
            <div class="col-md-4 center">
                <img src="https://img.openpay.mx/plugins/mail.svg">
                <p>{l s='Inmediatamente después de recibir tu pago te enviaremos un correo electrónico con la confirmación de pago.' mod='openpaystores'}</p>
            </div>
        </div>

    </div>
</div>