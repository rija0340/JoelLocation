var btnsDevisPDF;
var devisID;
var btnValiderDevis;


getElements();
addEventListener();

function getElements() {
    btnsDevisPDF = document.querySelectorAll("a[id='telechargerDevis']");
    btnValiderDevis = document.querySelectorAll("a[id='validerDevis']");

    console.log(btnsDevisPDF);
}

function addEventListener() {
    for (let i = 0; i < btnsDevisPDF.length; i++) {
        btnsDevisPDF[i].addEventListener('click', generatePDF, false);
    }

    for (let i = 0; i < btnValiderDevis.length; i++) {
        btnValiderDevis[i].addEventListener('click', redirectToStep3, false);
    }

}

function generatePDF() {

    //get the ID of the devis
    devisID = parseInt(this.parentElement.parentElement.parentElement.parentElement.firstElementChild.innerText);

    console.log(devisID);

    $.ajax({
        type: 'GET',
        url: '/espaceclient/devisPDF/' + devisID,
        Type: "json",
        success: function (data) {

            dateDepartValue = data['dateDepart'];
            dateRetourValue = data['dateRetour'];
            nomClientValue = data['nomClient'];
            prenomClientValue = data['prenomClient'];
            adresseClientValue = data['adresseClient'];
            vehiculeValue = data['vehicule'];
            dureeValue = data['duree'];
            agenceDepartValue = data['agenceDepart'];
            agenceRetourValue = data['agenceRetour'];
            tarifValue = data['tarif'];
            numeroDevisValue = data['numeroDevis'];

            // /** this function is defined in another file devisJsPDF.js */
            devis();

        },
        error: function (erreur) {
            // alert('La requête n\'a pas abouti' + erreur);
            console.log(erreur.responseText);
        }
    });

}

function redirectToStep3(e) {
    devisID = parseInt(this.parentElement.parentElement.firstElementChild.innerText);
    console.log("ity ilay devisID :" + devisID);
    e.preventDefault();
    $.ajax({
        type: 'GET',
        url: '/espaceclient/optionsGaranties',
        data: {
            'devisID': devisID
        },
        Type: "json",
        beforeSend: function (xhr) {

            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest')


        },
        success: function (data) {

            // window.location = "/espaceclient/optionsGaranties";
        },
        error: function (erreur) {
            // alert('La requête n\'a pas abouti' + erreur);
            console.log(erreur.responseText);
        }
    });

}

