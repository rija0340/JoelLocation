var listeConducteurs = [];
var idClient;

getElements();
getListConducteurs();
autocomplete(listeConducteurs);


function getElements() {
    idReservation = document.getElementById('idReservation').value;
    console.log(idReservation);
}

//enabled by library jquery.ui
function autocomplete(listeConducteurs) {
    $(function () {
        $("#selectConducteur").autocomplete({ source: listeConducteurs });
    });
}

//liste clients pour autocompletion client input
function getListConducteurs() {
    $.ajax({
        type: 'GET',
        url: '/backoffice/reservation/liste-conducteurs',
        data: { 'idReservation': idReservation },
        success: function (data) {

            console.log(data);
            for (let i = 0; i < data.length; i++) {
                listeConducteurs.push(data[i].nom + ' ' + data[i].prenom + ' (' + data[i].numPermis + ')');
            }
        },
        error: function () {
            alert('La requête n\'a pas abouti');
        }
    });

}
