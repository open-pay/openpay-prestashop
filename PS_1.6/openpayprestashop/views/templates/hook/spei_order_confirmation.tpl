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

    .Yellow1, .Yellow2, .Yellow3{
        background-color: {$openpay_order.bg_color|escape:'htmlall':'UTF-8'};
    }

    .amount, .marketing h4, .amount small{
        color: {$openpay_order.bg_color|escape:'htmlall':'UTF-8'};
    }

    .Yellow2 span{
        color: {$openpay_order.font_color|escape:'htmlall':'UTF-8'};
    }

</style>

<div class="container container-bank-receipt">
    <div class="row nomargin">
        <div class="Yellow1 col-md-1"></div>
        <div class="col-sm-5 col-md-5 col-lg-5">
            <img style="background-color: #fff;" class="img-responsive center-block" src="{$openpay_order.logo|escape:'htmlall':'UTF-8'}" alt="Logo">
        </div>
        <div class="Yellow2 col-sm-6 col-md-6 col-lg-6">
            <span>{l s='We expect your payment' mod='openpayprestashop'}</span>
        </div>
    </div>

    <div class="subcontainer">
        <div class="row nomargin">
            <div class="col-xs-9 col-sm-8 col-md-8 col-lg-8 nopadding">
                <h1><strong>{l s='Total' mod='openpayprestashop'}</strong></h1>
                <h2 class="amount">${$openpay_order.amount|escape:'htmlall':'UTF-8'}<small> {$openpay_order.currency|escape:'htmlall':'UTF-8'}</small></h2>
                <h1><strong>{l s='Due date' mod='openpayprestashop'}</strong></h1>
                <h1>{$openpay_order.due_date|escape:'htmlall':'UTF-8'}</h1>
            </div>
            <div class="col-xs-3 col-sm-4 col-md-4 col-lg-4 nopadding">
                <a class="spei" href="http://www.openpay.mx/bancos.html" target="_blank"><img class="img-responsive" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/spei.gif"  alt="SPEI"></a>
            </div>
        </div>

        <div class="marketing mt30">
            <h1><strong>{l s='Electronic data transfer' mod='openpayprestashop'}</strong></h1>

            <table class="detail">
                <tr class="odd">
                    <td>{l s='Bank' mod='openpayprestashop'}:</td>
                    <td>{l s='STP' mod='openpayprestashop'}</td>
                </tr>
                <tr class="even">
                    <td>{l s='CLABE' mod='openpayprestashop'}:</td>
                    <td>{$openpay_order.clabe|escape:'htmlall':'UTF-8'}</td>
                </tr>
                <tr class="odd">
                    <td>{l s='Reference' mod='openpayprestashop'}:</td>
                    <td>{$openpay_order.reference|escape:'htmlall':'UTF-8'}</td>
                </tr>
                <tr class="even">
                    <td>{l s='Beneficiary' mod='openpayprestashop'}:</td>
                    <td>{$openpay_order.shop_name|escape:'htmlall':'UTF-8'}</td>
                </tr>
            </table>

            <div class="col-lg-12" style="text-align: center; margin-top: 20px;">
                <p>{l s='Have a question or problem? Call us on the phone' mod='openpayprestashop'}</p>
                <h4>{$openpay_order.phone|escape:'htmlall':'UTF-8'}</h4>
                <p>{l s='Or write to us at' mod='openpayprestashop'}</p>
                <h4>{$openpay_order.email|escape:'htmlall':'UTF-8'}</h4>
                <div class="col-lg-6 mb30" style="text-align:right; margin-top:5px;">
                    <a href="javascript:void(0)" class="btn btn-info btn-lg" onclick="window.print();">{l s='Print receipt' mod='openpayprestashop'}</a>
                </div>
                <div class="col-lg-6 mb30" style="text-align:left; margin-top:5px;">
                    <a  class="btn btn-success btn-lg" href="/">{l s='Continue shopping' mod='openpayprestashop'}</a>
                </div>
            </div>
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