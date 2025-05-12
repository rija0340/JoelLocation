// Configuration DataTable pour l'onglet Devis
$(document).ready(function () {
    // Détruire l'instance existante pour éviter les conflits
    if ($.fn.dataTable.isDataTable('#datatable_devis')) {
        $('#datatable_devis').DataTable().destroy();
    }

    // Initialiser avec les bonnes options
    $('#datatable_devis').DataTable({
        "scrollX": true,
        "order": [[0, "desc"]],
        "language": {
            "emptyTable": "Aucune donnée disponible dans le tableau",
            "lengthMenu": "Afficher _MENU_ éléments",
            "loadingRecords": "Chargement...",
            "processing": "Traitement...",
            "zeroRecords": "Aucun élément correspondant trouvé",
            "paginate": {
                "first": "Premier",
                "last": "Dernier",
                "previous": "Précédent",
                "next": "Suivant"
            },
            "aria": {
                "sortAscending": ": activer pour trier la colonne par ordre croissant",
                "sortDescending": ": activer pour trier la colonne par ordre décroissant"
            },
            "info": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
            "infoEmpty": "Affichage de 0 à 0 sur 0 entrées",
            "infoFiltered": "(filtré depuis _MAX_ entrées au total)",
            "search": "Rechercher:"
        },
        "responsive": true,
        "columnDefs": [
            { "responsivePriority": 1, "targets": 0 },
            { "responsivePriority": 2, "targets": -1 }
        ],
        "drawCallback": function () {
            // Réinitialiser les styles après le rendu
            $('.reservation-container table.dataTable thead th').css('background-color', '#444444');
            $('.reservation-container table.dataTable thead th').css('color', '#ffffff');
        }
    });
});
