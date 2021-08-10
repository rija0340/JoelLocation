
//elements 
var agenceDepartElem;
var agenceRetourElem;
var dateDepartElem;
var dateRetourElem;
var vehiculeElem;
var nomClientElem;
var emailClientElem;
var prenomClientElem;
var numeroClientElem;
var lieuSejourElem;
var dureeElem;
var tarifElem;
var numeroDevisElem;
var adresseClientElem;

var alertVehiculeElem;
var idVehiculeElem;

//btn
var btnGenererPDf;
var garantieAppelation;
var siegeAppelation;
var btnReserver;
var btnGenererFacturePDF;

var garantiePrix;
var siegePrix;

//values elem
var agenceDepartValue;
var agenceRetourValue;
var dateDepartValue;
var dateRetourValue;
var vehiculeValue;
var nomClientValue;
var emailClientValue;
var prenomClientValue;
var numeroClientValue;
var lieuSejourValue;
var dureeValue;
var tarifValue;
var idVehiculeValue;
var numeroDevisValue;
var adresseClientValue;

getElements();
getValues();
addEventListener();
getListeVehicules();

function getElements() {

    agenceRetourElem = document.querySelector(' .js-agenceRetour');
    agenceDepartElem = document.querySelector(' .js-agenceDepart');
    dateDepartElem = document.querySelector(' .js-dateDepart');
    dateRetourElem = document.querySelector(' .js-dateRetour');
    vehiculeElem = document.querySelector(' .js-vehicule');
    nomClientElem = document.querySelector(' .js-nom_client');
    prenomClientElem = document.querySelector(' .js-prenom_client');
    emailClientElem = document.querySelector(' .js-email_client');
    numeroClientElem = document.querySelector('.js-tel_client');
    dureeElem = document.querySelector(' .js-duree');
    tarifElem = document.querySelector('.js-prix ');
    alertVehiculeElem = document.getElementById('js-alertVehicule');
    idVehiculeElem = document.querySelector('.js-idVehicule');
    numeroDevisElem = document.querySelector('.js-numeroDevis');
    adresseClientElem = document.querySelector('.js-adresse_client');

    //boutons
    btnGenererPDf = document.getElementById('btnGenererPDf');
    btnReserver = document.getElementById('btnReserver');
    btnGenererFacturePDF = document.getElementById('btnGenererFacturePDF');
}

function getValues() {
    agenceDepartValue = agenceDepartElem.innerText
    agenceRetourValue = agenceRetourElem.innerText
    dateDepartValue = dateDepartElem.innerText
    dateRetourValue = dateRetourElem.innerText;
    vehiculeValue = vehiculeElem.innerText;
    nomClientValue = nomClientElem.innerText;
    emailClientValue = emailClientElem.innerText;
    prenomClientValue = prenomClientElem.innerText;
    numeroClientValue = numeroClientElem.innerText;
    dureeValue = dureeElem.innerText;
    tarifValue = tarifElem.innerText;
    idVehiculeValue = idVehiculeElem.innerText;
    numeroDevisValue = numeroDevisElem.innerText;
    adresseClientValue = adresseClientElem.innerText;
}

function addEventListener() {
    btnGenererPDf.addEventListener('click', genererDevisPDF, false);
    btnGenererFacturePDF.addEventListener('click', genererFacturePDF, false);
}


function getListeVehicules() {
    // var d = new Date(dateInputValue);
    // var n = d.toString();
    $.ajax({
        type: 'GET',
        url: '/reservation/listeVehicules',
        data: {
            "dateDepart": dateDepartValue,
            "dateRetour": dateRetourValue
        },
        Type: "json",
        success: function (data) {
            console.log(data);
            console.log("idVehiculeValue");
            console.log(idVehiculeValue);
            //verifier si le véhicule choisi lors création devis est disponible ou pas , si non afficher PAS DISPO
            for (let i = 0; i < data.length; i++) {

                if (data[i].id == parseInt(idVehiculeValue)) {

                    //affichage vehicule non dispo si déja pris.
                    if (alertVehiculeElem.classList.contains('hide'))
                        alertVehiculeElem.classList.replace('hide', 'noHide');
                    else
                        alertVehiculeElem.classList.replace('noHide', 'hide');

                    //afficher ou cacher bouton "reserver" en fonction disponibilité véhicule choisi
                    if (btnReserver.classList.contains('hide'))
                        btnReserver.classList.replace('hide', 'noHide');
                    else
                        btnReserver.classList.replace('noHide', 'hide');
                }
            }

        },
        error: function (erreur) {
            // alert('La requête n\'a pas abouti' + erreur);
            console.log(erreur.responseText);
        }
    });
}


function genererFacturePDF() {

    facture();

}

function genererDevisPDF() {

    devis();

}
