/**
 * Tarifs V2 Intervals Management
 */
document.addEventListener('DOMContentLoaded', function() {
    const intervalsList = document.getElementById('intervals-list');
    const addForm = document.getElementById('add-interval-form');

    loadIntervals();
    addForm.addEventListener('submit', handleAddInterval);

    async function loadIntervals() {
        try {
            const response = await fetch('/api/tarifs-v2/intervals');
            const intervals = await response.json();
            renderIntervals(intervals);
        } catch (error) {
            console.error('Error loading intervals:', error);
            intervalsList.innerHTML = `<li class="v2-empty"><i class="fa fa-exclamation-circle" style="color:var(--v2-danger);"></i><p>Error loading intervals</p></li>`;
        }
    }

    function renderIntervals(intervals) {
        if (intervals.length === 0) {
            intervalsList.innerHTML = `<li class="v2-empty"><i class="fa fa-inbox"></i><p>No intervals defined. Add your first one.</p></li>`;
            return;
        }

        intervalsList.innerHTML = intervals.map(interval => `
            <li class="v2-interval-item">
                <span class="v2-interval-badge">#${interval.sort_order}</span>
                <div class="v2-interval-info">
                    <div class="v2-interval-label">${interval.label}</div>
                    <div class="v2-interval-range">${interval.min_days} to ${interval.max_days || '\u221E'} days</div>
                </div>
                <button class="v2-btn v2-btn-sm v2-btn-danger" onclick="window.deleteInterval(${interval.id})">
                    <i class="fa fa-trash-o"></i>
                </button>
            </li>
        `).join('');
    }

    async function handleAddInterval(e) {
        e.preventDefault();

        const minDays = parseInt(document.getElementById('new-min-days').value);
        const maxDays = document.getElementById('new-max-days').value;
        const label = document.getElementById('new-label').value;

        if (!minDays || minDays < 1) {
            $.alert({ title: 'Invalid', content: 'Enter a valid minimum days value', type: 'orange' });
            return;
        }

        try {
            const response = await fetch('/api/tarifs-v2/intervals', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    min_days: minDays,
                    max_days: maxDays ? parseInt(maxDays) : null,
                    label: label || null
                })
            });
            const result = await response.json();
            if (result.success) {
                addForm.reset();
                loadIntervals();
                $.alert({ title: 'Added!', content: 'Interval created successfully', type: 'green' });
            } else {
                $.alert({ title: 'Error', content: result.error || 'Unknown error', type: 'red' });
            }
        } catch (error) {
            $.alert({ title: 'Error', content: 'Network error', type: 'red' });
        }
    }

    window.deleteInterval = async function(id) {
        $.confirm({
            title: 'Delete this interval?',
            content: 'All associated prices will also be deleted.',
            type: 'red',
            buttons: {
                confirm: {
                    text: 'Delete',
                    btnClass: 'btn-red',
                    action: async function() {
                        try {
                            const response = await fetch(`/api/tarifs-v2/intervals/${id}`, { method: 'DELETE' });
                            const result = await response.json();
                            if (result.success) {
                                loadIntervals();
                            } else {
                                $.alert({ title: 'Error', content: result.error, type: 'red' });
                            }
                        } catch (error) {
                            $.alert({ title: 'Error', content: 'Network error', type: 'red' });
                        }
                    }
                },
                cancel: () => {}
            }
        });
    };
});
