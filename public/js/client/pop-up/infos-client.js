// processus
// get the element and add to its class the class popup
// add addEventListener to the element and create span and show the popup following the event

var btnEditInfosClient;
var btnTelechargerDevis;

getElements();
addEventListener();

function getElements() {
    //btn modifier information client dans accueil espace client
    btnEditInfosClient = document.getElementById('editInfosClient');
    //telecharger devis dans section reservation > devis
    btnTelechargerDevis = document.querySelectorAll('a[class="telechargerDevis"]');

}

function addEventListener() {
    //btn modifier informations client
    btnEditInfosClient.addEventListener("mouseover", showPopUp, false);
    btnEditInfosClient.addEventListener("mouseout", hidePopUp, false);

    //telecharger devis dans section reservation > devis
    for (let i = 0; i < btnTelechargerDevis.length; i++) {
        btnTelechargerDevis[i].addEventListener("mouseover", showPopUp, false);
        btnTelechargerDevis[i].addEventListener("mouseout", hidePopUp, false);
    }

}

function showPopUp() {
    var element = this; //get l'element declencheur de l'evenement
    element.classList.add("popup");
    var spanElem = createSpan(element);
    element.appendChild(spanElem);
    spanElem.classList.toggle('show');

}
function hidePopUp() {
    //un span a été ajouté à l'élément, on mouseout la class show du span est remové
    this.lastElementChild.classList.toggle('show');
}

function createSpan(element) {
    var span = document.createElement('span');
    if (element.id == "editInfosClient") {
        span.innerText = "Modifier";
    }
    if (element.id == "telechargerDevis") {
        span.innerText = "Télécharger le devis";
    }
    span.classList.add("popuptext");
    return span;
}