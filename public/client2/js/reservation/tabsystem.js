document.addEventListener('DOMContentLoaded', function () {
  const tabs = document.querySelectorAll('#reservationTabs .tab');
  const panels = document.querySelectorAll('#reservationPanels .tab-panel');

  tabs.forEach(tab => {
    tab.addEventListener('click', function () {
      // Remove active tab class and reset font color
      tabs.forEach(t => {
        t.classList.remove('tab-active', 'text-red-500');
        t.classList.add('text-gray-700');
      });
      // Add active tab class and set red font color
      tab.classList.add('tab-active', 'text-red-500');
      tab.classList.remove('text-gray-700');

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