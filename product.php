<?php
session_start();

require_once 'php/db_connect.php';

// Determine the current user's role
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'guest';
$is_logged_in = isset($_SESSION['user_id']);

// 1. Get the Game ID from the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$game_id = (int)$_GET['id'];

// 2. Fetch the Game Details and Stock Count
$stmt = $pdo->prepare("
    SELECT g.*, 
           (SELECT COUNT(*) FROM Game_Keys k WHERE k.game_id = g.id AND k.is_sold = 0) AS stock_count
    FROM Games g
    WHERE g.id = :id
");
$stmt->execute(['id' => $game_id]);
$game = $stmt->fetch();

if (!$game) {
    die("<h2 style='text-align:center; padding: 50px; font-family: Arial;'>Game not found. <a href='index.php'>Return to store</a></h2>");
}

// 3. Fetch Images for this Game
$stmt_img = $pdo->prepare("
    SELECT filename, is_cover 
    FROM Game_Images 
    WHERE game_id = :id 
    ORDER BY is_cover DESC
");
$stmt_img->execute(['id' => $game_id]);
$images = $stmt_img->fetchAll();

$main_image = !empty($images) ? ltrim($images[0]['filename'], '/') : '';

// 4. Calculate some display variables
$in_stock = $game['stock_count'] > 0;
$old_price = $game['price'] * 1.20; 

$platforms = array_filter(array_map('trim', explode(',', $game['platform'])));
$genres = array_filter(array_map('trim', explode(',', $game['genres'])));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game['name']); ?> — GameHub Online Store</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/product.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="breadcrumb">
        <a href="index.php">← Back to Store</a>
    </div>


    <div class="product-layout">

        <div>
            <div class="product-main-image bg-dark">
                <?php if ($main_image): ?>
                    <img src="<?php echo htmlspecialchars($main_image); ?>" alt="<?php echo htmlspecialchars($game['name']); ?>">
                <?php else: ?>
                    <span style="color: white; font-size: 20px;">No Image</span>
                <?php endif; ?>
            </div>
            
            <div class="thumbnail-row">
                <?php foreach ($images as $img): ?>
                    <div class="thumbnail">
                        <img src="<?php echo htmlspecialchars(ltrim($img['filename'], '/')); ?>" alt="Thumbnail">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="product-details">

            <h1 class="product-title"><?php echo htmlspecialchars($game['name']); ?></h1>

            <div class="product-tags">
                <?php foreach ($platforms as $platform): ?>
                    <span class="product-tag"><?php echo htmlspecialchars($platform); ?></span>
                <?php endforeach; ?>
                <?php foreach ($genres as $genre): ?>
                    <span class="product-tag" style="background-color: #e0e7ff; border-color: #c7d2fe; color: #3730a3;"><?php echo htmlspecialchars($genre); ?></span>
                <?php endforeach; ?>
            </div>

            <div class="product-price">
                <span class="price-display" data-usd="<?php echo $game['price']; ?>">$<?php echo number_format($game['price'], 2); ?></span>
                
                <s><span class="price-display" style="font-size: 0.6em; color: #888;" data-usd="<?php echo $old_price; ?>">$<?php echo number_format($old_price, 2); ?></span></s>
                
                <span class="discount">−20%</span>
            </div>

            <?php if ($in_stock): ?>
                <span class="stock-status stock-available">✅ Available — <?php echo $game['stock_count']; ?> in stock</span>
            <?php else: ?>
                <span class="stock-status stock-out">Not Available</span>
            <?php endif; ?>

            <div>
                <div class="quantity-label">Quantity</div>
                <div class="quantity-control">
                    <button class="qty-btn" type="button" onclick="document.getElementById('qtyInput').stepDown()" <?php echo !$in_stock ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>−</button>
                    
                    <input type="number" id="qtyInput" class="qty-input" value="1" min="1" max="<?php echo $game['stock_count'] > 0 ? $game['stock_count'] : 1; ?>" <?php echo !$in_stock ? 'disabled' : ''; ?>>
                    
                    <button class="qty-btn" type="button" onclick="document.getElementById('qtyInput').stepUp()" <?php echo !$in_stock ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>+</button>
                </div>
            </div>

            <div class="delivery-box">
                <div class="delivery-icon">⚡</div>
                <div>
                    <div class="delivery-title">Instant Digital Delivery</div>
                    <div class="delivery-text">Receive your game key immediately after purchase</div>
                </div>
            </div>

            <div class="action-buttons">
                <?php if ($in_stock): ?>
                    <a href="cart.html?add=<?php echo $game['id']; ?>" class="btn-blue">🛒 Buy Now</a>
                    <a href="cart.html?add=<?php echo $game['id']; ?>" class="btn-white">+ Add to Cart</a>
                <?php else: ?>
                    <a href="#" class="btn-gray" style="grid-column: 1 / -1; text-align: center;">Currently Unavailable</a>
                <?php endif; ?>
            </div>

            <div class="trust-row">
                <div class="trust-item">✅ Verified Seller</div>
                <div class="trust-item">🔑 Official Keys</div>
                <div class="trust-item">🛡️ Secure Payment</div>
            </div>

            <hr class="divider">

            <div>
                <div class="description-title">Description</div>
                <p class="description-text" style="white-space: pre-wrap; margin-top: 10px;"><?php echo htmlspecialchars($game['description'] ? $game['description'] : 'No description available for this game.'); ?></p>
            </div>

            <div>
                <div class="description-title">System Requirements</div>
                <p class="description-text">
                    OS: Windows 10/11 · Processor: Intel Core i5-8600K or equivalent ·
                    Memory: 8 GB RAM · Graphics: DirectX 11 Compatible GPU
                </p>
            </div>

        </div>
    </div>

    <div class="footer">
        © 2026 GameHub Online Store. All rights reserved.
    </div>

</body>
</html>
