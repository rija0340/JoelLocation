var btnsDevisPDF;
var devisID;


getElements();
addEventListener();

function getElements() {
    btnsDevisPDF = document.querySelectorAll("a[id='telechargerDevis']");
    console.log(btnsDevisPDF);
}

function addEventListener() {
    for (let i = 0; i < btnsDevisPDF.length; i++) {
        btnsDevisPDF[i].addEventListener('click', generatePDF, false);
    }
}

function generatePDF() {

    //get the ID of the devis
    devisID = parseInt(this.parentElement.parentElement.firstElementChild.innerText);

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