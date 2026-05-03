/**
 * admin.js — Ghos Admin Panel
 */
document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initSortableTables();
    initSearch();
    initOrderStatusFilter();
    initToast();
});

/* =============================================
   SIDEBAR
   ============================================= */
function initSidebar() {
    const links = document.querySelectorAll('.sidebar-link[data-section]');
    const sections = document.querySelectorAll('.admin-section');

    function activate(sectionId, save) {
        sections.forEach(s => s.classList.remove('active-section'));
        links.forEach(l => l.classList.remove('active'));

        const target = document.getElementById(sectionId);
        if (target) target.classList.add('active-section');

        const link = document.querySelector('.sidebar-link[data-section="' + sectionId + '"]');
        if (link) link.classList.add('active');

        if (save) sessionStorage.setItem('adminSection', sectionId);
    }

    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            activate(this.dataset.section, true);
        });
    });

    // If PHP set a forced section (e.g. keys viewer), respect it
    var forced = document.body.dataset.forceSection;
    if (forced) {
        activate(forced, false);
    } else {
        var saved = sessionStorage.getItem('adminSection') || 'section-dashboard';
        // Make sure the saved section actually exists on this page
        if (!document.getElementById(saved)) saved = 'section-dashboard';
        activate(saved, false);
    }
}

/* =============================================
   SORTABLE TABLES
   ============================================= */
function initSortableTables() {
    document.querySelectorAll('table[data-sortable]').forEach(function(table) {
        var tbody = table.querySelector('tbody');
        if (!tbody) return;

        table.querySelectorAll('th[data-col]').forEach(function(th) {
            th.style.cursor = 'pointer';
            th.title = 'Click to sort';
            var asc = true;

            th.addEventListener('click', function() {
                var col = th.dataset.col;
                var rows = Array.from(tbody.querySelectorAll('tr:not(#gamesEmptySearch)'));

                rows.sort(function(a, b) {
                    var cellA = a.querySelector('td[data-col="' + col + '"]');
                    var cellB = b.querySelector('td[data-col="' + col + '"]');
                    if (!cellA || !cellB) return 0;

                    var valA = cellA.dataset.val !== undefined ? cellA.dataset.val : cellA.textContent.trim();
                    var valB = cellB.dataset.val !== undefined ? cellB.dataset.val : cellB.textContent.trim();

                    var numA = parseFloat(valA);
                    var numB = parseFloat(valB);

                    if (!isNaN(numA) && !isNaN(numB)) return asc ? numA - numB : numB - numA;
                    return asc ? valA.localeCompare(valB) : valB.localeCompare(valA);
                });

                table.querySelectorAll('th[data-col]').forEach(function(h) {
                    h.textContent = h.textContent.replace(/ [▲▼]$/, '');
                });
                th.textContent += asc ? ' ▲' : ' ▼';
                asc = !asc;

                rows.forEach(function(r) { tbody.appendChild(r); });
            });
        });
    });
}

/* =============================================
   LIVE SEARCH — Games table
   ============================================= */
function initSearch() {
    var input = document.getElementById('gamesSearch');
    var tbody = document.getElementById('gamesTableBody');
    var empty = document.getElementById('gamesEmptySearch');
    var countEl = document.getElementById('gamesCount');
    if (!input || !tbody) return;

    input.addEventListener('input', function() {
        var query = input.value.trim().toLowerCase();
        var rows = tbody.querySelectorAll('tr:not(#gamesEmptySearch)');
        var visible = 0;

        rows.forEach(function(row) {
            var match = row.textContent.toLowerCase().includes(query);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        if (empty) empty.style.display = visible === 0 ? '' : 'none';
        if (countEl) countEl.textContent = visible;
    });
}

/* =============================================
   ORDER STATUS FILTER TABS
   ============================================= */
function initOrderStatusFilter() {
    var tabs = document.querySelectorAll('.order-filter-tab');
    var tbody = document.getElementById('ordersTableBody');
    var countEl = document.getElementById('ordersCount');
    if (!tabs.length || !tbody) return;

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            tabs.forEach(function(t) { t.classList.remove('active'); });
            tab.classList.add('active');

            var filter = tab.dataset.filter;
            var rows = tbody.querySelectorAll('tr');
            var visible = 0;

            rows.forEach(function(row) {
                var status = row.dataset.status || '';
                var show = filter === 'all' || status === filter;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            if (countEl) countEl.textContent = visible;
        });
    });
}

/* =============================================
   TOAST
   ============================================= */
function initToast() {
    var msg = document.body.dataset.flash;
    if (!msg) return;
    showToast(msg, document.body.dataset.flashType || 'success');
}

function showToast(message, type) {
    var existing = document.querySelector('.admin-toast');
    if (existing) existing.remove();

    var toast = document.createElement('div');
    toast.className = 'admin-toast';
    toast.textContent = message;

    toast.style.position = 'fixed';
    toast.style.bottom = '24px';
    toast.style.right = '24px';
    toast.style.padding = '12px 20px';
    toast.style.borderRadius = '8px';
    toast.style.fontWeight = 'bold';
    toast.style.fontSize = '14px';
    toast.style.color = '#fff';
    toast.style.background = type === 'error' ? '#dc2626' : '#16a34a';
    toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    toast.style.zIndex = '9999';
    toast.style.opacity = '0';
    toast.style.transition = 'opacity 0.3s ease';

    document.body.appendChild(toast);

    requestAnimationFrame(function() { toast.style.opacity = '1'; });

    setTimeout(function() {
        toast.style.opacity = '0';
        toast.addEventListener('transitionend', function() { toast.remove(); });
    }, 3000);
}
