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

{if $openpay_order.valid == 1}
    <div class="conf confirmation" style="font-size: 16px;">{l s='Congratulations, your payment is approved, the reference number of your order is:' mod='openpayprestashop'} <b>{$openpay_order.reference|escape:'htmlall':'UTF-8'}</b>.</div>
{else}
    {if $order_pending}
        <div class="error">{l s='Unfortunately we detected a problem while processing your order and it needs to be reviewed.' mod='openpayprestashop'}<br/><br/>
            {l s='Do not try to submit your order again, as the funds have already been received.  We will review the order and provide a status shortly.' mod='openpayprestashop'}<br /><br/>
            {l s='Your Orders Reference:' mod='openpayprestashop'} <b>{$openpay_order.reference|escape:'htmlall':'UTF-8'}</b>
        </div>
    {else}
        <div class="error">{l s='Sorry, unfortunately an error occurred during the transaction.' mod='openpayprestashop'}<br /><br />
            {l s='Please double-check your credit card details and try again or feel free to contact us to resolve this issue.' mod='openpayprestashop'}<br /><br />
            {l s='Your Orders Reference:' mod='openpayprestashop'} <b>{$openpay_order.reference|escape:'htmlall':'UTF-8'}</b>
        </div>
    {/if}
{/if}
