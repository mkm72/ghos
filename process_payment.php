<?php
// php/process_payment.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
s
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}
require_once 'php/db_connect.php';

$user_id = (int)$_SESSION['user_id'];
$method  = $_POST['payment_method'] ?? 'card';

// ── Validate card fields ──────────────────────────────────────
if ($method === 'card') {
    $card_name   = trim($_POST['card_name']   ?? '');
    // Strip spaces and dashes so "4111 1111 1111 1111" and "4111-1111-1111-1111" both work
    $card_number = preg_replace('/[\s\-]+/', '', $_POST['card_number'] ?? '');
    // Strip spaces and slash: "12 / 27" or "12/27" → "1227"
    $card_expiry = preg_replace('/[\s\/]+/', '', $_POST['card_expiry'] ?? '');
    $card_cvv    = trim($_POST['card_cvv']    ?? '');

    if (!$card_name) {
        $_SESSION['pay_error'] = 'Cardholder name is required.';
        header('Location: checkout.php');
        exit;
    }
    if (!preg_match('/^\d{16}$/', $card_number)) {
        $_SESSION['pay_error'] = 'Please enter a valid 16-digit card number.';
        header('Location: checkout.php');
        exit;
    }
    if (!preg_match('/^\d{4}$/', $card_expiry)) {
        $_SESSION['pay_error'] = 'Please enter a valid expiry date (MM/YY).';
        header('Location: checkout.php');
        exit;
    }
    // Validate expiry is not in the past
    $exp_month = (int)substr($card_expiry, 0, 2);
    $exp_year  = (int)('20' . substr($card_expiry, 2, 2));
    $now_month = (int)date('m');
    $now_year  = (int)date('Y');
    if ($exp_year < $now_year || ($exp_year === $now_year && $exp_month < $now_month)) {
        $_SESSION['pay_error'] = 'Your card has expired.';
        header('Location: checkout.php');
        exit;
    }
    if (!preg_match('/^\d{3,4}$/', $card_cvv)) {
        $_SESSION['pay_error'] = 'Please enter a valid CVV.';
        header('Location: checkout.php');
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
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

// ── Verify stock BEFORE opening transaction ───────────────────
// Gives a friendly "out of stock" error early, before any money/order intent is recorded
foreach ($cart_items as $item) {
    $avail_stmt = $pdo->prepare('
        SELECT COUNT(*) FROM Game_Keys WHERE game_id = ? AND is_sold = 0
    ');
    $avail_stmt->execute([$item['game_id']]);
    $available = (int)$avail_stmt->fetchColumn();

    if ($available < $item['quantity']) {
        $_SESSION['pay_error'] = htmlspecialchars($item['name'])
            . ' only has ' . $available . ' key(s) left in stock.';
        header('Location: checkout.php');
        exit;
    }
}

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// ── Create order ──────────────────────────────────────────────
try {
    $pdo->beginTransaction();

    // 1. Insert the main order record
    // NOTE: order_date is omitted — the column default (CURRENT_TIMESTAMP) handles it
    $order_stmt = $pdo->prepare('
        INSERT INTO Orders (user_id, total_price, payment_method, status)
        VALUES (?, ?, ?, ?)
    ');
    $order_stmt->execute([$user_id, $total, $method, 'completed']);
    $order_id = (int)$pdo->lastInsertId();

    if (!$order_id) {
        throw new Exception('Failed to create order record.');
    }

    // 2. Prepare statements for items and keys
    $item_stmt = $pdo->prepare('
        INSERT INTO Order_Items (order_id, game_id, key_id, quantity, unit_price)
        VALUES (?, ?, ?, ?, ?)
    ');

    // FOR UPDATE locks the row inside the transaction so two simultaneous
    // checkouts cannot grab the same key
    $key_fetch_stmt = $pdo->prepare('
        SELECT id FROM Game_Keys
        WHERE game_id = ? AND is_sold = 0
        LIMIT 1 FOR UPDATE
    ');

    $key_update_stmt = $pdo->prepare('
        UPDATE Game_Keys SET is_sold = 1 WHERE id = ?
    ');

    // 3. Loop through cart items and assign keys
    foreach ($cart_items as $item) {
        for ($i = 0; $i < $item['quantity']; $i++) {

            $key_fetch_stmt->execute([$item['game_id']]);
            $key = $key_fetch_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$key) {
                // Race condition: another checkout grabbed the last key between our
                // pre-check above and now — roll back everything
                throw new Exception('Sorry, "' . $item['name'] . '" just sold out. Please remove it from your cart.');
            }

            $key_update_stmt->execute([$key['id']]);

            $item_stmt->execute([
                $order_id,
                $item['game_id'],
                $key['id'],
                1,               // always 1 per row — each key gets its own Order_Items row
                $item['price'],  // locked-in price at time of purchase
            ]);
        }
    }

    // 4. Clear the cart
    $pdo->prepare('DELETE FROM Cart WHERE user_id = ?')->execute([$user_id]);

    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['pay_error'] = $e->getMessage();
    header('Location: checkout.php');
    exit;
}

// ── Success ───────────────────────────────────────────────────
$_SESSION['success'] = 'Payment successful! Your keys are ready.';
header('Location: orders.php?new=1');
exit;
