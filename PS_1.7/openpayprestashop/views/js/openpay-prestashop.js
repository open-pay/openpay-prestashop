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
const initOpenPayEvents = () => {     
    $('#holder_name').on("cut copy paste",function(e) {
        e.preventDefault();
    });
        
    $("#holder_name").keypress(function(e){
        var keyCode = e.which;
        /*             
            65-90 - (A-Z)
            97-122 - (a-z)
            8 - (backspace)
            32 - (space)
        */
        // Not allow special 
        if (!((keyCode >= 65 && keyCode <= 90) || (keyCode >= 97 && keyCode <= 122)) && keyCode !== 8 && keyCode !== 32) {
            e.preventDefault();
        }
    });
    
    $('[data-toggle="popover"]').popover({
        trigger: 'hover',
        'placement': 'top'
    });
    
    $('#card_number').cardNumberInput();
        
    var total = openpayprestashop.total;                 
    var months = parseInt($("#interest-free").val());
    var monthly_payment = 0;       
    monthly_payment = total/months;
    var formatted = monthly_payment.toFixed(2);
    $("#monthly-payment").text(formatted);
    
    if (months > 1) {
        $("#total-monthly-payment").removeClass('hidden');
    } else {
        $("#total-monthly-payment").addClass('hidden');
    }

    $("#interest-free").change(function() {      
        monthly_payment = 0;
        months = parseInt($(this).val());     

        if (months > 1) {
            $("#total-monthly-payment").removeClass('hidden');
        } else {
            $("#total-monthly-payment").addClass('hidden');
        }

        monthly_payment = total/months;
        monthly_payment = monthly_payment.toFixed(2);

        $("#monthly-payment").text(monthly_payment);
    });
        
    $(document).on("change", "#openpay_cc", function() {
        if ($('#openpay_cc').val() !== "new") {                                 
            $('#save_cc').prop('checked', false);                
            $('#save_cc').prop('disabled', true);                 

            $('#openpay-holder-name').val("");
            $('#openpay-card-number').val("");                                     
            $('#openpay-card-expiry').val("");            
            $('#openpay-card-cvc').val("");                

            if(openpayprestashop.openpay_save_cc_option != '2') {
                $('#payment_form_openpay_cards .row div:not(.wrapper_cvv2)').hide();
            } else {
                $('#payment_form_openpay_cards').hide();
            }
        } else {            
            $('#payment_form_openpay_cards .row div:not(.wrapper_cvv2)').show();
            $('#payment_form_openpay_cards').show();                
            $('#save_cc').prop('disabled', false);
        }
    });  

    var openpay_public_key = openpayprestashop.pk;
    var openpay_merchant_id = openpayprestashop.id;
    var mode = openpayprestashop.mode;

    OpenPay.setId(openpay_merchant_id);
    OpenPay.setApiKey(openpay_public_key);
    //OpenPay.setDevelopMode(true);

    if(mode == "0"){
        OpenPay.setSandboxMode(true);
    }

    $("#payment-confirmation > .ps-shown-by-js > button").click(function(event) {    
        var myPaymentMethodSelected = $(".payment-options").find("input[data-module-name='openpayprestashop']").is(":checked");
        if (myPaymentMethodSelected){
            event.preventDefault();
            var cvc = $('#cvv2').val();
            
            //antifraudes
            OpenPay.deviceData.setup("openpay-payment-form", "device_session_id");
            
            $(this).prop('disabled', true); /* Disable the submit button to prevent repeated clicks */
            $('.openpay-payment-errors').hide();            
            $('#openpay-ajax-loader').show();    
            $('#openpay-payment-form').hide();
            
            if ($('#openpay_cc').val() !== 'new') {
                if(openpayprestashop.openpay_save_cc_option != '2'){
                    if(cvc == ""){
                        $('#alert-cvv-error').text("El cvv2 es requerido");
                        $('#cvv2').addClass('checkout-input-error');
                        $("#payment-confirmation > .ps-shown-by-js > button").prop('disabled', false);
                        $('#openpay-payment-form').show();
                        $('#openpay-ajax-loader').hide();
                        event.preventDefault();
                        return false; 
                    } else if(cvc.length < 3){
                        $('#alert-cvv-error').text("El cvv2 debe contener por lo menos 3 dígitos");
                        $('#cvv2').addClass('checkout-input-error');
                        $("#payment-confirmation > .ps-shown-by-js > button").prop('disabled', false);
                        $('#openpay-payment-form').show();
                        $('#openpay-ajax-loader').hide();
                        event.preventDefault();
                        return false; 
                    } else if(!cvc.match(/^[0-9]+$/)){
                        $('#alert-cvv-error').text("El cvv2 solo debe contener dígitos");
                        $('#cvv2').addClass('checkout-input-error');
                        $("#payment-confirmation > .ps-shown-by-js > button").prop('disabled', false);
                        $('#openpay-payment-form').show();
                        $('#openpay-ajax-loader').hide();
                        event.preventDefault();
                        return false; 
                    }
                }
                $('#openpay-payment-form').append('<input type="hidden" name="openpay_token" value="' + $('#openpay_cc').val() + '" />');
                $('#openpay-payment-form').append('<input type="hidden" name="hidden_cvv" value="'+cvc+'" />')
                $('#openpay-payment-form').get(0).submit();
                return false;
            }

            var holder_name = $('#holder_name').val();
            if(holder_name == ""){
                $('.openpay-payment-errors').fadeIn(1000);
                $('.openpay-payment-errors').text('ERROR El nombre del titular es requerido').fadeIn(1000);
                $("#payment-confirmation > .ps-shown-by-js > button").prop('disabled', false);
                $('#openpay-payment-form').show();
                $('#openpay-ajax-loader').hide();
                event.preventDefault();
                return false; 
            }
            
            return openpayFormHandler();
        }
    });

    var card_old;
    $('body').on("keyup", "#card_number", function() {
        let card = jQuery(this).val()
        let country = openpayprestashop.country;
        let show_months_interest_free = (openpayprestashop.show_months_interest_free == '1');
        let show_installments_pe = (openpayprestashop.cuotas_pe == '1');
        let card_without_space = card.replace(/\s+/g, '')
        let lng = country == 'PE' ? 6 : 8;

        if (card_without_space.length == lng) {
            if(country == "MX" && !show_months_interest_free || (country == 'PE' && !show_installments_pe)) {
                return;
            }
            var card_bin = card_without_space.substring(0, lng);

            if(card_bin != card_old) {
                getTypeCard(card_bin, country);
                card_old = card_bin; 
            }
            
        }
    })
}

function getTypeCard(cardBin, country) {
    $.ajax({
        type : "post",
        url : openpayprestashop.url_ajax,
        data : {
            card_bin : cardBin
        },
        error: function(response){
            console.log(response);
        },
        beforeSend: function () {
            jQuery("#card-container").addClass("opacity");
            jQuery(".ajax-loader").addClass("is-active");
        },
        success: function(response) {
            let data = JSON.parse(response);
            if(data.status == 'success'){
                if (data.card_type == 'CREDIT') {
                    if (country == 'MX'){
                        jQuery("#interest-free").closest(".row").show();
                    }else {
                        jQuery('#installment').closest(".row").show();
                    }
                }
                else if(data.installments && data.installments.length > 0 && 1 == openpayprestashop.cuotas_pe) {
                    jQuery('#openpay_installments_pe').closest(".row").show();
                    jQuery('#openpay_installments_pe').empty();

                    jQuery('#openpay_installments_pe').append(jQuery('<option>', {
                        value: 1,
                        text : 'Solo una cuota'
                    }));

                    if (data.withInterest || data.withInterest === null ){
                        jQuery("#installments_title").text("Cuotas con Interés");
                        jQuery('#withInterest').val(true);
                    }else{
                        jQuery("#installments_title").text("Cuotas sin Interés");
                        jQuery('#withInterest').val(false);
                    }

                    jQuery.each( data.installments, function( i, val ) {
                        if (val != 1) {
                            jQuery('#openpay_installments_pe').append(jQuery('<option>', {
                                value: val,
                                text: val + ' cuotas'
                            }));
                        }
                    });
                }
                else {
                    jQuery('#openpay_installments_pe').closest(".row").hide();
                    jQuery('#openpay_installments_pe option[value="1"]').attr("selected",true);

                    if (country == 'MX') {
                        jQuery("#interest-free").closest(".row").hide();
                        jQuery('#interest-free option[value="1"]').attr("selected",true);
                        $("#total-monthly-payment").addClass('hidden');
                    } else {
                        jQuery("#installment").closest(".row").hide();
                        jQuery('#installment option[value="1"]').attr("selected",true);
                    }
                }
            } else {
                jQuery("#installment").closest(".installments").hide();
                jQuery("#interest-free").closest(".installments").hide();
                $("#total-monthly-payment").addClass('hidden');
            }
        },
        complete: function () { 
            jQuery("#card-container").removeClass("opacity");
            jQuery(".ajax-loader").removeClass("is-active");  
        } 
    })
}

function openpayFormHandler() {
    var holder_name = $('#holder_name').val();
    var card = $('#card_number').val();
    var cvc = $('#cvv2').val();
    var year = $('#expiration_year').val();
    var month = $('#expiration_month').val();

    var data = {
        holder_name: holder_name,
        card_number: card.replace(/ /g, ''),
        expiration_month: month || 0,
        expiration_year: year || 0,
        cvv2: cvc
    };
    var hidden_card_number = data.card_number.substring(0, 6) + data.card_number.substring(data.card_number.length - 4);

    $('#openpay-payment-form').append('<input type="hidden" name="hidden_card_number" value="'+hidden_card_number+'" />');
    OpenPay.token.create(data, success_callbak, error_callbak);

    return false;
}

$(document).on('click', "#points-yes-button", function () {
    $('#use_card_points').val('true');   
    $('#openpay-payment-form').get(0).submit();
});

$(document).on('click', "#points-no-button", function () {
    $('#use_card_points').val('false');
    $('#openpay-payment-form').get(0).submit();
});   

var success_callbak = function(response) {
    $('.openpay-payment-errors').hide();
    var token_id = response.data.id;
    $('#openpay-payment-form').append('<input type="hidden" name="openpay_token" value="' + escape(token_id) + '" />');
    
    if (response.data.card.points_card && openpayprestashop.use_card_points == '1') {                        
        $("#card-points-dialog").modal("show");
    } else {
        $('#openpay-payment-form').get(0).submit();
    }                
};

var error_callbak = function(response) {
    var msg = "";
    switch (response.data.error_code) {
        case 1000:
        case 1004:
        case 1005:
            msg = openpayprestashop.Msg.service_available;
            break;

        case 1001:
            msg = openpayprestashop.Msg.incorrect_format;
            break;

        case 2005:
            msg = openpayprestashop.Msg.incorrect_expiration_date;
            break;

        case 2006:
            msg = openpayprestashop.Msg.cvv2_required;
            break;

        default: //Demás errores 400
            msg = openpayprestashop.Msg.unprocessed_request;
            break;
    }
            
    var submitBtn = $("#payment-confirmation > .ps-shown-by-js > button");

    $('.openpay-payment-errors').fadeIn(1000);
    $('.openpay-payment-errors').text('ERROR ' + response.data.error_code + '. ' + response.data.description).fadeIn(1000);
    if(response.data.error_code == '1001' || response.data.error_code == '2006'){
        $('#alert-cvv-error').text(response.data.description);
        $('#cvv2').addClass('checkout-input-error');
    }
    submitBtn.prop('disabled', false);
    $('#openpay-payment-form').show();
    $('#openpay-ajax-loader').hide();        
};

$(function() {
    if (typeof OPC === typeof undefined && typeof OnePageCheckoutPS === typeof undefined) {
        initOpenPayEvents();
    } else if (typeof OPC !== typeof undefined) {
        prestashop.on('opc-payment-getPaymentList-complete', function() {
            initOpenPayEvents();
        });
    } else if (typeof OnePageCheckoutPS !== typeof undefined) {
        $(document).on('opc-load-payment:completed', () => {
            initOpenPayEvents();
        });
    }
});
