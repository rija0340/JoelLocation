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
        formData.append('type', this.type);

        // Indicateur de traitement global
        if (this.messageContainer) {
            this.messageContainer.innerHTML = `
                <div class="alert alert-info" role="alert">
                    <i class="fa fa-spinner fa-spin"></i> Traitement et envoi en cours...
                </div>
            `;
        }

        // Extraire les dates EXIF AVANT compression (canvas détruit les EXIF)
        const exifDates = await Promise.all(imageFiles.map(async file => {
            if (file.type === 'image/jpeg' || file.type === 'image/jpg') {
                return await this.extractExifDate(file);
            }
            return null;
        }));

        // Envoyer les dates EXIF avec le FormData (TOUTES les dates, y compris null)
        imageFiles.forEach((_, idx) => {
            formData.append(`exifDate[${idx}]`, exifDates[idx] || '');
        });

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
                return file;
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
     * Extract DateTimeOriginal from JPEG EXIF data before compression
     * @param {File} file
     * @returns {Promise<string|null>} Date string or null
     */
    extractExifDate(file) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                try {
                    const buffer = e.target.result;
                    const view = new DataView(buffer);
                    let offset = 2; // Skip SOI marker

                    // Find APP1 (EXIF) marker
                    while (offset < buffer.byteLength) {
                        if (view.getUint16(offset) !== 0xFFE1) {
                            // Not APP1, skip this segment
                            const segLen = view.getUint16(offset + 2);
                            offset += 2 + segLen;
                            continue;
                        }

                        offset += 2; // Skip FF E1
                        const exifLen = view.getUint16(offset);
                        offset += 2;

                        // Check "Exif\0\0"
                        const exifStr = String.fromCharCode(
                            view.getUint8(offset), view.getUint8(offset + 1),
                            view.getUint8(offset + 2), view.getUint8(offset + 3),
                            view.getUint8(offset + 4), view.getUint8(offset + 5)
                        );
                        if (exifStr !== 'Exif\x00\x00') {
                            offset += exifLen - 2;
                            continue;
                        }

                        offset += 6; // Skip Exif header
                        const tiffStart = offset;
                        const byteOrder = view.getUint16(offset);
                        const littleEndian = (byteOrder === 0x4949); // "II"
                        offset += 4; // Skip byte order + 0x002A
                        const ifdOffset = littleEndian
                            ? view.getUint32(offset, true)
                            : view.getUint32(offset, false);

                        // Parse IFD0
                        const ifdPos = tiffStart + ifdOffset;
                        const nbEntries = view.getUint16(ifdPos, littleEndian);
                        let exifIfdOffset = null;

                        for (let i = 0; i < nbEntries; i++) {
                            const entryPos = ifdPos + 2 + (i * 12);
                            const tag = view.getUint16(entryPos, littleEndian);
                            if (tag === 0x8769) {
                                // Exif IFD Pointer
                                exifIfdOffset = littleEndian
                                    ? view.getUint32(entryPos + 8, true)
                                    : view.getUint32(entryPos + 8, false);
                                break;
                            }
                        }

                        // Parse Exif IFD for DateTimeOriginal (0x9003)
                        if (exifIfdOffset) {
                            const exifIfdPos = tiffStart + exifIfdOffset;
                            const exifNbEntries = view.getUint16(exifIfdPos, littleEndian);

                            for (let i = 0; i < exifNbEntries; i++) {
                                const entryPos = exifIfdPos + 2 + (i * 12);
                                const tag = view.getUint16(entryPos, littleEndian);

                                if (tag === 0x9003) {
                                    const valueOffset = littleEndian
                                        ? view.getUint32(entryPos + 8, true)
                                        : view.getUint32(entryPos + 8, false);

                                    const dateStrPos = tiffStart + valueOffset;
                                    let dateStr = '';
                                    for (let j = 0; j < 19; j++) {
                                        dateStr += String.fromCharCode(view.getUint8(dateStrPos + j));
                                    }

                                    // Format: "YYYY:MM:DD HH:MM:SS" -> "DD/MM/YYYY HH:MM"
                                    const parts = dateStr.match(/(\d{4}):(\d{2}):(\d{2})\s+(\d{2}):(\d{2}):(\d{2})/);
                                    if (parts) {
                                        const [, y, m, d, h, min] = parts;
                                        resolve(`${d}/${m}/${y} ${h}:${min}`);
                                        return;
                                    }
                                }
                            }
                        }

                        resolve(null);
                        return;
                    }

                    resolve(null);
                } catch (err) {
                    console.error('EXIF parse error:', err);
                    resolve(null);
                }
            };
            reader.onerror = () => resolve(null);
            reader.readAsArrayBuffer(file.slice(0, 65536)); // Read first 64KB only (EXIF is at the start)
        });
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

            // Assuming result.photos is an array of {id, url, name, image}
            this.photos = result.photos || []; // Store photos for navigation
            this.displayPhotos(this.photos);
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

        photos.forEach((photo, index) => {
            const photoElement = this.createPhotoElement(photo, index);
            this.photosContainer.appendChild(photoElement);
        });
    }

    createPhotoElement(photo, index) {
        const colDiv = document.createElement('div');
        colDiv.className = 'col-md-3 col-sm-4 col-6 mb-3 d-flex';

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

        const fullscreenBtn = colDiv.querySelector('.btn-fullscreen');
        fullscreenBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.openFullscreen(index);
        });

        const img = colDiv.querySelector('.photo-thumbnail');
        img.addEventListener('click', () => {
            this.openFullscreen(index);
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

    openFullscreen(index) {
        this.currentIndex = index;
        const photo = this.photos[this.currentIndex];
        
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
        modal.style.zIndex = '100000';

        modal.innerHTML = `
            <div class="modal-dialog" role="document" style="max-width: 100%; margin: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                <div class="modal-content" style="background: rgba(0,0,0,0.95); border: none; width: 100%; height: 100%; display: flex; flex-direction: column;">
                    
                    <div style="position: absolute; top: 0; right: 0; padding: 15px; z-index: 10001;">
                        <button type="button" class="close-modal-btn" style="background: rgba(0,0,0,0.5); border: none; color: white; font-size: 30px; cursor: pointer; width: 50px; height: 50px; border-radius: 50%;">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>

                    <div style="position: absolute; top: 15px; left: 15px; right: 80px; z-index: 10000;">
                        <h5 id="modalTitle" class="text-white text-truncate" style="margin: 0; font-size: 1.1rem; text-shadow: 1px 1px 2px black;">${photo.image || 'Aperçu'}</h5>
                        <div class="text-white-50 small mt-1" id="modalCounter">Photo ${this.currentIndex + 1} / ${this.photos.length}</div>
                    </div>

                    <div class="modal-body" style="flex: 1; padding: 0; display: flex; align-items: center; justify-content: center; overflow: hidden; width: 100%; height: 100%; position: relative;">
                        <!-- Navigation Buttons -->
                        <button type="button" id="prevPhoto" class="nav-photo-btn" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); border: none; color: white; border-radius: 50%; width: 60px; height: 60px; z-index: 10002; display: ${this.currentIndex > 0 ? 'flex' : 'none'}; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                            <i class="fa fa-chevron-left" style="font-size: 24px;"></i>
                        </button>
                        
                        <button type="button" id="nextPhoto" class="nav-photo-btn" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); border: none; color: white; border-radius: 50%; width: 60px; height: 60px; z-index: 10002; display: ${this.currentIndex < this.photos.length - 1 ? 'flex' : 'none'}; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                            <i class="fa fa-chevron-right" style="font-size: 24px;"></i>
                        </button>

                        <img id="modalImage" src="${photo.url}" style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; box-shadow: 0 0 20px rgba(0,0,0,0.5); transition: opacity 0.3s ease;">
                    </div>

                </div>
            </div>
        `;
        document.body.appendChild(modal);

        const closeModal = () => {
            document.removeEventListener('keydown', keyHandler);
            if (typeof $ !== 'undefined' && $.fn.modal) {
                $(modal).modal('hide');
            } else {
                modal.classList.remove('show');
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
                modal.remove();
            }
        };

        const keyHandler = (e) => {
            if (e.key === 'ArrowRight') this.showNext();
            if (e.key === 'ArrowLeft') this.showPrev();
            if (e.key === 'Escape') closeModal();
        };

        document.addEventListener('keydown', keyHandler);

        if (typeof $ !== 'undefined' && $.fn.modal) {
            $(modal).modal('show');
        } else {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }

        const closeBtns = modal.querySelectorAll('.close-modal-btn');
        closeBtns.forEach(btn => btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            closeModal();
        }));

        document.getElementById('prevPhoto').addEventListener('click', (e) => {
            e.stopPropagation();
            this.showPrev();
        });

        document.getElementById('nextPhoto').addEventListener('click', (e) => {
            e.stopPropagation();
            this.showNext();
        });

        const img = document.getElementById('modalImage');
        if (img) {
            img.addEventListener('click', (e) => {
                e.stopPropagation();
                // On mobile, click on the right half goes next, left half goes prev
                const rect = img.getBoundingClientRect();
                const x = e.clientX - rect.left;
                if (x > rect.width / 2) {
                    this.showNext();
                } else {
                    this.showPrev();
                }
            });
        }
    }

    showNext() {
        if (this.currentIndex >= this.photos.length - 1) return;
        this.currentIndex++;
        this.updateModalImage();
    }

    showPrev() {
        if (this.currentIndex <= 0) return;
        this.currentIndex--;
        this.updateModalImage();
    }

    updateModalImage() {
        const photo = this.photos[this.currentIndex];
        const img = document.getElementById('modalImage');
        const title = document.getElementById('modalTitle');
        const counter = document.getElementById('modalCounter');
        const prevBtn = document.getElementById('prevPhoto');
        const nextBtn = document.getElementById('nextPhoto');

        if (img) {
            img.style.opacity = '0';
            setTimeout(() => {
                img.src = photo.url;
                img.style.opacity = '1';
                if (title) title.textContent = photo.image || 'Aperçu';
                if (counter) counter.textContent = `Photo ${this.currentIndex + 1} / ${this.photos.length}`;
                
                // Update buttons visibility
                if (prevBtn) prevBtn.style.display = (this.currentIndex > 0) ? 'flex' : 'none';
                if (nextBtn) nextBtn.style.display = (this.currentIndex < this.photos.length - 1) ? 'flex' : 'none';
            }, 300);
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