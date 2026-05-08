<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// ── LOGOUT ───────────────────────────────────────
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

// ── PROTECTION ───────────────────────────────────
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    ?><!DOCTYPE html><html><head><meta charset="UTF-8"><title>Access Denied</title>
    <link rel="stylesheet" href="css/navbar.css"></head><body>
    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:80vh;text-align:center;gap:16px;">
        <div style="font-size:64px;">🚫</div>
        <div style="font-size:28px;font-weight:bold;">Access Denied</div>
        <div style="font-size:15px;color:#888;">Admin access only.</div>
        <a href="index.php" class="btn-blue">← Back to Store</a>
    </div></body></html><?php
    exit;
}

require_once 'php/db_connect.php';

// ── ACTIONS (POST) ───────────────────────────────
$add_error   = '';
$add_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Handle Add Game
    if ($action === 'add_game') {
        $name     = trim($_POST['name'] ?? '');
        $price    = (float)($_POST['price'] ?? 0);
        $platform = trim($_POST['platform'] ?? '');
        $genres   = trim($_POST['genres'] ?? '');

        if (empty($name) || $price <= 0 || empty($platform)) {
            $add_error = 'Name, price, and platform are required.';
        } else {
            $ins = $pdo->prepare('INSERT INTO Games (name, price, platform, genres) VALUES (?, ?, ?, ?)');
            $ins->execute([$name, $price, $platform, $genres]);
            $game_id = $pdo->lastInsertId();

            if (!empty($_FILES['cover_image']['name'])) {
                $file    = $_FILES['cover_image'];
                $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','webp'];
                if (in_array($ext, $allowed) && $file['size'] <= 5 * 1024 * 1024) {
                    $dir = 'images/games/';
                    if (!is_dir($dir)) mkdir($dir, 0777, true);
                    $filename = 'game_' . $game_id . '_' . time() . '.' . $ext;
                    $dest     = $dir . $filename;
                    if (move_uploaded_file($file['tmp_name'], $dest)) {
                        $pdo->prepare('INSERT INTO Game_Images (game_id, filename, is_cover) VALUES (?, ?, 1)')
                            ->execute([$game_id, $dest]);
                    }
                }
            }
            $add_success = "Game \"$name\" added successfully!";
        }
    }

    // Handle User Toggle (Block/Unblock)
    if ($action === 'toggle_user') {
        $target     = (int)$_POST['target_user_id'];
        $new_status = (int)$_POST['new_status'];
        if ($target !== (int)$_SESSION['user_id']) {
            $pdo->prepare('UPDATE Users SET is_active=? WHERE id=?')->execute([$new_status, $target]);
            header('Location: admin.php#section-users'); exit;
        }
    }
}

// ── FETCH DATA ───────────────────────────────────
$total_revenue = (float)$pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM Orders WHERE status IN ('delivered', 'completed')")->fetchColumn();
$total_users   = (int)$pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();
$total_orders  = (int)$pdo->query("SELECT COUNT(*) FROM Orders")->fetchColumn();
$low_stock_count = (int)$pdo->query("SELECT COUNT(*) FROM (SELECT g.id FROM Games g LEFT JOIN Game_Keys k ON g.id = k.game_id AND k.is_sold = 0 GROUP BY g.id HAVING COUNT(k.id) < 5) AS low")->fetchColumn();

$recent_orders = $pdo->query("
    SELECT o.id, u.email AS user_email, g.name AS game_name, i.filename AS cover_image, 
           k.key_code AS key_value, oi.unit_price AS total_price, o.order_date AS created_at, o.status
    FROM Orders o
    JOIN Users u ON o.user_id = u.id
    JOIN Order_Items oi ON o.id = oi.order_id
    JOIN Game_Keys k ON oi.key_id = k.id
    JOIN Games g ON oi.game_id = g.id
    LEFT JOIN Game_Images i ON g.id = i.game_id AND i.is_cover = 1
    ORDER BY o.order_date DESC LIMIT 10
")->fetchAll();

$games = $pdo->query("
    SELECT g.id, g.name, g.price, g.genres, g.platform, i.filename AS cover_image, COUNT(k.id) AS stock_count
    FROM Games g
    LEFT JOIN Game_Images i ON g.id = i.game_id AND i.is_cover = 1
    LEFT JOIN Game_Keys k ON g.id = k.game_id AND k.is_sold = 0
    GROUP BY g.id, g.name, g.price, g.genres, g.platform, i.filename
    ORDER BY g.name ASC
")->fetchAll();

$users_list = $pdo->query("
    SELECT id, email, role, is_active, (SELECT COUNT(*) FROM Orders WHERE user_id=Users.id) AS order_count
    FROM Users ORDER BY id ASC
")->fetchAll();

function stockBadge(int $n): string {
    if ($n === 0) return '<span class="badge-red">Out of Stock</span>';
    if ($n < 5)   return '<span class="badge-orange">Low Stock</span>';
    return '<span class="badge-green">Available</span>';
}

function statusBadge(string $s): string {
    return match(strtolower($s)) {
        'delivered','completed' => '<span class="badge-green">Delivered</span>',
        'pending'   => '<span class="badge-blue">Pending</span>',
        'cancelled' => '<span class="badge-red">Cancelled</span>',
        default     => '<span class="badge-orange">'.htmlspecialchars(ucfirst($s)).'</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — Ghos</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/dashboard-layout.css">
    <style>
        .admin-section { display: none; }
        .admin-section.active-section { display: block; }
        .badge-green { background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }
        .badge-blue  { background:#dbeafe;color:#2563eb;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }
        .badge-red   { background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }
        .badge-orange{ background:#ffedd5;color:#ea580c;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }
        .badge-gray  { background:#f3f4f6;color:#6b7280;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }
        .fg label { display:block;font-size:12px;font-weight:bold;color:#666;text-transform:uppercase;margin-bottom:5px; }
        .fg input,.fg select,.fg textarea { width:100%;padding:8px 12px;border:1px solid #e0e0e0;border-radius:6px;font-size:14px; }
        .upload-zone { border:2px dashed #e0e0e0;border-radius:8px;padding:20px;text-align:center;cursor:pointer;background:#fafafa;position:relative; }
        #img-preview { max-height:80px;border-radius:6px;margin-top:10px;display:none; }
        .user-blocked td { opacity: 0.5; }
        .btn-status { padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; cursor: pointer; border: none; }
        .btn-block { background: #fee2e2; color: #dc2626; }
        .btn-unblock { background: #dcfce7; color: #16a34a; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-box">Ghos</div> Admin</div>
    <a href="#" class="sidebar-link" data-section="section-dashboard">📊 Dashboard</a>
    <a href="#" class="sidebar-link" data-section="section-orders">🛒 Orders</a>
    <a href="#" class="sidebar-link" data-section="section-users">👥 Users</a>
    <a href="#" class="sidebar-link" data-section="section-games">🎮 Manage Games</a>
    <a href="#" class="sidebar-link" data-section="section-add-game">➕ Add Game</a>
    <hr class="sidebar-divider">
    <a href="index.php" class="sidebar-back">← Back to Store</a>
    <a href="?logout=1" class="sidebar-back" style="color:#ef4444;margin-top:8px;">🚪 Logout</a>
</aside>

<main class="main-content">

    <div id="section-dashboard" class="admin-section">
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

    <div id="section-users" class="admin-section panel">
        <div class="panel-header"><span class="panel-title">Users Management</span></div>
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Email</th><th>Role</th><th>Orders</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users_list as $user): ?>
                <tr class="<?= !$user['is_active'] ? 'user-blocked' : '' ?>">
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><span class="badge-gray"><?= strtoupper($user['role']) ?></span></td>
                    <td><?= $user['order_count'] ?></td>
                    <td><?= $user['is_active'] ? '<span class="badge-green">Active</span>' : '<span class="badge-red">Blocked</span>' ?></td>
                    <td>
                        <?php if ($user['id'] !== (int)$_SESSION['user_id']): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="toggle_user">
                            <input type="hidden" name="target_user_id" value="<?= $user['id'] ?>">
                            <input type="hidden" name="new_status" value="<?= $user['is_active'] ? 0 : 1 ?>">
                            <button type="submit" class="btn-status <?= $user['is_active'] ? 'btn-block' : 'btn-unblock' ?>">
                                <?= $user['is_active'] ? 'BLOCK' : 'UNBLOCK' ?>
                            </button>
                        </form>
                        <?php else: ?><small style="color:#aaa">You</small><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="section-orders" class="admin-section panel">
        <div class="panel-header"><span class="panel-title">Recent Orders</span></div>
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>User</th><th>Game</th><th>CD Key</th><th>Price</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders as $order): ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($order['game_name']) ?></td>
                    <td><code><?= htmlspecialchars($order['key_value'] ?? '—') ?></code></td>
                    <td>$<?= number_format($order['total_price'],2) ?></td>
                    <td><?= statusBadge($order['status']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="section-games" class="admin-section panel">
        <div class="panel-header"><span class="panel-title">Games List</span></div>
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Game</th><th>Price</th><th>Stock</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($games as $game): 
                    $img = ltrim($game['cover_image'] ?? '', '/');
                ?>
                <tr>
                    <td><?= $game['id'] ?></td>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <img src="<?= htmlspecialchars($img) ?>" style="width:30px; height:40px; object-fit:cover; border-radius:4px;" onerror="this.style.display='none'">
                            <?= htmlspecialchars($game['name']) ?>
                        </div>
                    </td>
                    <td>$<?= number_format($game['price'],2) ?></td>
                    <td><?= $game['stock_count'] ?></td>
                    <td><?= stockBadge($game['stock_count']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="section-add-game" class="admin-section">
        <h1 class="page-title">Add New Game</h1>
        <div class="panel">
            <?php if ($add_success): ?><div class="alert-sm success"><?= $add_success ?></div><?php endif; ?>
            <?php if ($add_error): ?><div class="alert-sm error"><?= $add_error ?></div><?php endif; ?>
            <form method="POST" enctype="multipart/form-data" class="add-game-form">
                <input type="hidden" name="action" value="add_game">
                <div class="form-row">
                    <div class="fg"><label>Game Name *</label><input type="text" name="name" required></div>
                    <div class="fg"><label>Price (USD) *</label><input type="number" name="price" step="0.01" required></div>
                </div>
                <div class="form-row">
                    <div class="fg">
                        <label>Platform *</label>
                        <select name="platform" required>
                            <option value="PC">PC</option><option value="PlayStation">PlayStation</option>
                            <option value="Xbox">Xbox</option><option value="Nintendo Switch">Nintendo Switch</option>
                        </select>
                    </div>
                    <div class="fg"><label>Genres</label><input type="text" name="genres"></div>
                </div>
                <div class="fg" style="margin-top:14px;">
                    <label>Cover Image</label>
                    <div class="upload-zone" id="uploadZone">
                        <input type="file" name="cover_image" id="coverInput" accept="image/*" onchange="previewImg(this)">
                        <div class="upload-zone-text"><strong>Click to upload</strong></div>
                        <img id="img-preview">
                    </div>
                </div>
                <div style="text-align:right; margin-top:20px;">
                    <button type="submit" class="btn-blue" style="padding:10px 30px;">Add Game</button>
                </div>
            </form>
        </div>
    </div>

</main>

<script src="js/admin.js"></script>
<script>
function previewImg(input) {
    const preview = document.getElementById('img-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
