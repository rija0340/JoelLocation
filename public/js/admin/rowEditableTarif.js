var btnAjouter = [];
var month;
var modele;
var marque;
var troisJours;
var septJours;
var quinzeJours;
var trenteJours;


getElements();
addEventListener();

function getElements() {

    btnAjouter = document.querySelectorAll("button[id='ajouterTarif']");

}

function addEventListener() {
    for (let i = 0; i < btnAjouter.length; i++) {
        btnAjouter[i].addEventListener('click', getData, false);
    }

}

function getData() {


    var numMois = this.parentElement.previousElementSibling.innerText;
    var vehicule = this.parentElement.parentElement.firstElementChild.innerText;

    modele = vehicule.substring(vehicule.indexOf(' ') + 1);
    marque = vehicule.split(' ')[0];
    month = getMonth(numMois);

    console.log(marque);
    console.log(modele);
    console.log(month);



    createInputs(this);
    disableButtons();


}


function createInputs(clickedBtn) {

    thisElement = clickedBtn.parentElement;
    th = clickedBtn.parentElement.parentElement;

    td1 = document.createElement('td');
    td2 = document.createElement('td');
    td3 = document.createElement('td');
    td4 = document.createElement('td');
    td5 = document.createElement('td');


    input1 = document.createElement('input');
    input2 = document.createElement('input');
    input3 = document.createElement('input');
    input4 = document.createElement('input');
    buttonOK = document.createElement('button');

    input1.style.width = '3em';
    input1.style.height = '30px';
    input1.classList.add('troisJours');

    input2.style.width = '3em';
    input2.style.height = '30px';
    input2.classList.add('septJours');


    input3.style.width = '3em';
    input3.style.height = '30px';
    input3.classList.add('quinzeJours');


    input4.style.width = '3em';
    input4.style.height = '30px';
    input4.classList.add('trenteJours');


    buttonOK.classList.add('btn');
    buttonOK.classList.add('btn-danger');
    buttonOK.innerText = 'Ok';
    buttonOK.addEventListener('click', traitementInputs, false);

    td1.appendChild(input1);
    td2.appendChild(input2);
    td3.appendChild(input3);
    td4.appendChild(input4);
    td5.appendChild(buttonOK);

    th.insertBefore(td1, thisElement);
    th.insertBefore(td2, thisElement);
    th.insertBefore(td3, thisElement);
    th.insertBefore(td4, thisElement);
    th.insertBefore(td5, thisElement);

    thisElement.remove();
}

function getMonth(numMonth) {


    var month;
    switch (parseInt(numMonth)) {
        case 1:
            month = 'Janvier';
            break;
        case 2:
            month = 'Février';
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

function traitementInputs() {
    troisJours = document.querySelector("input[class='troisJours']").value;
    septJours = document.querySelector("input[class='septJours']").value;
    quinzeJours = document.querySelector("input[class='quinzeJours']").value;
    trenteJours = document.querySelector("input[class='trenteJours']").value;
    saveInputs();
}

function saveInputs() {

    $.ajax({
        type: 'GET',
        url: '/tarif/newTarif',
        data: {

            'marque': marque,
            'modele': modele,
            'mois': month,
            'troisJours': troisJours,
            'septJours': septJours,
            'quinzeJours': quinzeJours,
            'trenteJours': trenteJours

        },
        Type: "json",
        success: function (data) {
            window.location.href = '/tarifs';
        },
        error: function (erreur) {
            // alert('La requête n\'a pas abouti' + erreur);
            console.log(erreur.responseText);
        }
    });
}
function disableButtons() {
    var buttons = document.querySelectorAll("button[id='ajouterTarif']");

    for (let i = 0; i < buttons.length; i++) {
        buttons[i].disabled = true;
        buttons[i].classList.add('hide');
    }
}

