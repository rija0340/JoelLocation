var btnRechSimple;
var btnRechImm;
var inputRechSimple;
var motcleRechSimple;
var divResultRechSimple;
var tbodyRechSimple;
var RS_aucun;
var RS_tBodyChildLength = 0;
var RIM_tBodyChildLength = 0;
var RIM_selectVehiculeElem;
var RIM_dateElem;
var RIM_btnChercher;
var RIM_idVehicule;
var RIM_date;
var RIM_tbody;
var RIM_divResultRech;
var RIM_aucun;
var RA_btnRechercher;

// recherche avancee
var typeDateElem;
var typeDateValue;
var debutPeriodeValue;
var debutPeriodeElem;
var finPeriodeElem;
var finPeriodeValue;



getElements();
addEventListener();

function getElements() {
    btnRechSimple = document.getElementById('btnrechercheSimple');
    inputRechSimple = document.getElementById('rechercheSimple');
    divResultRechSimple = document.getElementById('resultRechSimple');
    tbodyRechSimple = document.getElementById('tbodyRechSimple');
    RS_aucun = document.getElementById('RS_aucun');
    RIM_tbody = document.getElementById('RIM_tbodyRech');
    RIM_selectVehiculeElem = document.getElementById('RIM_selectVehicule');
    RIM_dateElem = document.getElementById('RIM_date');
    RIM_divResultRech = document.getElementById('RIM_resultRech');
    RIM_btnChercher = document.getElementById('RIM_btnChercher');
    RIM_aucun = document.getElementById('RIM_aucun');
    // rechercha avance
    RA_btnRechercher = document.getElementById('RA_btnRechercher');
    typeDateElem = document.getElementById('typeDate');
    debutPeriodeElem = document.getElementById('debutPeriode');
    finPeriodeElem = document.getElementById('finPeriode');

}

function addEventListener() {
    btnRechSimple.addEventListener('click', rechercheSimple, false);
    RIM_btnChercher.addEventListener('click', rechercheIM, false)
    RA_btnRechercher.addEventListener('click', rechercheRA, false)
}

function getValues() {
    typeDateValue = typeDateElem.value;
    debutPeriodeValue = debutPeriodeElem.value;
    finPeriodeValue = finPeriodeElem.value;
}

//********************************** */
//        Recherche simple 
//********************************** */

function rechercheSimple(e) {
    e.preventDefault();
    motcleRechSimple = inputRechSimple.value;

    if (motcleRechSimple != "") {

        $.ajax({
            type: 'GET',
            url: '/recherchesimple',
            timeout: 3000,
            data: { 'recherche': motcleRechSimple },
            success: function (data) {

                if (data.length != 0) {

                    if (RS_tBodyChildLength != 0) {
                        clearTable(tbodyRechSimple, RS_tBodyChildLength);
                    }
                    affichageResultat(data, divResultRechSimple, tbodyRechSimple);
                    //mettre à jour nombre de childNodes de tbody
                    RS_tBodyChildLength = tbodyRechSimple.childNodes.length;

                    //verifier si affichage aucun est bien désactivé
                    if (RS_aucun.classList.contains('noHide')) {
                        RS_aucun.classList.replace('noHide', 'hide');
                    }

                } else {

                    if (divResultRechSimple.classList.contains('noHide')) {
                        divResultRechSimple.classList.replace('noHide', 'hide');
                    }

                    //verifier si affichage aucun est bien désactivé
                    if (RS_aucun.classList.contains('hide')) {
                        RS_aucun.classList.replace('hide', 'noHide');
                    }

                }
            },
            error: function () {
                alert('La requête n\'a pas abouti');
            }
        });
    } else {

        alert("Veuillez remplir le champ");
    }

}

//************************************************** */
//       Generer resulat (tableau) 
//************************************************** */

function generateTable(data, tbody) {

    //insersetion de données dans la table
    for (let i = 0; i < data.length; i++) {

        var tr = document.createElement('tr');

        var tdPaiement = document.createElement('td');
        var tdDateResa = document.createElement('td');
        var tdClient = document.createElement('td');
        var tdCodeResaVeh = document.createElement('td');
        var tdDatesDuree = document.createElement('td');
        var tdPrix = document.createElement('td');
        var tdAction = document.createElement('td');

        tr.appendChild(tdPaiement);
        tr.appendChild(tdDateResa);
        tr.appendChild(tdClient);
        tr.appendChild(tdCodeResaVeh);
        tr.appendChild(tdDatesDuree);
        tr.appendChild(tdPrix);
        tr.appendChild(tdAction);


        tdPaiement.innerHTML = `<div class="badge badge-success p-2">FUL</div>`;
        tdDateResa.innerText = data[i].dateResa;
        tdClient.innerText = data[i].nomPrenomClient + ' ' + data[i].mailClient;
        tdCodeResaVeh.innerText = data[i].codeResa + " " + data[i].vehicule;
        tdDatesDuree.innerText = data[i].dateDepart + '-' + data[i].dureeResa + "jours";
        tdPrix.innerText = data[i].prix + " €";

        if (data[i].status == 1) {

            tdAction.innerHTML = `<a href="/reservation/contrats_en_cours/${data[i].id}"><i class=" fa fa-info-circle" style="font-size: 2em !important;"></i></a>`;
        } else {
            tdAction.innerHTML = `<a href="/reservation/contrat_termine/${data[i].id}"><i class=" fa fa-info-circle" style="font-size: 2em !important;"></i></a>`;

        }

        tbody.appendChild(tr);

    }

}


//*****************************************/
//        Recherche par immatriculation 
//*****************************************/
function rechercheIM(e) {

    e.preventDefault();

    RIM_idVehicule = RIM_selectVehiculeElem.value;
    RIM_date = RIM_dateElem.value;

    $.ajax({
        type: 'GET',
        url: '/rechercheimmatriculation',
        timeout: 3000,
        data: { 'idVehicule': RIM_idVehicule, 'date': RIM_date },
        success: function (data) {

            if (data.length != 0) {
                //effacer les données du tableau avant affichage recherche s'il y en a.
                if (RIM_tBodyChildLength != 0) {
                    clearTable(RIM_tbody, RIM_tBodyChildLength);
                }
                affichageResultat(data, RIM_divResultRech, RIM_tbody)
                //mettre à jour nombre de childNodes de tbody
                RIM_tBodyChildLength = RIM_tbody.childNodes.length;

                //verifier si affichage aucun est bien désactivé
                if (RIM_aucun.classList.contains('noHide')) {
                    RIM_aucun.classList.replace('noHide', 'hide');
                }

            } else {

                if (RIM_divResultRech.classList.contains('noHide')) {
                    RIM_divResultRech.classList.replace('noHide', 'hide');
                }

                if (RIM_aucun.classList.contains('hide')) {
                    RIM_aucun.classList.replace('hide', 'noHide');
                }

            }
        },
        error: function () {
            alert('La requête n\'a pas abouti');
        }
    });

}




//*****************************************/
//        Effacer table
//*****************************************/

//effacer la table avant d'inserer de nouveau données
// à chaque boucle, le nombre de childNode diminue de '1 childNode' puisque on remove 1 à chaque fois. 
// s'il y a 2 childNodes -> premier boucle on remove childNode[0] et il reste un seul childNode qui devient à son tour numero 0
//donc on supprimant [0] à chaque fois on est sur de tout effacer à la fin du boucle for

function clearTable(tbody, tbodyLength) {

    for (let i = 0; i < tbodyLength; i++) {
        tbody.childNodes[0].remove();
    }
    tbodyLength = 0;
}

//*****************************************/
//        Affichage résultat recherche
//*****************************************/
function affichageResultat(data, divResult, tbody) {

    if (divResult.classList.contains('hide')) {

        generateTable(data, tbody);

        divResult.classList.replace('hide', 'noHide');
    } else {
        generateTable(data, tbody);

    }

}