<?php
session_start();
require_once 'php/db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;
$user_email = $_SESSION['user_email'] ?? null;
$orders = [];

if ($user_id) {
    $stmt = $pdo->prepare("
        SELECT o.id AS order_id, o.order_date, o.total_price, o.status, oi.quantity, oi.unit_price,
               g.name AS game_name, g.platform, gk.key_code, img.filename AS cover_image
        FROM Orders o
        JOIN Order_Items oi ON o.id = oi.order_id
        JOIN Games g ON oi.game_id = g.id
        JOIN Game_Keys gk ON oi.key_id = gk.id
        LEFT JOIN Game_Images img ON g.id = img.game_id AND img.is_cover = 1
        WHERE o.user_id = :uid OR o.guest_email = :uemail
        ORDER BY o.order_date DESC
    ");
    $stmt->execute(['uid' => $user_id, 'uemail' => $user_email]);
    $orders = $stmt->fetchAll();
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders — GameHub</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/orders.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="page-wrapper">
    <h1 class="page-title">My Orders</h1>
    <?php if (!$user_id): ?>
        <div class="summary-box" style="max-width: 500px; margin: 40px auto; text-align: center; padding: 40px 20px;">
            <div class="summary-title" style="font-size: 1.25rem; margin-bottom: 15px;">Guest Orders</div>
            <p style="color: #64748b; font-size: 1.1rem; line-height: 1.5;">We will email you with your game keys.</p>
            <p style="margin-top: 30px; font-size: 0.9rem; color: #94a3b8;">Have an account? <a href="auth.php">Log in here</a>.</p>
        </div>
    <?php else: ?>
        <?php if (empty($orders)): ?>
            <p style="padding: 20px; background: #fff; text-align: center; border: 1px solid #e2e8f0;">You have no orders yet.</p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-top">
                        <div class="order-left">
                            <div class="order-game-name"><?= htmlspecialchars($order['game_name']) ?></div>
                            <div class="order-meta">Date: <?= date('n/j/Y', strtotime($order['order_date'])) ?> | Order #<?= htmlspecialchars($order['order_id']) ?></div>
                        </div>
                        <div class="order-right">
                            <div class="order-price">$<?= number_format($order['unit_price'], 2) ?></div>
                            <span class="badge-green">✅ Delivered</span>
                        </div>
                    </div>
                    <div class="key-box" style="margin-top: 15px; background: #f8fafc; padding: 15px; border-radius: 6px;">
                        <strong>Key:</strong> <?= htmlspecialchars($order['key_code']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
