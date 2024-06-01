// datatable-config.js
var datatableConfig = function (dateColumnIndex) {
    return {
        columnDefs: [
            {
                targets: dateColumnIndex,
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
        "order": [
            [dateColumnIndex, "desc"]
        ],
        "language": languages_fr
    };
};