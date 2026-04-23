<?php
session_start();

require_once 'php/db_connect.php';

// --- MOCK LOGIN FOR TESTING ---
$_SESSION['user_id'] = 3; 
$_SESSION['role'] = 'customer'; 
// ------------------------------

$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
$is_logged_in = isset($_SESSION['user_id']);

$current_category = isset($_GET['category']) ? trim($_GET['category']) : 'All Games';


// 1. Fetch Multiple Featured Games for the Hero Carousel (Randomized)
$stmt_featured = $pdo->prepare("
    SELECT g.id, g.name, g.price, i.filename AS cover_image
    FROM Games g
    JOIN Game_Images i ON g.id = i.game_id AND i.is_cover = 1
    ORDER BY RAND() 
    LIMIT 3
");
$stmt_featured->execute();
$featured_games = $stmt_featured->fetchAll();


// 2. Fetch Popular Categories/Genres
$categories = ['Action', 'RPG', 'Shooter', 'Adventure', 'Strategy', 'Indie', 'Platformer'];


// 3. Fetch ALL Games for the Grid (No SQL Limit, we hide extras with CSS/JS)
$grid_query = "
    SELECT 
        g.id, 
        g.name, 
        g.price, 
        g.platform, 
        g.genres,
        i.filename AS cover_image,
        (SELECT COUNT(*) FROM Game_Keys k WHERE k.game_id = g.id AND k.is_sold = 0) AS stock_count
    FROM Games g
    LEFT JOIN Game_Images i ON g.id = i.game_id AND i.is_cover = 1
";

// If a specific category is selected, add a WHERE clause
if ($current_category !== 'All Games') {
    $grid_query .= " WHERE g.genres LIKE :category ";
}

$grid_query .= " ORDER BY RAND()"; // Shuffles the entire grid

$stmt_games = $pdo->prepare($grid_query);

if ($current_category !== 'All Games') {
    $stmt_games->bindValue(':category', '%' . $current_category . '%');
}

$stmt_games->execute();
$games = $stmt_games->fetchAll();

// Array of background colors
$bg_colors = ['bg-purple', 'bg-green', 'bg-dark', 'bg-blue', 'bg-red', 'bg-navy', 'bg-black', 'bg-forest'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameHub Online Store - Home</title>

    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

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

            <select id="currencySelector" class="currency-select" onchange="updateCurrency()">
                <option value="USD">USD ($)</option>
                <option value="SAR">SAR (ر.س)</option>
            </select>

            <?php if ($user_role === 'admin'): ?>
                <a href="admin.html">Admin dashboard</a>
            <?php endif; ?>

            <?php if ($user_role === 'business'): ?>
                <a href="business-dashboard.html">Your Business</a>
            <?php endif; ?>

            <a href="business.html">Business Service</a>

            <?php if ($is_logged_in): ?>
                <div class="profile-dropdown">
                    <button class="profile-btn">👤 Profile ▼</button>
                    <div class="dropdown-content">
                        <a href="settings.php">⚙️ Settings</a>
                        <a href="logout.php">🚪 Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.html">Login</a>
            <?php endif; ?>

            <a href="cart.html" class="cart-link">
                🛒 Cart
                <span class="cart-badge">3</span>
            </a>
        </div>

    </nav>


    <div class="hero-carousel">
        <button class="carousel-btn prev-btn" onclick="moveSlide(-1)">&#10094;</button>
        <button class="carousel-btn next-btn" onclick="moveSlide(1)">&#10095;</button>

        <div class="hero-slides-container" id="carouselWrapper">
            <?php foreach ($featured_games as $index => $hero): ?>
            <div class="hero-slide" style="background-image: linear-gradient(rgba(26, 26, 46, 0.7), rgba(26, 26, 46, 0.9)), url('<?php echo htmlspecialchars(ltrim($hero['cover_image'], '/')); ?>');">
                <div class="featured-product-inner">
                    <div class="featured-product-badge">
                        <?php echo $index === 0 ? '⚡ Hot Deal' : '🔥 Top Rated'; ?>
                    </div>
                    <h1><?php echo htmlspecialchars($hero['name']); ?></h1>
                    <p class="featured-product-promo">Epic adventures await. Buy now and play instantly!</p>
                    <div class="featured-product-price price-display" data-usd="<?php echo $hero['price']; ?>">
                        $<?php echo number_format($hero['price'], 2); ?>
                    </div>
                    <a href="product.php?id=<?php echo $hero['id']; ?>" class="btn-blue">Get Key</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>


    <div class="games-section">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="section-title">Browse Games</h2>
        </div>

        <div class="category-bar">
            <a href="index.php" class="category-pill <?php echo $current_category === 'All Games' ? 'active' : ''; ?>">All Games</a>
            
            <?php foreach ($categories as $cat): ?>
                <a href="index.php?category=<?php echo urlencode($cat); ?>" class="category-pill <?php echo $current_category === $cat ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="games-grid">
            <?php 
            if (count($games) === 0): ?>
                <div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: #888;">
                    No games found in the "<?php echo htmlspecialchars($current_category); ?>" category.
                </div>
            <?php 
            else:
                $color_index = 0;
                foreach ($games as $index => $game): 
                    $in_stock = $game['stock_count'] > 0;
                    $current_bg = $bg_colors[$color_index % count($bg_colors)];
                    $color_index++;
                    $image_path = ltrim($game['cover_image'], '/');
                    
                    // If the index is 16 or higher, it gets the hidden-game class for JS to reveal later
                    $hidden_class = $index >= 16 ? 'hidden-game' : '';
            ?>
            <a href="product.php?id=<?php echo $game['id']; ?>" class="game-card <?php echo $hidden_class; ?>">
                <div class="game-image <?php echo $current_bg; ?>" <?php echo !$in_stock ? 'style="position: relative;"' : ''; ?>>
                    
                    <?php if ($image_path): ?>
                        <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($game['name']); ?> Cover">
                    <?php else: ?>
                        <span style="color:white; font-size:14px;">No Image</span>
                    <?php endif; ?>
                    
                    <?php if (!$in_stock): ?>
                    <div class="out-of-stock-overlay">
                        <span class="out-of-stock-label">Out of Stock</span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="game-info">
                    <div class="game-name"><?php echo htmlspecialchars($game['name']); ?></div>
                    
                    <div class="game-genre">
                        <?php 
                            $genres_array = explode(',', $game['genres']);
                            echo htmlspecialchars(trim($genres_array[0])); 
                        ?>
                    </div>

                    <div class="game-platform"><?php echo htmlspecialchars($game['platform']); ?></div>
                    <div class="game-footer" style="margin-top: 10px;">
                        <span class="game-price price-display" data-usd="<?php echo $game['price']; ?>">$<?php echo number_format($game['price'], 2); ?></span>
                        
                        <?php if ($in_stock): ?>
                            <span class="btn-dark">View</span>
                        <?php else: ?>
                            <span class="btn-gray">Unavailable</span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <?php 
                endforeach; 
            endif;
            ?>
        </div>

        <?php if (count($games) > 16): ?>
        <div style="text-align: center; margin-top: 40px;">
            <button id="loadMoreBtn" onclick="loadMoreGames()" class="btn-white" style="padding: 12px 30px; font-size: 15px;">
                ↻ Load More Games
            </button>
        </div>
        <?php endif; ?>

    </div>

    <div class="footer">
        © 2026 GameHub Online Store. All rights reserved.
    </div>
<div id="contactModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>Contact Us</h2>
            <p><strong>Email:</strong> support@ghos.com</p>
            <p><strong>Location:</strong> iau, Saudi Arabia</p>
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1754.1487865674999!2d50.194878487615526!3d26.39430784899561!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e49ef811304efab%3A0xe664343a49ebbf2b!2sCollege%20of%20Computer%20Science%20and%20Information%20Technology!5e0!3m2!1sen!2ssa!4v1776965850652!5m2!1sen!2ssa" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>          </div>
        </div>
    </div>
    <script src="js/index.js"></script>

</body>
</html>
