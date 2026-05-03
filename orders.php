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
        <!-- GUEST MESSAGE -->
        <div class="summary-box" style="max-width: 500px; margin: 40px auto; text-align: center; padding: 40px 20px;">
            <div class="summary-title" style="font-size: 1.25rem; margin-bottom: 15px;">Guest Orders</div>
            <p style="color: #64748b; font-size: 1.1rem; line-height: 1.5;">
                Thank you for your purchase! We will email you with your game keys and activation instructions shortly.
            </p>
            <p style="margin-top: 30px; font-size: 0.9rem; color: #94a3b8;">
                Have an account? <a href="auth.php" style="color: #2563eb;">Log in here</a> to view your purchase history.
            </p>
        </div>

    <?php else: ?>
        <!-- ORDER LIST -->
        <?php if (empty($orders)): ?>
            <p style="padding: 20px; background: #fff; border-radius: 8px; text-align: center; border: 1px solid #e2e8f0;">
                You have no orders yet.
            </p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-top">
                        <div class="order-left">
                            <!-- RESTORED IMAGE BLOCK -->
                            <div class="order-thumb thumb-purple">
                                <?php if (!empty($order['cover_image'])): ?>
                                    <img src="<?= htmlspecialchars(ltrim($order['cover_image'], '/')) ?>" alt="cover" style="width:100%; height:100%; object-fit:cover; border-radius:4px;">
                                <?php else: ?>
                                    ⚔️
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="order-game-name"><?= htmlspecialchars($order['game_name']) ?></div>
                                <div class="order-meta">
                                    Order Date: <?= date('n/j/Y', strtotime($order['order_date'])) ?><br>
                                    Order #<?= htmlspecialchars($order['order_id']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="order-right">
                            <div class="order-price">$<?= number_format($order['unit_price'], 2) ?></div>
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
        
    <?php endif; ?>
</div>

<div class="footer">© 2026 GameHub Online Store. All rights reserved.</div>

<script>
    document.querySelectorAll('.reveal-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const body = e.target.closest('.key-box').querySelector('.key-box-body');
            const keyVal = body.querySelector('.key-value');
            if (keyVal.classList.contains('key-hidden')) {
                keyVal.classList.remove('key-hidden');
                e.target.textContent = '🙈 Hide Key';
            } else {
                keyVal.classList.add('key-hidden');
                e.target.textContent = '👁️ Reveal Key';
            }
        });
    });

    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const keyText = e.target.closest('.key-box-body').querySelector('.key-value').textContent;
            navigator.clipboard.writeText(keyText).then(() => {
                const originalText = e.target.textContent;
                e.target.textContent = '✅ Copied!';
                setTimeout(() => e.target.textContent = originalText, 2000);
            });
        });
    });
</script>

</body>
</html>
