<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// 1. PROTECTION: Admin only
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: index.php");
    exit;
}

require_once 'php/db_connect.php';

// --- DASHBOARD STATS ---
$total_revenue = (float) $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM Orders WHERE status IN ('delivered', 'completed')")->fetchColumn();
$total_users = (int) $pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();
$total_orders = (int) $pdo->query("SELECT COUNT(*) FROM Orders")->fetchColumn();
$low_stock_count = (int) $pdo->query("
    SELECT COUNT(*) FROM (
        SELECT game_id FROM Game_Keys WHERE is_sold = 0 GROUP BY game_id HAVING COUNT(id) < 5
    ) AS low
")->fetchColumn();

// --- RECENT ORDERS (Subquery method - 100% Safe) ---
$recent_orders = $pdo->query("
    SELECT 
        o.id, u.email AS user_email, g.name AS game_name, 
        k.key_code AS key_value, oi.unit_price AS total_price, 
        o.order_date AS created_at, o.status,
        (SELECT filename FROM Game_Images WHERE game_id = g.id AND is_cover = 1 LIMIT 1) AS cover_image
    FROM Order_Items oi
    JOIN Orders o ON oi.order_id = o.id
    JOIN Users u ON o.user_id = u.id
    JOIN Games g ON oi.game_id = g.id
    JOIN Game_Keys k ON oi.key_id = k.id
    ORDER BY o.order_date DESC LIMIT 15
")->fetchAll();

// --- GAMES LIST (Subquery method - Bypasses ONLY_FULL_GROUP_BY) ---
$games_list = $pdo->query("
    SELECT 
        g.id, g.name, g.price,
        (SELECT filename FROM Game_Images WHERE game_id = g.id AND is_cover = 1 LIMIT 1) AS cover_image,
        (SELECT COUNT(*) FROM Game_Keys WHERE game_id = g.id AND is_sold = 0) AS stock_count
    FROM Games g
    ORDER BY g.name ASC
")->fetchAll();

function statusBadge($s) {
    $s = strtolower($s);
    if ($s == 'delivered' || $s == 'completed') return '<span class="badge-green">Delivered</span>';
    if ($s == 'pending') return '<span class="badge-blue">Pending</span>';
    return '<span class="badge-red">Cancelled</span>';
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
        .active-section { display: block !important; }
        .sidebar-link.active { background: rgba(255,255,255,0.1); border-left: 4px solid #2563eb; }
        
        .badge-green { background:#dcfce7; color:#16a34a; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:bold; }
        .badge-blue { background:#dbeafe; color:#2563eb; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:bold; }
        .badge-red { background:#fee2e2; color:#dc2626; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:bold; }
        
        .game-cell { display: flex; align-items: center; gap: 10px; }
        .mini-img img { width: 35px; height: 45px; object-fit: cover; border-radius: 4px; background: #222; }

        /* Order Filter Tabs */
        .order-filter-tab {
            padding: 5px 14px; border-radius: 20px; border: 1px solid #e0e0e0;
            background: white; font-size: 12px; font-weight: bold;
            color: #888; cursor: pointer; transition: all 0.15s;
        }
        .order-filter-tab.active { background: #1a1a1a; color: white; border-color: #1a1a1a; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-logo"><div class="logo-box">Ghos</div> Admin</div>
        <!-- IMPORTANT: The data-section attributes are what makes the JS work! -->
        <a href="#" class="sidebar-link active" data-section="section-dashboard">📊 Dashboard</a>
        <a href="#" class="sidebar-link" data-section="section-games">🎮 Manage Games</a>
        <a href="#" class="sidebar-link" data-section="section-orders">🛒 Orders</a>
        <hr class="sidebar-divider">
        <a href="index.php" class="sidebar-back">← Store</a>
        <a href="?logout=1" class="sidebar-back" style="color:#ef4444; margin-top:8px;">🚪 Logout</a>
    </aside>

    <main class="main-content">
        <!-- DASHBOARD SECTION -->
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

        <!-- MANAGE GAMES SECTION -->
        <div id="section-games" class="admin-section panel">
            <div class="panel-header">
                <h2 class="page-title">Games (<span id="gamesCount"><?= count($games_list) ?></span>)</h2>
                <input id="gamesSearch" type="text" placeholder="Search games..." style="padding: 8px; border-radius: 6px; border: 1px solid #ddd;">
            </div>
            <table class="data-table" data-sortable>
                <thead>
                    <tr>
                        <th data-col="id">ID</th>
                        <th>Game</th>
                        <th data-col="price">Price</th>
                        <th data-col="stock">Stock</th>
                    </tr>
                </thead>
                <tbody id="gamesTableBody">
                    <?php foreach ($games_list as $g): ?>
                    <tr>
                        <td data-col="id" data-val="<?= $g['id'] ?>"><?= $g['id'] ?></td>
                        <td>
                            <div class="game-cell">
                                <div class="mini-img">
                                    <img src="<?= htmlspecialchars(ltrim($g['cover_image'] ?? '', '/')) ?>" alt="">
                                </div>
                                <span><?= htmlspecialchars($g['name']) ?></span>
                            </div>
                        </td>
                        <td data-col="price" data-val="<?= $g['price'] ?>">$<?= number_format($g['price'], 2) ?></td>
                        <td data-col="stock" data-val="<?= $g['stock_count'] ?>"><?= $g['stock_count'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr id="gamesEmptySearch" style="display:none;"><td colspan="4" style="text-align:center;">No games found.</td></tr>
                </tbody>
            </table>
        </div>

        <!-- ORDERS SECTION -->
        <div id="section-orders" class="admin-section panel">
            <div class="panel-header">
                <h2 class="page-title">Orders (<span id="ordersCount"><?= count($recent_orders) ?></span>)</h2>
                <div style="display:flex; gap:8px;">
                    <button class="order-filter-tab active" data-filter="all">All</button>
                    <button class="order-filter-tab" data-filter="pending">Pending</button>
                    <button class="order-filter-tab" data-filter="delivered">Delivered</button>
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
                                    <img src="<?= htmlspecialchars(ltrim($o['cover_image'] ?? '', '/')) ?>" alt="">
                                </div>
                                <span><?= htmlspecialchars($o['game_name']) ?></span>
                            </div>
                        </td>
                        <td><code><?= htmlspecialchars($o['key_value']) ?></code></td>
                        <td data-col="price" data-val="<?= $o['total_price'] ?>">$<?= number_format($o['total_price'], 2) ?></td>
                        <td><?= statusBadge($o['status']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- The ?v= forces the browser to pull the newest version of your JS -->
    <script src="js/admin.js?v=<?= time() ?>"></script>
</body>
</html>
