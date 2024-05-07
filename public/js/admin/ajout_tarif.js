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


    var numMois = this.parentElement.parentElement.previousElementSibling.innerText;
    var vehicule = this.parentElement.parentElement.parentElement.firstElementChild.innerText;

    modele = vehicule.substring(vehicule.indexOf(' ') + 1);
    marque = vehicule.split(' ')[0];
    month = getMonth(numMois);

    createInputs(this);
    disableButtons();

}


function createInputs(clickedBtn) {

    thisElement = clickedBtn.parentElement;
    th = clickedBtn.parentElement.parentElement;

    div1 = document.createElement('div');
    div1.classList.add('form-group');
    div1.classList.add('text-center');

    div2 = document.createElement('div');
    div2.classList.add('form-group');
    div2.classList.add('text-center');

    div3 = document.createElement('div');
    div3.classList.add('form-group');
    div3.classList.add('text-center');

    div4 = document.createElement('div');
    div4.classList.add('form-group');
    div4.classList.add('text-center');

    divBtn = document.createElement('div');
    divBtn.classList.add('form-group');
    divBtn.classList.add('text-center');

    lbl1 = document.createElement('label');
    lbl2 = document.createElement('label');
    lbl3 = document.createElement('label');
    lbl4 = document.createElement('label');
    lblBtn = document.createElement('label');

    lbl1.innerText = '3j';
    lbl2.innerText = '7j';
    lbl3.innerText = '15j';
    lbl4.innerText = '30j';
    lblBtn.innerText = "Action";

    input1 = document.createElement('input');
    input2 = document.createElement('input');
    input3 = document.createElement('input');
    input4 = document.createElement('input');
    buttonOK = document.createElement('button');

    input1.style.width = '3em';
    input1.style.height = '30px';
    input1.classList.add('troisJours');
    input1.classList.add('form-control');

    input2.style.width = '3em';
    input2.style.height = '30px';
    input2.classList.add('septJours');
    input2.classList.add('form-control');

    input3.style.width = '3em';
    input3.style.height = '30px';
    input3.classList.add('quinzeJours');
    input3.classList.add('form-control');

    input4.style.width = '3em';
    input4.style.height = '30px';
    input4.classList.add('trenteJours');
    input4.classList.add('form-control');

    buttonOK.classList.add('btn');
    buttonOK.classList.add('btn-danger');
    buttonOK.innerText = 'Ok';
    buttonOK.addEventListener('click', traitementInputs, false);

    div1.appendChild(lbl1);
    div1.appendChild(input1);

    div2.appendChild(lbl2);
    div2.appendChild(input2);

    div3.appendChild(lbl3);
    div3.appendChild(input3);

    div4.appendChild(lbl4);
    div4.appendChild(input4);

    divBtn.appendChild(lblBtn);
    divBtn.appendChild(buttonOK);

    td1 = document.createElement('td');
    td2 = document.createElement('td');
    td3 = document.createElement('td');
    td4 = document.createElement('td');
    td5 = document.createElement('td');

    td1.appendChild(div1);
    td2.appendChild(div2);
    td3.appendChild(div3);
    td4.appendChild(div4);
    td5.appendChild(divBtn);


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
    troisJours = document.querySelector("input[class='troisJours form-control']").value;
    septJours = document.querySelector("input[class='septJours form-control']").value;
    quinzeJours = document.querySelector("input[class='quinzeJours form-control']").value;
    trenteJours = document.querySelector("input[class='trenteJours form-control']").value;
    saveInputs();
}

function saveInputs() {

    $.ajax({
        type: 'GET',
        url: '/backoffice/tarif/newTarif',
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
            window.location.href = '/backoffice/tarifs';
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

