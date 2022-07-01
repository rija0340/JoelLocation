$(document).ready(function () {


    // $(window).on('load', function () {

    //     console.log($('#frais-table tbody tr').length);

    //     if (parseInt($('#frais-table tbody tr').length) == 0) {
    //         console.log($('#frais-table'));
    //         $('#frais-table').addClass('hide');
    //     } else {
    //         $('#frais-table').removeClass('hide');
    //     }

    // });

    $('#add-frais').click(function () { // recuperation numero futur champ a ajouter


        // remove hide if table hidden
        if ($('#frais-table').hasClass('hide')) {
            $('#frais-table').removeClass('hide');
        }

        if ($('#btnSaveFrais').hasClass('hide')) {
            $('#btnSaveFrais').removeClass('hide');
        }

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


            if (parseInt($('#frais-table tbody tr').length) == 0) {
                $('#frais-table').addClass('hide');
                $('#btnSaveFrais').addClass('hide');
            } else {
                $('#btnSaveFrais').removeClass('hide');
                $('#frais-table').removeClass('hide');
            }

            $('#btnSaveFrais').trigger('click');
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

