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

<div style="text-align: center">
    <iframe id="pdf" src="{$openpay_order.pdf_url|escape:'htmlall':'UTF-8'}" style="width:70%; height:1000px;" frameborder="0"></iframe>
    
    {if ($openpay_order.show_map)}
        <div class="mt20 mb20">
            <h2>Mapa de tiendas</h2>
            {if $openpay_order.country == MX}
                <iframe src="https://www.paynet.com.mx/mapa-tiendas/index.html?locationNotAllowed=true&postalCode={$openpay_order.postal_code}" style="border: 1px solid #000; width:70%; height:300px;" frameborder="0"></iframe>                
            {else}
                <iframe src="https://docs.openpay.co/docs/mapa-de-tiendas.html?locationNotAllowed=true&address={$openpay_order.address}" style="border: 1px solid #000; width:70%; height:300px;" frameborder="0"></iframe>
            {/if}
        </div>    
    {/if}    
</div>