// changement de langue pour le table datatable plugin de jquery
$(document).ready(function () {
    $("#datatable").dataTable().fnDestroy();
    $('#datatable').dataTable({
        columnDefs: [
            {
                targets: 1,
                render: function (data, type, row) {
                    if (type === 'display') {
                        var date = moment(data);
                        return date.isValid() ? date.format('DD-MM-YYYY HH:mm') : data;
                    }
                    return data;
                },
                type: 'date-euro'
            }
        ],
        "scrollX": true,
        "order": [[1, "desc"]],
        "language": languages_fr
        // "bDestroy": true
    });
});
