$(function () { // voir configuration daterangepicker

    // Fonction utilitaire pour formater les montants
    function formatMontant(montant) {
        return parseFloat(montant).toLocaleString('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    var start = moment().subtract(60, 'days');
    var end = moment();

    function cb(start, end) {
        $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));

        // Mettre à jour aussi l'affichage des dates dans le titre
        var dateRange = start.format('D MMMM YYYY') + ' AU ' + end.format('D MMMM YYYY');
        $('#datesRange').text('DU ' + dateRange);
    }

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

    //on load du fenetre, initialisation
    // Charger les données immédiatement après l'initialisation du daterangepicker
    calculChiffreAffaire(start.format('DD-MM-YYYY'), end.format('DD-MM-YYYY'));


    // fonction après evenement click bouton apply
    $('#reportrange').on('apply.daterangepicker', function (_, picker) {
        var dateDebut = picker.startDate.format('DD-MM-YYYY');
        var dateFin = picker.endDate.format('DD-MM-YYYY');

        calculChiffreAffaire(dateDebut, dateFin);
    });

    function calculChiffreAffaire(dateDebut, dateFin) {
        // Afficher un indicateur de chargement
        var tbody = document.getElementById('tbody');
        tbody.innerHTML = '<tr><td colspan="7" class="text-center"><i class="fa fa-spinner fa-spin"></i> Chargement des données...</td></tr>';

        $.ajax({
            type: 'GET',
            url: '/backoffice/chiffre-affaire-par-vehicule/',
            data: {
                'dateDebut': dateDebut,
                'dateFin': dateFin
            },
            Type: "json",
            success: function (data) {
                // Les dates sont déjà mises à jour par la fonction cb(), pas besoin de les redéfinir ici

                var tbody = document.getElementById('tbody');
                var table = document.getElementById('table');
                //enlever les enfants de tbody s'il y en a
                if (tbody.hasChildNodes) {
                    table.removeChild(table.lastElementChild);
                    tbody = document.createElement('tbody');
                    tbody.id = 'tbody';
                    table.appendChild(tbody);
                }
                if (data.length > 0) {
                    for (let i = 0; i < data.length; i++) {
                        var tr = document.createElement('tr');
                        var line = `
                            <td class="font-weight-bold">${data[i].vehicule}</td>
                            <td class="text-right">${formatMontant(data[i].ca)} €</td>
                            <td class="text-right">${formatMontant(data[i].web)} €</td>
                            <td class="text-right">${formatMontant(data[i].cpt)} €</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                        `
                        tr.innerHTML = line;
                        tbody.appendChild(tr);
                    }
                } else {
                    var tr = document.createElement('tr');
                    var line = `<td colspan="7" class="text-center text-muted"><i class="fa fa-info-circle"></i> Aucune donnée à afficher pour cette période</td>`
                    tr.innerHTML = line;
                    tbody.appendChild(tr);
                }
            },
            error: function (erreur) {
                console.error('Erreur lors du chargement des données:', erreur);
                var tbody = document.getElementById('tbody');
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> Erreur lors du chargement des données</td></tr>';
            }
        });

    }
});