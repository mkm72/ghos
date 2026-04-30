<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'php/db_connect.php';

$stmt = $pdo->prepare("
    SELECT Orders.id, Orders.order_date, Games.name, Games.price, Game_Keys.key_code
    FROM Orders
    JOIN Games ON Orders.game_id = Games.id
    JOIN Game_Keys ON Orders.key_id = Game_Keys.id
    WHERE Orders.user_id = ?
    ORDER BY Orders.order_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders — GameHub Online Store</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/orders.css">
</head>
<body>

<!-- NAVIGATION -->
<nav class="navbar">
    <a href="index.php" class="navbar-logo">
        <div class="logo-box">Ghos</div>
        <span class="logo-name">GameHub Online Store</span>
    </a>
    <div class="navbar-links" style="margin-left: auto;">
        <a href="index.php">Store</a>
        <a href="orders.php" style="color: #2563eb; font-weight: bold;">My Orders</a>
        <a href="logout.php">Logout</a>
        <a href="cart.php" class="cart-link">🛒 Cart <span class="cart-badge">0</span></a>
    </div>
</nav>

<div class="success-bar">✅ Purchase successful! Your keys are ready below.</div>

<div class="page-wrapper">
    <h1 class="page-title">My Orders</h1>

    <?php if (empty($orders)): ?>
        <p>You have no orders yet.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-top">
                    <div class="order-left">
                        <div class="order-thumb thumb-purple">⚔️</div>
                        <div>
                            <div class="order-game-name"><?= htmlspecialchars($order['name']) ?></div>
                            <div class="order-meta">
                                Order Date: <?= date('n/j/Y', strtotime($order['order_date'])) ?><br>
                                Order #<?= $order['id'] ?>
                            </div>
                        </div>
                    </div>
                    <div class="order-right">
                        <div class="order-price">$<?= number_format($order['price'], 2) ?></div>
                        <span class="badge-green">✅ Delivered</span>
                    </div>
                </div>
                <div class="key-box">
                    <div class="key-box-header">
                        <div class="key-box-title">📦 Your CD Key / Activation Code</div>
                        <button class="reveal-btn">👁️ Reveal Key</button>
                    </div>
                    <div class="key-box-body">
                        <div class="key-value key-hidden"><?= htmlspecialchars($order['key_code']) ?></div>
                        <div class="key-hint">Click "Reveal Key" to view your activation code</div>
                        <br>
                        <button class="copy-btn">📋 Copy Key</button>
                    </div>
                    <div class="key-box-footer">🔒 Keep this key safe. You'll need it to activate your game.</div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="footer">© 2026 GameHub Online Store. All rights reserved.</div>

</body>
</html>
