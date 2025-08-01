document.addEventListener('DOMContentLoaded', function () {
  const tabs = document.querySelectorAll('#reservationTabs .tab');
  const panels = document.querySelectorAll('#reservationPanels .tab-panel');
  const searchInput = document.getElementById('searchInput');

  function setActiveTab(tabName) {
    console.log('setActiveTab called with:', tabName);

    tabs.forEach(t => {
      const tabData = t.getAttribute('data-tab');
      if (tabData === tabName) {
        console.log('Activating tab:', tabData);
        t.classList.add('tab-active', 'text-red-500');
        t.classList.remove('text-gray-700');
      } else {
        t.classList.remove('tab-active', 'text-red-500');
        t.classList.add('text-gray-700');
      }
    });

    panels.forEach(p => {
      const panelData = p.getAttribute('data-tab');
      const shouldHide = panelData !== tabName;
      console.log(`Panel ${panelData}: ${shouldHide ? 'hiding' : 'showing'}`);
      p.classList.toggle('hidden', shouldHide);
    });

    // update URL fragment without scrolling
    history.replaceState(null, '', `#${encodeURIComponent(tabName)}`);
  }

  // Function to get initial tab from URL (hash or _fragment parameter)
  function getInitialTab() {
    // First check URL hash
    const hash = decodeURIComponent(window.location.hash.slice(1));
    if (hash) return hash;

    // Then check _fragment parameter
    const urlParams = new URLSearchParams(window.location.search);
    const fragment = urlParams.get('_fragment');
    if (fragment) return fragment;

    return null;
  }

  // click handlers (prevent full jump if you want smooth)
  tabs.forEach(tab => {
    tab.addEventListener('click', function (e) {
      e.preventDefault(); // avoid default jump
      const selected = tab.getAttribute('data-tab');
      setActiveTab(selected);
      const searchTerm = searchInput?.value.toLowerCase();
      if (searchTerm) {
        performSearch(searchTerm);
      }
    });
  });

  // On load: activate based on hash, _fragment parameter, or fallback to first tab
  const validTabs = Array.from(tabs).map(t => t.getAttribute('data-tab'));
  const initialTab = getInitialTab();

  // Debug logging
  console.log('Current URL:', window.location.href);
  console.log('Hash:', window.location.hash);
  console.log('Search params:', window.location.search);
  console.log('Initial tab found:', initialTab);
  console.log('Valid tabs:', validTabs);
  console.log('Tabs found:', tabs.length);
  console.log('Panels found:', panels.length);

  if (initialTab && validTabs.includes(initialTab)) {
    console.log('Setting active tab to:', initialTab);
    setActiveTab(initialTab);
  } else if (validTabs.length) {
    console.log('No initial tab found, setting to first tab:', validTabs[0]);
    setActiveTab(validTabs[0]);
  }

  // Listen for hash changes (when user navigates back/forward)
  window.addEventListener('hashchange', function () {
    const newHash = decodeURIComponent(window.location.hash.slice(1));
    if (newHash && validTabs.includes(newHash)) {
      setActiveTab(newHash);
      const searchTerm = searchInput?.value.toLowerCase();
      if (searchTerm) {
        performSearch(searchTerm);
      }
    }
  });

  function performSearch(searchTerm) {
    const activePanel = document.querySelector('.tab-panel:not(.hidden)');
    if (!activePanel) return;
    const cards = activePanel.querySelectorAll('.booking-card');
    cards.forEach(card => {
      const text = card.textContent.toLowerCase();
      card.classList.toggle('hidden', !text.includes(searchTerm));
    });
    if (typeof updateTabBadges === 'function') {
      updateTabBadges();
    }
  }

  // apply existing search on load if any
  if (searchInput) {
    const initial = searchInput.value.toLowerCase();
    if (initial) performSearch(initial);
  }
});