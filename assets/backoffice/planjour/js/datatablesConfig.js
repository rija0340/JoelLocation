// datatable-config.js
import { languages_fr } from './datatables.language.fr.js';
import moment from 'moment';

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

export default datatableConfig;