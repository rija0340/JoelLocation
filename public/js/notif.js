$(document).ready(function() {

    // mise en place de la notification automatique qui augmente la valeur sans recharger la page
    function notification() {
        // mise en place d'ajax pour récupérer le nombre de message recue
        let routage = $('#notif_validation').val();
        //let routage = 'http://127.0.0.1:8000/avalider';
        $.get(routage, function(data) {
            // Une ou plusieurs instructions
            $('#non').text(data['dataResponse']);
            //alert(data['dataResponse']);
        });

        // mise à jour affichage

    }
    setInterval(notification, 10000);

    window.onload = notification;
});