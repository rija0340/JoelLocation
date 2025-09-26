// Modern Planning JavaScript

class ModernPlanning {
    constructor() {
        this.data = [];
        this.filteredData = [];
        this.selectedVehicles = new Set();
        this.currentView = 'week'; // week, fortnight, month, bimonth
        this.startDate = new Date();
        this.endDate = new Date(this.startDate.getTime() + 6 * 24 * 60 * 60 * 1000); // +6 days for week view
        this.timelineWidth = 0;
        this.dayWidth = 0;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.calculateTimelineDimensions();
        this.loadInitialData();
    }

    setupEventListeners() {
        // View controls
        document.getElementById('btn7jours')?.addEventListener('click', () => this.changeView('week'));
        document.getElementById('btn14jours')?.addEventListener('click', () => this.changeView('fortnight'));
        document.getElementById('btn1mois')?.addEventListener('click', () => this.changeView('month'));
        document.getElementById('btn2mois')?.addEventListener('click', () => this.changeView('bimonth'));
        
        // Reset button
        document.getElementById('resetPlanning')?.addEventListener('click', () => this.resetPlanning());
        
        // Vehicle filter
        document.getElementById('vehicleFilter')?.addEventListener('input', (e) => {
            this.filterVehicles(e.target.value);
        });
        
        // Date input
        document.getElementById('planningDate')?.addEventListener('change', (e) => {
            this.changeStartDate(e.target.value);
        });
        
        // Window resize handler
        window.addEventListener('resize', () => {
            this.calculateTimelineDimensions();
            this.renderPlanning();
        });
    }

    calculateTimelineDimensions() {
        // Calculate the width based on the container, excluding the sidebar
        const container = document.querySelector('.main-content');
        if (container) {
            // Account for vehicle label width (200px) and some padding
            this.timelineWidth = container.clientWidth - 220;
        } else {
            // Fallback width
            this.timelineWidth = 800;
        }
        
        // Calculate day width based on current view
        const daysCount = this.getDaysCount();
        this.dayWidth = this.timelineWidth / daysCount;
    }

    getDaysCount() {
        const daysMap = {
            'week': 7,
            'fortnight': 14,
            'month': 30,
            'bimonth': 60
        };
        return daysMap[this.currentView] || 7;
    }

    async loadInitialData() {
        try {
            this.showLoading();
            const response = await fetch('/planningGeneralData');
            this.data = await response.json();
            this.filteredData = [...this.data];
            
            // Select all vehicles by default
            this.selectAllVehicles();
            
            this.renderVehicleList();
            this.renderPlanning();
            this.hideLoading();
        } catch (error) {
            console.error('Error loading planning data:', error);
            this.hideLoading();
            alert('Erreur lors du chargement des données de planning');
        }
    }

    changeView(view) {
        this.currentView = view;
        this.updateDateRange();
        this.calculateTimelineDimensions();
        this.renderPlanning();
        this.updateActiveButton(view);
    }

    updateDateRange() {
        // Update end date based on current view
        const daysMap = {
            'week': 6,
            'fortnight': 13,
            'month': 29,
            'bimonth': 59
        };
        const daysToAdd = daysMap[this.currentView] || 6;
        this.endDate = new Date(this.startDate);
        this.endDate.setDate(this.startDate.getDate() + daysToAdd);
    }

    changeStartDate(dateString) {
        this.startDate = new Date(dateString);
        this.updateDateRange();
        this.calculateTimelineDimensions();
        this.renderPlanning();
    }

    filterVehicles(searchTerm) {
        if (!searchTerm) {
            this.filteredData = [...this.data];
        } else {
            // Get vehicles that match the search term
            const matchingVehicles = this.data.filter(item => {
                return item.parent === 0 && item.text && 
                       item.text.toLowerCase().includes(searchTerm.toLowerCase());
            });
            
            // Include reservations for matching vehicles
            const vehicleIds = matchingVehicles.map(v => v.id);
            this.filteredData = this.data.filter(item => {
                return item.parent === 0 || vehicleIds.includes(item.parent);
            });
        }
        
        this.renderPlanning();
        this.renderVehicleList();
    }

    resetPlanning() {
        document.getElementById('vehicleFilter').value = '';
        document.getElementById('planningDate').value = new Date().toISOString().split('T')[0];
        this.startDate = new Date();
        this.currentView = 'week';
        this.updateDateRange();
        this.calculateTimelineDimensions();
        this.selectAllVehicles();
        this.filteredData = [...this.data];
        this.renderPlanning();
        this.renderVehicleList();
        this.updateActiveButton('week');
    }

    selectAllVehicles() {
        // Select all vehicles by default
        const vehicles = this.data.filter(item => item.parent === 0);
        this.selectedVehicles = new Set(vehicles.map(v => v.id));
    }

    toggleVehicleSelection(vehicleId) {
        if (this.selectedVehicles.has(vehicleId)) {
            this.selectedVehicles.delete(vehicleId);
        } else {
            this.selectedVehicles.add(vehicleId);
        }
        this.renderPlanning();
    }

    updateActiveButton(activeView) {
        // Remove active class from all buttons
        document.querySelectorAll('.btn-view').forEach(btn => {
            btn.classList.remove('active');
        });
        // Add active class to the clicked button
        const buttonId = `btn${activeView === 'week' ? '7jours' : 
                             activeView === 'fortnight' ? '14jours' : 
                             activeView === 'month' ? '1mois' : '2mois'}`;
        document.getElementById(buttonId)?.classList.add('active');
    }

    renderVehicleList() {
        const vehicleList = document.querySelector('.vehicle-list');
        if (!vehicleList) return;

        // Get unique vehicles (items with parent = 0)
        const vehicles = this.filteredData.filter(item => item.parent === 0);
        
        vehicleList.innerHTML = '';
        vehicles.forEach(vehicle => {
            const vehicleElement = document.createElement('div');
            vehicleElement.className = 'vehicle-item';
            if (this.selectedVehicles.has(vehicle.id)) {
                vehicleElement.classList.add('selected');
            }
            vehicleElement.dataset.id = vehicle.id;
            
            const reservationCount = this.getReservationsForVehicle(vehicle.id).length;
            
            vehicleElement.innerHTML = `
                <div class="vehicle-checkbox">
                    <input type="checkbox" ${this.selectedVehicles.has(vehicle.id) ? 'checked' : ''}>
                </div>
                <div class="vehicle-info">
                    <div class="vehicle-name">${vehicle.text}</div>
                    <div class="reservation-count">${reservationCount} réservation${reservationCount !== 1 ? 's' : ''}</div>
                </div>
            `;
            
            vehicleElement.addEventListener('click', (e) => {
                // Prevent checkbox toggle when clicking on the checkbox itself
                if (e.target.tagName !== 'INPUT') {
                    const checkbox = vehicleElement.querySelector('input');
                    checkbox.checked = !checkbox.checked;
                }
                this.toggleVehicleSelection(vehicle.id);
            });
            
            vehicleList.appendChild(vehicleElement);
        });
    }

    getReservationsForVehicle(vehicleId) {
        return this.filteredData.filter(item => item.parent === vehicleId);
    }

    renderPlanning() {
        const timelineHeader = document.querySelector('.timeline-header');
        const timelineBody = document.querySelector('.timeline-body');
        
        if (!timelineHeader || !timelineBody) return;

        // Generate timeline header
        this.generateTimelineHeader();
        
        // Get selected vehicles
        const vehicles = this.filteredData.filter(item => 
            item.parent === 0 && this.selectedVehicles.has(item.id)
        );
        
        timelineBody.innerHTML = '';
        
        vehicles.forEach(vehicle => {
            const vehicleRow = document.createElement('div');
            vehicleRow.className = 'vehicle-row';
            
            const vehicleLabel = document.createElement('div');
            vehicleLabel.className = 'vehicle-label';
            vehicleLabel.textContent = vehicle.text;
            
            const vehicleTimeline = document.createElement('div');
            vehicleTimeline.className = 'vehicle-timeline';
            
            // Add reservations for this vehicle
            const reservations = this.getReservationsForVehicle(vehicle.id);
            reservations.forEach(reservation => {
                const reservationElement = this.createReservationElement(reservation);
                vehicleTimeline.appendChild(reservationElement);
            });
            
            vehicleRow.appendChild(vehicleLabel);
            vehicleRow.appendChild(vehicleTimeline);
            timelineBody.appendChild(vehicleRow);
        });
    }

    generateTimelineHeader() {
        const timelineHeader = document.querySelector('.timeline-header');
        if (!timelineHeader) return;

        timelineHeader.innerHTML = '';
        
        const currentDate = new Date(this.startDate);
        const endDateCopy = new Date(this.endDate);
        
        // Include the end date in the timeline
        endDateCopy.setDate(endDateCopy.getDate() + 1);
        
        while (currentDate < endDateCopy) {
            const dayElement = document.createElement('div');
            dayElement.className = 'timeline-day';
            dayElement.style.width = `${this.dayWidth}px`;
            
            // Add weekend class for styling
            if (currentDate.getDay() === 0 || currentDate.getDay() === 6) {
                dayElement.classList.add('weekend');
            }
            
            const dayOfWeek = currentDate.toLocaleDateString('fr-FR', { weekday: 'short' });
            const dayOfMonth = currentDate.getDate();
            const month = currentDate.toLocaleDateString('fr-FR', { month: 'short' });
            
            dayElement.innerHTML = `
                <div class="day-name">${dayOfWeek.charAt(0).toUpperCase() + dayOfWeek.slice(1)}</div>
                <div class="day-date">${dayOfMonth} ${month}</div>
            `;
            
            timelineHeader.appendChild(dayElement);
            currentDate.setDate(currentDate.getDate() + 1);
        }
    }

    createReservationElement(reservation) {
        const element = document.createElement('div');
        element.className = `reservation-item reservation-${this.getReservationTypeClass(reservation)}`;
        element.dataset.id = reservation.id_r;
        
        // Parse dates
        const startDate = this.parseDate(reservation.start_date);
        const endDate = this.parseDate(reservation.end_date_formated);
        
        // Calculate position and width
        const position = this.calculateReservationPosition(startDate, endDate);
        
        element.style.left = `${position.left}px`;
        element.style.width = `${position.width}px`;
        
        // Add status class
        if (reservation.etat) {
            element.classList.add(`etat-${reservation.etat}`);
        }
        
        element.innerHTML = `
            <div class="reservation-content">
                <div class="reservation-client">${reservation.client}</div>
                <div class="reservation-dates">${reservation.start_date_formated} - ${reservation.end_date_formated}</div>
            </div>
        `;
        
        element.addEventListener('click', () => {
            this.openReservationModal(reservation);
        });
        
        return element;
    }

    calculateReservationPosition(startDate, endDate) {
        // Calculate the difference in days from the planning start date
        const startTime = startDate.getTime();
        const endTime = endDate.getTime();
        const planningStartTime = this.startDate.getTime();
        
        // Calculate position as days from start
        const startOffsetDays = (startTime - planningStartTime) / (24 * 60 * 60 * 1000);
        const durationDays = (endTime - startTime) / (24 * 60 * 60 * 1000) + 1; // +1 to include end day
        
        // Convert to pixels
        const left = Math.max(0, startOffsetDays * this.dayWidth);
        const width = Math.max(50, durationDays * this.dayWidth); // Minimum width of 50px
        
        return { left, width };
    }

    getReservationTypeClass(reservation) {
        if (reservation.agenceDepart === "garage") return 'garage';
        if (reservation.agenceDepart && reservation.agenceDepart.includes("Aéroport")) return 'aeroport';
        if (reservation.agenceDepart && reservation.agenceDepart.includes("Gare")) return 'gare';
        if (reservation.agenceDepart && reservation.agenceDepart.includes("Agence")) return 'agence';
        return 'livraison';
    }

    parseDate(dateString) {
        // Parse date string in format "dd-mm-yyyy HH:MM"
        try {
            const [datePart, timePart] = dateString.split(' ');
            const [day, month, year] = datePart.split('-');
            const [hours, minutes] = timePart.split(':');
            return new Date(year, month - 1, day, hours, minutes);
        } catch (e) {
            console.error('Error parsing date:', dateString, e);
            return new Date();
        }
    }

    openReservationModal(reservation) {
        // Create modal HTML
        const modalHtml = `
            <div class="modal" id="reservationModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Détails de la réservation</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Référence</label>
                                        <input type="text" class="form-control" value="${reservation.reference || ''}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Client</label>
                                        <input type="text" class="form-control" value="${reservation.client || ''}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Téléphone</label>
                                        <input type="text" class="form-control" value="${reservation.telClient || ''}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Immatriculation</label>
                                        <input type="text" class="form-control" value="${reservation.immatriculation || ''}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date de départ</label>
                                        <input type="text" class="form-control" value="${reservation.start_date_formated || ''}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date de retour</label>
                                        <input type="text" class="form-control" value="${reservation.end_date_formated || ''}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Agence de départ</label>
                                        <input type="text" class="form-control" value="${reservation.agenceDepart || ''}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Agence de retour</label>
                                        <input type="text" class="form-control" value="${reservation.agenceRetour || ''}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tarif véhicule</label>
                                        <input type="text" class="form-control" value="${reservation.tarifVehicule ? reservation.tarifVehicule + ' €' : ''}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Options/Garanties</label>
                                        <input type="text" class="form-control" value="${reservation.tarifOptionsGaranties ? reservation.tarifOptionsGaranties + ' €' : ''}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tarif total</label>
                                        <input type="text" class="form-control" value="${reservation.tarifResa ? reservation.tarifResa + ' €' : ''}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <button type="button" class="btn btn-primary" id="editReservationBtn">Modifier</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if present
        const existingModal = document.getElementById('reservationModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to document
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Add event listeners
        document.querySelector('#reservationModal .close')?.addEventListener('click', () => {
            document.getElementById('reservationModal')?.remove();
        });
        
        document.querySelector('#reservationModal #editReservationBtn')?.addEventListener('click', () => {
            if (reservation.id_r) {
                window.location.href = `/backoffice/reservation/${reservation.id_r}/edit/`;
            }
        });
        
        // Show modal using Bootstrap
        $('#reservationModal').modal('show');
        
        // Clean up when modal is hidden
        $('#reservationModal').on('hidden.bs.modal', function () {
            $(this).remove();
        });
    }

    showLoading() {
        // Create loading overlay if it doesn't exist
        if (!document.getElementById('loadingOverlay')) {
            const loadingHtml = `
                <div id="loadingOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; justify-content: center; align-items: center;">
                    <div style="background: white; padding: 20px; border-radius: 8px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Chargement...</span>
                        </div>
                        <div style="margin-top: 10px;">Chargement des données...</div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', loadingHtml);
        }
    }

    hideLoading() {
        const loadingOverlay = document.getElementById('loadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.remove();
        }
    }
}

// Initialize the planning when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.modernPlanning = new ModernPlanning();
});

// Export for potential use in other modules
export default ModernPlanning;