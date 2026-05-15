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
// 1. SIDEBAR — Dashboard always visible, others toggle
// ============================================================
function initSidebar() {
    const links    = document.querySelectorAll('.sidebar-link[data-section]');
    const sections = document.querySelectorAll('.admin-section');
    const DASHBOARD = 'section-dashboard';

    function showSection(id) {
        sections.forEach(s => {
            if (s.id === DASHBOARD) {
                s.style.display = 'block'; // always visible
            } else {
                s.style.display = s.id === id ? 'block' : 'none';
            }
        });
        links.forEach(l => l.classList.toggle('active', l.dataset.section === id));
        // If dashboard clicked, show ALL sections
        if (id === DASHBOARD) {
            sections.forEach(s => s.style.display = 'block');
        }
        history.replaceState(null, '', '#' + id);
    }

    links.forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            showSection(link.dataset.section);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    // On load: show dashboard only by default
    const hash = location.hash.replace('#', '');
    const validIds = Array.from(sections).map(s => s.id);
    showSection(validIds.includes(hash) ? hash : DASHBOARD);
}

// ============================================================
// 2. LIVE SEARCH — Games Table
// ============================================================
function initSearch() {
    const input = document.getElementById('gamesSearch');
    if (!input) return;

    input.addEventListener('input', () => {
        const q = input.value.trim().toLowerCase();
        const rows = document.querySelectorAll('#gamesTableBody tr:not(#gamesEmptySearch)');
        let visible = 0;
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const show = text.includes(q);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        const empty = document.getElementById('gamesEmptySearch');
        if (empty) empty.style.display = visible === 0 ? '' : 'none';

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
            const rows   = document.querySelectorAll('#ordersTableBody tr');
            let visible  = 0;
            rows.forEach(row => {
                const status = row.dataset.status ?? '';
                const show   = filter === 'all' || status === filter;
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
        let lastCol   = null;
        let ascending = true;

        headers.forEach(th => {
            th.style.cursor = 'pointer';
            th.title = 'Click to sort';

            th.addEventListener('click', () => {
                const col  = th.dataset.col;
                ascending  = lastCol === col ? !ascending : true;
                lastCol    = col;

                headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
                th.classList.add(ascending ? 'sort-asc' : 'sort-desc');

                const tbody = table.querySelector('tbody');
                const rows  = Array.from(tbody.querySelectorAll('tr:not(#gamesEmptySearch)'));

                rows.sort((a, b) => {
                    const aCell = a.querySelector(`td[data-col="${col}"]`);
                    const bCell = b.querySelector(`td[data-col="${col}"]`);
                    if (!aCell || !bCell) return 0;

                    const aVal = aCell.dataset.val ?? aCell.textContent.trim();
                    const bVal = bCell.dataset.val ?? bCell.textContent.trim();
                    const aNum = parseFloat(aVal);
                    const bNum = parseFloat(bVal);

                    if (!isNaN(aNum) && !isNaN(bNum)) return ascending ? aNum - bNum : bNum - aNum;
                    return ascending ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
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

    const flash     = document.body.dataset.flash;
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

    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

window.showToast = showToast;

// ============================================================
// 7. VIEW KEYS (INVENTORY)
// ============================================================
async function viewKeys(gameId, gameName) {
    document.getElementById('viewKeysGameName').textContent = gameName;
    const tbody = document.getElementById('keysTableBody');
    tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;">Loading...</td></tr>';
    document.getElementById('viewKeysModal').classList.add('open');

    const response = await fetch(`admin.php?action=get_keys&game_id=${gameId}`);
    const keys = await response.json();
    
    tbody.innerHTML = '';
    if (keys.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding: 20px;">No keys in stock.</td></tr>';
        return;
    }

    keys.forEach(k => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="font-family: monospace;">${k.key_code}</td>
            <td>${k.is_sold == 1 ? '<span class="badge-red">Sold</span>' : '<span class="badge-green">Available</span>'}</td>
            <td>
                ${k.is_sold == 0 ? `<button onclick="deleteKey(${k.id}, ${gameId}, this)" style="background:none; border:none; color:#ef4444; cursor:pointer; font-weight:bold;">Delete</button>` : '-'}
            </td>
        `;
        tbody.appendChild(tr);
    });
}
window.viewKeys = viewKeys;

async function deleteKey(keyId, gameId, btn) {
    if (!confirm('Delete this unsold key?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete_key');
    formData.append('key_id', keyId);
    formData.append('game_id', gameId);

    const response = await fetch('admin.php', { method: 'POST', body: formData });
    const res = await response.json();
    if (res.success) {
        btn.closest('tr').remove();
    } else {
        alert('Error: ' + res.error);
    }
}
window.deleteKey = deleteKey;
