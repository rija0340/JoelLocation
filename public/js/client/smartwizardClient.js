//-----------------------ETAPE 1------------------------------
//etape  choix / agenceDepart, agenceRetour, dateDepart, dateRetour, Lieu séjour, véhicule
//après choix des deux dates : une requête ajax est envoyé pour recuperer la liste voitures disponible pendant cet interval de dates.
//requete ajax pour recuperer liste "options" et "garanties" ainsi que détails véhicule choisi


//---------------------ETAPE 2-------------------------------
//affichage détails véhicule choisi dans étape 1 ->
//récupération éléments html et ajout de valeurs (détails véhicule)

//--------------------- ETAPE 3--------------------------------
//affichage recapitulatif des choix faites dans étape 1 (à gauche )
//recuperation html et ajout de valeur 

//affichage options et garanties (données recupérées depuis étape 1)
//choix option et garantie par le client (chauffeur, siège enfant, garantie)


//-----------------------ETAPE 4--------------------------------
//affichage recapitulatif des choix faites dans les etapes précedentes
//formulaire de choix du client proprio du devis 
//formulaire de creation de nouveau client (mot de passe par défaut : ?prenom+nom+numtelepone)
//bouton "enregistrerPDF" du devis -> génere un pdf à partir des données qui doivent figurer dans le devis 
//bouton "enregistrer Devis" -> enregistrement du devis dans table "devis" dans la base de données, 
//bouton "reserver" -> enregistrer directement le devis en tant que "contrat"

$(document).ready(function () { //S'assure que le dom est entièrement chargé

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
    var siege = Array();
    var garantie;
    var idGarantie;
    var idSiege;
    var radioConducteur;
    var radioSiege;
    var radioGarantie;

    // get elem step 4
    var conducteurSpanElem;
    var siegeSpanElem;
    var garantieSpanElem;

    var btnReserver;

    //champ caché dans html steps>index.html
    var clientID;


    var listeOptions;
    var listeGaranties;
    //formulaire creation client




    getElements()
    addEvent();
    initSmartWizard();


    function getValuesStep1() {
        vehiculeSelected = vehiculeSelectElem.value;
        agenceDepartSelected = agenceDepartSelectElem.value;
        agenceRetourSelected = agenceRetourSelectElem.value;
        lieuSejourValue = lieuSejourInputElem.value;
        datetimeDepartValue = dateDepartElem.value;
        datetimeRetourValue = dateRetourElem.value;
    }

    function getValuesStep3() {
        //recuperer valeur value radio (id option) et l'option correspondante
        for (var i = 0; i < radioSiege.length; i++) {
            if (radioSiege[i].type == 'radio' && radioSiege[i].checked) {
                idSiege = radioSiege[i].value;
            }
        }

        for (let i = 0; i < listeOptions.length; i++) {
            if (idSiege == listeOptions[i].id) {
                siege = listeOptions[i];
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

        //recuperer valeur value radio (id garantie) et la garentie qui corresponde
        for (var i = 0; i < radioGarantie.length; i++) {
            if (radioGarantie[i].type == 'radio' && radioGarantie[i].checked) {
                idGarantie = radioGarantie[i].value;
            }
        }
        for (let i = 0; i < listeGaranties.length; i++) {
            if (idGarantie == listeGaranties[i].id) {
                garantie = listeGaranties[i];
            }

        }
    }

    function setValuesRecapitulatif() {

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

    }
    function setValuesOptionGarantie() {
        //existe seulement dans step4
        conducteurSpanElem.innerHTML = conducteur;
        siegeSpanElem.innerHTML = siege.appelation;
        garantieSpanElem.innerHTML = garantie.appelation;
    }

    function setValuesDetailsVehicule() {
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


    //à chaque passage d'un étape à un autre des fonctions sont exécutées selon current et next StepIndex
    $("#smartwizard").on("leaveStep", function (e, anchorObject, currentStepIndex, nextStepIndex, stepDirection) {

        if (currentStepIndex == 0 && nextStepIndex == 1) {
            tachesStep1toStep2();
        }

        if (currentStepIndex == 1 && nextStepIndex == 2) {

            tachesStep2toStep3();

        }

        if (currentStepIndex == 2 && nextStepIndex == 3) {

            tachesStep3toStep4();
        }

    });

    function getElements() {

        //ETAPE 1 -> elements html dans l'étape 1
        vehiculeSelectElem = document.querySelector('select[id="selectVehicule"]');
        agenceDepartSelectElem = document.querySelector('select[id="agence_depart"]');
        agenceRetourSelectElem = document.querySelector('select[id="agence_retour"]');
        lieuSejourInputElem = document.querySelector('input[id="lieu_sejour"]');
        dateDepartElem = document.querySelector('input[id="reservation_date_debut"]');
        dateRetourElem = document.querySelector('input[id="reservation_date_fin"]');

        // ETAPE 2 -> elements html pour Afficher détails véhicule choisi
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

        //ETAPE 3

        radioConducteur = document.querySelectorAll('input[name="radio-conducteur"]');
        radioSiege = document.querySelectorAll('input[name="radio-siege"]');
        radioGarantie = document.querySelectorAll('input[name="radio-garantie"]');


        //ETAPE 3 et ETAPE 4
        //Recapitulatif choix faite dans ETAPE 1
        agenceDepartSpanElem = document.querySelectorAll('span[id="agence_depart"]');
        agenceRetourSpanElem = document.querySelectorAll('span[id="agence_retour"]');
        dateDepartSpanElem = document.querySelectorAll('span[id="date_depart"]');
        heureDepartSpanElem = document.querySelectorAll('span[id="heure_depart"]');
        dateRetourSpanElem = document.querySelectorAll('span[id="date_retour"]');
        heureRetourSpanElem = document.querySelectorAll('span[id="heure_retour"]');
        nbJrLocationSpanElem = document.querySelectorAll('span[id="nombre_jours_location"]')
        vehiculeImgElem = document.querySelectorAll('img[id="vehicule_photo"]');
        prixSpanElem = document.querySelectorAll('span[id="prix"]');

        conducteurSpanElem = document.querySelector('span[id="span_conducteur"]');
        siegeSpanElem = document.querySelector('span[id="span_siege"]');
        garantieSpanElem = document.querySelector('span[id="span_garantie"]');

        //ETAPE 4 
        clientID = document.getElementById('clientID');
        btnReserver = document.getElementById('reserver');



    }
    function addEvent() {

        btnReserver.addEventListener('click', reserverAjax, false);


    }



    //--------------------------liste des fonctions AJAX--------------------

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
                setValuesDetailsVehicule();
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


    function getListeGarantiesAjax() {
        $.ajax({
            type: 'GET',
            url: "/listeOptions",
            timeout: 3000,
            beforeSend: function (xhr) {
            },
            success: function (data) {

                listeOptions = data;
                console.log("listeOptions");
                console.log(listeOptions);

            },
            error: function () {
                alert('La requête n\'a pas abouti');
            }
        });
    }

    function getListeOptionsAjax() {
        $.ajax({
            type: 'GET',
            url: "/listeGaranties",
            timeout: 3000,
            beforeSend: function (xhr) {
            },
            success: function (data) {

                listeGaranties = data;
                console.log("listeGaranties");
                console.log(listeGaranties);

            },
            error: function () {
                alert('La requête n\'a pas abouti');
            }
        });
    }

    function reserverAjax() { //envoi donnée à controller par ajax
        clientID = parseInt(clientID.innerText);

        console.log("data : " +
            clientID,
            agenceDepartSelected,
            agenceRetourSelected,
            datetimeDepartValue,
            datetimeRetourValue,
            conducteur,
            detailsVehicule.immatriculation,
            lieuSejourValue,
            idSiege,
            idGarantie
        );

        $.ajax({
            type: 'GET',
            url: '/reservation/newReservationWeb',
            data: {
                'clientID': clientID,
                'agenceDepart': agenceDepartSelected,
                'agenceRetour': agenceRetourSelected,
                'dateTimeDepart': datetimeDepartValue,
                'dateTimeRetour': datetimeRetourValue,
                'conducteur': conducteur,
                'idSiege': idSiege,
                'idGarantie': idGarantie,
                'vehiculeIM': detailsVehicule.immatriculation,
                'lieuSejour': lieuSejourValue
            },
            timeout: 3000,
            beforeSend: function (xhr) {
                // Show the loader
                $('#smartwizard').smartWizard("loader", "show");
            },
            success: function (xmlHttp) {
                // xmlHttp is a XMLHttpRquest object
                console.log('met le izy');
                // console.log('mety ilay izy zao');
                window.document.location = '/client/reservations';

                $('#smartwizard').smartWizard("loader", "hide");
            },
            error: function () {
                alert('La requête n\'a pas abouti');
            }
        });

    }

    //--------------------------liste des fonctions exectuées dans chaque step--------------------

    function tachesStep1toStep2() {

        getValuesStep1(); //recupère les choix faites dans étapes 1
        getListeOptionsAjax(); //from database
        getListeGarantiesAjax(); //from database
        setValuesRecapitulatif();

        if (vehiculeSelected != null && agenceDepartSelected != "Choisir" && agenceRetourSelected != "Choisir" && lieuSejourValue != "") {

            retrieveTarifsAjax(vehiculeSelected);
            retrieveVehiculeAjax(vehiculeSelected); //in success status include setValues 2,3,4

            ;
            return true;

        } else {
            alert("Veuillez remplir tous les champs");
            return false;
        }
    }

    function tachesStep2toStep3() {

        if (tarifs != null) {

            if (dureeReservation <= 3) tarifApplique = tarifs.troisJours;

            if (dureeReservation > 3 && dureeReservation <= 7) tarifApplique = tarifs.septJours;

            if (dureeReservation > 7 && dureeReservation <= 15) tarifApplique = tarifs.quinzeJours;

            if (dureeReservation > 15 && dureeReservation <= 30) tarifApplique = tarifs.trenteJours;

            for (let i = 0; i < prixSpanElem.length; i++) {
                prixSpanElem[i].innerText = tarifApplique;

            }
        }
        console.log(dureeReservation, tarifApplique);
    }

    function tachesStep3toStep4() {
        getValuesStep3();
        setValuesOptionGarantie();
        return true;
    }

    //configuration du plugin smartWizard 

    function initSmartWizard() {
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
    }

    //--------------------------------------fonctions utilitaires -------------------------------
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

    function nullifyForm() {
        nomClientValue = "";
        prenomClientValue = "";
        emailClientValue = "";
        telephoneClientValue = "";
        nomClientElem.innerText = "";
        prenomClientElem.innerText = "";
        emailClientElem.innerText = "";
        telephoneClientElem.innerText = "";

        document.getElementById('formCreateClient').style.display = 'none';
    }
    function initDetailsVehicule(data) {

        detailsVehicule = data;

    }

    function calculPrixTotal() {
        var total = tarifApplique + siege.prix + garantie.prix;
        return total;
    }

    function closeAlert() {
        $('#btnCloseAlert').trigger('click');
    }
});
