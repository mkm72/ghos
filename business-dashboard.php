<?php
session_start();
require_once 'php/db_connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'business') {
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Access Denied</title><link rel="stylesheet" href="css/navbar.css"></head><body>';
    echo '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:80vh;text-align:center;gap:16px;">';
    echo '<div style="font-size:28px;font-weight:bold;">Access Denied</div>';
    echo '<div style="font-size:15px;color:#888;">Business seller access only.</div>';
    echo '<a href="index.php" class="btn-blue">Back to Store</a></div></body></html>';
    exit;
}

$user_id    = (int)$_SESSION['user_id'];
$user_email = $_SESSION['user_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_game') {
        $name = trim($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $platform = trim($_POST['platform'] ?? '');
        $genres = trim($_POST['genres'] ?? '');
        $desc = trim($_POST['description'] ?? '');

        $ins = $pdo->prepare('INSERT INTO Games (name, description, price, platform, genres, seller_id) VALUES (?,?,?,?,?,?)');
        $ins->execute([$name, $desc, $price, $platform, $genres, $user_id]);
        $game_id = $pdo->lastInsertId();

        if (!empty($_FILES['cover_image']['name'])) {
            $file = $_FILES['cover_image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $dir = 'images/games/';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $fname = 'game_'.$game_id.'_'.time().'.'.$ext;
                if (move_uploaded_file($file['tmp_name'], $dir.$fname)) {
                    $pdo->prepare('INSERT INTO Game_Images (game_id, filename, is_cover) VALUES (?,?,1)')->execute([$game_id, $dir.$fname]);
                }
            }
        }
        header('Location: business-dashboard.php');
        exit;
    }

    if ($action === 'add_existing_game') {
        $base_game_id = (int)($_POST['base_game_id'] ?? 0);
        $my_price = (float)($_POST['price'] ?? 0);
        
        $stmt = $pdo->prepare("SELECT * FROM Games WHERE id = ?");
        $stmt->execute([$base_game_id]);
        $base = $stmt->fetch();
        
        if ($base) {
            $ins = $pdo->prepare('INSERT INTO Games (name, description, price, platform, genres, seller_id) VALUES (?,?,?,?,?,?)');
            $ins->execute([$base['name'], $base['description'], $my_price, $base['platform'], $base['genres'], $user_id]);
            $new_game_id = $pdo->lastInsertId();
            
            $stmt_img = $pdo->prepare("SELECT filename FROM Game_Images WHERE game_id = ? AND is_cover = 1 LIMIT 1");
            $stmt_img->execute([$base_game_id]);
            $img = $stmt_img->fetch();
            if ($img) {
                $pdo->prepare('INSERT INTO Game_Images (game_id, filename, is_cover) VALUES (?,?,1)')->execute([$new_game_id, $img['filename']]);
            }
        }
        header('Location: business-dashboard.php');
        exit;
    }

    if ($action === 'add_key') {
        $game_id = (int)($_POST['game_id'] ?? 0);
        $chk = $pdo->prepare('SELECT id FROM Games WHERE id=? AND seller_id=?');
        $chk->execute([$game_id, $user_id]);
        if ($chk->fetch()) {
            $keys_raw = trim($_POST['keys'] ?? '');
            $keys = array_filter(array_map('trim', explode("\n", $keys_raw)));
            $stmt = $pdo->prepare('INSERT IGNORE INTO Game_Keys (game_id, key_code) VALUES (?,?)');
            foreach ($keys as $k) {
                if ($k) $stmt->execute([$game_id, $k]);
            }
        }
        header('Location: business-dashboard.php');
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete_game') {
    $game_id = (int)$_GET['id'];
    $pdo->prepare('DELETE FROM Games WHERE id=? AND seller_id=?')->execute([$game_id, $user_id]);
    header('Location: business-dashboard.php');
    exit;
}

try {
    $stmt_listings = $pdo->prepare("
        SELECT g.id, g.name, g.price, g.platform, g.genres,
            (SELECT COUNT(*) FROM Game_Keys k WHERE k.game_id = g.id AND k.is_sold = 0) AS stock,
            (SELECT filename FROM Game_Images i WHERE i.game_id = g.id AND i.is_cover = 1 LIMIT 1) AS cover_image
        FROM Games g
        WHERE g.seller_id = :uid
        ORDER BY g.id DESC
    ");
    $stmt_listings->execute(['uid' => $user_id]);
    $listings = $stmt_listings->fetchAll();
} catch (\PDOException $e) { $listings = []; }

try {
    $stmt_sales = $pdo->prepare("
        SELECT o.id AS sale_id, g.name AS game_name, oi.unit_price AS amount, o.order_date
        FROM Order_Items oi
        JOIN Orders o ON oi.order_id = o.id
        JOIN Games  g ON oi.game_id  = g.id
        WHERE g.seller_id = :uid
        ORDER BY o.order_date DESC
        LIMIT 20
    ");
    $stmt_sales->execute(['uid' => $user_id]);
    $recent_sales = $stmt_sales->fetchAll();
} catch (\PDOException $e) { $recent_sales = []; }

// Fetch unique games by name, picking the lowest ID for each title
try {
    $stmt_global = $pdo->query("
        SELECT MIN(id) as id, name 
        FROM Games 
        GROUP BY name 
        ORDER BY name ASC
    ");
    $global_games = $stmt_global->fetchAll();
} catch (\PDOException $e) { 
    $global_games = []; 
}
$total_revenue   = array_sum(array_column($recent_sales, 'amount'));
$active_listings = count(array_filter($listings, fn($l) => $l['stock'] > 0));
$total_sales     = count($recent_sales);
$total_games     = count($listings);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Dashboard</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/dashboard-layout.css">
    <style>
        .badge-green { background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }
        .badge-red { background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }
        .badge-orange { background:#ffedd5;color:#ea580c;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }
        .user-chip { display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(255,255,255,0.06);border-radius:8px;margin:12px 0;font-size:12px;color:#aaa;word-break:break-all; }
        .user-chip .avatar { width:28px;height:28px;border-radius:50%;background:#3b82f6;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:bold;color:white;flex-shrink:0; }
        .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:white;border-radius:12px;padding:24px;max-width:540px;width:90%;max-height:90vh;overflow-y:auto; }
        .modal-title { font-size:16px;font-weight:bold;margin-bottom:16px; }
        .modal-close { float:right;background:none;border:none;font-size:20px;cursor:pointer;color:#888; }
        .form-row { display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px; }
        .form-row.full { grid-template-columns:1fr; }
        .fg label { display:block;font-size:12px;font-weight:bold;color:#666;text-transform:uppercase;margin-bottom:5px; }
        .fg input, .fg select, .fg textarea { width:100%;padding:8px 12px;border:1px solid #e0e0e0;border-radius:6px;font-size:14px;box-sizing:border-box; }
        .header-actions { display:flex; gap:10px; }
        .btn-white { background: white; border: 1px solid #ccc; color: #333; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; }
        .btn-white:hover { background: #f9f9f9; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-box">Ghos</div>
        <span class="logo-name">Seller Panel</span>
    </div>
    <div class="user-chip">
        <div class="avatar"><?= strtoupper(substr($user_email, 0, 1)) ?></div>
        <span><?= htmlspecialchars($user_email) ?></span>
    </div>
    <a href="business-dashboard.php" class="sidebar-link active">Dashboard</a>
    <hr class="sidebar-divider">
    <a href="index.php" class="sidebar-back">Back to Store</a>
    <a href="auth.php?logout=1" class="sidebar-back" style="color:#ef4444;margin-top:8px;">Logout</a>
</aside>

<main class="main-content">
    <h1 class="page-title">Business Dashboard</h1>
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);">
        <div class="stat-card">
            <div><div class="stat-label">Total Revenue</div><div class="stat-value green">$<?= number_format($total_revenue, 2) ?></div></div>
        </div>
        <div class="stat-card">
            <div><div class="stat-label">Total Games</div><div class="stat-value blue"><?= $total_games ?></div></div>
        </div>
        <div class="stat-card">
            <div><div class="stat-label">Active Listings</div><div class="stat-value green"><?= $active_listings ?></div></div>
        </div>
        <div class="stat-card">
            <div><div class="stat-label">Total Sales</div><div class="stat-value purple"><?= $total_sales ?></div></div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Game Listings (<?= $total_games ?>)</span>
            <div class="header-actions">
                <button class="btn-white" onclick="document.getElementById('addExistingGameModal').classList.add('open')">Sell Existing Game</button>
                <button class="btn-blue" onclick="document.getElementById('addGameModal').classList.add('open')">Add New Game</button>
            </div>
        </div>
        <table class="data-table">
            <thead>
                <tr><th>Game</th><th>Price</th><th>Stock</th><th>Platform</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($listings)): ?>
                    <tr><td colspan="6" style="text-align:center;color:#888;padding:30px;">No game listings yet.</td></tr>
                <?php else: foreach ($listings as $game):
                    $img = ltrim($game['cover_image'] ?? '', '/');
                    $stock = (int)$game['stock'];
                    $isLow = $stock < 5;
                ?>
                    <tr style="<?= $isLow ? 'background:#fffbeb;' : '' ?>">
                        <td>
                            <div class="game-cell">
                                <div class="mini-img bg-dark">
                                    <?php if ($img): ?><img src="<?= htmlspecialchars($img) ?>" alt=""><?php endif; ?>
                                </div>
                                <span class="mini-name"><?= htmlspecialchars($game['name']) ?></span>
                            </div>
                        </td>
                        <td>$<?= number_format((float)$game['price'], 2) ?></td>
                        <td><?= $stock === 0 ? '<span style="color:#dc2626;font-weight:bold;">0</span>' : ($isLow ? '<span class="stock-low">'.$stock.'</span>' : $stock) ?></td>
                        <td><?= htmlspecialchars($game['platform']) ?></td>
                        <td>
                            <?php if ($stock > 0 && !$isLow): ?><span class="badge-green">Active</span>
                            <?php elseif ($stock > 0 && $isLow): ?><span class="badge-orange">Low Stock</span>
                            <?php else: ?><span class="badge-red">Out of Stock</span><?php endif; ?>
                        </td>
                        <td>
                            <button class="btn-sm-edit" onclick="openAddKeys(<?= $game['id'] ?>)">Add Keys</button>
                            <a href="business-dashboard.php?action=delete_game&id=<?= $game['id'] ?>" class="btn-sm-delete" onclick="return confirm('Delete this game?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <div class="panel">
        <div class="panel-header"><span class="panel-title">Recent Sales</span></div>
        <table class="data-table">
            <thead><tr><th>Sale ID</th><th>Game</th><th>Amount</th><th>Date</th></tr></thead>
            <tbody>
                <?php if (empty($recent_sales)): ?>
                    <tr><td colspan="4" style="text-align:center;color:#888;padding:30px;">No sales yet.</td></tr>
                <?php else: foreach ($recent_sales as $sale): ?>
                    <tr>
                        <td>#<?= (int)$sale['sale_id'] ?></td>
                        <td><?= htmlspecialchars($sale['game_name']) ?></td>
                        <td>$<?= number_format((float)$sale['amount'], 2) ?></td>
                        <td><?= date('M j, Y', strtotime($sale['order_date'])) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</main>

<div class="modal-overlay" id="addExistingGameModal">
    <div class="modal-box">
        <div class="modal-title">Sell an Existing Game <button class="modal-close" onclick="document.getElementById('addExistingGameModal').classList.remove('open')">×</button></div>
        <form method="POST" action="business-dashboard.php">
            <input type="hidden" name="action" value="add_existing_game">
            <div class="form-row full">
                <div class="fg">
                    <label>Select Game *</label>
                    <select name="base_game_id" required>
                        <option value="">-- Choose a game from the catalog --</option>
                        <?php foreach ($global_games as $gg): ?>
                            <option value="<?= $gg['id'] ?>"><?= htmlspecialchars($gg['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row full">
                <div class="fg"><label>Your Price *</label><input type="number" name="price" step="0.01" required></div>
            </div>
            <div style="text-align:right;"><button type="submit" class="btn-blue">Add to My Store</button></div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="addGameModal">
    <div class="modal-box">
        <div class="modal-title">Add New Custom Game <button class="modal-close" onclick="document.getElementById('addGameModal').classList.remove('open')">×</button></div>
        <form method="POST" action="business-dashboard.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_game">
            <div class="form-row">
                <div class="fg"><label>Name *</label><input type="text" name="name" required></div>
                <div class="fg"><label>Price *</label><input type="number" name="price" step="0.01" required></div>
            </div>
            <div class="form-row">
                <div class="fg"><label>Platform</label><input type="text" name="platform"></div>
                <div class="fg"><label>Genres</label><input type="text" name="genres"></div>
            </div>
            <div class="form-row full">
                <div class="fg"><label>Description</label><textarea name="description" rows="3"></textarea></div>
            </div>
            <div class="form-row full">
                <div class="fg"><label>Cover Image</label><input type="file" name="cover_image" accept="image/*"></div>
            </div>
            <div style="text-align:right;"><button type="submit" class="btn-blue">Create Game</button></div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="addKeysModal">
    <div class="modal-box">
        <div class="modal-title">Add Keys <button class="modal-close" onclick="document.getElementById('addKeysModal').classList.remove('open')">×</button></div>
        <form method="POST" action="business-dashboard.php">
            <input type="hidden" name="action" value="add_key">
            <input type="hidden" name="game_id" id="keys_game_id">
            <div class="fg" style="margin-bottom:12px;">
                <label>Keys (one per line)</label>
                <textarea name="keys" rows="5" required></textarea>
            </div>
            <div style="text-align:right;"><button type="submit" class="btn-blue">Save Keys</button></div>
        </form>
    </div>
</div>

<script>
    function openAddKeys(id) {
        document.getElementById('keys_game_id').value = id;
        document.getElementById('addKeysModal').classList.add('open');
    }
</script>
</body>
</html>
