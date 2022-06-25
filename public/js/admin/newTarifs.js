//get valeur tarifs et mettre à jour automatiquement valeur modèle 
//get valeur tarifs et modèle et mettre à jour automatiquement liste mois sans valeur pour dans tarifs



$(function () {

    // Ici, le DOM est entièrement défini
    var selectMarqueElem;
    var marqueValue;
    var selectModeleElem;
    var modeleElem;
    var modeleValue;
    var vehiculeValue;
    var selectMoisElem;

    getElements();
    addEventListener();

    window.onload = function () {

        getElements();
        getMarqueValue();
        getModeleValue();
        console.log(marqueValue);

        if (marqueValue != "" && modeleValue != "") {
            getListeMoisSansValeur(marqueValue, modeleValue);
        }

        console.log(marqueValue);

    };

    function getElements() {
        selectMarqueElem = document.getElementById("tarifs_marque");
        selectMoisElem = document.getElementById("selectMois");
        selectModeleElem = document.getElementById("selectModele");
    }

    // function getValues() {
    //     console.log(marqueElem.value);
    // }

    function addEventListener() {
        selectMarqueElem.addEventListener('change', setModeleToSelect, false);
        selectModeleElem.addEventListener('change', getModeleValue, false);
    }

    function setModeleToSelect() {
        getMarqueValue();
        retrieveModeleAjax(marqueValue);
        getListeMoisSansValeur(marqueValue, modeleValue);
    }

    function getMarqueValue() {
        marqueValue = selectMarqueElem.value;

    }

    function getModeleValue() {
        modeleValue = selectModeleElem.value;
        getMarqueValue();
        if (marqueValue != "" && modeleValue != "") {

            getListeMoisSansValeur(marqueValue, modeleValue);
        }
    }


    function retrieveModeleAjax(marqueID) {
        $.ajax({
            type: 'GET',
            url: '/backoffice/modele/liste',
            data: {
                'marqueID': marqueID
            },
            Type: "json",
            success: function (data) {
                console.log(data);
                populateSelectNew(data, "selectModele");

            },
            error: function (erreur) {
                // alert('La requête n\'a pas abouti' + erreur);
                console.log(erreur.responseText);
            }
        });
    }


    function getListeMoisSansValeur(marqueID, modeleID) {

        console.log("marqueID: " + marqueID);
        console.log("modeleID: " + modeleID);

        $.ajax({
            type: 'GET',
            url: '/listeMoisSansValeur',
            data: {
                "marqueID": marqueID,
                "modeleID": modeleID
            },
            // Type: "json",
            success: function (data) {
                console.log(data);
                populateSelectMois(data);

            },
            error: function (erreur) {
                // alert('La requête n\'a pas abouti' + erreur);
                console.log(erreur.responseText);
            }
        });
    }


    function populateSelectMois(options) {


        $("#selectMois").empty(); //remove old options jquery

        for (var i = 0; i < options.length; i++) {

            var opt = options[i];
            console.log('ity ny opt ' + opt.marque);
            var el = document.createElement("option");
            el.text = opt;
            el.value = opt;
            selectMoisElem.add(el);

        }
    }


});
