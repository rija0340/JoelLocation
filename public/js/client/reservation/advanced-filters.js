// Script pour les filtres avancés de réservation
document.addEventListener('DOMContentLoaded', function () {
    // Configuration pour chaque onglet
    const tabs = ['encours', 'devis', 'avenir', 'termine'];

    tabs.forEach(tabId => {
        setupAdvancedFilters(tabId);
    });

    function setupAdvancedFilters(tabId) {
        // Obtenir l'élément conteneur de l'onglet
        const tabElement = document.getElementById(tabId);
        if (!tabElement) return;

        // Éléments DOM avec les IDs corrects
        const toggleFiltersBtn = document.getElementById(`toggleFilters_${tabId}`);
        const closeFiltersBtn = document.getElementById(`closeFilters_${tabId}`);
        const advancedFilters = document.getElementById(`advancedFilters_${tabId}`);
        const applyFiltersBtn = document.getElementById(`applyFilters_${tabId}`);
        const resetFiltersBtn = document.getElementById(`resetFilters_${tabId}`);
        const resetSearchBtn = document.getElementById(`resetSearch_${tabId}`);
        const searchInput = document.getElementById(`searchInput_${tabId}`);
        const filterTags = document.getElementById(`filterTags_${tabId}`);
        const reservationCards = document.querySelectorAll(`#reservationCards_${tabId} .reservation-card`);
        const noResults = document.getElementById(`noResults_${tabId}`);
        const resultCount = document.getElementById(`resultCount_${tabId}`);

        if (!toggleFiltersBtn || !advancedFilters) return;

        // Toggle des filtres avancés
        toggleFiltersBtn.addEventListener('click', function () {
            console.log('Toggle filters clicked for', tabId); // Ajout de log pour déboguer

            // Vérifier si l'élément advancedFilters existe
            if (!advancedFilters) {
                console.error('Advanced filters element not found for', tabId);
                return;
            }

            // Définir un style d'affichage initial si non défini
            if (advancedFilters.style.display === '') {
                advancedFilters.style.display = 'none';
            }

            // Toggle de l'affichage
            if (advancedFilters.style.display === 'none') {
                advancedFilters.style.display = 'block';
                this.classList.add('active');
            } else {
                advancedFilters.style.display = 'none';
                this.classList.remove('active');
            }
        });

        // Fermer les filtres avec le bouton X
        if (closeFiltersBtn) {
            closeFiltersBtn.addEventListener('click', function () {
                advancedFilters.style.display = 'none';
                toggleFiltersBtn.classList.remove('active');
            });
        }

        // Recherche simple
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                applyFilters();
            });
        }

        // Appliquer les filtres
        if (applyFiltersBtn) {
            applyFiltersBtn.addEventListener('click', function () {
                applyFilters();
                updateFilterTags();
                advancedFilters.style.display = 'none';
                toggleFiltersBtn.classList.remove('active');
            });
        }

        // Réinitialiser les filtres
        if (resetFiltersBtn) {
            resetFiltersBtn.addEventListener('click', function () {
                resetFilters();
                applyFilters();
                updateFilterTags();
            });
        }

        // Réinitialiser la recherche depuis le message "pas de résultats"
        if (resetSearchBtn) {
            resetSearchBtn.addEventListener('click', function () {
                resetFilters();
                applyFilters();
                updateFilterTags();
            });
        }

        // Fonction pour appliquer les filtres
        function applyFilters() {
            const filters = getFilters();
            let visibleCount = 0;

            reservationCards.forEach(card => {
                if (matchesFilters(card, filters)) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Mettre à jour le compteur de résultats
            if (resultCount) {
                resultCount.textContent = visibleCount;
            }

            // Afficher le message "pas de résultats" si nécessaire
            if (visibleCount === 0 && noResults) {
                noResults.style.display = 'block';
            } else if (noResults) {
                noResults.style.display = 'none';
            }
        }

        // Fonction pour obtenir les valeurs des filtres
        function getFilters() {
            return {
                search: searchInput ? searchInput.value.toLowerCase() : '',
                dateFrom: advancedFilters.querySelector('[data-filter="date-from"]') ? advancedFilters.querySelector('[data-filter="date-from"]').value : '',
                dateTo: advancedFilters.querySelector('[data-filter="date-to"]') ? advancedFilters.querySelector('[data-filter="date-to"]').value : '',
                vehicle: advancedFilters.querySelector('[data-filter="vehicle"]') ? advancedFilters.querySelector('[data-filter="vehicle"]').value : '',
                priceMin: advancedFilters.querySelector('[data-filter="price-min"]') ? advancedFilters.querySelector('[data-filter="price-min"]').value : '',
                priceMax: advancedFilters.querySelector('[data-filter="price-max"]') ? advancedFilters.querySelector('[data-filter="price-max"]').value : '',
                durationMin: advancedFilters.querySelector('[data-filter="duration-min"]') ? advancedFilters.querySelector('[data-filter="duration-min"]').value : '',
                durationMax: advancedFilters.querySelector('[data-filter="duration-max"]') ? advancedFilters.querySelector('[data-filter="duration-max"]').value : ''
            };
        }

        // Fonction pour vérifier si une carte correspond aux filtres
        function matchesFilters(card, filters) {
            // Recherche textuelle
            if (filters.search && !card.textContent.toLowerCase().includes(filters.search)) {
                return false;
            }

            // Filtre par date
            const cardDate = card.getAttribute('data-date');
            if (filters.dateFrom && cardDate < filters.dateFrom) {
                return false;
            }
            if (filters.dateTo && cardDate > filters.dateTo) {
                return false;
            }

            // Filtre par véhicule
            const cardVehicle = card.getAttribute('data-vehicle');
            if (filters.vehicle && cardVehicle !== filters.vehicle) {
                return false;
            }

            // Filtre par prix
            const cardPrice = parseFloat(card.getAttribute('data-price'));
            if (filters.priceMin && cardPrice < parseFloat(filters.priceMin)) {
                return false;
            }
            if (filters.priceMax && cardPrice > parseFloat(filters.priceMax)) {
                return false;
            }

            // Filtre par durée
            const cardDuration = parseInt(card.getAttribute('data-duration'));
            if (filters.durationMin && cardDuration < parseInt(filters.durationMin)) {
                return false;
            }
            if (filters.durationMax && cardDuration > parseInt(filters.durationMax)) {
                return false;
            }

            return true;
        }

        // Fonction pour réinitialiser les filtres
        function resetFilters() {
            if (searchInput) searchInput.value = '';

            // Remplacer 'tab' par 'tabElement' qui est défini au début de la fonction
            const filterInputs = tabElement.querySelectorAll('.advanced-filters input, .advanced-filters select');
            filterInputs.forEach(input => {
                input.value = '';
            });
        }

        // Fonction pour mettre à jour les tags de filtre
        function updateFilterTags() {
            if (!filterTags) return;

            filterTags.innerHTML = '';
            const filters = getFilters();

            // Créer des tags pour chaque filtre actif
            if (filters.dateFrom) {
                addFilterTag('Du', formatDate(filters.dateFrom), 'date-from');
            }

            if (filters.dateTo) {
                addFilterTag('Au', formatDate(filters.dateTo), 'date-to');
            }

            if (filters.vehicle) {
                const vehicleSelect = advancedFilters.querySelector('[data-filter="vehicle"]');
                const vehicleText = vehicleSelect.options[vehicleSelect.selectedIndex].text;
                addFilterTag('Véhicule', vehicleText, 'vehicle');
            }

            if (filters.priceMin) {
                addFilterTag('Prix min', filters.priceMin + ' €', 'price-min');
            }

            if (filters.priceMax) {
                addFilterTag('Prix max', filters.priceMax + ' €', 'price-max');
            }

            if (filters.durationMin) {
                addFilterTag('Durée min', filters.durationMin + ' jours', 'duration-min');
            }

            if (filters.durationMax) {
                addFilterTag('Durée max', filters.durationMax + ' jours', 'duration-max');
            }
        }

        // Fonction pour ajouter un tag de filtre
        function addFilterTag(label, value, filterName) {
            const tag = document.createElement('div');
            tag.className = 'filter-tag';
            tag.innerHTML = `
                <span class="filter-tag-label">${label}:</span>
                <span class="filter-tag-value">${value}</span>
                <span class="filter-tag-remove" data-filter="${filterName}">
                    <i class="fa fa-times"></i>
                </span>
            `;

            filterTags.appendChild(tag);

            // Ajouter un événement pour supprimer le filtre
            const removeBtn = tag.querySelector('.filter-tag-remove');
            removeBtn.addEventListener('click', function () {
                const filterName = this.getAttribute('data-filter');
                const filterInput = advancedFilters.querySelector(`[data-filter="${filterName}"]`);
                if (filterInput) {
                    filterInput.value = '';
                    applyFilters();
                    updateFilterTags();
                }
            });
        }

        // Fonction pour formater les dates
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }

        // Initialiser les filtres au chargement
        updateFilterTags();

        // Ajouter des événements pour les filtres de date
        const dateFilters = tabElement.querySelectorAll('.date-filter');
        dateFilters.forEach(filter => {
            filter.addEventListener('change', function () {
                // Vérifier la cohérence des dates
                const dateFrom = tabElement.querySelector('[data-filter="date-from"]').value;
                const dateTo = tabElement.querySelector('[data-filter="date-to"]').value;

                if (dateFrom && dateTo && dateFrom > dateTo) {
                    // Si la date de début est après la date de fin, ajuster la date de fin
                    tabElement.querySelector('[data-filter="date-to"]').value = dateFrom;
                }
            });
        });

        // Ajouter des événements pour les filtres de prix
        const priceFilters = tabElement.querySelectorAll('.price-filter');
        priceFilters.forEach(filter => {
            filter.addEventListener('input', function () {
                // Vérifier la cohérence des prix
                const priceMin = tabElement.querySelector('[data-filter="price-min"]').value;
                const priceMax = tabElement.querySelector('[data-filter="price-max"]').value;

                if (priceMin && priceMax && parseFloat(priceMin) > parseFloat(priceMax)) {
                    // Si le prix min est supérieur au prix max, ajuster le prix max
                    tabElement.querySelector('[data-filter="price-max"]').value = priceMin;
                }
            });
        });

        // Ajouter des événements pour les filtres de durée
        const durationFilters = tabElement.querySelectorAll('.duration-filter');
        durationFilters.forEach(filter => {
            filter.addEventListener('input', function () {
                // Vérifier la cohérence des durées
                const durationMin = tabElement.querySelector('[data-filter="duration-min"]').value;
                const durationMax = tabElement.querySelector('[data-filter="duration-max"]').value;

                if (durationMin && durationMax && parseInt(durationMin) > parseInt(durationMax)) {
                    // Si la durée min est supérieure à la durée max, ajuster la durée max
                    tabElement.querySelector('[data-filter="duration-max"]').value = durationMin;
                }
            });
        });
    }

    // Ajouter des animations pour les cartes
    function animateCards() {
        const cards = document.querySelectorAll('.reservation-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';

            setTimeout(() => {
                card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 50);
        });
    }

    // Animer les cartes au chargement initial
    animateCards();

    // Animer les cartes lors du changement d'onglet
    const tabLinks = document.querySelectorAll('.nav-link');
    tabLinks.forEach(link => {
        link.addEventListener('click', function () {
            setTimeout(animateCards, 150);
        });
    });
});










