document.addEventListener('DOMContentLoaded', function () {
    // Initialiser les tooltips Bootstrap si nécessaire
    if (typeof $().tooltip === 'function') {
        $('[data-toggle="tooltip"]').tooltip();
    }

    // Ajouter des événements pour les boutons si nécessaire
    const downloadPdfBtn = document.querySelector('a[href*="devis_pdf"]');
    if (downloadPdfBtn) {
        downloadPdfBtn.addEventListener('click', function (e) {
            // Vous pouvez ajouter un suivi d'événement ici si nécessaire
            console.log('Téléchargement du PDF demandé');
        });
    }

    const validateBtn = document.querySelector('a[href*="validation_step2"]');
    if (validateBtn) {
        validateBtn.addEventListener('click', function (e) {
            // Vous pouvez ajouter un suivi d'événement ici si nécessaire
            console.log('Validation du devis demandée');
        });
    }
});