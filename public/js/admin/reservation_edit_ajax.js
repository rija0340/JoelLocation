var dateDebutElem;
var dateDebutValue;
var dateFinElem;
var dateFinValue;
var dateDebutToPhp;
var dateFintToPhp;
var dateFintToPhp;
var btnRechercherElem;
var reservation2Elem;
var imVehElem;
var imVehValue;


getElements();
addEventListener();



window.onload = function test() {
    dateDebutValue = document.getElementById("reservation_date_debut").value;
    dateFinValue = document.getElementById("reservation_date_fin").value;
    imVehValue = document.getElementById("imVeh").innerHTML;

    retrieveDataAjax();
};

function getElements() {

    dateDebutElem = document.getElementById("reservation_date_debut");
    dateFinElem = document.getElementById("reservation_date_fin");
    // btnRechercherElem = document.getElementById("rechercherVehicules");
    reservation2Elem = document.getElementById("reservation2");
}

function addEventListener() {

    dateDebutElem.addEventListener('change', getDateDebutValue, false);
    dateFinElem.addEventListener('change', getDateFinValue, false);
    // btnRechercherElem.addEventListener('click', getDatesValues, false);
}


function getDatesValues() {
    console.log(document.getElementById("reservation_date_debut").value);
    console.log(document.getElementById("reservation_date_fin").value);
    retrieveDataAjax();
}

function getDateDebutValue() {
    dateDebutValue = this.value;
    console.log('ity ilay date ' + dateDebutValue);
}

function getDateFinValue() {

    dateFinValue = this.value;
    if ((dateFinValue = this.value) != null && dateDebutValue != null) {

        if (dateToTimestamp(dateFinValue) > dateToTimestamp(dateDebutValue)) {

            retrieveDataAjax();

        } else {

            $("#selectVehicule").empty();
            alert("La date de fin doit être supérieure à la date de début");
            dateDebutElem.value = null;
            dateFinElem.value = null;

        }

    } else {
        dateFinElem.value = null;
        alert("Veuillez entrer en premier la date de début");
    }
}

function formatDateForAjax(date) {
    var date = new Date(date);
    day = date.getDate();
    month = date.getMonth();
    fullyear = date.getFullYear();
    date = (month + 1) + "/" + day + "/" + fullyear;
    console.log(date);
    return date;
}


function retrieveDataAjax() {
    // var d = new Date(dateInputValue);
    // var n = d.toString();
    $.ajax({
        type: 'GET',
        url: '/reservation/vehiculeDispoFonctionDates',
        data: { "dateDebut": formatDateForAjax(dateDebutValue), "dateFin": formatDateForAjax(dateFinValue) },
        Type: "json",
        success: function (data) {
            console.log(data);
            if ($("#reservation2").hasClass('hide') && $("#vehiculeDispo").hasClass('hide')) {
                $("#reservation2").removeClass('hide');
                $("#vehiculeDispo").removeClass('hide');
                populateSelectElem(data);
                dataForSelect(data)
            } else {
                populateSelectElem(data);
                dataForSelect(data)
            }

        },
        error: function (erreur) {
            // alert('La requête n\'a pas abouti' + erreur);
            console.log(erreur.responseText);
        }
    });
}



function populateSelectElem(options) {

    var select = document.getElementById('selectVehicule');
    $("#selectVehicule").empty(); //remove old options jquery

    for (var i = 0; i < options.length; i++) {

        var opt = options[i];
        console.log('ity ny opt ' + opt.marque);
        var option = document.createElement("option");
        option.text = opt.marque + ' ' + opt.modele + ' ' + opt.immatriculation;
        option.value = opt.id;
        if (imVehValue == opt.immatriculation) {
            option.setAttribute('selected', 'selected');
        }
        select.add(option);

    }
}

function dataForSelect(data) {
    var data2 = [];

    for (let i = 0; i < data.length; i++) {

        data2.push({
            id: data[i].id,
            marque: data[i].marque,
            modele: data[i].modele,
            immatriculation: data[i].immatriculation
        });

    }
    console.log(data2);
    return data2;
}

function dateToTimestamp(date) {

    return new Date(date).getTime();

}