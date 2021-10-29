var btnValiderElem;
var radioModePaiementElem;
var checkboxConditionElem;
var checkedCondition = false;
var checkedModePaiement = false;

getElements();
addEvent();

function getElements() {
    btnValiderElem = document.getElementById("validerPaiement");
    checkboxConditionElem = document.getElementById("conditionGeneralVente");
    radioModePaiementElem = document.querySelectorAll("input[name='modePaiement']");
}

function addEvent() {
    btnValiderElem.addEventListener('click', valider, false);

    for (let i = 0; i < radioModePaiementElem.length; i++) {
        radioModePaiementElem[i].addEventListener('click', addTextButton, false);
    }
}

function addTextButton() {
    // btnValiderElem.innerText = "Valider mon paiement de " + this.realValue + "€";
}

function valider(e) {
    for (let i = 0; i < radioModePaiementElem.length; i++) {
        if (radioModePaiementElem[i].type == 'radio' && radioModePaiementElem[i].checked) {
            checkedModePaiement = true;
        }
    }
    if (checkboxConditionElem.checked) {
        checkedCondition = true;
    }
    if (checkedModePaiement && checkedCondition) {
    } else {
        if (!checkedModePaiement && !checkedCondition) {
            $.alert({
                title: 'Erreur',
                icon: 'fa fa-warning',
                type: 'red',
                content: '<p>"Veuillez choisir un mode de paiement et accepter les conditions générales de vente"</p>',
            });
            e.preventDefault();
        }
        if (!checkedCondition && checkedModePaiement) {
            $.alert({
                title: 'Erreur',
                icon: 'fa fa-warning',
                type: 'red',
                content: '<p>"Veuillez accepter les conditions générales de vente"</p>',
            });
            e.preventDefault();
        }

        if (!checkedModePaiement && checkedCondition) {
            $.alert({
                title: 'Erreur',
                icon: 'fa fa-warning',
                type: 'red',
                content: '<p>"Veuillez choisir un mode de paiement"</p>',
            });
            e.preventDefault();

        }
    }
}