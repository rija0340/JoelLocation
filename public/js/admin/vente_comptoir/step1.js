var dateDebutElem;
var dateDebutValue;
var dateFinElem;
var dateFinValue;
var btnRechercherElem;
var reservation2Elem;
var dateDepart;
var dateRetour;
var listeVehiculesDispo;

getElements();
addEventListener()

function getElements() {
    dateDebutElem = document.getElementById("reservation_step1_dateDepart");
    dateFinElem = document.getElementById("reservation_step1_dateRetour");
}

function addEventListener() {
    dateDebutElem.addEventListener('change', getDateDebutValue, false);
    dateFinElem.addEventListener('change', getDateFinValue, false);
}

// function getDatesValues() {
//     console.log(document.getElementById("reservation_date_debut").value);
//     console.log(document.getElementById("reservation_date_fin").value);
// }

//date de départ inférieur à date now
function getDateDebutValue() {
    dateDebutValue = this.value;
    if (dateToTimestamp(dateDebutValue) < dateToTimestamp(new Date(Date.now()))) {
        $.alert({
            title: 'Erreur',
            icon: 'fa fa-warning',
            type: 'red',
            content: '<p>La date de départ ne peut pas être avant maintenant</p>',
        });
        dateDebutElem.value = null;
        dateFinElem.value = null;
        dateDebutValue = null;
        dateFinValue = null;
    }

    if (dateFinValue != null) {

        if (dateToTimestamp(dateDebutValue) > dateToTimestamp(dateFinValue)) {
            $("#selectVehicule").empty();
            // alert("La date de fin doit être supérieure à la date de début");
            $.alert({
                title: 'Erreur',
                icon: 'fa fa-warning',
                type: 'red',
                content: '<p>La date de retour doit être supérieure à la date de départ</p>',
            });
            dateDebutElem.value = null;
            dateFinElem.value = null;
            dateDebutValue = null;
            dateFinValue = null;
        } else {

        }

    }
}

function getDateFinValue() {

    dateFinValue = this.value;
    if ((dateFinValue = this.value) != null && dateDebutValue != null) {

        if (dateToTimestamp(dateFinValue) > dateToTimestamp(dateDebutValue)) {


        } else {

            $("#selectVehicule").empty();
            $.alert({
                title: 'Erreur',
                icon: 'fa fa-warning',
                type: 'red',
                content: '<p>La date de retour doit être supérieure à la date de départ</p>',
            });
            dateDebutElem.value = null;
            dateFinElem.value = null;
            dateDebutValue = null;
            dateFinValue = null;

        }

    } else {
        dateFinElem.value = null;
        dateFinValue = null;

        $.alert({
            title: 'Erreur',
            icon: 'fa fa-warning',
            type: 'red',
            content: '<p>Veuillez entrer en premier la date de départ</p>',
        });
    }
}



function dateToTimestamp(date) {

    return new Date(date).getTime();

}
