$(document).ready(function () {

    var content;
    var radioElements;


    getElements();
    addEventListener();

    function getElements() {
        // content = document.getElementById('input_tarif');
        radioElements = document.querySelectorAll("input[type='radio']");
    }

    function addEventListener() {
        for (let i = 0; i < radioElements.length; i++) {
            radioElements[i].addEventListener('click', addInput, false);
        }
    }


    function addInput() {
        // Remove existing tarif elements
        document.querySelectorAll(".tarifVehicule").forEach(el => el.remove());

        // Create new tarif element
        const tarifHtml = `
            <div class="tarifVehicule">
                <label>Autre tarif total : </label>
                <input type="text" name="tarifVehicule" class="form-control inputTarif">
            </div>

            <div class="tarifVehicule">
                <label>Autre tarif journalier : </label>
                <input type="text" name="tarifVehiculeJournalier" class="form-control inputTarif">
            </div>
        `;

        // Insert the new element
        this.parentElement.insertAdjacentHTML('beforeend', tarifHtml);
    }
});