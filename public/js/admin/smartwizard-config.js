var vehicule;
var agenceDepart;
var agenceRetour;
var lieuSejour;

function getElements() {

    vehicule = document.getElementById("selectVehicule");
    agenceDepart = document.getElementById("agence_depart");
    agenceRetour = document.getElementById("agence_retour");
    lieuSejour = document.getElementById("lieu_sejour");

}

$(document).ready(function () { // Step show event
    $("#smartwizard").on("showStep", function (e, anchorObject, stepNumber, stepDirection, stepPosition) {
        $("#prev-btn").removeClass('disabled');
        $("#next-btn").removeClass('disabled');
        if (stepPosition === 'first') {
            $("#prev-btn").addClass('disabled');
        } else if (stepPosition === 'last') {
            $("#next-btn").addClass('disabled');
        } else {
            $("#prev-btn").removeClass('disabled');
            $("#next-btn").removeClass('disabled');
        }
    });

    // Smart Wizard
    $('#smartwizard').smartWizard({
        selected: 0, theme: 'arrows',
        // default, arrows, dots, progress
        // darkMode: true,
        transition: {
            animation: 'slide-horizontal', // Effect on navigation, none/fade/slide-horizontal/slide-vertical/slide-swing
        },
        toolbarSettings: {
            toolbarPosition: 'both', // both bottom

        }
    });
    $("#smartwizard").on("leaveStep", function (e, anchorObject, currentStepIndex, nextStepIndex, stepDirection) {

        getElements();
        retrieveVehiculeAjax(vehicule.value);

        return alert('test');
    });

    // External Button Events
    $("#reset-btn").on("click", function () { // Reset wizard
        $('#smartwizard').smartWizard("reset");
        return true;
    });

    $("#prev-btn").on("click", function () { // Navigate previous
        $('#smartwizard').smartWizard("prev");
        return true;
    });

    $("#next-btn").on("click", function () { // Navigate next
        $('#smartwizard').smartWizard("next");
        return true;
    });


    // Demo Button Events
    $("#got_to_step").on("change", function () { // Go to step
        var step_index = $(this).val() - 1;
        $('#smartwizard').smartWizard("goToStep", step_index);
        return true;
    });


    $("#dark_mode").on("click", function () { // Change dark mode
        var options = {
            darkMode: $(this).prop("checked")
        };

        $('#smartwizard').smartWizard("setOptions", options);
        return true;
    });

    $("#is_justified").on("click", function () { // Change Justify
        var options = {
            justified: $(this).prop("checked")
        };

        $('#smartwizard').smartWizard("setOptions", options);
        return true;
    });

    $("#animation").on("change", function () { // Change theme
        var options = {
            transition: {
                animation: $(this).val()
            }
        };
        $('#smartwizard').smartWizard("setOptions", options);
        return true;
    });

    $("#theme_selector").on("change", function () { // Change theme
        var options = {
            theme: $(this).val()
        };
        $('#smartwizard').smartWizard("setOptions", options);
        return true;
    });

});
$('#smartwizard').smartWizard({
    lang: { // Language variables for button
        next: 'Suivant',
        previous: 'Precedant'
    },
    theme: 'dots'
});


function retrieveVehiculeAjax(vehicule) {
    // var d = new Date(dateInputValue);
    // var n = d.toString();
    $.ajax({
        type: 'GET',
        url: '/vehicule-vente-comptoir',
        data: {
            "vehicule_id": vehicule
        },
        Type: "json",
        success: function (data) {
            console.log(data);
            insertVehicule(data);

        },
        error: function (erreur) {
            // alert('La requête n\'a pas abouti' + erreur);
            console.log(erreur.responseText);
        }
    });
}

function insertVehicule(data) {
    var vehicule_photo = document.getElementById("vehicule_photo");
    console.log(vehicule_photo);
    $('#vehicule_photo').attr('src', "/uploads/vehicules/" + data.image + "");

}
