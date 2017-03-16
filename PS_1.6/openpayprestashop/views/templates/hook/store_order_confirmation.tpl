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

<div class="container">    
    <div class="row">              
        <div class="col-lg-8 col-lg-offset-2">
            <iframe id="pdf" src="{$openpay_order.pdf|escape:'htmlall':'UTF-8'}" style="width:100%; height:950px; visibility: visible !important; opacity: 1 !important;" frameborder="0"></iframe>    
        </div>
        
        <div class="col-lg-8 mt30 text-center col-lg-offset-2">
            <a  class="btn btn-success btn-lg" href="/">{l s='Continue shopping' mod='openpayprestashop'}</a>
        </div>        
    </div>        
</div>
