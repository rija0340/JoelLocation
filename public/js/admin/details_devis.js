
//elements 
var agenceDepartElem;
var agenceRetourElem;
var dateDepartElem;
var dateRetourElem;
var vehiculeElem;
var nomclientElem;
var emailClientElem;
var prenomClientElem;
var numeroClientElem;
var lieuSejourElem;
var dureeElem;
var tarifElem;

var alertVehiculeElem;
var idVehiculeElem;

//btn
var btnGenererPDf;
var garantieAppelation;
var siegeAppelation;

var garantiePrix;
var siegePrix;

//values elem
var agenceDepartValue;
var agenceRetourValue;
var dateDepartValue;
var dateRetourValue;
var vehiculeValue;
var nomclientValue;
var emailClientValue;
var prenomClientValue;
var numeroClientValue;
var lieuSejourValue;
var dureeValue;
var tarifValue;
var idVehiculeValue;

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
    nomclientElem = document.querySelector(' .js-nom_client');
    prenomClientElem = document.querySelector(' .js-prenom_client');
    emailClientElem = document.querySelector(' .js-email_client');
    numeroClientElem = document.querySelector('.js-tel_client');
    dureeElem = document.querySelector(' .js-duree');
    tarifElem = document.querySelector('.js-prix ');
    alertVehiculeElem = document.getElementById('js-alertVehicule');
    idVehiculeElem = document.querySelector('.js-idVehicule');

    //boutons
    btnGenererPDf = document.getElementById('btnGenererPDf');
}

function getValues() {
    agenceDepartValue = agenceDepartElem.innerText
    agenceRetourValue = agenceRetourElem.innerText
    dateDepartValue = dateDepartElem.innerText
    dateRetourValue = dateRetourElem.innerText;
    vehiculeValue = vehiculeElem.innerText;
    nomclientValue = nomclientElem.innerText;
    emailClientValue = emailClientElem.innerText;
    prenomClientValue = prenomClientElem.innerText;
    numeroClientValue = numeroClientElem.innerText;
    dureeValue = dureeElem.innerText;
    tarifValue = tarifElem.innerText;
    idVehiculeValue = idVehiculeElem.innerText;
}

function addEventListener() {
    btnGenererPDf.addEventListener('click', genererDevisPDF, false);
}

function genererDevisPDF() {

    alert('efa ato ah zao  ');

    var doc = new jsPDF();

    doc.text(20, 20, 'Client : ');
    doc.text(40, 20, nomclientValue + prenomClientValue);
    doc.text(20, 30, 'Agence de départ : ');
    doc.text(70, 30, agenceDepartValue);
    doc.text(20, 40, 'Agence de retour : ');
    doc.text(70, 40, agenceRetourValue);
    // doc.text(20, 50, 'lieu sejour value : ');
    // doc.text(70, 50, lieuSejourValue);
    doc.text(20, 60, 'Date depart : ');
    doc.text(70, 60, dateDepartValue);
    doc.text(20, 70, 'Date retour : ');
    doc.text(70, 70, dateRetourValue);
    doc.text(20, 80, 'Duree réservation : ');
    doc.text(70, 80, dureeValue);
    doc.text(20, 90, 'Tarif appliquée : ');
    doc.text(70, 90, tarifValue);
    // doc.text(20, 100, 'Marque : ');
    // doc.text(70, 100, detailsVehicule.marque);
    // doc.text(20, 110, 'Modèle : ');
    // doc.text(70, 110, detailsVehicule.modele);
    // doc.text(20, 120, 'Immatriculation :  ');
    // doc.text(70, 120, detailsVehicule.immatriculation);
    // doc.text(20, 130, 'Chauffeur :  ');
    // doc.text(70, 130, conducteur);
    // doc.text(20, 140, 'Option :  ');
    // doc.text(70, 140, siege.appelation);
    // doc.text(20, 150, 'Garantie :  ');
    // doc.text(70, 150, garantie.appelation + " " + garantie.prix.toString() + " €");
    // doc.text(20, 160, 'Total à payer :  ');
    // doc.text(70, 160, calculPrixTotal().toString() + " €");
    doc.save(nomclientValue + '.pdf');

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
            for (let i = 0; i < data.length; i++) {

                if (data[i].id == parseInt(idVehiculeValue)) {
                    if (alertVehiculeElem.classList.contains('hide'))
                        alertVehiculeElem.classList.replace('hide', 'noHide');
                    else
                        alertVehiculeElem.classList.replace('noHide', 'hide');
                }
            }

        },
        error: function (erreur) {
            // alert('La requête n\'a pas abouti' + erreur);
            console.log(erreur.responseText);
        }
    });
}