document.addEventListener('DOMContentLoaded', function () {
    // Vérifier si advanced-filters.js est chargé
    const isAdvancedFiltersLoaded = typeof setupAdvancedFilters === 'function';

    // Initialiser les filtres pour chaque section seulement si advanced-filters.js n'est pas chargé
    if (!isAdvancedFiltersLoaded) {
        setupFilters('encours');
        setupFilters('avenir');
        setupFilters('termine');
        setupFilters('devis');
    }

    // Simuler le chargement des données
    simulateLoading('encours');
    simulateLoading('avenir');
    simulateLoading('termine');
    simulateLoading('devis');
});

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

    // Simuler un délai de chargement (entre 1 et 3 secondes)
    const loadingTime = Math.floor(Math.random() * 2000) + 1000;

    setTimeout(function () {
        // Masquer l'indicateur de chargement et afficher les cartes
        loadingContainer.style.display = 'none';
        reservationCards.style.display = 'block';
    }, loadingTime);
}

/**
 * Configure les filtres pour une section spécifique
 * @param {string} sectionId - L'identifiant de la section
 */
function setupFilters(sectionId) {
    const section = document.getElementById(sectionId);
    if (!section) return;

    // Éléments DOM
    const toggleFiltersBtn = document.getElementById(`toggleFilters_${sectionId}`);
    const closeFiltersBtn = document.getElementById(`closeFilters_${sectionId}`);
    const advancedFilters = document.getElementById(`advancedFilters_${sectionId}`);
    const applyFiltersBtn = document.getElementById(`applyFilters_${sectionId}`);
    const resetFiltersBtn = document.getElementById(`resetFilters_${sectionId}`);
    const resetSearchBtn = document.getElementById(`resetSearch_${sectionId}`);
    const searchInput = document.getElementById(`searchInput_${sectionId}`);
    const filterTags = document.getElementById(`filterTags_${sectionId}`);
    const reservationCards = document.querySelectorAll(`#reservationCards_${sectionId} .reservation-card`);
    const noResults = document.getElementById(`noResults_${sectionId}`);
    const resultCount = document.getElementById(`resultCount_${sectionId}`);

    if (!toggleFiltersBtn || !advancedFilters) return;

    // Toggle des filtres avancés
    toggleFiltersBtn.addEventListener('click', function () {
        if (advancedFilters.style.display === 'block') {
            advancedFilters.style.display = 'none';
            this.classList.remove('active');
        } else {
            advancedFilters.style.display = 'block';
            this.classList.add('active');
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
        });
    }

    // Réinitialiser la recherche
    if (resetSearchBtn) {
        resetSearchBtn.addEventListener('click', function () {
            resetFilters();
        });
    }

    /**
     * Applique les filtres et met à jour l'affichage
     */
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

    /**
     * Récupère les valeurs des filtres
     * @returns {Object} Les filtres appliqués
     */
    function getFilters() {
        const filters = {
            search: searchInput ? searchInput.value.toLowerCase() : '',
            dateFrom: document.querySelector(`#advancedFilters_${sectionId} [data-filter="date-from"]`)?.value || '',
            dateTo: document.querySelector(`#advancedFilters_${sectionId} [data-filter="date-to"]`)?.value || '',
            vehicle: document.querySelector(`#advancedFilters_${sectionId} [data-filter="vehicle"]`)?.value || '',
            priceMin: document.querySelector(`#advancedFilters_${sectionId} [data-filter="price-min"]`)?.value || '',
            priceMax: document.querySelector(`#advancedFilters_${sectionId} [data-filter="price-max"]`)?.value || '',
            durationMin: document.querySelector(`#advancedFilters_${sectionId} [data-filter="duration-min"]`)?.value || '',
            durationMax: document.querySelector(`#advancedFilters_${sectionId} [data-filter="duration-max"]`)?.value || ''
        };

        return filters;
    }

    /**
     * Vérifie si une carte correspond aux filtres
     * @param {HTMLElement} card - La carte à vérifier
     * @param {Object} filters - Les filtres à appliquer
     * @returns {boolean} True si la carte correspond aux filtres
     */
    function matchesFilters(card, filters) {
        // Recherche simple
        if (filters.search) {
            const cardText = card.textContent.toLowerCase();
            if (!cardText.includes(filters.search)) {
                return false;
            }
        }

        // Filtre par date
        const cardDate = card.dataset.date;
        if (filters.dateFrom && cardDate < filters.dateFrom) {
            return false;
        }
        if (filters.dateTo && cardDate > filters.dateTo) {
            return false;
        }

        // Filtre par véhicule
        if (filters.vehicle && card.dataset.vehicle !== filters.vehicle) {
            return false;
        }

        // Filtre par prix
        const cardPrice = parseFloat(card.dataset.price);
        if (filters.priceMin && cardPrice < parseFloat(filters.priceMin)) {
            return false;
        }
        if (filters.priceMax && cardPrice > parseFloat(filters.priceMax)) {
            return false;
        }

        // Filtre par durée
        const cardDuration = parseInt(card.dataset.duration);
        if (filters.durationMin && cardDuration < parseInt(filters.durationMin)) {
            return false;
        }
        if (filters.durationMax && cardDuration > parseInt(filters.durationMax)) {
            return false;
        }

        return true;
    }

    /**
     * Met à jour les tags de filtre
     */
    function updateFilterTags() {
        if (!filterTags) return;

        filterTags.innerHTML = '';
        const filters = getFilters();

        // Créer les tags pour chaque filtre actif
        if (filters.dateFrom || filters.dateTo) {
            createFilterTag('Date',
                `${filters.dateFrom ? formatDate(filters.dateFrom) : ''} ${filters.dateFrom && filters.dateTo ? 'au' : ''} ${filters.dateTo ? formatDate(filters.dateTo) : ''}`,
                () => {
                    document.querySelector(`#advancedFilters_${sectionId} [data-filter="date-from"]`).value = '';
                    document.querySelector(`#advancedFilters_${sectionId} [data-filter="date-to"]`).value = '';
                    applyFilters();
                    updateFilterTags();
                }
            );
        }

        if (filters.vehicle) {
            const vehicleSelect = document.querySelector(`#advancedFilters_${sectionId} [data-filter="vehicle"]`);
            const vehicleName = vehicleSelect.options[vehicleSelect.selectedIndex].text;
            createFilterTag('Véhicule', vehicleName, () => {
                vehicleSelect.value = '';
                applyFilters();
                updateFilterTags();
            });
        }

        if (filters.priceMin || filters.priceMax) {
            createFilterTag('Prix',
                `${filters.priceMin ? filters.priceMin + '€' : ''} ${filters.priceMin && filters.priceMax ? 'à' : ''} ${filters.priceMax ? filters.priceMax + '€' : ''}`,
                () => {
                    document.querySelector(`#advancedFilters_${sectionId} [data-filter="price-min"]`).value = '';
                    document.querySelector(`#advancedFilters_${sectionId} [data-filter="price-max"]`).value = '';
                    applyFilters();
                    updateFilterTags();
                }
            );
        }

        if (filters.durationMin || filters.durationMax) {
            createFilterTag('Durée',
                `${filters.durationMin ? filters.durationMin + 'j' : ''} ${filters.durationMin && filters.durationMax ? 'à' : ''} ${filters.durationMax ? filters.durationMax + 'j' : ''}`,
                () => {
                    document.querySelector(`#advancedFilters_${sectionId} [data-filter="duration-min"]`).value = '';
                    document.querySelector(`#advancedFilters_${sectionId} [data-filter="duration-max"]`).value = '';
                    applyFilters();
                    updateFilterTags();
                }
            );
        }
    }

    /**
     * Crée un tag de filtre
     * @param {string} label - Le libellé du filtre
     * @param {string} value - La valeur du filtre
     * @param {Function} removeCallback - Fonction à appeler lors de la suppression du tag
     */
    function createFilterTag(label, value, removeCallback) {
        const tag = document.createElement('div');
        tag.className = 'filter-tag';
        tag.innerHTML = `
            <span class="filter-tag-label">${label}:</span>
            <span class="filter-tag-value">${value}</span>
            <span class="filter-tag-remove"><i class="fa fa-times"></i></span>
        `;

        tag.querySelector('.filter-tag-remove').addEventListener('click', removeCallback);
        filterTags.appendChild(tag);
    }

    /**
     * Réinitialise tous les filtres
     */
    function resetFilters() {
        // Réinitialiser la recherche
        if (searchInput) {
            searchInput.value = '';
        }

        // Réinitialiser les filtres avancés
        const filterInputs = document.querySelectorAll(`#advancedFilters_${sectionId} input, #advancedFilters_${sectionId} select`);
        filterInputs.forEach(input => {
            input.value = '';
        });

        // Réinitialiser les tags
        if (filterTags) {
            filterTags.innerHTML = '';
        }

        // Réappliquer les filtres
        applyFilters();

        // Masquer le message "pas de résultats"
        if (noResults) {
            noResults.style.display = 'none';
        }
    }

    /**
     * Formate une date YYYY-MM-DD en DD/MM/YYYY
     * @param {string} dateString - La date au format YYYY-MM-DD
     * @returns {string} La date au format DD/MM/YYYY
     */
    function formatDate(dateString) {
        const parts = dateString.split('-');
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
}


