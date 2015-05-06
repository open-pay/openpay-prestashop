<div class="container container-receipt">
    <div class="row header">
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <img style="background-color: #fff;" class="img-responsive center-block" src="/img/logo.jpg" alt="Logo">
        </div>	
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">	
            <p class="Yellow2">Esperamos tu pago</p>
        </div>	
    </div>

    <div class="row">
        <div class="col-xs-9 col-sm-8 col-md-8 col-lg-8">
            <h1><strong>Total a pagar</strong></h1>
            <h2 class="amount">${$openpay_order.amount}<small> {$openpay_order.currency}</small></h2>
            <h1><strong>Fecha límite de pago:</strong></h1>
            <h1>{$openpay_order.due_date}</h1>
        </div>
        <div class="col-xs-3 col-sm-4 col-md-4 col-lg-4">
            <a href="http://www.openpay.mx/bancos.html" target="_blank"><img class="img-responsive spei" src="/modules/openpaystores/img/spei.gif"  alt="SPEI"></a>
        </div>
    </div>

    <div class="row marketing">
        <h1 style="padding-bottom:10px;"><strong>Datos para transferencia electrónica</strong></h1>
        <div class="col-lg-12 datos_pago">
            <table>
                <tr>
                    <td style="width: 50%;">Banco:</td>
                    <td>STP</td>
                </tr>    
                <tr>
                    <td>CLABE:</td>
                    <td>{$openpay_order.clabe|escape:html:'UTF-8'}</td>
                </tr>    
                <tr>
                    <td>Referencia:</td>
                    <td>{$openpay_order.reference|escape:html:'UTF-8'}</td>
                </tr>    
                <tr>
                    <td>Beneficiario:</td>
                    <td>{$openpay_order.shop_name|escape:html:'UTF-8'}</td>
                </tr>    
            </table>
        </div>	        



        <div class="col-lg-12" style="text-align: center; margin-top: 20px;">
            <p>¿Tienes alguna dudas o problema? Llámanos al teléfono</p>
            <h4>{$openpay_order.phone|escape:html:'UTF-8'}</h4>
            <p>O escríbenos a</p>
            <h4>{$openpay_order.email|escape:html:'UTF-8'}</h4>
            <a type="button" class="btn btn-success btn-lg" onclick="window.print();">Imprimir</a>
        </div>
    </div>

    <div class="footer">
        <img src="/modules/openpaystores/img/powered_openpay.png" alt="Powered by Openpay">
    </div>

</div>        

