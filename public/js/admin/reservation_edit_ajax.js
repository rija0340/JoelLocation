var dateDebutElem;
var dateDebutValue;
var dateFinElem;
var dateFinValue;
var btnRechercherElem;
var reservation2Elem;
var imVehElem;
var imVehValue;
var dateDepart;
var dateRetour;

getElements();
addEventListener();

window.onload = function test() {
    dateDebutValue = document.getElementById("reservation_date_debut").value;
    dateFinValue = document.getElementById("reservation_date_fin").value;
    imVehValue = document.getElementById("reservation_vehicule").value; //envoyé depuis controller et puis reçu dans edit html (champ caché)
    retrieveDataAjax();
};

function getElements() {

    dateDebutElem = document.getElementById("reservation_date_debut");
    dateFinElem = document.getElementById("reservation_date_fin");
    // btnRechercherElem = document.getElementById("rechercherVehicules");
    reservation2Elem = document.getElementById("reservation2");
    btnModifier = document.querySelector("button");
}

function addEventListener() {

    dateDebutElem.addEventListener('change', getDateDebutValue, false);
    dateFinElem.addEventListener('change', getDateFinValue, false);
    btnModifier.addEventListener('click', setNullHiddenVehicule, false);

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

    if (dateFinValue != null) {


        if (dateToTimestamp(dateDebutValue) > dateToTimestamp(dateFinValue)) {
            $("#selectVehicule").empty();
            alert("La date de fin doit être supérieure à la date de début");
            dateDebutElem.value = null;
            dateFinElem.value = null;
            dateDebutValue = null;
            dateFinValue = null;
        } else {

            retrieveDataAjax();
        }

    }
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
            dateDebutValue = null;
            dateFinValue = null;

        }

    } else {
        dateFinElem.value = null;
        dateFinValue = null;

        alert("Veuillez entrer en premier la date de début");
    }
}

function retrieveDataAjax() {
    dateDepart = dateDebutValue;
    dateRetour = dateFinValue;
    $.ajax({
        type: 'GET',
        url: '/reservation/vehiculeDispoFonctionDates',
        data: {
            'dateDepart': dateDepart, 'dateRetour': dateRetour
        },
        Type: "json",
        success: function (data) {
            console.log(data);

            populateSelectElem(data);
            dataForSelect(data)

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
        if (imVehValue == option.text) {
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
function setNullHiddenVehicule() {
    document.getElementById("reservation_vehicule").value = null;
}