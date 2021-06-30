var btnRechSimple;
var btnRechImm;
var inputRechSimple;
var motcleRechSimple;
var resultRechSimple;
var tbody;


getElements();
addEventListener();

function getElements() {
    btnRechSimple = document.getElementById('btnrechercheSimple');
    inputRechSimple = document.getElementById('rechercheSimple');
    resultRechSimple = document.getElementById('resultRechSimple');
    resultRechSimple = document.getElementById('resultRechSimple');
    tbody = document.getElementById('tbody');
}

function addEventListener() {
    btnRechSimple.addEventListener('click', rechercheSimple, false);
}

function rechercheSimple(e) {
    e.preventDefault();
    motcleRechSimple = inputRechSimple.value;

    if (motcleRechSimple != "") {

        $.ajax({
            type: 'GET',
            url: '/reservation/recherchesimple',
            timeout: 3000,
            data: { 'recherche': motcleRechSimple },
            success: function (data) {

                console.log(data);

                if (resultRechSimple.classList.contains('hide')) {
                    generateTable(data);
                    // tableCreate(data)
                    resultRechSimple.classList.replace('hide', 'noHide');
                }
                else {

                    resultRechSimple.classList.replace('noHide', 'hide');
                }

            },
            error: function () {
                alert('La requête n\'a pas abouti');
            }
        });
    } else {

        alert("Veuillez remplir le champ");
    }

}

function generateTable(data) {

    for (let i = 0; i < data.length; i++) {

        var tr = document.createElement('tr');


        var tdDateRes = document.createElement('td');
        var tdDateDepart = document.createElement('td');
        var tdDateRetour = document.createElement('td');
        var tdagenceDepart = document.createElement('td');
        var tdagenceRetour = document.createElement('td');

        tr.appendChild(tdDateDepart);
        tr.appendChild(tdDateRetour);
        tr.appendChild(tdDateRes);
        tr.appendChild(tdagenceDepart);
        tr.appendChild(tdagenceRetour);

        tdDateDepart.innerText = data[i].dateDepart;
        tdDateRetour.innerText = data[i].dateRetour;
        tdDateRes.innerText = data[i].dateRes;
        tdagenceDepart.innerText = data[i].agenceDepart;
        tdagenceRetour.innerText = data[i].agenceRetour;

        tbody.appendChild(tr);

    }


}
