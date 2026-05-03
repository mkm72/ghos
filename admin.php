<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// 1. PROTECTION: Admin only (Checking role 'admin' per your Users table)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: index.php");
    exit;
}

require_once 'php/db_connect.php';

// --- STATS QUERIES ---
// Total revenue from delivered orders (Using Orders table directly)
$stmt = $pdo->query("SELECT SUM(g.price) FROM Orders o JOIN Games g ON o.game_id = g.id WHERE o.status IN ('delivered', 'completed')");
$total_revenue = (float) $stmt->fetchColumn();

// Total users
$total_users = (int) $pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();

// Low stock games (< 5 keys)
$stmt = $pdo->query("SELECT COUNT(*) FROM (SELECT g.id FROM Games g LEFT JOIN Game_Keys k ON g.id = k.game_id AND k.is_sold = 0 GROUP BY g.id HAVING COUNT(k.id) < 5) AS low");
$low_stock_count = (int) $stmt->fetchColumn();

$total_orders = (int) $pdo->query("SELECT COUNT(*) FROM Orders")->fetchColumn();

// --- RECENT ORDERS (Updated to match your actual SQL schema) ---
$stmt = $pdo->query("
    SELECT o.id, u.email AS user_email, g.name AS game_name, i.filename AS cover_image, 
           k.key_code AS key_value, g.price AS total_price, o.order_date AS created_at, o.status
    FROM Orders o
    JOIN Users u ON o.user_id = u.id
    JOIN Games g ON o.game_id = g.id
    JOIN Game_Keys k ON o.key_id = k.id
    LEFT JOIN Game_Images i ON g.id = i.game_id AND i.is_cover = 1
    ORDER BY o.order_date DESC LIMIT 10
");
$recent_orders = $stmt->fetchAll();

// --- GAMES LIST ---
$games = $pdo->query("
    SELECT g.id, g.name, g.price, i.filename AS cover_image, COUNT(k.id) AS stock_count
    FROM Games g
    LEFT JOIN Game_Images i ON g.id = i.game_id AND i.is_cover = 1
    LEFT JOIN Game_Keys k ON g.id = k.game_id AND k.is_sold = 0
    GROUP BY g.id ORDER BY g.name ASC
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
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-logo"><div class="logo-box">Ghos</div><span class="logo-name">Admin</span></div>
        <a href="#section-dashboard" class="sidebar-link active">📊 Dashboard</a>
        <a href="#section-games" class="sidebar-link">🎮 Manage Games</a>
        <a href="#section-orders" class="sidebar-link">🛒 Orders</a>
        <hr class="sidebar-divider">
        <a href="index.php" class="sidebar-back">← Back to Store</a>
    </aside>

    <main class="main-content">
        <!-- DASHBOARD (Added active-section class so it shows by default) -->
        <div id="section-dashboard" class="admin-section active-section">
            <h1 class="page-title">Dashboard</h1>
            <div class="stats-grid">
                <div class="stat-card">
                    <div><div class="stat-label">Revenue</div><div class="stat-value green">$<?= number_format($total_revenue, 2) ?></div></div>
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

        <!-- ORDERS SECTION -->
        <div id="section-orders" class="admin-section panel">
            <h1 class="page-title">Recent Orders</h1>
            <table class="data-table">
                <thead><tr><th>#</th><th>User</th><th>Game</th><th>CD Key</th><th>Price</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($recent_orders as $o): ?>
                    <tr>
                        <td><?= $o['id'] ?></td>
                        <td><?= htmlspecialchars($o['user_email']) ?></td>
                        <td><?= htmlspecialchars($o['game_name']) ?></td>
                        <td><code><?= htmlspecialchars($o['key_value']) ?></code></td>
                        <td>$<?= number_format($o['total_price'], 2) ?></td>
                        <td><?= statusBadge($o['status']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- GAMES SECTION -->
        <div id="section-games" class="admin-section panel">
            <h1 class="page-title">Manage Games</h1>
            <table class="data-table">
                <thead><tr><th>ID</th><th>Game</th><th>Price</th><th>Stock</th></tr></thead>
                <tbody>
                    <?php foreach ($games as $g): ?>
                    <tr>
                        <td><?= $g['id'] ?></td>
                        <td><?= htmlspecialchars($g['name']) ?></td>
                        <td>$<?= number_format($g['price'], 2) ?></td>
                        <td><?= $g['stock_count'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script src="js/admin.js"></script>
</body>
</html>
