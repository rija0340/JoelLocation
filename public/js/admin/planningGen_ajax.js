var datedebutplanning;
var dateValue;
var startDate;
var startDateString;
var newStartDate;
var startDateTimestamp;
var endDateString;
var newEndDate;
var endDateTimestamp;
var spanElemStartDate;
var spanElemEndDate;

//affichage peride intervalle


//declaration boutons pour changement scale affichage
var btn7jours;
var btn14jours;
var btn1mois;
var btn2mois;
getElements();
addEventListner();


$(window).load(function () {

    ganttInit();
    retrieveDataAjax();
});

function retrieveDataAjax(startDatePeriode, endDatePeriode) {

    $.ajax({
        type: 'GET',
        url: '/planningGeneralData',
        timeout: 3000,
        success: function (data) {

            ganttLoadData(data, startDatePeriode, endDatePeriode);
        },
        error: function () {
            alert('La requête n\'a pas abouti');
        }
    });
}

function ganttInit(startDateScale, endDateScale) {
    gantt.config.readonly = true;
    gantt.config.columns = [
        {
            name: "text",
            label: "RESSOURCES",
            tree: false,
            width: 175,
            resize: true,

        }
    ];
    //affichage scale (organisatio date, mois, jours, année)
    gantt.config.scales = [
        {
            unit: "day",
            step: 1,
            format: "%d %M"
        }
    ];
    //test sur les bares de taches
    gantt.templates.task_text = function (start, end, task) {
        return task.client_name + " " + task.start_date.toLocaleDateString('fr-FR') + " " + task.start_time + " - " + task.real_end_date + " " + task.end_time;
    };

    //date de début et fin de l'affichage tasks
    if (startDateScale != null && endDateScale != null) {
        gantt.config.start_date = new Date(startDateScale);
        gantt.config.end_date = new Date(endDateScale);


    } else {

    }

    gantt.i18n.setLocale("fr");
    gantt.init("gantt_here");
}
function ganttLoadData(data, startDatePeriode, endDatePeriode) {

    var arrData = [];
    var len = data.length;

    for (var i = 0; i < len; i++) { //boucle sur l'objet "data" qui est un Json

        //recuperer date de data.json ensuite convertir en date js 
        startDate = data[i].start_date.date;
        startDateString = JSON.stringify(startDate);
        newStartDate = new Date(startDateString);
        startDateTimestamp = newStartDate.getTime(); //pour recuperer durée si c'est nécessaire (soustraction fin et debut) conversion timestamp nécessaire

        endDate = data[i].end_date.date;
        endDateString = JSON.stringify(endDate);
        newEndDate = new Date(endDateString);
        endDateTimestamp = newEndDate.getTime();

        var result = endDateTimestamp - startDateTimestamp; // On fait la soustraction

        var durationDays = result / (1000 * 60 * 60 * 24);

        var onDayTimestamp = 24 * 60 * 60 * 1000;

        var endDatePlusOneDay = new Date(endDateTimestamp + onDayTimestamp);

        var hour = newEndDate.getHours();


        if (hour > 0) { //on a remarqué que lorsque l'heure est different de 00:00, la durée manque une journée dans gantt

            endDatePlusOneDay = newEndDate;

        }

        arrData.push({
            id: data[i].id,
            text: data[i].text,
            start_date: newStartDate,
            start_time: newStartDate.toLocaleTimeString('fr-FR'),
            end_date: endDatePlusOneDay, //date fin dans bdd + un jour car l'affichage n'est pas correct (durée - 1jour) lorsque l'heure = 00:00
            end_time: newEndDate.toLocaleTimeString('fr-FR'),
            real_end_date: data[i].end_date_formated, //real_end_date correspond date fin dans base de donnée
            client_name: data[i].client_name,
            color: "red"
        });
    }

    gantt.parse({ data: arrData });

    if (startDatePeriode != null && endDatePeriode != null) {
        addTextPeriode(dateToShortFormat(newDate(startDatePeriode)), dateToShortFormat(newDate(endDatePeriode)));
    } else {
        addTextPeriode(dateToShortFormat(gantt.getSubtaskDates().start_date), dateToShortFormat(gantt.getSubtaskDates().end_date))
    }

}

// datedebutplanning = document.getElementById('datedebutplanning');

datedebutplanning.onchange = function () {
    dateValue = this.value;
    ganttInit(dateValue, startDatePlus7Days(dateValue));
    retrieveDataAjax(dateValue, startDatePlus7Days(dateValue));

}

function startDatePlus7Days(startDate) {

    var startDateTimestamp = dateToTimestamp(startDate);
    var endDateTimestamp = startDateTimestamp + daysToTimestamp(6);
    return newDate(endDateTimestamp);

}
function startDatePlus14Days(startDate) {

    var startDateTimestamp = dateToTimestamp(startDate);
    var endDateTimestamp = startDateTimestamp + daysToTimestamp(13);
    return newDate(endDateTimestamp);

}
function startDatePlus1Mouth(startDate) {

    var startDateTimestamp = dateToTimestamp(startDate);
    var endDateTimestamp = startDateTimestamp + daysToTimestamp(29);
    return newDate(endDateTimestamp);

}
function startDatePlus2Mouths(startDate) {

    var startDateTimestamp = dateToTimestamp(startDate);
    var endDateTimestamp = startDateTimestamp + daysToTimestamp(59);
    return newDate(endDateTimestamp);

}

function daysToTimestamp(numberOfDays) {
    return 24 * 60 * 60 * 1000 * numberOfDays;
}

function dateToTimestamp(date) {

    return new Date(date).getTime();

}
function newDate(date) {
    return new Date(date);
}


function getElements() {
    datedebutplanning = document.getElementById('datedebutplanning');
    btn7jours = document.getElementById('7jours');
    btn14jours = document.getElementById('14jours');
    btn1mois = document.getElementById('1mois');
    btn2mois = document.getElementById('2mois');
    spanElemStartDate = document.querySelector('#spandStartDate');
    spanElemEndDate = document.querySelector('#spanEndDate');
}

function addEventListner() {

    btn7jours.addEventListener('click', changeScale7jours, false);
    btn14jours.addEventListener("click", changeScale14jours, false);
    btn1mois.addEventListener("click", changeScale1mois, false);
    btn2mois.addEventListener("click", changeScale2mois, false);
}

function changeScale7jours() {

    if (datedebutplanning.value == 0) {
        var startDate = newDate(Date.now());
        ganttInit(startDate, startDatePlus7Days(startDate));
        retrieveDataAjax(startDate, startDatePlus7Days(startDate));


    } else {
        ganttInit(datedebutplanning.value, startDatePlus7Days(datedebutplanning.value));
        retrieveDataAjax(datedebutplanning.value, startDatePlus7Days(datedebutplanning.value));

    }

}
function changeScale14jours() {
    if (datedebutplanning.value == 0) {
        var startDate = newDate(Date.now());
        ganttInit(startDate, startDatePlus14Days(startDate));
        retrieveDataAjax(startDate, startDatePlus14Days(startDate));


    } else {

        ganttInit(datedebutplanning.value, startDatePlus14Days(datedebutplanning.value));
        retrieveDataAjax(datedebutplanning.value, startDatePlus14Days(datedebutplanning.value));

    }
}
function changeScale1mois() {
    if (datedebutplanning.value == 0) {
        var startDate = newDate(Date.now());
        ganttInit(startDate, startDatePlus1Mouth(startDate));
        retrieveDataAjax(startDate, startDatePlus1Mouth(startDate));

    } else {
        ganttInit(datedebutplanning.value, startDatePlus1Mouth(datedebutplanning.value));
        retrieveDataAjax(datedebutplanning.value, startDatePlus1Mouth(datedebutplanning.value));


    }
}
function changeScale2mois() {
    if (datedebutplanning.value == 0) {
        var startDate = newDate(Date.now());
        ganttInit(startDate, startDatePlus2Mouths(startDate));
        retrieveDataAjax(startDate, startDatePlus2Mouths(startDate));

    } else {

        ganttInit(datedebutplanning.value, startDatePlus2Mouths(datedebutplanning.value));
        retrieveDataAjax(datedebutplanning.value, startDatePlus2Mouths(datedebutplanning.value));

    }
}

function addTextPeriode(startDate, endDate) {
    spanElemStartDate.innerText = "< " + startDate + " au ";
    spanElemEndDate.innerText = endDate + " > ";
}

function dateToShortFormat(date) {
    return date.toLocaleDateString('fr-FR');
}

// });
