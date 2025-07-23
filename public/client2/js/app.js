document.addEventListener('DOMContentLoaded', () => {
  // Hide preloader after page load
  const preloader = document.getElementById('preloader');
  setTimeout(() => {
    preloader.classList.add('hidden');
  }, 500);

  // Tab navigation
  const tabs = document.querySelectorAll('.btn-ghost[data-toggle="tab"]');
  const tabPanes = document.querySelectorAll('.tab-pane');

  tabs.forEach(tab => {
    tab.addEventListener('click', (e) => {
      e.preventDefault();
      // Remove active classes
      tabs.forEach(t => t.classList.remove('active', 'bg-primary', 'text-primary-content'));
      tabPanes.forEach(pane => pane.classList.remove('show', 'active'));
      // Add active class to clicked tab
      tab.classList.add('active', 'bg-primary', 'text-primary-content');
      // Show corresponding pane
      const targetId = tab.getAttribute('href').substring(1);
      const targetPane = document.getElementById(targetId);
      if (targetPane) {
        targetPane.classList.add('show', 'active');
      }
    });
  });
});