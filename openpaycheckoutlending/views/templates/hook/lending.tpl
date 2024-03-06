{if isset($smarty.get.error_msg)}
    <script type="text/javascript">
        const error_loader= '<div class="error-loader"><p>{l s='Algo salió mal, por favor espera.' mod='openpaycheckoutlending'}</p><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/ajax-loader.gif" alt=""/></div>';
        let element = document.querySelector(".payment-options ");
        element.insertAdjacentHTML('afterbegin', error_loader);

        setTimeout(function(){
                let id = document.querySelector("input[data-module-name='openpaycheckoutlending']").id;
                console.log(id);
                document.querySelector("input[data-module-name='openpaycheckoutlending']").checked=true;
                console.log(id+"-additional-information");
                document.getElementById(id+"-additional-information").style.display = "block";
                document.querySelector('.error-loader').style.display = "none";
        }, 2000);
    </script>
{/if}
<div id="store-container" class="payment_module" >
    <div class="">
        <div class="row mt20">
            <div class="openpay-cl-payment-errors" style="display: {if isset($smarty.get.error_msg)}block{else}none{/if};">
                {if isset($smarty.get.error_msg)}{$smarty.get.error_msg|escape:'htmlall':'UTF-8'}{/if}
            </div>
            <div class="col-md-4">
                <img style="width:100%" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/kueskipay.svg">
            </div>
        </div>
    </div>
</div>