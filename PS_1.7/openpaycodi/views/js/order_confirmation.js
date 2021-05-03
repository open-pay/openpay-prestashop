$(document).ready(function(){
    var due_date = $( "#due_date" ).val();
    var id_order = $( "#id_order" ).val();
    var module_dir = $( "#module_dir" ).val();

    var countDownDate = new Date(due_date).getTime();
    fetchStatus();

    function fetchStatus(){
        
        $.ajax({
            type: 'POST',
            url: '__PS_BASE_URI__/index.php?fc=module&module=openpaycodi&controller=fetchstatus' ,
            headers: { "cache-control": "no-cache" },
            dataType: 'json',
            data: 	{
                        ajax: true,
                        order_id: id_order,
                    }  ,
            success: function(response) {
                if(response.status_code == 200){
                    switch(response.status_order){
                        case 'completed':
                            clearInterval(x);
                            $('#CoDiImage').attr('src', '/modules/openpaycodi/views/img/codi/check.png');
                            $('.codi__icon').hide();
                            $('#CodiTimerTxt').html('Su pago ha sido &nbsp;');
                            $('#CoDiTimer').html('completado');
                            $('#CoDiTimer').attr('color', '#34a964');
                            console.log('completed');
                        break;
                        case 'cancelled':
                            clearInterval(x);
                            $("#CodiTimerTxt").html("Su pago ha &nbsp;");
                            $("#CoDiTimer").html("expirado");
                        break;
                        case 'waiting_cash_payment':
                            console.log('waiting_cash_payment'); 
                        break;
                    }
                }else{

                }
              console.log("SUCCESS22",response);
        },
        error: function(response) {
          console.log("ERROR",response);
            },
        });
    }

    var x = setInterval(function() {
        // Get today's date and time
        var now = new Date().getTime();
        // Find the distance between now and the count down date
        var distance = countDownDate - now;

        // Time calculations for days, hours, minutes and seconds
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        var timer = "Completa tu compra en:&nbsp; ";

        if(days > 0)
            timer += days + "d ";

        if(hours > 0)
            timer += ((hours < 10) ? "0" + hours : hours) + "h ";

        if(minutes > 0)
            timer += ((minutes < 10) ? "0" + minutes : minutes) + "m ";

        if(seconds >= 0)
            timer += ((seconds < 10) ? "0" + seconds : seconds ) + "s ";

        if(seconds % 9 == 0){
            fetchStatus()
        }

        // Display the result in the element with id="demo"
        $("#CoDiTimer").html(timer);

        // If the count down is finished, write some text
        if (distance < 0) {
            clearInterval(x);
        }
    }, 1000);
});