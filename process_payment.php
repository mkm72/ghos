<?php
// php/process_payment.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth.php');
    exit;
}
require_once 'db_connect.php';

$user_id = (int)$_SESSION['user_id'];
$method  = $_POST['payment_method'] ?? 'card';

// ── Validate card fields ──────────────────────────────────────
if ($method === 'card') {
    $card_name   = trim($_POST['card_name']   ?? '');
    $card_number = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
    $card_expiry = preg_replace('/[\s\/]/', '', $_POST['card_expiry'] ?? '');
    $card_cvv    = trim($_POST['card_cvv']    ?? '');

    if (!$card_name) {
        $_SESSION['pay_error'] = 'Cardholder name is required.';
        header('Location: ../checkout.php');
        exit;
    }
    if (!preg_match('/^\d{16}$/', $card_number)) {
        $_SESSION['pay_error'] = 'Please enter a valid 16-digit card number.';
        header('Location: ../checkout.php');
        exit;
    }
    if (!preg_match('/^\d{4}$/', $card_expiry)) {
        $_SESSION['pay_error'] = 'Please enter a valid expiry date (MM/YY).';
        header('Location: ../checkout.php');
        exit;
    }
    if (!preg_match('/^\d{3,4}$/', $card_cvv)) {
        $_SESSION['pay_error'] = 'Please enter a valid CVV.';
        header('Location: ../checkout.php');
        exit;
    }
}

// ── Fetch cart ────────────────────────────────────────────────
$cart_stmt = $pdo->prepare('
    SELECT c.quantity, c.game_id, g.price, g.name
    FROM Cart c
    JOIN Games g ON c.game_id = g.id
    WHERE c.user_id = ?
');
$cart_stmt->execute([$user_id]);
$cart_items = $cart_stmt->fetchAll();

if (empty($cart_items)) {
    header('Location: ../cart.php');
    exit;
}

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// ── Create order ──────────────────────────────────────────────
try {
    $pdo->beginTransaction();

    // 1. Insert the main order record
    $order_stmt = $pdo->prepare('
        INSERT INTO Orders (user_id, total_price, payment_method, status, order_date)
        VALUES (?, ?, ?, ?, NOW())
    ');
    $order_stmt->execute([$user_id, $total, $method, 'completed']);
    $order_id = $pdo->lastInsertId();

    // 2. Prepare statements for items and keys
    $item_stmt = $pdo->prepare('
        INSERT INTO Order_Items (order_id, game_id, key_id, quantity, unit_price)
        VALUES (?, ?, ?, ?, ?)
    ');
    
    // Grabs one available key and locks the row so no one else can buy it simultaneously
    $key_fetch_stmt = $pdo->prepare('
        SELECT id FROM Game_Keys 
        WHERE game_id = ? AND is_sold = 0 
        LIMIT 1 FOR UPDATE
    ');
    
    $key_update_stmt = $pdo->prepare('
        UPDATE Game_Keys SET is_sold = 1 WHERE id = ?
    ');

    // 3. Loop through the cart and assign keys
    foreach ($cart_items as $item) {
        // Loop based on the quantity purchased
        for ($i = 0; $i < $item['quantity']; $i++) {
            
            // Fetch an available key for this game
            $key_fetch_stmt->execute([$item['game_id']]);
            $key = $key_fetch_stmt->fetch();

            if (!$key) {
                // If a game sells out during checkout, this triggers the rollback
                throw new Exception("Not enough keys in stock for " . $item['name']);
            }

            // Mark key as sold
            $key_update_stmt->execute([$key['id']]);

            // Insert the order item with the assigned key and locked-in price
            $item_stmt->execute([$order_id, $item['game_id'], $key['id'], 1, $item['price']]);
        }
    }

    // 4. Clear the cart
    $pdo->prepare('DELETE FROM Cart WHERE user_id = ?')->execute([$user_id]);

    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
    // Passing the actual error message will help immensely if anything fails
    $_SESSION['pay_error'] = 'An error occurred: ' . $e->getMessage();
    header('Location: ../checkout.php');
    exit;
}

// ── Redirect to orders ────────────────────────────────────────
header('Location: ../orders.php?new=1');
exit;
