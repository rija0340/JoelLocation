// var options = [];
// var garanties = [];
// var optionsCheckboxes;
// var garantiesCheckboxes;
// var sommePrixOptions = 0;
// var sommePrixGaranties = 0;
// var divPrixOptions;
// var divPrixGaranties;
// var ajaxOptions;
// var ajaxGaranties;

// getElements();
// getOptions();
// getGaranties();
// checkAjaxReturns();
// addEventListener();

// function defaultValues() {
//     calculSommeGaranties();
//     calculSommeOptions();
// }

// function getOptions() {
//     ajaxOptions = $.ajax({
//         type: 'GET',
//         url: '/backoffice/options/liste',
//         success: function (data) {
//         },
//         error: function () {
//             alert('La requête n\'a pas abouti');
//         }
//     });
// }

// function getGaranties() {
//     ajaxGaranties = $.ajax({
//         type: 'GET',
//         url: '/listeGaranties',
//         success: function (data) {
//         },
//         error: function () {
//             alert('La requête n\'a pas abouti');
//         }
//     });
// }

// //lorsque les requetes ajax sont retourné, on appelle fonction default pour afficher defaults values
// function checkAjaxReturns() {
//     $.when(ajaxOptions, ajaxGaranties).done(function (a1, a2) {
//         // a1 and a2 are arguments resolved for the page1 and page2 ajax requests, respectively.
//         // Each argument is an array with the following structure: [ data, statusText, jqXHR ]
//         options = a1[0];
//         garanties = a2[0];
//         defaultValues();

//     });
// }

// function getElements() {

//     optionsCheckboxes = document.querySelectorAll("input[name='options_garanties[options][]']");
//     garantiesCheckboxes = document.querySelectorAll("input[name='options_garanties[garanties][]']");
//     divPrixGaranties = document.getElementById('prixGaranties');
//     divPrixOptions = document.getElementById('prixOptions');

// }

// function addEventListener() {
//     for (let i = 0; i < optionsCheckboxes.length; i++) {
//         optionsCheckboxes[i].addEventListener('click', calculSommeOptions, false);
//     }
//     for (let i = 0; i < garantiesCheckboxes.length; i++) {
//         garantiesCheckboxes[i].addEventListener('click', calculSommeGaranties, false);
//     }
// }

// function calculSommeOptions() {
//     sommePrixOptions = 0;
//     for (let i = 0; i < optionsCheckboxes.length; i++) {
//         if (optionsCheckboxes[i].checked) {
//             sommePrixOptions = sommePrixOptions + getPrixOptionFromId(optionsCheckboxes[i].value);
//         }
//     }
//     divPrixOptions.innerHTML = sommePrixOptions + "€";
// }

// function calculSommeGaranties() {
//     sommePrixGaranties = 0;
//     for (let i = 0; i < garantiesCheckboxes.length; i++) {
//         if (garantiesCheckboxes[i].checked) {
//             sommePrixGaranties = sommePrixGaranties + getPrixGarantieFromId(garantiesCheckboxes[i].value);
//         }
//     }
//     divPrixGaranties.innerHTML = sommePrixGaranties + "€";
// }

// function getPrixOptionFromId(id) {
//     for (let i = 0; i < options.length; i++) {
//         if (options[i].id == id) {
//             return options[i].prix;
//         }
//     }
// }

// function getPrixGarantieFromId(id) {
//     for (let i = 0; i < garanties.length; i++) {
//         if (garanties[i].id == id) {
//             return garanties[i].prix;
//         }
//     }
// }


$(document).ready(function () {

    selectedOptions = $("#options_garanties_data").data('selected-options');
    selectedGaranties = $("#options_garanties_data").data('selected-garanties');
    garanties = $("#options_garanties_data").data('garanties');
    options = $("#options_garanties_data").data('options');

    var optionsPrix = 0;
    var garantiesPrix = 0;

    setDefaultGaranties();
    setDefaultOptions();
    calculSommeOptions();
    calculSommeGaranties();

    //check les checkes options
    function setDefaultOptions() {

        $("input[name='checkboxOptions[]']").each(function () {

            for (let i = 0; i < selectedOptions.length; i++) {

                if ($(this).val() == selectedOptions[i].id) {

                    $(this).attr('checked', 'checked');
                }
            }
        });
    }

    //check les checkes garanties

    function setDefaultGaranties() {
        $("input[name='checkboxGaranties[]']").each(function () {

            for (let i = 0; i < selectedGaranties.length; i++) {

                if ($(this).val() == selectedGaranties[i].id) {

                    $(this).attr('checked', 'checked');

                }
            }
        });
    }

    // calcul somme options
    function calculSommeOptions() {
        optionsPrix = 0;

        $("input[name='checkboxOptions[]']").each(function () {

            if ($(this).is(':checked')) {
                for (let i = 0; i < options.length; i++) {
                    if ($(this).val() == options[i].id) {
                        optionsPrix = optionsPrix + options[i].prix;
                    }
                }
            }
        });
        $("#prixOptions").html(optionsPrix + " €");

    }

    // calcul somme options
    function calculSommeGaranties() {
        garantiesPrix = 0;
        $("input[name='checkboxGaranties[]']").each(function () {

            if ($(this).is(':checked')) {
                for (let i = 0; i < garanties.length; i++) {
                    if ($(this).val() == garanties[i].id) {
                        garantiesPrix = garantiesPrix + garanties[i].prix;
                    }
                }
            }

        });
        $("#prixGaranties").html(garantiesPrix + " €");
    }

    $("input[name='checkboxGaranties[]']").each(function () {
        $(this).change(function () {
            calculSommeGaranties();
        });
    });


    $("input[name='checkboxOptions[]']").each(function () {
        $(this).change(function () {
            calculSommeOptions();
        });
    });


});