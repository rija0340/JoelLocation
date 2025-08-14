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

        // Filter only images
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
                this.loadExistingPhotos(); // Reload photos
            } else {
                this.showMessage(result.error || 'Erreur lors de l\'upload', 'error');
            }
        } catch (error) {
            console.error('Upload error:', error);
            this.showMessage('Erreur de connexion', 'error');
        } finally {
            this.showProgress(false);
            this.fileInput.value = ''; // Reset file input
        }
    }

    async loadExistingPhotos() {
        try {
            const response = await fetch(`/backoffice/reservation/${this.reservationId}/photos`);
            const result = await response.json();

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
        colDiv.className = 'col-md-3 col-sm-4 col-6 mb-3';

        colDiv.innerHTML = `
            <div class="card photo-card h-100">
                <div class="photo-wrapper position-relative">
                    <img src="${photo.url}" alt="Photo réservation" 
                         class="card-img-top photo-thumbnail" 
                         data-bs-toggle="modal" 
                         data-bs-target="#imageModal"
                         data-image-src="${photo.url}"
                         style="cursor: pointer;">
                    <div class="photo-overlay position-absolute w-100 h-100 d-flex align-items-center justify-content-center">
                        <button type="button" class="btn btn-danger btn-sm me-2 btn-delete" data-photo-id="${photo.id}">
                            <i class="fa fa-trash"></i>
                        </button>
                        <button type="button" class="btn btn-primary btn-sm btn-fullscreen" data-image-src="${photo.url}">
                            <i class="fa fa-expand"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Add delete event
        const deleteBtn = colDiv.querySelector('.btn-delete');
        deleteBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.deletePhoto(photo.id);
        });

        // Add fullscreen event
        const fullscreenBtn = colDiv.querySelector('.btn-fullscreen');
        fullscreenBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.openFullscreen(photo.url);
        });

        // Add click on image for fullscreen
        const img = colDiv.querySelector('.photo-thumbnail');
        img.addEventListener('click', () => {
            this.openFullscreen(photo.url);
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
                this.loadExistingPhotos(); // Reload photos
            } else {
                this.showMessage(result.error || 'Erreur lors de la suppression', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            this.showMessage('Erreur de connexion', 'error');
        }
    }

    openFullscreen(imageUrl) {
        // Create modal if it doesn't exist
        let modal = document.getElementById('imageModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.id = 'imageModal';
            modal.setAttribute('tabindex', '-1');
            modal.innerHTML = `
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content bg-transparent border-0">
                        <div class="modal-header border-0 pb-0">
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center p-0">
                            <img id="modalImage" class="img-fluid" style="max-height: 90vh;">
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        // Set image source and show modal
        const modalImage = document.getElementById('modalImage');
        modalImage.src = imageUrl;

        // Use Bootstrap 5 modal
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

        // Auto hide after 5 seconds
        setTimeout(() => {
            this.messageContainer.innerHTML = '';
        }, 5000);
    }
}

// Usage example:
// document.addEventListener('DOMContentLoaded', function() {
//     const uploader = new ReservationPhotoUploader({
//         reservationId: 123,
//         dropZoneId: 'photo-drop-zone',
//         fileInputId: 'photo-input',
//         photosContainerId: 'photos-container',
//         progressBarId: 'upload-progress',
//         messageContainerId: 'upload-messages'
//     });
// });