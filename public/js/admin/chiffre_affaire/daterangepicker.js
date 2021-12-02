$(function() { // voir configuration daterangepicker
    var start = moment().subtract(60, 'days');
    var end = moment();

    function cb(start, end) {
        $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
    }
    // fonction après evenement click bouton apply
    $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
        var dateDebut = picker.startDate.format('DD-MM-YYYY');
        var dateFin = picker.endDate.format('DD-MM-YYYY');

        calculChiffreAffaire(dateDebut, dateFin);
    });
    $('#reportrange').daterangepicker({
        // changement de langue d'affichage des lables

        "locale": {
            "customRangeLabel": "Personnalisé",
            "applyLabel": "Appliquer",
            "cancelLabel": "Annuler"
        },
        applyButtonClasses: 'btn-danger',
        cancelButtonClasses: 'btn-dark',
        startDate: start,
        endDate: end,
        showDropdowns: true,
        // esoria le oe 1 mois fona ny intervalle
        linkedCalendars: false,

        ranges: {
            "Aujourd'hui": [
                moment(), moment()
            ],
            'Hier': [
                moment().subtract(1, 'days'),
                moment().subtract(1, 'days')
            ],
            'Les 7 derniers jours': [
                moment().subtract(6, 'days'),
                moment()
            ],
            'Les 30 derniers jours': [
                moment().subtract(29, 'days'),
                moment()
            ],
            'Ce mois': [
                moment().startOf('month'), moment().endOf('month')
            ],
            'Le mois dernier': [
                moment().subtract(1, 'month').startOf('month'),
                moment().subtract(1, 'month').endOf('month')
            ]
        }
    }, cb);

    cb(start, end);


    function calculChiffreAffaire(dateDebut, dateFin) {

        $.ajax({
            type: 'GET',
            url: '/paiement/chiffre-affaire-paiement',
            data: {
                'dateDebut': dateDebut,
                'dateFin': dateFin
            },
            Type: "json",
            success: function(data) {
                console.log(data);
            },
            error: function(erreur) {
                // alert('La requête n\'a pas abouti' + erreur);
                console.log(erreur.responseText);
            }
        });

    }
});