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

<div class="openpay-module-wrapper">

    <div class="openpay-module-header">
        <a href="http://www.openpay.mx" target="_blank" rel="external"><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/openpay-logo.png" alt="Openpay logo" class="openpay-logo" /></a>
        <span class="openpay-module-intro">{l s='Start accepting card payments, cash payments and bank payments today with Openpay.' mod='openpayprestashop'}</span>
        <a href="https://sandbox-dashboard.openpay.mx/merchant/production" rel="external" target="_blank" class="openpay-module-create-btn"><span>{l s='Create an account' mod='openpayprestashop'}</span></a>
    </div>
    <div class="openpay-module-wrap">
        <div class="openpay-module-col1 floatRight">
            <div class="openpay-module-wrap-video">
                <h3>{l s='Management panel' mod='openpayprestashop'}</h3>
                <p>{l s='We have an administration panel where you can check your business transactions.' mod='openpayprestashop'}</p>
                <a target="_blank" href="http://www.openpay.mx"><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/openpay-dashboard.png" alt="openpay dashboard" class="openpay-dashboard" /></a>
                <hr>
                <div class="openpay-prestashop-partner mt30">
                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/prestashop_partner.png" alt="" />
                </div>
            </div>
        </div>
        <div class="openpay-module-col2">
            <div class="row">
                <div class="col-md-6">
                    <h3>{l s='Benefits' mod='openpayprestashop'}</h3>
                    <p>{l s='Openpay offers a simple cost structure all-inclusive for all types of e-commerce or m-commerce solutions.' mod='openpayprestashop'}</p>
                </div>
                <div class="col-md-5">
                    <h3>&nbsp;</h3>
                    <ul>
                        <li>{l s='No monthly rent' mod='openpayprestashop'}</li>
                        <li>{l s='Without integration costs' mod='openpayprestashop'}</li>
                        <li>{l s='No fee configuration' mod='openpayprestashop'}</li>
                        <li>{l s='No compulsory terms' mod='openpayprestashop'}</li>
                        <li>{l s='No hidden fees or fine print' mod='openpayprestashop'}</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="openpay-module-col2inner">
                <div class="row">
                    <div class="col-md-7">
                        <h3>{l s='Accept credit card payments' mod='openpayprestashop'}</h3>
                        <div class="row">
                            {for $i=1 to 4}
                                <div class="col-xs-2 store-image">
                                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/credit_cards/{sprintf("%02d", $i|escape:'htmlall':'UTF-8')}.png">
                                </div>
                            {/for}
                        </div>
                        <br><br>
                        <h3>{l s='Accept debit card payments' mod='openpayprestashop'}</h3>
                        <div class="row">
                            {for $i=1 to 4}
                                <div class="col-xs-2 store-image">
                                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/debit_cards/{sprintf("%02d", $i|escape:'htmlall':'UTF-8')}.png">
                                </div>
                            {/for}
                        </div>
                        <div>
                            <strong><a href="http://www.openpay.mx/tarjetas.html" target="_blank" class="openpay-module-btn">{l s='Supported cards' mod='openpayprestashop'}</a></strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h3>{l s='Fee for successful transaction: Visa y MasterCard' mod='openpayprestashop'}</h3>
                        <p class="comision">{l s='2.9% + $2.5 MXN' mod='openpayprestashop'}</p>
                        <br><br>
                        <h3>{l s='Fee for successful transaction: American Express' mod='openpayprestashop'}</h3>
                        <p class="comision">{l s='4.5% + $2.5 MXN' mod='openpayprestashop'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="openpay-module-col2inner">
                <div class="row">
                    <div class="col-md-7">
                        <h3>{l s='Accept cash payments' mod='openpayprestashop'}</h3>
                        {for $i=1 to 4}
                            <div class="col-xs-2 store-image">
                                <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/stores/{sprintf("%02d", $i|escape:'htmlall':'UTF-8')}.png">
                            </div>
                        {/for}
                        <div>
                            <strong>
                                <a href="http://www.openpay.mx/tiendas-de-conveniencia.html" target="_blank" class="openpay-module-btn">
                                    {l s='Affiliated stores' mod='openpayprestashop'}
                                </a>
                            </strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h3>{l s='Fee for successful transaction' mod='openpayprestashop'}</h3>
                        <p class="comision">{l s='2.9% + $2.5 MXN' mod='openpayprestashop'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="openpay-module-col2inner">

                <div class="row">
                    <div class="col-md-7">
                        <h3>{l s='Accept bank payments (SPEI)' mod='openpayprestashop'}</h3>
                        <div class="row">
                            <div class="col-md-6 store-image">
                                <img class="openpay-cc" alt="openpay" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/spei.png">
                            </div>
                        </div>
                        <div>
                            <strong>
                                <a href="http://www.openpay.mx/bancos.html" target="_blank" class="openpay-module-btn">
                                    {l s='Supported banks' mod='openpayprestashop'}
                                </a>
                            </strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h3>{l s='Fee for successful transaction' mod='openpayprestashop'}</h3>
                        <p class="comision">{l s='$8 MXN' mod='openpayprestashop'}</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <fieldset>
        <legend><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/checks-icon.gif" alt="" />{l s='Technical checks' mod='openpayprestashop'}</legend>
        <div class="conf">{$openpay_validation_title|escape:'htmlall':'UTF-8'}</div>
        <table cellspacing="0" cellpadding="0" class="openpay-technical">
            {if $openpay_validation}
                {foreach from=$openpay_validation item=validation}
                    <tr>
                        <td>
                            <img src="../img/admin/{($validation['result']|escape:'htmlall':'UTF-8') ? 'ok' : 'forbbiden'}.gif" alt="" />
                        </td>
                        <td>
                            {$validation['name']|escape:'htmlall':'UTF-8'}
                        </td>
                    </tr>
                {/foreach}
            {/if}
        </table>
    </fieldset>
    <br />

    {if $openpay_error}
        <fieldset>
            <legend>Errors</legend>
            <table cellspacing="0" cellpadding="0" class="openpay-technical">
                <tbody>
                    {foreach from=$openpay_error item=error}
                        <tr>
                            <td><img src="../img/admin/forbbiden.gif" alt=""></td>
                            <td>{$error|escape:'htmlall':'UTF-8'}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </fieldset>
        <br />
    {/if}

    <form action="{$openpay_form_link|escape:'htmlall':'UTF-8'}" method="post">
        <fieldset class="openpay-settings">
            <legend><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/technical-icon.gif" alt="" />{l s='Configurations' mod='openpayprestashop'}</legend>
            <label>Modo</label>
            <input type="radio" name="openpay_mode" value="0" {if $openpay_configuration.OPENPAY_MODE == 0} checked="checked"{/if} /> {l s='Sandbox' mod='openpayprestashop'}
            <input type="radio" name="openpay_mode" value="1" {if $openpay_configuration.OPENPAY_MODE == 1} checked="checked"{/if} /> {l s='Live' mod='openpayprestashop'}
            <br /><br />
            <table cellspacing="0" cellpadding="0" class="openpay-settings">
                <tr>
                    <td align="center" valign="middle" colspan="2">
                        <table cellspacing="0" cellpadding="0" class="innerTable">
                            <tr>
                                <td align="left" valign="middle">{l s='Sandbox merchant ID' mod='openpayprestashop'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="text" name="openpay_merchant_id_test" value="{if $openpay_configuration.OPENPAY_MERCHANT_ID_TEST}{$openpay_configuration.OPENPAY_MERCHANT_ID_TEST|escape:'htmlall':'UTF-8'}{/if}" /></td>
                                <td width="15"></td>
                                <td width="15" class="vertBorder"></td>
                                <td align="left" valign="middle">{l s='Live merchant ID' mod='openpayprestashop'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="text" name="openpay_merchant_id_live" value="{if $openpay_configuration.OPENPAY_MERCHANT_ID_LIVE}{$openpay_configuration.OPENPAY_MERCHANT_ID_LIVE|escape:'htmlall':'UTF-8'}{/if}" /></td>
                            </tr>
                            <tr>
                                <td align="left" valign="middle">{l s='Sandbox priavate key' mod='openpayprestashop'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="password" name="openpay_private_key_test" value="{if $openpay_configuration.OPENPAY_PRIVATE_KEY_TEST}{$openpay_configuration.OPENPAY_PRIVATE_KEY_TEST|escape:'htmlall':'UTF-8'}{/if}" /></td>
                                <td width="15"></td>
                                <td width="15" class="vertBorder"></td>
                                <td align="left" valign="middle">{l s='Live priavate key' mod='openpayprestashop'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="password" name="openpay_private_key_live" value="{if $openpay_configuration.OPENPAY_PRIVATE_KEY_LIVE}{$openpay_configuration.OPENPAY_PRIVATE_KEY_LIVE|escape:'htmlall':'UTF-8'}{/if}" /></td>
                            </tr>
                            <tr>
                                <td align="left" valign="middle">{l s='Sandbox public key' mod='openpayprestashop'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="text" name="openpay_public_key_test" value="{if $openpay_configuration.OPENPAY_PUBLIC_KEY_TEST}{$openpay_configuration.OPENPAY_PUBLIC_KEY_TEST|escape:'htmlall':'UTF-8'}{/if}" /></td>
                                <td width="15"></td>
                                <td width="15" class="vertBorder"></td>
                                <td align="left" valign="middle">{l s='Live piblic key' mod='openpayprestashop'}</td>
                                <td align="left" valign="middle"><input autocomplete="off" type="text" name="openpay_public_key_live" value="{if $openpay_configuration.OPENPAY_PUBLIC_KEY_LIVE}{$openpay_configuration.OPENPAY_PUBLIC_KEY_LIVE|escape:'htmlall':'UTF-8'}{/if}" /></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <h3>{l s='Payment methods' mod='openpayprestashop'}</h3>
                        <table cellspacing="0" cellpadding="0" class="innerTable">
                            <tr>
                                <td align="left" valign="middle">
                                    <input type="checkbox" name="openpay_cards" id="openpay_cards" value="1" {if $openpay_configuration.OPENPAY_CARDS == 1} checked="checked"{/if}> {l s='Credit-debit card payment' mod='openpayprestashop'}
                                </td>
                                <td align="left" valign="middle">
                                    <input type="checkbox" name="openpay_stores" id="openpay_stores" value="1" {if $openpay_configuration.OPENPAY_STORES == 1} checked="checked"{/if}> {l s='Cash payment' mod='openpayprestashop'}
                                </td>
                                <td align="left" valign="middle">
                                    <input type="checkbox" name="openpay_spei" id="openpay_spei" value="1" {if $openpay_configuration.OPENPAY_SPEI == 1} checked="checked" {/if}> {l s='Bank payment' mod='openpayprestashop'}
                                </td>
                                <td align="left" valign="middle">
                                    <input type="checkbox" name="openpay_bitcoins" id="openpay_bitcoins" value="1" {if $openpay_configuration.OPENPAY_BITCOINS == 1} checked="checked" {/if}> {l s='Bitcoins' mod='openpayprestashop'}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <h3>{l s='Time limit for payment' mod='openpayprestashop'}</h3>
                        <table cellspacing="0" cellpadding="0" class="innerTable">
                            <tr>
                                <td valign="middle" align="left">{l s='Cash payment (hrs)' mod='openpayprestashop'}</td>
                                <td valign="middle" align="left">
                                    <input type="text" name="openpay_deadline_stores" placeholder="{l s='Hours' mod='openpayprestashop'}" value="{if $openpay_configuration.OPENPAY_DEADLINE_STORES}{$openpay_configuration.OPENPAY_DEADLINE_STORES|escape:'htmlall':'UTF-8'}{/if}">
                                </td>
                                <td width="15"></td>
                                <td class="vertBorder" width="15"></td>
                                <td valign="middle" align="left">{l s='Bank payment (hrs.)' mod='openpayprestashop'}</td>
                                <td valign="middle" align="left">
                                    <input type="text" name="openpay_deadline_spei" placeholder="{l s='Hours' mod='openpayprestashop'}" value="{if $openpay_configuration.OPENPAY_DEADLINE_SPEI}{$openpay_configuration.OPENPAY_DEADLINE_SPEI|escape:'htmlall':'UTF-8'}{/if}">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <h3>{l s='Design for payment receipt' mod='openpayprestashop'} (<a title="{l s='See payment receipt' mod='openpayprestashop'}" href="{$receipt|escape:'htmlall':'UTF-8'}" target="_blank"><i class="icon-question"></i></a>)</h3>
                        <table cellspacing="0" cellpadding="0" class="innerTable">
                            <tr>
                                <td valign="middle" align="left">{l s='Background color' mod='openpayprestashop'}</td>
                                <td valign="middle" align="left">
                                    <input type="text" name="openpay_background_color" placeholder="{l s='Color in hexadecimal' mod='openpayprestashop'}" value="{if $openpay_configuration.OPENPAY_BACKGROUND_COLOR}{$openpay_configuration.OPENPAY_BACKGROUND_COLOR|escape:'htmlall':'UTF-8'}{/if}">
                                </td>
                                <td width="15"></td>
                                <td class="vertBorder" width="15"></td>
                                <td valign="middle" align="left">{l s='Font color' mod='openpayprestashop'}</td>
                                <td valign="middle" align="left">
                                    <input type="text" name="openpay_font_color" placeholder="{l s='Color in hexadecimal' mod='openpayprestashop'}" value="{if $openpay_configuration.OPENPAY_FONT_COLOR}{$openpay_configuration.OPENPAY_FONT_COLOR|escape:'htmlall':'UTF-8'}{/if}">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" class="td-noborder save"><input type="submit" class="button" name="SubmitOpenpay" value="{l s='Save configuration' mod='openpayprestashop'}" /></td>
                </tr>
            </table>
        </fieldset>
        <fieldset class="openpay-cc-numbers">
            <legend><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/cc-icon.gif" alt="" />{l s='Test card numbers' mod='openpayprestashop'}</legend>
            <table cellspacing="0" cellpadding="0" class="openpay-cc-numbers">
                <thead>
                    <tr>
                        <th>{l s='Number' mod='openpayprestashop'}</th>
                        <th>{l s='Card type' mod='openpayprestashop'}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td class="number"><code>4111111111111111</code></td><td>Visa</td></tr>
                    <tr><td class="number"><code>4242424242424242</code></td><td>Visa</td></tr>
                    <tr><td class="number"><code>5555555555554444</code></td><td>MasterCard</td></tr>
                    <tr><td class="number"><code>5105105105105100</code></td><td>MasterCard</td></tr>
                    <tr><td class="number"><code>345678000000007</code></td><td>American Express</td></tr>
                    <tr><td class="number"><code>343434343434343</code></td><td>American Express</td></tr>
                    <tr><td class="number"><code>4222222222222220</code></td><td>{l s='Card was declined' mod='openpayprestashop'}</td></tr>
                    <tr><td class="number"><code>4000000000000069</code></td><td>{l s='Card was expired' mod='openpayprestashop'}</td></tr>
                    <tr><td class="number"><code>4444444444444448</code></td><td>{l s='Card has insufficient funds' mod='openpayprestashop'}</td></tr>
                </tbody>
            </table>
        </fieldset>
    </form>
    <div class="clear"></div>

</div>

<script type="text/javascript">
$(document).ready(function() {

    $('input:radio[name=openpay_mode]').click(function() {
        updateOpenpaySettings();
    });

});

function updateOpenpaySettings()
{
    if ($('input:radio[name=openpay_mode]:checked').val() == 1)
        $('fieldset.openpay-cc-numbers').hide(1000);
    else
        $('fieldset.openpay-cc-numbers').show(1000);
}

</script>