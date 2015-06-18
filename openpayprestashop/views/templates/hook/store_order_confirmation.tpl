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
        background-color: {$openpay_order.bg_color};
        color: {$openpay_order.font_color};
    }

    .Big_Bullet span{
        background-color: {$openpay_order.bg_color};
    }

</style>

<div class="container container-receipt">
    <div class="row">
        <div class="col-sm-6 col-md-6 col-lg-6">
            <img class="img-responsive center-block" src="{$openpay_order.logo}" alt="Logo">
        </div>
        <div class="col-sm-6 col-md-6 col-lg-6">
            <div class="mt30 pull-right">
                <span style="font-size: 20px;font-weight: lighter;">Servicio a pagar</span>
                <img class="logo_paynet" src="/modules/openpayprestashop/views/img/paynet_logo.png" alt="Logo">
            </div>
        </div>
    </div>

    <div class="row data">

        <div class="col-sm-6 col-md-6 col-lg-6">
            <div class="Big_Bullet">
                <span></span>
            </div>
            <h1><strong>Fecha límite de pago</strong></h1>
            <strong>{$openpay_order.due_date}</strong>
            <div class="col-lg-12 datos_pago" style="margin-left: 20px;">
                <img width="300" src="{$openpay_order.barcode_url}" alt="Código de Barras">
                <span style="font-size: 15px">{$openpay_order.barcode}</span>
                <br/>
                <p>En caso de que el escáner no sea capaz de leer el código de barras, escribir la referencia tal como se muestra.</p>
            </div>

        </div>

        <div class="col-sm-6 col-md-6 col-lg-6">
            <div class="data_amount">
                <h2>Total a pagar</h2>
                <h2 class="amount">${$openpay_order.amount}<small> {$openpay_order.currency}</small></h2>
                <h2 style="margin-top: 0px;">+8 pesos por comisión</h2>
            </div>
        </div>


    </div>

    <div class="row data">

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="Big_Bullet">
                <span></span>
            </div>
            <h1 style="padding-top: 7px;"><strong>Detalles de la compra</strong></h1>
            <div class="datos_tiendas">
                <table class="detail">
                    <tr class="odd">
                        <td width="40%">Orden:</td>
                        <td width="60%">122015</td>
                    </tr>
                    <tr class="even">
                        <td>Fecha y hora:</td>
                        <td>{$openpay_order.date}</td>
                    </tr>
                    <tr class="odd">
                        <td>Correo electrónico:</td>
                        <td>{$openpay_order.email}</td>
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
            <h1><strong>Como realizar el pago</strong></h1>
            <ol style="margin-left: 30px;">
                <li>Acude a cualquier tienda afiliada</li>
                <li>Entrega al cajero el código de barras y menciona que realizarás un pago de servicio Paynet</li>
                <li>Realizar el pago en efectivo por ${$openpay_order.amount} {$openpay_order.currency} (más $8 pesos por comisión)</li>
                <li>Conserva el ticket para cualquier aclaración</li>
            </ol>
            <p style="margin-left: 30px; font-size: 12px;">
                Si tienes dudas comunícate a {$openpay_order.shop_name} al teléfono {$openpay_order.phone} o al correo {$openpay_order.email}
            </p>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <h1><strong>Instrucciones para el cajero</strong></h1>
            <ol>
                <li>Ingresar al menú de Pago de Servicios</li>
                <li>Seleccionar Paynet</li>
                <li>Escanear el código de barras o ingresar el núm. de referencia</li>
                <li>Ingresa la cantidad total a pagar</li>
                <li>Cobrar al cliente el monto total más la comisión de $8 pesos</li>
                <li>Confirmar la transacción y entregar el ticket al cliente</li>
            </ol>
        </div>
    </div>

    <div class="row marketing">

        <div class="col-lg-12" style="text-align:center;">
            {for $i=1 to 4}
                <div class="col-xs-3 store-image">
                    <img src="/modules/openpayprestashop/views/img/stores/{sprintf("%02d", $i)}.png">
                </div>
            {/for}
        </div>
        <div class="col-lg-12" style="text-align:center;">
            <div class="link_tiendas">¿Quieres pagar en otras tiendas? visita: <a target="_blank" href="http://www.openpay.mx/tiendas-de-conveniencia.html">www.openpay.mx/tiendas</a></div>
        </div>
        <div class="col-lg-6 mb30" style="text-align:right; margin-top:5px;">
            <a href="javascript:void(0)" class="btn btn-info btn-lg" onclick="window.print();">Imprimir recibo</a>
        </div>
        <div class="col-lg-6 mb30" style="text-align:left; margin-top:5px;">
            <a  class="btn btn-success btn-lg" href="/">Seguir comprando</a>
        </div>
    </div>

    <div class="footer">
        <img src="/modules/openpayprestashop/views/img/powered_openpay.png" alt="Powered by Openpay">
    </div>

</div>

<script type="text/javascript">
  $(document).ready(function() {
        window.print();
  });
</script>