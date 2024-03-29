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

    let defaultSelectedOptions = $("#options_garanties_data").data('selected-options');
    let defaultSelectedGaranties = $("#options_garanties_data").data('selected-garanties');
    let defaultConducteur = $("#options_garanties_data").data('conducteur');
    let garanties = $("#options_garanties_data").data('garanties');
    let options = $("#options_garanties_data").data('options');
    let subscribedOptions = $("#optionsSubscribed");
    let subscribedGaranties = $("#garantiesSubscribed");

    const conducteurLabel = "Conducteur additionnel";

    var optionsPrix = 0;
    var garantiesPrix = 0;

    setDefaultGaranties();
    setDefaultOptions();
    setDefaultConducteur();
    calculSommeOptions();
    calculSommeGaranties();

    function setDefaultConducteur() {
        if (parseInt(defaultConducteur) == 1) {
            let li = document.createElement('li');
            li.innerText = conducteurLabel;
            subscribedOptions.append(li);

        }
    }

    //check les checkes options
    function setDefaultOptions() {
        for (let i = 0; i < defaultSelectedOptions.length; i++) {
            let li = document.createElement('li');
            li.innerHTML = defaultSelectedOptions[i].appelation;
            optionsSubscribed.append(li);

        }
        $("input[name='checkboxOptions[]']").each(function () {

            for (let i = 0; i < defaultSelectedOptions.length; i++) {

                if ($(this).val() == defaultSelectedOptions[i].id) {

                    $(this).attr('checked', 'checked');
                }
            }
        });
    }

    //check les checkes garanties

    function setDefaultGaranties() {
        for (let i = 0; i < defaultSelectedGaranties.length; i++) {
            let li = document.createElement('li');
            li.innerHTML = defaultSelectedGaranties[i].appelation;
            subscribedGaranties.append(li);

        }
        $("input[name='checkboxGaranties[]']").each(function () {

            for (let i = 0; i < defaultSelectedGaranties.length; i++) {

                if ($(this).val() == defaultSelectedGaranties[i].id) {

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

        let conducteurPrix = $('input[name="radio-conducteur"]:checked').val() == "true" ? 50 : 0;

        //ajouter prix conducteur avec prix options
        $("#prixOptions").html(optionsPrix + conducteurPrix + " €");

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
            clearGaranties();
            setSelectedGaranties();
        });
    });


    $("input[name='checkboxOptions[]']").each(function () {
        $(this).change(function () {
            calculSommeOptions();
            clearOptions();
            setSelectedOptions();
        });
    });

    // handle conducteur change 

    $("input[name='radio-conducteur']").change(function () {

        calculSommeOptions();
        clearOptions();
        setSelectedOptions();
    });


    function setSelectedOptions() {

        $("input[name='checkboxOptions[]']").each(function () {
            if ($(this).is(':checked')) {
                console.log($(this));
                for (let i = 0; i < options.length; i++) {
                    if ($(this).val() == options[i].id) {
                        let li = document.createElement('li');
                        li.innerHTML = options[i].appelation;
                        subscribedOptions.append(li);
                    }
                }
            }
        });
        if ($('input[name="radio-conducteur"]:checked').val() == "true") {
            let li = document.createElement('li');
            li.innerHTML = conducteurLabel;
            subscribedOptions.append(li);
        }

    }


    function setSelectedGaranties() {

        $("input[name='checkboxGaranties[]']").each(function () {
            if ($(this).is(':checked')) {
                for (let i = 0; i < garanties.length; i++) {
                    if ($(this).val() == garanties[i].id) {
                        let li = document.createElement('li');
                        li.innerHTML = garanties[i].appelation;
                        subscribedGaranties.append(li);
                    }
                }
            }
        });
    }

    function clearOptions() {
        var optionsChildren = subscribedOptions.children();
        if (optionsChildren.length > 0) {
            for (let i = 0; i < optionsChildren.length; i++) {
                optionsChildren[i].remove();
            }
        }
    }

    function clearGaranties() {
        var garantiesChildren = subscribedGaranties.children();
        if (garantiesChildren.length > 0) {
            for (let i = 0; i < garantiesChildren.length; i++) {
                garantiesChildren[i].remove();
            }
        }
    }
});