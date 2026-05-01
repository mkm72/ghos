<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

$is_logged_in = isset($_SESSION['user_id']); 
$user_role = $_SESSION['role'] ?? '';

$cart_count = 0;
if ($is_logged_in) {
    require_once 'php/db_connect.php';
    $stmt_cart = $pdo->prepare("SELECT SUM(quantity) FROM Cart WHERE user_id = ?");
    $stmt_cart->execute([$_SESSION['user_id']]);
    $cart_count = (int)$stmt_cart->fetchColumn(); 
}
?>

<nav class="navbar">
    <a href="index.php" class="navbar-logo">
        <div class="logo-box">Ghos</div>
        <span class="logo-name">GameHub Online Store</span>
    </a>
    <div class="navbar-search">
        <input type="text" placeholder="Search for games...">
    </div>
    
    <div class="navbar-links">
        <a href="#contactModal" class="contact-link">📞 Contact Us</a>

        <select id="currencySelector" class="currency-select">
            <option value="USD">USD ($)</option>
            <option value="SAR">SAR (ر.س)</option>
        </select>

        <?php if ($user_role === 'admin'): ?>
            <a href="admin.php">Admin dashboard</a>
        <?php endif; ?>

        <?php if ($user_role === 'business'): ?>
            <a href="business-dashboard.html">Your Business</a>
        <?php endif; ?>

        <a href="business.html">Business Service</a>

        <?php if ($is_logged_in): ?>
            <div class="profile-dropdown">
                <button class="profile-btn">👤 Profile ▼</button>
                <div class="dropdown-content">
                    <a href="settings.php">Settings</a>
                    <a href="?logout=1">Logout</a>
                </div>
            </div>
        <?php else: ?>
            <a href="auth.php">Login</a>
        <?php endif; ?>

        <a href="cart.php" class="cart-link">
            🛒 Cart
            <span class="cart-badge"><?php echo $cart_count; ?></span>
        </a>
    </div>
</nav>

<script src="js/navbar.js"></script>
