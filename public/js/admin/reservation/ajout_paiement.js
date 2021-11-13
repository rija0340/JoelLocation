var btnAjouterPaiement;
var formAjoutPaiement;

getElements();
addEventListener();

function getElements() {
    btnAjouterPaiement = document.getElementById('btnAjouterPaiement');
    formAjoutPaiement = document.getElementById('formAjoutPaiement');
}

function addEventListener() {
    btnAjouterPaiement.addEventListener('click', addFormPaiement, false);
}

function addFormPaiement() {

    divRow = document.createElement('div');
    divModePaiement = document.createElement('div');
    divMontant = document.createElement('div');
    divAction = document.createElement('div');
    // ajout de class bootstrap
    divRow.classList.add("row");
    divRow.classList.add("mb-4");
    divModePaiement.classList.add("col-auto");
    divMontant.classList.add("col-auto");
    divAction.classList.add("col-auto");
    // creation des elements dans chaque div
    label = document.createElement('label');
    input = document.createElement('input');
    // ajout attribut dans les elements
    label.innerText = "Ajouter un autre paiement : ";
    input.classList.add("form-control");
    input.name = "montant";
    // a.classList.add("btn");
    btnSubmit = document.createElement('button');
    btnSubmit.type = "submit";
    btnSubmit.classList.add("btn");
    btnSubmit.classList.add("btn-danger");
    btnSubmit.innerText = "Valider"

    divModePaiement.appendChild(label);
    divMontant.appendChild(input);
    divAction.appendChild(btnSubmit);

    divRow.appendChild(divModePaiement);
    divRow.appendChild(divMontant);
    divRow.appendChild(divAction);
    formAjoutPaiement.appendChild(divRow);

}


