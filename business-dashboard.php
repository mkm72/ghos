<?php
session_start();
require_once 'php/db_connect.php';

// =============================================
// ACCESS CONTROL: Business users only
// =============================================

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'business') {
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Access Denied</title><link rel="stylesheet" href="css/navbar.css"></head><body>';
    echo '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:80vh;text-align:center;gap:16px;">';
    echo '<div style="font-size:64px;">🚫</div><div style="font-size:28px;font-weight:bold;">Access Denied</div>';
    echo '<div style="font-size:15px;color:#888;">Business seller access only.</div>';
    echo '<a href="index.php" class="btn-blue">← Back to Store</a></div></body></html>';
    exit;
}

$user_id    = (int)$_SESSION['user_id'];
$user_email = $_SESSION['user_email'] ?? '';

// ---- Fetch all game listings ----
try {
    $stmt_listings = $pdo->prepare("
        SELECT
            g.id,
            g.name,
            g.price,
            g.platform,
            g.genres,
            (SELECT COUNT(*) FROM Game_Keys k WHERE k.game_id = g.id AND k.is_sold = 0) AS stock,
            (SELECT filename FROM Game_Images i WHERE i.game_id = g.id AND i.is_cover = 1 LIMIT 1) AS cover_image
        FROM Games g
        ORDER BY g.id DESC
    ");
    $stmt_listings->execute();
    $listings = $stmt_listings->fetchAll();
} catch (\PDOException $e) {
    $listings = [];
    error_log('business-dashboard listings: ' . $e->getMessage());
}

// ---- Fetch recent sales ----
try {
    $stmt_sales = $pdo->prepare("
        SELECT
            o.id            AS sale_id,
            g.name          AS game_name,
            oi.unit_price   AS amount,
            o.order_date
        FROM Order_Items oi
        JOIN Orders o ON oi.order_id = o.id
        JOIN Games  g ON oi.game_id  = g.id
        ORDER BY o.order_date DESC
        LIMIT 20
    ");
    $stmt_sales->execute();
    $recent_sales = $stmt_sales->fetchAll();
} catch (\PDOException $e) {
    $recent_sales = [];
    error_log('business-dashboard sales: ' . $e->getMessage());
}

// ---- Summary stats ----
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
    <title>Business Dashboard — Ghos</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/dashboard-layout.css">
    <style>
        .badge-green  { background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }
        .badge-red    { background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }
        .badge-orange { background:#ffedd5;color:#ea580c;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }
        .user-chip {
            display:flex;align-items:center;gap:8px;padding:10px 14px;
            background:rgba(255,255,255,0.06);border-radius:8px;margin:12px 0;
            font-size:12px;color:#aaa;word-break:break-all;
        }
        .user-chip .avatar {
            width:28px;height:28px;border-radius:50%;background:#3b82f6;
            display:flex;align-items:center;justify-content:center;
            font-size:13px;font-weight:bold;color:white;flex-shrink:0;
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-box">Ghos</div>
        <span class="logo-name">Seller Panel</span>
    </div>

    <div class="user-chip">
        <div class="avatar"><?= strtoupper(substr($user_email, 0, 1)) ?></div>
        <span><?= htmlspecialchars($user_email) ?></span>
    </div>

    <a href="business-dashboard.php" class="sidebar-link active">📊 Dashboard</a>
    <a href="#" class="sidebar-link">🎮 My Listings</a>
    <a href="#" class="sidebar-link">📈 Sales Reports</a>

    <hr class="sidebar-divider">
    <a href="index.php" class="sidebar-back">← Back to Store</a>
    <a href="auth.php?logout=1" class="sidebar-back" style="color:#ef4444;margin-top:8px;">🚪 Logout</a>
</aside>

<!-- MAIN CONTENT -->
<main class="main-content">
    <h1 class="page-title">Business Dashboard</h1>

    <!-- STATS CARDS -->
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);">
        <div class="stat-card">
            <div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value green">$<?= number_format($total_revenue, 2) ?></div>
            </div>
            <div class="stat-icon icon-green">$</div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-label">Total Games</div>
                <div class="stat-value blue"><?= $total_games ?></div>
            </div>
            <div class="stat-icon icon-blue">🎮</div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-label">Active Listings</div>
                <div class="stat-value green"><?= $active_listings ?></div>
            </div>
            <div class="stat-icon icon-green">📦</div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-label">Total Sales</div>
                <div class="stat-value purple"><?= $total_sales ?></div>
            </div>
            <div class="stat-icon icon-purple">📈</div>
        </div>
    </div>

    <!-- GAME LISTINGS TABLE -->
    <div class="panel" style="margin-top:0;">
        <div class="panel-header">
            <span class="panel-title">Game Listings (<?= $total_games ?>)</span>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Game</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Platform</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($listings)): ?>
                    <tr><td colspan="5" style="text-align:center;color:#888;padding:30px;">No game listings yet.</td></tr>
                <?php else: foreach ($listings as $game):
                    $img = ltrim($game['cover_image'] ?? '', '/');
                ?>
                    <tr>
                        <td>
                            <div class="game-cell">
                                <div class="mini-img bg-dark">
                                    <?php if ($img): ?>
                                        <img src="<?= htmlspecialchars($img) ?>" alt="">
                                    <?php endif; ?>
                                </div>
                                <span class="mini-name"><?= htmlspecialchars($game['name']) ?></span>
                            </div>
                        </td>
                        <td>$<?= number_format((float)$game['price'], 2) ?></td>
                        <td><?= (int)$game['stock'] ?></td>
                        <td><?= htmlspecialchars($game['platform']) ?></td>
                        <td>
                            <?php if ($game['stock'] > 0): ?>
                                <span class="badge-green">Active</span>
                            <?php elseif ($game['stock'] === 0): ?>
                                <span class="badge-red">Out of Stock</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
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
</body>
</html>
