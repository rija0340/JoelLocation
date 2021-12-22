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
            url: '/paiement/liste-paiements',
            data: {
                'dateDebut': dateDebut,
                'dateFin': dateFin
            },
            Type: "json",
            success: function(data) {
                LoadDataDatatable(data);
            },
            error: function(erreur) {
                // alert('La requête n\'a pas abouti' + erreur);
                console.log(erreur.responseText);
            }
        });

    }

    function LoadDataDatatable(data) {

        var data = data;
        // dateSpanElem.innerText = new Date(dateInputValue).toLocaleDateString('fr-FR');
        // addDateToHtml(dateInputValue);

        //Load  datatable

        // var oTblReport = $("#tblReportResultsDemographics");
        var table = $("#datatable");

        if (!$.fn.DataTable.isDataTable('#datatable')) {
            paiementDatatable = table.DataTable({
                "data": data,
                // dom: 'Blfrtip',
                // buttons: [
                //     'csv', 'excel', 'pdf'
                // ],
                "data": data,
                "columns": [
                    { "data": "reservation" },
                    { "data": "client" },
                    { "data": "montant" },
                    { "data": "type" },
                    { "data": "date" },
                    {
                        "data": "reservationID",
                        render: function(data, type, row, val) {
                            return '<a href="http://localhost:8000/backoffice/reservation/details/' + row.reservationID + '">  <i class=" fa fa-info-circle text-danger" style="font-size: 2em !important; "></i> </a>';
                        }
                    },
                ],
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
                        "next": "Suiv"
                    },
                    "linkedCalendars": false,
                    "aria": {
                        "sortAscending": ": activer pour trier la colonne par ordre croissant",
                        "sortDescending": ": activer pour trier la colonne par ordre décroissant"
                    },
                    "select": {
                        "rows": {
                            "_": "%d lignes sélectionnées",
                            "0": "Aucune ligne sélectionnée",
                            "1": "1 ligne sélectionnée"
                        },
                        "1": "1 ligne selectionnée",
                        "_": "%d lignes selectionnées",
                        "cells": {
                            "1": "1 cellule sélectionnée",
                            "_": "%d cellules sélectionnées"
                        },
                        "columns": {
                            "1": "1 colonne sélectionnée",
                            "_": "%d colonnes sélectionnées"
                        }
                    },
                    "autoFill": {
                        "cancel": "Annuler",
                        "fill": "Remplir toutes les cellules avec <i>%d<\/i>",
                        "fillHorizontal": "Remplir les cellules horizontalement",
                        "fillVertical": "Remplir les cellules verticalement",
                        "info": "Exemple de remplissage automatique"
                    },
                    "searchBuilder": {
                        "conditions": {
                            "date": {
                                "after": "Après le",
                                "before": "Avant le",
                                "between": "Entre",
                                "empty": "Vide",
                                "equals": "Egal à",
                                "not": "Différent de",
                                "notBetween": "Pas entre",
                                "notEmpty": "Non vide"
                            },
                            "number": {
                                "between": "Entre",
                                "empty": "Vide",
                                "equals": "Egal à",
                                "gt": "Supérieur à",
                                "gte": "Supérieur ou égal à",
                                "lt": "Inférieur à",
                                "lte": "Inférieur ou égal à",
                                "not": "Différent de",
                                "notBetween": "Pas entre",
                                "notEmpty": "Non vide"
                            },
                            "string": {
                                "contains": "Contient",
                                "empty": "Vide",
                                "endsWith": "Se termine par",
                                "equals": "Egal à",
                                "not": "Différent de",
                                "notEmpty": "Non vide",
                                "startsWith": "Commence par"
                            },
                            "array": {
                                "equals": "Egal à",
                                "empty": "Vide",
                                "contains": "Contient",
                                "not": "Différent de",
                                "notEmpty": "Non vide",
                                "without": "Sans"
                            }
                        },
                        "add": "Ajouter une condition",
                        "button": {
                            "0": "Recherche avancée",
                            "_": "Recherche avancée (%d)"
                        },
                        "clearAll": "Effacer tout",
                        "condition": "Condition",
                        "data": "Donnée",
                        "deleteTitle": "Supprimer la règle de filtrage",
                        "logicAnd": "Et",
                        "logicOr": "Ou",
                        "title": {
                            "0": "Recherche avancée",
                            "_": "Recherche avancée (%d)"
                        },
                        "value": "Valeur"
                    },
                    "searchPanes": {
                        "clearMessage": "Effacer tout",
                        "count": "{total}",
                        "title": "Filtres actifs - %d",
                        "collapse": {
                            "0": "Volet de recherche",
                            "_": "Volet de recherche (%d)"
                        },
                        "countFiltered": "{shown} ({total})",
                        "emptyPanes": "Pas de volet de recherche",
                        "loadMessage": "Chargement du volet de recherche..."
                    },
                    "buttons": {
                        "copyKeys": "Appuyer sur ctrl ou u2318 + C pour copier les données du tableau dans votre presse-papier.",
                        "collection": "Collection",
                        "colvis": "Visibilité colonnes",
                        "colvisRestore": "Rétablir visibilité",
                        "copy": "Copier",
                        "copySuccess": {
                            "1": "1 ligne copiée dans le presse-papier",
                            "_": "%ds lignes copiées dans le presse-papier"
                        },
                        "copyTitle": "Copier dans le presse-papier",
                        "csv": "CSV",
                        "excel": "Excel",
                        "pageLength": {
                            "-1": "Afficher toutes les lignes",
                            "1": "Afficher 1 ligne",
                            "_": "Afficher %d lignes"
                        },
                        "pdf": "PDF",
                        "print": "Imprimer"
                    },
                    "decimal": ",",
                    "info": "Affichage de _START_ à _END_ sur _TOTAL_ éléments",
                    "infoEmpty": "Affichage de 0 à 0 sur 0 éléments",
                    "infoThousands": ".",
                    "search": "Rechercher:",
                    "searchPlaceholder": "...",
                    "thousands": ".",
                    "infoFiltered": "(filtrés depuis un total de _MAX_ éléments)",
                    "datetime": {
                        "previous": "Précédent",
                        "next": "Suivant",
                        "hours": "Heures",
                        "minutes": "Minutes",
                        "seconds": "Secondes",
                        "unknown": "-",
                        "amPm": ["am", "pm"]
                    },
                    "editor": {
                        "close": "Fermer",
                        "create": {
                            "button": "Nouveaux",
                            "title": "Créer une nouvelle entrée",
                            "submit": "Envoyer"
                        },
                        "edit": {
                            "button": "Editer",
                            "title": "Editer Entrée",
                            "submit": "Modifier"
                        },
                        "remove": {
                            "button": "Supprimer",
                            "title": "Supprimer",
                            "submit": "Supprimer"
                        },
                        "error": {
                            "system": "Une erreur système s'est produite"
                        },
                        "multi": {
                            "title": "Valeurs Multiples",
                            "restore": "Rétablir Modification"
                        }
                    }
                }
            });

            new $.fn.dataTable.Buttons(paiementDatatable, {
                buttons: [

                    {
                        extend: 'csv',
                        text: '<i class="fa fa-files-o"></i> CSV',
                        titleAttr: 'CSV',
                        className: 'btn btn-dark btn-sm',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fa fa-files-o"></i> Excel',
                        titleAttr: 'Excel',
                        title: 'Véhicules disponible du ',
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fa fa-file-pdf-o"></i> PDF',
                        titleAttr: 'PDF',
                        title: 'Véhicules disponible du ',
                        className: 'btn btn-danger btn-sm',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fa fa-print"></i> Print',
                        titleAttr: 'Print',
                        className: 'btn btn-primary btn-sm',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                ]
            });
            if (data.length != 0) {

                paiementDatatable.buttons().container().appendTo('#test');
            }
        } else {
            // table.dataTable().fnClearTable();
            table.dataTable().fnDestroy();
            paiementDatatable = table.DataTable({
                "data": data,
                // dom: 'Blfrtip',
                // buttons: [
                //     'csv', 'excel', 'pdf'
                // ],
                "columns": [
                    { "data": "reservation" },
                    { "data": "client" },
                    { "data": "montant" },
                    { "data": "type" },
                    { "data": "date" },
                    {
                        "data": "reservationID",
                        render: function(data, type, row, val) {
                            return '<a href="http://localhost:8000/backoffice/reservation/details/' + row.reservationID + '">  <i class=" fa fa-info-circle text-danger" style="font-size: 2em !important; "></i> </a>';
                        }
                    },
                    //     { "data": null, "defaultContent": `<a href="http://localhost:8000/backoffice/reservation/details/${data.reservationID}">
                    //     <i class=" fa fa-info-circle text-danger" style="font-size: 2em !important; "></i>
                    // </a>` },

                ],
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
                        "next": "Suiv"
                    },
                    "linkedCalendars": false,
                    "aria": {
                        "sortAscending": ": activer pour trier la colonne par ordre croissant",
                        "sortDescending": ": activer pour trier la colonne par ordre décroissant"
                    },
                    "select": {
                        "rows": {
                            "_": "%d lignes sélectionnées",
                            "0": "Aucune ligne sélectionnée",
                            "1": "1 ligne sélectionnée"
                        },
                        "1": "1 ligne selectionnée",
                        "_": "%d lignes selectionnées",
                        "cells": {
                            "1": "1 cellule sélectionnée",
                            "_": "%d cellules sélectionnées"
                        },
                        "columns": {
                            "1": "1 colonne sélectionnée",
                            "_": "%d colonnes sélectionnées"
                        }
                    },
                    "autoFill": {
                        "cancel": "Annuler",
                        "fill": "Remplir toutes les cellules avec <i>%d<\/i>",
                        "fillHorizontal": "Remplir les cellules horizontalement",
                        "fillVertical": "Remplir les cellules verticalement",
                        "info": "Exemple de remplissage automatique"
                    },
                    "searchBuilder": {
                        "conditions": {
                            "date": {
                                "after": "Après le",
                                "before": "Avant le",
                                "between": "Entre",
                                "empty": "Vide",
                                "equals": "Egal à",
                                "not": "Différent de",
                                "notBetween": "Pas entre",
                                "notEmpty": "Non vide"
                            },
                            "number": {
                                "between": "Entre",
                                "empty": "Vide",
                                "equals": "Egal à",
                                "gt": "Supérieur à",
                                "gte": "Supérieur ou égal à",
                                "lt": "Inférieur à",
                                "lte": "Inférieur ou égal à",
                                "not": "Différent de",
                                "notBetween": "Pas entre",
                                "notEmpty": "Non vide"
                            },
                            "string": {
                                "contains": "Contient",
                                "empty": "Vide",
                                "endsWith": "Se termine par",
                                "equals": "Egal à",
                                "not": "Différent de",
                                "notEmpty": "Non vide",
                                "startsWith": "Commence par"
                            },
                            "array": {
                                "equals": "Egal à",
                                "empty": "Vide",
                                "contains": "Contient",
                                "not": "Différent de",
                                "notEmpty": "Non vide",
                                "without": "Sans"
                            }
                        },
                        "add": "Ajouter une condition",
                        "button": {
                            "0": "Recherche avancée",
                            "_": "Recherche avancée (%d)"
                        },
                        "clearAll": "Effacer tout",
                        "condition": "Condition",
                        "data": "Donnée",
                        "deleteTitle": "Supprimer la règle de filtrage",
                        "logicAnd": "Et",
                        "logicOr": "Ou",
                        "title": {
                            "0": "Recherche avancée",
                            "_": "Recherche avancée (%d)"
                        },
                        "value": "Valeur"
                    },
                    "searchPanes": {
                        "clearMessage": "Effacer tout",
                        "count": "{total}",
                        "title": "Filtres actifs - %d",
                        "collapse": {
                            "0": "Volet de recherche",
                            "_": "Volet de recherche (%d)"
                        },
                        "countFiltered": "{shown} ({total})",
                        "emptyPanes": "Pas de volet de recherche",
                        "loadMessage": "Chargement du volet de recherche..."
                    },
                    "buttons": {
                        "copyKeys": "Appuyer sur ctrl ou u2318 + C pour copier les données du tableau dans votre presse-papier.",
                        "collection": "Collection",
                        "colvis": "Visibilité colonnes",
                        "colvisRestore": "Rétablir visibilité",
                        "copy": "Copier",
                        "copySuccess": {
                            "1": "1 ligne copiée dans le presse-papier",
                            "_": "%ds lignes copiées dans le presse-papier"
                        },
                        "copyTitle": "Copier dans le presse-papier",
                        "csv": "CSV",
                        "excel": "Excel",
                        "pageLength": {
                            "-1": "Afficher toutes les lignes",
                            "1": "Afficher 1 ligne",
                            "_": "Afficher %d lignes"
                        },
                        "pdf": "PDF",
                        "print": "Imprimer"
                    },
                    "decimal": ",",
                    "info": "Affichage de _START_ à _END_ sur _TOTAL_ éléments",
                    "infoEmpty": "Affichage de 0 à 0 sur 0 éléments",
                    "infoThousands": ".",
                    "search": "Rechercher:",
                    "searchPlaceholder": "...",
                    "thousands": ".",
                    "infoFiltered": "(filtrés depuis un total de _MAX_ éléments)",
                    "datetime": {
                        "previous": "Précédent",
                        "next": "Suivant",
                        "hours": "Heures",
                        "minutes": "Minutes",
                        "seconds": "Secondes",
                        "unknown": "-",
                        "amPm": ["am", "pm"]
                    },
                    "editor": {
                        "close": "Fermer",
                        "create": {
                            "button": "Nouveaux",
                            "title": "Créer une nouvelle entrée",
                            "submit": "Envoyer"
                        },
                        "edit": {
                            "button": "Editer",
                            "title": "Editer Entrée",
                            "submit": "Modifier"
                        },
                        "remove": {
                            "button": "Supprimer",
                            "title": "Supprimer",
                            "submit": "Supprimer"
                        },
                        "error": {
                            "system": "Une erreur système s'est produite"
                        },
                        "multi": {
                            "title": "Valeurs Multiples",
                            "restore": "Rétablir Modification"
                        }
                    }
                }
            });
            new $.fn.dataTable.Buttons(paiementDatatable, {
                buttons: [

                    {
                        extend: 'csv',
                        text: '<i class="fa fa-files-o"></i> CSV',
                        titleAttr: 'CSV',
                        className: 'btn btn-dark btn-sm',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fa fa-files-o"></i> Excel',
                        titleAttr: 'Excel',
                        title: 'Véhicules disponible du ',
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fa fa-file-pdf-o"></i> PDF',
                        titleAttr: 'PDF',
                        title: 'Véhicules disponible du ',
                        className: 'btn btn-danger btn-sm',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fa fa-print"></i> Print',
                        titleAttr: 'Print',
                        className: 'btn btn-primary btn-sm',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                ]
            });
            if (data.length != 0) {

                paiementDatatable.buttons().container().appendTo('#test');
            }
        }
        // table.destroy();

    }
});