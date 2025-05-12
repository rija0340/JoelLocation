// Script pour les cartes de réservation modernes
document.addEventListener('DOMContentLoaded', function() {
    // Fonction de recherche
    const setupSearch = (tabId) => {
        const searchInput = document.querySelector(`#${tabId} .search-input`);
        const cards = document.querySelectorAll(`#${tabId} .reservation-card`);
        
        if (!searchInput) return;
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            cards.forEach(card => {
                const cardText = card.textContent.toLowerCase();
                if (cardText.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    };
    
    // Configurer la recherche pour chaque onglet
    setupSearch('encours');
    setupSearch('devis');
    setupSearch('avenir');
    setupSearch('termine');
    
    // Animation lors du changement d'onglet
    const tabs = document.querySelectorAll('.nav-link');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Ajouter une petite animation aux cartes
            setTimeout(() => {
                const targetId = this.getAttribute('href').substring(1);
                const cards = document.querySelectorAll(`#${targetId} .reservation-card`);
                
                cards.forEach((card, index) => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    
                    setTimeout(() => {
                        card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, index * 50);
                });
            }, 150);
        });
    });
});