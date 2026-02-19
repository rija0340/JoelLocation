class ReservationPhotoUploader {
    constructor(options) {
        this.reservationId = options.reservationId;
        this.type = options.type || 'depart'; // Default to 'depart'
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
        formData.append('type', this.type); // Add type to form data

        // Indicateur de traitement global
        if (this.messageContainer) {
            this.messageContainer.innerHTML = `
                <div class="alert alert-info" role="alert">
                    <i class="fa fa-spinner fa-spin"></i> Traitement et envoi en cours...
                </div>
            `;
        }

        // Traitement parallèle des images
        const processingPromises = imageFiles.map(async file => {
            try {
                // On compresse si l'image fait plus de 1MB
                if (file.size > 1024 * 1024) {
                    return await this.compressImage(file);
                }
                return file;
            } catch (error) {
                console.error('Erreur compression pour ' + file.name, error);
                return file; // Fallback sur l'original
            }
        });

        const processedFiles = await Promise.all(processingPromises);

        processedFiles.forEach(file => {
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

    /**
     * Compresse une image côté client
     * @param {File} file 
     * @param {number} maxWidth 
     * @param {number} quality 
     * @returns {Promise<File>}
     */
    compressImage(file, maxWidth = 1920, quality = 0.7) {
        return new Promise((resolve, reject) => {
            let fileName = file.name;
            // On s'assure que l'extension est bien .jpg car on convertit en JPEG
            if (!fileName.toLowerCase().endsWith('.jpg') && !fileName.toLowerCase().endsWith('.jpeg')) {
                // Remplace l'extension existante ou ajoute .jpg
                fileName = fileName.replace(/\.[^/.]+$/, "") + ".jpg";
            }

            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = event => {
                const img = new Image();
                img.src = event.target.result;
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;

                    // Calcul des nouvelles dimensions en gardant le ratio
                    if (width > maxWidth || height > maxWidth) {
                        if (width > height) {
                            height = Math.round((height * maxWidth) / width);
                            width = maxWidth;
                        } else {
                            width = Math.round((width * maxWidth) / height);
                            height = maxWidth;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    ctx.canvas.toBlob((blob) => {
                        if (!blob) {
                            reject(new Error('Canvas is empty'));
                            return;
                        }
                        const newFile = new File([blob], fileName, {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });
                        resolve(newFile);
                    }, 'image/jpeg', quality); // Compression JPEG
                };
                img.onerror = error => reject(error);
            };
            reader.onerror = error => reject(error);
        });
    }

    async loadExistingPhotos() {
        try {
            const response = await fetch(`/backoffice/reservation/${this.reservationId}/photos?type=${this.type}`);
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
        if (modal) {
            modal.remove();
        }

        modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'imageModal';
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-hidden', 'true');

        // Z-index très élevé pour être sûr d'être au dessus de tout
        modal.style.zIndex = '100000';

        modal.innerHTML = `
            <div class="modal-dialog" role="document" style="max-width: 100%; margin: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                <div class="modal-content" style="background: rgba(0,0,0,0.95); border: none; width: 100%; height: 100%; display: flex; flex-direction: column;">
                    
                    <!-- Header avec bouton close classique -->
                    <div style="position: absolute; top: 0; right: 0; padding: 15px; z-index: 10001;">
                        <button type="button" class="close-modal-btn" style="background: none; border: none; color: white; font-size: 30px; cursor: pointer;">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>

                    <!-- Titre -->
                    <div style="position: absolute; top: 15px; left: 15px; right: 60px; z-index: 10000;">
                        <h5 class="text-white text-truncate" style="margin: 0; font-size: 1.1rem;">${imageName || 'Aperçu'}</h5>
                    </div>

                    <!-- Corps avec l'image -->
                    <div class="modal-body" style="flex: 1; padding: 0; display: flex; align-items: center; justify-content: center; overflow: hidden; width: 100%; height: 100%;">
                        <img id="modalImage" src="${imageUrl}" style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; box-shadow: 0 0 20px rgba(0,0,0,0.5);">
                    </div>

                    <!-- Footer avec gros bouton close (utile sur mobile) -->
                    <div style="position: absolute; bottom: 20px; left: 0; width: 100%; display: flex; justify-content: center; z-index: 10001; pointer-events: none;">
                        <button type="button" class="close-modal-btn btn btn-light rounded-circle shadow" style="width: 50px; height: 50px; padding: 0; display: flex; align-items: center; justify-content: center; pointer-events: auto;">
                            <i class="fa fa-times" style="font-size: 20px;"></i>
                        </button>
                    </div>

                </div>
            </div>
        `;
        document.body.appendChild(modal);

        // Définition de la fonction de fermeture pour réutilisation
        const closeModal = () => {
            if (typeof $ !== 'undefined' && $.fn.modal) {
                $(modal).modal('hide');
            } else {
                try {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) bsModal.hide();
                    else {
                        // Fallback si l'instance n'est pas trouvée (ex: pas encore créée ou déjà détruite)
                        modal.classList.remove('show');
                        modal.style.display = 'none';
                        document.body.classList.remove('modal-open');
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) backdrop.remove();
                        modal.remove();
                    }
                } catch (e) {
                    // Dernier recours manuel
                    modal.remove();
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) backdrop.remove();
                }
            }
        };

        // Ouverture du modal
        if (typeof $ !== 'undefined' && $.fn.modal) {
            $(modal).modal('show');
            // Force le focus pour l'accessibilité
            $(modal).on('shown.bs.modal', function () {
                $('#modalImage').focus();
            });
        } else {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }

        // Attachement des événements de fermeture sur TOUS les boutons close
        const closeBtns = modal.querySelectorAll('.close-modal-btn');
        closeBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                closeModal();
            });
        });

        // Fermeture sur clic image
        const img = document.getElementById('modalImage');
        if (img) {
            img.addEventListener('click', (e) => {
                // Optionnel : ne pas fermer si l'utilisateur veut zoomer ? 
                // Pour l'instant on ferme comme demandé implicitement pour une expérience "rapide"
                closeModal();
            });
        }
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