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
        <img src="images/logo/logo2.png" alt="Ghos Logo" style="height: 50px; border-radius: 8px;">
        <span class="logo-name">GameHub Online Store</span>
    </a>
    <div class="navbar-search">
        <div class="search-wrap">
            <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" id="searchInput" placeholder="Search for games..." autocomplete="off" style="padding-left: 38px;">
            <div class="search-dropdown" id="searchDropdown"></div>
        </div>
    </div>
    
    <div class="navbar-links">
        <a href="javascript:void(0)" class="contact-link" onclick="document.getElementById('contactModal').style.display='flex'">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l2.28-2.28a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
            Contact Us
        </a>

        <select id="currencySelector" class="currency-select">
            <option value="USD">USD ($)</option>
            <option value="SAR">SAR (ريال)</option>
        </select>

        <?php if ($user_role === 'admin'): ?>
            <a href="admin.php">Admin dashboard</a>
        <?php endif; ?>

        <?php if ($user_role === 'business'): ?>
            <a href="business-dashboard.php">Your Business</a>
        <?php endif; ?>

        <a href="business.php">Business Service</a>

        <?php if ($is_logged_in): ?>
            <div class="profile-dropdown">
                <button class="profile-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 2px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Profile ▼
                </button>
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
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
            Cart
            <span class="cart-badge"><?php echo $cart_count; ?></span>
        </a>
    </div>
</nav>

<!-- ========== CONTACT MODAL ========== -->
<div id="contactModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="document.getElementById('contactModal').style.display='none'">×</span>
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

<script src="js/navbar.js?v=2026.05.15.v2"></script>
