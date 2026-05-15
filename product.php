<?php
session_start();

require_once 'php/db_connect.php';

$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'guest';
$is_logged_in = isset($_SESSION['user_id']);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$game_id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    SELECT g.*, 
           (SELECT COUNT(*) FROM Game_Keys k WHERE k.game_id = g.id AND k.is_sold = 0) AS stock_count,
           ba.business_name AS seller_name
    FROM Games g
    LEFT JOIN Business_Applications ba ON g.seller_id = ba.user_id AND ba.status = 'approved'
    WHERE g.id = :id
");
$stmt->execute(['id' => $game_id]);
$game = $stmt->fetch();
if (!$game) {
    die("<h2 style='text-align:center; padding: 50px; font-family: Arial;'>Game not found. <a href='index.php'>Return to store</a></h2>");
}

$stmt_img = $pdo->prepare("SELECT filename, is_cover FROM Game_Images WHERE game_id = :id ORDER BY is_cover DESC");
$stmt_img->execute(['id' => $game_id]);
$images = $stmt_img->fetchAll();

$main_image = !empty($images) ? ltrim($images[0]['filename'], '/') : '';
$in_stock = $game['stock_count'] > 0;
$old_price = $game['price'] * 1.20; 

$platforms = array_filter(array_map('trim', explode(',', $game['platform'])));
$genres = array_filter(array_map('trim', explode(',', $game['genres'])));

$stmt_others = $pdo->prepare("
    SELECT g.id, g.price, COALESCE(ba.business_name, 'GameHub Official') AS seller_name
    FROM Games g
    LEFT JOIN Business_Applications ba ON g.seller_id = ba.user_id
    WHERE g.name = :name AND g.id != :id
");
$stmt_others->execute(['name' => $game['name'], 'id' => $game_id]);
$other_sellers = $stmt_others->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game['name']); ?> — GameHub Online Store</title>
    <link rel="stylesheet" href="css/navbar.css?v=2026.05.15">
    <link rel="stylesheet" href="css/product.css?v=2026.05.15">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="breadcrumb">
        <a href="index.php">Back to Store</a>
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

            <!-- Star rating (teammate's addition) -->
            <div class="product-stars" style="color: #f59e0b; display: flex; gap: 4px; margin-top: -10px;">
                <?php for($i=0; $i<5; $i++): ?>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                <?php endfor; ?>
                <span style="color: #888; font-size: 14px; margin-left: 8px; font-weight: 500;">(4.9 Rating)</span>
            </div>

            <!-- Tags with show more (my addition) -->
            <div class="product-tags" id="tagsContainer">
                <?php 
                $all_tags = [];
                foreach ($platforms as $p) $all_tags[] = ['label' => $p, 'type' => 'platform'];
                foreach ($genres as $g)    $all_tags[] = ['label' => $g,  'type' => 'genre'];
                
                foreach ($all_tags as $i => $tag): 
                    $style = $tag['type'] === 'genre' 
                        ? 'background-color:#e0e7ff;border-color:#c7d2fe;color:#3730a3;' 
                        : '';
                    $hidden = $i >= 4 ? 'class="product-tag extra-tag" style="display:none;' . $style . '"' 
                                      : 'class="product-tag" style="' . $style . '"';
                ?>
                    <span <?= $hidden ?>><?= htmlspecialchars($tag['label']) ?></span>
                <?php endforeach; ?>

                <?php if (count($all_tags) > 4): ?>
                    <button class="tag-toggle" onclick="
                        document.querySelectorAll('.extra-tag').forEach(t => t.style.display = t.style.display === 'none' ? '' : 'none');
                        this.textContent = this.textContent === '+ more' ? '− less' : '+ more';
                    ">+ more</button>
                <?php endif; ?>
            </div>

            <div class="product-price">
                <span class="price-display" data-usd="<?php echo $game['price']; ?>">$<?php echo number_format($game['price'], 2); ?></span>
                <s><span class="price-display" style="font-size: 0.6em; color: #888;" data-usd="<?php echo $old_price; ?>">$<?php echo number_format($old_price, 2); ?></span></s>
                <span class="discount">-20%</span>
            </div>
            <?php if ($in_stock): ?>
                <span class="stock-status stock-available">Available — <?php echo $game['stock_count']; ?> in stock</span>
            <?php else: ?>
                <span class="stock-status stock-out">Not Available</span>
            <?php endif; ?>

            <form action="cart.php" method="POST">
                <input type="hidden" name="game_id" value="<?php echo $game['id']; ?>">
                <div>
                    <div class="quantity-label">Quantity</div>
                    <div class="quantity-control">
                        <button class="qty-btn" type="button" onclick="document.getElementById('qtyInput').stepDown()" <?php echo !$in_stock ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>-</button>
                        <input type="number" name="quantity" id="qtyInput" class="qty-input" value="1" min="1" max="<?php echo $game['stock_count'] > 0 ? $game['stock_count'] : 1; ?>" <?php echo !$in_stock ? 'disabled' : ''; ?> oninput="if(parseInt(this.value) > parseInt(this.max)) this.value = this.max;">
                        <button class="qty-btn" type="button" onclick="document.getElementById('qtyInput').stepUp()" <?php echo !$in_stock ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>+</button>
                    </div>
                </div>
                <div class="delivery-box">
                    <div class="delivery-title">Instant Digital Delivery</div>
                    <div class="delivery-text">Receive your game key immediately after purchase</div>
                </div>
                <div class="action-buttons">
                    <?php if ($in_stock): ?>
                        <button type="submit" name="action" value="buy_now" class="btn-blue" style="border: none; cursor: pointer; font-family: inherit; font-size: inherit;">Buy Now</button>
                        <button type="submit" name="action" value="add_cart" class="btn-white" style="border: 1px solid #ccc; cursor: pointer; font-family: inherit; font-size: inherit;">Add to Cart</button>
                    <?php else: ?>
                        <a href="#" class="btn-gray" style="grid-column: 1 / -1; text-align: center;">Currently Unavailable</a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="trust-row">
                <div class="trust-item">Sold by: <?php echo htmlspecialchars($game['seller_name'] ?? 'GameHub Official'); ?></div>
                <div class="trust-item">Official Keys</div>
                <div class="trust-item">Secure Payment</div>
            </div>

            <?php if (!empty($other_sellers)): ?>
            <div style="margin-top:20px; padding:15px; border:1px solid #e0e0e0; border-radius:8px;">
                <h3 style="font-size:16px; margin-bottom:12px;">Other Sellers</h3>
                <ul style="list-style:none; padding:0; margin:0;">
                    <?php foreach ($other_sellers as $os): ?>
                    <li style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #f0f0f0;">
                        <span style="font-weight:bold;"><?php echo htmlspecialchars($os['seller_name']); ?></span>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <span class="price-display" data-usd="<?php echo $os['price']; ?>" style="color:#2563eb; font-weight:bold;">$<?php echo number_format($os['price'], 2); ?></span>
                            <a href="product.php?id=<?php echo $os['id']; ?>" class="btn-dark" style="padding:6px 12px; font-size:12px;">View</a>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div style="margin-top: 20px;">
                <button type="button" class="btn-white" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; border-style: dashed; border-color: #3b82f6; color: #3b82f6;" onclick="document.getElementById('helpModal').style.display='flex'">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    How to Order?
                </button>
            </div>

            <hr class="divider">
            <div>
                <div class="description-title">Description</div>
                <p class="description-text" style="white-space: pre-wrap; margin-top: 10px;"><?php echo htmlspecialchars($game['description'] ? $game['description'] : 'No description available for this game.'); ?></p>
            </div>
            <div>
                <div class="description-title">System Requirements</div>
                <p class="description-text">OS: Windows 10/11 · Processor: Intel Core i5-8600K or equivalent · Memory: 8 GB RAM · Graphics: DirectX 11 Compatible GPU</p>
            </div>
        </div>
    </div>
    <div class="footer">© 2026 GameHub Online Store. All rights reserved.</div>

    <!-- Help Modal -->
    <div id="helpModal" class="modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div style="background: white; width: 90%; max-width: 450px; border-radius: 16px; padding: 30px; position: relative; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
            <span style="position: absolute; top: 15px; right: 20px; font-size: 28px; cursor: pointer; color: #888;" onclick="document.getElementById('helpModal').style.display='none'">&times;</span>
            <h2 style="margin-bottom: 20px; color: #1a1a1a; display: flex; align-items: center; gap: 10px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                How to Purchase
            </h2>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; gap: 15px;">
                    <div style="background: #eff6ff; color: #3b82f6; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">1</div>
                    <div>
                        <div style="font-weight: bold; color: #333;">Add to Cart</div>
                        <div style="font-size: 13px; color: #666;">Choose your quantity and click "Add to Cart" or "Buy Now".</div>
                    </div>
                </div>
                <div style="display: flex; gap: 15px;">
                    <div style="background: #eff6ff; color: #3b82f6; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">2</div>
                    <div>
                        <div style="font-weight: bold; color: #333;">Secure Checkout</div>
                        <div style="font-size: 13px; color: #666;">Go to your cart and proceed to checkout using your preferred payment method.</div>
                    </div>
                </div>
                <div style="display: flex; gap: 15px;">
                    <div style="background: #eff6ff; color: #3b82f6; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">3</div>
                    <div>
                        <div style="font-weight: bold; color: #333;">Receive Your Key</div>
                        <div style="font-size: 13px; color: #666;">Once the payment is confirmed, your game key will be sent to your email instantly.</div>
                    </div>
                </div>
            </div>
            <button class="btn-blue" style="width: 100%; margin-top: 25px;" onclick="document.getElementById('helpModal').style.display='none'">Got it!</button>
        </div>
    </div>

    <script>
        window.addEventListener('click', (e) => {
            const modal = document.getElementById('helpModal');
            if (e.target === modal) modal.style.display = 'none';
        });
    </script>
</body>
</html>