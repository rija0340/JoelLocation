// processus
// get the element and add to its class the class popup
// add addEventListener to the element and create span and show the popup following the event

var btnEditConducteur;

getElements();
addEventListener();

function getElements() {
    btnEditConducteur = document.querySelectorAll('a[id="editConducteur"]');
}

function addEventListener() {

    for (let i = 0; i < btnEditConducteur.length; i++) {
        btnEditConducteur[i].addEventListener("mouseover", showPopUp, false);
        btnEditConducteur[i].addEventListener("mouseout", hidePopUp, false);
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

    if (element.id == "editConducteur") {
        span.innerText = "Modifier le conducteur";
    }
    span.classList.add("popuptext");
    return span;
}