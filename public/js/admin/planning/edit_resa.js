
var modalButton = document.querySelector('button[data-target="#exampleModal"]');
var refInput = document.getElementById('reference');
var tarifResaInput = document.getElementById('tarif-resa');
var tarifOptionsGarantiesInput = document.getElementById('tarifs-options-garanties');
var dateDepartInput = document.getElementById('dateDepart');
var dateRetourInput = document.getElementById('dateRetour');
var hasCustomTarifInput = document.getElementById('has-custom-tarif');
var tarifVehiculeContainer = document.querySelector('.container-tarif-vehicule');
var customTarifContainer = document.querySelector('.container-custom-tarif');
var customTarifInput = document.getElementById('custom-tarif');
var tarifVehiculeInput = document.getElementById('tarif-vehicule');
var cancelButton = document.getElementById("cancelButton");


function editResa(task) {

    //deselectionner customtarif input
    hasCustomTarifInput.checked = false;
    //effacer value
    customTarifInput.value = '';

    //cacher custom par defaut 
    customTarifContainer.style.display = "none";
    tarifVehiculeContainer.style.display = "block";

    // Single event listener for both input fields
    dateDepartInput.addEventListener('change', function () {
        handleDateChange(task);
    }
    );
    dateRetourInput.addEventListener('change', function () {
        handleDateChange(task);
    }
    );
    customTarifInput.addEventListener('input', (event) => {
        //mettre a jour tarif resa 
        let value = event.target.value;
        value = value == "" ? 0 : value;
        let tarifresa = parseInt(value) + parseInt(tarifOptionsGarantiesInput.value);
        tarifResaInput.value = tarifresa;

    });

    //pour custom tarif checkbox
    addEventListenerHasCustomTarifCheckbox(task);
    onClickCancelButton();

    //add data to inputs
    addDataToModalForm(task);
    showModal();

    //set dynamically href value 
    addActionToForm(task);

}

function addActionToForm(task) {
    const protocol = location.protocol; // 'http:' or 'https:'
    const hostname = location.hostname; // 'localhost'
    const port = location.port; // '8000'

    const baseUrl = `${protocol}//${hostname}:${port}`;

    console.log("baseUrl");
    console.log(baseUrl);

    document.getElementById('form-task').setAttribute('action', `/backoffice/reservation/${task.id_r}/edit/`);
}

function addDataToModalForm(task) {
    console.log("task");
    console.log(task);
    refInput.value = task.reference;
    tarifResaInput.value = task.tarifResa;
    tarifVehiculeInput.value = task.tarifVehicule;
    tarifOptionsGarantiesInput.value = task.tarifOptionsGaranties;

    console.log("task.start_date");
    console.log(task.start_date);
    console.log("task.end_date");
    console.log(task.end_date);

    let dateDepart = convertDateToIsoDate(task.start_date);
    let dateRetour = convertDateToIsoDate(task.end_date);
    let refResa = task.reference;

    getVehiculeFromDates(refResa, dateDepart, dateRetour)
        .then(dataVehicule => {
            createOptions(dataVehicule, task);
        })
        .catch(error => {
            console.error('Error fetching available vehicles:', error);
        });

    var today = new Date();
    var formattedToday = today.toISOString().split('T')[0];
    dateDepartInput.value = dateDepart;
    dateDepartInput.setAttribute('min', formattedToday);
    dateRetourInput.value = dateRetour;
    dateRetourInput.setAttribute('min', formattedToday);
}

function addEventListenerHasCustomTarifCheckbox(task) {
    hasCustomTarifInput.addEventListener('change', () => {
        //switch input 
        if (hasCustomTarifInput.checked) {

            //display none
            tarifVehiculeContainer.style.display = 'none';
            customTarifContainer.style.display = 'block';
            // tarifBddInput.value = '';

            //mise a jour tarif resa  =  tarif options garanties seulement
            // tarifResaInput.value = tarifOptionsGarantiesInput.value;

            //mettre tarifresainput required
            customTarifInput.required = true;
        } else {
            //display block
            tarifVehiculeContainer.style.display = 'block';
            customTarifContainer.style.display = 'none';
            customTarifInput.value = '';
            //remettre la valeur du tarif resa 
            tarifResaInput.value = task.tarifResa;
            // document.getElementById('default-option').selected = true;
            customTarifInput.removeAttribute('required');
        }


    });
}

function handleDateChange(task) {

    setTarifBddToHtml("");

    const dateDepart = dateDepartInput.value;
    const dateRetour = dateRetourInput.value;
    const refResa = task.reference;

    getVehiculeFromDates(refResa, dateDepart, dateRetour)
        .then(dataVehicule => {
            createOptions(dataVehicule, task);

        })
        .catch(error => {
            console.error('Error fetching available vehicles:', error);
        });
}


function showModal() {
    modalButton.click();
}

function onClickCancelButton() {
    tarifVehiculeContainer.style.display = "block";
    customTarifContainer.style.display = "none";

    // Add click event listener to the cancel button
    cancelButton.addEventListener("click", function (e) { // Get the modal element
        e.preventDefault();
        var modal = document.getElementById("exampleModal");

        // Hide the modal
        $(modal).modal("hide");
    });
}

function updateTarifVehiculeAndResa(event, data, task) {
    const tarifVehiculeEl = document.getElementById('tarif-vehicule');
    const tarifResaEl = document.getElementById('tarif-resa');
    const inputCustomTarif = document.getElementById('custom-tarif');
    tarifVehiculeEl.innerHTML = '';
    tarifResaEl.innerHTML = '';
    inputCustomTarif.value = "";

    if (event.target.value != "") {
        let vehiculeObj = data.find(item => item.immatriculation === event.target.value)
        // setTarifBddToHtml(vehiculeObj.tarifBdd);
        // // somme tarif vehicule et options garanties 
        // tarifResaEl.value = vehiculeObj.tarifBdd + task.tarifOptionsGaranties;
    }
}


//create options from liste in data
function createOptions(data, task) {

    //select element html 
    const selectEl = document.getElementById('vehicule');

    // Clear the existing options
    selectEl.innerHTML = '';
    const vehicleArray = Object.values(data);

    // Create a default option
    const defaultOption = document.createElement('option');
    defaultOption.value = ''; // No value for the default option
    //add attr id to defaultoption
    defaultOption.setAttribute('id', 'default-option');
    defaultOption.textContent = 'Select a vehicle'; // You can change this text as per your requirement
    selectEl.appendChild(defaultOption);

    vehicleArray.forEach(option => {
        const optionElement = document.createElement('option');
        optionElement.value = option.immatriculation;
        optionElement.textContent = option.marque + " " + option.modele + " " + option.immatriculation;
        if (task.immatriculation === option.immatriculation) {
            optionElement.selected = true;
        }
        selectEl.appendChild(optionElement);

    });

    //ajout gestion evenement
    selectEl.addEventListener('change',
        (event) => {
            updateTarifVehiculeAndResa(event, data, task);
        }
    );
    //set valeur tafir du premier vehicule 
}

/**
 * Fetches available vehicles based on the provided departure and return dates.
 * @param {string} dateDepart - The departure date in the format 'YYYY-MM-DD'.
 * @param {string} dateRetour - The return date in the format 'YYYY-MM-DD'.
 * @returns {Promise<Array<Object>>} - A promise that resolves to an array of available vehicles.
 */
async function getVehiculeFromDates(refResa, dateDepart, dateRetour) {
    try {
        const url = `/backoffice/reservation/liste-vehicules-disponibles?refResa=${refResa}&dateDepart=${dateDepart}&dateRetour=${dateRetour}`;
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP error ${response.status}`);
        }

        const data = await response.json();
        return data;

    } catch (error) {
        console.error('Error fetching available vehicles:', error);
        throw error;
    }
}

function convertDateToIsoDate(date) {

    const dateObj = new Date(date);

    const year = dateObj.getFullYear();
    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
    const day = String(dateObj.getDate()).padStart(2, '0');
    const hours = String(dateObj.getHours()).padStart(2, '0');
    const minutes = String(dateObj.getMinutes()).padStart(2, '0');

    const formattedDate = `${year}-${month}-${day}T${hours}:${minutes}`;

    // Convert the date to the ISO 8601 format
    // const formattedDate = dateObj.toISOString().slice(0, 16);

    return formattedDate;
}