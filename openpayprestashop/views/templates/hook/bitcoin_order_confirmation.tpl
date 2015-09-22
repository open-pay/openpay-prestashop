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
</style>

<div class="container container-receipt center">
    <div class="row">
        <div class="col-md-12 center">
            <img class="bitcoin-receipt-logo" src="{$logo|escape:'htmlall':'UTF-8'}" alt="Logo">
        </div>
        <div class="col-sm-6 col-md-6 col-lg-6">
        </div>
    </div>
    <div class="row data">
        <div id="bitcoin_div"></div>
    </div>    
</div>

<script type="text/javascript">
  $(document).ready(function() {
        var merchant_id = "{$merchant_id|escape:'htmlall':'UTF-8'}";
        var transaction_id = "{$transaction_id|escape:'htmlall':'UTF-8'}";
        var mode = "{$mode|escape:'htmlall':'UTF-8'}";

        OpenPayBitcoin.setId(merchant_id);

        if(mode == "0"){
            OpenPayBitcoin.setSandboxMode(true);
        }

        OpenPayBitcoin.setupIframe('bitcoin_div', transaction_id, function(status){ if(status == 'completed'){ console.log("Gracias por tu pago"); } });
  });
</script>