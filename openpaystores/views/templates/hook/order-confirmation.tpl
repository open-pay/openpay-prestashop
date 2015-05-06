<div class="container container-receipt">
    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <img class="img-responsive center-block" src="{$openpay_order.logo|escape:html:'UTF-8'}" alt="Logo">
        </div>	
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">	
            <p class="Logo_paynet">Servicio a pagar</p>
            <img class="img-responsive center-block Logo_paynet" src="/modules/openpaystores/img/paynet_logo.png" alt="Logo">
        </div>	
    </div>

    <div class="row data">

        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <div class="Big_Bullet">
                <span></span>
            </div>
            <h1><strong>Fecha límite de pago</strong></h1> 
            <strong>{$openpay_order.due_date}</strong>
            <div class="col-lg-12 datos_pago">
                <!--<h4>30 de Noviembre 2014, a las 2:30 AM</h4>-->
                <img style="left: -10px;" width="300" src="{$openpay_order.barcode_url|escape:html:'UTF-8'}" alt="Código de Barras">
                <span style="font-size: 14px">{$openpay_order.barcode|escape:html:'UTF-8'}</span>
                <br/>
                <p>En caso de que el escáner no sea capaz de leer el código de barras, escribir la referencia tal como se muestra.</p>
            </div>

        </div>
        
        <div class="col-xs-12 col-sm-1 col-md-1 col-lg-1"></div>
        
        <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5">
            <div class="data_amount"> 
                <h2>Total a pagar</h2>
                <h2 class="amount">${$openpay_order.amount}<small> {$openpay_order.currency }</small></h2>
                <h2 class="S-margin">+8 pesos por comisión</h2>
            </div>
        </div>


    </div>

    <div class="row data">

        <div class="col-xs-12 col-sm-11 col-md-11 col-lg-11">
            <div class="Big_Bullet">
                <span></span>
            </div>
            <h1><strong>Detalles de la compra</strong></h1> 
            <div class="col-lg-12 datos_tiendas">
                <table>
                    <tr>
                        <td width="40%">Orden:</td>
                        <td width="60%">122015</td>
                    </tr>    
                    <tr>
                        <td>Fecha y hora:</td>
                        <td>{$openpay_order.date}</td>
                    </tr>    
                    <tr>
                        <td>Correo electrónico:</td>
                        <td>{$openpay_order.email|escape:html:'UTF-8'}</td>
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
                <li>Realizar el pago en efectivo por ${$openpay_order.amount} {$openpay_order.currency } (más $8 pesos por comisión)</li>
                <li>Conserva el ticket para cualquier aclaración</li>
            </ol>	
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
    
    
    <div class="col-lg-12" style="text-align:center;">
        Para cualquier duda sobre como cobrar, por favor llamar al teléfono 01 800 300 08 08 en un horario de 8am a 9pm de lunes a domingo.
    </div>
    


    <div class="row marketing">

        <div class="col-lg-12" style="text-align:center;">
            <img width="50" src="/modules/openpaystores/img/7eleven.png" alt="7elven">
            <img width="90" src="/modules/openpaystores/img/extra.png" alt="7elven">
            <img width="90" src="/modules/openpaystores/img/farmacia_ahorro.png" alt="7elven">
            <img width="150" src="/modules/openpaystores/img/benavides.png" alt="7elven">
        </div>
        <div class="col-lg-12" style="text-align:center;">
            <div class="link_tiendas">¿Quieres pagar en otras tiendas? visita: <a target="_blank" href="http://www.openpay.mx/tiendas-de-conveniencia.html">www.openpay.mx/tiendas</a></div>
        </div>
        <div class="col-lg-12" style="text-align:center; margin-top:5px;">
            <a type="button" class="btn btn-success btn-lg" onclick="window.print();">Imprimir</a>
        </div>	  
    </div>

    <div class="footer">
        <img src="/modules/openpaystores/img/powered_openpay.png" alt="Powered by Openpay">
    </div>

</div>                     