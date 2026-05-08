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
        <a href="javascript:void(0)" class="contact-link" onclick="document.getElementById('contactModal').style.display='flex'">📞 Contact Us</a>

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
                    <a href="orders.php">My Orders</a>
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

<!-- ========== CONTACT MODAL ========== -->
<div id="contactModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">×</span>
        <h2>Contact Us</h2>
        <p><strong>Email:</strong> support@ghos.com</p>
        <p><strong>Location:</strong> IAU, Saudi Arabia</p>
        <div class="map-container">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1754.1487865674999!2d50.194878487615526!3d26.39430784899561!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e49ef811304efab%3A0xe664343a49ebbf2b!2sCollege%20of%20Computer%20Science%20and%20Information%20Technology!5e0!3m2!1sen!2ssa!4v1776965850652!5m2!1sen!2ssa"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</div>

<script src="js/navbar.js"></script>
