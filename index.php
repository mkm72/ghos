<?php
session_start();
require_once 'php/db_connect.php';

$user_role = $_SESSION['user_role'] ?? 'guest';
$is_logged_in = isset($_SESSION['user_id']);

$current_category = isset($_GET['category']) ? trim($_GET['category']) : 'All Games';
$current_sort = $_GET['sort'] ?? 'rating';

$order_by = match($current_sort) {
    'price_asc'  => 'ORDER BY price ASC',
    'price_desc' => 'ORDER BY price DESC',
    'name'       => 'ORDER BY name ASC',
    'rating'     => 'ORDER BY rating DESC',
    default      => 'ORDER BY id DESC',
};

$stmt_featured = $pdo->prepare("
    SELECT MIN(g.id) AS id, g.name, MIN(g.price) AS price, MAX(i.filename) AS cover_image, MAX(g.rating) AS rating
    FROM Games g
    JOIN Game_Images i ON g.id = i.game_id AND i.is_cover = 1
    GROUP BY g.name
    ORDER BY RAND()
    LIMIT 3
");
$stmt_featured->execute();
$featured_games = $stmt_featured->fetchAll();

foreach ($featured_games as &$g) {
    $g['price'] = (float)($g['price'] ?? 0);
}
unset($g);

$categories = ['Action', 'RPG', 'Shooter', 'Adventure', 'Strategy', 'Indie', 'Platformer'];

$grid_query = "
    SELECT t.* FROM (
        SELECT 
            g.id, 
            g.name, 
            g.price, 
            g.platform, 
            g.genres,
            g.rating,
            i.filename AS cover_image,
            (SELECT COUNT(*) FROM Game_Keys k JOIN Games g2 ON k.game_id = g2.id WHERE g2.name = g.name AND k.is_sold = 0) AS stock_count,
            ROW_NUMBER() OVER(PARTITION BY g.name ORDER BY g.price ASC, g.id ASC) as rn
        FROM Games g
        LEFT JOIN Game_Images i ON g.id = i.game_id AND i.is_cover = 1
        " . ($current_category !== 'All Games' ? "WHERE g.genres LIKE :category" : "") . "
    ) t WHERE t.rn = 1
    $order_by
";

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

$bg_colors = ['bg-purple', 'bg-green', 'bg-dark', 'bg-blue', 'bg-red', 'bg-navy', 'bg-black', 'bg-forest'];

$sort_options = [
    'rating'     => 'Top Rated',
    'price_asc'  => 'Price: Low to High',
    'price_desc' => 'Price: High to Low',
    'name'       => 'Name: A–Z',
];
$current_sort_label = $sort_options[$current_sort] ?? 'Top Rated';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameHub Online Store - Home</title>
    <link rel="icon" type="image/png" href="images/logo/logo2.png">
    <link rel="stylesheet" href="css/navbar.css?v=2026.05.17.v2">
    <link rel="stylesheet" href="css/index.css?v=2026.05.15.v2">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <?php if (isset($_SESSION['guest_success'])): ?>
        <div id="guestPopup" class="guest-popup-overlay">
            <div class="guest-popup-card">
                <h2 class="guest-popup-title">Order Confirmed!</h2>
                <p class="guest-popup-text">
                    <?= htmlspecialchars($_SESSION['guest_success']) ?>
                </p>
                <button onclick="document.getElementById('guestPopup').style.display='none'" class="guest-popup-btn">
                    Continue Shopping
                </button>
            </div>
        </div>
        <?php unset($_SESSION['guest_success']); ?>
    <?php endif; ?>

    <div class="hero-carousel">
        <button class="carousel-btn prev-btn" onclick="moveSlide(-1)">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>
        <button class="carousel-btn next-btn" onclick="moveSlide(1)">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>

        <div class="hero-slides-container" id="carouselWrapper">
            <?php foreach ($featured_games as $index => $hero): ?>
            <div class="hero-slide <?php echo $index === 0 ? 'active' : ''; ?>" style="background-image: linear-gradient(rgba(26, 26, 46, 0.7), rgba(26, 26, 46, 0.9)), url('<?php echo htmlspecialchars(ltrim($hero['cover_image'], '/')); ?>');">
                <div class="featured-product-inner">
                    <div class="featured-product-badge">
                        <?php if ($index !== 0): ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 2px;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        <?php endif; ?>
                        <?php echo $index === 0 ? 'Hot Deal' : (number_format($hero['rating'], 1) . ' Rating'); ?>
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
        <div class="section-header" style="display: flex !important; justify-content: space-between !important; align-items: center !important; flex-wrap: nowrap !important;">
            <h2 class="section-title" style="margin-bottom: 0 !important;">Browse Games</h2>

            <div class="custom-select-wrapper" id="sortDropdown">
                <div class="custom-select-trigger" onclick="toggleSortDropdown(event)">
                    <span id="sort-label"><?php echo htmlspecialchars($current_sort_label); ?></span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                         stroke="#555555" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
                <div class="custom-select-options">
                    <?php foreach ($sort_options as $value => $label): ?>
                        <div class="custom-option <?php echo $current_sort === $value ? 'selected' : ''; ?>"
                             onclick="applySort('<?php echo $value; ?>')">
                            <?php if ($value === 'rating'): ?>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 4px; color: #f59e0b;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($label); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
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
                <div class="no-games-found">
                    No games found in the "<?php echo htmlspecialchars($current_category); ?>" category.
                </div>
            <?php 
            else:
                $color_index = 0;
                foreach ($games as $index => $game): 
                    $in_stock = $game['stock_count'] > 0;
                    $current_bg = $bg_colors[$color_index % count($bg_colors)];
                    $color_index++;
                    $image_path = ltrim($game['cover_image'] ?? '', '/');
                    $hidden_class = $index >= 16 ? 'hidden-game' : '';
            ?>
            <a href="product.php?id=<?php echo $game['id']; ?>" class="game-card <?php echo $hidden_class; ?> reveal-animation">
                <div class="game-image <?php echo $current_bg; ?>">
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
                    <div class="game-stars" style="color: #f59e0b; display: flex; gap: 2px; margin-bottom: 6px; align-items: center;">
                        <div style="display: flex; gap: 2px;">
                            <?php 
                            $rating = (float)($game['rating'] ?? 0);
                            for($i=1; $i<=5; $i++): 
                                $fill = 'none';
                                if ($rating >= $i) $fill = 'currentColor';
                                elseif ($rating > $i-1) $fill = 'url(#halfGrad)'; // Simplified for now, just full stars
                            ?>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="<?php echo $rating >= $i ? 'currentColor' : '#ccc'; ?>"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <?php endfor; ?>
                        </div>
                        <span style="font-size: 10px; color: #888; margin-left: 4px; font-weight: bold;"><?php echo number_format($rating, 1); ?></span>
                    </div>
                    <div class="game-genre">
                        <?php 
                            $genres_array = explode(',', $game['genres']);
                            echo htmlspecialchars(trim($genres_array[0])); 
                        ?>
                    </div>
                    <div class="game-platform"><?php echo htmlspecialchars($game['platform']); ?></div>
                    <div class="game-footer">
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
        <div class="load-more-container" style="display: flex !important; justify-content: center !important; width: 100% !important; margin: 40px 0 !important;">
            <button id="loadMoreBtn" onclick="loadMoreGames()" class="btn-white">
                Load More Games
            </button>
        </div>
        <?php endif; ?>

    </div>

    <div class="footer">
        © 2026 GameHub Online Store. All rights reserved.
    </div>

    <script src="js/index.js?v=2026.05.15.v2"></script>

    <?php if (isset($_COOKIE['past_purchases'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const pastIds = <?= json_encode(explode(',', $_COOKIE['past_purchases'])) ?>;
            if (pastIds.length > 0) {
                const response = await fetch('php/search.php?ids=' + pastIds.join(','));
                const games = await response.json();
                if (games && games.length > 0) {
                    const section = document.createElement('div');
                    section.className = 'games-section';
                    section.style.marginTop = '40px';
                    section.innerHTML = `
                        <h2 class="section-title">Your Recent Purchases</h2>
                        <div class="games-grid">
                            ${games.map(g => `
                                <a href="product.php?id=${g.id}" class="game-card reveal-animation">
                                    <div class="game-image bg-dark">
                                        <img src="${g.cover_image.replace(/^\//, '')}" alt="${g.name}">
                                    </div>
                                    <div class="game-info">
                                        <div class="game-name">${g.name}</div>
                                        <div class="game-footer">
                                            <span class="game-price">$${parseFloat(g.price).toFixed(2)}</span>
                                            <span class="btn-dark">Buy Again</span>
                                        </div>
                                    </div>
                                </a>
                            `).join('')}
                        </div>
                    `;
                    // Insert before the footer
                    const footer = document.querySelector('.footer');
                    footer.parentNode.insertBefore(section, footer);
                }
            }
        });
    </script>
    <?php endif; ?>

    <script>
        const currentCategory = <?php echo json_encode($current_category); ?>;

        function toggleSortDropdown(e) {
            e.stopPropagation();
            document.getElementById('sortDropdown').classList.toggle('open');
        }

        function applySort(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', value);
            window.location.href = url.toString();
        }

        document.addEventListener('click', function () {
            const dropdown = document.getElementById('sortDropdown');
            if (dropdown) dropdown.classList.remove('open');
        });
    </script>
</body>
</html>
