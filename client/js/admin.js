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
    var StoredDateTime = [];
    var hours = [];
    var minutes = [];
    var seconds = [];
    var TotalPunches = 0;
    document.getElementById("data_list").innerHTML = ""; // clear old data
    select = document.getElementById("data_list");
    TotalPunches = Object.keys(data).length;
    var i = 1;
    Object.entries(data).forEach(([key, value]) => {
        if (!value.empid.includes(employee_filter.value) && employee_filter.value != "") { return; }
        var opt = document.createElement('tr');
        var DBDateTime = new Date(value.datetime);

        if (empid == 0) empid = value.empid; // set our initial empid
        if (hours[value.empid] === undefined) {
            hours[value.empid] = 0;
            minutes[value.empid] = 0;
            seconds[value.empid] = 0;
        }

        if (value.punchin == "true") {
            StoredDateTime[value.empid] = new Date(value.datetime);
        }
        else {
            hours[value.empid] += Math.abs(new Date(value.datetime).getHours()-StoredDateTime[value.empid].getHours());
            minutes[value.empid] += Math.abs(new Date(value.datetime).getMinutes()-StoredDateTime[value.empid].getMinutes());
            seconds[value.empid] += Math.abs(new Date(value.datetime).getSeconds()-StoredDateTime[value.empid].getSeconds());
        }
        
        if (empid != value.empid){
            opt.innerHTML = '<td><hr></td><td><hr></td><td>TOTAL ' + hours[value.empid] + 'h ' + minutes[value.empid] + 'm ' + seconds[value.empid] + 's</td>';
            select.appendChild(opt);
        }
        
        var opt2 = document.createElement('tr');
        opt2.innerHTML = '<td>' + value.empid + '</td><td>' + DBDateTime.toLocaleTimeString('en-US', { timeZone: 'America/New_York' }) + '</td><td>' + DBDateTime.toLocaleDateString() + '</td>';
        select.appendChild(opt2);

        if (i == TotalPunches){
            opt.innerHTML = '<td><hr></td><td><hr></td><td>TOTAL ' + hours[value.empid] + 'h ' + minutes[value.empid] + 'm ' + seconds[value.empid] + 's</td>';
            select.appendChild(opt);
        }
        
        empid = value.empid;
        i++;
    });
}