<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// 1. Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'php/db_connect.php';

// ------------------------------------------------------------------
// 2. HANDLE ALL FORM SUBMISSIONS (Add, Update, Delete)
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_SESSION['user_id'];

    // Action: DELETE ITEM
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['cart_id'])) {
        $stmt_delete = $pdo->prepare("DELETE FROM Cart WHERE id = ? AND user_id = ?");
        $stmt_delete->execute([(int)$_POST['cart_id'], $user_id]);
        header("Location: cart.php");
        exit();
    }

    // Action: UPDATE QUANTITY (+ / -)
    if (isset($_POST['action']) && $_POST['action'] === 'update_qty' && isset($_POST['cart_id'], $_POST['quantity'])) {
        $cart_id = (int)$_POST['cart_id'];
        $requested_qty = max(1, (int)$_POST['quantity']);

        // First, get the game_id for this cart item
        $stmt_game = $pdo->prepare("SELECT game_id FROM Cart WHERE id = ? AND user_id = ?");
        $stmt_game->execute([$cart_id, $user_id]);
        $cart_row = $stmt_game->fetch();

        if ($cart_row) {
            $game_id = $cart_row['game_id'];

            // Check actual stock in the database
            $stmt_stock = $pdo->prepare("SELECT COUNT(*) FROM Game_Keys WHERE game_id = ? AND is_sold = 0");
            $stmt_stock->execute([$game_id]);
            $stock = (int)$stmt_stock->fetchColumn();

            // Cap the quantity at available stock
            $final_qty = min($requested_qty, $stock);

            $stmt_update = $pdo->prepare("UPDATE Cart SET quantity = ? WHERE id = ?");
            $stmt_update->execute([$final_qty, $cart_id]);
        }
        header("Location: cart.php");
        exit();
    }

    // Action: ADD TO CART (From product.php)
    if (isset($_POST['game_id'], $_POST['quantity']) && (!isset($_POST['action']) || in_array($_POST['action'], ['add_cart', 'buy_now']))) {
        $game_id = (int)$_POST['game_id'];
        $requested_qty = max(1, (int)$_POST['quantity']);

        // Check actual stock in the database
        $stmt_stock = $pdo->prepare("SELECT COUNT(*) FROM Game_Keys WHERE game_id = ? AND is_sold = 0");
        $stmt_stock->execute([$game_id]);
        $stock = (int)$stmt_stock->fetchColumn();

        $stmt_check = $pdo->prepare("SELECT id, quantity FROM Cart WHERE user_id = ? AND game_id = ?");
        $stmt_check->execute([$user_id, $game_id]);
        $existing_item = $stmt_check->fetch();

        if ($existing_item) {
            // Combine existing qty with requested qty, capped at max stock
            $new_qty = min($existing_item['quantity'] + $requested_qty, $stock);
            $stmt_update = $pdo->prepare("UPDATE Cart SET quantity = ? WHERE id = ?");
            $stmt_update->execute([$new_qty, $existing_item['id']]);
        } else {
            // Cap initial request at max stock
            $final_qty = min($requested_qty, $stock);
            if ($final_qty > 0) { // Only insert if we actually have stock
                $stmt_insert = $pdo->prepare("INSERT INTO Cart (user_id, game_id, quantity) VALUES (?, ?, ?)");
                $stmt_insert->execute([$user_id, $game_id, $final_qty]);
            }
        }

        if (isset($_POST['action']) && $_POST['action'] === 'buy_now') {
            header("Location: orders.php"); 
            exit();
        } else {
            header("Location: cart.php");
            exit();
        }
    }
}
// ------------------------------------------------------------------

// 3. Fetch current cart items for display AND check stock count
$stmt = $pdo->prepare("
    SELECT Cart.id AS cart_id, Cart.quantity,
           Games.id AS game_id, Games.name, Games.price, Games.platform,
           Game_Images.filename AS cover_image,
           (SELECT COUNT(*) FROM Game_Keys k WHERE k.game_id = Games.id AND k.is_sold = 0) AS stock_count
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

        <?php include 'navbar.php'; ?>

        <div class="page-wrapper">
            <h1 class="page-title">Shopping Cart</h1>

            <div class="cart-layout">

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
                                                        <img src="<?= htmlspecialchars($item['cover_image'] ?? '') ?>"
                                                             alt="<?= htmlspecialchars($item['name']) ?>">
                                                    </div>
                                                    <div>
                                                        <div class="game-thumb-name"><?= htmlspecialchars($item['name']) ?></div>
                                                        <div class="game-thumb-sub">Steam · Global</div>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <!-- Added span for Currency Script -->
                                            <td>
                                                <span class="price-display" data-usd="<?= $item['price'] ?>">
                                                    $<?= number_format($item['price'], 2) ?>
                                                </span>
                                            </td>
                                            
                                            <td>
                                                <!-- Wrapped Quantity in a form that auto-submits on click -->
                                                <form action="cart.php" method="POST" style="margin:0;">
                                                    <input type="hidden" name="action" value="update_qty">
                                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                                    <div class="qty-wrap">
                                                        <button type="button" class="qty-btn" onclick="this.nextElementSibling.stepDown(); this.form.submit();">−</button>
                                                        <!-- Added max attribute dynamically to prevent frontend overallocation -->
                                                        <input type="number" name="quantity" class="qty-input" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock_count'] ?>" onchange="this.form.submit();">
                                                        <button type="button" class="qty-btn" onclick="this.previousElementSibling.stepUp(); this.form.submit();">+</button>
                                                    </div>
                                                </form>
                                            </td>
                                            
                                            <td class="total-price">
                                                <!-- Added span for Currency Script -->
                                                <span class="price-display" data-usd="<?= $item['price'] * $item['quantity'] ?>">
                                                    $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                                </span>
                                            </td>
                                            
                                            <td>
                                                <!-- Wrapped Delete in a form -->
                                                <form action="cart.php" method="POST" style="margin:0;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                                    <button type="submit" class="btn-remove" style="cursor: pointer;">Delete</button>
                                                </form>
                                            </td>
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

                <div class="summary-box">
                    <div class="summary-title">Order Summary</div>
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <!-- Added span for Currency Script -->
                        <span class="price-display" data-usd="<?= $subtotal ?>">$<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Tax:</span>
                        <span class="price-display" data-usd="0">0.00</span>
                    </div>
                    <hr class="summary-divider">
                    <div class="summary-total">
                        <span>Total:</span>
                        <!-- Added span for Currency Script -->
                        <span class="summary-total-price price-display" data-usd="<?= $subtotal ?>">$<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <a href="orders.php" class="checkout-btn">Proceed to Payment ⚡</a>
                    <p class="checkout-note">🔒 Digital keys delivered instantly</p>
                </div>

            </div>
        </div>

        <div class="footer">© 2026 GameHub Online Store. All rights reserved.</div>

    </body>
</html>
