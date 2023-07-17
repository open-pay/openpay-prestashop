{if $show_refund}
    <div class="row">
        <div class="col-lg-7">
            
            {if !empty({$error})} <div class="alert alert-danger">{$error}</div> {/if}
            
            <div class="panel bg-white p-2">
                {if $country == 'CO'}
                <div class="alert alert-info">
                    {l s='NO ES POSIBLE REALIZAR REEMBOLSOS' mod='openpayprestashop'}
                </div>
                {else}
                <div class="panel-heading">
                    <i class="icon-credit-card"></i>
                    {l s='Reembolso en Openpay' mod='openpayprestashop'}
                </div>
                
                <div class="alert alert-info">
                    {l s='ESTA ACCIÓN ÚNICAMENTE REALIZARÁ EL REEMBOLSO EN OPENPAY, NO CAMBIARÁ NINGÚN ESTATUS SOBRE LA ORDEN DE COMPRA O PRODUCTOS QUE ELLA CONTIENE.' mod='openpayprestashop'}
                </div>
                
                <div class="well hidden-print">
                    
                    <div id="openpay-refund" class="form-horizontal">
                        <input type="hidden" id="openpay_refund_order_id" name='openpay_refund_order_id' value="{$order_id|escape:'intval'}">     
                        <input type="hidden" id="openpay_refund_transaction_id" name='openpay_refund_transaction_id' value="{$transaction_id|escape:htmlall}">     
                        
                        <div id="refund_error" class="alert alert-danger hidden"></div>
                        <div id="refund_success" class="alert alert-success hidden"></div>

                        <div class="form-group">
                            <label class="control-label col-lg-3">{l s='Monto a reembolsar' mod='openpayprestashop'}</label>
                            <div class="col-lg-9">
                                <input type="text" name="openpay_refund_amount" id="openpay_refund_amount" class="form-control">
                            </div>
                        </div>                          

                        <div class="form-group">
                            <label class="control-label col-lg-3">{l s='Motivo' mod='openpayprestashop'}</label>
                            <div class="col-lg-9">
                                <textarea id="openpay_refund_reason" class="textarea-autosize" name="openpay_refund_reason"></textarea>                                    
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="button" id="openpay-refund-btn" class="btn btn-primary pull-right">
                                {l s='Realizar reembolso' mod='openpayprestashop'}
                            </button>
                        </div>    
                    </div>                        

                </div>      
                {/if} 
            </div>
        </div>
    </div>
{/if}                

<script type="text/javascript">
    $( document ).ready(function() {
        $("#refund_error").hide();
        $("#refund_success").hide();
        $("#openpay-refund-btn").click(function () {
            $("#refund_error").addClass('hidden');
            $("#refund_success").addClass('hidden');            
            
            var r = confirm("¿Estás seguro que deseas realizar un reembolso?");
            if (r === false) {
                return false;
            }
            
            var amount = $("#openpay_refund_amount").val();
            var order_id = $("#openpay_refund_order_id").val();
            var transaction_id = $("#openpay_refund_transaction_id").val();
            var reason = $("#openpay_refund_reason").val();

            $.ajax({
                url: "{$refund_url}",
                type: "POST",
                data: { order_id: order_id, amount: amount, transaction_id: transaction_id, reason: reason },
                success: function (result) {
                    console.log('result', result);
                    var message = JSON.parse(result);
                    if (message['msg'] === 'fail') {
                        $("#refund_error").html(message['response']);
                        $("#refund_error").show();
                        return false;
                    } else {
                        //location.reload();                        
                        $("#refund_success").html(message['response']);
                        $("#refund_success").show();
                        return false;
                    }
                }
            });
        });
    });        
</script>