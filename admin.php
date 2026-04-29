<?php
session_start();

// ── PROTECTION: Admin only ───────────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied — GameHub</title>
        <link rel="stylesheet" href="css/navbar.css">
        <style>
            .denied-wrap {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 80vh;
                text-align: center;
                gap: 16px;
            }
            .denied-icon { font-size: 64px; }
            .denied-title { font-size: 28px; font-weight: bold; color: #1a1a1a; }
            .denied-sub   { font-size: 15px; color: #888; max-width: 360px; }
        </style>
    </head>
    <body>
        <div class="denied-wrap">
            <div class="denied-icon">🚫 tessssst </div>
            <div class="denied-title">Access Denied</div>
            <div class="denied-sub">You don't have permission to view this page. Admin access only.</div>
            <a href="index.php" class="btn-blue" style="margin-top:8px;">← Back to Store</a>
        </div>
        <script src="js/navbar.js"></script>
    </body>
    </html>
    <?php
    exit;
}

// ── DB ───────────────────────────────────────────────────────────────────────
require_once 'php/db_connect.php';

$user_role    = $_SESSION['user_role'];
$is_logged_in = true;

// ── STATS ────────────────────────────────────────────────────────────────────

// Total revenue from delivered orders
$stmt = $pdo->query("SELECT COALESCE(SUM(total_price), 0) AS revenue FROM Orders WHERE status = 'delivered'");
$total_revenue = (float) $stmt->fetchColumn();

// Total users
$stmt = $pdo->query("SELECT COUNT(*) FROM Users");
$total_users = (int) $stmt->fetchColumn();

// Low stock games (< 5 keys remaining)
$stmt = $pdo->query("
    SELECT COUNT(*) FROM (
        SELECT g.id
        FROM Games g
        LEFT JOIN Game_Keys k ON g.id = k.game_id AND k.is_sold = 0
        GROUP BY g.id
        HAVING COUNT(k.id) < 5
    ) AS low
");
$low_stock_count = (int) $stmt->fetchColumn();

// Total orders
$stmt = $pdo->query("SELECT COUNT(*) FROM Orders");
$total_orders = (int) $stmt->fetchColumn();

// ── RECENT ORDERS ────────────────────────────────────────────────────────────
$stmt = $pdo->query("
    SELECT
        o.id,
        u.email AS user_email,
        g.name  AS game_name,
        g.id    AS game_id,
        i.filename AS cover_image,
        k.key_value,
        o.total_price,
        o.created_at,
        o.status
    FROM Orders o
    JOIN Users      u ON o.user_id  = u.id
    JOIN Game_Keys  k ON o.key_id   = k.id
    JOIN Games      g ON k.game_id  = g.id
    LEFT JOIN Game_Images i ON g.id = i.game_id AND i.is_cover = 1
    ORDER BY o.created_at DESC
    LIMIT 10
");
$recent_orders = $stmt->fetchAll();

// ── GAMES LIST ───────────────────────────────────────────────────────────────
$stmt = $pdo->query("
    SELECT
        g.id,
        g.name,
        g.price,
        g.genres,
        i.filename AS cover_image,
        COUNT(k.id) AS stock_count
    FROM Games g
    LEFT JOIN Game_Images i ON g.id = i.game_id AND i.is_cover = 1
    LEFT JOIN Game_Keys   k ON g.id = k.game_id AND k.is_sold = 0
    GROUP BY g.id, g.name, g.price, g.genres, i.filename
    ORDER BY g.name ASC
");
$games = $stmt->fetchAll();

// ── HELPERS ──────────────────────────────────────────────────────────────────
$bg_colors = ['bg-purple','bg-green','bg-dark','bg-blue','bg-red','bg-navy','bg-black','bg-forest'];

function stockBadge(int $n): string {
    if ($n === 0)  return '<span class="badge-red">Out of Stock</span>';
    if ($n < 5)    return '<span class="badge-orange">Low Stock</span>';
    return '<span class="badge-green">Available</span>';
}

function statusBadge(string $s): string {
    return match(strtolower($s)) {
        'delivered' => '<span class="badge-green">Delivered</span>',
        'pending'   => '<span class="badge-blue">Pending</span>',
        'cancelled' => '<span class="badge-red">Cancelled</span>',
        default     => '<span class="badge-orange">' . htmlspecialchars(ucfirst($s)) . '</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — GameHub Online Store</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/dashboard-layout.css">
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-box">Ghos</div>
            <span class="logo-name">Admin Panel</span>
        </div>
        <a href="#section-dashboard" class="sidebar-link active">📊 Dashboard</a>
        <a href="#section-games"     class="sidebar-link">🎮 Manage Games</a>
        <a href="#section-orders"    class="sidebar-link">🛒 Orders</a>
        <hr class="sidebar-divider">
        <a href="index.php" class="sidebar-back">← Back to Store</a>
        <a href="?logout=1" class="sidebar-back" style="color:#ef4444; margin-top:8px;">🚪 Logout</a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <!-- ── DASHBOARD OVERVIEW ── -->
        <div id="section-dashboard">
            <h1 class="page-title">Dashboard Overview</h1>

            <div class="stats-grid">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-value green">$<?= number_format($total_revenue, 2) ?></div>
                    </div>
                    <div class="stat-icon icon-green">$</div>
                </div>
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total Users</div>
                        <div class="stat-value blue"><?= $total_users ?></div>
                    </div>
                    <div class="stat-icon icon-blue">👥</div>
                </div>
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total Orders</div>
                        <div class="stat-value blue"><?= $total_orders ?></div>
                    </div>
                    <div class="stat-icon icon-blue">🛒</div>
                </div>
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Low Stock Alerts</div>
                        <div class="stat-value orange"><?= $low_stock_count ?></div>
                    </div>
                    <div class="stat-icon icon-orange">⚠️</div>
                </div>
            </div>
        </div>

        <!-- ── RECENT ORDERS ── -->
        <div id="section-orders" class="panel" style="margin-top: 30px;">
            <div class="panel-header">
                <span class="panel-title">Recent Orders (<?= count($recent_orders) ?>)</span>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Game</th>
                        <th>CD Key</th>
                        <th>Price</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_orders)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; color:#888; padding:30px;">No orders yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_orders as $order):
                            $img = ltrim($order['cover_image'] ?? '', '/');
                        ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['user_email']) ?></td>
                            <td>
                                <div class="game-cell">
                                    <div class="mini-img bg-dark">
                                        <?php if ($img): ?>
                                            <img src="<?= htmlspecialchars($img) ?>" alt="">
                                        <?php endif; ?>
                                    </div>
                                    <span class="mini-name"><?= htmlspecialchars($order['game_name']) ?></span>
                                </div>
                            </td>
                            <td><code><?= htmlspecialchars($order['key_value'] ?? '—') ?></code></td>
                            <td>$<?= number_format((float)$order['total_price'], 2) ?></td>
                            <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                            <td><?= statusBadge($order['status']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ── GAMES LIST ── -->
        <div id="section-games" class="panel" style="margin-top: 30px;">
            <div class="panel-header">
                <span class="panel-title">Games (<?= count($games) ?>)</span>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Game</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($games)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center; color:#888; padding:30px;">No games found.</td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $ci = 0;
                        foreach ($games as $game):
                            $img      = ltrim($game['cover_image'] ?? '', '/');
                            $stock    = (int)$game['stock_count'];
                            $colorCls = $bg_colors[$ci % count($bg_colors)];
                            $ci++;
                        ?>
                        <tr>
                            <td><?= $game['id'] ?></td>
                            <td>
                                <div class="game-cell">
                                    <div class="mini-img <?= $colorCls ?>">
                                        <?php if ($img): ?>
                                            <img src="<?= htmlspecialchars($img) ?>" alt="">
                                        <?php endif; ?>
                                    </div>
                                    <span class="mini-name"><?= htmlspecialchars($game['name']) ?></span>
                                </div>
                            </td>
                            <td>$<?= number_format((float)$game['price'], 2) ?></td>
                            <td class="<?= $stock < 5 ? 'stock-low' : '' ?>"><?= $stock ?></td>
                            <td><?= stockBadge($stock) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

    <!-- Logout handler (inline — no extra file needed) -->
    <?php
    if (isset($_GET['logout'])) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit;
    }
    ?>

</body>
</html>
