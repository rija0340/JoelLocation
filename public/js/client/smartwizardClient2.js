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

    var tarifVehicule;
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

    //for step2a.html.twig
    var radioVehiculeElem;
    var radioVehiculeValue;

    // prix location elem
    var prixTotalSpanElem;
    var prixJournalierSpanElem;

    // var detailsVehicule from ajax; 
    let detailsVehicule;

    var dureeReservation;
    // get elem step 3
    var conducteur;
    var siege = Array();
    var garantie;
    var arrayGarantiesID = Array();
    var arrayOptionsID = Array();
    var radioConducteur;
    var checkboxGarantiesElem;
    var checkboxOptionsElem;

    // get elem step 4
    var conducteurSpanElem;
    var siegeSpanElem;
    var garantieSpanElem;
    var selectedClient;
    var btnEnregistrerDevis;
    var selectClientElem;
    var alertCreatedClient;

    var listeOptions;
    var listeGaranties;

    var btnPdfDevis;

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

        console.log(checkboxOptionsElem);
        console.log(checkboxGarantiesElem);

        //recuperer les checkboxes selectionné 
        arrayOptionsID = [];
        for (var i = 0; i < checkboxOptionsElem.length; i++) {
            if (checkboxOptionsElem[i].type == 'checkbox' && checkboxOptionsElem[i].checked) {
                arrayOptionsID.push(checkboxOptionsElem[i].value);
            }
        }

        // for (let i = 0; i < listeOptions.length; i++) {
        //     if (idSiege == listeOptions[i].id) {
        //         siege = listeOptions[i];
        //     }

        // }
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
        arrayGarantiesID = [];
        for (var i = 0; i < checkboxGarantiesElem.length; i++) {
            if (checkboxGarantiesElem[i].type == 'checkbox' && checkboxGarantiesElem[i].checked) {
                arrayGarantiesID.push(checkboxGarantiesElem[i].value);
            }
        }
        // for (let i = 0; i < listeGaranties.length; i++) {
        //     if (idGarantie == listeGaranties[i].id) {
        //         garantie = listeGaranties[i];
        //     }

        // }
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

    function setValuesDetailsVehicule(vehicule) {
        // for vehicule details
        for (let i = 0; i < vehiculeImgElem.length; i++) {

            vehiculeImgElem[i].setAttribute("src", "/uploads/vehicules/" + vehicule.image + "");
        }
        // marqueSpanElem.innerText = detailsVehicule.marque;
        // modeleSpanElem.innerText = detailsVehicule.modele;
        // carburationSpanElem.innerText = detailsVehicule.carburation;
        // vitesseSpanElem.innerText = detailsVehicule.vitesse;
        // bagageSpanElem.innerText = detailsVehicule.bagages;
        // portesSpanElem.innerText = detailsVehicule.portes;
        // passagersSpanElem.innerText = detailsVehicule.passagers;
        // atoutsSpanElem.innerText = detailsVehicule.atouts;
        // cautionSpanElem.innerText = detailsVehicule.caution;
        // immatriculationSpanElem.innerText = detailsVehicule.immatriculation;
    }


    //à chaque passage d'un étape à un autre des fonctions sont exécutées selon current et next StepIndex
    $("#smartwizard").on("leaveStep", function (e, anchorObject, currentStepIndex, nextStepIndex, stepDirection) {

        if (currentStepIndex == 0 && nextStepIndex == 1) {
            return tachesStep1toStep2();
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
        checkboxOptionsElem = document.querySelectorAll('input[name="checkboxOptions"]');
        checkboxGarantiesElem = document.querySelectorAll('input[name="checkboxGaranties"]');


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
        prixTotalSpanElem = document.querySelectorAll('span[id="prixTotal"]');
        prixJournalierSpanElem = document.querySelectorAll('span[id="prixJournalier"]');

        conducteurSpanElem = document.querySelector('span[id="span_conducteur"]');
        siegeSpanElem = document.querySelector('span[id="span_siege"]');
        garantieSpanElem = document.querySelector('span[id="span_garantie"]');

        //ETAPE 4 
        selectClientElem = document.querySelector('input[id="selectClient"]');
        btnEnregistrerDevis = document.getElementById('enregistrerDevis');
        btnPdfDevis = document.getElementById('pdfDevis');


        //ETAPE 4 ->formulaire creation nouveau client
        nomClientElem = document.querySelector('input[id="nom"]');
        prenomClientElem = document.querySelector('input[id="prenom"]');
        emailClientElem = document.querySelector('input[id="email"]');
        telephoneClientElem = document.querySelector('input[id="telephone"]');
        alertCreatedClient = document.getElementById('alertCreatedClient');

    }
    function addEvent() {

        btnEnregistrerDevis.addEventListener('click', enregistrerDevisAjax, false);

    }

    //--------------------------liste des fonctions AJAX--------------------

    //requete vers VehiculeController
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
                // detailsVehicule = data;
                setValuesDetailsVehicule(data);
                $('#smartwizard').smartWizard("loader", "hide");
                // Hide the loader

            },
            error: function (erreur) {
                // alert('La requête n\'a pas abouti' + erreur);
                console.log(erreur.responseText);
            }
        });
    }

    function retrieveTarifsAjax(dateDepart, dateRetour, vehicule) {
        // var d = new Date(dateInputValue);
        // var n = d.toString();
        $.ajax({
            type: 'GET',
            url: '/tarifVenteComptoir',
            data: {
                "vehicule_id": vehicule,
                // "mois": getMonth(datetimeDepartValue)
                "dateDepart": dateDepart,
                "dateRetour": dateRetour
            },
            beforeSend: function (xhr) {
            },
            Type: "json",
            success: function (data) {
                console.log(data);
                console.log(getMonth(datetimeDepartValue));
                console.log(vehicule);
                tarifVehicule = data.tarif;

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
            url: "/espaceclient/listeOptions",
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

    function enregistrerDevisAjax() { //envoi donnée à controller par ajax


        console.log("data : " +
            agenceDepartSelected,
            agenceRetourSelected,
            datetimeDepartValue,
            datetimeRetourValue,
            conducteur,
            detailsVehicule.immatriculation,
            lieuSejourValue,
            arrayOptionsID,
            arrayGarantiesID
        );

        $.ajax({
            type: 'GET',
            url: '/espaceclient/enregistrerDevisWizard',
            data: {
                'agenceDepart': agenceDepartSelected,
                'agenceRetour': agenceRetourSelected,
                'dateTimeDepart': datetimeDepartValue,
                'dateTimeRetour': datetimeRetourValue,
                'conducteur': conducteur,
                'arrayOptionsID': arrayOptionsID,
                'arrayGarantiesID': arrayGarantiesID,
                'vehiculeIM': detailsVehicule.immatriculation,
                'lieuSejour': lieuSejourValue
            },
            timeout: 3000,
            beforeSend: function (xhr) {
                // Show the loader
                $('#smartwizard').smartWizard("loader", "show");
            },
            success: function (data) {

                window.document.location = '/espaceclient/reservations';

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
        var completed;
        if (vehiculeSelected != null && agenceDepartSelected != "Choisir" && agenceRetourSelected != "Choisir" && lieuSejourValue != "") {

            retrieveTarifsAjax(datetimeDepartValue, datetimeRetourValue, vehiculeSelected);

            ;
            completed = true;

        } else {
            alert("Veuillez remplir tous les champs");
            completed = false;
        }
        //add liste véhicule sans immatriculation
        addListVehicules();

        return completed;
    }

    function tachesStep2toStep3() {

        for (let i = 0; i < prixTotalSpanElem.length; i++) {
            prixTotalSpanElem[i].innerText = tarifVehicule;

        }

        for (let i = 0; i < prixJournalierSpanElem.length; i++) {
            prixJournalierSpanElem[i].innerText = tarifVehicule;

        }

        //for step2a

        radioVehiculeElem = document.querySelectorAll("input[name='radio-vehicule']");

        for (var i = 0; i < radioVehiculeElem.length; i++) {
            if (radioVehiculeElem[i].type == 'radio' && radioVehiculeElem[i].checked) {
                radioVehiculeValue = radioVehiculeElem[i].value;
            }
        }

        console.log(" id véhicule: " + radioVehiculeValue);

        // retrieve et fonction setValuesDetailsVehicule dans ajax
        retrieveVehiculeAjax(radioVehiculeValue);

        //fin step2a
        console.log(dureeReservation, tarifVehicule);

        //condition sur véhicule
        if (radioVehiculeValue != null) {

            return true;
        } else {
            return false
        }
    }

    function tachesStep3toStep4() {
        getValuesStep3();
        console.log(arrayOptionsID);
        console.log(arrayGarantiesID);

        var listeOptionsElem = document.getElementById('listeOptions');
        var listeGarantiesElem = document.getElementById('listeGaranties');

        //afficher les options séléctionés dans steps 3 dans step 4
        for (let i = 0; i < arrayOptionsID.length; i++) {
            console.log("ity ve : " + arrayOptionsID[i]);
            for (let j = 0; j < listeOptions.length; j++) {
                if (listeOptions[j].id == arrayOptionsID[i]) {
                    var p = document.createElement('p');
                    p.innerText = listeOptions[j].appelation + " : " + listeOptions[j].prix + "€";
                    listeOptionsElem.appendChild(p);
                }
            }


        }

        //afficher les garanties séléctionés dans steps 3 dans step 4

        for (let i = 0; i < arrayGarantiesID.length; i++) {
            for (let j = 0; j < listeGaranties.length; j++) {
                if (listeGaranties[j].id == arrayGarantiesID[i]) {
                    var p = document.createElement('p');
                    p.innerText = listeGaranties[j].appelation + " : " + listeGaranties[j].prix + "€";
                    listeGarantiesElem.appendChild(p);
                }
            }

        }


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
                previous: 'Précédent'
            },
            // default, arrows, , progress
            // darkMode: true,
            transition: {
                animation: 'slide-horizontal', // Effect on navigation, none/fade/slide-horizontal/slide-vertical/slide-swing
            },
            toolbarSettings: {
                toolbarPosition: 'both', // both bottom

            },
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


    //test boucle affichage véhicules
    function addListVehicules() {

        for (let i = 0; i < getListVehiculeDispo().length; i++) {
            var div = document.createElement('div');
            div.innerHTML = `
            <div class="card mb-2" >
            <div class="card-body">
		<div class="row">
			<div class="col-md-5"><img src="/uploads/vehicules/${getListVehiculeDispo()[i].image}" style="width:70%;" id="vehicule_photo"></div>
			<div class="col-md-7">
				<div class="row">
					<div class="col-md-6">
						<div class="caracteristiques">
							<ul>

								<li>
									<i class="fa fa-battery-full"></i>
									Marque :
									<span id="vehicule_marque">${getListVehiculeDispo()[i].marque}</span>
								</li>
								<li>
									<i class="fa fa-battery-full"></i>
									Modele :
									<span id="vehicule_modele">${getListVehiculeDispo()[i].modele}</span>
								</li>
					
								<li>
									<i class="fa fa-battery-full"></i>
									Carburant :
									<span id="vehicule_carburation">${getListVehiculeDispo()[i].carburation}</span>
								</li>
								<li>
									<i class="fa fa-sun-o" aria-hidden="true"></i>
									Boite de vitesse :
									<span id="vehicule_vitesse">${getListVehiculeDispo()[i].vitesse}</span>
								</li>

							</ul>
						</div>
					</div>
					<div class="col-md-6">
						<div class="caracteristiques">
							<ul>
								<li>
									<i class="fa fa-shopping-bag" aria-hidden="true"></i>
									Nombre max de bagages :
									<span id="vehicule_bagage">${getListVehiculeDispo()[i].bagages}</span>
								</li>
								<li>
									<i class="fa fa-car" aria-hidden="true"></i>
									Nombre de portes :
									<span id="vehicule_portes">${getListVehiculeDispo()[i].portes}</span>
								</li>
								<li>
									<i class="fa fa-users" aria-hidden="true"></i>
									Max passagers:
									<span id="vehicule_passagers">${getListVehiculeDispo()[i].passagers}</span>
								</li>
								<li>
									<i class="fa fa-rss" aria-hidden="true"></i>
									Atouts :
									<span id="vehicule_atouts">${getListVehiculeDispo()[i].atouts}</span>
								</li>
							</ul>
						</div>
                        <div>
                        <p>Tarif comptoir</p>
                        <span class="text-danger">${getListVehiculeDispo()[i].tarif}</span> EUR (soit ${getListVehiculeDispo()[i].tarifJour} EUR / jour)
                        <div>
                        <label>Réserver</label>
                            <input type="radio" checked="checked" name="radio-vehicule" value="${getListVehiculeDispo()[i].id}"> 
                        </div>
                        </div>
					</div>
				</div>
			</div>
		</div>
		</div>
	</div>`
            var container = document.getElementById('listeVehiculesDispo');
            container.appendChild(div);
        }
    }
});
