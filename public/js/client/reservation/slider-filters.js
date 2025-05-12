// Script pour les sliders de filtres (prix et durée)
document.addEventListener('DOMContentLoaded', function () {
    // Fonction pour vérifier si noUiSlider est chargé
    function checkNoUiSlider() {
        if (typeof noUiSlider !== 'undefined') {
            // noUiSlider est chargé, initialiser les sliders
            initializeSliders();
        } else {
            // noUiSlider n'est pas encore chargé, attendre 100ms et réessayer
            setTimeout(checkNoUiSlider, 100);
        }
    }

    // Démarrer la vérification
    checkNoUiSlider();

    // Fonction pour initialiser les sliders
    function initializeSliders() {
        const tabs = ['encours', 'avenir', 'termine'];
        tabs.forEach(tabId => {
            const tab = document.getElementById(tabId);
            if (tab) {
                setupSliders(tab);
            }
        });
    }

    // Fonction pour initialiser les sliders
    function setupSliders(tabElement) {
        // Récupérer toutes les cartes de réservation
        const cards = tabElement.querySelectorAll('.reservation-card');
        if (!cards.length) return;

        // Trouver les valeurs min et max pour les prix et durées
        let minPrice = Infinity;
        let maxPrice = 0;
        let minDuration = Infinity;
        let maxDuration = 0;

        cards.forEach(card => {
            const price = parseFloat(card.dataset.price);
            const duration = parseInt(card.dataset.duration);

            if (price < minPrice) minPrice = price;
            if (price > maxPrice) maxPrice = price;
            if (duration < minDuration) minDuration = duration;
            if (duration > maxDuration) maxDuration = duration;
        });

        // Arrondir les valeurs
        minPrice = Math.floor(minPrice);
        maxPrice = Math.ceil(maxPrice);

        // Créer les conteneurs pour les sliders
        createSliderContainers(tabElement, minPrice, maxPrice, minDuration, maxDuration);

        // Initialiser le slider de prix
        const priceSlider = tabElement.querySelector('#priceSlider');
        if (priceSlider) {
            noUiSlider.create(priceSlider, {
                start: [minPrice, maxPrice],
                connect: true,
                step: 10,
                range: {
                    'min': minPrice,
                    'max': maxPrice
                },
                format: {
                    to: function (value) {
                        return Math.round(value);
                    },
                    from: function (value) {
                        return Number(value);
                    }
                }
            });

            // Mettre à jour les inputs lors du changement du slider
            const priceMinInput = tabElement.querySelector('[data-filter="price-min"]');
            const priceMaxInput = tabElement.querySelector('[data-filter="price-max"]');
            const priceValues = tabElement.querySelectorAll('.price-slider-value');

            priceSlider.noUiSlider.on('update', function (values, handle) {
                priceValues[handle].innerHTML = values[handle] + ' €';

                if (handle === 0) {
                    priceMinInput.value = values[handle];
                } else {
                    priceMaxInput.value = values[handle];
                }
            });

            // Mettre à jour le slider lors du changement des inputs
            priceMinInput.addEventListener('change', function () {
                priceSlider.noUiSlider.set([this.value, null]);
            });

            priceMaxInput.addEventListener('change', function () {
                priceSlider.noUiSlider.set([null, this.value]);
            });
        }

        // Initialiser le slider de durée
        const durationSlider = tabElement.querySelector('#durationSlider');
        if (durationSlider) {
            noUiSlider.create(durationSlider, {
                start: [minDuration, maxDuration],
                connect: true,
                step: 1,
                range: {
                    'min': minDuration,
                    'max': maxDuration
                },
                format: {
                    to: function (value) {
                        return Math.round(value);
                    },
                    from: function (value) {
                        return Number(value);
                    }
                }
            });

            // Mettre à jour les inputs lors du changement du slider
            const durationMinInput = tabElement.querySelector('[data-filter="duration-min"]');
            const durationMaxInput = tabElement.querySelector('[data-filter="duration-max"]');
            const durationValues = tabElement.querySelectorAll('.duration-slider-value');

            durationSlider.noUiSlider.on('update', function (values, handle) {
                const value = values[handle];
                durationValues[handle].innerHTML = value + (value > 1 ? ' jours' : ' jour');

                if (handle === 0) {
                    durationMinInput.value = values[handle];
                } else {
                    durationMaxInput.value = values[handle];
                }
            });

            // Mettre à jour le slider lors du changement des inputs
            durationMinInput.addEventListener('change', function () {
                durationSlider.noUiSlider.set([this.value, null]);
            });

            durationMaxInput.addEventListener('change', function () {
                durationSlider.noUiSlider.set([null, this.value]);
            });
        }
    }

    // Fonction pour créer les conteneurs de sliders
    function createSliderContainers(tabElement, minPrice, maxPrice, minDuration, maxDuration) {
        // Remplacer les inputs de prix par un slider
        const priceGroup = tabElement.querySelector('.filter-group:has(.price-range)');
        if (priceGroup) {
            const priceRange = priceGroup.querySelector('.price-range');
            const priceMinInput = priceRange.querySelector('[data-filter="price-min"]');
            const priceMaxInput = priceRange.querySelector('[data-filter="price-max"]');

            // Créer le conteneur du slider
            const sliderContainer = document.createElement('div');
            sliderContainer.className = 'slider-container';
            sliderContainer.innerHTML = `
                <div id="priceSlider" class="price-slider"></div>
                <div class="slider-values">
                    <span class="price-slider-value">${minPrice} €</span>
                    <span class="price-slider-value">${maxPrice} €</span>
                </div>
            `;

            // Cacher les inputs originaux mais les garder pour stocker les valeurs
            priceMinInput.style.display = 'none';
            priceMaxInput.style.display = 'none';

            // Ajouter le slider après les inputs
            priceRange.appendChild(sliderContainer);
        }

        // Remplacer les inputs de durée par un slider
        const durationGroup = tabElement.querySelector('.filter-group:has(.duration-range)');
        if (durationGroup) {
            const durationRange = durationGroup.querySelector('.duration-range');
            const durationMinInput = durationRange.querySelector('[data-filter="duration-min"]');
            const durationMaxInput = durationRange.querySelector('[data-filter="duration-max"]');

            // Créer le conteneur du slider
            const sliderContainer = document.createElement('div');
            sliderContainer.className = 'slider-container';
            sliderContainer.innerHTML = `
                <div id="durationSlider" class="duration-slider"></div>
                <div class="slider-values">
                    <span class="duration-slider-value">${minDuration} jour${minDuration > 1 ? 's' : ''}</span>
                    <span class="duration-slider-value">${maxDuration} jour${maxDuration > 1 ? 's' : ''}</span>
                </div>
            `;

            // Cacher les inputs originaux mais les garder pour stocker les valeurs
            durationMinInput.style.display = 'none';
            durationMaxInput.style.display = 'none';

            // Ajouter le slider après les inputs
            durationRange.appendChild(sliderContainer);
        }
    }

    // Ajouter des événements pour appliquer les filtres lors du changement des sliders
    const tabsArray = ['encours', 'avenir', 'termine']; // Définir la variable tabsArray ici

    tabsArray.forEach(tabId => {
        const tab = document.getElementById(tabId);
        if (!tab) return;

        const priceSlider = tab.querySelector('#priceSlider');
        const durationSlider = tab.querySelector('#durationSlider');
        const applyFiltersBtn = tab.querySelector('#applyFilters');

        if (priceSlider && priceSlider.noUiSlider) {
            priceSlider.noUiSlider.on('change', function () {
                if (applyFiltersBtn) {
                    applyFiltersBtn.click();
                }
            });
        }

        if (durationSlider && durationSlider.noUiSlider) {
            durationSlider.noUiSlider.on('change', function () {
                if (applyFiltersBtn) {
                    applyFiltersBtn.click();
                }
            });
        }
    });
});




