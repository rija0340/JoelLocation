// changement de langue pour le table datatable plugin de jquery
$(document).ready(function () {
    $("#datatable").dataTable().fnDestroy();
    $('#datatable').dataTable({

        "scrollX": true,
        "order": [[0, "desc"]],
        "language": languages_fr
        // "bDestroy": true
    });
});
