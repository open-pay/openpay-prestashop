## Plugin para PrestaShop - Openpay Checkout Lending 

=================

## Instalación

1. Comprimir el directorio "openpaycheckoutlending" en formato .zip y subirlo por el manejador de módulos de PrestaShop. Si prefiere la opción vía FTP deberá subir el directorio "openpaycheckoutlending" al directorio "modules" ubicado en el directorio raíz de PrestaShop.

2. En la administración de Prestashop (backoffice) ir a: **Módulos > Módulos** y buscar el nombre del módulo: **Openpay Checkout Lending** y dar click en "Instalar". Una vez instlado recibirá el siguiente mensaje: " Módulo(s) instalado correctamente."

3. Agregar las llaves, ir al panel de administración de Openpay (https://sandbox-dashboard.openpay.mx/login), copiar y pegar las llaves, dentro de las configuraciones del módulo en el panel de administración de PrestaShop (backoffice).

## Changelog

**1.0.1**
- Modificación del flujo de pago y creación de orden.
- Adición de capa de seguridad al flujo de compra.
- Envio de correo al cliente al ejecutar cambio de status a "En espera de aprobación" desactivado por defecto.
