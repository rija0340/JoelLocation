$(document).ready(function () {

    $('#validerPaiement').click(function (e) {
        if ($('#conditionGeneralVente').is(':checked')) {
            // alert('checked');
        } else {
            $.alert({
                title: 'Erreur',
                icon: 'fa fa-warning',
                type: 'red',
                content: '<p>"Veuillez accepter les conditions générales de location"</p>',
            });
            e.preventDefault();
        }
    });


})  