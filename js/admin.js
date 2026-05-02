// ============================================================
//  admin.js — GameHub Admin Panel Interactivity
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initSearch();
    initOrderStatusFilter();
    initSortableTables();
    initConfirmDeletes();
    initToast();
});

// ============================================================
// 1. SIDEBAR — Single-Page Navigation (no reload)
// ============================================================
function initSidebar() {
    const links = document.querySelectorAll('.sidebar-link[data-section]');
    const sections = document.querySelectorAll('.admin-section');

    function showSection(id) {
        sections.forEach(s => s.classList.toggle('active-section', s.id === id));
        links.forEach(l => l.classList.toggle('active', l.dataset.section === id));
        // Update URL hash silently
        history.replaceState(null, '', '#' + id);
    }

    links.forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            showSection(link.dataset.section);
        });
    });

    // On load, respect hash or default to dashboard
    const hash = location.hash.replace('#', '');
    const validIds = Array.from(sections).map(s => s.id);
    showSection(validIds.includes(hash) ? hash : validIds[0]);
}

// ============================================================
// 2. LIVE SEARCH — Games Table
// ============================================================
function initSearch() {
    const input = document.getElementById('gamesSearch');
    if (!input) return;

    input.addEventListener('input', () => {
        const q = input.value.trim().toLowerCase();
        const rows = document.querySelectorAll('#gamesTableBody tr');
        let visible = 0;
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const show = text.includes(q);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        // Show/hide empty state
        const empty = document.getElementById('gamesEmptySearch');
        if (empty) empty.style.display = visible === 0 ? '' : 'none';

        // Update count badge
        const badge = document.getElementById('gamesCount');
        if (badge) badge.textContent = visible;
    });
}

// ============================================================
// 3. ORDERS — Status Filter Tabs
// ============================================================
function initOrderStatusFilter() {
    const tabs = document.querySelectorAll('.order-filter-tab');
    if (!tabs.length) return;

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const filter = tab.dataset.filter;
            const rows = document.querySelectorAll('#ordersTableBody tr');
            let visible = 0;
            rows.forEach(row => {
                const status = row.dataset.status ?? '';
                const show = filter === 'all' || status === filter;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            const badge = document.getElementById('ordersCount');
            if (badge) badge.textContent = visible;
        });
    });
}

// ============================================================
// 4. SORTABLE TABLE HEADERS
// ============================================================
function initSortableTables() {
    document.querySelectorAll('table[data-sortable]').forEach(table => {
        const headers = table.querySelectorAll('th[data-col]');
        let lastCol = null;
        let ascending = true;

        headers.forEach(th => {
            th.style.cursor = 'pointer';
            th.title = 'Click to sort';

            th.addEventListener('click', () => {
                const col = th.dataset.col;
                ascending = lastCol === col ? !ascending : true;
                lastCol = col;

                // Update sort arrow indicator
                headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
                th.classList.add(ascending ? 'sort-asc' : 'sort-desc');

                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));

                rows.sort((a, b) => {
                    const aCell = a.querySelector(`td[data-col="${col}"]`);
                    const bCell = b.querySelector(`td[data-col="${col}"]`);
                    if (!aCell || !bCell) return 0;

                    const aVal = aCell.dataset.val ?? aCell.textContent.trim();
                    const bVal = bCell.dataset.val ?? bCell.textContent.trim();

                    const aNum = parseFloat(aVal);
                    const bNum = parseFloat(bVal);

                    if (!isNaN(aNum) && !isNaN(bNum)) {
                        return ascending ? aNum - bNum : bNum - aNum;
                    }
                    return ascending
                        ? aVal.localeCompare(bVal)
                        : bVal.localeCompare(aVal);
                });

                rows.forEach(r => tbody.appendChild(r));
            });
        });
    });
}

// ============================================================
// 5. CONFIRM BEFORE DELETE
// ============================================================
function initConfirmDeletes() {
    document.addEventListener('click', e => {
        const btn = e.target.closest('[data-confirm]');
        if (!btn) return;
        e.preventDefault();
        const msg = btn.dataset.confirm || 'Are you sure?';
        if (confirm(msg)) {
            const href = btn.href || btn.dataset.href;
            if (href) location.href = href;
            else btn.closest('form')?.submit();
        }
    });
}

// ============================================================
// 6. TOAST NOTIFICATIONS
// ============================================================
let toastContainer;

function initToast() {
    toastContainer = document.createElement('div');
    toastContainer.id = 'toast-container';
    toastContainer.style.cssText = `
        position: fixed; bottom: 24px; right: 24px;
        display: flex; flex-direction: column; gap: 10px;
        z-index: 9999; pointer-events: none;
    `;
    document.body.appendChild(toastContainer);

    // Auto-show toast if PHP set a flash message in data attribute
    const flash = document.body.dataset.flash;
    const flashType = document.body.dataset.flashType || 'success';
    if (flash) showToast(flash, flashType);
}

function showToast(message, type = 'success') {
    const colors = {
        success: '#16a34a',
        error:   '#dc2626',
        info:    '#2563eb',
        warning: '#ea580c',
    };
    const toast = document.createElement('div');
    toast.style.cssText = `
        background: ${colors[type] || colors.info};
        color: white; padding: 12px 18px; border-radius: 8px;
        font-size: 14px; font-weight: bold;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        pointer-events: all; opacity: 0;
        transform: translateY(10px);
        transition: opacity 0.25s, transform 0.25s;
        max-width: 320px;
    `;
    toast.textContent = message;
    toastContainer.appendChild(toast);

    // Animate in
    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });

    // Animate out after 3.5s
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// Expose globally for PHP-generated onclick attributes
window.showToast = showToast;
