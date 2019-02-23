/**
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
*  @version  Release: $Revision: 6844 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$(document).ready(function() {

    $( ".openpay-payment-form" ).on( "submit", function( event ) {
        event.stopPropagation();
        console.log($(this).attr('id'))
    });

    $( ".openpay-payment-form" ).on( "submit", function( event ) {
        event.preventDefault();
        console.log($(this).attr('id'))
    });

    if($('#openpay-payment-form').length) {
        console.log($('#openpay-payment-form'));
        $('#openpay-payment-form').addClass('FEDERICO');
    }

    $('#openpay-payment-form').submit(function(event) {
        console.log("SUBMIT");
    });

});