// ===== CAR BOOKING TIMELINE APPLICATION =====

class CarBookingTimeline {
    constructor() {
        // Data
        this.cars = [];
        this.bookings = [];
        this.filteredCars = [];

        // Timeline state
        this.currentViewStart = new Date();
        this.currentViewEnd = new Date();
        this.zoomLevel = 1;
        this.minZoom = 0.5;
        this.maxZoom = 5;
        this.timelineWidth = 0;

        // UI state
        this.selectedCar = null;
        this.selectedBooking = null;
        this.isCarListVisible = true;
        this.carSearchTerm = '';

        // Configuration
        this.config = {
            dayWidth: 60, // Base day width
            hourWidth: 2.5, // Base hour width
            minBarWidth: 40,
            maxBarWidth: 400,
            carItemHeight: 60,
            timelineHeaderHeight: 60
        };

        this.init();
    }

    init() {
        this.loadDataFromBackend();
        this.setupEventListeners();
        this.setupDefaultView();
    }

    // ===== DATA MANAGEMENT =====
    async loadDataFromBackend() {
        try {
            const response = await fetch(window.PLANNING_DATA_URL || '/planningGeneralData');
            if (!response.ok) {
                throw new Error('Failed to fetch planning data');
            }

            const data = await response.json();
            this.processBackendData(data);
            this.renderAll();
            this.startAutoRefresh();
        } catch (error) {
            console.error('Error loading data:', error);
            this.showToast('Erreur de chargement des données', 'error');
        }
    }

    processBackendData(rawData) {
        // Separate vehicles and reservations based on parent property
        const vehicles = rawData.filter(item => item.parent === 0);
        const reservations = rawData.filter(item => item.parent !== 0);

        // Generate random colors for vehicles
        const colors = ['#3B82F6', '#10B981', '#8B5CF6', '#F59E0B', '#EF4444', '#06B6D4', '#84CC16', '#F97316'];

        // Transform vehicles to our format
        this.cars = vehicles.map((vehicle, index) => ({
            id: vehicle.id,
            name: vehicle.text,
            model: vehicle.marque_modele || '',
            plate: vehicle.text.split(' ').pop() || '', // Extract immatriculation from text
            category: vehicle.marque_modele ? vehicle.marque_modele.split(' ')[0] : 'Voiture',
            status: 'available',
            color: colors[index % colors.length],
            customer: null,
            nextBooking: null
        }));

        // Transform reservations to our format
        this.bookings = reservations.map(reservation => {
            // Parse date format "dd-mm-yyyy hh:mm" to Date object
            const parseDate = (dateStr) => {
                const [datePart, timePart] = dateStr.split(' ');
                const [day, month, year] = datePart.split('-');
                const [hours, minutes] = timePart.split(':');
                return new Date(year, month - 1, day, hours, minutes);
            };

            // Map status from French to English
            const statusMap = {
                'nouvelle': 'confirmed',
                'encours': 'confirmed',
                'termine': 'completed',
                'stopSale': 'cancelled'
            };

            return {
                id: reservation.id_r,
                carId: reservation.parent,
                customerName: reservation.client,
                customerPhone: reservation.telClient || '',
                customerEmail: '',
                startDate: parseDate(reservation.start_date_formated),
                endDate: parseDate(reservation.end_date_formated),
                status: statusMap[reservation.etat] || 'confirmed',
                pickupLocation: reservation.agenceDepart || '',
                dropoffLocation: reservation.agenceRetour || '',
                notes: reservation.reference || '',
                price: reservation.tarifResa || 0,
                reference: reservation.reference,
                immatriculation: reservation.immatriculation,
                createdAt: new Date()
            };
        });

        this.filteredCars = [...this.cars];
    }

    // ===== TIMELINE SETUP =====
    setupDefaultView() {
        const today = new Date();
        this.currentViewStart = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 7);
        this.currentViewEnd = new Date(today.getFullYear(), today.getMonth(), today.getDate() + 30);
        this.zoomLevel = 1;
    }

    // ===== RENDERING =====
    renderAll() {
        this.renderStats();
        this.renderCarList();
        this.renderTimeline();
        this.renderBookingBars();
        this.updateDateRangeDisplay();
    }

    renderStats() {
        document.getElementById('totalCars').textContent = this.cars.length;

        const activeBookings = this.bookings.filter(booking =>
            booking.status === 'confirmed' || booking.status === 'pending'
        ).length;
        document.getElementById('activeBookings').textContent = activeBookings;

        // Calculate utilization (simplified)
        const occupiedCars = this.cars.filter(car => car.status === 'occupied').length;
        const utilization = Math.round((occupiedCars / this.cars.length) * 100);
        document.getElementById('utilization').textContent = `${utilization}%`;
    }

    renderCarList() {
        const carList = document.getElementById('carList');
        carList.innerHTML = '';

        this.filteredCars.forEach(car => {
            const carElement = document.createElement('div');
            carElement.className = `car-item ${this.selectedCar === car.id ? 'selected' : ''}`;
            carElement.dataset.carId = car.id;

            const isOccupied = this.isCarOccupied(car.id);
            const statusClass = isOccupied ? 'occupied' : car.status;

            carElement.innerHTML = `
                <div class="car-avatar" style="background: ${car.color}">
                    ${car.name.charAt(0)}
                </div>
                <div class="car-info">
                    <div class="car-name">${car.name}</div>
                    <div class="car-details">
                        <span>${car.plate}</span>
                        <span>${car.category}</span>
                    </div>
                </div>
                <div class="car-status ${statusClass}">
                    ${isOccupied ? 'Booked' : car.status}
                </div>
            `;

            carElement.addEventListener('click', () => {
                this.selectCar(car.id);
            });

            carList.appendChild(carElement);
        });
    }

    renderTimeline() {
        this.renderTimelineDates();
        this.renderTimelineGrid();
    }

    renderTimelineDates() {
        const datesContainer = document.getElementById('timelineDates');
        datesContainer.innerHTML = '';

        // Calculate the total number of days in the range
        const totalDays = this.daysBetween(this.currentViewStart, this.currentViewEnd) + 1;
        const availableWidth = this.getAvailableTimelineWidth();
        const dayWidth = availableWidth / totalDays;

        // Set container width to available width instead of calculated timeline width
        datesContainer.style.width = `${availableWidth}px`;

        const currentDate = new Date(this.currentViewStart);
        let dayIndex = 0;

        while (currentDate <= this.currentViewEnd) {
            const dateElement = document.createElement('div');
            dateElement.className = 'date-header';

            // Set width per day to fill the available space
            dateElement.style.minWidth = `${dayWidth}px`;

            if (this.isToday(currentDate)) {
                dateElement.classList.add('today');
            }

            if (currentDate.getDay() === 0 || currentDate.getDay() === 6) {
                dateElement.classList.add('weekend');
            }

            const day = currentDate.getDate();
            const weekday = currentDate.toLocaleDateString('fr-FR', { weekday: 'short' });
            const month = currentDate.toLocaleDateString('fr-FR', { month: 'short' });

            dateElement.innerHTML = `
                <div class="date-day">${day}</div>
                <div class="date-weekday">${weekday}</div>
                <div class="date-month">${month}</div>
            `;

            dateElement.addEventListener('click', () => {
                this.selectDate(currentDate);
            });

            datesContainer.appendChild(dateElement);
            currentDate.setDate(currentDate.getDate() + 1);
            dayIndex++;
        }
    }

    renderTimelineGrid() {
        const gridContainer = document.getElementById('timelineGrid');
        const gridContent = document.createElement('div');

        // Calculate the total number of days in the range
        const totalDays = this.daysBetween(this.currentViewStart, this.currentViewEnd) + 1;
        const availableWidth = this.getAvailableTimelineWidth();
        const dayWidth = availableWidth / totalDays;

        gridContent.className = 'grid-content';
        gridContent.style.width = `${availableWidth}px`;
        gridContent.style.height = `${this.filteredCars.length * this.config.carItemHeight}px`;

        // Create horizontal grid lines for each vehicle row
        for (let i = 0; i <= this.filteredCars.length; i++) {
            const hLine = document.createElement('div');
            hLine.className = 'grid-h-line';
            hLine.style.top = `${i * this.config.carItemHeight}px`;
            hLine.style.width = '100%';
            gridContent.appendChild(hLine);
        }

        // Create vertical grid lines
        const currentDate = new Date(this.currentViewStart);
        let dayIndex = 0;

        while (currentDate <= this.currentViewEnd) {
            // Calculate position based on available width and day index
            const x = dayIndex * dayWidth;
            const vLine = document.createElement('div');
            vLine.className = 'grid-line';

            // Weekend styling
            if (currentDate.getDay() === 0 || currentDate.getDay() === 6) {
                vLine.classList.add('weekend');
            }

            // Major lines (every 7 days)
            if (currentDate.getDate() === 1 || this.daysBetween(new Date(this.currentViewStart), currentDate) % 7 === 0) {
                vLine.classList.add('major');
            }

            vLine.style.left = `${x}px`;
            gridContent.appendChild(vLine);

            currentDate.setDate(currentDate.getDate() + 1);
            dayIndex++;
        }

        // Clear and append all content to grid container
        gridContainer.innerHTML = '';
        gridContainer.appendChild(gridContent);

        // The booking container is a separate element, we just need to position it correctly
        const bookingContainer = document.getElementById('bookingContainer');
        if (bookingContainer) {
            bookingContainer.style.width = `${availableWidth}px`;
            bookingContainer.style.height = `${this.filteredCars.length * this.config.carItemHeight}px`;
        }
    }

    renderBookingBars() {
        const bookingContainer = document.getElementById('bookingContainer');
        bookingContainer.innerHTML = '';
        // Use the available width instead of calculated timeline width
        bookingContainer.style.width = `${this.getAvailableTimelineWidth()}px`;
        // Set the height to match the grid content
        bookingContainer.style.height = `${this.filteredCars.length * this.config.carItemHeight}px`;

        // Filter bookings by current timeline
        const visibleBookings = this.bookings.filter(booking =>
            this.isBookingVisible(booking) &&
            (this.selectedCar === null || booking.carId === this.selectedCar)
        );

        visibleBookings.forEach(booking => {
            const bookingBar = this.createBookingBar(booking);
            if (bookingBar) {
                bookingContainer.appendChild(bookingBar);
            }
        });
    }

    createBookingBar(booking) {
        const car = this.cars.find(c => c.id === booking.carId);
        if (!car) return null;

        const bar = document.createElement('div');
        bar.className = `booking-bar ${booking.status}`;

        // Calculate position and size using the scaled timeline
        const startX = this.getDatePosition(booking.startDate);
        const endX = this.getDatePosition(booking.endDate);
        const carIndex = this.filteredCars.findIndex(c => c.id === booking.carId);

        // Ensure the booking bar is properly positioned within the visible range
        const left = startX;
        const width = Math.max(endX - startX, this.config.minBarWidth);

        // Position booking bar vertically aligned with the corresponding car
        // Calculate vertical position based on car index and item height
        const top = carIndex * this.config.carItemHeight;

        // Ensure top is not negative
        const safeTop = Math.max(0, top);

        bar.style.left = `${left}px`;
        bar.style.width = `${width}px`;
        bar.style.top = `${safeTop}px`;
        bar.style.height = `${this.config.carItemHeight - 2}px`; // Match vehicle row height minus horizontal line

        const duration = this.formatBookingDuration(booking.startDate, booking.endDate);
        const customerName = booking.customerName.split(' ')[0]; // First name only

        bar.innerHTML = `
            <div class="booking-customer">${customerName}</div>
            <div class="booking-time">${duration}</div>
        `;

        // Add click handler
        bar.addEventListener('click', (e) => {
            e.stopPropagation();
            this.showBookingDetails(booking);
        });

        return bar;
    }

    updateDateRangeDisplay() {
        const start = this.formatDateDisplay(this.currentViewStart);
        const end = this.formatDateDisplay(this.currentViewEnd);
        document.getElementById('dateRangeDisplay').textContent = `${start} - ${end}`;
    }

    // ===== INTERACTION METHODS =====
    selectCar(carId) {
        this.selectedCar = this.selectedCar === carId ? null : carId;
        this.renderCarList();
        this.renderBookingBars();
    }

    selectDate(date) {
        // Center timeline on selected date
        const daysDiff = this.daysBetween(this.currentViewStart, date);
        const centerOffset = (this.getTimelineWidth() / 2) - (this.config.dayWidth * this.zoomLevel / 2);

        // Scroll to center on date
        const timelineGrid = document.getElementById('timelineGrid');
        timelineGrid.scrollLeft = Math.max(0, this.getDatePosition(date) - centerOffset);
    }

    zoomIn() {
        this.zoomLevel = Math.min(this.maxZoom, this.zoomLevel * 1.5);
        this.renderTimeline();
        this.renderBookingBars();
    }

    zoomOut() {
        this.zoomLevel = Math.max(this.minZoom, this.zoomLevel / 1.5);
        this.renderTimeline();
        this.renderBookingBars();
    }

    resetZoom() {
        this.zoomLevel = 1;
        this.setupDefaultView();
        this.renderTimeline();
        this.renderBookingBars();
        this.updateDateRangeDisplay();
    }

    setQuickRange(days) {
        const today = new Date();
        this.currentViewStart = new Date(today.getFullYear(), today.getMonth(), today.getDate() - Math.floor(days / 2));
        this.currentViewEnd = new Date(today.getFullYear(), today.getMonth(), today.getDate() + Math.floor(days / 2));

        // Update active button
        document.querySelectorAll('.quick-btn').forEach(btn => {
            btn.classList.toggle('active', parseInt(btn.dataset.range) === days);
        });

        this.renderTimeline();
        this.renderBookingBars();
        this.updateDateRangeDisplay();
    }

    // ===== BOOKING MANAGEMENT =====
    showBookingModal() {
        const modal = document.getElementById('bookingModal');
        const carSelect = document.getElementById('bookingCar');

        // Populate car options
        carSelect.innerHTML = '<option value="">Select Car</option>';
        this.filteredCars.forEach(car => {
            const option = document.createElement('option');
            option.value = car.id;
            option.textContent = `${car.name} (${car.plate})`;
            carSelect.appendChild(option);
        });

        // Set smart default dates based on current view
        const startInput = document.getElementById('bookingStart');
        const endInput = document.getElementById('bookingEnd');

        // Default start: tomorrow at 10 AM
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setHours(10, 0, 0, 0);

        // Default end: 3 days later at 10 AM
        const defaultEnd = new Date(tomorrow);
        defaultEnd.setDate(defaultEnd.getDate() + 3);
        defaultEnd.setHours(10, 0, 0, 0);

        startInput.value = this.formatDateTimeForInput(tomorrow);
        endInput.value = this.formatDateTimeForInput(defaultEnd);

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    hideBookingModal() {
        const modal = document.getElementById('bookingModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
        document.getElementById('bookingForm').reset();
        this.clearErrors();
    }

    showBookingDetails(booking) {
        const modal = document.getElementById('bookingDetailsModal');
        const content = document.getElementById('bookingDetailsContent');
        const car = this.cars.find(c => c.id === booking.carId);

        const duration = this.formatBookingDuration(booking.startDate, booking.endDate);

        content.innerHTML = `
            <div class="booking-details">
                <h4>Booking Information</h4>
                <div class="detail-row">
                    <strong>Customer:</strong> ${booking.customerName}
                </div>
                <div class="detail-row">
                    <strong>Car:</strong> ${car ? car.name : 'Unknown'} (${car ? car.plate : 'N/A'})
                </div>
                <div class="detail-row">
                    <strong>Period:</strong> ${this.formatDateTime(booking.startDate)} - ${this.formatDateTime(booking.endDate)}
                </div>
                <div class="detail-row">
                    <strong>Duration:</strong> ${duration}
                </div>
                <div class="detail-row">
                    <strong>Status:</strong> <span class="status-badge status-${booking.status}">${booking.status}</span>
                </div>
                <div class="detail-row">
                    <strong>Price:</strong> $${booking.price}
                </div>
                <div class="detail-row">
                    <strong>Pickup:</strong> ${booking.pickupLocation}
                </div>
                <div class="detail-row">
                    <strong>Dropoff:</strong> ${booking.dropoffLocation}
                </div>
                ${booking.notes ? `<div class="detail-row"><strong>Notes:</strong> ${booking.notes}</div>` : ''}
            </div>
        `;

        // Store booking ID for actions
        modal.dataset.bookingId = booking.id;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    hideBookingDetailsModal() {
        const modal = document.getElementById('bookingDetailsModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    addBooking(bookingData) {
        const newBooking = {
            id: Date.now(),
            ...bookingData,
            createdAt: new Date()
        };

        this.bookings.push(newBooking);

        // Ensure the booking is visible in the current timeline
        if (!this.isBookingVisible(newBooking)) {
            // Expand timeline to include the new booking
            const bufferDays = 2;
            const start = new Date(Math.min(this.currentViewStart, newBooking.startDate));
            const end = new Date(Math.max(this.currentViewEnd, newBooking.endDate));

            start.setDate(start.getDate() - bufferDays);
            end.setDate(end.getDate() + bufferDays);

            this.currentViewStart = start;
            this.currentViewEnd = end;

            // Auto-fit width to show the new booking
            this.autoFitWidth();
        }

        // Refresh the view
        this.renderTimeline();
        this.renderBookingBars();
        this.renderStats();
        this.updateDateRangeDisplay();

        this.showToast('Booking added successfully!', 'success');
        this.hideBookingModal();
    }

    deleteCurrentBooking() {
        const modal = document.getElementById('bookingDetailsModal');
        const bookingId = parseInt(modal.dataset.bookingId);

        if (confirm('Are you sure you want to delete this booking?')) {
            this.bookings = this.bookings.filter(b => b.id !== bookingId);
            this.renderBookingBars();
            this.renderStats();
            this.hideBookingDetailsModal();
            this.showToast('Booking deleted successfully!', 'success');
        }
    }

    // ===== UTILITY METHODS =====
    getAvailableTimelineWidth() {
        // Get the actual available width of the timeline container
        const timelinePanel = document.querySelector('.timeline-panel');
        return timelinePanel ? timelinePanel.clientWidth - 1 : 1200; // -1 for border
    }

    getTimelineWidth() {
        const days = this.daysBetween(this.currentViewStart, this.currentViewEnd) + 1;

        // Get the actual available width of the timeline container
        const availableWidth = this.getAvailableTimelineWidth();

        // Calculate required width based on days and zoom level
        const requiredWidth = days * this.config.dayWidth * this.zoomLevel;

        // Use the maximum of required width and available width to ensure full coverage
        return Math.max(requiredWidth, availableWidth);
    }

    getDatePosition(date) {
        const totalDays = this.daysBetween(this.currentViewStart, this.currentViewEnd) + 1;
        const availableWidth = this.getAvailableTimelineWidth();
        const dayWidth = availableWidth / totalDays;

        const daysDiff = this.daysBetween(this.currentViewStart, date);
        return daysDiff * dayWidth;
    }

    daysBetween(start, end) {
        const startTime = new Date(start.getFullYear(), start.getMonth(), start.getDate());
        const endTime = new Date(end.getFullYear(), end.getMonth(), end.getDate());
        return Math.round((endTime - startTime) / (1000 * 60 * 60 * 24));
    }

    isBookingVisible(booking) {
        return booking.endDate >= this.currentViewStart && booking.startDate <= this.currentViewEnd;
    }

    isCarOccupied(carId) {
        const now = new Date();
        return this.bookings.some(booking =>
            booking.carId === carId &&
            booking.status !== 'cancelled' &&
            booking.startDate <= now &&
            booking.endDate >= now
        );
    }

    isToday(date) {
        const today = new Date();
        return date.toDateString() === today.toDateString();
    }

    formatDateDisplay(date) {
        return date.toLocaleDateString('fr-FR', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    formatDateTime(date) {
        return date.toLocaleDateString('fr-FR', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    formatBookingDuration(start, end) {
        const diffDays = this.daysBetween(start, end);
        const diffHours = Math.round((end - start) / (1000 * 60 * 60));

        if (diffDays > 0) {
            return `${diffDays}d ${diffHours - (diffDays * 24)}h`;
        } else {
            return `${diffHours}h`;
        }
    }

    formatDateTimeForInput(date) {
        // Format date for datetime-local input
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    searchCars(query) {
        this.carSearchTerm = query.toLowerCase();
        this.filteredCars = this.cars.filter(car =>
            car.name.toLowerCase().includes(this.carSearchTerm) ||
            car.plate.toLowerCase().includes(this.carSearchTerm) ||
            car.category.toLowerCase().includes(this.carSearchTerm)
        );
        this.renderCarList();
        this.renderTimelineGrid();
        this.renderBookingBars();
    }

    // ===== VALIDATION =====
    validateBookingForm(formData) {
        const errors = {};

        if (!formData.car) {
            errors.car = 'Please select a car';
        }

        if (!formData.customer || formData.customer.trim().length < 2) {
            errors.customer = 'Customer name must be at least 2 characters';
        }

        if (!formData.start) {
            errors.start = 'Start date is required';
        }

        if (!formData.end) {
            errors.end = 'End date is required';
        }

        if (formData.start && formData.end) {
            const start = new Date(formData.start);
            const end = new Date(formData.end);

            if (start >= end) {
                errors.end = 'End date must be after start date';
            }

            if (start < new Date()) {
                errors.start = 'Start date cannot be in the past';
            }
        }

        return errors;
    }

    showErrors(errors) {
        this.clearErrors();

        Object.keys(errors).forEach(field => {
            const input = document.getElementById(`booking${this.capitalizeFirst(field)}`);
            const errorElement = document.getElementById(`${field}Error`);

            if (input) {
                input.classList.add('error');
            }
            if (errorElement) {
                errorElement.textContent = errors[field];
            }
        });
    }

    clearErrors() {
        const inputs = document.querySelectorAll('#bookingForm input, #bookingForm select, #bookingForm textarea');
        const errors = document.querySelectorAll('.error-message');

        inputs.forEach(input => input.classList.remove('error'));
        errors.forEach(error => error.textContent = '');
    }

    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // ===== EVENT LISTENERS =====
    setupEventListeners() {
        // Header controls
        document.getElementById('zoomInBtn').addEventListener('click', () => this.zoomIn());
        document.getElementById('zoomOutBtn').addEventListener('click', () => this.zoomOut());
        document.getElementById('resetZoomBtn').addEventListener('click', () => this.resetZoom());

        // Quick range buttons
        document.querySelectorAll('.quick-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const days = parseInt(btn.dataset.range);
                this.setQuickRange(days);
            });
        });

        // Search functionality
        document.getElementById('carSearchInput').addEventListener('input', (e) => {
            this.searchCars(e.target.value);
        });

        // Add booking
        document.getElementById('addBookingBtn').addEventListener('click', () => {
            this.showBookingModal();
        });

        // Modal controls
        document.getElementById('modalClose').addEventListener('click', () => {
            this.hideBookingModal();
        });

        document.getElementById('cancelBookingBtn').addEventListener('click', () => {
            this.hideBookingModal();
        });

        document.getElementById('detailsModalClose').addEventListener('click', () => {
            this.hideBookingDetailsModal();
        });

        // Form submission
        document.getElementById('bookingForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleBookingSubmit();
        });

        // Booking actions
        document.getElementById('deleteBookingBtn').addEventListener('click', () => {
            this.deleteCurrentBooking();
        });

        // Close modals on overlay click
        document.getElementById('bookingModal').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                this.hideBookingModal();
            }
        });

        document.getElementById('bookingDetailsModal').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                this.hideBookingDetailsModal();
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hideBookingModal();
                this.hideBookingDetailsModal();
            }
            if (e.ctrlKey && e.key === '=') {
                e.preventDefault();
                this.zoomIn();
            }
            if (e.ctrlKey && e.key === '-') {
                e.preventDefault();
                this.zoomOut();
            }
        });

        // Timeline interactions
        const timelineGrid = document.getElementById('timelineGrid');
        timelineGrid.addEventListener('scroll', () => {
            // Sync scroll with date headers
            const datesContainer = document.getElementById('timelineDates');
            datesContainer.scrollLeft = timelineGrid.scrollLeft;
        });
    }

    handleBookingSubmit() {
        const formData = new FormData(document.getElementById('bookingForm'));
        const bookingData = {
            carId: parseInt(formData.get('car')),
            customerName: formData.get('customer'),
            startDate: new Date(formData.get('start')),
            endDate: new Date(formData.get('end')),
            status: formData.get('status'),
            pickupLocation: 'Main Office',
            dropoffLocation: 'Main Office',
            notes: formData.get('notes') || '',
            price: 300 // Default price
        };

        const errors = this.validateBookingForm(Object.fromEntries(formData.entries()));

        if (Object.keys(errors).length > 0) {
            this.showErrors(errors);
            this.showToast('Please fix the errors in the form', 'error');
            return;
        }

        this.addBooking(bookingData);
    }

    // ===== TOAST NOTIFICATIONS =====
    showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;

        const icons = {
            success: '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22,4 12,14.01 9,11.01"/>',
            warning: '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
            error: '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>'
        };

        toast.innerHTML = `
            <div class="toast-content">
                <div class="toast-title">${type === 'success' ? 'Success' : type === 'error' ? 'Error' : 'Warning'}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        `;

        toastContainer.appendChild(toast);

        // Show toast with animation
        setTimeout(() => toast.classList.add('show'), 100);

        // Auto remove after 4 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // ===== AUTO REFRESH =====
    startAutoRefresh() {
        setInterval(() => {
            this.renderStats();
            this.renderCarList();
            this.renderBookingBars();
        }, 30000); // Refresh every 30 seconds
    }
}

// ===== INITIALIZATION =====
let carBookingTimeline;

document.addEventListener('DOMContentLoaded', () => {
    carBookingTimeline = new CarBookingTimeline();
});

// ===== GLOBAL ACCESS =====
window.carBookingTimeline = carBookingTimeline;