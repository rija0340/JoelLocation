document.addEventListener('DOMContentLoaded', function () {
  const tabs = document.querySelectorAll('#reservationTabs .tab');
  const panels = document.querySelectorAll('#reservationPanels .tab-panel');

  tabs.forEach(tab => {
    tab.addEventListener('click', function () {
      // Remove active tab class
      tabs.forEach(t => t.classList.remove('tab-active'));
      tab.classList.add('tab-active');

      const selected = tab.getAttribute('data-tab');

      panels.forEach(panel => {
        panel.classList.toggle('hidden', panel.getAttribute('data-tab') !== selected);
      });

      // Optional: trigger search/sort again for new panel
      const searchTerm = document.getElementById('searchInput').value.toLowerCase();
      if (searchTerm) {
        performSearch(searchTerm);
      }
    });
  });

  // Existing search function (adjusted for panel-based structure)
  function performSearch(searchTerm) {
    const activePanel = document.querySelector('.tab-panel:not(.hidden)');
    const cards = activePanel.querySelectorAll('.booking-card');
    cards.forEach(card => {
      const text = card.textContent.toLowerCase();
      card.classList.toggle('hidden', !text.includes(searchTerm));
    });
    updateTabBadges(); // Optional
  }
});
