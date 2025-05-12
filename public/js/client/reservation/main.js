document.addEventListener('DOMContentLoaded', function () {
    // Initialiser les onglets
    initTabs();

    // Initialiser les filtres (déjà fait dans reservation-filters.js)

    // Afficher les indicateurs de chargement
    showLoadingIndicators();
});

/**
 * Initialise les onglets de navigation
 */
function initTabs() {
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');

    tabLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            // Désactiver tous les onglets
            tabLinks.forEach(tab => tab.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Activer l'onglet cliqué
            this.classList.add('active');
            const targetId = this.getAttribute('data-tab');
            const targetContent = document.getElementById(targetId);

            if (targetContent) {
                targetContent.classList.add('active');

                // Simuler le chargement des données pour l'onglet actif
                const sectionId = targetContent.querySelector('.reservation-section')?.id;
                if (sectionId) {
                    simulateLoading(sectionId);
                }
            }
        });
    });

    // Activer le premier onglet par défaut
    if (tabLinks.length > 0) {
        tabLinks[0].click();
    }
}

/**
 * Affiche les indicateurs de chargement pour toutes les sections
 */
function showLoadingIndicators() {
    const sections = ['encours', 'avenir', 'termine', 'devis'];

    sections.forEach(section => {
        const loadingContainer = document.getElementById(`loading_${section}`);
        const reservationCards = document.getElementById(`reservationCards_${section}`);

        if (loadingContainer && reservationCards) {
            loadingContainer.style.display = 'block';
            reservationCards.style.display = 'none';
        }
    });
}

/**
 * Simule le chargement des données avec un délai
 * @param {string} sectionId - L'identifiant de la section
 */
function simulateLoading(sectionId) {
    const loadingContainer = document.getElementById(`loading_${sectionId}`);
    const reservationCards = document.getElementById(`reservationCards_${sectionId}`);

    if (!loadingContainer || !reservationCards) return;

    // Afficher l'indicateur de chargement
    loadingContainer.style.display = 'block';
    reservationCards.style.display = 'none';

    // Simuler un délai de chargement (entre 1 et 2 secondes)
    const loadingTime = Math.floor(Math.random() * 1000) + 1000;

    setTimeout(function () {
        // Masquer l'indicateur de chargement et afficher les cartes
        loadingContainer.style.display = 'none';
        reservationCards.style.display = 'block';
    }, loadingTime);
}