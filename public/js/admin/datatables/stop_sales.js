// changement de langue pour le table datatable plugin de jquery
$(document).ready(function () {
    $("#datatable").dataTable().fnDestroy();
    $('#datatable').dataTable({
        columnDefs: [
            {
                targets: 0,
                render: $.fn.dataTable.render.moment('DD-MM-YYYY')
            }
        ],
        "scrollX": true,
        "order": [[0, "desc"]],
        "language": languages_fr
        // "bDestroy": true
    });
});
