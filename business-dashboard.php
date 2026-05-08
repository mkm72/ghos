<?php
session_start();
require_once 'php/db_connect.php';

// =============================================
// ACCESS CONTROL: Business users only
// =============================================
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'business') {
    header("Location: index.php");
    exit;
}

// ---- Fetch all game listings ----
$stmt_listings = $pdo->prepare("
    SELECT
        g.id,
        g.name,
        g.price,
        g.platform,
        (SELECT COUNT(*) FROM Game_Keys k WHERE k.game_id = g.id AND k.is_sold = 0) AS stock,
        (SELECT filename FROM Game_Images i WHERE i.game_id = g.id AND i.is_cover = 1 LIMIT 1) AS cover_image
    FROM Games g
    ORDER BY g.id DESC
");
$stmt_listings->execute();
$listings = $stmt_listings->fetchAll();

// ---- Fetch recent sales (via Order_Items) ----
$stmt_sales = $pdo->prepare("
    SELECT
        o.id            AS sale_id,
        g.name          AS game_name,
        oi.unit_price   AS amount,
        o.order_date
    FROM Order_Items oi
    JOIN Orders o  ON oi.order_id = o.id
    JOIN Games  g  ON oi.game_id  = g.id
    ORDER BY o.order_date DESC
    LIMIT 10
");
$stmt_sales->execute();
$recent_sales = $stmt_sales->fetchAll();

// ---- Summary stats ----
$total_revenue   = array_sum(array_column($recent_sales, 'amount'));
$active_listings = count(array_filter($listings, fn($l) => $l['stock'] > 0));
$total_sales     = count($recent_sales);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Dashboard — GameHub Online Store</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/dashboard-layout.css">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-box">Ghos</div>
        <span class="logo-name">Seller Panel</span>
    </div>
    <div class="sidebar-subtitle">GameKeys Pro</div>

    <a href="business-dashboard.php" class="sidebar-link active">📊 Dashboard</a>
    <a href="#" class="sidebar-link">➕ Add New Game</a>
    <a href="#" class="sidebar-link">🎮 My Listings</a>
    <a href="#" class="sidebar-link">📈 Sales Reports</a>

    <hr class="sidebar-divider">
    <a href="index.php" class="sidebar-back">← Back to Store</a>
</div>

<!-- MAIN CONTENT -->
<main class="main-content">
    <h1 class="page-title">Business Dashboard</h1>

    <!-- STATS CARDS -->
    <div class="stats-row">
        <div class="stat-card">
            <div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value green">$<?php echo number_format($total_revenue, 2); ?></div>
            </div>
            <div class="stat-icon icon-green">$</div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-label">Active Listings</div>
                <div class="stat-value blue"><?php echo $active_listings; ?></div>
            </div>
            <div class="stat-icon icon-blue">📦</div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-label">Total Sales</div>
                <div class="stat-value purple"><?php echo $total_sales; ?></div>
            </div>
            <div class="stat-icon icon-purple">📈</div>
        </div>
    </div>

    <!-- GAME LISTINGS TABLE -->
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">My Game Listings</span>
            <a href="#" class="btn-add">+ Add New Game</a>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Game</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Platform</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($listings)): ?>
                    <tr><td colspan="6" class="empty-row">No game listings yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($listings as $game): ?>
                    <tr>
                        <td>
                            <div class="game-cell">
                                <div class="game-mini-img bg-dark">
                                    <?php if ($game['cover_image']): ?>
                                        <img src="<?php echo htmlspecialchars(ltrim($game['cover_image'], '/')); ?>"
                                             alt="<?php echo htmlspecialchars($game['name']); ?>">
                                    <?php endif; ?>
                                </div>
                                <span class="game-mini-name"><?php echo htmlspecialchars($game['name']); ?></span>
                            </div>
                        </td>
                        <td>$<?php echo number_format($game['price'], 2); ?></td>
                        <td><?php echo $game['stock']; ?></td>
                        <td><?php echo htmlspecialchars($game['platform']); ?></td>
                        <td>
                            <?php if ($game['stock'] > 0): ?>
                                <span class="badge-green">Active</span>
                            <?php else: ?>
                                <span class="badge-red">Out of Stock</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn-edit">Edit</button>
                            <button class="btn-delete">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- RECENT SALES TABLE -->
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Recent Sales</span>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Game</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_sales)): ?>
                    <tr><td colspan="4" class="empty-row">No sales yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($recent_sales as $sale): ?>
                    <tr>
                        <td>#<?php echo $sale['sale_id']; ?></td>
                        <td><?php echo htmlspecialchars($sale['game_name']); ?></td>
                        <td>$<?php echo number_format($sale['amount'], 2); ?></td>
                        <td><?php echo date('M j, Y', strtotime($sale['order_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>
</body>
</html>