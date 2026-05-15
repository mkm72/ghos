<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'php/db_connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'business') {
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Access Denied</title><link rel="stylesheet" href="css/navbar.css"></head><body>';
    echo '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:80vh;text-align:center;gap:16px;">';
    echo '<img src="images/logo/logo1.png" alt="Ghos Logo" style="height: 80px; margin-bottom: 10px;">';
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

    if ($action === 'delete_key') {
        $key_id = (int)($_POST['key_id'] ?? 0);
        $game_id = (int)($_POST['game_id'] ?? 0);
        
        // Verify ownership
        $chk = $pdo->prepare('SELECT g.id FROM Games g JOIN Game_Keys k ON g.id = k.game_id WHERE k.id = ? AND g.seller_id = ? AND k.is_sold = 0');
        $chk->execute([$key_id, $user_id]);
        if ($chk->fetch()) {
            $pdo->prepare('DELETE FROM Game_Keys WHERE id = ?')->execute([$key_id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Unauthorized or key already sold']);
        }
        exit;
    }

    if ($action === 'bulk_price_update') {
        $percent = (float)($_POST['percent'] ?? 0);
        if ($percent != 0) {
            $multiplier = 1 + ($percent / 100);
            $stmt = $pdo->prepare("UPDATE Games SET price = price * ? WHERE seller_id = ?");
            $stmt->execute([$multiplier, $user_id]);
        }
        header('Location: business-dashboard.php');
        exit;
    }
}

// --- CSV EXPORT ---
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    $stmt_sales_all = $pdo->prepare("
        SELECT o.id AS sale_id, g.name AS game_name, oi.unit_price AS amount, o.order_date, 
               COALESCE(u.email, o.guest_email) AS customer_email, k.key_code
        FROM Order_Items oi
        JOIN Orders o ON oi.order_id = o.id
        JOIN Games g ON oi.game_id = g.id
        LEFT JOIN Users u ON o.user_id = u.id
        JOIN Game_Keys k ON oi.key_id = k.id
        WHERE g.seller_id = :uid
        ORDER BY o.order_date DESC
    ");
    $stmt_sales_all->execute(['uid' => $user_id]);
    $all_sales = $stmt_sales_all->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sales_report_'.date('Y-m-d').'.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Sale ID', 'Game Name', 'Amount ($)', 'Date', 'Customer Email', 'Key Code']);
    foreach ($all_sales as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// --- AJAX: FETCH KEYS ---
if (isset($_GET['action']) && $_GET['action'] === 'get_keys') {
    $game_id = (int)($_GET['game_id'] ?? 0);
    $stmt = $pdo->prepare("
        SELECT k.id, k.key_code, k.is_sold 
        FROM Game_Keys k
        JOIN Games g ON k.game_id = g.id
        WHERE g.id = ? AND g.seller_id = ?
        ORDER BY k.is_sold ASC, k.id DESC
    ");
    $stmt->execute([$game_id, $user_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete_game') {
    $game_id = (int)$_GET['id'];
    
    try {
        // 1. Check if the game has any sales history
        $chk_sales = $pdo->prepare('SELECT id FROM Order_Items WHERE game_id=? LIMIT 1');
        $chk_sales->execute([$game_id]);
        if ($chk_sales->fetch()) {
            // Has orders, don't delete to preserve order history
            echo "<script>alert('Cannot delete this game because it has already been sold to customers. Please delete its keys to mark it Out of Stock instead.'); window.location.href='business-dashboard.php';</script>";
            exit;
        }
        
        // 2. Safely delete child records first to prevent Database Constraint 500 errors
        $pdo->prepare('DELETE FROM Game_Images WHERE game_id=?')->execute([$game_id]);
        $pdo->prepare('DELETE FROM Game_Keys WHERE game_id=?')->execute([$game_id]);
        $pdo->prepare('DELETE FROM Cart WHERE game_id=?')->execute([$game_id]);
        
        // 3. Finally, delete the game
        $pdo->prepare('DELETE FROM Games WHERE id=? AND seller_id=?')->execute([$game_id, $user_id]);
        
        header('Location: business-dashboard.php');
        exit;
        
    } catch (\PDOException $e) {
        echo "<script>alert('Database error during deletion. Please contact support.'); window.location.href='business-dashboard.php';</script>";
        exit;
    }
}
// Handle Search and Filtering for Listings
$search = trim($_GET['search'] ?? '');
$filter = $_GET['filter'] ?? 'all'; // all, low_stock, out_of_stock

$where = "WHERE g.seller_id = :uid";
$params = ['uid' => $user_id];

if ($search) {
    $where .= " AND g.name LIKE :search";
    $params['search'] = "%$search%";
}

try {
    $stmt_listings = $pdo->prepare("
        SELECT g.id, g.name, g.price, g.platform, g.genres,
            (SELECT COUNT(*) FROM Game_Keys k WHERE k.game_id = g.id AND k.is_sold = 0) AS stock,
            (SELECT filename FROM Game_Images i WHERE i.game_id = g.id AND i.is_cover = 1 LIMIT 1) AS cover_image
        FROM Games g
        $where
        ORDER BY g.id DESC
    ");
    $stmt_listings->execute($params);
    $all_listings = $stmt_listings->fetchAll();
    
    // Apply client-side filter for simplicity since we already have the stock count
    if ($filter === 'low_stock') {
        $listings = array_filter($all_listings, fn($l) => $l['stock'] > 0 && $l['stock'] < 5);
    } elseif ($filter === 'out_of_stock') {
        $listings = array_filter($all_listings, fn($l) => $l['stock'] == 0);
    } else {
        $listings = $all_listings;
    }
    
} catch (\PDOException $e) { $listings = []; }

try {
    $stmt_sales = $pdo->prepare("
        SELECT o.id AS sale_id, g.name AS game_name, oi.unit_price AS amount, o.order_date,
               COALESCE(u.email, o.guest_email) AS customer_email, k.key_code
        FROM Order_Items oi
        JOIN Orders o ON oi.order_id = o.id
        JOIN Games  g ON oi.game_id  = g.id
        LEFT JOIN Users u ON o.user_id = u.id
        JOIN Game_Keys k ON oi.key_id = k.id
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
        SELECT g.id, g.name, i.filename as cover_image
        FROM Games g
        LEFT JOIN Game_Images i ON g.id = i.game_id AND i.is_cover = 1
        WHERE g.id IN (SELECT MIN(id) FROM Games GROUP BY name)
        ORDER BY g.name ASC
    ");
    $global_games = $stmt_global->fetchAll();
} catch (\PDOException $e) { 
    $global_games = []; 
}
// --- GLOBAL STATS (Unfiltered) ---
$stmt_stats = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT g.id) as total_games,
        SUM(oi.unit_price) as total_revenue,
        COUNT(oi.id) as total_sales,
        (SELECT COUNT(*) FROM Games g2 WHERE g2.seller_id = :uid1 AND (SELECT COUNT(*) FROM Game_Keys k WHERE k.game_id = g2.id AND k.is_sold = 0) > 0) as active_listings
    FROM Games g
    LEFT JOIN Order_Items oi ON g.id = oi.game_id
    WHERE g.seller_id = :uid2
");
$stmt_stats->execute(['uid1' => $user_id, 'uid2' => $user_id]);
$stats = $stmt_stats->fetch();

$total_revenue   = (float)($stats['total_revenue'] ?? 0);
$active_listings = (int)($stats['active_listings'] ?? 0);
$total_sales     = (int)($stats['total_sales'] ?? 0);
$total_games     = (int)($stats['total_games'] ?? 0);
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

        /* Custom Game Selector Styles */
        .game-search-input { margin-bottom: 10px; width: 100%; padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 14px; }
        .game-selector-list { max-height: 280px; overflow-y: auto; border: 1px solid #e0e0e0; border-radius: 8px; background: #fff; }
        .game-selector-item { display: flex; align-items: center; gap: 12px; padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f1f5f9; transition: all 0.2s; }
        .game-selector-item:last-child { border-bottom: none; }
        .game-selector-item:hover { background: #f8fafc; }
        .game-selector-item.selected { background: #eff6ff; border-left: 4px solid #3b82f6; }
        .game-selector-item img { width: 36px; height: 48px; object-fit: cover; border-radius: 4px; background: #eee; flex-shrink: 0; }
        .game-selector-item .gn { font-size: 14px; font-weight: 500; color: #334155; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="images/logo/logo1.png" alt="Ghos Logo" style="height: 40px; border-radius: 8px;">
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

    <!-- SEARCH & FILTER BAR -->
    <div class="panel" style="margin-bottom: 20px; padding: 15px;">
        <form method="GET" action="business-dashboard.php" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <div class="fg" style="flex: 1; min-width: 200px;">
                <input type="text" name="search" placeholder="Search your games..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="fg">
                <select name="filter" onchange="this.form.submit()">
                    <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Listings</option>
                    <option value="low_stock" <?= $filter === 'low_stock' ? 'selected' : '' ?>>Low Stock (< 5)</option>
                    <option value="out_of_stock" <?= $filter === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                </select>
            </div>
            <button type="submit" class="btn-blue" style="padding: 8px 16px;">Apply</button>
            <?php if ($search || $filter !== 'all'): ?>
                <a href="business-dashboard.php" style="font-size: 13px; color: #666;">Clear All</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Game Listings (<?= count($listings) ?>)</span>
            <div class="header-actions">
                <button class="btn-white" onclick="document.getElementById('bulkPriceModal').classList.add('open')">Bulk Price Update</button>
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
                    <tr><td colspan="6" style="text-align:center;color:#888;padding:30px;">No game listings found matching your criteria.</td></tr>
                <?php else: foreach ($listings as $game):
                    $img = ltrim($game['cover_image'] ?? '', '/');
                    $stock = (int)$game['stock'];
                    $isLow = $stock > 0 && $stock < 5;
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
                            <div style="display:flex; gap:5px;">
                                <button class="btn-sm-edit" onclick="openAddKeys(<?= $game['id'] ?>)">+ Keys</button>
                                <button class="btn-sm-edit" style="background:#6366f1;" onclick="viewKeys(<?= $game['id'] ?>, <?= htmlspecialchars(json_encode($game['name'])) ?>)">View Keys</button>
                                <a href="business-dashboard.php?action=delete_game&id=<?= $game['id'] ?>" class="btn-sm-delete" onclick="return confirm('Delete this game?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Recent Sales</span>
            <a href="business-dashboard.php?action=export_csv" class="btn-white" style="text-decoration:none;">Download CSV Report</a>
        </div>
        <table class="data-table">
            <thead><tr><th>Sale ID</th><th>Game</th><th>Amount</th><th>Date</th><th>Action</th></tr></thead>
            <tbody>
                <?php if (empty($recent_sales)): ?>
                    <tr><td colspan="5" style="text-align:center;color:#888;padding:30px;">No sales yet.</td></tr>
                <?php else: foreach ($recent_sales as $sale): ?>
                    <tr>
                        <td>#<?= (int)$sale['sale_id'] ?></td>
                        <td><?= htmlspecialchars($sale['game_name']) ?></td>
                        <td>$<?= number_format((float)$sale['amount'], 2) ?></td>
                        <td><?= date('M j, Y', strtotime($sale['order_date'])) ?></td>
                        <td>
                            <button class="btn-sm-edit" onclick="viewOrderDetails(<?= htmlspecialchars(json_encode($sale)) ?>)">View Details</button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</main>

<div class="modal-overlay" id="addExistingGameModal">
    <div class="modal-box" style="max-width: 600px;">
        <div class="modal-title">Sell an Existing Game <button class="modal-close" onclick="document.getElementById('addExistingGameModal').classList.remove('open')">×</button></div>
        <form method="POST" action="business-dashboard.php">
            <input type="hidden" name="action" value="add_existing_game">
            <input type="hidden" name="base_game_id" id="selectedBaseGameId" required>
            
            <div class="fg" style="margin-bottom: 12px;">
                <label>1. Select Game from Catalog *</label>
                <input type="text" class="game-search-input" placeholder="Search by name..." onkeyup="filterGameSelector(this.value)">
                <div class="game-selector-list" id="gameSelectorList">
                    <?php foreach ($global_games as $gg): 
                        $imgPath = htmlspecialchars(ltrim($gg['cover_image'] ?? '', '/'));
                    ?>
                        <div class="game-selector-item" onclick="selectGameFromList(this, <?= $gg['id'] ?>)" data-name="<?= htmlspecialchars(strtolower($gg['name'])) ?>">
                            <img src="<?= $imgPath ?: 'images/placeholder.jpg' ?>" alt="">
                            <span class="gn"><?= htmlspecialchars($gg['name']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="fg" style="margin-bottom: 20px;">
                <label>2. Set Your Price ($) *</label>
                <input type="number" name="price" step="0.01" required placeholder="29.99">
            </div>

            <div style="text-align:right;">
                <button type="submit" class="btn-blue" id="submitExistingBtn" disabled>Add to My Store</button>
            </div>
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

<div class="modal-overlay" id="viewKeysModal">
    <div class="modal-box" style="max-width: 600px;">
        <div class="modal-title">Inventory for: <span id="viewKeysGameName"></span> <button class="modal-close" onclick="document.getElementById('viewKeysModal').classList.remove('open')">×</button></div>
        <div style="max-height: 400px; overflow-y: auto;">
            <table class="data-table" style="font-size: 13px;">
                <thead><tr><th>Key Code</th><th>Status</th><th>Action</th></tr></thead>
                <tbody id="keysTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="bulkPriceModal">
    <div class="modal-box">
        <div class="modal-title">Bulk Price Update <button class="modal-close" onclick="document.getElementById('bulkPriceModal').classList.remove('open')">×</button></div>
        <form method="POST" action="business-dashboard.php">
            <input type="hidden" name="action" value="bulk_price_update">
            <p style="font-size: 14px; color: #666; margin-bottom: 15px;">Update all your game prices by a percentage. (e.g. 5 for +5% increase, -10 for 10% discount).</p>
            <div class="fg">
                <label>Percentage Change (%)</label>
                <input type="number" name="percent" step="0.1" required placeholder="5.0">
            </div>
            <div style="text-align:right; margin-top: 15px;"><button type="submit" class="btn-blue" onclick="return confirm('Apply this price change to ALL your listings?')">Apply Change</button></div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="orderDetailsModal">
    <div class="modal-box">
        <div class="modal-title">Order Details <button class="modal-close" onclick="document.getElementById('orderDetailsModal').classList.remove('open')">×</button></div>
        <div id="orderDetailsContent" style="font-size: 14px; line-height: 1.6;"></div>
    </div>
</div>

<script>
    function openAddKeys(id) {
        document.getElementById('keys_game_id').value = id;
        document.getElementById('addKeysModal').classList.add('open');
    }

    async function viewKeys(gameId, gameName) {
        document.getElementById('viewKeysGameName').textContent = gameName;
        const tbody = document.getElementById('keysTableBody');
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;">Loading...</td></tr>';
        document.getElementById('viewKeysModal').classList.add('open');

        const response = await fetch(`business-dashboard.php?action=get_keys&game_id=${gameId}`);
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

    async function deleteKey(keyId, gameId, btn) {
        if (!confirm('Delete this unsold key?')) return;
        
        const formData = new FormData();
        formData.append('action', 'delete_key');
        formData.append('key_id', keyId);
        formData.append('game_id', gameId);

        const response = await fetch('business-dashboard.php', { method: 'POST', body: formData });
        const res = await response.json();
        if (res.success) {
            btn.closest('tr').remove();
        } else {
            alert('Error: ' + res.error);
        }
    }

    function viewOrderDetails(sale) {
        const content = document.getElementById('orderDetailsContent');
        content.innerHTML = `
            <div style="background:#f8fafc; padding: 15px; border-radius: 8px;">
                <p><strong>Sale ID:</strong> #${sale.sale_id}</p>
                <p><strong>Game:</strong> ${sale.game_name}</p>
                <p><strong>Customer:</strong> ${sale.customer_email}</p>
                <p><strong>Key Sent:</strong> <code style="background:#e2e8f0; padding: 2px 6px; border-radius: 4px;">${sale.key_code}</code></p>
                <p><strong>Amount:</strong> $${parseFloat(sale.amount).toFixed(2)}</p>
                <p><strong>Date:</strong> ${sale.order_date}</p>
            </div>
        `;
        document.getElementById('orderDetailsModal').classList.add('open');
    }

    function selectGameFromList(element, gameId) {
        // Remove 'selected' class from all items
        document.querySelectorAll('.game-selector-item').forEach(item => {
            item.classList.remove('selected');
        });
        
        // Add 'selected' class to the clicked item
        element.classList.add('selected');
        
        // Update hidden input and enable submit button
        document.getElementById('selectedBaseGameId').value = gameId;
        document.getElementById('submitExistingBtn').disabled = false;
    }

    function filterGameSelector(query) {
        query = query.toLowerCase();
        const items = document.querySelectorAll('.game-selector-item');
        items.forEach(item => {
            const name = item.getAttribute('data-name');
            if (name.includes(query)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>
</body>
</html>
