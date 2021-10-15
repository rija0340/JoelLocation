


$(document).ready(function () {

    var btnEnregistrer;

    function getElements() {
        btnEnregistrer = document.querySelector("button[id='enregistrer']");
        console.log(btnEnregistrer);
    }

    function addEventListener() {
        btnEnregistrer.addEventListener('click', test, false);
    }

    function test(e) {
        console.log('test');
        // e.preventDefault();
    }

    $(".callController").on('click', function (e) {

        e.preventDefault();

        //recuperaton de l'element cliqué (unique car chacun a son href)
        var $a = $(this);

        //recuperation valeur  data-target
        var url = $a.attr('href');

        //recuperer classe sans "."
        // var id = url.slice(1, url.length);

        // var include = "{% include 'pages/tarifs/tarif_visite/"+ id +".html.twig' %}" ;

        // console.log(include);

        // var url = '/' + id;
        // var url = '/tarif_consta';
        //ceci permet de recuperer un element html et le met dans le div main-wrapper de la page fille
        //bouton clické -> ce code ci dessous active url dans controle et recupere element html dans render
        $('.modal-body').load(url, function () {
            $('#modal').trigger('click');
            // getElements();
            // addEventListener();
            getVehicules();
        });

    });



});




function getVehicules() {
    var dateDebutElem;
    var dateDebutValue;
    var dateFinElem;
    var dateFinValue;
    var dateDebutToPhp;
    var dateFintToPhp;
    var dateFintToPhp;
    var btnRechercherElem;
    var reservation2Elem;


    getElements();
    addEventListener()


    function getElements() {
        dateDebutElem = document.getElementById("stop_sales_date_debut");
        dateFinElem = document.getElementById("stop_sales_date_fin");
        // btnRechercherElem = document.getElementById("rechercherVehicules");
        reservation2Elem = document.getElementById("reservation2");
    }

    function addEventListener() {
        dateDebutElem.addEventListener('change', getDateDebutValue, false);
        dateFinElem.addEventListener('change', getDateFinValue, false);
        // btnRechercherElem.addEventListener('click', getDatesValues, false);
    }

    function getDatesValues() {
        console.log(document.getElementById("reservation_date_debut").value);
        console.log(document.getElementById("reservation_date_fin").value);
        retrieveDataAjax();
    }

    function getDateDebutValue() {
        dateDebutValue = this.value;
        console.log('ity ilay date ' + dateDebutValue);

        if (dateFinValue != null) {


            if (dateToTimestamp(dateDebutValue) > dateToTimestamp(dateFinValue)) {
                $("#selectVehicule").empty();
                alert("La date de fin doit être supérieure à la date de début");
                dateDebutElem.value = null;
                dateFinElem.value = null;
                dateDebutValue = null;
                dateFinValue = null;
            } else {

                retrieveDataAjax();
            }

        }
    }

    function getDateFinValue() {

        dateFinValue = this.value;
        if ((dateFinValue = this.value) != null && dateDebutValue != null) {

            if (dateToTimestamp(dateFinValue) > dateToTimestamp(dateDebutValue)) {

                retrieveDataAjax();

            } else {

                $("#selectVehicule").empty();
                alert("La date de fin doit être supérieure à la date de début");
                dateDebutElem.value = null;
                dateFinElem.value = null;
                dateDebutValue = null;
                dateFinValue = null;

            }

        } else {
            dateFinElem.value = null;
            dateFinValue = null;

            alert("Veuillez entrer en premier la date de début");
        }
    }


    function retrieveDataAjax() {
        // var d = new Date(dateInputValue);
        // var n = d.toString();
        $.ajax({
            type: 'GET',
            url: '/reservation/liste-vehicules-disponibles',
            data: {
                'dateDepart': dateDebutValue, 'dateRetour': dateFinValue
            },
            Type: "json",
            success: function (data) {
                console.log(data);
                populateSelectElem(data);
                dataForSelect(data);
            },
            error: function (erreur) {
                // alert('La requête n\'a pas abouti' + erreur);
                console.log(erreur.responseText);
            }
        });
    }


    function populateSelectElem(options) {

        var select = document.getElementById('selectVehicule');
        console.log(select);
        $("#selectVehicule").empty(); //remove old options jquery

        for (var i = 0; i < options.length; i++) {

            var opt = options[i];
            console.log('ity ny opt ' + opt.marque);
            var el = document.createElement("option");
            el.text = opt.marque + ' ' + opt.modele + ' ' + opt.immatriculation;
            el.value = opt.id;
            select.add(el);

        }
    }

    function dataForSelect(data) {
        var data2 = [];

        for (let i = 0; i < data.length; i++) {

            data2.push({
                id: data[i].id,
                marque: data[i].marque,
                modele: data[i].modele,
                immatriculation: data[i].immatriculation
            });

        }
        console.log(data2);
        return data2;
    }

    function dateToTimestamp(date) {

        return new Date(date).getTime();

    }
}
