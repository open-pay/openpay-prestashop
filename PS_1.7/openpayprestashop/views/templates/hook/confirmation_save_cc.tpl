{if $openpay_order.save_cc }
    <div style="">
        <div class="savecc-alert">
            <span><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/alert_savecc.png" width="20"/>  <p>Tu tarjeta con terminación {$openpay_order.card_number_complement} fué registrada exitosamente.</p></span>
        </div>
    </div>
{/if}