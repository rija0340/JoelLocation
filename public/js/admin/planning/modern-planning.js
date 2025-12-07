// class ModernPlanning {
//     constructor() {
//         this.data = [];
//         this.filteredData = [];
//         this.selectedVehicles = new Set();
//         this.currentStartDate = new Date();
//         this.currentDays = 6;
//         this.vehicleSearch = '';

//         this.init();
//     }

//     init() {
//         this.bindEvents();
//         this.loadData();
//     }

//     bindEvents() {
//         // Date picker
//         document.getElementById('startDate').addEventListener('change', (e) => {
//             this.currentStartDate = new Date(e.target.value);
//             this.updateTimeline();
//         });

//         // View buttons
//         document.querySelectorAll('.btn-view').forEach(btn => {
//             btn.addEventListener('click', (e) => {
//                 document.querySelectorAll('.btn-view').forEach(b => b.classList.remove('active'));
//                 e.target.classList.add('active');
//                 this.currentDays = parseInt(e.target.dataset.days);
//                 this.updateTimeline();
//             });
//         });

//         // Reset button
//         document.getElementById('resetPlanning').addEventListener('click', () => {
//             this.reset();
//         });

//         // Vehicle search
//         document.getElementById('vehicleSearch').addEventListener('input', (e) => {
//             this.vehicleSearch = e.target.value.toLowerCase();
//             this.filterVehicles();
//         });

//         // Select all checkbox
//         document.getElementById('selectAll').addEventListener('change', (e) => {
//             this.toggleAllVehicles(e.target.checked);
//         });
//     }

//     async loadData() {
//         try {
//             document.getElementById('timelineBody').innerHTML = '<div class="loading">Chargement...</div>';

//             const response = await fetch('/planningGeneralData');
//             const data = await response.json();

//             this.processData(data);
//             this.renderVehicleList();
//             this.updateTimeline();

//         } catch (error) {
//             console.error('Erreur lors du chargement des données:', error);
//             document.getElementById('timelineBody').innerHTML = '<div class="loading">Erreur de chargement</div>';
//         }
//     }

//     processData(rawData) {
//         // Separate vehicles and reservations
//         this.vehicles = rawData.filter(item => item.parent === 0);
//         this.reservations = rawData.filter(item => item.parent !== 0);

//         // Initialize all vehicles as selected
//         this.selectedVehicles = new Set(this.vehicles.map(v => v.id));

//         this.data = rawData;
//         this.filteredData = [...this.data];
//     }

//     renderVehicleList() {
//         const vehicleList = document.getElementById('vehicleList');
//         vehicleList.innerHTML = '';

//         this.vehicles.forEach(vehicle => {
//             const reservationCount = this.reservations.filter(r => r.parent === vehicle.id).length;

//             const vehicleItem = document.createElement('div');
//             vehicleItem.className = 'vehicle-item';
//             vehicleItem.dataset.vehicleId = vehicle.id;

//             vehicleItem.innerHTML = `
//                 <input type="checkbox" class="vehicle-checkbox" ${this.selectedVehicles.has(vehicle.id) ? 'checked' : ''}>
//                 <div class="vehicle-info">
//                     <div class="vehicle-name">${vehicle.text}</div>
//                     <div class="vehicle-count">${reservationCount} réservation(s)</div>
//                 </div>
//             `;

//             const checkbox = vehicleItem.querySelector('.vehicle-checkbox');
//             checkbox.addEventListener('change', (e) => {
//                 this.toggleVehicle(vehicle.id, e.target.checked);
//             });

//             vehicleList.appendChild(vehicleItem);
//         });
//     }

//     updateTimeline() {
//         this.renderTimelineHeader();
//         this.renderTimelineBody();
//         this.updatePeriodDisplay();
//     }

//     renderTimelineHeader() {
//         const header = document.getElementById('timelineHeader');
//         header.innerHTML = '';

//         const endDate = new Date(this.currentStartDate);
//         endDate.setDate(endDate.getDate() + this.currentDays);

//         for (let d = new Date(this.currentStartDate); d <= endDate; d.setDate(d.getDate() + 1)) {
//             const dayHeader = document.createElement('div');
//             dayHeader.className = 'day-header';

//             if (d.getDay() === 0 || d.getDay() === 6) {
//                 dayHeader.classList.add('weekend');
//             }

//             dayHeader.innerHTML = `
//                 <div class="day-number">${d.getDate()}</div>
//                 <div class="day-name">${d.toLocaleDateString('fr-FR', { weekday: 'short' })}</div>
//             `;

//             header.appendChild(dayHeader);
//         }
//     }

//     renderTimelineBody() {
//         const body = document.getElementById('timelineBody');
//         body.innerHTML = '';

//         const selectedVehicles = this.vehicles.filter(v => this.selectedVehicles.has(v.id));

//         selectedVehicles.forEach(vehicle => {
//             const vehicleRow = this.createVehicleRow(vehicle);
//             body.appendChild(vehicleRow);
//         });
//     }

//     createVehicleRow(vehicle) {
//         const row = document.createElement('div');
//         row.className = 'vehicle-row';
//         row.dataset.vehicleId = vehicle.id;

//         const endDate = new Date(this.currentStartDate);
//         endDate.setDate(endDate.getDate() + this.currentDays);

//         // Create day cells
//         for (let d = new Date(this.currentStartDate); d <= endDate; d.setDate(d.getDate() + 1)) {
//             const dayCell = document.createElement('div');
//             dayCell.className = 'day-cell';

//             if (d.getDay() === 0 || d.getDay() === 6) {
//                 dayCell.classList.add('weekend');
//             }

//             row.appendChild(dayCell);
//         }

//         // Add reservations
//         const vehicleReservations = this.reservations.filter(r => r.parent === vehicle.id);
//         vehicleReservations.forEach(reservation => {
//             this.addReservationToRow(row, reservation);
//         });

//         return row;
//     }

//     addReservationToRow(row, reservation) {
//         const startDate = this.parseDate(reservation.start_date_formated);
//         const endDate = this.parseDate(reservation.end_date_formated);

//         // Check if reservation overlaps with current view
//         const viewStart = this.currentStartDate;
//         const viewEnd = new Date(this.currentStartDate);
//         viewEnd.setDate(viewEnd.getDate() + this.currentDays);

//         if (endDate < viewStart || startDate > viewEnd) {
//             return; // Reservation outside view
//         }

//         const reservationBlock = document.createElement('div');
//         reservationBlock.className = `reservation-block ${reservation.etat}`;

//         // Calculate position and width
//         const totalDays = this.currentDays + 1;
//         const dayWidth = 100 / totalDays;

//         const startOffset = Math.max(0, this.getDaysDifference(viewStart, startDate));
//         const endOffset = Math.min(totalDays, this.getDaysDifference(viewStart, endDate) + 1);
//         const width = endOffset - startOffset;

//         reservationBlock.style.left = `${startOffset * dayWidth}%`;
//         reservationBlock.style.width = `${width * dayWidth}%`;

//         reservationBlock.innerHTML = `
//             <div class="reservation-text">${reservation.client} - ${reservation.reference}</div>
//         `;

//         reservationBlock.addEventListener('click', () => {
//             this.showReservationModal(reservation);
//         });

//         row.appendChild(reservationBlock);
//     }

//     parseDate(dateString) {
//         // Parse "dd-mm-yyyy hh:mm" format
//         const [datePart] = dateString.split(' ');
//         const [day, month, year] = datePart.split('-');
//         return new Date(year, month - 1, day);
//     }

//     getDaysDifference(date1, date2) {
//         const timeDiff = date2.getTime() - date1.getTime();
//         return Math.floor(timeDiff / (1000 * 3600 * 24));
//     }

//     showReservationModal(reservation) {
//         const modal = new bootstrap.Modal(document.getElementById('reservationModal'));
//         const modalBody = document.getElementById('modalBody');

//         modalBody.innerHTML = `
//             <div class="row">
//                 <div class="col-md-6">
//                     <strong>Référence:</strong> ${reservation.reference}<br>
//                     <strong>Client:</strong> ${reservation.client}<br>
//                     <strong>Véhicule:</strong> ${reservation.immatriculation}<br>
//                     <strong>État:</strong> <span class="badge bg-${this.getStatusColor(reservation.etat)}">${reservation.etat}</span>
//                 </div>
//                 <div class="col-md-6">
//                     <strong>Date début:</strong> ${reservation.start_date_formated}<br>
//                     <strong>Date fin:</strong> ${reservation.end_date_formated}<br>
//                     <strong>Agence départ:</strong> ${reservation.agenceDepart}<br>
//                     <strong>Agence retour:</strong> ${reservation.agenceRetour}
//                 </div>
//             </div>
//             <hr>
//             <div class="row">
//                 <div class="col-md-6">
//                     <strong>Prix:</strong> ${reservation.tarifResa}€<br>
//                     <strong>Téléphone:</strong> ${reservation.telClient}
//                 </div>
//             </div>
//         `;

//         document.getElementById('editReservation').onclick = () => {
//             window.location.href = `/backoffice/reservation/details/${reservation.id_r}`;
//         };

//         modal.show();
//     }

//     getStatusColor(status) {
//         const colors = {
//             'nouvelle': 'success',
//             'encours': 'warning',
//             'termine': 'secondary',
//             'stopSale': 'danger'
//         };
//         return colors[status] || 'primary';
//     }

//     toggleVehicle(vehicleId, selected) {
//         if (selected) {
//             this.selectedVehicles.add(vehicleId);
//         } else {
//             this.selectedVehicles.delete(vehicleId);
//         }
//         this.updateTimeline();
//         this.updateSelectAllCheckbox();
//     }

//     toggleAllVehicles(selectAll) {
//         const visibleVehicles = this.getVisibleVehicles();

//         if (selectAll) {
//             visibleVehicles.forEach(v => this.selectedVehicles.add(v.id));
//         } else {
//             visibleVehicles.forEach(v => this.selectedVehicles.delete(v.id));
//         }

//         // Update checkboxes
//         document.querySelectorAll('.vehicle-checkbox').forEach(checkbox => {
//             const vehicleId = parseInt(checkbox.closest('.vehicle-item').dataset.vehicleId);
//             checkbox.checked = this.selectedVehicles.has(vehicleId);
//         });

//         this.updateTimeline();
//     }

//     filterVehicles() {
//         document.querySelectorAll('.vehicle-item').forEach(item => {
//             const vehicleName = item.querySelector('.vehicle-name').textContent.toLowerCase();
//             const matches = vehicleName.includes(this.vehicleSearch);
//             item.classList.toggle('hidden', !matches);
//         });

//         this.updateSelectAllCheckbox();
//     }

//     getVisibleVehicles() {
//         return this.vehicles.filter(vehicle => {
//             const vehicleName = vehicle.text.toLowerCase();
//             return vehicleName.includes(this.vehicleSearch);
//         });
//     }

//     updateSelectAllCheckbox() {
//         const visibleVehicles = this.getVisibleVehicles();
//         const selectedVisible = visibleVehicles.filter(v => this.selectedVehicles.has(v.id));

//         const selectAllCheckbox = document.getElementById('selectAll');
//         selectAllCheckbox.checked = selectedVisible.length === visibleVehicles.length && visibleVehicles.length > 0;
//         selectAllCheckbox.indeterminate = selectedVisible.length > 0 && selectedVisible.length < visibleVehicles.length;
//     }

//     updatePeriodDisplay() {
//         const endDate = new Date(this.currentStartDate);
//         endDate.setDate(endDate.getDate() + this.currentDays);

//         const startStr = this.currentStartDate.toLocaleDateString('fr-FR');
//         const endStr = endDate.toLocaleDateString('fr-FR');

//         document.getElementById('periodText').textContent = `Du ${startStr} au ${endStr}`;
//     }

//     reset() {
//         this.currentStartDate = new Date();
//         this.currentDays = 6;
//         this.vehicleSearch = '';

//         document.getElementById('startDate').value = this.currentStartDate.toISOString().split('T')[0];
//         document.getElementById('vehicleSearch').value = '';

//         document.querySelectorAll('.btn-view').forEach(btn => btn.classList.remove('active'));
//         document.querySelector('.btn-view[data-days="6"]').classList.add('active');

//         this.selectedVehicles = new Set(this.vehicles.map(v => v.id));
//         this.renderVehicleList();
//         this.updateTimeline();
//     }
// }

// // Initialize when DOM is loaded
// document.addEventListener('DOMContentLoaded', () => {
//     new ModernPlanning();
// });



class ModernPlanning {
    constructor() {
        this.data = [];
        this.filteredData = [];
        this.selectedVehicles = new Set();
        this.currentStartDate = new Date();
        this.currentDays = 6;
        this.vehicleSearch = '';
        this.draggedReservation = null;
        this.dragOffset = { x: 0, y: 0 };
        this.hoursPerDay = 12; // Working hours per day
        this.startHour = 8; // Day starts at 8 AM

        this.init();
    }

    init() {
        this.bindEvents();
        this.loadData();
    }

    bindEvents() {
        // Date picker
        document.getElementById('startDate').addEventListener('change', (e) => {
            this.currentStartDate = new Date(e.target.value);
            this.updateTimeline();
        });

        // View buttons
        document.querySelectorAll('.btn-view').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.btn-view').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                this.currentDays = parseInt(e.target.dataset.days);
                this.updateTimeline();
            });
        });

        // Reset button
        document.getElementById('resetPlanning').addEventListener('click', () => {
            this.reset();
        });

        // Vehicle search
        document.getElementById('vehicleSearch').addEventListener('input', (e) => {
            this.vehicleSearch = e.target.value.toLowerCase();
            this.filterVehicles();
        });

        // Select all checkbox
        document.getElementById('selectAll').addEventListener('change', (e) => {
            this.toggleAllVehicles(e.target.checked);
        });
    }

    async loadData() {
        try {
            document.getElementById('timelineBody').innerHTML = '<div class="loading">Chargement...</div>';

            const response = await fetch('/planningGeneralData');
            const data = await response.json();
            this.processData(data);
            this.renderVehicleList();
            this.updateTimeline();

        } catch (error) {
            console.error('Erreur lors du chargement des données:', error);
            document.getElementById('timelineBody').innerHTML = '<div class="loading">Erreur de chargement</div>';
        }
    }

    processData(rawData) {
        // Separate vehicles and reservations
        this.vehicles = rawData.filter(item => item.parent === 0);
        this.reservations = rawData.filter(item => item.parent !== 0);

        // Initialize all vehicles as selected
        this.selectedVehicles = new Set(this.vehicles.map(v => v.id));

        this.data = rawData;
        this.filteredData = [...this.data];
    }

    renderVehicleList() {
        const visibleList = document.getElementById('vehicleListVisible');
        const hiddenList = document.getElementById('vehicleListHidden');
        const visibleCount = document.getElementById('visibleCount');
        const hiddenCount = document.getElementById('hiddenCount');

        // Safety check - ensure elements exist
        if (!visibleList || !hiddenList) {
            console.error('Vehicle list containers not found. Expected elements with IDs: vehicleListVisible, vehicleListHidden');
            console.error('visibleList:', visibleList);
            console.error('hiddenList:', hiddenList);
            return;
        }

        visibleList.innerHTML = '';
        hiddenList.innerHTML = '';

        let vCount = 0;
        let hCount = 0;

        // Filter vehicles based on search first
        const searchFilteredVehicles = this.vehicles.filter(v =>
            v.text.toLowerCase().includes(this.vehicleSearch)
        );

        searchFilteredVehicles.forEach(vehicle => {
            const isSelected = this.selectedVehicles.has(vehicle.id);
            const reservationCount = this.reservations.filter(r => r.parent === vehicle.id).length;

            const vehicleItem = document.createElement('div');
            vehicleItem.className = 'vehicle-item';
            vehicleItem.dataset.vehicleId = vehicle.id;

            // Add visual indicator for selection state
            if (isSelected) {
                vehicleItem.classList.add('selected');
            }

            vehicleItem.innerHTML = `
                <input type="checkbox" class="vehicle-checkbox" ${isSelected ? 'checked' : ''}>
                <div class="vehicle-info">
                    <div class="vehicle-name">${vehicle.text}</div>
                    <div class="vehicle-count">${reservationCount} réservation(s)</div>
                </div>
            `;

            const checkbox = vehicleItem.querySelector('.vehicle-checkbox');
            checkbox.addEventListener('change', (e) => {
                this.toggleVehicle(vehicle.id, e.target.checked);
            });

            // Add click handler to the whole item to toggle checkbox
            vehicleItem.addEventListener('click', (e) => {
                if (e.target !== checkbox) {
                    checkbox.checked = !checkbox.checked;
                    this.toggleVehicle(vehicle.id, checkbox.checked);
                }
            });

            if (isSelected) {
                visibleList.appendChild(vehicleItem);
                vCount++;
            } else {
                hiddenList.appendChild(vehicleItem);
                hCount++;
            }
        });

        // Update counts
        if (visibleCount) visibleCount.textContent = vCount;
        if (hiddenCount) hiddenCount.textContent = hCount;
    }

    updateTimeline() {
        this.renderTimelineHeader();
        this.renderTimelineBody();
        this.updatePeriodDisplay();
    }

    renderTimelineHeader() {
        const header = document.getElementById('timelineHeader');
        header.innerHTML = '';

        const endDate = new Date(this.currentStartDate);
        endDate.setDate(endDate.getDate() + this.currentDays - 1);

        for (let d = new Date(this.currentStartDate); d <= endDate; d.setDate(d.getDate() + 1)) {
            const dayHeader = document.createElement('div');
            dayHeader.className = 'day-header';

            if (d.getDay() === 0 || d.getDay() === 6) {
                dayHeader.classList.add('weekend');
            }

            dayHeader.innerHTML = `
                <div class="day-number">${d.getDate()}</div>
                <div class="day-name">${d.toLocaleDateString('fr-FR', { weekday: 'short' })}</div>
            `;

            header.appendChild(dayHeader);
        }
    }

    renderTimelineBody() {
        const body = document.getElementById('timelineBody');
        body.innerHTML = '';

        // Add spacer to compensate for the "Affichés" section-header in sidebar (32px)
        const spacer = document.createElement('div');
        spacer.className = 'timeline-spacer';
        body.appendChild(spacer);

        const visibleAndSelectedVehicles = this.vehicles.filter(v =>
            this.selectedVehicles.has(v.id) &&
            v.text.toLowerCase().includes(this.vehicleSearch)
        );

        visibleAndSelectedVehicles.forEach(vehicle => {
            const vehicleRow = this.createVehicleRow(vehicle);
            body.appendChild(vehicleRow);
        });
    }

    createVehicleRow(vehicle) {
        const row = document.createElement('div');
        row.className = 'vehicle-row selected'; // Add 'selected' class so it displays
        row.dataset.vehicleId = vehicle.id;
        row.style.position = 'relative'; // Enable absolute positioning for reservation blocks

        const endDate = new Date(this.currentStartDate);
        endDate.setDate(endDate.getDate() + this.currentDays - 1);

        // Create day cells
        for (let d = new Date(this.currentStartDate); d <= endDate; d.setDate(d.getDate() + 1)) {
            const dayCell = document.createElement('div');
            dayCell.className = 'day-cell';

            if (d.getDay() === 0 || d.getDay() === 6) {
                dayCell.classList.add('weekend');
            }

            row.appendChild(dayCell);
        }

        // Add reservations with overlap prevention
        const vehicleReservations = this.reservations.filter(r => r.parent === vehicle.id);
        this.addReservationsWithOverlapHandling(row, vehicleReservations);

        return row;
    }

    addReservationsWithOverlapHandling(row, reservations) {
        // Sort reservations by start date
        const sortedReservations = reservations.sort((a, b) => {
            const dateA = this.parseDateTime(a.start_date_formated);
            const dateB = this.parseDateTime(b.start_date_formated);
            return dateA - dateB;
        });

        // Track occupied time slots to prevent overlaps
        const lanes = []; // Each lane represents a horizontal level for reservations

        sortedReservations.forEach(reservation => {
            const startDateTime = this.parseDateTime(reservation.start_date_formated);
            const endDateTime = this.parseDateTime(reservation.end_date_formated);

            // Check if reservation is within view
            const viewStart = this.currentStartDate;
            const viewEnd = new Date(this.currentStartDate);
            viewEnd.setDate(viewEnd.getDate() + this.currentDays);

            if (endDateTime < viewStart || startDateTime > viewEnd) {
                return; // Skip reservations outside view
            }

            // Find the first available lane (no overlap)
            let laneIndex = 0;
            while (laneIndex < lanes.length) {
                const lane = lanes[laneIndex];
                let hasOverlap = false;

                for (const existingRes of lane) {
                    const existingStart = this.parseDateTime(existingRes.start_date_formated);
                    const existingEnd = this.parseDateTime(existingRes.end_date_formated);

                    // Check for overlap: reservation starts before existing ends and ends after existing starts
                    if (startDateTime < existingEnd && endDateTime > existingStart) {
                        hasOverlap = true;
                        break;
                    }
                }

                if (!hasOverlap) {
                    break;
                }
                laneIndex++;
            }

            // If no available lane found, create a new one
            if (laneIndex >= lanes.length) {
                lanes.push([]);
            }

            // Add reservation to the lane
            lanes[laneIndex].push(reservation);

            // Create and position the reservation block
            this.addReservationToRow(row, reservation, laneIndex);
        });

        // Adjust row height based on number of lanes (60px = --vehicle-item-height)
        if (lanes.length > 1) {
            row.style.height = `${lanes.length * 60}px`;
        }
    }

    addReservationToRow(row, reservation, laneIndex = 0) {
        const startDateTime = this.parseDateTime(reservation.start_date_formated);
        const endDateTime = this.parseDateTime(reservation.end_date_formated);

        // Check if reservation overlaps with current view
        const viewStart = this.currentStartDate;
        const viewEnd = new Date(this.currentStartDate);
        viewEnd.setDate(viewEnd.getDate() + this.currentDays);

        if (endDateTime < viewStart || startDateTime > viewEnd) {
            return; // Reservation outside view
        }

        const reservationBlock = document.createElement('div');
        reservationBlock.className = `reservation-block ${reservation.etat}`;
        reservationBlock.dataset.reservationId = reservation.id;

        // Calculate position and width based on hours
        const { left, width } = this.calculatePositionFromDateTime(startDateTime, endDateTime);

        reservationBlock.style.left = `${left}%`;
        reservationBlock.style.width = `${width}%`;

        // Position vertically based on lane index to prevent overlaps
        const laneHeight = 60; // Height of each lane in pixels (matches --vehicle-item-height)
        reservationBlock.style.top = `${laneIndex * laneHeight + 2}px`;
        reservationBlock.style.height = `${laneHeight - 4}px`; // Leave 4px margin
        reservationBlock.style.position = 'absolute';
        reservationBlock.style.zIndex = `${10 + laneIndex}`;

        // Apply the color from reservation data if available
        if (reservation.color) {
            reservationBlock.style.backgroundColor = reservation.color;
            const textColor = this.isColorLight(reservation.color) ? '#000000' : '#ffffff';
            reservationBlock.style.color = textColor;
        } else {
            reservationBlock.classList.add(reservation.etat);
        }

        reservationBlock.innerHTML = `
            <div class="reservation-text">${reservation.client} - ${reservation.reference}</div>
        `;

        // Add drag functionality
        // this.makeDraggable(reservationBlock, reservation);

        // Add right-click functionality
        reservationBlock.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.openEditModal(reservation);
        });
        reservationBlock.addEventListener('click', (e) => {
            e.stopPropagation();
            this.showReservationDetails(reservation, reservationBlock);
        });


        row.appendChild(reservationBlock);
    }

    calculatePositionFromDateTime(startDateTime, endDateTime) {
        const viewStart = this.currentStartDate;
        const totalDays = this.currentDays;
        const dayWidth = 100 / totalDays;

        // Calculate start position - days only for day-based view
        const startDayOffset = this.getDaysDifference(viewStart, startDateTime);
        const left = Math.max(0, Math.min(100, startDayOffset * dayWidth));

        // Calculate end position
        const endDayOffset = this.getDaysDifference(viewStart, endDateTime);
        const right = Math.min(100, Math.max(0, (endDayOffset + 1) * dayWidth));

        // Calculate width ensuring it doesn't exceed timeline bounds
        const width = Math.max(0, right - left);

        return { left, width };
    }

    makeDraggable(element, reservation) {
        element.draggable = false; // Use custom drag to avoid browser default

        element.addEventListener('mousedown', (e) => {
            e.preventDefault();
            this.startDrag(element, reservation, e);
        });

        element.addEventListener('click', (e) => {
            if (!this.draggedReservation) {
                e.stopPropagation();
                this.showReservationDetails(reservation, element);
            }
        });

        // Add right-click functionality
        element.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.openEditModal(reservation);
        });
    }

    startDrag(element, reservation, e) {
        this.draggedReservation = { element, reservation };

        const rect = element.getBoundingClientRect();
        this.dragOffset.x = e.clientX - rect.left;
        this.dragOffset.y = e.clientY - rect.top;

        element.classList.add('dragging');

        document.addEventListener('mousemove', this.onDragMove.bind(this));
        document.addEventListener('mouseup', this.onDragEnd.bind(this));

        // Prevent text selection during drag
        document.body.style.userSelect = 'none';
    }

    onDragMove(e) {
        if (!this.draggedReservation) return;

        const { element } = this.draggedReservation;
        const timelineContainer = document.querySelector('.timeline-container');
        const containerRect = timelineContainer.getBoundingClientRect();

        // Calculate relative position within timeline
        const relativeX = e.clientX - containerRect.left - this.dragOffset.x;
        const relativeY = e.clientY - containerRect.top - this.dragOffset.y;

        // Move element visually
        element.style.transform = `translate(${relativeX}px, ${relativeY}px)`;
        element.style.zIndex = '1000';

        // Highlight drop targets
        this.updateDropTargets(e);
    }

    updateDropTargets(e) {
        // Clear previous highlights
        document.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over'));
        document.querySelectorAll('.invalid-drop').forEach(el => el.classList.remove('invalid-drop'));

        const elementUnder = document.elementFromPoint(e.clientX, e.clientY);
        if (!elementUnder) return;

        const vehicleRow = elementUnder.closest('.vehicle-row');
        const dayCell = elementUnder.closest('.day-cell');

        if (vehicleRow && dayCell) {
            const targetVehicleId = parseInt(vehicleRow.dataset.vehicleId);
            const newPosition = this.calculateDropPosition(dayCell, e, vehicleRow);

            if (this.canDropReservation(targetVehicleId, newPosition)) {
                vehicleRow.classList.add('drag-over');
                dayCell.classList.add('drag-over');
            } else {
                this.draggedReservation.element.classList.add('invalid-drop');
            }
        }
    }

    calculateDropPosition(dayCell, e, vehicleRow) {
        const dayCells = Array.from(vehicleRow.querySelectorAll('.day-cell'));
        const dayIndex = dayCells.indexOf(dayCell);

        const cellRect = dayCell.getBoundingClientRect();
        const relativeX = e.clientX - cellRect.left;
        const hourProgress = relativeX / cellRect.width;

        const dropDate = new Date(this.currentStartDate);
        dropDate.setDate(dropDate.getDate() + dayIndex);

        const dropHour = this.startHour + (hourProgress * this.hoursPerDay);
        dropDate.setHours(Math.floor(dropHour), (dropHour % 1) * 60, 0, 0);

        return dropDate;
    }

    canDropReservation(targetVehicleId, newStartTime) {
        const { reservation } = this.draggedReservation;
        const durationMs = new Date(reservation.end_date_formated.replace(/(\d{2})-(\d{2})-(\d{4})/, '$3-$2-$1')) -
            new Date(reservation.start_date_formated.replace(/(\d{2})-(\d{2})-(\d{4})/, '$3-$2-$1'));
        const newEndTime = new Date(newStartTime.getTime() + durationMs);

        // Check for overlaps with other reservations
        const targetVehicleReservations = this.reservations.filter(r =>
            r.parent === targetVehicleId && r.id !== reservation.id
        );

        return !targetVehicleReservations.some(r => {
            const rStart = this.parseDateTime(r.start_date_formated);
            const rEnd = this.parseDateTime(r.end_date_formated);
            return (newStartTime < rEnd && newEndTime > rStart);
        });
    }

    onDragEnd(e) {
        if (!this.draggedReservation) return;

        const { element, reservation } = this.draggedReservation;

        // Clean up drag state
        element.classList.remove('dragging', 'invalid-drop');
        element.style.transform = '';
        element.style.zIndex = '';
        document.body.style.userSelect = '';

        // Clear highlights
        document.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over'));

        // Check if valid drop
        const elementUnder = document.elementFromPoint(e.clientX, e.clientY);
        const vehicleRow = elementUnder?.closest('.vehicle-row');
        const dayCell = elementUnder?.closest('.day-cell');

        if (vehicleRow && dayCell) {
            const targetVehicleId = parseInt(vehicleRow.dataset.vehicleId);
            const newPosition = this.calculateDropPosition(dayCell, e, vehicleRow);

            if (this.canDropReservation(targetVehicleId, newPosition)) {
                this.updateReservation(reservation, targetVehicleId, newPosition);
            }
        }

        // Clean up event listeners
        document.removeEventListener('mousemove', this.onDragMove.bind(this));
        document.removeEventListener('mouseup', this.onDragEnd.bind(this));

        this.draggedReservation = null;
    }

    updateReservation(reservation, newVehicleId, newStartTime) {
        const durationMs = new Date(reservation.end_date_formated.replace(/(\d{2})-(\d{2})-(\d{4})/, '$3-$2-$1')) -
            new Date(reservation.start_date_formated.replace(/(\d{2})-(\d{2})-(\d{4})/, '$3-$2-$1'));
        const newEndTime = new Date(newStartTime.getTime() + durationMs);

        // Update reservation data
        reservation.parent = newVehicleId;
        reservation.start_date_formated = this.formatDateTime(newStartTime);
        reservation.end_date_formated = this.formatDateTime(newEndTime);

        // Update vehicle assignment if changed
        if (reservation.vehicule) {
            const newVehicle = this.vehicles.find(v => v.id === newVehicleId);
            if (newVehicle) {
                reservation.immatriculation = newVehicle.text.split(' ').pop();
                reservation.vehicule.id = newVehicleId;
                reservation.vehicule.immatriculation = reservation.immatriculation;
            }
        }

        // Re-render timeline
        this.renderTimelineBody();

        console.log('Reservation updated:', reservation);
    }

    openEditModal(reservation) {
        console.log('Opening edit modal for reservation:', reservation);

        // Check if editResa function is available
        if (typeof window.editResa === 'function') {
            window.editResa(reservation);
        } else {
            console.error('editResa function not available');
            // Fallback to show reservation details modal
            this.showReservationModal(reservation);
        }
    }

    formatDateTime(date) {
        const day = date.getDate().toString().padStart(2, '0');
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const year = date.getFullYear();
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        return `${day}-${month}-${year} ${hours}:${minutes}`;
    }

    isColorLight(color) {
        // Convert hex color to RGB
        let r, g, b;
        if (color.match(/^rgb/)) {
            // If color is in rgb format
            const rgbValues = color.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)/);
            r = parseInt(rgbValues[1]);
            g = parseInt(rgbValues[2]);
            b = parseInt(rgbValues[3]);
        } else {
            // If color is in hex format
            const hex = color.replace('#', '');
            r = parseInt(hex.substr(0, 2), 16);
            g = parseInt(hex.substr(2, 2), 16);
            b = parseInt(hex.substr(4, 2), 16);
        }

        // Calculate luminance
        const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        return luminance > 0.5;
    }

    showReservationDetails(reservation, reservationElement) {
        // Remove any existing detail elements
        const existingDetails = document.querySelectorAll('.reservation-details');
        existingDetails.forEach(el => el.remove());

        // Create details element
        const detailsElement = document.createElement('div');
        detailsElement.className = 'reservation-details';
        detailsElement.style.position = 'absolute';
        detailsElement.style.zIndex = '10';
        detailsElement.style.marginTop = '5px';
        detailsElement.style.padding = '10px';
        detailsElement.style.border = '1px solid #ccc';
        detailsElement.style.backgroundColor = 'white';
        detailsElement.style.borderRadius = '4px';
        detailsElement.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
        detailsElement.style.fontSize = '0.8rem';
        detailsElement.style.width = '300px';
        detailsElement.style.left = '0';
        detailsElement.style.top = '100%';

        // Create content for the details
        const url = `/backoffice/reservation/details/${reservation.id_r}`;
        detailsElement.innerHTML = `
            Référence : <a class='' href="${url}" style="color: #007bff; text-decoration: underline;">${reservation.reference}</a> <br>
            Agence de départ : ${reservation.agenceDepart}<br>
            Date de départ : ${reservation.start_date_formated}<br>
            Agence de retour : ${reservation.agenceRetour}<br>
            Date de retour : ${reservation.end_date_formated}<br>
            Téléphone : ${reservation.telClient}<br>
        `;

        // Add the details element to the parent of the reservation block
        reservationElement.parentElement.appendChild(detailsElement);

        // Position the details element correctly
        const rect = reservationElement.getBoundingClientRect();
        const containerRect = reservationElement.parentElement.getBoundingClientRect();
        const top = reservationElement.offsetTop + reservationElement.offsetHeight;
        const left = reservationElement.offsetLeft;

        detailsElement.style.top = `${top + 5}px`;
        detailsElement.style.left = `${left}px`;

        // Close details when clicking elsewhere
        const closeDetails = (e) => {
            if (!detailsElement.contains(e.target) && !reservationElement.contains(e.target)) {
                detailsElement.remove();
                document.removeEventListener('click', closeDetails);
            }
        };

        setTimeout(() => {
            document.addEventListener('click', closeDetails);
        }, 0);
    }

    parseDate(dateString) {
        // Parse "dd-mm-yyyy hh:mm" format
        const [datePart] = dateString.split(' ');
        const [day, month, year] = datePart.split('-');
        return new Date(year, month - 1, day);
    }

    parseDateTime(dateTimeString) {
        // Parse "dd-mm-yyyy hh:mm" format
        const [datePart, timePart] = dateTimeString.split(' ');
        const [day, month, year] = datePart.split('-');
        const [hours, minutes] = (timePart || '00:00').split(':');
        return new Date(year, month - 1, day, parseInt(hours), parseInt(minutes));
    }

    getDaysDifference(date1, date2) {
        const timeDiff = date2.getTime() - date1.getTime();
        return Math.floor(timeDiff / (1000 * 3600 * 24));
    }

    showReservationModal(reservation) {
        const modal = new bootstrap.Modal(document.getElementById('reservationModal'));
        const modalBody = document.getElementById('modalBody');

        modalBody.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <strong>Référence:</strong> ${reservation.reference}<br>
                    <strong>Client:</strong> ${reservation.client}<br>
                    <strong>Véhicule:</strong> ${reservation.immatriculation}<br>
                    <strong>État:</strong> <span class="badge bg-${this.getStatusColor(reservation.etat)}">${reservation.etat}</span>
                </div>
                <div class="col-md-6">
                    <strong>Date début:</strong> ${reservation.start_date_formated}<br>
                    <strong>Date fin:</strong> ${reservation.end_date_formated}<br>
                    <strong>Agence départ:</strong> ${reservation.agenceDepart}<br>
                    <strong>Agence retour:</strong> ${reservation.agenceRetour}
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <strong>Prix:</strong> ${reservation.tarifResa}€<br>
                    <strong>Téléphone:</strong> ${reservation.telClient}
                </div>
            </div>
        `;

        document.getElementById('editReservation').onclick = () => {
            window.location.href = `/backoffice/reservation/details/${reservation.id_r}`;
        };

        modal.show();
    }

    getStatusColor(status) {
        const colors = {
            'nouvelle': 'success',
            'encours': 'warning',
            'termine': 'secondary',
            'stopSale': 'danger'
        };
        return colors[status] || 'primary';
    }

    toggleVehicle(vehicleId, selected) {
        if (selected) {
            this.selectedVehicles.add(vehicleId);
        } else {
            this.selectedVehicles.delete(vehicleId);
        }
        // Re-render lists to move vehicle between sections
        this.renderVehicleList();
        // Update timeline to show/hide row
        this.updateTimeline();
        this.updateSelectAllCheckbox();
    }

    toggleAllVehicles(selectAll) {
        const visibleVehicles = this.getVisibleVehicles();

        if (selectAll) {
            visibleVehicles.forEach(v => this.selectedVehicles.add(v.id));
        } else {
            visibleVehicles.forEach(v => this.selectedVehicles.delete(v.id));
        }

        // Re-render lists to move vehicles between sections
        this.renderVehicleList();
        this.updateTimeline();
    }

    filterVehicles() {
        // Re-render the list which applies the search filter
        this.renderVehicleList();
        this.updateTimeline(); // Also update timeline to filter rows
        this.updateSelectAllCheckbox();
    }

    getVisibleVehicles() {
        return this.vehicles.filter(vehicle => {
            const vehicleName = vehicle.text.toLowerCase();
            return vehicleName.includes(this.vehicleSearch);
        });
    }

    updateSelectAllCheckbox() {
        const visibleVehicles = this.getVisibleVehicles();
        const selectedVisible = visibleVehicles.filter(v => this.selectedVehicles.has(v.id));

        const selectAllCheckbox = document.getElementById('selectAll');
        selectAllCheckbox.checked = selectedVisible.length === visibleVehicles.length && visibleVehicles.length > 0;
        selectAllCheckbox.indeterminate = selectedVisible.length > 0 && selectedVisible.length < visibleVehicles.length;
    }

    updatePeriodDisplay() {
        const endDate = new Date(this.currentStartDate);
        endDate.setDate(endDate.getDate() + this.currentDays - 1);

        const startStr = this.currentStartDate.toLocaleDateString('fr-FR');
        const endStr = endDate.toLocaleDateString('fr-FR');

        document.getElementById('periodText').textContent = `Du ${startStr} au ${endStr}`;
    }

    reset() {
        this.currentStartDate = new Date();
        this.currentDays = 6;
        this.vehicleSearch = '';

        document.getElementById('startDate').value = this.currentStartDate.toISOString().split('T')[0];
        document.getElementById('vehicleSearch').value = '';

        document.querySelectorAll('.btn-view').forEach(btn => btn.classList.remove('active'));
        document.querySelector('.btn-view[data-days="6"]').classList.add('active');

        this.selectedVehicles = new Set(this.vehicles.map(v => v.id));
        this.renderVehicleList();
        this.updateTimeline();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ModernPlanning();
});