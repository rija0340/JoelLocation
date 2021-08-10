var vehiculeMarqueElem;
var marqueID;


getElements();
addEventListener();


function getElements() {
    vehiculeMarqueElem = document.getElementById('vehicule_marque');
}

function addEventListener() {
    vehiculeMarqueElem.addEventListener('change', getValues, false);
}

function getValues() {
    marqueID = vehiculeMarqueElem.value;
    console.log(marqueID);
    retrieveModeleAjax();
}

function retrieveModeleAjax() {
    $.ajax({
        type: 'GET',
        url: '/modele/liste',
        data: {
            'marqueID': marqueID
        },
        Type: "json",
        success: function (data) {
            console.log(data);
            populateSelectNew(data, "selectModele", "libelle");

        },
        error: function (erreur) {
            // alert('La requête n\'a pas abouti' + erreur);
            console.log(erreur.responseText);
        }
    });
}

