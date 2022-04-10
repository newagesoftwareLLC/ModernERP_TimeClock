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
        ProcessData();
        
    });
    $("#empid").on('keyup', function (e) {
        if ($('#empid').val().length >= MIN_EMPID_CHARS && $('#empid').val().length <= MAX_EMPID_CHARS)
            {
                console.log("MIN REACHED");
                $('#enterbtn').prop('disabled', false);
            }
        if (e.keyCode == 13) {
            $("#enterbtn").click();
        }
    });
    $("#punch_notification").hide();
});

function ProcessData() {
    //console.log("starttime=" + document.getElementById("starttime").value + " endtime=" + document.getElementById("endtime").value);
    $.ajax({
        url: APIURL + "/punches?starttime=" + new Date().toISOString().substr(0, 10) + "&endtime=" + new Date().toISOString().substr(0, 10),
        type: 'GET',
        dataType: "json",
        success: DataProcessed
    });
}

function DataProcessed(data) {
    var i = 0;
    var PunchIn = true;

    Object.entries(data).forEach(([key, value]) => {
        if (!value.empid.includes("111")) { return; }
        i++;
    });

    if(i % 2 == 0 || i == 0) {
        PunchIn = true;
    } else {
        PunchIn = false;
    }

    $.ajax({
        url: APIURL + "/punch",
        type: 'POST',
        data: { empid: $('#empid').val(), punchin: PunchIn },
        success: function() {
            if(i % 2 == 0 || i == 0) {
                $('#punch_notification').html("PUNCHED IN"); 
            } else {
                $('#punch_notification').html("PUNCHED OUT"); 
            }
            
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
}