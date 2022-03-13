$(document).ready(function () {
    $("#starttime").val(new Date().toISOString().substr(0, 10));
    $("#endtime").val(new Date().toISOString().substr(0, 10));
    FetchData();
});

$( "#starttime" ).change(function() {
    FetchData();
});
$( "#endtime" ).change(function() {
    FetchData();
});

function FetchData() {
    $.ajax({
        url: APIURL + "/punches?starttime=" + document.getElementById("starttime") + "&endtime=" + document.getElementById("endtime"),
        type: 'GET',
        dataType: "json",
        success: DisplayData
    });
}

function DisplayData(data) {
    document.getElementById("data_list").innerHTML = ""; // clear old data
    select = document.getElementById("data_list");
    var dict = new Object();
    Object.entries(data).forEach(([key, value]) => {
        var opt = document.createElement('tr');
        opt.innerHTML = '<td>' + value.empid + '</td><td>' + new Date(value.datetime).toLocaleTimeString('en-US', { timeZone: 'America/New_York' }); + '</td>';
        select.appendChild(opt);
    });
}