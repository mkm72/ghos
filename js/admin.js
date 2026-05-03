<?php
session_start();
require_once 'php/db_connect.php';

// Protection: Admin only
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: index.php");
    exit;
}

// --- Fetch Stats ---
$total_revenue = (float) $pdo->query("SELECT SUM(g.price) FROM Orders o JOIN Games g ON o.game_id = g.id WHERE o.status IN ('delivered', 'completed')")->fetchColumn();
$total_users = (int) $pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();
$total_orders = (int) $pdo->query("SELECT COUNT(*) FROM Orders")->fetchColumn();
$low_stock = (int) $pdo->query("SELECT COUNT(*) FROM (SELECT g.id FROM Games g LEFT JOIN Game_Keys k ON g.id = k.game_id AND k.is_sold = 0 GROUP BY g.id HAVING COUNT(k.id) < 5) AS low")->fetchColumn();

// --- Fetch Orders (Formatted for admin.js) ---
$recent_orders = $pdo->query("
    SELECT o.id, u.email, g.name as game_name, g.price, o.order_date, o.status
    FROM Orders o
    JOIN Users u ON o.user_id = u.id
    JOIN Games g ON o.game_id = g.id
    ORDER BY o.order_date DESC LIMIT 15
")->fetchAll();

// --- Fetch Games (Formatted for admin.js) ---
$games_list = $pdo->query("
    SELECT g.id, g.name, g.price, COUNT(k.id) as stock
    FROM Games g
    LEFT JOIN Game_Keys k ON g.id = k.game_id AND k.is_sold = 0
    GROUP BY g.id ORDER BY g.name ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ghos Admin</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/dashboard-layout.css">
</head>
<!-- The data-flash attributes allow your initToast() to show messages on load -->
<body data-flash="<?= $_SESSION['success'] ?? '' ?>" data-flash-type="success">
    <?php unset($_SESSION['success']); ?>

    <aside class="sidebar">
        <div class="sidebar-logo"><div class="logo-box">Ghos</div> Admin</div>
        <!-- Matches your initSidebar() querySelector -->
        <a href="#" class="sidebar-link" data-section="section-dashboard">📊 Dashboard</a>
        <a href="#" class="sidebar-link" data-section="section-games">🎮 Manage Games</a>
        <a href="#" class="sidebar-link" data-section="section-orders">🛒 Orders</a>
        <hr class="sidebar-divider">
        <a href="index.php" class="sidebar-back">← Store</a>
    </aside>

    <main class="main-content">
        <!-- Section: Dashboard -->
        <div id="section-dashboard" class="admin-section">
            <h1 class="page-title">Dashboard</h1>
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-label">Revenue</div><div class="stat-value">$<?= number_format($total_revenue, 2) ?></div></div>
                <div class="stat-card"><div class="stat-label">Users</div><div class="stat-value"><?= $total_users ?></div></div>
                <div class="stat-card"><div class="stat-label">Orders</div><div class="stat-value"><?= $total_orders ?></div></div>
                <div class="stat-card"><div class="stat-label">Low Stock</div><div class="stat-value orange"><?= $low_stock ?></div></div>
            </div>
        </div>

        <!-- Section: Manage Games -->
        <div id="section-games" class="admin-section panel">
            <div class="panel-header">
                <h2>Games (<span id="gamesCount"><?= count($games_list) ?></span>)</h2>
                <input id="gamesSearch" type="text" placeholder="Live search...">
            </div>
            <table data-sortable class="data-table">
                <thead>
                    <tr>
                        <th data-col="id">ID</th>
                        <th data-col="name">Name</th>
                        <th data-col="price">Price</th>
                        <th data-col="stock">Stock</th>
                    </tr>
                </thead>
                <tbody id="gamesTableBody">
                    <?php foreach ($games_list as $g): ?>
                    <tr>
                        <td data-col="id" data-val="<?= $g['id'] ?>"><?= $g['id'] ?></td>
                        <td data-col="name"><?= htmlspecialchars($g['name']) ?></td>
                        <td data-col="price" data-val="<?= $g['price'] ?>">$<?= number_format($g['price'], 2) ?></td>
                        <td data-col="stock" data-val="<?= $g['stock'] ?>"><?= $g['stock'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <!-- Matches your initSearch() empty state -->
                    <tr id="gamesEmptySearch" style="display:none;"><td colspan="4">No matches found.</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Section: Orders -->
        <div id="section-orders" class="admin-section panel">
            <div class="panel-header">
                <h2>Orders (<span id="ordersCount"><?= count($recent_orders) ?></span>)</h2>
                <div class="filter-tabs">
                    <button class="order-filter-tab active" data-filter="all">All</button>
                    <button class="order-filter-tab" data-filter="pending">Pending</button>
                    <button class="order-filter-tab" data-filter="delivered">Delivered</button>
                </div>
            </div>
            <table data-sortable class="data-table">
                <thead>
                    <tr>
                        <th data-col="id">Order #</th>
                        <th>User</th>
                        <th data-col="price">Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    <?php foreach ($recent_orders as $o): ?>
                    <!-- data-status is used by your initOrderStatusFilter() -->
                    <tr data-status="<?= strtolower($o['status']) ?>">
                        <td data-col="id" data-val="<?= $o['id'] ?>"><?= $o['id'] ?></td>
                        <td><?= htmlspecialchars($o['email']) ?></td>
                        <td data-col="price" data-val="<?= $o['price'] ?>">$<?= number_format($o['price'], 2) ?></td>
                        <td><?= htmlspecialchars($o['status']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="js/admin.js"></script>
</body>
</html>
