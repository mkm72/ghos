<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$is_logged_in = isset($_SESSION['user_id']);
$user_role    = $_SESSION['role'] ?? '';
$cart_count   = 0;

require_once 'php/db_connect.php';

if ($is_logged_in) {
    $stmt_cart = $pdo->prepare("SELECT SUM(c.quantity) AS count, SUM(c.quantity * g.price) AS total FROM Cart c JOIN Games g ON c.game_id = g.id WHERE c.user_id = ?");
    $stmt_cart->execute([$_SESSION['user_id']]);
} else {
    $session_id = session_id();
    $stmt_cart = $pdo->prepare("SELECT SUM(c.quantity) AS count, SUM(c.quantity * g.price) AS total FROM Cart c JOIN Games g ON c.game_id = g.id WHERE c.session_id = ?");
    $stmt_cart->execute([$session_id]);
}
$cart_data = $stmt_cart->fetch();
$cart_count = (int)($cart_data['count'] ?? 0);
$cart_total = (float)($cart_data['total'] ?? 0);
?>

<nav class="navbar">

    <!-- Logo -->
    <a href="index.php" class="navbar-logo">
        <img src="images/logo/logo2.png" alt="Ghos Logo" style="height:50px;border-radius:8px;">
        <span class="logo-name">GameHub Online Store</span>
    </a>

    <!-- Search (desktop) -->
    <div class="navbar-search">
        <div class="search-wrap">
            <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);pointer-events:none;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" id="searchInput" placeholder="Search for games..." autocomplete="off" style="padding-left:38px;">
            <div class="search-dropdown" id="searchDropdown"></div>
        </div>
    </div>

    <!-- Links -->
    <div class="navbar-links" id="navLinks">

        <!-- Search inside mobile menu -->
        <div class="navbar-mobile-search">
            <input type="text" id="searchInputMobile" placeholder="Search for games..." autocomplete="off">
            <div class="search-dropdown" id="searchDropdownMobile"></div>
        </div>

        <a href="about.php">About Us</a>

        <select id="currencySelector" class="currency-select">
            <option value="USD">USD ($)</option>
            <option value="SAR">SAR (ريال)</option>
        </select>

        <?php if ($user_role === 'admin'): ?>
            <a href="admin.php">Admin Dashboard</a>
        <?php endif; ?>

        <?php if ($user_role === 'business'): ?>
            <a href="business-dashboard.php">Your Business</a>
        <?php endif; ?>

        <a href="business.php">Business Service</a>

        <?php if ($is_logged_in): ?>
            <div class="profile-dropdown" id="profileDropdown">
                <button class="profile-btn" onclick="toggleProfile(event)" type="button">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:2px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Profile ▼
                </button>
                <div class="dropdown-content" id="profileMenu">
                    <a href="orders.php" onclick="closeNav()">My Orders</a>
                    <a href="settings.php" onclick="closeNav()">Settings</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        <?php else: ?>
            <a href="auth.php">Login</a>
        <?php endif; ?>

        <a href="cart.php" class="cart-link" onclick="closeNav()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px;"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
            Cart
            <span class="price-display" data-usd="<?= $cart_total ?>" style="font-weight:bold; margin-left:4px;">$<?= number_format($cart_total, 2) ?></span>
            <span class="cart-badge"><?= $cart_count ?></span>
        </a>

    </div>

    <!-- Hamburger (mobile only) - placed last so it renders above the menu drawer -->
    <button class="navbar-hamburger" id="navHamburger" onclick="toggleNav()" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>

</nav>

<script src="js/navbar.js?v=2026.05.17.v2"></script>
<script>
// ── Hamburger toggle ──────────────────────────
function toggleNav() {
    const links = document.getElementById('navLinks');
    const btn   = document.getElementById('navHamburger');
    links.classList.toggle('open');
    btn.classList.toggle('open');
    document.body.style.overflow = links.classList.contains('open') ? 'hidden' : '';
}

function closeNav() {
    document.getElementById('navLinks').classList.remove('open');
    document.getElementById('navHamburger').classList.remove('open');
    document.body.style.overflow = '';
}

// ── Profile dropdown ──────────────────────────
// Desktop: CSS hover handles it
// Mobile: click toggle
function toggleProfile(e) {
    if (window.innerWidth > 820) return; // let CSS handle on desktop
    e.stopPropagation();
    const dropdown = document.getElementById('profileDropdown');
    dropdown.classList.toggle('open');
}
document.addEventListener('click', (e) => {
    if (window.innerWidth <= 820) {
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown && !dropdown.contains(e.target)) {
            dropdown.classList.remove('open');
        }
    }
});

// ── Close on ESC ──
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeNav(); });

// ── Close when clicking outside the menu ──
document.addEventListener('click', function(e) {
    const links  = document.getElementById('navLinks');
    const btn    = document.getElementById('navHamburger');
    if (!links || !btn) return;
    if (links.classList.contains('open') &&
        !links.contains(e.target) &&
        !btn.contains(e.target)) {
        closeNav();
    }
});
</script>
