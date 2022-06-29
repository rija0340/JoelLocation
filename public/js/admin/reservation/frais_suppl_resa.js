$(document).ready(function () {

    $('#add-frais').click(function () { // recuperation numero futur champ a ajouter
        const index = $('#frais-table .table-ligne').length;
        // recuperer
        const template = $('#collection_frais_suppl_resa_fraisSupplResas').data('prototype').replace(/__name__/g, index);

        // injecter le code dans la div
        // td.appendChild(template);
        $('#frais-table tbody').append(template);

        handleDelete();
        handleChangePrixUnitaire();
        handleChangeQuantity();

    });

    function handleDelete() {

        $('button[data-action="delete"]').click(function () {
            const target = this.dataset.target;
            $('#' + target).remove();

        });
    }
    handleDelete();
    handleChangePrixUnitaire();
    handleChangeQuantity();

    // function calculatePrixHT() {

    //calcul et insertion quantité HT
    function handleChangePrixUnitaire() {

        $('.prixUnitaire').change(function () {
            console.log('length : ' + $('.prixUnitaire').length);

            var idQuantite = $(this).attr('id').replace('prixUnitaire', 'quantite');
            var idTotalHT = $(this).attr('id').replace('prixUnitaire', 'totalHT');

            idQuantite = '#' + idQuantite;
            idTotalHT = '#' + idTotalHT;

            if ($(idQuantite).val() != "") {
                $(idTotalHT).val($(this).val() * $(idQuantite).val());
            }
        });
    }

    function handleChangeQuantity() {
        $('.quantite').change(function () {

            console.log('length : ' + $('.quantite').length);
            var idPrixUnitaire = $(this).attr('id').replace('quantite', 'prixUnitaire');
            var idTotalHT = $(this).attr('id').replace('quantite', 'totalHT');

            idPrixUnitaire = '#' + idPrixUnitaire;
            idTotalHT = '#' + idTotalHT;

            if ($(idPrixUnitaire).val() != "") {
                $(idTotalHT).val($(this).val() * $(idPrixUnitaire).val());
            }

        });
    }



    // }

});

