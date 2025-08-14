class ReservationPhotoUploader {
    constructor(options) {
        this.reservationId = options.reservationId;
        this.dropZone = document.getElementById(options.dropZoneId);
        this.fileInput = document.getElementById(options.fileInputId);
        this.photosContainer = document.getElementById(options.photosContainerId);
        this.progressBar = document.getElementById(options.progressBarId);
        this.messageContainer = document.getElementById(options.messageContainerId);

        this.uploadUrl = `/backoffice/reservation/${this.reservationId}/photos/upload`;
        this.deleteUrl = '/backoffice/reservation/photo/{id}/delete';

        this.init();
        this.loadExistingPhotos();
    }

    init() {
        // Drag & Drop events
        this.dropZone.addEventListener('dragover', (e) => this.handleDragOver(e));
        this.dropZone.addEventListener('dragleave', (e) => this.handleDragLeave(e));
        this.dropZone.addEventListener('drop', (e) => this.handleDrop(e));
        this.dropZone.addEventListener('click', () => this.fileInput.click());

        // File input change
        this.fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
    }

    handleDragOver(e) {
        e.preventDefault();
        this.dropZone.classList.add('dragover');
    }

    handleDragLeave(e) {
        e.preventDefault();
        this.dropZone.classList.remove('dragover');
    }

    handleDrop(e) {
        e.preventDefault();
        this.dropZone.classList.remove('dragover');

        const files = Array.from(e.dataTransfer.files);
        this.uploadFiles(files);
    }

    handleFileSelect(e) {
        const files = Array.from(e.target.files);
        this.uploadFiles(files);
    }

    async uploadFiles(files) {
        if (files.length === 0) return;

        const imageFiles = files.filter(file => file.type.startsWith('image/'));

        if (imageFiles.length === 0) {
            this.showMessage('Veuillez sélectionner uniquement des images', 'error');
            return;
        }

        this.showProgress(true);

        const formData = new FormData();
        imageFiles.forEach(file => {
            formData.append('photos[]', file);
        });

        try {
            const response = await fetch(this.uploadUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                this.showMessage(result.message, 'success');
                this.loadExistingPhotos();
            } else {
                this.showMessage(result.error || 'Erreur lors de l\'upload', 'error');
            }
        } catch (error) {
            console.error('Upload error:', error);
            this.showMessage('Erreur de connexion', 'error');
        } finally {
            this.showProgress(false);
            this.fileInput.value = '';
        }
    }

    async loadExistingPhotos() {
        try {
            const response = await fetch(`/backoffice/reservation/${this.reservationId}/photos`);
            const result = await response.json();

            // Assuming result.photos is an array of {id, url, name}
            this.displayPhotos(result.photos);
        } catch (error) {
            console.error('Error loading photos:', error);
        }
    }

    displayPhotos(photos) {
        this.photosContainer.innerHTML = '';

        if (photos.length === 0) {
            this.photosContainer.innerHTML = '<div class="col-12 text-center text-muted py-4">Aucune photo uploadée</div>';
            return;
        }

        photos.forEach(photo => {
            const photoElement = this.createPhotoElement(photo);
            this.photosContainer.appendChild(photoElement);
        });
    }

    createPhotoElement(photo) {
        const colDiv = document.createElement('div');
        colDiv.className = 'col-md-3 col-sm-4 col-6 mb-3 d-flex';

        // MODIFIED: Added card-body for the name and adjusted structure
        colDiv.innerHTML = `
            <div class="card photo-card h-100 w-100">
                <div class="photo-wrapper position-relative">
                    <img src="${photo.url}" alt="Photo de la réservation" 
                         class="card-img-top photo-thumbnail" 
                         style="cursor: pointer;">
                    <div class="photo-overlay position-absolute w-100 h-100 d-flex align-items-center justify-content-center">
                        <button type="button" class="btn btn-danger btn-sm me-2 btn-delete" data-photo-id="${photo.id}">
                            <i class="fa fa-trash"></i>
                        </button>
                        <button type="button" class="btn btn-primary btn-sm btn-fullscreen">
                            <i class="fa fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-2 text-center">
                    <small class="card-text text-muted text-truncate d-block">${photo.image || 'Untitled'}</small>
                </div>
            </div>
        `;

        const deleteBtn = colDiv.querySelector('.btn-delete');
        deleteBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.deletePhoto(photo.id);
        });

        // MODIFIED: Pass photo name to fullscreen function
        const fullscreenBtn = colDiv.querySelector('.btn-fullscreen');
        fullscreenBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.openFullscreen(photo.url, photo.image);
        });

        // MODIFIED: Pass photo name to fullscreen function
        const img = colDiv.querySelector('.photo-thumbnail');
        img.addEventListener('click', () => {
            this.openFullscreen(photo.url, photo.image);
        });

        return colDiv;
    }

    async deletePhoto(photoId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette photo ?')) {
            return;
        }

        try {
            const response = await fetch(this.deleteUrl.replace('{id}', photoId), {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                this.showMessage(result.message, 'success');
                this.loadExistingPhotos();
            } else {
                this.showMessage(result.error || 'Erreur lors de la suppression', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            this.showMessage('Erreur de connexion', 'error');
        }
    }

    // MODIFIED: Function now accepts imageName
    openFullscreen(imageUrl, imageName) {
        let modal = document.getElementById('imageModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.id = 'imageModal';
            modal.setAttribute('tabindex', '-1');
            // MODIFIED: Added modal-title element
            modal.innerHTML = `
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content bg-transparent border-0">
                        <div class="modal-header border-0 pb-2 text-white align-items-center">
                            <h5 class="modal-title" id="modalImageTitle"></h5>
                            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center p-0">
                            <img id="modalImage" class="img-fluid" style="max-height: 90vh;">
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        // ADDED: Get references to modal elements
        const modalImage = document.getElementById('modalImage');
        const modalTitle = document.getElementById('modalImageTitle');

        // ADDED: Set image source and title text
        modalImage.src = imageUrl;
        modalTitle.textContent = imageName || 'Untitled';

        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }


    showProgress(show) {
        if (this.progressBar) {
            this.progressBar.style.display = show ? 'block' : 'none';
        }
    }

    showMessage(message, type) {
        if (!this.messageContainer) return;

        this.messageContainer.innerHTML = `
            <div class="alert alert-${type === 'error' ? 'danger' : 'success'}" role="alert">
                ${message}
            </div>
        `;

        setTimeout(() => {
            this.messageContainer.innerHTML = '';
        }, 5000);
    }
}