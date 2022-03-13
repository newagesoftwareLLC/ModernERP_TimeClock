$(document).ready(function () {
    FetchData();
});

function FetchData() {
    document.getElementById("data_list").innerHTML = ""; // clear old data
    $.ajax({
        url: APIURL + "/punches",
        type: 'GET',
        dataType: "json",
        success: DisplayData
    });
}

function DisplayData(data) {
    select = document.getElementById("data_list");
    Object.entries(data).forEach(([key, value]) => {
        var opt = document.createElement('tr');
        opt.innerHTML = '<td>' + value.empid + '</td><td>' + value.datetime + '</td><td>' + '' + '</td>' + '</td><td>' + '' + '</td>';
        select.appendChild(opt);
    });
}