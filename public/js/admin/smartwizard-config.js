// for step 1
var vehiculeSelectElem;
var agenceDepartSelectElem;
var agenceRetourSelectElem;
var lieuSejourInputElem;
var dateDepartElem;
var dateRetourElem;

var vehiculeSelected;
var agenceDepartSelected;
var agenceRetourSelected;
var lieuSejourValue;
var datetimeDepartValue;
var datetimeRetourValue;

// for step 2 , 3 , 4
var agenceDepartSpanElem;
var agenceRetourSpanElem;
var dateDepartSpanElem;
var heureDepartSpanElem;
var dateRetourSpanElem;
var heureRetourSpanElem;
var nbJrLocationSpanElem;
var vehiculeImgElem;

var tarifs;
var tarifApplique;

//  get elem vehicule details step 2
var marqueSpanElem;
var modeleSpanElem;
var carburationSpanElem;
var vitesseSpanElem;
var bagageSpanElem;
var portesSpanElem;
var passagersSpanElem;
var atoutsSpanElem;
var cautionSpanElem;
var immatriculationSpanElem;

// prix location elem
var prixSpanElem;

// var detailsVehicule from ajax; 
let detailsVehicule;

var dureeReservation;
// get elem step 3
var conducteur;
var siege;
var garantie;

// get elem step 4
var conducteurSpanElem;
var siegeSpanElem;
var garantieSpanElem;
var selectedClient;
var btnReserver;
var selectClientElem;
var listeClients = [];

getElements()
addEvent();

function initDetailsVehicule(data) {

    detailsVehicule = data;

}

function getElementsStep1() {
    // for step 1
    vehiculeSelectElem = document.querySelector('select[id="selectVehicule"]');
    agenceDepartSelectElem = document.querySelector('select[id="agence_depart"]');
    agenceRetourSelectElem = document.querySelector('select[id="agence_retour"]');
    lieuSejourInputElem = document.querySelector('input[id="lieu_sejour"]');
    dateDepartElem = document.querySelector('input[id="reservation_date_debut"]');
    dateRetourElem = document.querySelector('input[id="reservation_date_fin"]');

}

function getValuesStep1() {
    vehiculeSelected = vehiculeSelectElem.value;
    agenceDepartSelected = agenceDepartSelectElem.value;
    agenceRetourSelected = agenceRetourSelectElem.value;
    lieuSejourValue = lieuSejourInputElem.value;
    datetimeDepartValue = dateDepartElem.value;
    datetimeRetourValue = dateRetourElem.value;
}

function getElementsStep2Step3Step4() {

    agenceDepartSpanElem = document.querySelectorAll('span[id="agence_depart"]');
    agenceRetourSpanElem = document.querySelectorAll('span[id="agence_retour"]');
    dateDepartSpanElem = document.querySelectorAll('span[id="date_depart"]');
    heureDepartSpanElem = document.querySelectorAll('span[id="heure_depart"]');
    dateRetourSpanElem = document.querySelectorAll('span[id="date_retour"]');
    heureRetourSpanElem = document.querySelectorAll('span[id="heure_retour"]');
    nbJrLocationSpanElem = document.querySelectorAll('span[id="nombre_jours_location"]')
    vehiculeImgElem = document.querySelectorAll('img[id="vehicule_photo"]');
    prixSpanElem = document.querySelectorAll('span[id="prix"]');

    // special step 2
    marqueSpanElem = document.querySelector('span[id="vehicule_marque"]');
    modeleSpanElem = document.querySelector('span[id="vehicule_modele"]');
    carburationSpanElem = document.querySelector('span[id="vehicule_carburation"]');
    vitesseSpanElem = document.querySelector('span[id="vehicule_vitesse"]');
    bagageSpanElem = document.querySelector('span[id="vehicule_bagage"]');
    portesSpanElem = document.querySelector('span[id="vehicule_portes"]');
    atoutsSpanElem = document.querySelector('span[id="vehicule_atouts"]');
    cautionSpanElem = document.querySelector('span[id="vehicule_caution"]');
    passagersSpanElem = document.querySelector('span[id="vehicule_passagers"]');
    immatriculationSpanElem = document.querySelector('span[id="vehicule_immatriculation"]');

    // step 4
    selectClientElem = document.querySelector('input[id="selectClient"]');



}

function setValuesStep2Step3Step4() {


    for (let i = 0; i < agenceDepartSpanElem.length; i++) {
        agenceDepartSpanElem[i].innerText = agenceDepartSelected;
    }
    for (let i = 0; i < agenceRetourSpanElem.length; i++) {
        agenceRetourSpanElem[i].innerText = agenceRetourSelected;
    }
    for (let i = 0; i < dateDepartSpanElem.length; i++) {

        dateDepartSpanElem[i].innerText = getDate(datetimeDepartValue);
    }
    for (let i = 0; i < heureDepartSpanElem.length; i++) {

        heureDepartSpanElem[i].innerText = getHours(datetimeDepartValue);
    }

    for (let i = 0; i < dateRetourSpanElem.length; i++) {
        dateRetourSpanElem[i].innerText = getDate(datetimeRetourValue);
    }
    for (let i = 0; i < heureRetourSpanElem.length; i++) {

        heureRetourSpanElem[i].innerText = getHours(datetimeRetourValue);
    }
    for (let i = 0; i < nbJrLocationSpanElem.length; i++) {

        nbJrLocationSpanElem[i].innerText = diff2Dates(datetimeDepartValue, datetimeRetourValue);

    }
    // for vehicule details
    for (let i = 0; i < vehiculeImgElem.length; i++) {

        vehiculeImgElem[i].setAttribute("src", "/uploads/vehicules/" + detailsVehicule.image + "");

    }

    marqueSpanElem.innerText = detailsVehicule.marque;
    modeleSpanElem.innerText = detailsVehicule.modele;
    carburationSpanElem.innerText = detailsVehicule.carburation;
    vitesseSpanElem.innerText = detailsVehicule.vitesse;
    bagageSpanElem.innerText = detailsVehicule.bagages;
    portesSpanElem.innerText = detailsVehicule.portes;
    passagersSpanElem.innerText = detailsVehicule.passagers;
    atoutsSpanElem.innerText = detailsVehicule.atouts;
    cautionSpanElem.innerText = detailsVehicule.caution;
    immatriculationSpanElem.innerText = detailsVehicule.immatriculation;

}

function setValuesOptionsGarantie() {

    conducteurSpanElem.innertText = conducteur;
    siegeSpanElem.innertText = siege;
    garantieSpanElem.innertText = garantie;
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
        selected: 0,
        theme: 'dots',
        lang: { // Language variables for button
            next: 'Suivant',
            previous: 'Precedant'
        },
        // default, arrows, , progress
        // darkMode: true,
        transition: {
            animation: 'slide-horizontal', // Effect on navigation, none/fade/slide-horizontal/slide-vertical/slide-swing
        },
        toolbarSettings: {
            toolbarPosition: 'both', // both bottom

        }
    });

    $("#smartwizard").on("leaveStep", function (e, anchorObject, currentStepIndex, nextStepIndex, stepDirection) {

        if (currentStepIndex == 0 && nextStepIndex == 1) {

            getElementsStep1();
            getValuesStep1();

            if (vehiculeSelected != null && agenceDepartSelected != "Choisir" && agenceRetourSelected != "Choisir" && lieuSejourValue != "") {

                getElementsStep2Step3Step4();
                retrieveTarifsAjax(vehiculeSelected);
                retrieveVehiculeAjax(vehiculeSelected); //in success status include setValues 2,3,4

                ;
                return true;

            } else {
                alert("Veuillez remplir tous les champs");
                return false;
            }
        }

        if (currentStepIndex == 1 && nextStepIndex == 2) {

            if (dureeReservation <= 3) tarifApplique = tarifs.troisJours;

            if (dureeReservation > 3 && dureeReservation <= 7) tarifApplique = tarifs.septJours;

            if (dureeReservation > 7 && dureeReservation <= 15) tarifApplique = tarifs.quinzeJours;

            if (dureeReservation > 15 && dureeReservation <= 30) tarifApplique = tarifs.trenteJours;

            for (let i = 0; i < prixSpanElem.length; i++) {
                prixSpanElem[i].innerText = tarifApplique;

            }
            console.log(dureeReservation, tarifApplique);
            retrieveUsersAjax();
        }


        if (currentStepIndex == 2 && nextStepIndex == 3) {

            var radioConducteur = document.querySelectorAll('input[name="radio-conducteur"]');
            var radioSiege = document.querySelectorAll('input[name="radio-siege"]');
            var radioGarantie = document.querySelectorAll('input[name="radio-garantie"]');

            for (var i = 0; i < radioSiege.length; i++) {
                if (radioSiege[i].type == 'radio' && radioSiege[i].checked) {
                    switch (radioSiege[i].value) {
                        case '1':
                            siege = "Siège bébé : 30€";
                            break;
                        case '2':
                            siege = "Siège nourrisson : 30€";
                            break;
                        case '3':
                            siege = "Rehausseur : gratuit";
                            break;
                    }
                }
            }

            for (var i = 0; i < radioConducteur.length; i++) {
                if (radioConducteur[i].type == 'radio' && radioConducteur[i].checked) {
                    switch (radioConducteur[i].value) {
                        case '1':
                            conducteur = "oui";
                            break;
                        case '2':
                            conducteur = "non";
                            break;
                    }
                }
            }

            for (var i = 0; i < radioGarantie.length; i++) {
                if (radioGarantie[i].type == 'radio' && radioGarantie[i].checked) {
                    switch (radioGarantie[i].value) {
                        case '1':
                            garantie = "GARANTIE BRIS DE GLACE & PNEUS (90€)";
                            break;
                        case '2':
                            garantie = "ASSISTANCE ESPRIT TRANQUILLE (100€)";
                            break;

                        case '3':
                            garantie = " ASSISTANCE PREMIUM (160 €)";
                            break;
                    }
                }
            }
            conducteurSpanElem = document.querySelector('span[id="span_conducteur"]');
            siegeSpanElem = document.querySelector('span[id="span_siege"]');
            garantieSpanElem = document.querySelector('span[id="span_garantie"]');
            conducteurSpanElem.innerHTML = conducteur;
            siegeSpanElem.innerHTML = siege;
            garantieSpanElem.innerHTML = garantie;
            // autocomplete(document.getElementById("selectClient"), listeClients);
            autocomplete(listeClients);

            return true;
        }



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


function retrieveVehiculeAjax(vehicule) {
    // var d = new Date(dateInputValue);
    // var n = d.toString();
    $.ajax({
        type: 'GET',
        url: '/vehicule-vente-comptoir',
        data: {
            "vehicule_id": vehicule
        },
        beforeSend: function (xhr) {
            // Show the loader
            $('#smartwizard').smartWizard("loader", "show");
        },
        Type: "json",
        success: function (data) {
            initDetailsVehicule(data);
            setValuesStep2Step3Step4();
            // Hide the loader
            $('#smartwizard').smartWizard("loader", "hide");

        },
        error: function (erreur) {
            // alert('La requête n\'a pas abouti' + erreur);
            console.log(erreur.responseText);
        }
    });
}

function retrieveTarifsAjax(vehicule) {
    // var d = new Date(dateInputValue);
    // var n = d.toString();
    $.ajax({
        type: 'GET',
        url: '/tarifVenteComptoir',
        data: {
            "vehicule_id": vehicule,
            "mois": getMonth(datetimeDepartValue)
        },
        beforeSend: function (xhr) {
        },
        Type: "json",
        success: function (data) {
            console.log(data);
            console.log(getMonth(datetimeDepartValue));
            console.log(vehicule);
            tarifs = data;

        },
        error: function (erreur) {
            // alert('La requête n\'a pas abouti' + erreur);
            console.log(erreur.responseText);
        }
    });
}

function retrieveUsersAjax() {
    // var d = new Date(dateInputValue);
    // var n = d.toString();
    $.ajax({
        type: 'GET',
        url: '/user/listeclients',
        beforeSend: function (xhr) {
        },
        Type: "json",
        success: function (data) {
            console.log(data);

            for (let i = 0; i < data.length; i++) {

                listeClients.push(data[i].prenom + ' ' + data[i].nom + ' (' + data[i].email + ')');

            }

            console.log(listeClients)
            // populateSelectClientElem(data);

        },
        error: function (erreur) {
            // alert('La requête n\'a pas abouti' + erreur);
            console.log(erreur.responseText);
        }
    });
}

function populateSelectClientElem(options) {

    $("#selectClient").empty(); //remove old options jquery

    for (var i = 0; i < options.length; i++) {

        var opt = options[i];
        console.log('ity ny opt ' + opt.marque);
        var el = document.createElement("option");
        el.text = opt.nom + ' ' + opt.prenom + ' (' + opt.email + ')';
        el.value = opt.id + " " + opt.nom;
        selectClientElem.add(el);

    }
}

function getMonth(date) {
    var date = new Date(date);
    var month;
    switch (date.getMonth() + 1) {
        case 1:
            month = 'Janvier';
            break;
        case 2:
            month = 'Fevrier';
            break;

        case 3:
            month = 'Mars';
            break;
        case 4:
            month = 'Avril';
            break;
        case 5:
            month = 'Mai';
            break;
        case 6:
            month = 'Juin';
            break;
        case 7:
            month = 'Juillet';
            break;
        case 8:
            month = 'Août';
            break;
        case 9:
            month = 'Septembre';
            break;
        case 10:
            month = 'Octobre';
            break;
        case 11:
            month = 'Novembre';
            break;
        case 12:
            month = 'Décembre';
            break;
    }

    return month;
}
function getDate(date) {
    var date = new Date(date);
    var day = date.getDate();
    var month;
    var year = date.getFullYear();
    switch (date.getMonth() + 1) {
        case 1:
            month = 'Janvier';
            break;
        case 2:
            month = 'Fevrier';
            break;

        case 3:
            month = 'Mars';
            break;
        case 4:
            month = 'Avril';
            break;
        case 5:
            month = 'Mai';
            break;
        case 6:
            month = 'Juin';
            break;
        case 7:
            month = 'Juillet';
            break;
        case 8:
            month = 'Août';
            break;
        case 9:
            month = 'Septembre';
            break;
        case 10:
            month = 'Octobre';
            break;
        case 11:
            month = 'Novembre';
            break;
        case 12:
            month = 'Décembre';
            break;
    }
    if (day < 10) {
        day = "0" + day;
    }
    return day + "-" + month + "-" + year;
}

function getHours(date) {
    var date = new Date(date);
    var hours = date.getHours();
    var minutes = date.getMinutes();

    return hours + ":" + minutes;
}

function diff2Dates(date1, date2) {

    var diffTS = dateToTimestamp(date2) - dateToTimestamp(date1);
    dureeReservation = Math.floor(diffTS / (24 * 60 * 60 * 1000))

    return dureeReservation;

}

function dateToTimestamp(date) {

    return new Date(date).getTime();

}

function getElements() {
    btnReserver = document.getElementById('reserver');

}
function addEvent() {

    btnReserver.addEventListener('click', selectClient, false);
}

function selectClient() {
    console.log(selectClientElem);
    selectedClient = selectClientElem.value;
    console.log("data : " + selectedClient, agenceDepartSelected, agenceRetourSelected, lieuSejourValue, datetimeDepartValue, datetimeRetourValue, dureeReservation,
        tarifApplique, conducteur, siege, garantie);
}

function autocomplete(listeClients) {
    $(function () {
        var availableTags = ["rakotoarinalina rija", "rakotoarinalina benjamina", "rakotoarinalina benjamina (rakoto@gmail.com)",];
        $("#selectClient").autocomplete({ source: listeClients });
    });
}

