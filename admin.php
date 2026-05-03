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
//  POST ACTIONS
// ============================================================
$action = $_POST['action'] ?? '';

if ($action === 'add_game') {
    $name  = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $desc  = trim($_POST['description'] ?? '');
    $plat  = trim($_POST['platform'] ?? '');
    $genre = trim($_POST['genres'] ?? '');
    if ($name && $price > 0) {
        $pdo->prepare('INSERT INTO Games (name, description, price, platform, genres) VALUES (?,?,?,?,?)')
            ->execute([$name, $desc, $price, $plat, $genre]);
        $_SESSION['success'] = "Game \"$name\" added.";
    }
    header('Location: admin.php'); exit;
}

if ($action === 'edit_game') {
    $id    = (int)$_POST['game_id'];
    $name  = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $desc  = trim($_POST['description'] ?? '');
    $plat  = trim($_POST['platform'] ?? '');
    $genre = trim($_POST['genres'] ?? '');
    $pdo->prepare('UPDATE Games SET name=?, price=?, description=?, platform=?, genres=? WHERE id=?')
        ->execute([$name, $price, $desc, $plat, $genre, $id]);
    $_SESSION['success'] = 'Game updated.';
    header('Location: admin.php'); exit;
}

if ($action === 'delete_game') {
    $id = (int)$_POST['game_id'];
    $pdo->prepare('DELETE FROM Games WHERE id = ?')->execute([$id]);
    $_SESSION['success'] = 'Game deleted.';
    header('Location: admin.php'); exit;
}

if ($action === 'add_keys') {
    $game_id = (int)$_POST['game_id'];
    $keys    = array_filter(array_map('trim', explode("\n", $_POST['keys'] ?? '')));
    $stmt    = $pdo->prepare('INSERT IGNORE INTO Game_Keys (game_id, key_code, is_sold) VALUES (?,?,0)');
    $added   = 0;
    foreach ($keys as $k) { if ($k) { $stmt->execute([$game_id, $k]); $added++; } }
    $_SESSION['success'] = "$added key(s) added.";
    header('Location: admin.php'); exit;
}

if ($action === 'delete_key') {
    $key_id = (int)$_POST['key_id'];
    // Only allow deleting unsold keys
    $pdo->prepare('DELETE FROM Game_Keys WHERE id = ? AND is_sold = 0')->execute([$key_id]);
    $_SESSION['success'] = 'Key deleted.';
    header('Location: admin.php'); exit;
}

if ($action === 'update_order_status') {
    $order_id = (int)$_POST['order_id'];
    $status   = $_POST['status'];
    $allowed  = ['pending','completed','delivered','cancelled'];
    if (in_array($status, $allowed)) {
        $pdo->prepare('UPDATE Orders SET status=? WHERE id=?')->execute([$status, $order_id]);
        $_SESSION['success'] = 'Order status updated.';
    }
    header('Location: admin.php'); exit;
}

if ($action === 'toggle_user') {
    $target     = (int)$_POST['target_user_id'];
    $new_status = (int)$_POST['new_status'];
    if ($target !== (int)$_SESSION['user_id']) {
        $pdo->prepare('UPDATE Users SET is_active=? WHERE id=?')->execute([$new_status, $target]);
        $_SESSION['success'] = $new_status ? 'User unblocked.' : 'User blocked.';
    }
    header('Location: admin.php'); exit;
}

// ============================================================
//  FETCH DATA
// ============================================================
$total_revenue   = (float)$pdo->query("SELECT COALESCE(SUM(total_price),0) FROM Orders WHERE status IN ('delivered','completed')")->fetchColumn();
$total_users     = (int)$pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();
$total_orders    = (int)$pdo->query("SELECT COUNT(*) FROM Orders")->fetchColumn();
$low_stock_count = (int)$pdo->query("SELECT COUNT(*) FROM (SELECT game_id FROM Game_Keys WHERE is_sold=0 GROUP BY game_id HAVING COUNT(id)>0 AND COUNT(id)<5) AS low")->fetchColumn();

$recent_orders = $pdo->query("
    SELECT o.id, u.email AS user_email, g.name AS game_name,
           k.key_code AS key_value, oi.unit_price AS total_price,
           o.order_date, o.status,
           (SELECT filename FROM Game_Images WHERE game_id=g.id AND is_cover=1 LIMIT 1) AS cover_image
    FROM Order_Items oi
    JOIN Orders o    ON oi.order_id = o.id
    JOIN Users u     ON o.user_id   = u.id
    JOIN Games g     ON oi.game_id  = g.id
    JOIN Game_Keys k ON oi.key_id   = k.id
    ORDER BY o.order_date DESC LIMIT 50
")->fetchAll();

$games_list = $pdo->query("
    SELECT g.id, g.name, g.price, g.description, g.platform, g.genres,
           (SELECT filename FROM Game_Images WHERE game_id=g.id AND is_cover=1 LIMIT 1) AS cover_image,
           (SELECT COUNT(*) FROM Game_Keys WHERE game_id=g.id AND is_sold=0) AS stock_unsold,
           (SELECT COUNT(*) FROM Game_Keys WHERE game_id=g.id AND is_sold=1) AS stock_sold
    FROM Games g ORDER BY g.name ASC
")->fetchAll();

$users_list = $pdo->query("
    SELECT id, email, role, is_active,
           (SELECT COUNT(*) FROM Orders WHERE user_id=Users.id) AS order_count
    FROM Users ORDER BY id ASC
")->fetchAll();

// Keys viewer — loaded when admin clicks "View Keys" for a game
$view_game_id = (int)($_GET['keys_game'] ?? 0);
$keys_list    = [];
$keys_game_name = '';
if ($view_game_id) {
    $kg = $pdo->prepare('SELECT name FROM Games WHERE id=?');
    $kg->execute([$view_game_id]);
    $keys_game_name = $kg->fetchColumn();
    $ks = $pdo->prepare('SELECT id, key_code, is_sold FROM Game_Keys WHERE game_id=? ORDER BY is_sold ASC, id ASC');
    $ks->execute([$view_game_id]);
    $keys_list = $ks->fetchAll();
}

function statusBadge($s) {
    $map = ['delivered'=>'badge-green','completed'=>'badge-green','pending'=>'badge-blue','cancelled'=>'badge-red'];
    $cls = $map[strtolower($s)] ?? 'badge-red';
    return '<span class="'.$cls.'">'.htmlspecialchars(ucfirst($s)).'</span>';
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
        .badge-orange{ background:#ffedd5; color:#ea580c; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:bold; }

        .game-cell { display:flex; align-items:center; gap:10px; }
        .mini-img img { width:35px; height:45px; object-fit:cover; border-radius:4px; }

        .order-filter-tab { background:#f3f4f6; border:1px solid #e0e0e0; padding:6px 14px; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer; color:#555; }
        .order-filter-tab:hover { background:#e5e7eb; }
        .order-filter-tab.active { background:#2563eb; color:#fff; border-color:#2563eb; }

        /* All action buttons */
        .btn { display:inline-block; padding:4px 10px; border-radius:5px; font-size:12px; font-weight:bold; cursor:pointer; border:1px solid; margin-right:3px; }
        .btn-primary  { background:#2563eb; color:#fff; border-color:#2563eb; }
        .btn-primary:hover { background:#1d4ed8; }
        .btn-edit     { background:#e0e7ff; color:#3730a3; border-color:#c7d2fe; }
        .btn-edit:hover { background:#c7d2fe; }
        .btn-green    { background:#dcfce7; color:#166534; border-color:#bbf7d0; }
        .btn-green:hover { background:#bbf7d0; }
        .btn-red      { background:#fee2e2; color:#991b1b; border-color:#fecaca; }
        .btn-red:hover { background:#fecaca; }

        .status-select { font-size:12px; padding:4px 6px; border-radius:5px; border:1px solid #ddd; cursor:pointer; }
        .stock-low { color:#ea580c; font-weight:bold; }
        code { background:#f3f4f6; padding:2px 6px; border-radius:4px; font-size:12px; color:#2563eb; font-family:monospace; }

        /* Modals */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:1000; align-items:center; justify-content:center; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:#fff; border-radius:12px; padding:28px; width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.3); }
        .modal-title { font-size:17px; font-weight:bold; margin-bottom:18px; color:#1a1a1a; }
        .form-group { margin-bottom:13px; }
        .form-group label { display:block; font-size:13px; font-weight:600; color:#555; margin-bottom:4px; }
        .form-group input, .form-group textarea, .form-group select {
            width:100%; padding:8px 11px; border:1px solid #ddd; border-radius:7px; font-size:14px; box-sizing:border-box;
        }
        .form-group textarea { resize:vertical; font-family:monospace; font-size:13px; }
        .modal-actions { display:flex; gap:10px; margin-top:18px; justify-content:flex-end; }
        .btn-save   { background:#2563eb; color:#fff; border:none; padding:8px 20px; border-radius:7px; font-weight:bold; cursor:pointer; font-size:14px; }
        .btn-save:hover { background:#1d4ed8; }
        .btn-cancel { background:#f3f4f6; color:#555; border:1px solid #ddd; padding:8px 20px; border-radius:7px; cursor:pointer; font-size:14px; }
        .btn-cancel:hover { background:#e5e7eb; }

        /* Keys panel inside modal */
        .keys-table { width:100%; border-collapse:collapse; margin-top:12px; font-size:13px; }
        .keys-table th { background:#f9f9f9; padding:8px 10px; text-align:left; border-bottom:1px solid #eee; font-size:11px; text-transform:uppercase; color:#888; }
        .keys-table td { padding:8px 10px; border-bottom:1px solid #f3f3f3; }
        .keys-table tr:last-child td { border-bottom:none; }

        .user-blocked td { opacity:0.55; }

        /* Add game button in panel header */
        .panel-header { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid #e0e0e0; flex-wrap:wrap; gap:8px; }
        .panel-header h2 { margin:0; }
    </style>
</head>
<body data-flash="<?= htmlspecialchars($_SESSION['success'] ?? '') ?>" data-flash-type="success">
<?php unset($_SESSION['success']); ?>

<aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-box">Ghos</div> Admin</div>
    <a href="#" class="sidebar-link active" data-section="section-dashboard">Dashboard</a>
    <a href="#" class="sidebar-link" data-section="section-games">Manage Games</a>
    <a href="#" class="sidebar-link" data-section="section-orders">Orders</a>
    <a href="#" class="sidebar-link" data-section="section-users">Users</a>
    <hr class="sidebar-divider">
    <a href="index.php" class="sidebar-back">← Store</a>
</aside>

<main class="main-content" id="mainContent">

    <!-- DASHBOARD -->
    <div id="section-dashboard" class="admin-section active-section">
        <h1 class="page-title">Dashboard Overview</h1>
        <div class="stats-grid">
            <div class="stat-card">
                <div><div class="stat-label">Total Revenue</div><div class="stat-value green">$<?= number_format($total_revenue,2) ?></div></div>
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

    <!-- MANAGE GAMES -->
    <div id="section-games" class="admin-section panel">
        <div class="panel-header">
            <h2 class="page-title">Games (<span id="gamesCount"><?= count($games_list) ?></span>)</h2>
            <div style="display:flex;gap:8px;align-items:center;">
                <input id="gamesSearch" type="text" placeholder="Search games..." style="padding:7px 10px;border-radius:6px;border:1px solid #ddd;font-size:13px;">
                <button class="btn btn-primary" onclick="openModal('modalAddGame')">+ Add Game</button>
            </div>
        </div>
        <table class="data-table" data-sortable>
            <thead>
                <tr>
                    <th data-col="id">ID</th>
                    <th>Game</th>
                    <th data-col="price">Price</th>
                    <th>Available</th>
                    <th>Sold</th>
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
                                <img src="<?= htmlspecialchars(ltrim($g['cover_image']??'','/')) ?>" alt="" onerror="this.style.display='none'">
                            </div>
                            <span><?= htmlspecialchars($g['name']) ?></span>
                        </div>
                    </td>
                    <td data-col="price" data-val="<?= $g['price'] ?>">$<?= number_format($g['price'],2) ?></td>
                    <td class="<?= $g['stock_unsold'] < 5 ? 'stock-low' : '' ?>"><?= $g['stock_unsold'] ?></td>
                    <td><?= $g['stock_sold'] ?></td>
                    <td style="white-space:nowrap;">
                        <button class="btn btn-edit" onclick="openEditGame(
                            <?= $g['id'] ?>,
                            <?= htmlspecialchars(json_encode($g['name'])) ?>,
                            <?= $g['price'] ?>,
                            <?= htmlspecialchars(json_encode($g['description']??'')) ?>,
                            <?= htmlspecialchars(json_encode($g['platform']??'')) ?>,
                            <?= htmlspecialchars(json_encode($g['genres']??'')) ?>
                        )">Edit</button>
                        <button class="btn btn-green" onclick="openAddKeys(<?= $g['id'] ?>, <?= htmlspecialchars(json_encode($g['name'])) ?>)">Add Keys</button>
                        <a href="admin.php?keys_game=<?= $g['id'] ?>" class="btn btn-green" style="text-decoration:none;">View Keys</a>
                        <button class="btn btn-red" onclick="openDeleteGame(<?= $g['id'] ?>, <?= htmlspecialchars(json_encode($g['name'])) ?>)">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr id="gamesEmptySearch" style="display:none;"><td colspan="6" style="text-align:center;color:#aaa;padding:20px;">No games found.</td></tr>
            </tbody>
        </table>
    </div>

    <!-- KEYS VIEWER (shown when ?keys_game=X) -->
    <?php if ($view_game_id && $keys_game_name): ?>
    <div id="section-keys" class="admin-section panel">
        <div class="panel-header">
            <h2 class="page-title">Keys — <?= htmlspecialchars($keys_game_name) ?></h2>
            <div style="display:flex;gap:8px;">
                <button class="btn btn-green" onclick="openAddKeys(<?= $view_game_id ?>, <?= htmlspecialchars(json_encode($keys_game_name)) ?>)">Add Keys</button>
                <a href="admin.php" class="btn btn-edit" style="text-decoration:none;">Back to Games</a>
            </div>
        </div>
        <table class="keys-table data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Key Code</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($keys_list as $k): ?>
                <tr>
                    <td><?= $k['id'] ?></td>
                    <td><code><?= htmlspecialchars($k['key_code']) ?></code></td>
                    <td>
                        <?php if ($k['is_sold']): ?>
                            <span class="badge-red">Sold</span>
                        <?php else: ?>
                            <span class="badge-green">Available</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$k['is_sold']): ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this key?')">
                            <input type="hidden" name="action" value="delete_key">
                            <input type="hidden" name="key_id" value="<?= $k['id'] ?>">
                            <button type="submit" class="btn btn-red">Delete</button>
                        </form>
                        <?php else: ?>
                            <span style="font-size:12px;color:#aaa;">Cannot delete sold key</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($keys_list)): ?>
                <tr><td colspan="4" style="text-align:center;color:#aaa;padding:20px;">No keys found for this game.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- ORDERS -->
    <div id="section-orders" class="admin-section panel">
        <div class="panel-header">
            <h2 class="page-title">Orders (<span id="ordersCount"><?= count($recent_orders) ?></span>)</h2>
            <div style="display:flex;gap:8px;">
                <button class="order-filter-tab active" data-filter="all">All</button>
                <button class="order-filter-tab" data-filter="pending">Pending</button>
                <button class="order-filter-tab" data-filter="completed">Completed</button>
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
                                <img src="<?= htmlspecialchars(ltrim($o['cover_image']??'','/')) ?>" alt="" onerror="this.style.display='none'">
                            </div>
                            <span><?= htmlspecialchars($o['game_name']) ?></span>
                        </div>
                    </td>
                    <td><code><?= htmlspecialchars($o['key_value']) ?></code></td>
                    <td data-col="price" data-val="<?= $o['total_price'] ?>">$<?= number_format($o['total_price'],2) ?></td>
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

    <!-- USERS -->
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
                    <td><?= $u['is_active'] ? '<span class="badge-green">Active</span>' : '<span class="badge-red">Blocked</span>' ?></td>
                    <td>
                        <?php if ($u['id'] === (int)$_SESSION['user_id']): ?>
                            <span style="font-size:12px;color:#aaa;">You</span>
                        <?php else: ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="toggle_user">
                                <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="new_status" value="<?= $u['is_active'] ? 0 : 1 ?>">
                                <?php if ($u['is_active']): ?>
                                    <button type="submit" class="btn btn-red" onclick="return confirm('Block <?= htmlspecialchars(addslashes($u['email'])) ?>?')">Block</button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-green">Unblock</button>
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

<!-- ADD GAME MODAL -->
<div class="modal-overlay" id="modalAddGame">
    <div class="modal-box">
        <div class="modal-title">Add New Game</div>
        <form method="POST">
            <input type="hidden" name="action" value="add_game">
            <div class="form-group">
                <label>Game Name *</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Price (USD) *</label>
                <input type="number" name="price" step="0.01" min="0.01" required>
            </div>
            <div class="form-group">
                <label>Platform</label>
                <input type="text" name="platform" placeholder="PC, Xbox One, PlayStation 4">
            </div>
            <div class="form-group">
                <label>Genres</label>
                <input type="text" name="genres" placeholder="Action, RPG">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4"></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('modalAddGame')">Cancel</button>
                <button type="submit" class="btn-save">Add Game</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT GAME MODAL -->
<div class="modal-overlay" id="modalEditGame">
    <div class="modal-box">
        <div class="modal-title">Edit Game</div>
        <form method="POST">
            <input type="hidden" name="action" value="edit_game">
            <input type="hidden" name="game_id" id="editGameId">
            <div class="form-group">
                <label>Game Name *</label>
                <input type="text" name="name" id="editGameName" required>
            </div>
            <div class="form-group">
                <label>Price (USD) *</label>
                <input type="number" name="price" id="editGamePrice" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label>Platform</label>
                <input type="text" name="platform" id="editGamePlatform">
            </div>
            <div class="form-group">
                <label>Genres</label>
                <input type="text" name="genres" id="editGameGenres">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="editGameDesc" rows="4"></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('modalEditGame')">Cancel</button>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ADD KEYS MODAL -->
<div class="modal-overlay" id="modalAddKeys">
    <div class="modal-box">
        <div class="modal-title">Add Keys — <span id="addKeysGameName"></span></div>
        <form method="POST">
            <input type="hidden" name="action" value="add_keys">
            <input type="hidden" name="game_id" id="addKeysGameId">
            <div class="form-group">
                <label>CD Keys (one per line)</label>
                <textarea name="keys" rows="6" placeholder="XXXXX-XXXXX-XXXXX&#10;XXXXX-XXXXX-XXXXX" required></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('modalAddKeys')">Cancel</button>
                <button type="submit" class="btn-save">Add Keys</button>
            </div>
        </form>
    </div>
</div>

<!-- DELETE GAME MODAL -->
<div class="modal-overlay" id="modalDeleteGame">
    <div class="modal-box">
        <div class="modal-title">Delete Game</div>
        <p style="color:#555;margin-bottom:20px;">
            Are you sure you want to delete <strong id="deleteGameName"></strong>?
            All its keys will also be removed. This cannot be undone.
        </p>
        <form method="POST">
            <input type="hidden" name="action" value="delete_game">
            <input type="hidden" name="game_id" id="deleteGameId">
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('modalDeleteGame')">Cancel</button>
                <button type="submit" class="btn-save" style="background:#dc2626;">Yes, Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('mainContent').classList.add('js-loaded');

    function openModal(id)  { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }

    document.querySelectorAll('.modal-overlay').forEach(m => {
        m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
    });

    function openEditGame(id, name, price, desc, platform, genres) {
        document.getElementById('editGameId').value       = id;
        document.getElementById('editGameName').value     = name;
        document.getElementById('editGamePrice').value    = price;
        document.getElementById('editGameDesc').value     = desc;
        document.getElementById('editGamePlatform').value = platform;
        document.getElementById('editGameGenres').value   = genres;
        openModal('modalEditGame');
    }

    function openAddKeys(id, name) {
        document.getElementById('addKeysGameId').value         = id;
        document.getElementById('addKeysGameName').textContent = name;
        openModal('modalAddKeys');
    }

    function openDeleteGame(id, name) {
        document.getElementById('deleteGameId').value         = id;
        document.getElementById('deleteGameName').textContent = name;
        openModal('modalDeleteGame');
    }

    <?php if ($view_game_id): ?>
    // Auto-switch to keys section if viewing keys
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active-section'));
        document.getElementById('section-keys').classList.add('active-section');
        document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
        document.querySelector('[data-section="section-games"]').classList.add('active');
    });
    <?php endif; ?>
</script>
<script src="js/admin.js?v=<?= time() ?>"></script>
</body>
</html>
