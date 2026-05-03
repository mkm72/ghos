<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'php/db_connect.php';

// --- GUEST CHECKOUT LOGIC ---
$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();

// Dynamic query builder: use user_id if logged in, otherwise use session_id
$where_clause = $user_id ? "user_id = :identifier" : "session_id = :identifier";
$identifier   = $user_id ?: $session_id;
// ----------------------------

// ------------------------------------------------------------------
// 2. HANDLE ALL FORM SUBMISSIONS (Add, Update, Delete)
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Action: DELETE ITEM
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['cart_id'])) {
        $stmt_delete = $pdo->prepare("DELETE FROM Cart WHERE id = :cart_id AND $where_clause");
        $stmt_delete->execute(['cart_id' => (int)$_POST['cart_id'], 'identifier' => $identifier]);
        
        $_SESSION['success'] = "Item successfully removed from your cart.";
        header("Location: cart.php");
        exit();
    }

    // Action: UPDATE QUANTITY (+ / -)
    if (isset($_POST['action']) && $_POST['action'] === 'update_qty' && isset($_POST['cart_id'], $_POST['quantity'])) {
        $cart_id = (int)$_POST['cart_id'];
        $requested_qty = (int)$_POST['quantity'];

        $stmt_game = $pdo->prepare("SELECT game_id, quantity FROM Cart WHERE id = :cart_id AND $where_clause");
        $stmt_game->execute(['cart_id' => $cart_id, 'identifier' => $identifier]);
        $cart_row = $stmt_game->fetch();

        if ($cart_row) {
            $game_id = $cart_row['game_id'];
            $current_qty = (int)$cart_row['quantity'];

            $stmt_stock = $pdo->prepare("SELECT COUNT(*) FROM Game_Keys WHERE game_id = ? AND is_sold = 0");
            $stmt_stock->execute([$game_id]);
            $stock = (int)$stmt_stock->fetchColumn();

            $key_text = ($stock === 1) ? "1 key" : "$stock keys";

            if ($requested_qty > $stock) {
                $_SESSION['error'] = "Could not update. We only have $key_text currently available.";
                $final_qty = $stock;
            } elseif ($requested_qty < 1) {
                $_SESSION['error'] = "Minimum quantity is 1. Use the 'Delete' button to remove the item.";
                $final_qty = 1;
            } elseif ($requested_qty === $current_qty) {
                if ($current_qty === $stock) {
                    $_SESSION['error'] = "You have reached the maximum stock limit ($key_text).";
                } elseif ($current_qty === 1) {
                    $_SESSION['error'] = "Minimum quantity is 1. Use the 'Delete' button to remove the item.";
                }
                $final_qty = $current_qty; 
            } else {
                $_SESSION['success'] = "Cart quantity updated.";
                $final_qty = $requested_qty;
            }

            if ($final_qty !== $current_qty) {
                $stmt_update = $pdo->prepare("UPDATE Cart SET quantity = ? WHERE id = ?");
                $stmt_update->execute([$final_qty, $cart_id]);
            }
        }
        header("Location: cart.php");
        exit();
    }

    // Action: ADD TO CART (From product.php)
    if (isset($_POST['game_id'], $_POST['quantity']) && (!isset($_POST['action']) || in_array($_POST['action'], ['add_cart', 'buy_now']))) {
        $game_id = (int)$_POST['game_id'];
        $requested_qty = max(1, (int)$_POST['quantity']);

        $stmt_stock = $pdo->prepare("SELECT COUNT(*) FROM Game_Keys WHERE game_id = ? AND is_sold = 0");
        $stmt_stock->execute([$game_id]);
        $stock = (int)$stmt_stock->fetchColumn();

        $key_text = ($stock === 1) ? "1 key" : "$stock keys";

        $stmt_check = $pdo->prepare("SELECT id, quantity FROM Cart WHERE game_id = :game_id AND $where_clause");
        $stmt_check->execute(['game_id' => $game_id, 'identifier' => $identifier]);
        $existing_item = $stmt_check->fetch();

        if ($existing_item) {
            $total_requested = $existing_item['quantity'] + $requested_qty;
            
            if ($total_requested > $stock) {
                $_SESSION['error'] = "Cannot add more. We only have $key_text in stock.";
                $new_qty = $stock;
            } else {
                $_SESSION['success'] = "Game added to your cart!";
                $new_qty = $total_requested;
            }

            $stmt_update = $pdo->prepare("UPDATE Cart SET quantity = ? WHERE id = ?");
            $stmt_update->execute([$new_qty, $existing_item['id']]);
        } else {
            if ($requested_qty > $stock) {
                $_SESSION['error'] = "Only $key_text available. Added maximum stock to cart.";
                $final_qty = $stock;
            } else {
                $_SESSION['success'] = "Game added to your cart!";
                $final_qty = $requested_qty;
            }

            if ($final_qty > 0) { 
                if ($user_id) {
                    $stmt_insert = $pdo->prepare("INSERT INTO Cart (user_id, game_id, quantity) VALUES (?, ?, ?)");
                    $stmt_insert->execute([$user_id, $game_id, $final_qty]);
                } else {
                    $stmt_insert = $pdo->prepare("INSERT INTO Cart (session_id, game_id, quantity) VALUES (?, ?, ?)");
                    $stmt_insert->execute([$session_id, $game_id, $final_qty]);
                }
            } else {
                 $_SESSION['error'] = "Sorry, this game is currently out of stock.";
            }
        }

        if (isset($_POST['action']) && $_POST['action'] === 'buy_now' && !isset($_SESSION['error'])) {
            header("Location: checkout.php"); 
        } else {
            header("Location: cart.php");
        }
        exit();
    }
}
// ------------------------------------------------------------------

// 3. Fetch current cart items for display
$stmt = $pdo->prepare("
    SELECT Cart.id AS cart_id, Cart.quantity,
           Games.id AS game_id, Games.name, Games.price, Games.platform,
           Game_Images.filename AS cover_image,
           (SELECT COUNT(*) FROM Game_Keys k WHERE k.game_id = Games.id AND k.is_sold = 0) AS stock_count
    FROM Cart
    JOIN Games ON Cart.game_id = Games.id
    LEFT JOIN Game_Images ON Game_Images.game_id = Games.id AND Game_Images.is_cover = 1
    WHERE Cart.$where_clause
");
$stmt->execute(['identifier' => $identifier]);
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
            
            <?php if (isset($_SESSION['success'])): ?>
                <div style="background-color: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #10b981; font-weight: bold;">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div style="background-color: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ef4444; font-weight: bold;">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

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
                                            
                                            <td>
                                                <span class="price-display" data-usd="<?= $item['price'] ?>">
                                                    $<?= number_format($item['price'], 2) ?>
                                                </span>
                                            </td>
                                            
                                            <td>
                                                <form action="cart.php" method="POST" style="margin:0;">
                                                    <input type="hidden" name="action" value="update_qty">
                                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                                    <div class="qty-wrap">
                                                        <button type="button" class="qty-btn" onclick="this.nextElementSibling.stepDown(); this.form.submit();">−</button>
                                                        <input type="number" name="quantity" class="qty-input" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock_count'] ?>" onchange="this.form.submit();">
                                                        <button type="button" class="qty-btn" onclick="this.previousElementSibling.stepUp(); this.form.submit();">+</button>
                                                    </div>
                                                </form>
                                            </td>
                                            
                                            <td class="total-price">
                                                <span class="price-display" data-usd="<?= $item['price'] * $item['quantity'] ?>">
                                                    $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                                </span>
                                            </td>
                                            
                                            <td>
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
                        <span class="price-display" data-usd="<?= $subtotal ?>">$<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Tax:</span>
                        <span class="price-display" data-usd="0">0.00</span>
                    </div>
                    <hr class="summary-divider">
                    <div class="summary-total">
                        <span>Total:</span>
                        <span class="summary-total-price price-display" data-usd="<?= $subtotal ?>">$<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <a href="checkout.php" class="checkout-btn">Proceed to Payment ⚡</a>
                    <p class="checkout-note">🔒 Digital keys delivered instantly</p>
                </div>
            </div>
        </div>

        <div class="footer">© 2026 GameHub Online Store. All rights reserved.</div>

    </body>
</html>
