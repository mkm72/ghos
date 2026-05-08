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

$action = $_POST['action'] ?? '';

if ($action === 'add_game') {
    $name  = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $desc  = trim($_POST['description'] ?? '');
    $plat  = trim($_POST['platform'] ?? '');
    $genre = trim($_POST['genres'] ?? '');

    if ($name && $price > 0) {
        $stmt = $pdo->prepare('INSERT INTO Games (name, description, price, platform, genres) VALUES (?,?,?,?,?)');
        $stmt->execute([$name, $desc, $price, $plat, $genre]);
        $game_id = $pdo->lastInsertId();

        if (!empty($_FILES['cover_image']['name'])) {
            $file = $_FILES['cover_image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowed) && $file['size'] <= 5 * 1024 * 1024) {
                $dir = 'images/games/';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                
                $filename = 'game_' . $game_id . '_' . time() . '.' . $ext;
                $dest = $dir . $filename;

                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $pdo->prepare('INSERT INTO Game_Images (game_id, filename, is_cover) VALUES (?, ?, 1)')
                        ->execute([$game_id, $dest]);
                }
            }
        }
        $_SESSION['success'] = "Game \"$name\" added successfully!";
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
    $pdo->prepare('DELETE FROM Order_Items WHERE game_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM Game_Images WHERE game_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM Game_Keys WHERE game_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM Games WHERE id = ?')->execute([$id]);
    $_SESSION['success'] = 'Game deleted.';
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

if ($action === 'add_keys') {
    $game_id = (int)$_POST['game_id'];
    $keys    = array_filter(array_map('trim', explode("\n", $_POST['keys'] ?? '')));
    $stmt    = $pdo->prepare('INSERT IGNORE INTO Game_Keys (game_id, key_code, is_sold) VALUES (?,?,0)');
    $added   = 0;
    foreach ($keys as $k) { if ($k) { $stmt->execute([$game_id, $k]); $added++; } }
    $_SESSION['success'] = "$added key(s) added.";
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

$total_revenue   = (float)$pdo->query("SELECT COALESCE(SUM(total_price),0) FROM Orders WHERE status IN ('delivered','completed')")->fetchColumn();
$total_users      = (int)$pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();
$total_orders     = (int)$pdo->query("SELECT COUNT(*) FROM Orders")->fetchColumn();
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

$users_list = $pdo->query("
    SELECT id, email, role, is_active,
           (SELECT COUNT(*) FROM Orders WHERE user_id=Users.id) AS order_count
    FROM Users ORDER BY id ASC
")->fetchAll();

$games_list = $pdo->query("
    SELECT g.id, g.name, g.price, g.description, g.platform, g.genres,
           (SELECT filename FROM Game_Images WHERE game_id=g.id AND is_cover=1 LIMIT 1) AS cover_image,
           (SELECT COUNT(*) FROM Game_Keys WHERE game_id=g.id AND is_sold=0) AS stock_unsold,
           (SELECT COUNT(*) FROM Game_Keys WHERE game_id=g.id AND is_sold=1) AS stock_sold
    FROM Games g ORDER BY g.name ASC
")->fetchAll();

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
        .game-cell { display:flex; align-items:center; gap:10px; }
        .mini-img img { width:35px; height:45px; object-fit:cover; border-radius:4px; }
        .btn { display:inline-block; padding:4px 10px; border-radius:5px; font-size:12px; font-weight:bold; cursor:pointer; border:1px solid; margin-right:3px; }
        .btn-primary { background:#2563eb; color:#fff; border-color:#2563eb; }
        .btn-red { background:#fee2e2; color:#991b1b; border-color:#fecaca; }
        .btn-green { background:#dcfce7; color:#166534; border-color:#bbf7d0; }
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:1000; align-items:center; justify-content:center; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:#fff; border-radius:12px; padding:28px; width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; }
        .form-group { margin-bottom:13px; }
        .form-group label { display:block; font-size:13px; font-weight:600; margin-bottom:4px; }
        .form-group input, .form-group textarea { width:100%; padding:8px; border:1px solid #ddd; border-radius:7px; box-sizing:border-box; }
        .user-blocked td { opacity:0.55; }
    </style>
</head>
<body data-flash="<?= htmlspecialchars($_SESSION['success'] ?? '') ?>">
<?php unset($_SESSION['success']); ?>

<aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-box">Ghos</div> Admin</div>
    <a href="#" class="sidebar-link active" data-section="section-dashboard">Dashboard</a>
    <a href="#" class="sidebar-link" data-section="section-orders">Orders</a>
    <a href="#" class="sidebar-link" data-section="section-users">Users</a>
    <a href="#" class="sidebar-link" data-section="section-games">Manage Games</a> 
    <hr class="sidebar-divider">
    <a href="index.php" class="sidebar-back">← Store</a>
</aside>

<main class="main-content" id="mainContent">

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

    <div id="section-orders" class="admin-section panel">
        <div class="panel-header"><h2 class="page-title">Orders</h2></div>
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>User</th><th>Game</th><th>Price</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders as $o): ?>
                <tr>
                    <td><?= $o['id'] ?></td>
                    <td><?= htmlspecialchars($o['user_email']) ?></td>
                    <td><?= htmlspecialchars($o['game_name']) ?></td>
                    <td>$<?= number_format($o['total_price'],2) ?></td>
                    <td><?= statusBadge($o['status']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="section-users" class="admin-section panel">
        <div class="panel-header"><h2 class="page-title">Users Management</h2></div>
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Email</th><th>Role</th><th>Orders</th><th>Status</th><th>Action</th></tr>
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
                        <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="toggle_user">
                            <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="new_status" value="<?= $u['is_active'] ? 0 : 1 ?>">
                            <button type="submit" class="btn <?= $u['is_active'] ? 'btn-red' : 'btn-green' ?>">
                                <?= $u['is_active'] ? 'Block' : 'Unblock' ?>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="section-games" class="admin-section panel">
        <div class="panel-header">
            <h2 class="page-title">Manage Games</h2>
            <button class="btn btn-primary" onclick="openModal('modalAddGame')">+ Add Game</button>
        </div>
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Game</th><th>Price</th><th>Stock</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($games_list as $g): ?>
                <tr>
                    <td><?= $g['id'] ?></td>
                    <td>
                        <div class="game-cell">
                            <div class="mini-img">
                                <img src="<?= htmlspecialchars(ltrim($g['cover_image']??'','/')) ?>" alt="" onerror="this.style.display='none'">
                            </div>
                            <span><?= htmlspecialchars($g['name']) ?></span>
                        </div>
                    </td>
                    <td>$<?= number_format($g['price'],2) ?></td>
                    <td><?= $g['stock_unsold'] ?></td>
                    <td style="white-space:nowrap;">
                        <button class="btn btn-green" onclick="openAddKeys(<?= $g['id'] ?>, '<?= addslashes($g['name']) ?>')">Add Keys</button>
                        <button class="btn btn-red" onclick="openDeleteGame(<?= $g['id'] ?>, '<?= addslashes($g['name']) ?>')">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</main>

<div class="modal-overlay" id="modalAddGame">
    <div class="modal-box">
        <div class="modal-title">Add New Game</div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_game">
            <div class="form-group">
                <label>Game Name *</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Price (USD) *</label>
                <input type="number" name="price" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Platform</label>
                <input type="text" name="platform" placeholder="PC, PS5, Xbox...">
            </div>
            <div class="form-group">
                <label>Genres</label>
                <input type="text" name="genres" placeholder="Action, RPG...">
            </div>
            <div class="form-group">
                <label>Game Cover Image</label>
                <input type="file" name="cover_image" accept="image/*">
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

<div class="modal-overlay" id="modalAddKeys">
    <div class="modal-box">
        <div class="modal-title">Add Keys — <span id="addKeysGameName"></span></div>
        <form method="POST">
            <input type="hidden" name="action" value="add_keys">
            <input type="hidden" name="game_id" id="addKeysGameId">
            <div class="form-group">
                <label>CD Keys (one per line)</label>
                <textarea name="keys" rows="6" placeholder="XXXXX-XXXXX-XXXXX" required></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('modalAddKeys')">Cancel</button>
                <button type="submit" class="btn-save">Add Keys</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalDeleteGame">
    <div class="modal-box">
        <div class="modal-title">Delete Game</div>
        <p>Are you sure you want to delete <strong id="deleteGameName"></strong>?</p>
        <form method="POST">
            <input type="hidden" name="action" value="delete_game">
            <input type="hidden" name="game_id" id="deleteGameId">
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('modalDeleteGame')">Cancel</button>
                <button type="submit" class="btn-save" style="background:#dc2626; color:white; border:none;">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('mainContent').classList.add('js-loaded');
    function openModal(id)  { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }
    
    function openAddKeys(id, name) {
        document.getElementById('addKeysGameId').value = id;
        document.getElementById('addKeysGameName').textContent = name;
        openModal('modalAddKeys');
    }

    function openDeleteGame(id, name) {
        document.getElementById('deleteGameId').value = id;
        document.getElementById('deleteGameName').textContent = name;
        openModal('modalDeleteGame');
    }

    document.querySelectorAll('.sidebar-link').forEach(link => {
        link.addEventListener('click', function(e) {
            if(this.dataset.section) {
                e.preventDefault();
                document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active-section'));
                document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
                document.getElementById(this.dataset.section).classList.add('active-section');
                this.classList.add('active');
            }
        });
    });
</script>
</body>
</html>
