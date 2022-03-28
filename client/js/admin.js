$(document).ready(function () {
    $('#starttime').val(new Date().toISOString().substr(0, 10));
    $('#endtime').val(new Date().toISOString().substr(0, 10));
    FetchData();

    $( '#starttime' ).change(function() {
        console.log("start time changed");
        FetchData();
    });
    $( '#endtime' ).change(function() {
        console.log("end time changed");
        FetchData();
    });
    $( '#employee_filter' ).on('input', function() {
        console.log("employee_filter changed");
        FetchData();
    });
});

function FetchData() {
    console.log("starttime=" + document.getElementById("starttime").value + " endtime=" + document.getElementById("endtime").value);
    $.ajax({
        url: APIURL + "/punches?starttime=" + document.getElementById("starttime").value + "&endtime=" + document.getElementById("endtime").value,
        type: 'GET',
        dataType: "json",
        success: DisplayData
    });
}

function DisplayData(data) {
    var empid = 0;
    var hours = new Date();
    document.getElementById("data_list").innerHTML = ""; // clear old data
    select = document.getElementById("data_list");
    Object.entries(data).forEach(([key, value]) => {
        if (!value.empid.includes(employee_filter.value) && employee_filter.value != "") { return; }
        var opt = document.createElement('tr');
        var DBDateTime = new Date(value.datetime);
        if (empid != value.empid){
            if (empid != 0) {
                hours += value.datetime;
                opt.innerHTML = '<td><hr></td><td><hr></td><td>TOTAL: ' + hours.getHours() + '</td>'; // FIX THIS
                select.appendChild(opt);
            }
            empid = value.empid;
        }
        var opt2 = document.createElement('tr');
        opt2.innerHTML = '<td>' + value.empid + '</td><td>' + DBDateTime.toLocaleTimeString('en-US', { timeZone: 'America/New_York' }) + '</td><td>' + DBDateTime.toLocaleDateString() + '</td>';
        select.appendChild(opt2);
    });
}