document.addEventListener('DOMContentLoaded', () => {
  // Handle hover effects for conductor cards
  document.querySelectorAll('.conductor-card').forEach(card => {
    card.addEventListener('mouseenter', () => {
      card.classList.add('shadow-md');
    });
    card.addEventListener('mouseleave', () => {
      card.classList.remove('shadow-md');
    });
  });

  // Confirmation for delete actions
  document.querySelectorAll('form[onsubmit*="confirm"]').forEach(form => {
    form.addEventListener('submit', (e) => {
      if (!confirm('Êtes-vous sûre de vouloir supprimer ce conducteur ?')) {
        e.preventDefault();
      }
    });
  });

  // Print functionality
  document.querySelectorAll('.btn').forEach(button => {
    if (button.textContent.includes('Imprimer')) {
      button.addEventListener('click', () => {
        window.print();
      });
    }
  });

  // Modal toggle
  const modalToggle = document.getElementById('modalConducteurToggle');
  const modal = document.getElementById('modalConducteur');
  modalToggle?.addEventListener('change', () => {
    if (modalToggle.checked) {
      modal.classList.add('modal-open');
    } else {
      modal.classList.remove('modal-open');
    }
  });
});