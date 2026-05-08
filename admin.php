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
    header("Location: index.php");
    exit;
}

require_once 'php/db_connect.php';

// ── ACTIONS (POST) ───────────────────────────────
$add_error   = '';
$add_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Add Game Action
    if ($action === 'add_game') {
        $name     = trim($_POST['name'] ?? '');
        $price    = (float)($_POST['price'] ?? 0);
        $platform = trim($_POST['platform'] ?? '');
        $genres   = trim($_POST['genres'] ?? '');

        if (empty($name) || $price <= 0) {
            $add_error = 'Name and price are required.';
        } else {
            $ins = $pdo->prepare('INSERT INTO Games (name, price, platform, genres) VALUES (?, ?, ?, ?)');
            $ins->execute([$name, $price, $platform, $genres]);
            $game_id = $pdo->lastInsertId();

            if (!empty($_FILES['cover_image']['name'])) {
                $file = $_FILES['cover_image'];
                $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp']) && $file['size'] <= 5 * 1024 * 1024) {
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
            $add_success = "Game \"$name\" added successfully!";
        }
    }

    // 2. Update Order Status Action
    if ($action === 'update_order_status') {
        $order_id = (int)$_POST['order_id'];
        $status   = $_POST['status'];
        $allowed  = ['pending', 'completed', 'delivered', 'cancelled'];
        if (in_array($status, $allowed)) {
            $pdo->prepare('UPDATE Orders SET status=? WHERE id=?')->execute([$status, $order_id]);
            header('Location: admin.php#section-orders'); exit;
        }
    }

    // 3. Toggle User Action
    if ($action === 'toggle_user') {
        $target = (int)$_POST['target_user_id'];
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
           oi.unit_price AS total_price, o.order_date, o.status
    FROM Orders o
    JOIN Users u ON o.user_id = u.id
    JOIN Order_Items oi ON o.id = oi.order_id
    JOIN Games g ON oi.game_id = g.id
    LEFT JOIN Game_Images i ON g.id = i.game_id AND i.is_cover = 1
    ORDER BY o.order_date DESC LIMIT 20
")->fetchAll();

$users_list = $pdo->query("SELECT id, email, role, is_active FROM Users ORDER BY id ASC")->fetchAll();

$games = $pdo->query("
    SELECT g.id, g.name, g.price, 
           MAX(i.filename) AS cover_image, 
           COUNT(k.id) AS stock
    FROM Games g
    LEFT JOIN Game_Images i ON g.id = i.game_id AND i.is_cover = 1
    LEFT JOIN Game_Keys k ON g.id = k.game_id AND k.is_sold = 0
    GROUP BY g.id, g.name, g.price
    ORDER BY g.id DESC
")->fetchAll();
function statusBadgeClass($s) {
    return match(strtolower($s)) {
        'delivered', 'completed' => 'badge-green',
        'pending'   => 'badge-blue',
        'cancelled' => 'badge-red',
        default     => 'badge-gray',
    };
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
        .admin-section { display: none; }
        .active-section { display: block; }
        .badge-green { background:#dcfce7; color:#16a34a; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:bold; }
        .badge-blue  { background:#dbeafe; color:#2563eb; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:bold; }
        .badge-red   { background:#fee2e2; color:#dc2626; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:bold; }
        .status-select { padding:4px; font-size:12px; border-radius:5px; border:1px solid #ddd; cursor:pointer; }
        
        /* Enhanced Add Game Form */
        .add-game-card { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; font-size: 13px; color: #444; margin-bottom: 8px; text-transform: uppercase; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; transition: 0.3s; }
        .form-group input:focus { border-color: #2563eb; outline: none; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        
        .upload-area { border: 2px dashed #cbd5e1; border-radius: 10px; padding: 20px; text-align: center; cursor: pointer; transition: 0.3s; background: #f8fafc; }
        .upload-area:hover { border-color: #2563eb; background: #eff6ff; }
        #img-preview { max-width: 150px; margin-top: 15px; border-radius: 8px; display: none; margin-left: auto; margin-right: auto; }
        
        .user-blocked { opacity: 0.5; background: #f1f1f1; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-box">Ghos</div> Admin</div>
    <a href="#" class="sidebar-link active" data-section="section-dashboard">📊 Dashboard</a>
    <a href="#" class="sidebar-link" data-section="section-orders">🛒 Orders</a>
    <a href="#" class="sidebar-link" data-section="section-users">👥 Users</a>
    <a href="#" class="sidebar-link" data-section="section-games">🎮 Manage Games</a>
    <a href="#" class="sidebar-link" data-section="section-add-game">➕ Add Game</a>
    <hr class="sidebar-divider">
    <a href="index.php" class="sidebar-back">← Back to Store</a>
    <a href="?logout=1" class="sidebar-back" style="color:#ef4444; margin-top:10px;">🚪 Logout</a>
</aside>

<main class="main-content">

    <div id="section-dashboard" class="admin-section active-section">
        <h1 class="page-title">Dashboard Overview</h1>
        <div class="stats-grid">
            <div class="stat-card">
                <div><div class="stat-label">Revenue</div><div class="stat-value green">$<?= number_format($total_revenue,2) ?></div></div>
                <div class="stat-icon icon-green">$</div>
            </div>
            <div class="stat-card">
                <div><div class="stat-label">Users</div><div class="stat-value blue"><?= $total_users ?></div></div>
                <div class="stat-icon icon-blue">👥</div>
            </div>
            <div class="stat-card">
                <div><div class="stat-label">Orders</div><div class="stat-value blue"><?= $total_orders ?></div></div>
                <div class="stat-icon icon-blue">🛒</div>
            </div>
            <div class="stat-card">
                <div><div class="stat-label">Low Stock</div><div class="stat-value orange"><?= $low_stock_count ?></div></div>
                <div class="stat-icon icon-orange">⚠️</div>
            </div>
        </div>
    </div>

    <div id="section-orders" class="admin-section panel">
        <div class="panel-header"><span class="panel-title">Order Management</span></div>
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>User</th><th>Game</th><th>Price</th><th>Date</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders as $o): ?>
                <tr>
                    <td>#<?= $o['id'] ?></td>
                    <td><?= htmlspecialchars($o['user_email']) ?></td>
                    <td><?= htmlspecialchars($o['game_name']) ?></td>
                    <td>$<?= number_format($o['total_price'],2) ?></td>
                    <td><?= date('M j, Y', strtotime($o['order_date'])) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="update_order_status">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <select name="status" class="status-select" onchange="this.form.submit()">
                                <option value="pending" <?= $o['status']=='pending'?'selected':'' ?>>Pending</option>
                                <option value="delivered" <?= $o['status']=='delivered'?'selected':'' ?>>Delivered</option>
                                <option value="completed" <?= $o['status']=='completed'?'selected':'' ?>>Completed</option>
                                <option value="cancelled" <?= $o['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="section-users" class="admin-section panel">
        <div class="panel-header"><span class="panel-title">Users List</span></div>
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Email</th><th>Role</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users_list as $u): ?>
                <tr class="<?= !$u['is_active'] ? 'user-blocked' : '' ?>">
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge-blue"><?= strtoupper($u['role']) ?></span></td>
                    <td>
                        <?php if($u['id'] != $_SESSION['user_id']): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="toggle_user">
                            <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="new_status" value="<?= $u['is_active'] ? 0 : 1 ?>">
                            <button type="submit" class="status-select"><?= $u['is_active'] ? 'Block' : 'Unblock' ?></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="section-add-game" class="admin-section">
        <h1 class="page-title">Add New Collection</h1>
        <div class="add-game-card">
            <?php if($add_success): ?><div class="badge-green" style="display:block; padding:10px; margin-bottom:20px;"><?= $add_success ?></div><?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_game">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Game Title</label>
                        <input type="text" name="name" placeholder="e.g. Cyberpunk 2077" required>
                    </div>
                    <div class="form-group">
                        <label>Base Price ($)</label>
                        <input type="number" name="price" step="0.01" placeholder="59.99" required>
                    </div>
                    <div class="form-group">
                        <label>Platform</label>
                        <select name="platform">
                            <option value="PC">PC (Steam/Epic)</option>
                            <option value="PS5">PlayStation 5</option>
                            <option value="Xbox">Xbox Series X</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Genres</label>
                        <input type="text" name="genres" placeholder="Action, RPG, Open World">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Cover Art</label>
                    <div class="upload-area" onclick="document.getElementById('coverInput').click()">
                        <input type="file" name="cover_image" id="coverInput" accept="image/*" style="display:none" onchange="previewImg(this)">
                        <div id="upload-text">📸 Click to upload game cover</div>
                        <img id="img-preview">
                    </div>
                </div>

                <div style="text-align:right;">
                    <button type="submit" class="btn-blue" style="padding:12px 40px; border-radius:8px; cursor:pointer;">Publish Game</button>
                </div>
            </form>
        </div>
    </div>

    <div id="section-games" class="admin-section panel">
        <div class="panel-header"><span class="panel-title">In-Store Games</span></div>
        <table class="data-table">
            <thead>
                <tr><th>Cover</th><th>Name</th><th>Price</th><th>Stock</th></tr>
            </thead>
            <tbody>
                <?php foreach ($games as $g): ?>
                <tr>
                    <td><img src="<?= htmlspecialchars(ltrim($g['cover_image']??'','/')) ?>" style="width:40px; height:50px; object-fit:cover; border-radius:4px;"></td>
                    <td><?= htmlspecialchars($g['name']) ?></td>
                    <td>$<?= number_format($g['price'],2) ?></td>
                    <td><?= $g['stock'] ?> keys</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</main>

<script>
    // Navigation logic
    document.querySelectorAll('.sidebar-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active-section'));
            document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
            
            const target = this.getAttribute('data-section');
            document.getElementById(target).classList.add('active-section');
            this.classList.add('active');
        });
    });

    // Image preview logic
    function previewImg(input) {
        const preview = document.getElementById('img-preview');
        const text = document.getElementById('upload-text');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => { 
                preview.src = e.target.result; 
                preview.style.display = 'block';
                text.style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
</body>
</html>
