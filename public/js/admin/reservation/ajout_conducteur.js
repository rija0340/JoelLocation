var listeConducteurs = [];
var idClient;

getElements();
getListConducteurs();


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
        url: '/reservation/liste-conducteurs',
        data: { 'idReservation': idReservation },
        success: function (data) {

            console.log(data);
            for (let i = 0; i < data.length; i++) {
                listeConducteurs.push(data[i].prenom + ' ' + data[i].nom);
            }
        },
        error: function () {
            alert('La requête n\'a pas abouti');
        }
    });

}
