/**
 * Tarifs V2 History
 */
document.addEventListener('DOMContentLoaded', function() {
    const historyBody = document.getElementById('history-body');
    const filterMonth = document.getElementById('filter-month');
    const filterDateFrom = document.getElementById('filter-date-from');
    const filterDateTo = document.getElementById('filter-date-to');
    const btnFilter = document.getElementById('btn-filter');
    const btnReset = document.getElementById('btn-reset');
    const historyCount = document.getElementById('history-count');

    loadHistory();
    btnFilter.addEventListener('click', loadHistory);
    btnReset.addEventListener('click', resetFilters);

    async function loadHistory() {
        historyBody.innerHTML = `<tr><td colspan="8"><div class="v2-empty"><i class="fa fa-spinner fa-spin"></i><p>Loading...</p></div></td></tr>`;
        try {
            let url = '/api/tarifs-v2/history';
            const params = new URLSearchParams();
            if (filterMonth.value) params.append('month', filterMonth.value);
            if (filterDateFrom.value) params.append('date_from', filterDateFrom.value);
            if (filterDateTo.value) params.append('date_to', filterDateTo.value);
            if (params.toString()) url += '?' + params.toString();

            const response = await fetch(url);
            const result = await response.json();
            renderHistory(result.data);
            updateStatistics(result.statistics);
            historyCount.textContent = `${result.data.length} entries`;
        } catch (error) {
            console.error('Error loading history:', error);
            historyBody.innerHTML = `<tr><td colspan="8"><div class="v2-empty"><i class="fa fa-exclamation-circle" style="color:var(--v2-danger);"></i><p>Error loading history</p></div></td></tr>`;
        }
    }

    function renderHistory(data) {
        if (data.length === 0) {
            historyBody.innerHTML = `<tr><td colspan="8"><div class="v2-empty"><i class="fa fa-inbox"></i><p>No history entries found</p></div></td></tr>`;
            return;
        }

        historyBody.innerHTML = data.map(entry => {
            let changeCls = 'v2-price-same';
            let diffSign = '';
            if (entry.difference > 0) { changeCls = 'v2-price-increase'; diffSign = '+'; }
            else if (entry.difference < 0) { changeCls = 'v2-price-decrease'; }

            let badge = '';
            if (entry.difference > 0) badge = '<span class="v2-badge v2-badge-danger" style="margin-left:6px;"><i class="fa fa-arrow-up"></i></span>';
            else if (entry.difference < 0) badge = '<span class="v2-badge v2-badge-success" style="margin-left:6px;"><i class="fa fa-arrow-down"></i></span>';

            return `<tr>
                <td style="white-space:nowrap;color:var(--v2-gray-500);">${formatDate(entry.date)}</td>
                <td style="font-weight:600;color:var(--v2-gray-800);">${entry.user}</td>
                <td>${entry.vehicle}</td>
                <td>${entry.month}</td>
                <td>${entry.interval}</td>
                <td style="text-align:right;">${entry.old_price !== null ? entry.old_price.toFixed(2) + ' \u20AC' : '\u2014'}</td>
                <td style="text-align:right;font-weight:600;">${entry.new_price !== null ? entry.new_price.toFixed(2) + ' \u20AC' : '\u2014'}</td>
                <td class="v2-change-cell ${changeCls}" style="text-align:right;">${entry.difference !== null ? diffSign + entry.difference.toFixed(2) + ' \u20AC' : '\u2014'} ${entry.percentage !== null ? '(' + diffSign + entry.percentage.toFixed(1) + '%)' : ''} ${badge}</td>
            </tr>`;
        }).join('');
    }

    function updateStatistics(stats) {
        document.getElementById('stat-total-changes').textContent = stats.total_changes;
        document.getElementById('stat-today-changes').textContent = stats.today_changes;
        document.getElementById('stat-last-change').textContent = stats.last_change ? formatDateTime(stats.last_change) : 'Never';
    }

    function resetFilters() {
        filterMonth.value = '';
        filterDateFrom.value = '';
        filterDateTo.value = '';
        loadHistory();
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    }

    function formatDateTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);
        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins} min ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        if (diffDays < 7) return `${diffDays}d ago`;
        return date.toLocaleDateString('fr-FR');
    }
});
