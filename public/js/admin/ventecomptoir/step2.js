var content;
var radioElements;


getElements();
addEventListener();

function getElements() {
    // content = document.getElementById('input_tarif');
    radioElements = document.querySelectorAll("input[type='radio']");
    console.log(radioElements);
}

function addEventListener() {
    for (let i = 0; i < radioElements.length; i++) {
        radioElements[i].addEventListener('click', addInput, false);
    }
}


function addInput() {
    inputTarifElements = [];
    var inputTarifElements = document.querySelectorAll("div[class='tarifVehicule']");
    if (inputTarifElements != 0) {
        for (let i = 0; i < inputTarifElements.length; i++) {
            inputTarifElements[i].remove();
        }
    }

    var div = document.createElement('div');
    div.classList.add('tarifVehicule');
    var label = document.createElement('label');
    label.innerText = "Autre tarif : ";
    var input = document.createElement('input');
    input.type = 'text';
    input.name = 'tarifVehicule';
    input.classList.add("form-control");
    input.classList.add("inputTarif");

    div.appendChild(label);
    div.appendChild(input);

    this.parentElement.insertBefore(div, this.parentElement.lastElementChild);
    // this.parentElement.lastElementChild.appendChild(input);


}
