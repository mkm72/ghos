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

// Total Revenue from Orders table
$stmt = $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM Orders WHERE status IN ('delivered', 'completed')");
$total_revenue = (float) $stmt->fetchColumn();

// Total Users
$total_users = (int) $pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();

// Total Orders
$total_orders = (int) $pdo->query("SELECT COUNT(*) FROM Orders")->fetchColumn();

// Low Stock Alerts (< 5 unsold keys)[cite: 2]
$stmt = $pdo->query("
    SELECT COUNT(*) FROM (
        SELECT g.id FROM Games g 
        LEFT JOIN Game_Keys k ON g.id = k.game_id AND k.is_sold = 0 
        GROUP BY g.id HAVING COUNT(k.id) < 5
    ) AS low
");
$low_stock_count = (int) $stmt->fetchColumn();

// --- RECENT ORDERS (Using Order_Items Bridge) ---
// This query finds the items inside the orders to show game names and keys[cite: 2]
$stmt = $pdo->query("
    SELECT 
        o.id, 
        u.email AS user_email, 
        g.name AS game_name, 
        k.key_code AS key_value, 
        oi.unit_price AS total_price, 
        o.order_date AS created_at, 
        o.status,
        i.filename AS cover_image
    FROM Order_Items oi
    JOIN Orders o ON oi.order_id = o.id
    JOIN Users u ON o.user_id = u.id
    JOIN Games g ON oi.game_id = g.id
    JOIN Game_Keys k ON oi.key_id = k.id
    LEFT JOIN Game_Images i ON g.id = i.game_id AND i.is_cover = 1
    ORDER BY o.order_date DESC LIMIT 10
");
$recent_orders = $stmt->fetchAll();

// ── GAMES LIST ───────────────────────────────────────────────────────────────
// We now include g.name and g.price in the GROUP BY to satisfy MySQL 8 requirements
// We use MAX(i.filename) to pick the cover image safely
$games = $pdo->query("
    SELECT 
        g.id, 
        g.name, 
        g.price, 
        MAX(i.filename) AS cover_image, 
        COUNT(k.id) AS stock_count
    FROM Games g
    LEFT JOIN Game_Images i ON g.id = i.game_id AND i.is_cover = 1
    LEFT JOIN Game_Keys k ON g.id = k.game_id AND k.is_sold = 0
    GROUP BY g.id, g.name, g.price
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
        .mini-img img { width: 40px; height: 50px; object-fit: cover; border-radius: 4px; }
        .game-cell { display: flex; align-items: center; gap: 10px; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-logo"><div class="logo-box">Ghos</div> Admin</div>
        <!-- Note: uses data-section for your admin.js -->
        <a href="#" class="sidebar-link active" data-section="section-dashboard">📊 Dashboard</a>
        <a href="#" class="sidebar-link" data-section="section-games">🎮 Manage Games</a>
        <a href="#" class="sidebar-link" data-section="section-orders">🛒 Orders</a>
        <hr class="sidebar-divider">
        <a href="index.php" class="sidebar-back">← Store</a>
    </aside>

    <main class="main-content">
        <!-- DASHBOARD -->
        <div id="section-dashboard" class="admin-section active-section">
            <h1 class="page-title">Dashboard</h1>
            <div class="stats-grid">
                <div class="stat-card">
                    <div><div class="stat-label">Revenue</div><div class="stat-value">$<?= number_format($total_revenue, 2) ?></div></div>
                    <div class="stat-icon">$</div>
                </div>
                <div class="stat-card">
                    <div><div class="stat-label">Users</div><div class="stat-value"><?= $total_users ?></div></div>
                    <div class="stat-icon">👥</div>
                </div>
                <div class="stat-card">
                    <div><div class="stat-label">Orders</div><div class="stat-value"><?= $total_orders ?></div></div>
                    <div class="stat-icon">🛒</div>
                </div>
                <div class="stat-card">
                    <div><div class="stat-label">Low Stock</div><div class="stat-value orange"><?= $low_stock_count ?></div></div>
                    <div class="stat-icon">⚠️</div>
                </div>
            </div>
        </div>

        <!-- ORDERS SECTION -->
        <div id="section-orders" class="admin-section panel">
            <h1 class="page-title">Orders (<span id="ordersCount"><?= count($recent_orders) ?></span>)</h1>
            <table class="data-table" data-sortable>
                <thead><tr><th data-col="id">#</th><th>User</th><th>Game</th><th>Key</th><th data-col="price">Price</th><th>Status</th></tr></thead>
                <tbody id="ordersTableBody">
                    <?php foreach ($recent_orders as $o): ?>
                    <tr data-status="<?= strtolower($o['status']) ?>">
                        <td><?= $o['id'] ?></td>
                        <td><?= htmlspecialchars($o['user_email']) ?></td>
                        <td>
                            <div class="game-cell">
                                <div class="mini-img">
                                    <img src="<?= ltrim($o['cover_image'] ?? '', '/') ?>" alt="">
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

        <!-- GAMES SECTION -->
        <div id="section-games" class="admin-section panel">
            <div class="panel-header">
                <h2>Games (<span id="gamesCount"><?= count($games) ?></span>)</h2>
                <input id="gamesSearch" type="text" placeholder="Search...">
            </div>
            <table class="data-table" data-sortable>
                <thead><tr><th data-col="id">ID</th><th>Game</th><th data-col="price">Price</th><th data-col="stock">Stock</th></tr></thead>
                <tbody id="gamesTableBody">
                    <?php foreach ($games as $g): ?>
                    <tr>
                        <td><?= $g['id'] ?></td>
                        <td><?= htmlspecialchars($g['name']) ?></td>
                        <td data-val="<?= $g['price'] ?>">$<?= number_format($g['price'], 2) ?></td>
                        <td data-val="<?= $g['stock_count'] ?>"><?= $g['stock_count'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script src="js/admin.js"></script>
</body>
</html>
