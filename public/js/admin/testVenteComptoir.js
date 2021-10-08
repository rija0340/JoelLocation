var listeClients = [];
getListClients();
autocomplete(listeClients);

//enabled by library jquery.ui
function autocomplete(listeClients) {
    $(function () {
        $("#selectClient").autocomplete({ source: listeClients });
    });
}

function getListClients() {
    $.ajax({
        type: 'GET',
        url: '/backoffice/listeclient',
        success: function (data) {

            console.log(data);
            for (let i = 0; i < data.length; i++) {
                listeClients.push(data[i].prenom + ' ' + data[i].nom + ' (' + data[i].email + ')');
            }
        },
        error: function () {
            alert('La requête n\'a pas abouti');
        }
    });

}