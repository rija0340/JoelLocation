
$(window).load(function () {
    var datedebutplanning;
    var dateValue;
    ganttInit();
    getElems();

    $.ajax({
        type: 'GET',
        url: '/planningGeneralData',
        timeout: 3000,
        success: function (data) {
            ganttData(data);
        },
        error: function () {
            alert('La requête n\'a pas abouti');
        }
    });

    function ganttInit() {
        gantt.config.readonly = true;
        gantt.config.columns = [
            {
                name: "text",
                label: "RESSOURCES",
                tree: true,
                width: 175,
                resize: true
            },
        ];
        gantt.config.scales = [
            {
                unit: "day",
                step: 1,
                format: "%j %M"
            }
        ];

        // gantt.config.start_date = new Date(2021, 02, 29);
        // gantt.config.end_date = new Date(2021, 03, 29);
        gantt.i18n.setLocale("fr");
        gantt.init("gantt_here");
    }
    function ganttData(data) {
        var arrData = [];
        var len = data.length;
        for (var i = 0; i < len; i++) {
            arrData.push({
                id: data[i].id,
                text: data[i].text + " " + data[i].start_date + " - " + data[i].end_date,
                start_date: data[i].start_date,
                end_date: data[i].end_date
            });
        }
        gantt.parse({ data: arrData });
    }

    function getElems() {
        datedebutplanning = document.getElementById('datedebutplanning');
        datedebutplanning.valueAsDate = new Date();
    }

    datedebutplanning.onchange = function () {
        dateValue = this.value;
        setDateDebut(dateValue);

    }

    function setDateDebut(dateValuetest) {

        var test = new Date(dateValuetest);
        // gantt.config.start_date = new Date(test);
        ganttInit();
        gantt.init("gantt_here", test);
        console.log("ity ilay date " + test);

    }


});


