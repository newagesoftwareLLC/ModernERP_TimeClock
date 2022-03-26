$(document).ready(function(){
    for (let i = 0; i < 10; i++) {
        $('#key' + i).click(function () {
            $('#empid').val($('#empid').val() + i);
            console.log("btn press MIN:" + MIN_EMPID_CHARS + " EMP ID CHARS:" + $('#empid').val().length);
            if ($('#empid').val().length >= MIN_EMPID_CHARS && $('#empid').val().length <= MAX_EMPID_CHARS)
            {
                console.log("MIN REACHED");
                $('#enterbtn').prop('disabled', false);
            } else {
                $('#enterbtn').prop('disabled', true);
            }
        });
    }
    $('#bkspc').click(function () {
        $('#empid').val($('#empid').val().slice(0, -1));
    });
    $('#enterbtn').click(function () {
        $.ajax({
                url: APIURL + "/punch",
                type: 'POST',
                data: { empid: $('#empid').val() },
                success: function() { 
                    $('#punch_notification').html("PUNCHED"); 
                    $("#punch_notification").show(); 
                    setTimeout(function() { 
                        $("#punch_notification").fadeOut(); 
                    }, 3000); 
                    $('#empid').val(""); 
                    $('#empid').focus(); 
                    $('#enterbtn').prop('disabled', true); 
                },
                error: function() { 
                    $('#punch_notification').html("ERROR!"); 
                    $("#punch_notification").show(); 
                    setTimeout(function() { 
                        $("#punch_notification").fadeOut(); 
                    }, 3000); 
                },
                autoClose: true
            });
    });
    $("#empid").on('keyup', function (e) {
        if (e.keyCode == 13) {
            $("#enterbtn").click();
        }
    });
    $("#punch_notification").hide();
});