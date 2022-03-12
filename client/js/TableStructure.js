
   
$(document).ready(function() {

    var columnDefs = [
        {
            data: "_id",
            title: "OBJ_ID",
            type: "hidden",
            visible: false,
            searchable: false
        },
        {
            data: "empid",
            title: "EMPID"
        },
        {
            data: "start",
            title: "START"
        },
        {
            data: "end",
            title: "END"
        },
        {
            data: "lunch",
            title: "LUNCH"
        }
    ];

    var myTable;

    myTable = $('#example').DataTable({
        "sPaginationType": "full_numbers",
        ajax: {
            url : APIURL + "/punches",
            dataSrc : ''
        },
        columns: columnDefs,
        dom: 'Bfrtip',        // Needs button container
        select: 'single',
        pageLength: 15,
        order: [[ 0, "desc" ]],
        responsive: true,
        altEditor: true,     // Enable altEditor
        buttons: [
            {
                text: 'Add',
                name: 'add'        // do not change name
            },
            {
                extend: 'selected', // Bind to Selected row
                text: 'Edit',
                name: 'edit'        // do not change name
            },
            {
                extend: 'selected', // Bind to Selected row
                text: 'Delete',
                name: 'delete'      // do not change name
            },
            {
                text: 'Refresh',
                name: 'refresh'      // do not change name
            }
        ],
        onAddRow: function(datatable, rowdata, success, error) {
            $.ajax({
                url: APIURL + "/punch",
                type: 'POST',
                data: rowdata,
                success: function() { $('#example').DataTable().ajax.reload(); $(".modal").trigger('click'); },
                error: error,
		        autoClose: true
            });
        },
        onDeleteRow: function(datatable, rowdata, success, error) {
            $.ajax({
                url: APIURL + "/punch" + "?delete",
                type: 'POST',
                data: rowdata,
                success: function() { $('#example').DataTable().ajax.reload(); $(".modal").trigger('click'); },
                error: error,
		        autoClose: true
            });
        },
        onEditRow: function(datatable, rowdata, success, error) {
            $.ajax({
                url: APIURL + "/punch" + "?edit",
                type: 'POST',
                data: rowdata,
                success: function() { $('#example').DataTable().ajax.reload(); $(".modal").trigger('click'); },
                error: error,
		        autoClose: true
            });
        }
    });


});