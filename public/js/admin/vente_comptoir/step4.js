var listeClients = [];
var btnSaveDevisAsPdf;
var btnReserverDevis;
var montantPaiement;

getElements();
getListClients();
autocomplete(listeClients);

function getElements() {
    btnSaveDevisAsPdf = document.getElementById('btnSaveDevisAsPdf');
    selectClientElement = document.getElementById('selectClient'); //input
    btnReserverDevis = document.getElementById('btnReserverDevis'); //input
}

function addEventListener() {
    btnSaveDevisAsPdf.addEventListener('click', checkClientInput, false);
    btnReserverDevis.addEventListener('click', checkClientAndPaiement, false)
}

function checkClientInput() {

    //si input client est vide, un alert apparaît
    if (selectClientElement.value.length == 0) {
        $.alert({
            title: 'Erreur',
            icon: 'fa fa-warning',
            type: 'red',
            content: '<p>Veuillez indiquer le nom du client</p>',
        });
    } else {
        saveDevisAsPdf();
    }

}

//return les paramètres nécessaire pour l'impression du devis
function saveDevisAsPdf() {
    $.ajax({
        type: 'GET',
        url: '/backoffice/vente-comptoir/enregistrer-devis-pdf',
        data: { 'client': selectClientElement.value },
        success: function (data) {
            // function called from another file (devisJsPDF.js with is define above this step4.js file)
            devis(data);

        },
        error: function () {
            alert('La requête n\'a pas abouti');
        }
    });

}

//enabled by library jquery.ui
function autocomplete(listeClients) {
    $(function () {
        $("#selectClient").autocomplete({ source: listeClients });
    });
}

//liste clients pour autocompletion client input
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


//reserver le devis directement
function checkClientAndPaiement() {
    //si input client est vide, un alert apparaît
    if (selectClientElement.value.length == 0) {
        $.alert({
            title: 'Erreur',
            icon: 'fa fa-warning',
            type: 'red',
            content: '<p>Veuillez indiquer le nom client</p>',
        });
    } else {
        $.confirm({
            title: 'Montant de paiement du client (€)',
            content: 'url:montant-paiement',
            buttons: {
                sayMyName: {
                    text: 'Valider',
                    btnClass: 'btn-danger',
                    action: function () {
                        var input = this.$content.find('input#input-paiement');
                        var errorText = this.$content.find('.text-danger');
                        if (!input.val().trim()) {
                            $.alert({
                                content: "Le champ pour le montant de paiement ne peut être vide",
                                type: 'red'
                            });
                            return false;
                        } else {

                            montantPaiement = input.val();
                            reserverDevis(selectClientElement.value, montantPaiement);

                            // $.alert('Vous avez entré ' + input.val() + '€ comme valeur de paiement');
                        }
                    }
                },
                later: function () {
                    // do nothing.
                }
            }
        });
    }
}

function reserverDevis(client, montant) {

    $.ajax({
        type: 'POST',
        url: '/backoffice/vente-comptoir/reserver-devis',
        data: { 'client': client, 'montant': montant },
        success: function (data) {
            $.alert({
                title: 'Succès',
                icon: 'fa fa-smile-o',
                type: 'red',
                content: '<p>La réservation N°' + data + 'a été effectuée avec succès</p>',
            });
            window.location.href = '/backoffice/reservation';

        },
        error: function () {
            alert('La requête n\'a pas abouti');
        }
    });

}

//utilisation de jquery-confirm pour alert, confirm modals


$('.increment').click(function () {
    var $input = $(this).parents('.input-number-group').find('.input-number');
    var val = parseInt($input.val(), 10);
    $input.val(val + 1);
});

$('.decrement').click(function () {
    var $input = $(this).parents('.input-number-group').find('.input-number');
    var val = parseInt($input.val(), 10);
    $input.val(val - 1);
})


