/**
 * Tarifs V2 Matrix - Main JavaScript (Modern UI)
 */
document.addEventListener('DOMContentLoaded', function() {
    let currentMatrix = null;
    let modifiedCells = {};
    let intervals = [];
    let currentVehicle = window.tarifsV2Config?.defaultVehicle || null;
    let activeInputKey = null;

    // French month names with accented characters - must match API response keys
    const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                   'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

    const vehicleSelect = document.getElementById('vehicle-select');
    const matrixBody = document.getElementById('matrix-body');
    const btnSaveAll = document.getElementById('btn-save-all');
    const btnCancel = document.getElementById('btn-cancel-changes');
    const saveStatus = document.getElementById('save-status');
    const unsavedStatus = document.getElementById('unsaved-status');
    const unsavedCount = document.getElementById('unsaved-count');
    const loadingOverlay = document.getElementById('loading-overlay');
    const importModal = document.getElementById('import-modal');

    loadIntervals().then(() => {
        if (currentVehicle) {
            loadMatrix(currentVehicle.marque_id, currentVehicle.modele_id);
        }
    });

    vehicleSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        loadMatrix(option.dataset.marque, option.dataset.modele);
    });

    document.getElementById('btn-prev-vehicle').addEventListener('click', () => navigateVehicle(-1));
    document.getElementById('btn-next-vehicle').addEventListener('click', () => navigateVehicle(1));
    btnSaveAll.addEventListener('click', saveAllChanges);
    btnCancel.addEventListener('click', cancelChanges);
    document.getElementById('btn-copy-month-to-all').addEventListener('click', copyMonthToAll);
    document.getElementById('btn-apply-percentage').addEventListener('click', applyPercentage);
    document.getElementById('btn-export-csv').addEventListener('click', exportCsv);

    // Copy from dropdown
    const copyFromBtn = document.getElementById('btn-copy-from');
    const copyFromMenu = document.getElementById('copy-from-vehicle-list');
    copyFromBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        copyFromMenu.classList.toggle('show');
    });
    document.addEventListener('click', () => copyFromMenu.classList.remove('show'));
    copyFromMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            copyFromMenu.classList.remove('show');
            copyFromVehicle(this.dataset.marque, this.dataset.modele);
        });
    });

    // Import modal
    document.getElementById('btn-import-csv').addEventListener('click', () => {
        importModal.classList.add('show');
    });
    document.getElementById('import-modal-close').addEventListener('click', closeModal);
    document.getElementById('import-modal-cancel').addEventListener('click', closeModal);
    importModal.addEventListener('click', (e) => {
        if (e.target === importModal) closeModal();
    });
    document.getElementById('import-file').addEventListener('change', previewImport);
    document.getElementById('btn-import-confirm').addEventListener('click', confirmImport);

    function closeModal() {
        importModal.classList.remove('show');
    }

    function showLoading() { loadingOverlay.classList.add('show'); }
    function hideLoading() { loadingOverlay.classList.remove('show'); }

    async function loadIntervals() {
        try {
            const response = await fetch('/api/tarifs-v2/intervals');
            if (!response.ok) throw new Error(`Erreur serveur ${response.status}`);
            intervals = await response.json();
        } catch (error) {
            console.error('Erreur lors du chargement des intervalles:', error);
            matrixBody.innerHTML = `<tr><td colspan="14" class="v2-empty" style="color:var(--v2-danger);">
                <i class="fa fa-exclamation-circle fa-2x"></i>
                <p>Erreur lors du chargement des intervalles: ${error.message}</p></td></tr>`;
        }
    }

    async function loadMatrix(marqueId, modeleId) {
        showLoading();
        try {
            const response = await fetch(`/api/tarifs-v2/vehicle/${marqueId}/${modeleId}`);
            currentMatrix = await response.json();
            renderMatrix();
        } catch (error) {
            console.error('Erreur lors du chargement de la matrice:', error);
        } finally {
            hideLoading();
        }
    }

    function renderMatrix() {
        if (!currentMatrix || !currentMatrix.rows) {
            matrixBody.innerHTML = '<tr><td colspan="14" class="v2-empty">Aucune donnée</td></tr>';
            return;
        }

        let html = '';
        currentMatrix.rows.forEach((row, rowIndex) => {
            html += '<tr>';
            html += `<td><span style="font-size:13px;">${row.interval.display_label}</span></td>`;

            months.forEach((month, colIndex) => {
                const cellData = row.months[month];
                const cellKey = `${row.interval.id}-${month}`;
                const modifiedValue = modifiedCells[cellKey];
                const price = modifiedValue !== undefined ? modifiedValue : (cellData ? cellData.price : null);
                const hasPrice = price !== null && price !== undefined;
                const isModified = modifiedValue !== undefined;
                const isEditing = activeInputKey === cellKey;

                let cls = 'v2-cell';
                if (isEditing) {
                    // render input below
                } else if (isModified) {
                    cls += ' modified';
                } else if (hasPrice) {
                    cls += ' filled';
                } else {
                    cls += ' empty';
                }

                html += `<td>`;
                if (isEditing) {
                    html += `<div class="v2-cell modified">
                        <input type="number" step="0.01" value="${price || ''}"
                               data-cell-key="${cellKey}"
                               data-interval="${row.interval.id}"
                               data-month="${month}"
                               data-row-index="${rowIndex}"
                               data-col-index="${colIndex}">
                    </div>`;
                } else {
                    html += `<div class="${cls}"
                                 data-interval="${row.interval.id}"
                                 data-month="${month}"
                                 data-row-index="${rowIndex}"
                                 data-col-index="${colIndex}">
                        <span class="v2-cell-text">${hasPrice ? price.toFixed(2) : '—'}</span>
                    </div>`;
                }
                html += `</td>`;
            });

            html += `<td><div class="v2-row-actions">
                <button onclick="window.copyRowToAll('${row.interval.id}')" title="Copier Jan sur tous les mois"><i class="fa fa-copy"></i></button>
                <button onclick="window.clearRow('${row.interval.id}')" title="Vider la ligne"><i class="fa fa-trash-o"></i></button>
            </div></td>`;
            html += '</tr>';
        });

        matrixBody.innerHTML = html;

        // Attach events
        matrixBody.querySelectorAll('.v2-cell').forEach(cell => {
            cell.addEventListener('click', handleCellClick);
        });
        matrixBody.querySelectorAll('.v2-cell input').forEach(input => {
            input.addEventListener('blur', handleCellBlur);
            input.addEventListener('keydown', handleCellKeydown);
            input.focus();
            input.select();
        });
    }

    function handleCellKeydown(e) {
        const input = e.target;
        const rowIndex = parseInt(input.dataset.rowIndex);
        const colIndex = parseInt(input.dataset.colIndex);
        const rowCount = currentMatrix.rows.length;
        const colCount = months.length;

        // Tab: move to next/previous cell
        if (e.key === 'Tab') {
            e.preventDefault();
            let nextRow = rowIndex;
            let nextCol = e.shiftKey ? colIndex - 1 : colIndex + 1;
            
            // Wrap horizontally
            if (nextCol < 0) {
                nextCol = colCount - 1;
                nextRow = nextRow - 1;
            } else if (nextCol >= colCount) {
                nextCol = 0;
                nextRow = nextRow + 1;
            }
            
            // Wrap vertically (continue to next row)
            if (nextRow < 0) {
                nextRow = rowCount - 1;
            } else if (nextRow >= rowCount) {
                nextRow = 0;
            }
            
            // Blur current and focus next
            input.blur();
            setTimeout(() => {
                focusCellByIndex(nextRow, nextCol);
            }, 10);
            return;
        }

        // Enter: move down, Shift+Enter: move up
        if (e.key === 'Enter') {
            e.preventDefault();
            let nextRow = e.shiftKey ? rowIndex - 1 : rowIndex + 1;
            let nextCol = colIndex;
            
            // Wrap vertically
            if (nextRow < 0) {
                nextRow = rowCount - 1;
                nextCol = colIndex - 1;
                if (nextCol < 0) nextCol = colCount - 1;
            } else if (nextRow >= rowCount) {
                nextRow = 0;
                nextCol = colIndex + 1;
                if (nextCol >= colCount) nextCol = 0;
            }
            
            input.blur();
            setTimeout(() => {
                focusCellByIndex(nextRow, nextCol);
            }, 10);
            return;
        }

        // Escape: cancel editing
        if (e.key === 'Escape') {
            activeInputKey = null;
            renderMatrix();
        }
    }

    function focusCellByIndex(rowIndex, colIndex) {
        if (!currentMatrix || !currentMatrix.rows[rowIndex]) return;
        
        const intervalId = currentMatrix.rows[rowIndex].interval.id;
        const month = months[colIndex];
        const cellKey = `${intervalId}-${month}`;
        
        activeInputKey = cellKey;
        renderMatrix();
        
        // Focus the input after render
        setTimeout(() => {
            const input = document.querySelector(`input[data-cell-key="${cellKey}"]`);
            if (input) {
                input.focus();
                input.select();
            }
        }, 10);
    }

    function handleCellClick(e) {
        if (e.target.tagName === 'INPUT') return;
        const cell = e.currentTarget;
        activeInputKey = `${cell.dataset.interval}-${cell.dataset.month}`;
        renderMatrix();
    }

    function handleCellBlur(e) {
        const input = e.target;
        const interval = input.dataset.interval;
        const month = input.dataset.month;
        const cellKey = `${interval}-${month}`;
        const value = input.value.trim();
        const originalValue = getOriginalPrice(interval, month);

        activeInputKey = null;

        if (value === '' || value === null) {
            // If the original cell had a price and user cleared it, store null to mark as "deleted"
            // If the cell was already empty, just remove from modifiedCells
            if (originalValue !== null && originalValue !== undefined) {
                modifiedCells[cellKey] = null; // Explicitly cleared - remember this!
            } else {
                delete modifiedCells[cellKey]; // Was already empty, no change needed
            }
        } else {
            const numValue = parseFloat(value);
            if (numValue !== originalValue) {
                modifiedCells[cellKey] = numValue;
            } else {
                delete modifiedCells[cellKey];
            }
        }
        updateSaveStatus();
        renderMatrix();
    }

    function getOriginalPrice(intervalId, month) {
        if (!currentMatrix) return null;
        const row = currentMatrix.rows.find(r => r.interval.id == intervalId);
        return row?.months[month]?.price ?? null;
    }

    function updateSaveStatus() {
        const count = Object.keys(modifiedCells).length;
        if (count === 0) {
            saveStatus.style.display = 'none';
            unsavedStatus.style.display = 'none';
            btnSaveAll.disabled = true;
            btnCancel.disabled = true;
        } else {
            saveStatus.style.display = 'none';
            unsavedStatus.style.display = 'inline-flex';
            unsavedCount.textContent = count;
            btnSaveAll.disabled = false;
            btnCancel.disabled = false;
        }
    }

    async function saveAllChanges() {
        if (Object.keys(modifiedCells).length === 0) return;
        showLoading();
        const changes = [];
        const option = vehicleSelect.options[vehicleSelect.selectedIndex];
        const marqueId = option.dataset.marque;
        const modeleId = option.dataset.modele;

        Object.entries(modifiedCells).forEach(([key, price]) => {
            const [intervalId, month] = key.split('-');
            if (price !== null) {
                changes.push({
                    marque_id: parseInt(marqueId),
                    modele_id: parseInt(modeleId),
                    interval_id: parseInt(intervalId),
                    month: month,
                    price: price
                });
            }
        });

        try {
            const response = await fetch('/api/tarifs-v2/bulk-save', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ changes })
            });
            const result = await response.json();
            if (result.success) {
                modifiedCells = {};
                updateSaveStatus();
                await loadMatrix(marqueId, modeleId);
                let msg = `Créés: ${result.created}, Modifiés: ${result.updated}`;
                if (result.deleted > 0) {
                    msg += `, Supprimés: ${result.deleted}`;
                }
                $.alert({ title: 'Sauvegardé !', content: msg, type: 'green' });
            } else {
                $.alert({ title: 'Erreur', content: result.error, type: 'red' });
            }
        } catch (error) {
            console.error('Erreur lors de la sauvegarde:', error);
            $.alert({ title: 'Erreur', content: 'Échec de la sauvegarde des modifications', type: 'red' });
        } finally {
            hideLoading();
        }
    }

    function cancelChanges() {
        $.confirm({
            title: 'Annuler les modifications ?',
            content: 'Toutes les modifications non sauvegardées seront perdues.',
            buttons: {
                confirm: () => { modifiedCells = {}; activeInputKey = null; updateSaveStatus(); renderMatrix(); },
                cancel: () => {}
            }
        });
    }

    function navigateVehicle(direction) {
        const newIndex = vehicleSelect.selectedIndex + direction;
        if (newIndex >= 0 && newIndex < vehicleSelect.options.length) {
            vehicleSelect.selectedIndex = newIndex;
            vehicleSelect.dispatchEvent(new Event('change'));
        }
    }

    async function copyMonthToAll() {
        $.confirm({
            title: 'Copier les prix de Janvier ?',
            content: 'Cela écrasera tous les autres mois avec les prix de Janvier.',
            buttons: {
                confirm: async () => {
                    showLoading();
                    const option = vehicleSelect.options[vehicleSelect.selectedIndex];
                    try {
                        const response = await fetch('/api/tarifs-v2/copy-month', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                marque_id: parseInt(option.dataset.marque),
                                modele_id: parseInt(option.dataset.modele),
                                source_month: 'Janvier'
                            })
                        });
                        const result = await response.json();
                        if (result.success) {
                            await loadMatrix(option.dataset.marque, option.dataset.modele);
                            $.alert({ title: 'Terminé !', content: result.message, type: 'green' });
                        } else {
                            $.alert({ title: 'Erreur', content: result.error, type: 'red' });
                        }
                    } catch (error) {
                        $.alert({ title: 'Erreur', content: 'Erreur réseau', type: 'red' });
                    } finally { hideLoading(); }
                },
                cancel: () => {}
            }
        });
    }

    async function applyPercentage() {
        const percentage = parseFloat(document.getElementById('percentage-input').value);
        if (isNaN(percentage)) { $.alert({ title: 'Invalide', content: 'Entrez un pourcentage valide', type: 'orange' }); return; }
        $.confirm({
            title: `Appliquer ${percentage > 0 ? '+' : ''}${percentage}% ?`,
            content: 'Cela ajustera tous les prix existants pour ce véhicule.',
            buttons: {
                confirm: async () => {
                    showLoading();
                    const option = vehicleSelect.options[vehicleSelect.selectedIndex];
                    try {
                        const response = await fetch('/api/tarifs-v2/apply-percentage', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                marque_id: parseInt(option.dataset.marque),
                                modele_id: parseInt(option.dataset.modele),
                                percentage
                            })
                        });
                        const result = await response.json();
                        if (result.success) {
                            await loadMatrix(option.dataset.marque, option.dataset.modele);
                            $.alert({ title: 'Terminé !', content: result.message, type: 'green' });
                        } else {
                            $.alert({ title: 'Erreur', content: result.error, type: 'red' });
                        }
                    } catch (error) {
                        $.alert({ title: 'Erreur', content: 'Erreur réseau', type: 'red' });
                    } finally { hideLoading(); }
                },
                cancel: () => {}
            }
        });
    }

    async function copyFromVehicle(sourceMarqueId, sourceModeleId) {
        const option = vehicleSelect.options[vehicleSelect.selectedIndex];
        if (sourceMarqueId === option.dataset.marque && sourceModeleId === option.dataset.modele) {
            $.alert({ title: 'Oups', content: 'Impossible de copier depuis le même véhicule', type: 'orange' });
            return;
        }
        $.confirm({
            title: 'Copier tous les prix ?',
            content: 'Cela écrasera tous les prix pour le véhicule actuel.',
            buttons: {
                confirm: async () => {
                    showLoading();
                    try {
                        const response = await fetch('/api/tarifs-v2/copy-vehicle', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                source_marque_id: parseInt(sourceMarqueId),
                                source_modele_id: parseInt(sourceModeleId),
                                target_marque_id: parseInt(option.dataset.marque),
                                target_modele_id: parseInt(option.dataset.modele)
                            })
                        });
                        const result = await response.json();
                        if (result.success) {
                            await loadMatrix(option.dataset.marque, option.dataset.modele);
                            $.alert({ title: 'Terminé !', content: 'Prix copiés avec succès', type: 'green' });
                        } else {
                            $.alert({ title: 'Erreur', content: result.error, type: 'red' });
                        }
                    } catch (error) {
                        $.alert({ title: 'Erreur', content: 'Erreur réseau', type: 'red' });
                    } finally { hideLoading(); }
                },
                cancel: () => {}
            }
        });
    }

    function exportCsv() { window.location.href = '/api/tarifs-v2/export'; }

    async function previewImport(e) {
        const file = e.target.files[0];
        if (!file) return;
        try {
            const formData = new FormData();
            formData.append('file', file);
            const response = await fetch('/api/tarifs-v2/import-preview', { method: 'POST', body: formData });
            const result = await response.json();
            document.getElementById('import-preview-body').innerHTML = result.preview.map(row =>
                `<tr><td>${row.marque}</td><td>${row.modele}</td><td>${row.month}</td><td>${row.interval}</td><td>${row.price}</td></tr>`
            ).join('');
            document.getElementById('import-preview').style.display = 'block';
            document.getElementById('btn-import-confirm').disabled = false;
        } catch (error) {
            $.alert({ title: 'Erreur', content: 'Erreur lors de l\'aperçu du fichier', type: 'red' });
        }
    }

    async function confirmImport() {
        const file = document.getElementById('import-file').files[0];
        if (!file) return;
        showLoading();
        closeModal();
        try {
            const formData = new FormData();
            formData.append('file', file);
            const response = await fetch('/api/tarifs-v2/import', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) {
                if (currentVehicle) await loadMatrix(currentVehicle.marque_id, currentVehicle.modele_id);
                $.alert({ title: 'Importation réussie !', content: `Créés: ${result.created}, Modifiés: ${result.updated}, Erreurs: ${result.errors.length}`, type: 'green' });
            } else {
                $.alert({ title: 'Erreur d\'importation', content: result.error, type: 'red' });
            }
        } catch (error) {
            $.alert({ title: 'Erreur', content: 'Erreur réseau', type: 'red' });
        } finally { hideLoading(); }
    }

    // Global row actions - use the full months array (with accents)
    window.copyRowToAll = function(intervalId) {
        const row = currentMatrix.rows.find(r => r.interval.id == intervalId);
        const janPrice = row?.months['Janvier']?.price;
        if (janPrice === null || janPrice === undefined) {
            $.alert({ title: 'Aucun prix', content: 'Aucun prix défini pour Janvier sur cet intervalle', type: 'orange' });
            return;
        }
        // Copy Janvier price to all other months (skip first one which is Janvier)
        months.slice(1).forEach(month => {
            modifiedCells[`${intervalId}-${month}`] = janPrice;
        });
        updateSaveStatus();
        renderMatrix();
    };

    window.clearRow = function(intervalId) {
        $.confirm({
            title: 'Vider la ligne ?',
            content: 'Cela supprimera définitivement tous les prix pour cet intervalle.',
            buttons: {
                confirm: async () => {
                    showLoading();
                    const option = vehicleSelect.options[vehicleSelect.selectedIndex];
                    const marqueId = parseInt(option.dataset.marque);
                    const modeleId = parseInt(option.dataset.modele);
                    
                    // Build changes array with null prices (deletions)
                    const changes = [];
                    months.forEach(month => {
                        changes.push({
                            marque_id: marqueId,
                            modele_id: modeleId,
                            interval_id: parseInt(intervalId),
                            month: month,
                            price: null  // null = delete
                        });
                    });

                    try {
                        const response = await fetch('/api/tarifs-v2/bulk-save', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ changes })
                        });
                        const result = await response.json();
                        if (result.success) {
                            // Clear any pending modifications for this interval
                            months.forEach(month => {
                                delete modifiedCells[`${intervalId}-${month}`];
                            });
                            updateSaveStatus();
                            await loadMatrix(marqueId, modeleId);
                            const msg = `Créés: ${result.created}, Modifiés: ${result.updated}, Supprimés: ${result.deleted || 0}`;
                            $.alert({ title: 'Ligne vidée !', content: msg, type: 'green' });
                        } else {
                            $.alert({ title: 'Erreur', content: result.error, type: 'red' });
                        }
                    } catch (error) {
                        console.error('Erreur lors du vidage de la ligne:', error);
                        $.alert({ title: 'Erreur', content: 'Échec du vidage de la ligne', type: 'red' });
                    } finally {
                        hideLoading();
                    }
                },
                cancel: () => {}
            }
        });
    };

    // Guide Modal
    const guideModal = document.getElementById('guide-modal');
    
    document.getElementById('btn-show-guide').addEventListener('click', () => {
        guideModal.classList.add('show');
    });
    
    document.getElementById('guide-modal-close').addEventListener('click', () => {
        guideModal.classList.remove('show');
    });
    
    document.getElementById('guide-modal-close-btn').addEventListener('click', () => {
        guideModal.classList.remove('show');
    });
    
    guideModal.addEventListener('click', (e) => {
        if (e.target === guideModal) {
            guideModal.classList.remove('show');
        }
    });
});