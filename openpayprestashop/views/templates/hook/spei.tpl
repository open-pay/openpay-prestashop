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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="payment_module">
    <div class="payment_module" >
        <div class="openpay-form-container">
            <h3 class="openpay_title mt30   ">Pago con transferencia electrónica (SPEI)</h3>

            <div class="row">
                <div class="col-md-4">
                    <img src="{$module_dir}/views/img/spei.png">
                </div>
                <div class="col-md-4">
                    <h4 class="subtitle">¿Qué es SPEI?</h4>
                    <p style="font-size: 14px;">El SPEI es un sistema de pagos para permitir a los clientes de los bancos enviar y recibir transferencias electrónicas de dinero en cuestión de segundos. <small><a target="_blank" href="http://www.openpay.mx/bancos.html">Consulta los bancos soportados</a></small>.</p>
                </div>
            </div>

            <h4 class="subtitle mt30 mb30">Pasos para tu pago por transferencia interbancaria</h4>

            <div class="row mb30">
                <div class="col-md-4 center">
                    <div><img src="{$module_dir}views/img/step1.png"></div>
                    <p>Haz clic en el botón "Generar CLABE", donde tu compra quedará en espera de que realices tu pago.</p>
                </div>
                <div class="col-md-4 center">
                    <div><img src="{$module_dir}views/img/step_spei.png"></div>
                    <p>Sigue la guía para realizar el pago SPEI a través del portal de tu banco.</p>
                </div>
                <div class="col-md-4 center">
                    <div><img src="{$module_dir}views/img/step3.png"></div>
                    <p>Inmediatamente después de recibir tu pago te enviaremos un correo electrónico con la confirmación de pago.</p>
                </div>
            </div>

            <form data-ajax="false" action="{$validation_url}" method="POST" id="openpay-spei-form">
                <input type="hidden" name="payment_method" value="bank_account" id="payment_method">
                <div class="row">
                    <div class="pull-right">
                        <input type="submit" value="{l s='Generar CLABE' mod='openpayprestashop'}" class="{if $openpay_ps_version >= '1.5'}openpay-submit-button {/if}exclusive" data-icon="check" data-iconpos="right" data-theme="b" />
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>