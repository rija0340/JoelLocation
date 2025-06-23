document.addEventListener('DOMContentLoaded', function () { // Handle vehicle selection
    const radioInputs = document.querySelectorAll('input[name="vehicule"]');
    const vehicleCards = document.querySelectorAll('.vehicle-card');

    radioInputs.forEach(function (radio) {
        radio.addEventListener('change', function () { // Remove selected class from all cards and buttons
            vehicleCards.forEach(card => card.classList.remove('selected'));
            document.querySelectorAll('.btn-select-vehicle').forEach(btn => {
                btn.classList.remove('selected');
                btn.querySelector('.selection-text').textContent = 'Choisir ce véhicule';
            });

            // Remove existing tarif elements
            document.querySelectorAll(".tarifVehicule").forEach(el => el.remove());

            // Add selected class to current card and button
            if (this.checked) {
                const currentCard = this.closest('.vehicle-card');
                const currentButton = this.nextElementSibling;

                currentCard.classList.add('selected');
                currentButton.classList.add('selected');
                currentButton.querySelector('.selection-text').textContent = 'Véhicule sélectionné';

                // Only add tariff HTML if user is admin
                if (window.isAdmin) {
                    // Create new tarif HTML
                    const tarifHtml = `
					<div class="tarifVehicule">
						<div class="bg-warning-light rounded p-2 border mt-2" style="background-color: #fff3cd;">
							<span class="d-block mb-2">
								<i class="fa fa-edit mr-2"></i>Tarif total
							</span>
							<div class="input-group">
								<input type="text" name="tarifVehicule" class="form-control inputTarif" placeholder="0.00" style="font-size: 1rem;">
								<div class="input-group-append">
									<span class="input-group-text" style="font-size: 1rem;">€</span>
								</div>
							</div>
							<span class="d-block mb-2 mt-2">
								<i class="fa fa-edit mr-2"></i>Tarif journalier
							</span>
							<div class="input-group">
								<input type="text" name="tarifVehiculeJournalier" class="form-control inputTarif" placeholder="0.00" style="font-size: 1rem;">
								<div class="input-group-append">
									<span class="input-group-text" style="font-size: 1rem;">€</span>
								</div>
							</div>
						</div>
					</div>`;

                    // Insert the new element inside the parent container
                    currentButton.parentElement.insertAdjacentHTML('beforeend', tarifHtml);
                }
            }
        });
    });

    // Handle label clicks
    document.querySelectorAll('.btn-select-vehicle').forEach(function (label) {
        label.addEventListener('click', function (e) {
            e.preventDefault();
            const radio = document.getElementById(this.getAttribute('for'));
            radio.checked = true;
            radio.dispatchEvent(new Event('change'));
        });
    });
});