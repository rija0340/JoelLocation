/**
 * Tarifs V2 - Dynamic range management
 * Handles adding/removing pricing ranges dynamically
 */

document.addEventListener('DOMContentLoaded', function() {
    const addRangeBtn = document.getElementById('add-range');
    const rangesContainer = document.querySelector('.ranges-list');
    let rangeIndex = rangesContainer ? rangesContainer.children.length : 0;

    if (addRangeBtn) {
        addRangeBtn.addEventListener('click', function() {
            addRange();
        });
    }

    function addRange() {
        const prototype = rangesContainer.dataset.prototype || 
            '<div class="range-item">' +
            '<div class="range-item-header">' +
            '<strong>Plage #{index}</strong>' +
            '<button type="button" class="btn btn-danger btn-sm remove-range"><i class="fa fa-trash"></i></button>' +
            '</div>' +
            '<div class="form-row">' +
            '<div class="col">' +
            '<label>Jours minimum</label>' +
            '<input type="number" name="tarifs_v2[tarifs][__name__][min_days]" class="form-control" min="1" required>' +
            '</div>' +
            '<div class="col">' +
            '<label>Jours maximum</label>' +
            '<input type="number" name="tarifs_v2[tarifs][__name__][max_days]" class="form-control" min="1" required>' +
            '</div>' +
            '<div class="col">' +
            '<label>Prix (€)</label>' +
            '<input type="number" name="tarifs_v2[tarifs][__name__][price]" class="form-control" min="0" step="0.01" required>' +
            '</div>' +
            '</div>' +
            '</div>';

        const newRangeHtml = prototype.replace(/__name__/g, rangeIndex)
            .replace(/__index__/g, rangeIndex);
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = newRangeHtml;
        const newRange = tempDiv.firstElementChild;
        
        rangesContainer.appendChild(newRange);
        
        // Add remove button handler
        const removeBtn = newRange.querySelector('.remove-range');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                newRange.remove();
            });
        }
        
        rangeIndex++;
    }

    // Initialize remove buttons for existing ranges
    document.querySelectorAll('.remove-range').forEach(function(btn) {
        btn.addEventListener('click', function() {
            btn.closest('.range-item').remove();
        });
    });
});
