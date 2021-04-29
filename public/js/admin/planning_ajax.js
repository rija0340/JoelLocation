
$(document).ready(function () {
    gantt.config.readonly = true;
    gantt.config.columns = [
        {
            name: "text",
            label: "RESSOURCES",
            tree: true,
            width: "*",
            resize: true
        },
        // {name:"duration",   label:"Durée",   align:"center" },
        // {name:"start_date", label:"Start time", align:"center" },
        // {name:"end_date",   label:"End date",   align:"center" },
    ];
    gantt.config.scales = [
        {
            unit: "day",
            step: 1,
            format: "%j %M"
        }
    ];

    $(document).ready(function () {
        $.ajax({
            type: 'GET',
            url: '/planningGeneralData',
            timeout: 3000,
            success: function (data) {
                // var string = JSON.stringify(data);
                // console.log('ireto ny entna : ')
                // console.log(string);
                gantt.init("gantt_here");
                gantt.parse({ data: data });
            },
            error: function () {
                alert('La requête n\'a pas abouti');
            }
        });
    });
});