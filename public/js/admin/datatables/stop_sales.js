// datatable-init.js
$(document).ready(function () {
    var $datatable = $('#datatable'); // Replace 'datatable' with the actual ID of your table
    var dateColumnIndex = 0;
    // Replace 2 with the actual index of the date column

    // Destroy any existing DataTables instance
    $datatable.dataTable().fnDestroy();

    // Initialize DataTables with the configuration
    $datatable.dataTable(datatableConfig(dateColumnIndex));
});