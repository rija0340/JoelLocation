var vehiculeMarqueElem;
var marqueID;
var btnModifier;


getElements();
addEventListener();


window.onload = function test() {
    marqueID = document.getElementById("vehicule_edit_marque").value;
    retrieveModeleAjax();

};


function getElements() {
    vehiculeMarqueElem = document.getElementById('vehicule_edit_marque');
    btnModifier = document.getElementById('enregistrer');
}

function addEventListener() {
    vehiculeMarqueElem.addEventListener('change', getValues, false);
    btnModifier = document.addEventListener('click', setNullHiddenVehicule, false);
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
            populateSelectEdit(data, "selectModele", "vehicule_edit_modele");

        },
        error: function (erreur) {
            // alert('La requête n\'a pas abouti' + erreur);
            console.log(erreur.responseText);
        }
    });
}

function setNullHiddenVehicule() {
    document.getElementById("vehicule_edit_modele").value = null;
}

