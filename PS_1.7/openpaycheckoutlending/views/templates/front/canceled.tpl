{extends file='page.tpl'}
{block name="page_content"}
<div class="container canceled-info" style="text-align: center">
    <div class="openpay-cl-payment-errors" style="display: {if isset($smarty.get.error_msg)}block{else}none{/if};">
        {if isset($smarty.get.error_msg)}{$smarty.get.error_msg|escape:'htmlall':'UTF-8'}{/if}
    </div>
    {if $valid_cancel}
    <img src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/cancelled.png" alt="" style="width: 50%;">
    <table class="order-details">
        <tr>
            <th>Reference</th>
            <td>{$order_reference}</td>
        </tr>
    </table>

    <br>

    <table>
        <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Price</th>
        </tr>
        {$product_details nofilter}
        <tr>
            <th colspan="2">Shipping Cost</th>
            <td>{$order->total_shipping}</td>
        </tr>
        <tr>
            <th colspan="2">Total</th>
            <td>{$total_paid}</td>
        </tr>
    </table>
    {else}
    <div class="openpay-cl-payment-errors" style="display:block;">
        CANCELACIÓN INVALIDA
    </div>
    {/if}

    <a href="{$base_url}" class="button" role="button"><span>{l s='Regresar a la tienda' mod='openpaycheckoutlending'}</span></a>

</div>
{/block}
