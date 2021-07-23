$(function () {
    // Ici, le DOM est entièrement défini
    var vehiculeElem;
    var vehiculeValue;
    var selectMoisElem;

    getElements();
    getValues();
    addEventListener();

    window.onload = function () {
        vehiculeValue = vehiculeElem.value;
        getValueSelect();
    };


    function getElements() {
        vehiculeElem = document.getElementById("tarifs_vehicule");
        selectMoisElem = document.getElementById("selectMois");
    }

    function getValues() {
        console.log(vehiculeElem.value);
    }

    function addEventListener() {
        vehiculeElem.addEventListener('change', getValueSelect, false);
    }

    function getValueSelect() {
        console.log(vehiculeElem.value);
        getListeTarifs(vehiculeElem.value);
    }
    function getListeTarifs(vehiculeID) {
        // var d = new Date(dateInputValue);
        // var n = d.toString();
        $.ajax({
            type: 'GET',
            url: '/listeTarifsByVehicule',
            data: {
                "vehiculeID": vehiculeID,
            },
            // Type: "json",
            success: function (data) {
                console.log(data);
                populateSelectElem(data);

            },
            error: function (erreur) {
                // alert('La requête n\'a pas abouti' + erreur);
                console.log(erreur.responseText);
            }
        });
    }


    function populateSelectElem(options) {

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
