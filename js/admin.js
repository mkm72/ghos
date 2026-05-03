/**
 * admin.js — Ghos Admin Panel
 * Handles: sidebar navigation, table sorting, live search, order filter tabs, toast notifications
 */

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initSortableTables();
    initSearch();
    initOrderStatusFilter();
    initToast();
});

/* =============================================
   SIDEBAR — single-page section switching
   ============================================= */
function initSidebar() {
    const links = document.querySelectorAll('.sidebar-link[data-section]');
    const sections = document.querySelectorAll('.admin-section');

    // Activate a section by id
    function activate(sectionId) {
        sections.forEach(s => s.classList.remove('active-section'));
        links.forEach(l => l.classList.remove('active'));

        const target = document.getElementById(sectionId);
        if (target) target.classList.add('active-section');

        const link = document.querySelector(`.sidebar-link[data-section="${sectionId}"]`);
        if (link) link.classList.add('active');

        // Persist choice so a refresh stays on the same tab
        sessionStorage.setItem('adminSection', sectionId);
    }

    links.forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            activate(link.dataset.section);
        });
    });

    // Restore last tab, default to dashboard
    const saved = sessionStorage.getItem('adminSection') || 'section-dashboard';
    activate(saved);
}

/* =============================================
   SORTABLE TABLES
   ============================================= */
function initSortableTables() {
    document.querySelectorAll('table[data-sortable]').forEach(table => {
        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        table.querySelectorAll('th[data-col]').forEach(th => {
            th.style.cursor = 'pointer';
            th.title = 'Click to sort';
            let asc = true;

            th.addEventListener('click', () => {
                const col = th.dataset.col;
                const rows = Array.from(tbody.querySelectorAll('tr:not(#gamesEmptySearch)'));

                rows.sort((a, b) => {
                    const cellA = a.querySelector(`td[data-col="${col}"]`);
                    const cellB = b.querySelector(`td[data-col="${col}"]`);
                    if (!cellA || !cellB) return 0;

                    // Use data-val for numeric sorting if available
                    const valA = cellA.dataset.val ?? cellA.textContent.trim();
                    const valB = cellB.dataset.val ?? cellB.textContent.trim();

                    const numA = parseFloat(valA);
                    const numB = parseFloat(valB);
                    const isNumeric = !isNaN(numA) && !isNaN(numB);

                    if (isNumeric) return asc ? numA - numB : numB - numA;
                    return asc
                        ? valA.localeCompare(valB)
                        : valB.localeCompare(valA);
                });

                // Update sort indicator on header
                table.querySelectorAll('th[data-col]').forEach(h => {
                    h.textContent = h.textContent.replace(/ [▲▼]$/, '');
                });
                th.textContent += asc ? ' ▲' : ' ▼';
                asc = !asc;

                rows.forEach(r => tbody.appendChild(r));
            });
        });
    });
}

/* =============================================
   LIVE SEARCH — Games table
   ============================================= */
function initSearch() {
    const input = document.getElementById('gamesSearch');
    const tbody = document.getElementById('gamesTableBody');
    const empty = document.getElementById('gamesEmptySearch');
    const countEl = document.getElementById('gamesCount');
    if (!input || !tbody) return;

    input.addEventListener('input', () => {
        const query = input.value.trim().toLowerCase();
        const rows = tbody.querySelectorAll('tr:not(#gamesEmptySearch)');
        let visible = 0;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const match = text.includes(query);
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
    const tabs = document.querySelectorAll('.order-filter-tab');
    const tbody = document.getElementById('ordersTableBody');
    const countEl = document.getElementById('ordersCount');
    if (!tabs.length || !tbody) return;

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const filter = tab.dataset.filter;
            const rows = tbody.querySelectorAll('tr');
            let visible = 0;

            rows.forEach(row => {
                const status = row.dataset.status ?? '';
                const show = filter === 'all' || status === filter;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            if (countEl) countEl.textContent = visible;
        });
    });
}

/* =============================================
   TOAST — reads data-flash from <body>
   ============================================= */
function initToast() {
    const body = document.body;
    const msg = body.dataset.flash;
    if (!msg) return;

    const type = body.dataset.flashType || 'success';
    showToast(msg, type);
}

function showToast(message, type = 'success') {
    // Remove any existing toast
    document.querySelector('.admin-toast')?.remove();

    const toast = document.createElement('div');
    toast.className = 'admin-toast';
    toast.textContent = message;

    Object.assign(toast.style, {
        position: 'fixed',
        bottom: '24px',
        right: '24px',
        padding: '12px 20px',
        borderRadius: '8px',
        fontWeight: 'bold',
        fontSize: '14px',
        color: '#fff',
        background: type === 'error' ? '#dc2626' : '#16a34a',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        zIndex: '9999',
        opacity: '0',
        transition: 'opacity 0.3s ease',
    });

    document.body.appendChild(toast);

    // Fade in
    requestAnimationFrame(() => { toast.style.opacity = '1'; });

    // Fade out after 3 s
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.addEventListener('transitionend', () => toast.remove());
    }, 3000);
}
