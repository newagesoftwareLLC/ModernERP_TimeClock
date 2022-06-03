$(document).ready(function () {
    $('#starttime').val(new Date().toLocaleDateString('fr-CA', { timeZone: 'America/New_York' }));
    $('#endtime').val(new Date().toLocaleDateString('fr-CA', { timeZone: 'America/New_York' }));
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

var EmpInfo = null;

function DisplayEmployees(data) {
    Object.entries(data).forEach(([key, value]) => {
        EmpInfo[value.id].push({ name: value.name });
    });

    $.ajax({
        url: APIURL + "/punches?starttime=" + document.getElementById("starttime").value + "&endtime=" + document.getElementById("endtime").value,
        type: 'GET',
        dataType: "json",
        success: DisplayData
    });
}

function FetchData() {
    console.log("starttime=" + document.getElementById("starttime").value + " endtime=" + document.getElementById("endtime").value);
    $.ajax({
        url: APIURL + "/employees",
        type: 'GET',
        dataType: "json",
        success: DisplayEmployees
    });
}

function secondsToTime(e){
    var h = Math.floor(e / 3600).toString().padStart(2,'0'),
        m = Math.floor(e % 3600 / 60).toString().padStart(2,'0'),
        s = Math.floor(e % 60).toString().padStart(2,'0');
    
    return h + 'h ' + m + 'm ' + s + 's';
    //return `${h}:${m}:${s}`;
}

function DisplayData(data) {
    var empid = 0;
    var StoredDateTime = [];
    var seconds = [];
    var TotalPunches = 0;
    document.getElementById("data_list").innerHTML = ""; // clear old data
    select = document.getElementById("data_list");
    TotalPunches = Object.keys(data).length;
    var i = 1;
    Object.entries(data).forEach(([key, value]) => {
        var DBDateTime = new Date(value.datetime);

        //console.log("empid:" + value.empid + " punchin:" + value.punchin + " timedate:" + value.datetime);

        if (empid == 0) empid = value.empid; // set our initial empid
        if (seconds[value.empid] === undefined) seconds[value.empid] = 0;

        if (value.punchin == "true") {
            StoredDateTime[value.empid] = new Date(value.datetime);
            console.log("StoredDT:" + StoredDateTime[value.empid]);
        }
        else {
            console.log("datetime diff:" + new Date(value.datetime));
            seconds[value.empid] += Math.abs((new Date(value.datetime).getTime() / 1000)-(StoredDateTime[value.empid].getTime() / 1000));
            console.log(seconds[value.empid] + "s");
        }
        
        if (empid != value.empid && empid.includes(employee_filter.value)){
            var opt = document.createElement('tr');
            opt.innerHTML = '<td><hr></td><td><hr></td><td>TOTAL ' + secondsToTime(seconds[empid]) + '</td>';
            select.appendChild(opt);
            //console.log("empid change OLD:" + empid + " NEW:" + value.empid);
        }

        if (!value.empid.includes(employee_filter.value) && employee_filter.value != "") { empid = value.empid; return; }
        
        var opt2 = document.createElement('tr'); // TODO: Get employee name 
        opt2.innerHTML = '<td>' + value.empid + '</td><td>' + DBDateTime.toLocaleTimeString('en-US', { timeZone: 'America/New_York' }) + '</td><td>' + DBDateTime.toLocaleDateString() + '</td>';
        select.appendChild(opt2);

        if (i == TotalPunches){
            var opt3 = document.createElement('tr');
            opt3.innerHTML = '<td><hr></td><td><hr></td><td>TOTAL ' + secondsToTime(seconds[value.empid]) + '</td>';
            select.appendChild(opt3);
        }
        
        empid = value.empid;
        i++;
    });
}