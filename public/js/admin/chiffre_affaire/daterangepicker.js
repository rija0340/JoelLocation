$(function() { // voir configuration daterangepicker



    var start = moment().subtract(60, 'days');
    var end = moment();

    function cb(start, end) {
        $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
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

    window.onload = function() {

        calculChiffreAffaire(start._d.format('d-m-Y'), end._d.format('d-m-Y'));
    };


    // fonction après evenement click bouton apply
    $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
        var dateDebut = picker.startDate.format('DD-MM-YYYY');
        var dateFin = picker.endDate.format('DD-MM-YYYY');

        calculChiffreAffaire(dateDebut, dateFin);
    });

    function calculChiffreAffaire(dateDebut, dateFin) {

        $.ajax({
            type: 'GET',
            url: '/backoffice/chiffre-affaire-par-vehicule/',
            data: {
                'dateDebut': dateDebut,
                'dateFin': dateFin
            },
            Type: "json",
            success: function(data) {
                // affichage des dates dans la page html

                var range = document.getElementById('reportrange').firstElementChild.nextSibling.nextSibling;
                var spanDates = document.getElementById('datesRange');
                range = range.innerText.replace('-', 'AU');
                spanDates.innerText = "DU " + range;

                var tbody = document.getElementById('tbody');
                var table = document.getElementById('table');
                console.log(table);
                //enlever les enfants de tbody s'il y en a
                if (tbody.hasChildNodes) {
                    table.removeChild(table.lastElementChild);
                    tbody = document.createElement('tbody');
                    tbody.id = 'tbody';
                    table.appendChild(tbody);
                }
                if (data.length > 0) {
                    for (let i = 0; i < data.length; i++) {
                        console.log(data[i].vehicule);
                        var tr = document.createElement('tr');
                        var line =
                            `
                    <td>${data[i].vehicule}</td>
                    <td>${data[i].ca}</td>
						<td>${data[i].web}</td>
						<td>${data[i].cpt}</td>
						<td></td>
						<td></td>
						<td></td>
                        `
                        tr.innerHTML = line;
                        tbody.appendChild(tr);
                    }
                } else {
                    var tr = document.createElement('tr');
                    var line = `<td colspan="7">Aucune donnée à afficher</td>`
                    tr.innerHTML = line;
                    tr.classList.add('text-center');
                    tbody.appendChild(tr);
                }
            },
            error: function(erreur) {
                // alert('La requête n\'a pas abouti' + erreur);
                console.log(erreur.responseText);
            }
        });

    }
});