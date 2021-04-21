<div id="store-container" class="payment_module" >
    <div class="openpay-form-container">
        <div class="row mt20">                
            <div class="col-md-4"></div>
            <div class="col-md-4 store-image">
                {if $country == 'MX' }
                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/paynet.png">
                {else}
                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/punto_red.png">
                {/if}
            </div>
        </div>    

        <div class="row">    
            {if $country == 'MX' }
                <div class="col-md-2"></div>
                    <div class="col-md-8 store-image">
                        <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/stores_mx.png">
                    </div>
                <div class="col-md-2"></div>   
            {else}
                <div class="col-md-12 store-image">
                    <div class="store-logos">
                        <div class="store-logos__puntored">
                            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/stores/puntored_logo.jpeg">
                        </div>
                        <div class="store-logos__via">
                            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/stores/baloto_logo.png">
                            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/stores/via_logo.png">
                        </div>
                    </div>
                </div>
            {/if}      
        </div>            

        <h4 class="subtitle mt30 mb30">{l s='Pasos para tu pago por tienda' mod='openpaystores'} (<small><a target="_blank" href="{if $country == 'MX' }http://www.openpay.mx/tiendas-de-conveniencia.html {else} https://www.openpay.co/tiendas/ {/if}">{l s='Tienda afiliadas' mod='openpaystores'}</a></small>)</h4>

        <div class="row mb30">

            <div class="col-md-4 center">
                <div><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/step1.png"></div>
                <p>{l s='Haz clic en el botón de "Realizar Pedido", donde tu compra quedará en espera de que realices tu pago.' mod='openpaystores'}</p>
            </div>
            <div class="col-md-4 center">
                <div><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/step_store.png"></div>
                <p>{l s='Imprime tu recibo, llévalo a tu tienda de conveniencia más cercana y realiza el pago.' mod='openpaystores'}</p>
            </div>
            <div class="col-md-4 center">
                <div><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/step3.png"></div>
                <p>{l s='Inmediatamente después de recibir tu pago te enviaremos un correo electrónico con la confirmación de pago.' mod='openpaystores'}</p>
            </div>
        </div>

    </div>
</div>