<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: index.php");
    exit;
}

require_once 'php/db_connect.php';

// ============================================================
//  POST ACTIONS — handle before any output
// ============================================================

$action = $_POST['action'] ?? '';

// --- Edit game name/price ---
if ($action === 'edit_game') {
    $id    = (int)$_POST['game_id'];
    $name  = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $pdo->prepare('UPDATE Games SET name = ?, price = ? WHERE id = ?')->execute([$name, $price, $id]);
    $_SESSION['success'] = 'Game updated successfully.';
    header('Location: admin.php');
    exit;
}

// --- Add keys to a game ---
if ($action === 'add_keys') {
    $game_id  = (int)$_POST['game_id'];
    $keys_raw = trim($_POST['keys']);
    $keys     = array_filter(array_map('trim', explode("\n", $keys_raw)));
    $stmt     = $pdo->prepare('INSERT IGNORE INTO Game_Keys (game_id, key_code, is_sold) VALUES (?, ?, 0)');
    $added    = 0;
    foreach ($keys as $key) {
        if ($key) { $stmt->execute([$game_id, $key]); $added++; }
    }
    $_SESSION['success'] = "$added key(s) added successfully.";
    header('Location: admin.php');
    exit;
}

// --- Delete a game ---
if ($action === 'delete_game') {
    $id = (int)$_POST['game_id'];
    $pdo->prepare('DELETE FROM Games WHERE id = ?')->execute([$id]);
    $_SESSION['success'] = 'Game deleted.';
    header('Location: admin.php');
    exit;
}

// --- Update order status ---
if ($action === 'update_order_status') {
    $order_id = (int)$_POST['order_id'];
    $status   = $_POST['status'];
    $allowed  = ['pending', 'completed', 'delivered', 'cancelled'];
    if (in_array($status, $allowed)) {
        $pdo->prepare('UPDATE Orders SET status = ? WHERE id = ?')->execute([$status, $order_id]);
        $_SESSION['success'] = 'Order status updated.';
    }
    header('Location: admin.php');
    exit;
}

// --- Block / Unblock user ---
if ($action === 'toggle_user') {
    $target     = (int)$_POST['target_user_id'];
    $new_status = (int)$_POST['new_status'];
    if ($target !== (int)$_SESSION['user_id']) {
        $pdo->prepare('UPDATE Users SET is_active = ? WHERE id = ?')->execute([$new_status, $target]);
        $_SESSION['success'] = $new_status ? 'User unblocked.' : 'User blocked.';
    }
    header('Location: admin.php');
    exit;
}

// ============================================================
//  FETCH DATA
// ============================================================

$stmt = $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM Orders WHERE status IN ('delivered', 'completed')");
$total_revenue = (float)$stmt->fetchColumn();

$total_users  = (int)$pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();
$total_orders = (int)$pdo->query("SELECT COUNT(*) FROM Orders")->fetchColumn();

$stmt = $pdo->query("
    SELECT COUNT(*) FROM (
        SELECT game_id FROM Game_Keys
        WHERE is_sold = 0
        GROUP BY game_id
        HAVING COUNT(id) > 0 AND COUNT(id) < 5
    ) AS low
");
$low_stock_count = (int)$stmt->fetchColumn();

$recent_orders = $pdo->query("
    SELECT
        o.id, u.email AS user_email, g.name AS game_name,
        k.key_code AS key_value, oi.unit_price AS total_price,
        o.order_date AS created_at, o.status,
        (SELECT filename FROM Game_Images WHERE game_id = g.id AND is_cover = 1 LIMIT 1) AS cover_image
    FROM Order_Items oi
    JOIN Orders o    ON oi.order_id = o.id
    JOIN Users u     ON o.user_id   = u.id
    JOIN Games g     ON oi.game_id  = g.id
    JOIN Game_Keys k ON oi.key_id   = k.id
    ORDER BY o.order_date DESC LIMIT 50
")->fetchAll();

$games_list = $pdo->query("
    SELECT
        g.id, g.name, g.price,
        (SELECT filename FROM Game_Images WHERE game_id = g.id AND is_cover = 1 LIMIT 1) AS cover_image,
        (SELECT COUNT(*) FROM Game_Keys WHERE game_id = g.id AND is_sold = 0) AS stock_count
    FROM Games g
    ORDER BY g.name ASC
")->fetchAll();

$users_list = $pdo->query("
    SELECT id, email, role, is_active,
           (SELECT COUNT(*) FROM Orders WHERE user_id = Users.id) AS order_count
    FROM Users
    ORDER BY id ASC
")->fetchAll();

function statusBadge($s) {
    $s = strtolower($s);
    if ($s === 'delivered' || $s === 'completed') return '<span class="badge-green">Delivered</span>';
    if ($s === 'pending')   return '<span class="badge-blue">Pending</span>';
    if ($s === 'cancelled') return '<span class="badge-red">Cancelled</span>';
    return '<span class="badge-red">' . htmlspecialchars($s) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel — Ghos</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/dashboard-layout.css">
    <style>
        .admin-section { display:block; margin-bottom:40px; }
        .js-loaded .admin-section { display:none; margin-bottom:0; }
        .js-loaded .active-section { display:block !important; }

        .stats-grid { grid-template-columns:repeat(4,1fr) !important; }

        .badge-green { background:#dcfce7; color:#16a34a; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:bold; }
        .badge-blue  { background:#dbeafe; color:#2563eb; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:bold; }
        .badge-red   { background:#fee2e2; color:#dc2626; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:bold; }
        .badge-gray  { background:#f3f4f6; color:#6b7280; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:bold; }

        .game-cell { display:flex; align-items:center; gap:10px; }
        .mini-img img { width:35px; height:45px; object-fit:cover; border-radius:4px; }

        .order-filter-tab { background:#f3f4f6; border:1px solid #e0e0e0; padding:6px 14px; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer; color:#555; }
        .order-filter-tab:hover { background:#e5e7eb; }
        .order-filter-tab.active { background:#2563eb; color:#fff; border-color:#2563eb; }

        .btn-edit   { background:#e0e7ff; color:#3730a3; border:1px solid #c7d2fe; padding:4px 10px; border-radius:5px; font-size:12px; font-weight:bold; cursor:pointer; margin-right:3px; }
        .btn-keys   { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; padding:4px 10px; border-radius:5px; font-size:12px; font-weight:bold; cursor:pointer; margin-right:3px; }
        .btn-delete { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; padding:4px 10px; border-radius:5px; font-size:12px; font-weight:bold; cursor:pointer; }
        .btn-block  { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; padding:4px 10px; border-radius:5px; font-size:12px; font-weight:bold; cursor:pointer; }
        .btn-unblock{ background:#dcfce7; color:#166534; border:1px solid #bbf7d0; padding:4px 10px; border-radius:5px; font-size:12px; font-weight:bold; cursor:pointer; }
        .btn-edit:hover    { background:#c7d2fe; }
        .btn-keys:hover    { background:#bbf7d0; }
        .btn-delete:hover  { background:#fecaca; }
        .btn-block:hover   { background:#fecaca; }
        .btn-unblock:hover { background:#bbf7d0; }

        .status-select { font-size:12px; padding:4px 6px; border-radius:5px; border:1px solid #ddd; cursor:pointer; }

        /* Modals */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:#fff; border-radius:12px; padding:28px; width:480px; max-width:95vw; box-shadow:0 20px 60px rgba(0,0,0,0.3); }
        .modal-title { font-size:18px; font-weight:bold; margin-bottom:18px; color:#1a1a1a; }
        .modal-box .form-group { margin-bottom:14px; }
        .modal-box label { display:block; font-size:13px; font-weight:600; color:#555; margin-bottom:5px; }
        .modal-box input, .modal-box textarea {
            width:100%; padding:9px 12px; border:1px solid #ddd; border-radius:7px; font-size:14px; box-sizing:border-box;
        }
        .modal-box textarea { height:140px; resize:vertical; font-family:monospace; font-size:13px; }
        .modal-actions { display:flex; gap:10px; margin-top:20px; justify-content:flex-end; }
        .btn-save   { background:#2563eb; color:#fff; border:none; padding:9px 20px; border-radius:7px; font-weight:bold; cursor:pointer; }
        .btn-cancel { background:#f3f4f6; color:#555; border:1px solid #ddd; padding:9px 20px; border-radius:7px; cursor:pointer; }
        .btn-save:hover   { background:#1d4ed8; }
        .btn-cancel:hover { background:#e5e7eb; }

        .stock-low { color:#ea580c; font-weight:bold; }
        code { background:#f3f4f6; padding:2px 6px; border-radius:4px; font-size:12px; color:#2563eb; }
        .user-blocked td { opacity:0.55; }
    </style>
</head>
<body data-flash="<?= htmlspecialchars($_SESSION['success'] ?? '') ?>" data-flash-type="success">
<?php unset($_SESSION['success']); ?>

<aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-box">Ghos</div> Admin</div>
    <a href="#" class="sidebar-link active" data-section="section-dashboard">📊 Dashboard</a>
    <a href="#" class="sidebar-link" data-section="section-games">🎮 Manage Games</a>
    <a href="#" class="sidebar-link" data-section="section-orders">🛒 Orders</a>
    <a href="#" class="sidebar-link" data-section="section-users">👥 Users</a>
    <hr class="sidebar-divider">
    <a href="index.php" class="sidebar-back">← Store</a>
</aside>

<main class="main-content" id="mainContent">

    <!-- ── DASHBOARD ── -->
    <div id="section-dashboard" class="admin-section active-section">
        <h1 class="page-title">Dashboard Overview</h1>
        <div class="stats-grid">
            <div class="stat-card">
                <div><div class="stat-label">Total Revenue</div><div class="stat-value green">$<?= number_format($total_revenue, 2) ?></div></div>
                <div class="stat-icon icon-green">$</div>
            </div>
            <div class="stat-card">
                <div><div class="stat-label">Total Users</div><div class="stat-value blue"><?= $total_users ?></div></div>
                <div class="stat-icon icon-blue">👥</div>
            </div>
            <div class="stat-card">
                <div><div class="stat-label">Total Orders</div><div class="stat-value blue"><?= $total_orders ?></div></div>
                <div class="stat-icon icon-blue">🛒</div>
            </div>
            <div class="stat-card">
                <div><div class="stat-label">Low Stock</div><div class="stat-value orange"><?= $low_stock_count ?></div></div>
                <div class="stat-icon icon-orange">⚠️</div>
            </div>
        </div>
    </div>

    <!-- ── MANAGE GAMES ── -->
    <div id="section-games" class="admin-section panel">
        <div class="panel-header">
            <h2 class="page-title">Games (<span id="gamesCount"><?= count($games_list) ?></span>)</h2>
            <input id="gamesSearch" type="text" placeholder="Search games..." style="padding:8px;border-radius:6px;border:1px solid #ddd;">
        </div>
        <table class="data-table" data-sortable>
            <thead>
                <tr>
                    <th data-col="id">ID</th>
                    <th>Game</th>
                    <th data-col="price">Price</th>
                    <th data-col="stock">Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="gamesTableBody">
                <?php foreach ($games_list as $g): ?>
                <tr>
                    <td data-col="id" data-val="<?= $g['id'] ?>"><?= $g['id'] ?></td>
                    <td>
                        <div class="game-cell">
                            <div class="mini-img">
                                <img src="<?= htmlspecialchars(ltrim($g['cover_image'] ?? '', '/')) ?>" alt="" onerror="this.style.display='none'">
                            </div>
                            <span><?= htmlspecialchars($g['name']) ?></span>
                        </div>
                    </td>
                    <td data-col="price" data-val="<?= $g['price'] ?>">$<?= number_format($g['price'], 2) ?></td>
                    <td data-col="stock" data-val="<?= $g['stock_count'] ?>" class="<?= $g['stock_count'] < 5 ? 'stock-low' : '' ?>">
                        <?= $g['stock_count'] ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <button class="btn-edit" onclick="openEditGame(<?= $g['id'] ?>, <?= htmlspecialchars(json_encode($g['name'])) ?>, <?= $g['price'] ?>)">✏️ Edit</button>
                        <button class="btn-keys" onclick="openAddKeys(<?= $g['id'] ?>, <?= htmlspecialchars(json_encode($g['name'])) ?>)">🔑 Keys</button>
                        <button class="btn-delete" onclick="openDeleteGame(<?= $g['id'] ?>, <?= htmlspecialchars(json_encode($g['name'])) ?>)">🗑️ Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr id="gamesEmptySearch" style="display:none;"><td colspan="5" style="text-align:center;">No games found.</td></tr>
            </tbody>
        </table>
    </div>

    <!-- ── ORDERS ── -->
    <div id="section-orders" class="admin-section panel">
        <div class="panel-header">
            <h2 class="page-title">Orders (<span id="ordersCount"><?= count($recent_orders) ?></span>)</h2>
            <div style="display:flex;gap:8px;">
                <button class="order-filter-tab active" data-filter="all">All</button>
                <button class="order-filter-tab" data-filter="pending">Pending</button>
                <button class="order-filter-tab" data-filter="delivered">Delivered</button>
                <button class="order-filter-tab" data-filter="cancelled">Cancelled</button>
            </div>
        </div>
        <table class="data-table" data-sortable>
            <thead>
                <tr>
                    <th data-col="id">#</th>
                    <th>User</th>
                    <th>Game</th>
                    <th>CD Key</th>
                    <th data-col="price">Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="ordersTableBody">
                <?php foreach ($recent_orders as $o): ?>
                <tr data-status="<?= strtolower($o['status']) ?>">
                    <td data-col="id" data-val="<?= $o['id'] ?>"><?= $o['id'] ?></td>
                    <td><?= htmlspecialchars($o['user_email']) ?></td>
                    <td>
                        <div class="game-cell">
                            <div class="mini-img">
                                <img src="<?= htmlspecialchars(ltrim($o['cover_image'] ?? '', '/')) ?>" alt="" onerror="this.style.display='none'">
                            </div>
                            <span><?= htmlspecialchars($o['game_name']) ?></span>
                        </div>
                    </td>
                    <td><code><?= htmlspecialchars($o['key_value']) ?></code></td>
                    <td data-col="price" data-val="<?= $o['total_price'] ?>">$<?= number_format($o['total_price'], 2) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="update_order_status">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <select name="status" class="status-select" onchange="this.form.submit()">
                                <option value="pending"   <?= $o['status']==='pending'   ?'selected':'' ?>>Pending</option>
                                <option value="completed" <?= $o['status']==='completed' ?'selected':'' ?>>Completed</option>
                                <option value="delivered" <?= $o['status']==='delivered' ?'selected':'' ?>>Delivered</option>
                                <option value="cancelled" <?= $o['status']==='cancelled' ?'selected':'' ?>>Cancelled</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ── USERS ── -->
    <div id="section-users" class="admin-section panel">
        <div class="panel-header">
            <h2 class="page-title">Users (<?= count($users_list) ?>)</h2>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Orders</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users_list as $u): ?>
                <tr class="<?= !$u['is_active'] ? 'user-blocked' : '' ?>">
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge-gray"><?= htmlspecialchars($u['role']) ?></span></td>
                    <td><?= $u['order_count'] ?></td>
                    <td>
                        <?= $u['is_active']
                            ? '<span class="badge-green">Active</span>'
                            : '<span class="badge-red">Blocked</span>' ?>
                    </td>
                    <td>
                        <?php if ($u['id'] === (int)$_SESSION['user_id']): ?>
                            <span style="font-size:12px;color:#aaa;">You</span>
                        <?php else: ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="toggle_user">
                                <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="new_status" value="<?= $u['is_active'] ? 0 : 1 ?>">
                                <?php if ($u['is_active']): ?>
                                    <button type="submit" class="btn-block"
                                        onclick="return confirm('Block <?= htmlspecialchars(addslashes($u['email'])) ?>?')">
                                        🚫 Block
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn-unblock">✅ Unblock</button>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</main>

<!-- ── EDIT GAME MODAL ── -->
<div class="modal-overlay" id="modalEditGame">
    <div class="modal-box">
        <div class="modal-title">✏️ Edit Game</div>
        <form method="POST">
            <input type="hidden" name="action" value="edit_game">
            <input type="hidden" name="game_id" id="editGameId">
            <div class="form-group">
                <label>Game Name</label>
                <input type="text" name="name" id="editGameName" required>
            </div>
            <div class="form-group">
                <label>Price (USD)</label>
                <input type="number" name="price" id="editGamePrice" step="0.01" min="0" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('modalEditGame')">Cancel</button>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ── ADD KEYS MODAL ── -->
<div class="modal-overlay" id="modalAddKeys">
    <div class="modal-box">
        <div class="modal-title">🔑 Add Keys — <span id="addKeysGameName"></span></div>
        <form method="POST">
            <input type="hidden" name="action" value="add_keys">
            <input type="hidden" name="game_id" id="addKeysGameId">
            <div class="form-group">
                <label>CD Keys (one per line)</label>
                <textarea name="keys" placeholder="XXXXX-XXXXX-XXXXX&#10;XXXXX-XXXXX-XXXXX" required></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('modalAddKeys')">Cancel</button>
                <button type="submit" class="btn-save">Add Keys</button>
            </div>
        </form>
    </div>
</div>

<!-- ── DELETE GAME MODAL ── -->
<div class="modal-overlay" id="modalDeleteGame">
    <div class="modal-box">
        <div class="modal-title">🗑️ Delete Game</div>
        <p style="color:#555;margin-bottom:20px;">
            Are you sure you want to delete <strong id="deleteGameName"></strong>?<br>
            This will also remove all its keys and cannot be undone.
        </p>
        <form method="POST">
            <input type="hidden" name="action" value="delete_game">
            <input type="hidden" name="game_id" id="deleteGameId">
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('modalDeleteGame')">Cancel</button>
                <button type="submit" class="btn-delete">Yes, Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('mainContent').classList.add('js-loaded');

    function openModal(id)  { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }

    // Close modal when clicking the dark backdrop
    document.querySelectorAll('.modal-overlay').forEach(m => {
        m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
    });

    function openEditGame(id, name, price) {
        document.getElementById('editGameId').value    = id;
        document.getElementById('editGameName').value  = name;
        document.getElementById('editGamePrice').value = price;
        openModal('modalEditGame');
    }

    function openAddKeys(id, name) {
        document.getElementById('addKeysGameId').value        = id;
        document.getElementById('addKeysGameName').textContent = name;
        openModal('modalAddKeys');
    }

    function openDeleteGame(id, name) {
        document.getElementById('deleteGameId').value         = id;
        document.getElementById('deleteGameName').textContent = name;
        openModal('modalDeleteGame');
    }
</script>
<script src="js/admin.js?v=<?= time() ?>"></script>
</body>
</html>
