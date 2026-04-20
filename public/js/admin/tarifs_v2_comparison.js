/**
 * Tarifs V2 Comparison View
 */
document.addEventListener('DOMContentLoaded', function() {
    const intervalSelect = document.getElementById('interval-select');
    const monthSelect = document.getElementById('month-select');
    const comparisonBody = document.getElementById('comparison-body');
    const btnLoad = document.getElementById('btn-load-comparison');
    const btnExport = document.getElementById('btn-export-comparison');
    const statisticsRow = document.getElementById('statistics-row');

    loadIntervals();
    btnLoad.addEventListener('click', loadComparison);
    btnExport.addEventListener('click', exportComparison);

    async function loadIntervals() {
        try {
            const response = await fetch('/api/tarifs-v2/intervals');
            const intervals = await response.json();
            intervalSelect.innerHTML = intervals.map(i =>
                `<option value="${i.id}">${i.label}</option>`
            ).join('');
        } catch (error) {
            console.error('Error loading intervals:', error);
        }
    }

    async function loadComparison() {
        comparisonBody.innerHTML = `<tr><td colspan="4"><div class="v2-empty"><i class="fa fa-spinner fa-spin"></i><p>Loading...</p></div></td></tr>`;
        try {
            const intervalId = intervalSelect.value;
            const month = monthSelect.value;
            const response = await fetch(`/api/tarifs-v2/comparison?month=${month}&interval_id=${intervalId}`);
            const result = await response.json();
            renderComparison(result.data);
            updateStatistics(result.data);
            statisticsRow.style.display = 'grid';
        } catch (error) {
            console.error('Error loading comparison:', error);
            comparisonBody.innerHTML = `<tr><td colspan="4"><div class="v2-empty"><i class="fa fa-exclamation-circle" style="color:var(--v2-danger);"></i><p>Error loading data</p></div></td></tr>`;
        }
    }

    function renderComparison(data) {
        if (data.length === 0) {
            comparisonBody.innerHTML = `<tr><td colspan="4"><div class="v2-empty"><i class="fa fa-inbox"></i><p>No prices set for this interval and month</p></div></td></tr>`;
            return;
        }

        const prices = data.map(d => d.price).filter(p => p !== null);
        const minPrice = Math.min(...prices);
        const maxPrice = Math.max(...prices);

        comparisonBody.innerHTML = data.map((item, idx) => {
            let priceCls = '';
            if (item.price === minPrice) priceCls = 'v2-price-low';
            else if (item.price === maxPrice) priceCls = 'v2-price-high';

            return `<tr>
                <td style="color:var(--v2-gray-400);font-weight:600;">${idx + 1}</td>
                <td style="font-weight:600;color:var(--v2-gray-800);">${item.vehicle}</td>
                <td class="v2-price-col ${priceCls}" style="text-align:right;">${item.price !== null ? item.price.toFixed(2) + ' \u20AC' : '\u2014'}</td>
                <td>
                    <a href="/backoffice/tarifs-v2/matrix" class="v2-btn v2-btn-sm" title="Edit in Matrix">
                        <i class="fa fa-pencil"></i>
                    </a>
                </td>
            </tr>`;
        }).join('');
    }

    function updateStatistics(data) {
        const prices = data.map(d => d.price).filter(p => p !== null);
        if (prices.length === 0) {
            document.getElementById('stat-min').textContent = '-';
            document.getElementById('stat-max').textContent = '-';
            document.getElementById('stat-avg').textContent = '-';
            document.getElementById('stat-count').textContent = '0';
            return;
        }
        const min = Math.min(...prices);
        const max = Math.max(...prices);
        const avg = prices.reduce((a, b) => a + b, 0) / prices.length;
        document.getElementById('stat-min').textContent = min.toFixed(2) + ' \u20AC';
        document.getElementById('stat-max').textContent = max.toFixed(2) + ' \u20AC';
        document.getElementById('stat-avg').textContent = avg.toFixed(2) + ' \u20AC';
        document.getElementById('stat-count').textContent = data.length;
    }

    function exportComparison() {
        const rows = Array.from(comparisonBody.querySelectorAll('tr'));
        let csv = 'Vehicle;Price\n';
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 2) {
                const vehicle = cells[1]?.textContent?.trim();
                const price = cells[2]?.textContent?.trim();
                if (vehicle && price) csv += `${vehicle};${price}\n`;
            }
        });
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `comparison-${monthSelect.value}.csv`;
        link.click();
    }
});
