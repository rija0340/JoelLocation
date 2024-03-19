// changement de langue pour le table datatable plugin de jquery
$(document).ready(function () {
    $("#datatable").dataTable().fnDestroy();
    $('#datatable').dataTable({
        columnDefs: [
            {
                targets: 2,
                render: $.fn.dataTable.render.moment('DD-MM-YYYY')
            }
        ],
        "scrollX": true,
        "order": [[2, "desc"]],
        "language": languages_fr
        // "bDestroy": true
    });
});
