var btnAddRowElem;
var dateDebut;
var dateFin;
var immatriculation;
var motif;
var commentaire;

getElements();
addEventListener();

function getElements() {
    btnAddRowElem = document.getElementById('add_row');
}
function addEventListener() {
    btnAddRowElem.addEventListener('click', addRow, false);
}

function addRow() {
    event.preventDefault();
    createRow()
}

function createRow() {

    var tr = document.createElement('tr');
    var td1 = document.createElement('td');
    var td2 = document.createElement('td');
    var td3 = document.createElement('td');
    var td4 = document.createElement('td');
    var td5 = document.createElement('td');
    var td6 = document.createElement('td');
    var td7 = document.createElement('td');

    input1 = document.createElement('input');
    input1.classList.add('form-control');
    input1.type = "date";
    input1.style.width = '150px';
    input1.id = "dateDebut";
    // input1.style.fontSize = '10px';

    input2 = document.createElement('input');
    input2.type = "date";
    input2.style.width = '150px';
    input2.classList.add('form-control');
    input2.id = "dateFin";

    input3 = document.createElement('input');
    input3.classList.add('form-control');
    input3.id = "immatriculation";

    input4 = document.createElement('input');
    input4.classList.add('form-control');
    input4.id = "motif";

    input5 = document.createElement('input');
    input5.classList.add('form-control');
    input5.id = "commentaire";

    btnValide = document.createElement('a');
    btnValide.classList.add('btn', 'btn-danger');
    btnValide.innerText = "Valider";
    btnValide.addEventListener('click', saveEntry, false);

    td1.appendChild(input1);
    td2.appendChild(input2);
    td3.appendChild(input3);
    td4.appendChild(input4);
    td5.appendChild(input5);
    td6.appendChild(btnValide);

    tr.appendChild(td1);
    tr.appendChild(td2);
    tr.appendChild(td3);
    tr.appendChild(td4);
    tr.appendChild(td5);
    tr.appendChild(td6);
    tr.appendChild(td7);

    var tbody = document.querySelector(' table tbody');
    tbody.appendChild(tr);
    console.log(tbody);

}

function saveEntry() {

    dateDebut = document.getElementById('dateDebut').value


}
