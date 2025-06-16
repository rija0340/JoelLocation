$(document).ready(function () { // Search functionality with debounce
    let searchTimeout;
    $('#searchInput').on('keyup', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const searchTerm = $(this).val().toLowerCase();
            const activeTab = $('.tab-pane.active');

            activeTab.find('.booking-card').each(function () {
                const cardText = $(this).text().toLowerCase();
                const isVisible = cardText.includes(searchTerm);
                $(this).toggleClass('hidden', !isVisible);
            });

            updateTabBadges();
        }, 300);
    });

    // Sort functionality
    $('#sortSelect').on('change', function () {
        const sortOrder = $(this).val();
        const activeTab = $('.tab-pane.active');
        const cards = activeTab.find('.booking-card').get();

        cards.sort((a, b) => {
            let valueA,
                valueB;

            if (sortOrder.startsWith('date')) { // Extract date from the card
                const dateTextA = $(a).find('.text-muted').first().text();
                const dateTextB = $(b).find('.text-muted').first().text();

                // Try to extract date from different formats
                const dateRegex = /(\d{2}\/\d{2}\/\d{4})/;
                const matchA = dateTextA.match(dateRegex);
                const matchB = dateTextB.match(dateRegex);

                if (matchA && matchB) {
                    valueA = new Date(matchA[1].split('/').reverse().join('-'));
                    valueB = new Date(matchB[1].split('/').reverse().join('-'));
                } else {
                    return 0;
                }
            } else if (sortOrder.startsWith('price')) { // Extract price from the card
                const priceTextA = $(a).find('.h5.font-weight-bold').text();
                const priceTextB = $(b).find('.h5.font-weight-bold').text();

                const priceRegex = /(\d+(?:,\d+)?(?:\.\d+)?)\s*€/;
                const matchA = priceTextA.match(priceRegex);
                const matchB = priceTextB.match(priceRegex);

                if (matchA && matchB) {
                    valueA = parseFloat(matchA[1].replace(',', '.'));
                    valueB = parseFloat(matchB[1].replace(',', '.'));
                } else {
                    return 0;
                }
            }

            if (sortOrder.endsWith('desc')) {
                return valueB - valueA;
            } else {
                return valueA - valueB;
            }
        });

        activeTab.find('.booking-list').append(cards);
    });

    // Update tab badges
    function updateTabBadges() {
        const tabCounts = {
            'devis': $('.tab-pane#devis .booking-card:not(.hidden)').length,
            'encours': $('.tab-pane#encours .booking-card:not(.hidden)').length,
            'avenir': $('.tab-pane#avenir .booking-card:not(.hidden)').length,
            'termine': $('.tab-pane#termine .booking-card:not(.hidden)').length
        };

        Object.keys(tabCounts).forEach(tabId => {
            $(`#${tabId}-count`).text(tabCounts[tabId]);
        });
    }

    // Tab change handler
    $('a[data-toggle="tab"]').on('shown.bs.tab', function () { // Reset search when switching tabs
        $('#searchInput').val('');
        $('.booking-card').removeClass('hidden');
        updateTabBadges();
    });

    // Initialize badges
    updateTabBadges();
});