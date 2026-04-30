<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'db.php';

$stmt = $pdo->prepare("
SELECT Cart.id AS cart_id, Cart.quantity,
Games.id AS game_id, Games.name, Games.price, Games.platform,
Game_Images.filename AS cover_image
FROM Cart
JOIN Games ON Cart.game_id = Games.id
LEFT JOIN Game_Images ON Game_Images.game_id = Games.id AND Game_Images.is_cover = 1
WHERE Cart.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shopping Cart — GameHub Online Store</title>
<link rel="stylesheet" href="css/navbar.css">
<link rel="stylesheet" href="css/cart.css">
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
<a href="orders.php">My Orders</a>
<a href="logout.php">Logout</a>
<a href="cart.php" class="cart-link">🛒 Cart <span class="cart-badge"><?= count($cart_items) ?></span></a>
</div>
</nav>

<!-- PAGE CONTENT -->
<div class="page-wrapper">
<h1 class="page-title">Shopping Cart</h1>

<div class="cart-layout">

<!-- LEFT: Cart Table + Trust -->
<div>
<div class="cart-table-wrap">
<table class="cart-table">
<thead>
<tr>
<th>Game</th>
<th>Price</th>
<th>Quantity</th>
<th>Total</th>
<th></th>
</tr>
</thead>
<tbody>

<?php if (empty($cart_items)): ?>
<tr>
<td colspan="5" style="text-align:center; padding: 2rem;">
Your cart is empty. <a href="index.php">Browse games</a>
</td>
</tr>
<?php else: ?>
<?php foreach ($cart_items as $item): ?>
<tr>
<td>
<div class="game-cell">
<div class="game-thumb bg-purple">
<img src="<?= htmlspecialchars($item['cover_image']) ?>"
alt="<?= htmlspecialchars($item['name']) ?>">
</div>
<div>
<div class="game-thumb-name"><?= htmlspecialchars($item['name']) ?></div>
<div class="game-thumb-sub">Steam · Global</div>
</div>
</div>
</td>
<td>$<?= number_format($item['price'], 2) ?></td>
<td>
<div class="qty-wrap">
<button class="qty-btn">−</button>
<input type="number" class="qty-input"
value="<?= $item['quantity'] ?>" min="1">
<button class="qty-btn">+</button>
</div>
</td>
<td class="total-price">
$<?= number_format($item['price'] * $item['quantity'], 2) ?>
</td>
<td><button class="btn-remove">Delete</button></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>

</tbody>
</table>
</div>

<div class="trust-grid">
<div class="trust-card">
<div class="trust-icon">⚡</div>
<div>
<div class="trust-name">Instant Delivery</div>
<div class="trust-sub">Keys sent immediately</div>
</div>
</div>
<div class="trust-card">
<div class="trust-icon">🛡️</div>
<div>
<div class="trust-name">Secure Payment</div>
<div class="trust-sub">100% safe checkout</div>
</div>
</div>
<div class="trust-card">
<div class="trust-icon">🔑</div>
<div>
<div class="trust-name">Official Keys</div>
<div class="trust-sub">Verified sellers only</div>
</div>
</div>
</div>
</div>

<!-- RIGHT: Summary -->
<div class="summary-box">
<div class="summary-title">Order Summary</div>
<div class="summary-row"><span>Subtotal:</span><span>$<?= number_format($subtotal, 2) ?></span></div>
<div class="summary-row"><span>Tax:</span><span>$0.00</span></div>
<hr class="summary-divider">
<div class="summary-total">
<span>Total:</span>
<span class="summary-total-price">$<?= number_format($subtotal, 2) ?></span>
</div>
<a href="orders.php" class="checkout-btn">Proceed to Payment ⚡</a>
<p class="checkout-note">🔒 Digital keys delivered instantly</p>
</div>

</div>
</div>

<div class="footer">© 2026 GameHub Online Store. All rights reserved.</div>

</body>
</html>
