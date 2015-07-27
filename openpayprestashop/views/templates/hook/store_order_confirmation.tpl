{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<style>
    #order_step{
        display: none;
    }

    .data_amount{
        background-color: {$openpay_order.bg_color|escape:'htmlall':'UTF-8'};
    }

    .data_amount, .data_amount .amount, .data_amount .amount small{
        color: {$openpay_order.font_color|escape:'htmlall':'UTF-8'};
    }

    .Big_Bullet span{
        background-color: {$openpay_order.bg_color|escape:'htmlall':'UTF-8'};
    }

</style>

<div class="container container-receipt">
    <div class="row">
        <div class="col-sm-6 col-md-6 col-lg-6">
            <img class="img-responsive center-block" src="{$openpay_order.logo|escape:'htmlall':'UTF-8'}" alt="Logo">
        </div>
        <div class="col-sm-6 col-md-6 col-lg-6">
            <div class="mt30 pull-right">
                <span style="font-size: 20px;font-weight: lighter;">{l s='Service to pay' mod='openpayprestashop'}</span>
                <img class="logo_paynet" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/paynet_logo.png" alt="Logo">
            </div>
        </div>
    </div>

    <div class="row data">

        <div class="col-sm-6 col-md-6 col-lg-6">
            <div class="Big_Bullet">
                <span></span>
            </div>
            <h1><strong>{l s='Due date' mod='openpayprestashop'}</strong></h1>
            <strong>{$openpay_order.due_date|escape:'htmlall':'UTF-8'}</strong>
            <div class="col-lg-12 datos_pago" style="margin-left: 20px;">
                <img width="300" src="{$openpay_order.barcode_url|escape:'htmlall':'UTF-8'}" alt="Código de Barras">
                <span style="font-size: 15px">{$openpay_order.barcode|escape:'htmlall':'UTF-8'}</span>
                <br/>
                <p>{l s='If the scanner is unable to read the barcode, write the reference as shown.' mod='openpayprestashop'}</p>
            </div>

        </div>

        <div class="col-sm-6 col-md-6 col-lg-6">
            <div class="data_amount">
                <h2>{l s='Total' mod='openpayprestashop'}</h2>
                <h2 class="amount">${$openpay_order.amount|escape:'htmlall':'UTF-8'}<small> {$openpay_order.currency|escape:'htmlall':'UTF-8'}</small></h2>
                <h2 style="margin-top: 0px;">{l s='+8 pesos fee' mod='openpayprestashop'}</h2>
            </div>
        </div>
    </div>

    <div class="row data">

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="Big_Bullet">
                <span></span>
            </div>
            <h1 style="padding-top: 7px;"><strong>{l s='Purchase details' mod='openpayprestashop'}</strong></h1>
            <div class="datos_tiendas">
                <table class="detail">
                    <tr class="odd">
                        <td width="40%">{l s='Order' mod='openpayprestashop'}:</td>
                        <td width="60%">{$openpay_order.order|escape:'htmlall':'UTF-8'}</td>
                    </tr>
                    <tr class="even">
                        <td>{l s='Date' mod='openpayprestashop'}:</td>
                        <td>{$openpay_order.date|escape:'htmlall':'UTF-8'}</td>
                    </tr>
                    <tr class="odd">
                        <td>{l s='Email' mod='openpayprestashop'}:</td>
                        <td>{$openpay_order.email|escape:'htmlall':'UTF-8'}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="row data">

        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <div class="Big_Bullet">
                <span></span>
            </div>
            <h1><strong>{l s='How to pay?' mod='openpayprestashop'}</strong></h1>
            <ol style="margin-left: 30px;">
                <li>{l s='Go to any affiliated store' mod='openpayprestashop'}</li>
                <li>{l s='Delivery cashier barcode and mentions that will take an Paynet payment service' mod='openpayprestashop'}</li>
                <li>{l s='Make payment for' mod='openpayprestashop'} ${$openpay_order.amount|escape:'htmlall':'UTF-8'} {$openpay_order.currency|escape:'htmlall':'UTF-8'} {l s='($8 pesos fee)' mod='openpayprestashop'}</li>
                <li>{l s='Retains the ticket for any clarification' mod='openpayprestashop'}</li>
            </ol>
            <p style="margin-left: 30px; font-size: 12px;">
                {l s='If you have questions contact to' mod='openpayprestashop'} {$openpay_order.shop_name|escape:'htmlall':'UTF-8'} {l s='at the phone' mod='openpayprestashop'} {$openpay_order.phone|escape:'htmlall':'UTF-8'} {l s='or email' mod='openpayprestashop'} {$openpay_order.shop_email|escape:'htmlall':'UTF-8'}.
            </p>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <h1><strong>{l s='Cashier instructions' mod='openpayprestashop'}</strong></h1>
            <ol>
                <li>{l s='Enter the menu Payment Services' mod='openpayprestashop'}</li>
                <li>{l s='Select Paynet' mod='openpayprestashop'}</li>
                <li>{l s='Scan the barcode or enter the reference' mod='openpayprestashop'}</li>
                <li>{l s='Enter the total amount due' mod='openpayprestashop'}</li>
                <li>{l s='Charge the customer the full amount plus $ 8 pesos fee' mod='openpayprestashop'}</li>
                <li>{l s='Confirm the transaction and deliver the ticket to the customer' mod='openpayprestashop'}</li>
            </ol>
        </div>
    </div>

    <div class="row marketing">

        <div class="col-lg-12" style="text-align:center;">
            {for $i=1 to 4}
                <div class="col-xs-3 store-image">
                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/stores/{sprintf("%02d", $i|escape:'htmlall':'UTF-8')}.png">
                </div>
            {/for}
        </div>
        <div class="col-lg-12" style="text-align:center;">
            <div class="link_tiendas">{l s='Do you want to pay in other stores? Visit:' mod='openpayprestashop'} <a target="_blank" href="http://www.openpay.mx/tiendas-de-conveniencia.html">www.openpay.mx/tiendas</a></div>
        </div>
        <div class="col-lg-6 mb30" style="text-align:right; margin-top:5px;">
            <a href="javascript:void(0)" class="btn btn-info btn-lg" onclick="window.print();">{l s='Print Receipt' mod='openpayprestashop'}</a>
        </div>
        <div class="col-lg-6 mb30" style="text-align:left; margin-top:5px;">
            <a  class="btn btn-success btn-lg" href="/">{l s='Continue shopping' mod='openpayprestashop'}</a>
        </div>
    </div>

    <div class="footer">
        <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/powered_openpay.png" alt="Powered by Openpay">
    </div>

</div>

<script type="text/javascript">
  $(document).ready(function() {
        window.print();
  });
</script>