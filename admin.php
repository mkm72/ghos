<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if (isset($_GET['logout'])) { session_unset(); session_destroy(); header('Location: index.php'); exit; }

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Access Denied</title><link rel="stylesheet" href="css/navbar.css"></head><body>';
    echo '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:80vh;text-align:center;gap:16px;">';
    echo '<div style="font-size:64px;">🚫</div><div style="font-size:28px;font-weight:bold;">Access Denied</div>';
    echo '<div style="font-size:15px;color:#888;">Admin access only.</div>';
    echo '<a href="index.php" class="btn-blue">← Back to Store</a></div></body></html>';
    exit;
}

require_once 'php/db_connect.php';
$is_logged_in = true;
$user_role    = $_SESSION['role'];

$flash = ''; $flash_type = 'success';

// ═══════════════════════════════════════════════
// HANDLE ACTIONS
// ═══════════════════════════════════════════════

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── Add Game ─────────────────────────────────
if ($action === 'add_game' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $price    = (float)($_POST['price'] ?? 0);
    $platform = trim($_POST['platform'] ?? '');
    $genres   = trim($_POST['genres'] ?? '');
    $desc     = trim($_POST['description'] ?? '');
    if (!$name || $price <= 0 || !$platform) {
        $flash = 'Name, price, and platform are required.'; $flash_type = 'error';
    } else {
        $ins = $pdo->prepare('INSERT INTO Games (name, description, price, platform, genres) VALUES (?,?,?,?,?)');
        $ins->execute([$name, $desc, $price, $platform, $genres]);
        $game_id = $pdo->lastInsertId();
        if (!empty($_FILES['cover_image']['name'])) {
            $file = $_FILES['cover_image'];
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp']) && $file['size'] <= 5*1024*1024) {
                $dir = 'images/games/';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $fname = 'game_'.$game_id.'_'.time().'.'.$ext;
                if (move_uploaded_file($file['tmp_name'], $dir.$fname)) {
                    $pdo->prepare('INSERT INTO Game_Images (game_id, filename, is_cover) VALUES (?,?,1)')
                        ->execute([$game_id, $dir.$fname]);
                }
            }
        }
        $flash = "Game \"$name\" added successfully!";
    }
}

// ── Delete Game ───────────────────────────────
if ($action === 'delete_game' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $pdo->prepare('DELETE FROM Games WHERE id = ?')->execute([$id]);
    $flash = 'Game deleted.'; $flash_type = 'error';
    header('Location: admin.php?section=section-games&flash='.urlencode($flash).'&flash_type=error'); exit;
}

// ── Edit Game ─────────────────────────────────
if ($action === 'edit_game' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['game_id'] ?? 0);
    $name     = trim($_POST['name'] ?? '');
    $price    = (float)($_POST['price'] ?? 0);
    $platform = trim($_POST['platform'] ?? '');
    $genres   = trim($_POST['genres'] ?? '');
    $desc     = trim($_POST['description'] ?? '');
    $pdo->prepare('UPDATE Games SET name=?,description=?,price=?,platform=?,genres=? WHERE id=?')
        ->execute([$name, $desc, $price, $platform, $genres, $id]);
    if (!empty($_FILES['cover_image']['name'])) {
        $file = $_FILES['cover_image'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp']) && $file['size'] <= 5*1024*1024) {
            $dir = 'images/games/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $fname = 'game_'.$id.'_'.time().'.'.$ext;
            if (move_uploaded_file($file['tmp_name'], $dir.$fname)) {
                $pdo->prepare('DELETE FROM Game_Images WHERE game_id=? AND is_cover=1')->execute([$id]);
                $pdo->prepare('INSERT INTO Game_Images (game_id, filename, is_cover) VALUES (?,?,1)')
                    ->execute([$id, $dir.$fname]);
            }
        }
    }
    $flash = "Game updated successfully!";
    header('Location: admin.php?section=section-games&flash='.urlencode($flash)); exit;
}

// ── Add Key ───────────────────────────────────
if ($action === 'add_key' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $game_id = (int)($_POST['game_id'] ?? 0);
    $keys_raw = trim($_POST['keys'] ?? '');
    $keys = array_filter(array_map('trim', explode("\n", $keys_raw)));
    $added = 0;
    $stmt  = $pdo->prepare('INSERT IGNORE INTO Game_Keys (game_id, key_code) VALUES (?,?)');
    foreach ($keys as $k) { if ($k) { $stmt->execute([$game_id, $k]); $added++; } }
    $flash = "$added key(s) added successfully!";
    header('Location: admin.php?section=section-games&flash='.urlencode($flash)); exit;
}

// ── Delete Key ────────────────────────────────
if ($action === 'delete_key' && isset($_GET['id'])) {
    $pdo->prepare('DELETE FROM Game_Keys WHERE id=? AND is_sold=0')->execute([(int)$_GET['id']]);
    $flash = 'Key deleted.'; $flash_type = 'error';
    header('Location: admin.php?section=section-games&flash='.urlencode($flash).'&flash_type=error'); exit;
}

// ── Toggle User Active ────────────────────────
if ($action === 'toggle_user' && isset($_GET['id'])) {
    $id  = (int)$_GET['id'];
    $cur = $pdo->prepare('SELECT is_active FROM Users WHERE id=?');
    $cur->execute([$id]);
    $row = $cur->fetch();
    $new = $row ? (int)!$row['is_active'] : 1;
    $pdo->prepare('UPDATE Users SET is_active=? WHERE id=?')->execute([$new, $id]);
    $flash = $new ? 'User enabled.' : 'User disabled.';
    $flash_type = $new ? 'success' : 'error';
    header('Location: admin.php?section=section-users&flash='.urlencode($flash).'&flash_type='.$flash_type); exit;
}

// ── Change User Role ──────────────────────────
if ($action === 'change_role' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id   = (int)($_POST['user_id'] ?? 0);
    $role = $_POST['role'] ?? 'user';
    if (in_array($role, ['admin','business','customer','user'])) {
        $pdo->prepare('UPDATE Users SET role=? WHERE id=?')->execute([$role, $id]);
        $flash = 'Role updated.';
    }
    header('Location: admin.php?section=section-users&flash='.urlencode($flash)); exit;
}

// ── Delete User ───────────────────────────────
if ($action === 'delete_user' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id !== (int)$_SESSION['user_id']) {
        $pdo->prepare('DELETE FROM Users WHERE id=?')->execute([$id]);
        $flash = 'User deleted.'; $flash_type = 'error';
    } else { $flash = 'Cannot delete your own account.'; $flash_type = 'error'; }
    header('Location: admin.php?section=section-users&flash='.urlencode($flash).'&flash_type='.$flash_type); exit;
}

// ── Approve/Reject Business Application ──────
if ($action === 'review_app' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_id    = (int)($_POST['app_id'] ?? 0);
    $decision  = $_POST['decision'] ?? '';
    $user_id   = (int)($_POST['user_id'] ?? 0);
    if (in_array($decision, ['approved','rejected'])) {
        $pdo->prepare('UPDATE Business_Applications SET status=?, reviewed_at=NOW() WHERE id=?')
            ->execute([$decision, $app_id]);
        if ($decision === 'approved' && $user_id) {
            $pdo->prepare('UPDATE Users SET role=? WHERE id=?')->execute(['business', $user_id]);
        }
        $flash = $decision === 'approved' ? 'Application approved. User is now a seller.' : 'Application rejected.';
        $flash_type = $decision === 'approved' ? 'success' : 'error';
    }
    header('Location: admin.php?section=section-business-apps&flash='.urlencode($flash).'&flash_type='.$flash_type); exit;
}

// ── Update Order Status ───────────────────────
if ($action === 'update_order_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? 'pending';
    if (in_array($status, ['pending','delivered','cancelled','completed'])) {
        $pdo->prepare('UPDATE Orders SET status=? WHERE id=?')->execute([$status, $id]);
        $flash = 'Order status updated.';
    }
    header('Location: admin.php?section=section-orders&flash='.urlencode($flash)); exit;
}

// ── Create Business_Applications table if not exists ──
try {
    $pdo->exec('CREATE TABLE IF NOT EXISTS Business_Applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        business_name VARCHAR(200) NOT NULL,
        reason TEXT,
        status ENUM("pending","approved","rejected") DEFAULT "pending",
        created_at DATETIME DEFAULT NOW(),
        reviewed_at DATETIME NULL,
        FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE
    )');
} catch (\PDOException $e) { /* already exists */ }

// ── Fetch Business Applications ────────────────
try {
    $biz_apps = $pdo->query("
        SELECT ba.*, u.email AS user_email
        FROM Business_Applications ba
        JOIN Users u ON ba.user_id = u.id
        ORDER BY FIELD(ba.status,'pending','approved','rejected'), ba.created_at DESC
    ")->fetchAll();
} catch (\PDOException $e) { $biz_apps = []; }

// ── Flash from redirect ───────────────────────
if (isset($_GET['flash'])) { $flash = $_GET['flash']; $flash_type = $_GET['flash_type'] ?? 'success'; }

// ═══════════════════════════════════════════════
// FETCH DATA
// ═══════════════════════════════════════════════

// Stats
$total_revenue   = (float)$pdo->query("SELECT COALESCE(SUM(total_price),0) FROM Orders WHERE status IN ('delivered','completed')")->fetchColumn();
$total_users     = (int)$pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();
$total_orders    = (int)$pdo->query("SELECT COUNT(*) FROM Orders")->fetchColumn();
$low_stock_count = (int)$pdo->query("SELECT COUNT(*) FROM (SELECT g.id FROM Games g LEFT JOIN Game_Keys k ON g.id=k.game_id AND k.is_sold=0 GROUP BY g.id HAVING COUNT(k.id)<5) x")->fetchColumn();

// Orders
$orders = $pdo->query("
    SELECT o.id, u.email AS user_email, o.total_price, o.order_date, o.status,
           GROUP_CONCAT(g.name SEPARATOR ', ') AS game_names
    FROM Orders o
    JOIN Users u ON o.user_id=u.id
    JOIN Order_Items oi ON o.id=oi.order_id
    JOIN Games g ON oi.game_id=g.id
    GROUP BY o.id ORDER BY o.order_date DESC
")->fetchAll();

// Games with stock
$games = $pdo->query("
    SELECT g.id, g.name, g.price, g.platform, g.genres, g.description,
           i.filename AS cover_image,
           COUNT(k.id) AS stock_count
    FROM Games g
    LEFT JOIN Game_Images i ON g.id=i.game_id AND i.is_cover=1
    LEFT JOIN Game_Keys k ON g.id=k.game_id AND k.is_sold=0
    GROUP BY g.id ORDER BY g.name ASC
")->fetchAll();

// Users
$users = $pdo->query("SELECT id, email, role, is_active FROM Users ORDER BY id ASC")->fetchAll();

// Get edit game if requested
$edit_game = null;
if (isset($_GET['edit_game'])) {
    $stmt = $pdo->prepare("SELECT g.*, i.filename AS cover_image FROM Games g LEFT JOIN Game_Images i ON g.id=i.game_id AND i.is_cover=1 WHERE g.id=?");
    $stmt->execute([(int)$_GET['edit_game']]);
    $edit_game = $stmt->fetch();
}

// Get keys for a game if requested
$view_keys_game = null;
$game_keys = [];
if (isset($_GET['view_keys'])) {
    $gid = (int)$_GET['view_keys'];
    $stmt = $pdo->prepare("SELECT id, name FROM Games WHERE id=?");
    $stmt->execute([$gid]);
    $view_keys_game = $stmt->fetch();
    $stmt = $pdo->prepare("SELECT * FROM Game_Keys WHERE game_id=? ORDER BY is_sold ASC, id DESC");
    $stmt->execute([$gid]);
    $game_keys = $stmt->fetchAll();
}

$active_section = $_GET['section'] ?? 'section-dashboard';
$bg_colors = ['bg-purple','bg-green','bg-dark','bg-blue','bg-red','bg-navy','bg-black','bg-forest'];

function stockBadge(int $n): string {
    if ($n===0) return '<span class="badge-red">Out of Stock</span>';
    if ($n<5)   return '<span class="badge-orange">Low ('.$n.')</span>';
    return '<span class="badge-green">In Stock</span>';
}
function statusBadge(string $s): string {
    return match(strtolower($s)) {
        'delivered','completed' => '<span class="badge-green">'.ucfirst($s).'</span>',
        'pending'   => '<span class="badge-blue">Pending</span>',
        'cancelled' => '<span class="badge-red">Cancelled</span>',
        default     => '<span class="badge-orange">'.htmlspecialchars(ucfirst($s)).'</span>',
    };
}
function roleBadge(string $r): string {
    $map = ['admin'=>'badge-red','business'=>'badge-orange','customer'=>'badge-blue','user'=>'badge-blue'];
    $cls = $map[$r] ?? 'badge-orange';
    return '<span class="'.$cls.'">'.ucfirst(htmlspecialchars($r)).'</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — GameHub</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/dashboard-layout.css">
    <style>
        .admin-section { display:none; }
        .admin-section.active-section { display:block; }

        .badge-green  { background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }
        .badge-blue   { background:#dbeafe;color:#2563eb;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }
        .badge-red    { background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }
        .badge-orange { background:#ffedd5;color:#ea580c;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }

        .order-filter-tab { padding:5px 14px;border-radius:20px;border:1px solid #e0e0e0;background:white;font-size:12px;font-weight:bold;color:#888;cursor:pointer;transition:all .15s; }
        .order-filter-tab.active { background:#1a1a1a;color:white;border-color:#1a1a1a; }

        th[data-col] { cursor:pointer; }
        th[data-col]:hover { background:#f0f0f0; }
        th.sort-asc::after  { content:' ↑';color:#2563eb; }
        th.sort-desc::after { content:' ↓';color:#2563eb; }

        .search-input { padding:6px 12px;border:1px solid #e0e0e0;border-radius:6px;font-size:13px;outline:none;width:200px; }
        .search-input:focus { border-color:#2563eb; }

        .stats-grid { grid-template-columns:repeat(4,1fr); }

        /* Forms */
        .form-panel { padding:20px; }
        .form-row { display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px; }
        .form-row.full { grid-template-columns:1fr; }
        .form-row.three { grid-template-columns:1fr 1fr 1fr; }
        .fg label { display:block;font-size:12px;font-weight:bold;color:#666;text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px; }
        .fg input,.fg select,.fg textarea { width:100%;padding:8px 12px;border:1px solid #e0e0e0;border-radius:6px;font-size:14px;outline:none;background:#fff;box-sizing:border-box; }
        .fg input:focus,.fg select:focus,.fg textarea:focus { border-color:#2563eb; }
        .fg textarea { resize:vertical;min-height:80px; }
        .form-actions { display:flex;justify-content:flex-end;gap:10px;padding:0 20px 20px; }

        /* Upload zone */
        .upload-zone { border:2px dashed #e0e0e0;border-radius:8px;padding:18px;text-align:center;cursor:pointer;transition:all .2s;background:#fafafa;position:relative; }
        .upload-zone:hover { border-color:#2563eb;background:#eff6ff; }
        .upload-zone input[type=file] { position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%; }
        #img-preview,#edit-img-preview { max-height:70px;border-radius:6px;margin-top:8px;display:none; }

        /* Low stock alert row */
        tr.low-stock-row { background:#fffbeb !important; }
        tr.low-stock-row:hover td { background:#fef3c7 !important; }

        /* Inline action buttons */
        .act-btn { padding:4px 10px;border-radius:5px;font-size:12px;font-weight:bold;cursor:pointer;border:none;text-decoration:none;display:inline-block;margin-right:4px; }
        .act-edit   { background:#e0e7ff;color:#3730a3;border:1px solid #c7d2fe; }
        .act-delete { background:#fee2e2;color:#991b1b;border:1px solid #fecaca; }
        .act-green  { background:#dcfce7;color:#166534;border:1px solid #86efac; }
        .act-orange { background:#ffedd5;color:#9a3412;border:1px solid #fed7aa; }
        .act-edit:hover   { background:#c7d2fe; }
        .act-delete:hover { background:#fecaca; }
        .act-green:hover  { background:#bbf7d0; }
        .act-orange:hover { background:#fed7aa; }

        /* Alert */
        .alert-bar { font-size:13px;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-weight:500; }
        .alert-bar.success { background:#f0fdf4;border:1px solid #86efac;color:#15803d; }
        .alert-bar.error   { background:#fff0f0;border:1px solid #fca5a5;color:#b91c1c; }

        /* Modal overlay */
        .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:white;border-radius:12px;padding:24px;max-width:540px;width:90%;max-height:90vh;overflow-y:auto; }
        .modal-title { font-size:16px;font-weight:bold;margin-bottom:16px; }
        .modal-close { float:right;background:none;border:none;font-size:20px;cursor:pointer;color:#888; }

        /* Keys list */
        .key-item { display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid #f0f0f0;font-size:13px; }
        .key-item:last-child { border-bottom:none; }
        .key-sold { color:#aaa;text-decoration:line-through; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-box">Ghos</div>
        <span class="logo-name">Admin Panel</span>
    </div>
    <a href="#" class="sidebar-link" data-section="section-dashboard">📊 Dashboard</a>
    <a href="#" class="sidebar-link" data-section="section-orders">🛒 Orders</a>
    <a href="#" class="sidebar-link" data-section="section-games">🎮 Games</a>
    <a href="#" class="sidebar-link" data-section="section-users">👥 Users</a>
    <a href="#" class="sidebar-link" data-section="section-add-game">➕ Add Game</a>
    <a href="#" class="sidebar-link" data-section="section-business-apps">🏢 Business Apps</a>
    <hr class="sidebar-divider">
    <a href="index.php" class="sidebar-back">← Back to Store</a>
    <a href="?logout=1" class="sidebar-back" style="color:#ef4444;margin-top:8px;">🚪 Logout</a>
</aside>

<main class="main-content">

<?php if ($flash): ?>
<div class="alert-bar <?= $flash_type ?>" id="flashAlert"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<!-- ══════════════ DASHBOARD ══════════════ -->
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

    <!-- Low stock quick list -->
    <?php
    $low_games = $pdo->query("
        SELECT g.id, g.name, g.price, i.filename AS cover_image, COUNT(k.id) AS stock_count
        FROM Games g
        LEFT JOIN Game_Images i ON g.id=i.game_id AND i.is_cover=1
        LEFT JOIN Game_Keys k ON g.id=k.game_id AND k.is_sold=0
        GROUP BY g.id HAVING stock_count < 5 ORDER BY stock_count ASC LIMIT 5
    ")->fetchAll();
    if ($low_games):
    ?>
    <div class="panel" style="margin-top:0;">
        <div class="panel-header"><span class="panel-title">⚠️ Low Stock Alert</span></div>
        <table class="data-table">
            <thead><tr><th>Game</th><th>Stock</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach($low_games as $i=>$g): $img=ltrim($g['cover_image']??'','/'); ?>
            <tr class="low-stock-row">
                <td>
                    <div class="game-cell">
                        <div class="mini-img <?= $bg_colors[$i%count($bg_colors)] ?>"><?php if($img):?><img src="<?=htmlspecialchars($img)?>" alt=""><?php endif;?></div>
                        <span class="mini-name"><?= htmlspecialchars($g['name']) ?></span>
                    </div>
                </td>
                <td><?= stockBadge((int)$g['stock_count']) ?></td>
                <td><a href="?action=view_keys_redirect&id=<?=$g['id']?>" class="act-green act-btn" onclick="event.preventDefault();openAddKeys(<?=$g['id']?>,<?=htmlspecialchars(json_encode($g['name']))?>')">➕ Add Keys</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- ══════════════ ORDERS ══════════════ -->
<div id="section-orders" class="admin-section panel" style="margin-top:0;">
    <div class="panel-header">
        <span class="panel-title">Orders (<span id="ordersCount"><?= count($orders) ?></span>)</span>
        <div style="display:flex;gap:8px;">
            <button class="order-filter-tab active" data-filter="all">All</button>
            <button class="order-filter-tab" data-filter="pending">Pending</button>
            <button class="order-filter-tab" data-filter="delivered">Delivered</button>
            <button class="order-filter-tab" data-filter="cancelled">Cancelled</button>
        </div>
    </div>
    <table class="data-table" data-sortable>
        <thead><tr>
            <th data-col="id">#</th>
            <th>User</th>
            <th>Games</th>
            <th data-col="price">Total</th>
            <th data-col="date">Date</th>
            <th>Status</th>
            <th>Change Status</th>
        </tr></thead>
        <tbody id="ordersTableBody">
        <?php if(empty($orders)): ?>
            <tr><td colspan="7" style="text-align:center;color:#888;padding:30px;">No orders yet.</td></tr>
        <?php else: foreach($orders as $o): $sl=strtolower($o['status']); ?>
            <tr data-status="<?= htmlspecialchars($sl) ?>">
                <td data-col="id" data-val="<?=$o['id']?>"><?=$o['id']?></td>
                <td><?= htmlspecialchars($o['user_email']) ?></td>
                <td style="max-width:200px;font-size:12px;"><?= htmlspecialchars($o['game_names']) ?></td>
                <td data-col="price" data-val="<?=(float)$o['total_price']?>">$<?= number_format((float)$o['total_price'],2) ?></td>
                <td data-col="date" data-val="<?=strtotime($o['order_date'])?>"><?= date('M j, Y', strtotime($o['order_date'])) ?></td>
                <td><?= statusBadge($o['status']) ?></td>
                <td>
                    <form method="POST" action="admin.php" style="display:flex;gap:4px;align-items:center;">
                        <input type="hidden" name="action" value="update_order_status">
                        <input type="hidden" name="order_id" value="<?=$o['id']?>">
                        <select name="status" style="padding:3px 6px;font-size:12px;border:1px solid #e0e0e0;border-radius:5px;">
                            <?php foreach(['pending','delivered','cancelled','completed'] as $s): ?>
                                <option value="<?=$s?>" <?=$o['status']===$s?'selected':''?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="act-btn act-green">Save</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- ══════════════ GAMES ══════════════ -->
<div id="section-games" class="admin-section panel" style="margin-top:0;">
    <div class="panel-header">
        <span class="panel-title">Games (<span id="gamesCount"><?= count($games) ?></span>)</span>
        <input id="gamesSearch" type="text" class="search-input" placeholder="Search games...">
    </div>
    <table class="data-table" data-sortable>
        <thead><tr>
            <th data-col="id">ID</th>
            <th>Game</th>
            <th data-col="price">Price</th>
            <th data-col="stock">Stock</th>
            <th>Status</th>
            <th>Actions</th>
        </tr></thead>
        <tbody id="gamesTableBody">
        <?php if(empty($games)): ?>
            <tr><td colspan="6" style="text-align:center;color:#888;padding:30px;">No games found.</td></tr>
        <?php else:
            $ci=0;
            foreach($games as $game):
                $img   = ltrim($game['cover_image']??'','/');
                $stock = (int)$game['stock_count'];
                $clr   = $bg_colors[$ci++%count($bg_colors)];
                $isLow = $stock < 5;
        ?>
            <tr class="<?= $isLow?'low-stock-row':'' ?>">
                <td data-col="id" data-val="<?=$game['id']?>"><?=$game['id']?></td>
                <td>
                    <div class="game-cell">
                        <div class="mini-img <?=$clr?>"><?php if($img):?><img src="<?=htmlspecialchars($img)?>" alt=""><?php endif;?></div>
                        <span class="mini-name"><?= htmlspecialchars($game['name']) ?></span>
                    </div>
                </td>
                <td data-col="price" data-val="<?=(float)$game['price']?>">$<?= number_format((float)$game['price'],2) ?></td>
                <td data-col="stock" data-val="<?=$stock?>"><?=$stock?></td>
                <td><?= stockBadge($stock) ?></td>
                <td>
                    <button class="act-btn act-edit" onclick="openEditGame(<?= htmlspecialchars(json_encode($game)) ?>)">Edit</button>
                    <button class="act-btn act-green" onclick="openAddKeys(<?=$game['id']?>,<?=htmlspecialchars(json_encode($game['name']))?>')">Keys</button>
                    <a href="admin.php?action=delete_game&id=<?=$game['id']?>" class="act-btn act-delete" data-confirm="Delete \"<?= htmlspecialchars($game['name']) ?>\"? This cannot be undone.">Delete</a>
                </td>
            </tr>
        <?php endforeach; endif; ?>
            <tr id="gamesEmptySearch" style="display:none;"><td colspan="6" style="text-align:center;color:#aaa;padding:24px;">No games match your search.</td></tr>
        </tbody>
    </table>
</div>

<!-- ══════════════ USERS ══════════════ -->
<div id="section-users" class="admin-section panel" style="margin-top:0;">
    <div class="panel-header">
        <span class="panel-title">Users (<span id="usersCount"><?= count($users) ?></span>)</span>
        <input id="usersSearch" type="text" class="search-input" placeholder="Search users...">
    </div>
    <table class="data-table" data-sortable>
        <thead><tr>
            <th data-col="id">ID</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Change Role</th>
            <th>Actions</th>
        </tr></thead>
        <tbody id="usersTableBody">
        <?php foreach($users as $u): ?>
            <tr>
                <td data-col="id" data-val="<?=$u['id']?>"><?=$u['id']?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= roleBadge($u['role']) ?></td>
                <td><?= $u['is_active'] ? '<span class="badge-green">Active</span>' : '<span class="badge-red">Suspended</span>' ?></td>
                <td>
                    <form method="POST" action="admin.php" style="display:flex;gap:4px;align-items:center;">
                        <input type="hidden" name="action" value="change_role">
                        <input type="hidden" name="user_id" value="<?=$u['id']?>">
                        <select name="role" style="padding:3px 6px;font-size:12px;border:1px solid #e0e0e0;border-radius:5px;">
                            <?php foreach(['user','customer','business','admin'] as $r): ?>
                                <option value="<?=$r?>" <?=$u['role']===$r?'selected':''?>><?= ucfirst($r) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="act-btn act-green">Save</button>
                    </form>
                </td>
                <td>
                    <a href="admin.php?action=toggle_user&id=<?=$u['id']?>&section=section-users" class="act-btn <?= $u['is_active']?'act-orange':'act-green' ?>">
                        <?= $u['is_active']?'Suspend':'Enable' ?>
                    </a>
                    <?php if($u['id'] !== (int)$_SESSION['user_id']): ?>
                    <a href="admin.php?action=delete_user&id=<?=$u['id']?>" class="act-btn act-delete" data-confirm="Delete user <?= htmlspecialchars($u['email']) ?>? This cannot be undone.">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- ══════════════ ADD GAME ══════════════ -->
<div id="section-add-game" class="admin-section">
    <h1 class="page-title">Add New Game</h1>
    <div class="panel">
        <form method="POST" action="admin.php" enctype="multipart/form-data" class="form-panel">
            <input type="hidden" name="action" value="add_game">
            <div class="form-row">
                <div class="fg"><label>Game Name *</label><input type="text" name="name" required placeholder="e.g. Elden Ring"></div>
                <div class="fg"><label>Price (USD) *</label><input type="number" name="price" step="0.01" min="0" required placeholder="29.99"></div>
            </div>
            <div class="form-row">
                <div class="fg">
                    <label>Platform *</label>
                    <select name="platform" required>
                        <option value="">Select platform</option>
                        <?php foreach(['PC','PlayStation','Xbox','Nintendo Switch','Multi-Platform','iOS','Android'] as $p): ?>
                            <option value="<?=$p?>"><?=$p?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="fg"><label>Genres</label><input type="text" name="genres" placeholder="e.g. Action, RPG"></div>
            </div>
            <div class="form-row full">
                <div class="fg"><label>Description</label><textarea name="description" placeholder="Game description..."></textarea></div>
            </div>
            <div class="form-row full">
                <div class="fg">
                    <label>Cover Image</label>
                    <div class="upload-zone" id="uploadZone">
                        <input type="file" name="cover_image" id="coverInput" accept="image/*" onchange="previewImg(this,'img-preview','upload-zone-text','upload-zone-icon')">
                        <div class="upload-zone-icon" id="upload-zone-icon">🖼️</div>
                        <div class="upload-zone-text" id="upload-zone-text">Click to upload or drag & drop<br><span style="font-size:11px;">JPG, PNG, WEBP — max 5MB</span></div>
                        <img id="img-preview" src="" alt="">
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="reset" style="padding:9px 20px;border:1px solid #e0e0e0;border-radius:6px;background:white;cursor:pointer;font-size:14px;" onclick="resetPreview('img-preview','upload-zone-text','upload-zone-icon')">Clear</button>
                <button type="submit" class="btn-blue" style="padding:9px 24px;">Add Game</button>
            </div>
        </form>
    </div>
</div>


<!-- ══════════════ BUSINESS APPLICATIONS ══════════════ -->
<div id="section-business-apps" class="admin-section panel" style="margin-top:0;">
    <div class="panel-header">
        <span class="panel-title">Business Applications (<span id="appsCount">0</span>)</span>
        <div style="display:flex;gap:8px;">
            <button class="order-filter-tab active" data-filter-apps="all">All</button>
            <button class="order-filter-tab" data-filter-apps="pending">Pending</button>
            <button class="order-filter-tab" data-filter-apps="approved">Approved</button>
            <button class="order-filter-tab" data-filter-apps="rejected">Rejected</button>
        </div>
    </div>
    <table class="data-table">
        <thead><tr>
            <th>#</th>
            <th>Applicant</th>
            <th>Business Name</th>
            <th>Reason</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr></thead>
        <tbody id="appsTableBody">
        <?php if(empty($biz_apps)): ?>
            <tr><td colspan="7" style="text-align:center;color:#888;padding:30px;">No applications yet.</td></tr>
        <?php else: foreach($biz_apps as $app):
            $sl = $app["status"];
        ?>
            <tr data-status-app="<?= htmlspecialchars($sl) ?>">
                <td><?= $app["id"] ?></td>
                <td><?= htmlspecialchars($app["user_email"]) ?></td>
                <td><strong><?= htmlspecialchars($app["business_name"]) ?></strong></td>
                <td style="max-width:200px;font-size:12px;color:#666;"><?= htmlspecialchars($app["reason"]) ?></td>
                <td style="font-size:12px;"><?= date("M j, Y", strtotime($app["created_at"])) ?></td>
                <td>
                    <?php if($sl === "pending"): ?>
                        <span class="badge-blue">Pending</span>
                    <?php elseif($sl === "approved"): ?>
                        <span class="badge-green">Approved</span>
                    <?php else: ?>
                        <span class="badge-red">Rejected</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($sl === "pending"): ?>
                    <form method="POST" action="admin.php" style="display:inline;">
                        <input type="hidden" name="action" value="review_app">
                        <input type="hidden" name="app_id" value="<?= $app["id"] ?>">
                        <input type="hidden" name="user_id" value="<?= $app["user_id"] ?>">
                        <button type="submit" name="decision" value="approved" class="act-btn act-green">Approve</button>
                        <button type="submit" name="decision" value="rejected" class="act-btn act-delete">Reject</button>
                    </form>
                    <?php else: ?>
                        <span style="font-size:12px;color:#aaa;">Reviewed</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

</main>

<!-- ══════════════ EDIT GAME MODAL ══════════════ -->
<div class="modal-overlay" id="editGameModal">
    <div class="modal-box">
        <div class="modal-title">Edit Game <button class="modal-close" onclick="closeModal('editGameModal')">×</button></div>
        <form method="POST" action="admin.php" enctype="multipart/form-data" class="form-panel" style="padding:0;">
            <input type="hidden" name="action" value="edit_game">
            <input type="hidden" name="game_id" id="edit_game_id">
            <div class="form-row">
                <div class="fg"><label>Name *</label><input type="text" name="name" id="edit_name" required></div>
                <div class="fg"><label>Price *</label><input type="number" name="price" id="edit_price" step="0.01" min="0" required></div>
            </div>
            <div class="form-row">
                <div class="fg"><label>Platform</label><input type="text" name="platform" id="edit_platform"></div>
                <div class="fg"><label>Genres</label><input type="text" name="genres" id="edit_genres"></div>
            </div>
            <div class="form-row full" style="margin-bottom:14px;">
                <div class="fg"><label>Description</label><textarea name="description" id="edit_description"></textarea></div>
            </div>
            <div class="form-row full" style="margin-bottom:14px;">
                <div class="fg">
                    <label>New Cover Image (leave empty to keep current)</label>
                    <div class="upload-zone">
                        <input type="file" name="cover_image" accept="image/*" onchange="previewImg(this,'edit-img-preview',null,null)">
                        <div style="font-size:12px;color:#aaa;">Click or drag to replace cover</div>
                        <img id="edit-img-preview" src="" alt="">
                    </div>
                </div>
            </div>
            <div class="form-actions" style="padding:0 0 4px;">
                <button type="button" onclick="closeModal('editGameModal')" style="padding:9px 20px;border:1px solid #e0e0e0;border-radius:6px;background:white;cursor:pointer;">Cancel</button>
                <button type="submit" class="btn-blue" style="padding:9px 24px;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════ ADD KEYS MODAL ══════════════ -->
<div class="modal-overlay" id="addKeysModal">
    <div class="modal-box">
        <div class="modal-title">Manage Keys — <span id="keysGameName"></span> <button class="modal-close" onclick="closeModal('addKeysModal')">×</button></div>
        <form method="POST" action="admin.php">
            <input type="hidden" name="action" value="add_key">
            <input type="hidden" name="game_id" id="keys_game_id">
            <div class="fg" style="margin-bottom:12px;">
                <label>Add Keys (one per line)</label>
                <textarea name="keys" placeholder="XXXXX-XXXXX-XXXXX&#10;YYYYY-YYYYY-YYYYY" style="min-height:100px;width:100%;padding:8px;border:1px solid #e0e0e0;border-radius:6px;font-family:monospace;font-size:13px;"></textarea>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" onclick="closeModal('addKeysModal')" style="padding:8px 16px;border:1px solid #e0e0e0;border-radius:6px;background:white;cursor:pointer;">Cancel</button>
                <button type="submit" class="btn-blue" style="padding:8px 20px;">Add Keys</button>
            </div>
        </form>
    </div>
</div>

<script src="js/admin.js"></script>
<script>
// ── Active section from URL ──────────────────────
const urlSection = new URLSearchParams(location.search).get('section');
if (urlSection) {
    document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active-section'));
    document.getElementById(urlSection)?.classList.add('active-section');
    document.querySelectorAll('.sidebar-link[data-section]').forEach(l =>
        l.classList.toggle('active', l.dataset.section === urlSection));
}

// ── Business apps count + filter ─────────────
(function() {
    const rows = document.querySelectorAll('#appsTableBody tr[data-status-app]');
    document.getElementById('appsCount').textContent = rows.length;

    document.querySelectorAll('[data-filter-apps]').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('[data-filter-apps]').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const f = tab.dataset.filterApps;
            let vis = 0;
            rows.forEach(r => {
                const show = f === 'all' || r.dataset.statusApp === f;
                r.style.display = show ? '' : 'none';
                if (show) vis++;
            });
            document.getElementById('appsCount').textContent = vis;
        });
    });
})();

// Auto-dismiss flash
setTimeout(() => document.getElementById('flashAlert')?.remove(), 4000);

// ── Modals ────────────────────────────────────────
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function openEditGame(game) {
    document.getElementById('edit_game_id').value   = game.id;
    document.getElementById('edit_name').value      = game.name;
    document.getElementById('edit_price').value     = game.price;
    document.getElementById('edit_platform').value  = game.platform ?? '';
    document.getElementById('edit_genres').value    = game.genres ?? '';
    document.getElementById('edit_description').value = game.description ?? '';
    document.getElementById('edit-img-preview').style.display = 'none';
    document.getElementById('editGameModal').classList.add('open');
}

function openAddKeys(gameId, gameName) {
    document.getElementById('keys_game_id').value = gameId;
    document.getElementById('keysGameName').textContent = gameName;
    document.getElementById('addKeysModal').classList.add('open');
}

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(el =>
    el.addEventListener('click', e => { if(e.target === el) el.classList.remove('open'); }));

// ── Image preview ────────────────────────────────
function previewImg(input, previewId, textId, iconId) {
    if (input.files && input.files[0]) {
        const r = new FileReader();
        r.onload = e => {
            const img = document.getElementById(previewId);
            img.src = e.target.result; img.style.display = 'block';
            if (textId) document.getElementById(textId).style.display = 'none';
            if (iconId) document.getElementById(iconId).style.display = 'none';
        };
        r.readAsDataURL(input.files[0]);
    }
}
function resetPreview(previewId, textId, iconId) {
    const img = document.getElementById(previewId);
    img.src = ''; img.style.display = 'none';
    if (textId) document.getElementById(textId).style.display = '';
    if (iconId) document.getElementById(iconId).style.display = '';
}

// ── Users search ─────────────────────────────────
document.getElementById('usersSearch')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    const rows = document.querySelectorAll('#usersTableBody tr');
    let vis = 0;
    rows.forEach(r => { const show = r.textContent.toLowerCase().includes(q); r.style.display=show?'':'none'; if(show) vis++; });
    document.getElementById('usersCount').textContent = vis;
});

<?php if ($flash): ?>
window.addEventListener('load', () => showToast(<?= json_encode($flash) ?>, <?= json_encode($flash_type) ?>));
<?php endif; ?>
</script>
</body>
</html>
