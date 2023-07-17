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
<section class="codi">
    <div class="codi__header">
        <div class="codi__icon">
            <img alt="" src="/modules/openpaycodi/views/img/codi/QR.svg" alt="QR">
            <div class="codi__text codi__text--small">Utilizar la aplicación móvil de banco para hacer el pago de la notificación</div>
        </div>
            <div class="codi__subtitle">Pago con CoDi®</div>
        </div>
        <div class="codi__content">
            <input id="due_date" name="due_date" type="hidden" value="{$openpay_order.due_date}">
            <input id="id_order" name="id_order" type="hidden" value="{$openpay_order.id_order}">
            <figure class="codi__image">
                <img id="CoDiImage" src="{$openpay_order.barcode_url|escape:'htmlall':'UTF-8'}" alt="QR CoDi®" />
            </figure>
            <div class="codi__information">
                <div class="codi__amount"></div>
                <div class="codi__currency"></div>
            </div>
            <div class="codi__expiration">
                <div id="CodiTimerTxt" class="codi__text codi__text--timer"></div>
                <div id="CoDiTimer" class="codi__timer"></div>
            </div>
        <div>
    </div>
</section> 
