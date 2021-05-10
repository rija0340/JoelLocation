var dateDebutElem;
var dateDebutValue;
var dateFinElem;
var dateFinValue;
var dateDebutToPhp;
var dateFintToPhp;
var dateFintToPhp;
var btnRechercherElem;

getElements();
addEventListener()

function getElements() {
    dateDebutElem = document.getElementById("dateDebut");
    dateFinElem = document.getElementById("dateFin");
    btnRechercherElem = document.getElementById("rechercherVehicules");
}

function addEventListener() {
    dateDebutElem.addEventListener('change', getDateDebutValue, false);
    dateFinElem.addEventListener('change', getDateFinValue, false);
    btnRechercherElem.addEventListener('click', getDatesValues, false);
}

function getDatesValues() {
    console.log(document.getElementById("dateDebut").value);
    console.log(document.getElementById("dateFin").value);
    retrieveDataAjax();
}

function getDateDebutValue() {
    dateDebutValue = this.value;
}

function getDateFinValue() {
    dateFinValue = this.value;

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
        },
        error: function (erreur) {
            // alert('La requête n\'a pas abouti' + erreur);
            console.log(erreur.responseText);
        }
    });
}