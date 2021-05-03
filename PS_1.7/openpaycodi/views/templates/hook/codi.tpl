<div id="spei-container" class="payment_module" >
    <div class="openpay-form-container">
        <div class="row mt30">
            <div class="col-md-4 store-image">
                <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/codi/QR.jpg">
            </div>
            <div class="col-md-8">
                <h4 class="subtitle">{l s='¿Qué es CoDi®?' mod='openpaybanks'}</h4>
                <p style="font-size: 14px;">
                    {l s='CoDi® es una plataforma desarrollada por Banco de México que permite hacer cobros a través de un código QR desde un celular' mod='openpaybanks'}
                </p>
            </div>
        </div>

        <h4 class="subtitle mt30 mb30">{l s='Pasos para completar tu pago' mod='openpaybanks'}</h4>

        <div class="row mb30">
            <div class="col-md-4 center">
                <div><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/step1.png"></div>
                <p>{l s='Haz clic en el botón de "Realizar Pedido", donde tu compra quedará en espera de que realices tu pago.' mod='openpaybanks'}</p>
            </div>
            <div class="col-md-4 center">
                <div><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/codi/codi_step2.png"></div>
                <p>{l s='Localiza la sección de CoDi® en tu app móvil, sigue los pasos para escanear el código QR para completar tu pago.' mod='openpaybanks'}</p>
            </div>
            <div class="col-md-4 center">
                <div><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/step3.png"></div>
                <p>{l s='Inmediatamente después de recibir tu pago te enviaremos un correo electrónico con la confirmación de pago.' mod='openpaybanks'}</p>
            </div>
        </div>

    </div>
</div>