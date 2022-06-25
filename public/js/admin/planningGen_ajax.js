// const { gantt } = require("../../Gantt/dhtmlxgantt");
$(document).ready(function () {

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
    let thedata;
    let completeData;

    //affichage peride intervalle


    //declaration boutons pour changement scale affichage
    var btn7jours;
    var btn14jours;
    var btn1mois;
    var btn2mois;
    getElements();
    addEventListner();


    function getData(data) {
        thedata = data;
        completeData = data;
    }


    window.onload = function () {
        $('body').loadingModal({
            text: 'Chargement...'
        });
        retrieveDataAjax();

    };

    function retrieveDataAjax() {
        var maxDate;
        var dateNow = new Date();
        $.ajax({
            type: 'GET',
            url: '/planningGeneralData',
            timeout: 3000,
            success: function (data) {
                var dataWithoutParent = [];
                for (var i = 0; i < data.length; i++) {
                    if (data[i].parent != 0) {
                        dataWithoutParent.push(data[i]);
                    }
                }
                //on ne peut pas acceder à end_date property de data[0]
                object_max_date = StringDateToObject(dataWithoutParent[0].end_date_formated);

                // maxDate = new Date(maxDate);
                for (var j = 1; j < dataWithoutParent.length; j++) {

                    object_date = StringDateToObject(dataWithoutParent[j].end_date_formated);
                    if (object_date.getTime() > object_max_date.getTime()) {
                        object_max_date = object_date;
                    }
                }
                object_max_date = object_max_date.setDate(object_max_date.getDate() + 5);
                //la date est converti en timestamp c'est pourquoi on doit faire new Date
                object_max_date = new Date(object_max_date);
                ganttInit(dateNow.toLocaleDateString("en"), object_max_date.toLocaleDateString("en"), 20);
                // addTextPeriode(dateNow.toLocaleDateString("en"), object_max_date.toLocaleDateString("en"));
                getData(data);
                createCheckboxes(getUniqueListVehicules(data));

                document.querySelector('div .selectAll').firstElementChild.click();
                // checkAllClickCallback();
                // ganttLoadData(thedata);
                $('body').loadingModal('destroy');

                //hauteur de la table 
                var i = 0;
                $('.gantt_tree_content').each(function () {
                    i = i + 1;
                });
                console.log(i);
                $('#gantt_here').css('max-height', i * 45 + 'px');

            },
            error: function () {
                alert('La requête n\'a pas abouti');
            }
        });
    }

    function ganttInit(startDateScale, endDateScale, cellWidth) {
        gantt.config.readonly = true;
        gantt.config.columns = [{
            name: "text",
            label: "RESSOURCES",
            tree: false,
            width: 175,
            resize: false,

        }];

        //hide task unscheduled
        gantt.config.show_unscheduled = false;

        //affichage scale (organisation date, mois, jours, année)
        gantt.config.scales = [{
            unit: "day",
            step: 1,
            format: "%d %m %Y"
        }];
        // test sur les bares de taches
        gantt.templates.task_text = function (start, end, task) {
            if (task.client != undefined) {
                return task.client + " " + task.start_date_formated + " - " + task.end_date_formated;
            } else {
                return " ";
            }
        };

        //date de début et fin de l'affichage tasks
        if (startDateScale != null && endDateScale != null) {
            gantt.config.start_date = new Date(startDateScale);
            gantt.config.end_date = new Date(endDateScale);
            gantt.config.show_tasks_outside_timescale = true;

        }
        //highlight weekend
        gantt.templates.scale_cell_class = function (date) {
            if (date.getDay() == 0 || date.getDay() == 6) {
                return "weekend";
            }
        };
        gantt.templates.timeline_cell_class = function (item, date) {
            if (date.getDay() == 0 || date.getDay() == 6) {
                return "weekend";
            }
        };

        // class en fonction type(ilaina)
        //valeur largeur colonne

        if (typeof cellWidth !== 'undefined') {
            gantt.config.min_column_width = cellWidth;
        } else {
            gantt.config.min_column_width = 40;
        }
        //cell for date
        gantt.config.scale_height = 50;

        // $('.gantt_scale_cell').css('text-size', "7px");


        gantt.i18n.setLocale("fr");

        gantt.clearAll();
        gantt.plugins({
            quick_info: true
        });

        gantt.init("gantt_here");

        //redirection vers des urls selon l'etat de la réservation
        gantt.attachEvent("onTaskDblClick", function (id, e) {
            var taskID = gantt.getTask(id).id_r;
            var etat = gantt.getTask(id).etat;
            if (etat == 'encours') {
                window.document.location = '/backoffice/reservation/details/' + taskID;
            }
            if (etat == 'termine') {
                window.document.location = '/backoffice/reservation/details/' + taskID;
            }
            if (etat == 'nouvelle') {
                window.document.location = '/backoffice/reservation/details/' + taskID;
            }
            if (etat == 'stopSale') {
                window.document.location = '';
            }

        });

    }

    function ganttLoadData(data, startDatePeriode, endDatePeriode) {

        var arrData = [];
        var len = data.length;

        for (var i = 0; i < len; i++) { //boucle sur l'objet "data" qui est un Json

            // //recuperer date de data.json ensuite convertir en date js
            // startDate = data[i].start_date.date;
            // startDateString = JSON.stringify(startDate);
            // newStartDate = new Date(startDateString);
            // startDateTimestamp = newStartDate.getTime(); //pour recuperer durée si c'est nécessaire (soustraction fin et debut) conversion timestamp nécessaire

            // endDate = data[i].end_date.date;
            // endDateString = JSON.stringify(endDate);
            // newEndDate = new Date(endDateString);
            // endDateTimestamp = newEndDate.getTime();

            // var result = endDateTimestamp - startDateTimestamp; // On fait la soustraction

            // var durationDays = result / (1000 * 60 * 60 * 24);

            // var onDayTimestamp = 24 * 60 * 60 * 1000;

            // var endDatePlusOneDay = new Date(endDateTimestamp + onDayTimestamp);

            // var hour = newEndDate.getHours();


            // if (hour > 0) { //on a remarqué que lorsque l'heure est different de 00:00, la durée manque une journée dans gantt

            //     endDatePlusOneDay = newEndDate;

            // }

            // arrData.push({
            //     id: data[i].id,
            //     text: data[i].text,
            //     start_date: newStartDate,
            //     start_time: newStartDate.toLocaleTimeString('fr-FR'),
            //     end_date: endDatePlusOneDay, //date fin dans bdd + un jour car l'affichage n'est pas correct (durée - 1jour) lorsque l'heure = 00:00
            //     end_time: newEndDate.toLocaleTimeString('fr-FR'),
            //     real_end_date: data[i].end_date_formated, //real_end_date correspond date fin dans base de donnée
            //     client_name: data[i].client_name,
            //     color: "red"
            // });
        }

        if (data.length != 0) {


            gantt.parse({ data: data });

            if (startDatePeriode != null && endDatePeriode != null) {
                addTextPeriode(dateToShortFormat(newDate(startDatePeriode)), dateToShortFormat(newDate(endDatePeriode)));
            } else {
                addTextPeriode(dateToShortFormat(gantt.getSubtaskDates().start_date), dateToShortFormat(gantt.getSubtaskDates().end_date));
            }
        }

    }

    // datedebutplanning = document.getElementById('datedebutplanning');

    datedebutplanning.onchange = function () {
        dateValue = this.value;
        ganttInit(dateValue, startDatePlus2Mouths(dateValue), 20);
        ganttLoadData(thedata, dateValue, startDatePlus2Mouths(dateValue));

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
            ganttInit(startDate, startDatePlus7Days(startDate), 20);

            if (document.querySelector('div .selectAll').firstElementChild.checked) {

                ganttLoadData(completeData, startDate, startDatePlus7Days(startDate));
            } else {

                ganttLoadData(thedata, startDate, startDatePlus7Days(startDate));
            }

        } else {
            ganttInit(datedebutplanning.value, startDatePlus7Days(datedebutplanning.value), 20);

            if (document.querySelector('div .selectAll').firstElementChild.checked) {

                ganttLoadData(completeData, datedebutplanning.value, startDatePlus7Days(datedebutplanning.value));

            } else {
                ganttLoadData(thedata, datedebutplanning.value, startDatePlus7Days(datedebutplanning.value));

            }

        }

    }

    function changeScale14jours() {
        if (datedebutplanning.value == 0) {
            var startDate = newDate(Date.now());
            ganttInit(startDate, startDatePlus14Days(startDate), 20);
            // ganttLoadData(thedata, startDate, startDatePlus14Days(startDate));
            if (document.querySelector('div .selectAll').firstElementChild.checked) {

                ganttLoadData(completeData, startDate, startDatePlus14Days(startDate));
            } else {

                ganttLoadData(thedata, startDate, startDatePlus14Days(startDate));
            }

        } else {

            ganttInit(datedebutplanning.value, startDatePlus14Days(datedebutplanning.value), 20);
            // ganttLoadData(thedata, datedebutplanning.value, startDatePlus14Days(datedebutplanning.value));
            if (document.querySelector('div .selectAll').firstElementChild.checked) {

                ganttLoadData(completeData, datedebutplanning.value, startDatePlus14Days(datedebutplanning.value));

            } else {
                ganttLoadData(thedata, datedebutplanning.value, startDatePlus14Days(datedebutplanning.value));

            }

        }
    }

    function changeScale1mois() {
        if (datedebutplanning.value == 0) {
            var startDate = newDate(Date.now());
            ganttInit(startDate, startDatePlus1Mouth(startDate), 10);
            // ganttLoadData(thedata, startDate, startDatePlus1Mouth(startDate));

            if (document.querySelector('div .selectAll').firstElementChild.checked) {

                ganttLoadData(completeData, startDate, startDatePlus1Mouth(startDate));
            } else {

                ganttLoadData(thedata, startDate, startDatePlus1Mouth(startDate));
            }

        } else {
            ganttInit(datedebutplanning.value, startDatePlus1Mouth(datedebutplanning.value), 10);
            // ganttLoadData(thedata, datedebutplanning.value, startDatePlus1Mouth(datedebutplanning.value));

            if (document.querySelector('div .selectAll').firstElementChild.checked) {

                ganttLoadData(completeData, datedebutplanning.value, startDatePlus1Mouth(datedebutplanning.value));
            } else {

                ganttLoadData(thedata, datedebutplanning.value, startDatePlus1Mouth(datedebutplanning.value));
            }

        }
    }

    function changeScale2mois() {
        if (datedebutplanning.value == 0) {
            var startDate = newDate(Date.now());
            ganttInit(startDate, startDatePlus2Mouths(startDate), 10);
            // ganttLoadData(thedata, startDate, startDatePlus2Mouths(startDate));
            if (document.querySelector('div .selectAll').firstElementChild.checked) {

                ganttLoadData(completeData, startDate, startDatePlus2Mouths(startDate));
            } else {

                ganttLoadData(thedata, startDate, startDatePlus2Mouths(startDate));
            }

        } else {

            ganttInit(datedebutplanning.value, startDatePlus2Mouths(datedebutplanning.value), 10);
            // ganttLoadData(thedata, datedebutplanning.value, startDatePlus2Mouths(datedebutplanning.value));
            if (document.querySelector('div .selectAll').firstElementChild.checked) {

                ganttLoadData(completeData, datedebutplanning.value, startDatePlus2Mouths(datedebutplanning.value));
            } else {

                ganttLoadData(thedata, datedebutplanning.value, startDatePlus2Mouths(datedebutplanning.value));
            }

        }
    }

    function addTextPeriode(startDate, endDate) {
        spanElemStartDate.innerText = "< " + " Du " + startDate + " au ";
        spanElemEndDate.innerText = endDate + " > ";
    }

    function dateToShortFormat(date) {
        return date.toLocaleDateString('fr-FR');
    }

    var a = 0;

    function getUniqueListVehicules(data) {
        let listVehicules = [];
        let filteredList = [];
        for (let i = 0; i < data.length; i++) {
            if (data[i].parent == 0) {
                listVehicules.push(data[i].marque_modele.toLowerCase());
            }
        }
        filteredList[0] = listVehicules[0]; //initilisation

        for (let i = 1; i < listVehicules.length; i++) {

            for (let j = 0; j < filteredList.length; j++) {
                if (listVehicules[i] == filteredList[j]) {
                    a++;
                }

            }
            if (a == 0) {
                filteredList.push(listVehicules[i])
            }
        }

        return filteredList;
    }

    function createCheckboxes(data) {

        //creation en fonction data (length)
        let checkboxesParent = document.getElementById("checkBoxesList");
        //creation elem div parent of input
        let divParent = document.createElement("label");
        divParent.classList.add('checkbox-label');
        divParent.classList.add('selectAll');
        divParent.innerText = "Tout cocher/décocher";


        //creation elem input
        let checkboxElem = document.createElement("input");
        checkboxElem.classList.add('form-check-input');
        checkboxElem.addEventListener("click", checkAllClickCallback, false);
        checkboxElem.type = "checkbox";


        //creation elem label
        let label = document.createElement("span");
        label.classList.add('checkmark');

        divParent.appendChild(checkboxElem);
        divParent.appendChild(label);

        checkboxesParent.appendChild(divParent);

        for (let i = 0; i < data.length; i++) {

            var marque = data[i].substring(0, data[i].indexOf(' '));
            var modele = data[i].substring(data[i].lastIndexOf(' ') + 1);

            var identifiant = marque + '_' + modele;

            //creation elem div parent of input
            let divParent = document.createElement("label");
            divParent.classList.add('checkbox-label');
            divParent.classList.add(identifiant);
            divParent.classList.add('vehicule');
            divParent.innerText = data[i].toUpperCase();


            //creation elem input
            let checkboxElem = document.createElement("input");
            checkboxElem.classList.add('form-check-input');
            checkboxElem.addEventListener("click", checkboxClickCallback, false);
            checkboxElem.type = "checkbox";
            checkboxElem.id = identifiant;

            //creation elem label
            let label = document.createElement("span");
            label.classList.add('checkmark');

            divParent.appendChild(checkboxElem);
            divParent.appendChild(label);

            checkboxesParent.appendChild(divParent);
        }
    }

    function checkAllClickCallback() {
        var checkboxes = document.querySelectorAll('div .vehicule');

        if (this.checked) {
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].firstElementChild.checked = true;
                checkboxes[i].firstElementChild.disabled = true;
            }

            if (datedebutplanning.value == 0) {
                ganttInit();
                ganttLoadData(completeData);
            } else {
                ganttInit(datedebutplanning.value, startDatePlus2Mouths(datedebutplanning.value));
                ganttLoadData(completeData, datedebutplanning.value, startDatePlus2Mouths(datedebutplanning.value));

            }


        } else {
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].firstElementChild.checked = false;
                checkboxes[i].firstElementChild.disabled = false;
            }
            ganttInit();
            ganttLoadData([]);

        }
    }

    function checkboxClickCallback() {

        sortData(completeData);

    }

    function sortData(data) {

        var list = document.querySelectorAll('.form-check-input');
        var checkedVehicules = [];
        var selectedVehicules = [];

        for (let j = 1; j < list.length; j++) {
            if (list[j].checked) {
                var element = list[j].id;
                checkedVehicules.push(element);
            }

        }

        for (let j = 0; j < checkedVehicules.length; j++) {

            for (let i = 0; i < data.length; i++) {

                if (data[i].marque_modele) { // filtre pour données sans clé "marque_modele"

                    var marque = data[i].marque_modele.substring(0, data[i].marque_modele.indexOf(' ')).toLowerCase();
                    var modele = data[i].marque_modele.substring(data[i].marque_modele.lastIndexOf(' ') + 1).toLowerCase();
                    marque_modele = marque + '_' + modele;

                    if (marque_modele == checkedVehicules[j]) {

                        var id = data[i].id;
                        selectedVehicules.push(data[i]);

                        for (let i = 0; i < data.length; i++) {
                            if (data[i].parent == id) {

                                selectedVehicules.push(data[i]);

                            }
                        }

                    }
                }

            }
        }
        thedata = selectedVehicules;

        if (datedebutplanning.value == 0) {
            ganttInit();
            ganttLoadData(thedata);
        } else {
            ganttInit(datedebutplanning.value, startDatePlus2Mouths(datedebutplanning.value));
            ganttLoadData(thedata, datedebutplanning.value, startDatePlus2Mouths(datedebutplanning.value));

        }
        // ganttInit();
        // ganttLoadData(thedata);
        selectedVehicules = null;
    }
    function StringDateToObject(date) {
        var objectDate;
        objectDate = date.split(" ")[0];
        objectDate = objectDate.split('-');
        objectDate = new Date(objectDate[2] + "-" + objectDate[1] + "-" + objectDate[0]);
        return objectDate;
    }


});