var btnGenerer;
var id;
var input;

getElements();
addEventListener();

function getElements() {
    btnGenerer = document.getElementById('genererFacturePDF');
    id = document.getElementById('idReservation').value;
    console.log(id);
}

function addEventListener() {
    btnGenerer.addEventListener('click', genererFacture, false);
}

function genererFacture() {

    $.ajax({
        type: 'GET',
        url: '/generer/facture-pdf/',
        data: {
            'id': id
        },
        Type: "json",
        success: function (data) {

            facture(
                data.dateDepartValue,
                data.dateRetourValue,
                data.nomClientValue,
                data.prenomClientValue,
                data.adresseClientValue,
                data.vehiculeValue,
                data.dureeValue,
                data.agenceDepartValue,
                data.agenceRetourValue,
                data.numeroDevisValue,
                data.tarifValue
            )
        },
        error: function (erreur) {
            // alert('La requête n\'a pas abouti' + erreur);
        }
    });
}