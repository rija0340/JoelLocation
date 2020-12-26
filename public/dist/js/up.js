$(document).ready(function() {

    // mise en place de la notification automatique qui augmente la valeur sans recharger la page
    function notification() {
        // mise en place d'ajax pour récupérer le nombre de message recue
        let routage = $('#notif_validation').val();
        //let routage = 'http://127.0.0.1:8000/notif_non';
        $.get(routage, function(data) {
            // Une ou plusieurs instructions
            $('#non').text(data['dataResponse']);
            //alert(data['dataResponse']);
        });

        // mise à jour affichage

    }
    setInterval(notification, 10000);



    // upload des fichiers
    //var routing = "{{ path('upload_file') }}";
    //var routing = "127.0.0.1:8000/upload_file";
    var routing = $('#upload_file').val();
    $("#fileuploader").uploadFile({
        url: routing,
        fileName: "myfile",
        maxFileSize: 50000000,
        //formData:{refpiecejointe: $('#piecejointes').val()}
    });

    window.onload = notification;


    // mise en forme de la selection afin que les selects soit avec l'option recherche
    $(".multi").select2();
    $("select").select2();

    // $(this).find(":selected").val()
    $('.restante').on('change', function() {
        // mise en place d'ajax pour récupérer le nombre de message recue
        let routage = $('#notif_restant').val() + '/?prod=' + $(this).find(":selected").val();
        //let routage = 'http://127.0.0.1:8000/notif_non';
        $.get(routage, function(data) {
            // Une ou plusieurs instructions
            $('#restant').text(data['dataResponse']);
            //alert(data['dataResponse']);
        });
    });
    $('.restante').load('change', function() {
        // mise en place d'ajax pour récupérer le nombre de message recue
        let routage = $('#notif_restant').val() + '/?prod=' + $(this).find(":selected").val();
        //let routage = 'http://127.0.0.1:8000/notif_non';
        $.get(routage, function(data) {
            // Une ou plusieurs instructions
            $('#restant').text(data['dataResponse']);
            //alert(data['dataResponse']);
        });
    });

    //mise en place datetimepicker
    //$('#datetimepicker').data("DateTimePicker").FUNCTION()
    $('.datetimepickerannee').datetimepicker({
        format : 'yyyy'
    });
    //$('#.datetimepicker').datetimepicker();
});