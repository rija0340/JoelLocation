var btnReserver;
var IDdevis;

getElements();
addEventListener();


function getElements() {
    btnReserver = document.getElementById("reserver");
    IDdevis = document.getElementById("IDdevis").innerText;

}

function addEventListener() {
    btnReserver.addEventListener('click', reserverAjax, false);

}

function reserverAjax() { //envoi donnée à controller par ajax

    alert("tena alefa zan an");
    $.ajax({
        type: 'GET',
        url: '/client/reserverDevis/' + IDdevis,
        timeout: 3000,
        success: function (xmlHttp) {

            console.log('met le izy');

            window.document.location = '/client/reservations';
        },
        error: function () {
            alert('La requête n\'a pas abouti');
        }
    });

}