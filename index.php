<?php
session_start();

require_once 'php/db_connect.php';


$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'guest';
$is_logged_in = isset($_SESSION['user_id']);

$current_category = isset($_GET['category']) ? trim($_GET['category']) : 'All Games';

$sort = $_GET['sort'] ?? '';
$random_order_by = match($sort) {
    'price_asc'  => 'ORDER BY g.price ASC',
    'price_desc' => 'ORDER BY g.price DESC',
    'name'       => 'ORDER BY g.name ASC',
    default      => 'ORDER BY RAND()',
};

// $random_order_by = "ORDER BY RAND()"; // NOTE: Can be slow on large tables; centralized for easier replacement.

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

foreach ($featured_games as &$g) {
    $g['price'] = (float)($g['price'] ?? 0);
}
unset($g);


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

$grid_query .= " $random_order_by"; // Shuffles the entire grid


$stmt_games = $pdo->prepare($grid_query);

if ($current_category !== 'All Games') {
    $stmt_games->bindValue(':category', '%' . $current_category . '%');
}

$stmt_games->execute();
$games = $stmt_games->fetchAll();

foreach ($games as &$g) {
    $g['price'] = (float)($g['price'] ?? 0);
}
unset($g);

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
<style>
select.sort-select {
    appearance: none !important;
    -webkit-appearance: none !important;
    background-color: transparent !important;
    border: 2px solid #e0e0e0 !important;
    padding: 8px 32px 8px 18px !important;
    border-radius: 20px !important;
    font-size: 13px !important;
    font-weight: bold !important;
    color: #555 !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
    background-size: 14px !important;
}
</style>
<body>

    <?php include 'navbar.php'; ?>

    <!-- ========================================== -->
    <!-- GUEST SUCCESS POP-OUT (Triggers after pay) -->
    <!-- ========================================== -->
    <?php if (isset($_SESSION['guest_success'])): ?>
        <div id="guestPopup" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.7); z-index: 9999; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(3px);">
            <div style="background: white; padding: 30px; border-radius: 12px; text-align: center; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                <div style="font-size: 3rem; margin-bottom: 10px;">✉️</div>
                <h2 style="margin-top: 0; color: #0f172a; font-size: 1.5rem;">Order Confirmed!</h2>
                <p style="color: #475569; font-size: 1.1rem; line-height: 1.5; margin-bottom: 20px;">
                    <?= htmlspecialchars($_SESSION['guest_success']) ?>
                </p>
                <button onclick="document.getElementById('guestPopup').style.display='none'" 
                        style="width: 100%; padding: 12px; background: #8b5cf6; color: white; border: none; border-radius: 6px; font-weight: bold; font-size: 1rem; cursor: pointer;">
                    Continue Shopping
                </button>
            </div>
        </div>
        <?php unset($_SESSION['guest_success']); // Clear it so it only shows once ?>
    <?php endif; ?>
    <!-- ========================================== -->

    <div class="hero-carousel">
        <button class="carousel-btn prev-btn" onclick="moveSlide(-1)">❮</button>
        <button class="carousel-btn next-btn" onclick="moveSlide(1)">❯</button>

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
            <select onchange="window.location='index.php?sort='+this.value+'&category=<?php echo urlencode($current_category); ?>'" class="sort-select">
                <option value="rating"      <?= ($_GET['sort'] ?? '') === 'rating'     ? 'selected' : '' ?>>Top Rated</option>
                <option value="price_asc"   <?= ($_GET['sort'] ?? '') === 'price_asc'  ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_desc"  <?= ($_GET['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                <option value="name"        <?= ($_GET['sort'] ?? '') === 'name'       ? 'selected' : '' ?>>Name: A–Z</option>
            </select>
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

    <script src="js/index.js"></script>

</body>
</html>
