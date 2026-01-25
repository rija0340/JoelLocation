document.addEventListener('DOMContentLoaded', function() {
  // Handle hover effects for conductor cards
  var conductorCards = document.querySelectorAll('.conductor-card');
  conductorCards.forEach(function(card) {
    card.addEventListener('mouseenter', function() {
      card.classList.add('shadow-md');
    });
    card.addEventListener('mouseleave', function() {
      card.classList.remove('shadow-md');
    });
  });

  // Print functionality
  var buttons = document.querySelectorAll('.btn');
  buttons.forEach(function(button) {
    if (button.textContent && button.textContent.includes('Imprimer')) {
      button.addEventListener('click', function() {
        window.print();
      });
    }
  });

  // Modal toggle for conducteur
  var modalToggle = document.getElementById('modalConducteurToggle');
  var modal = document.getElementById('modalConducteur');

  if (modalToggle && modal) {
    var modalContent = modal.querySelector('.bg-white');

    function openModal() {
      modal.classList.remove('opacity-0', 'pointer-events-none');
      modal.classList.add('opacity-100');
      if (modalContent) {
        modalContent.classList.remove('scale-95');
        modalContent.classList.add('scale-100');
      }
    }

    function closeModal() {
      modal.classList.add('opacity-0', 'pointer-events-none');
      modal.classList.remove('opacity-100');
      if (modalContent) {
        modalContent.classList.add('scale-95');
        modalContent.classList.remove('scale-100');
      }
    }

    modalToggle.addEventListener('change', function() {
      if (modalToggle.checked) {
        openModal();
      } else {
        closeModal();
      }
    });

    // Close modal when clicking on backdrop
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        modalToggle.checked = false;
        closeModal();
      }
    });
  }
});