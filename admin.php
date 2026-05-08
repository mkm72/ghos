<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// ── LOGOUT ───────────────────────────────────────
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

// ── PROTECTION ───────────────────────────────────
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    ?><!DOCTYPE html><html><head><meta charset="UTF-8"><title>Access Denied</title>
    <link rel="stylesheet" href="css/navbar.css"></head><body>
    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:80vh;text-align:center;gap:16px;">
        <div style="font-size:64px;">🚫</div>
        <div style="font-size:28px;font-weight:bold;">Access Denied</div>
        <div style="font-size:15px;color:#888;">Admin access only.</div>
        <a href="index.php" class="btn-blue">← Back to Store</a>
    </div></body></html><?php
    exit;
}

require_once 'php/db_connect.php';
$user_role    = $_SESSION['role'];
$is_logged_in = true;

// ── HANDLE ADD GAME ──────────────────────────────
$add_error   = '';
$add_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_game') {
    $name     = trim($_POST['name'] ?? '');
    $price    = (float)($_POST['price'] ?? 0);
    $platform = trim($_POST['platform'] ?? '');
    $genres   = trim($_POST['genres'] ?? '');

    if (empty($name) || $price <= 0 || empty($platform)) {
        $add_error = 'Name, price, and platform are required.';
    } else {
        $ins = $pdo->prepare('INSERT INTO Games (name, price, platform, genres) VALUES (?, ?, ?, ?)');
        $ins->execute([$name, $price, $platform, $genres]);
        $game_id = $pdo->lastInsertId();

        // Handle image upload
        if (!empty($_FILES['cover_image']['name'])) {
            $file     = $_FILES['cover_image'];
            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed  = ['jpg','jpeg','png','webp'];
            if (!in_array($ext, $allowed)) {
                $add_error = 'Image must be jpg, png, or webp.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $add_error = 'Image must be under 5MB.';
            } else {
                $dir      = 'images/games/';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $filename = 'game_' . $game_id . '_' . time() . '.' . $ext;
                $dest     = $dir . $filename;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $pdo->prepare('INSERT INTO Game_Images (game_id, filename, is_cover) VALUES (?, ?, 1)')
                        ->execute([$game_id, $dest]);
                }
            }
        }

        if (!$add_error) $add_success = "Game \"$name\" added successfully!";
    }
}

// ── STATS ────────────────────────────────────────
$stmt = $pdo->query("SELECT COALESCE(SUM(total_price), 0) AS revenue FROM Orders WHERE status IN ('delivered', 'completed')");
$total_revenue = (float)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM Users");
$total_users = (int)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM (SELECT g.id FROM Games g LEFT JOIN Game_Keys k ON g.id = k.game_id AND k.is_sold = 0 GROUP BY g.id HAVING COUNT(k.id) < 5) AS low");
$low_stock_count = (int)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM Orders");
$total_orders = (int)$stmt->fetchColumn();

// ── RECENT ORDERS ────────────────────────────────
$recent_orders = $pdo->query("
    SELECT o.id, u.email AS user_email, g.name AS game_name, g.id AS game_id,
           i.filename AS cover_image, k.key_code AS key_value,
           oi.unit_price AS total_price, o.order_date AS created_at, o.status
    FROM Orders o
    JOIN Users u ON o.user_id = u.id
    JOIN Order_Items oi ON o.id = oi.order_id
    JOIN Game_Keys k ON oi.key_id = k.id
    JOIN Games g ON oi.game_id = g.id
    LEFT JOIN Game_Images i ON g.id = i.game_id AND i.is_cover = 1
    ORDER BY o.order_date DESC LIMIT 10
")->fetchAll();

// ── GAMES LIST ───────────────────────────────────
$games = $pdo->query("
    SELECT g.id, g.name, g.price, g.genres, g.platform,
           i.filename AS cover_image, COUNT(k.id) AS stock_count
    FROM Games g
    LEFT JOIN Game_Images i ON g.id = i.game_id AND i.is_cover = 1
    LEFT JOIN Game_Keys k ON g.id = k.game_id AND k.is_sold = 0
    GROUP BY g.id, g.name, g.price, g.genres, g.platform, i.filename
    ORDER BY g.name ASC
")->fetchAll();

$bg_colors = ['bg-purple','bg-green','bg-dark','bg-blue','bg-red','bg-navy','bg-black','bg-forest'];

function stockBadge(int $n): string {
    if ($n === 0) return '<span class="badge-red">Out of Stock</span>';
    if ($n < 5)   return '<span class="badge-orange">Low Stock</span>';
    return '<span class="badge-green">Available</span>';
}
function statusBadge(string $s): string {
    return match(strtolower($s)) {
        'delivered','completed' => '<span class="badge-green">Delivered</span>',
        'pending'   => '<span class="badge-blue">Pending</span>',
        'cancelled' => '<span class="badge-red">Cancelled</span>',
        default     => '<span class="badge-orange">'.htmlspecialchars(ucfirst($s)).'</span>',
    };
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
        /* ── Sections ── */
        .admin-section { display: none; }
        .admin-section.active-section { display: block; }

        /* ── Badges ── */
        .badge-green  { background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }
        .badge-blue   { background:#dbeafe;color:#2563eb;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }
        .badge-red    { background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }
        .badge-orange { background:#ffedd5;color:#ea580c;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }

        /* ── Order filter tabs ── */
        .order-filter-tab { padding:5px 14px;border-radius:20px;border:1px solid #e0e0e0;background:white;font-size:12px;font-weight:bold;color:#888;cursor:pointer;transition:all .15s; }
        .order-filter-tab.active { background:#1a1a1a;color:white;border-color:#1a1a1a; }
        .order-filter-tab:hover:not(.active) { background:#f5f5f5; }

        /* ── Sort arrows ── */
        th[data-col] { cursor:pointer; }
        th[data-col]:hover { background:#f0f0f0; }
        th.sort-asc::after  { content:' ↑'; color:#2563eb; }
        th.sort-desc::after { content:' ↓'; color:#2563eb; }

        /* ── Add game form ── */
        .add-game-form { padding:20px; }
        .form-row { display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px; }
        .form-row.full { grid-template-columns:1fr; }
        .fg label { display:block;font-size:12px;font-weight:bold;color:#666;text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px; }
        .fg input,.fg select,.fg textarea {
            width:100%;padding:8px 12px;border:1px solid #e0e0e0;border-radius:6px;
            font-size:14px;outline:none;transition:border-color .2s;background:#fff;
        }
        .fg input:focus,.fg select:focus,.fg textarea:focus { border-color:#2563eb; }
        .fg textarea { resize:vertical;min-height:70px; }

        /* Image upload zone */
        .upload-zone {
            border:2px dashed #e0e0e0;border-radius:8px;padding:20px;text-align:center;
            cursor:pointer;transition:all .2s;background:#fafafa;position:relative;
        }
        .upload-zone:hover,.upload-zone.drag { border-color:#2563eb;background:#eff6ff; }
        .upload-zone input[type=file] { position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%; }
        .upload-zone-icon { font-size:28px;margin-bottom:6px; }
        .upload-zone-text { font-size:13px;color:#888; }
        .upload-zone-text strong { color:#2563eb; }
        #img-preview { max-height:80px;border-radius:6px;margin-top:10px;display:none; }

        .form-actions { display:flex;justify-content:flex-end;gap:10px;padding:0 20px 20px; }

        .alert-sm { font-size:13px;padding:9px 14px;border-radius:6px;margin:0 20px 14px; }
        .alert-sm.success { background:#f0fdf4;border:1px solid #86efac;color:#15803d; }
        .alert-sm.error   { background:#fff0f0;border:1px solid #fca5a5;color:#b91c1c; }

        /* search input */
        .search-input {
            padding:6px 12px;border:1px solid #e0e0e0;border-radius:6px;
            font-size:13px;outline:none;width:200px;
        }
        .search-input:focus { border-color:#2563eb; }

        /* Stats grid 4 cols */
        .stats-grid { grid-template-columns: repeat(4,1fr); }
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
    <a href="#" class="sidebar-link" data-section="section-games">🎮 Manage Games</a>
    <a href="#" class="sidebar-link" data-section="section-add-game">➕ Add Game</a>
    <hr class="sidebar-divider">
    <a href="index.php" class="sidebar-back">← Back to Store</a>
    <a href="?logout=1" class="sidebar-back" style="color:#ef4444;margin-top:8px;">🚪 Logout</a>
</aside>

<!-- MAIN -->
<main class="main-content">

    <!-- ── DASHBOARD ── -->
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
    </div>

    <!-- ── ORDERS ── -->
    <div id="section-orders" class="admin-section panel" style="margin-top:0;">
        <div class="panel-header">
            <span class="panel-title">Orders (<span id="ordersCount"><?= count($recent_orders) ?></span>)</span>
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
                <th>Game</th>
                <th>CD Key</th>
                <th data-col="price">Price</th>
                <th data-col="date">Date</th>
                <th>Status</th>
            </tr></thead>
            <tbody id="ordersTableBody">
            <?php if (empty($recent_orders)): ?>
                <tr><td colspan="7" style="text-align:center;color:#888;padding:30px;">No orders yet.</td></tr>
            <?php else: foreach ($recent_orders as $order):
                $img = ltrim($order['cover_image'] ?? '', '/');
                $statusLower = strtolower($order['status']);
            ?>
                <tr data-status="<?= htmlspecialchars($statusLower) ?>">
                    <td data-col="id" data-val="<?= $order['id'] ?>"><?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['user_email']) ?></td>
                    <td>
                        <div class="game-cell">
                            <div class="mini-img bg-dark"><?php if($img):?><img src="<?= htmlspecialchars($img)?>" alt=""><?php endif;?></div>
                            <span class="mini-name"><?= htmlspecialchars($order['game_name']) ?></span>
                        </div>
                    </td>
                    <td><code><?= htmlspecialchars($order['key_value'] ?? '—') ?></code></td>
                    <td data-col="price" data-val="<?= (float)$order['total_price'] ?>">$<?= number_format((float)$order['total_price'],2) ?></td>
                    <td data-col="date" data-val="<?= strtotime($order['created_at']) ?>"><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                    <td><?= statusBadge($order['status']) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ── GAMES LIST ── -->
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
            </tr></thead>
            <tbody id="gamesTableBody">
            <?php if (empty($games)): ?>
                <tr><td colspan="5" style="text-align:center;color:#888;padding:30px;">No games found.</td></tr>
            <?php else:
                $ci = 0;
                foreach ($games as $game):
                    $img   = ltrim($game['cover_image'] ?? '', '/');
                    $stock = (int)$game['stock_count'];
                    $clr   = $bg_colors[$ci++ % count($bg_colors)];
            ?>
                <tr>
                    <td data-col="id" data-val="<?= $game['id'] ?>"><?= $game['id'] ?></td>
                    <td>
                        <div class="game-cell">
                            <div class="mini-img <?= $clr ?>"><?php if($img):?><img src="<?= htmlspecialchars($img)?>" alt=""><?php endif;?></div>
                            <span class="mini-name"><?= htmlspecialchars($game['name']) ?></span>
                        </div>
                    </td>
                    <td data-col="price" data-val="<?= (float)$game['price'] ?>">$<?= number_format((float)$game['price'],2) ?></td>
                    <td data-col="stock" data-val="<?= $stock ?>" class="<?= $stock < 5 ? 'stock-low' : '' ?>"><?= $stock ?></td>
                    <td><?= stockBadge($stock) ?></td>
                </tr>
            <?php endforeach; endif; ?>
                <tr id="gamesEmptySearch" style="display:none;">
                    <td colspan="5" style="text-align:center;color:#aaa;padding:24px;">No games match your search.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ── ADD GAME ── -->
    <div id="section-add-game" class="admin-section">
        <h1 class="page-title">Add New Game</h1>
        <div class="panel">
            <?php if ($add_success): ?>
                <div class="alert-sm success"><?= htmlspecialchars($add_success) ?></div>
            <?php endif; ?>
            <?php if ($add_error): ?>
                <div class="alert-sm error"><?= htmlspecialchars($add_error) ?></div>
            <?php endif; ?>
            <form method="POST" action="admin.php#section-add-game" enctype="multipart/form-data" class="add-game-form">
                <input type="hidden" name="action" value="add_game">

                <div class="form-row">
                    <div class="fg">
                        <label>Game Name *</label>
                        <input type="text" name="name" placeholder="e.g. Elden Ring" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>
                    <div class="fg">
                        <label>Price (USD) *</label>
                        <input type="number" name="price" step="0.01" min="0" placeholder="29.99" required value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="fg">
                        <label>Platform *</label>
                        <select name="platform" required>
                            <option value="">Select platform</option>
                            <?php foreach(['PC','PlayStation','Xbox','Nintendo Switch','Multi-Platform'] as $p): ?>
                                <option value="<?= $p ?>" <?= ($_POST['platform'] ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="fg">
                        <label>Genres</label>
                        <input type="text" name="genres" placeholder="e.g. Action, RPG" value="<?= htmlspecialchars($_POST['genres'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row full">
                    <div class="fg">
                        <label>Cover Image</label>
                        <div class="upload-zone" id="uploadZone">
                            <input type="file" name="cover_image" id="coverInput" accept="image/jpeg,image/png,image/webp" onchange="previewImg(this)">
                            <div class="upload-zone-icon">🖼️</div>
                            <div class="upload-zone-text"><strong>Click to upload</strong> or drag & drop<br><span style="font-size:11px;">JPG, PNG, WEBP — max 5MB</span></div>
                            <img id="img-preview" src="" alt="Preview">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="reset" onclick="resetPreview()" style="padding:9px 20px;border:1px solid #e0e0e0;border-radius:6px;background:white;cursor:pointer;font-size:14px;">Clear</button>
                    <button type="submit" class="btn-blue" style="padding:9px 24px;">Add Game</button>
                </div>
            </form>
        </div>
    </div>

</main>

<script src="js/admin.js"></script>
<script>
// Image preview
function previewImg(input) {
    const preview = document.getElementById('img-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
        document.querySelector('.upload-zone-text').style.display = 'none';
        document.querySelector('.upload-zone-icon').style.display = 'none';
    }
}
function resetPreview() {
    const preview = document.getElementById('img-preview');
    preview.src = ''; preview.style.display = 'none';
    document.querySelector('.upload-zone-text').style.display = '';
    document.querySelector('.upload-zone-icon').style.display = '';
}

// Drag & drop
const zone = document.getElementById('uploadZone');
zone?.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag'); });
zone?.addEventListener('dragleave', () => zone.classList.remove('drag'));
zone?.addEventListener('drop', e => {
    e.preventDefault(); zone.classList.remove('drag');
    const file = e.dataTransfer.files[0];
    if (file) {
        const input = document.getElementById('coverInput');
        const dt = new DataTransfer(); dt.items.add(file); input.files = dt.files;
        previewImg(input);
    }
});

<?php if ($add_success): ?>
window.addEventListener('load', () => showToast('<?= addslashes($add_success) ?>', 'success'));
<?php elseif ($add_error): ?>
window.addEventListener('load', () => showToast('<?= addslashes($add_error) ?>', 'error'));
<?php endif; ?>
</script>

</body>
</html>
