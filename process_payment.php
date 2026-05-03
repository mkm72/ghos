<?php
// php/process_payment.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'php/db_connect.php';

// --- GUEST CHECKOUT LOGIC ---
$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();

$where_clause = $user_id ? "c.user_id = :identifier" : "c.session_id = :identifier";
$identifier   = $user_id ?: $session_id;
// ----------------------------

$method  = $_POST['payment_method'] ?? 'card';

// ── Validate card fields ──────────────────────────────────────
if ($method === 'card') {
    $card_name   = trim($_POST['card_name']   ?? '');
    $card_number = preg_replace('/[\s\-]+/', '', $_POST['card_number'] ?? '');
    $card_expiry = preg_replace('/[\s\/]+/', '', $_POST['card_expiry'] ?? '');
    $card_cvv    = trim($_POST['card_cvv']    ?? '');

    if (!$card_name || !preg_match('/^\d{16}$/', $card_number) || !preg_match('/^\d{4}$/', $card_expiry) || !preg_match('/^\d{3,4}$/', $card_cvv)) {
        $_SESSION['pay_error'] = 'Please check your card details and try again.';
        header('Location: checkout.php');
        exit;
    }

    $exp_month = (int)substr($card_expiry, 0, 2);
    $exp_year  = (int)('20' . substr($card_expiry, 2, 2));
    $now_month = (int)date('m');
    $now_year  = (int)date('Y');
    if ($exp_year < $now_year || ($exp_year === $now_year && $exp_month < $now_month)) {
        $_SESSION['pay_error'] = 'Your card has expired.';
        header('Location: checkout.php');
        exit;
    }
}

// ── Validate Guest Email ──────────────────────────────────────
$guest_email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$user_id && !$guest_email) {
    $_SESSION['pay_error'] = 'A valid email address is required for guest checkout.';
    header('Location: checkout.php');
    exit;
}

// ── Fetch cart ────────────────────────────────────────────────
$cart_stmt = $pdo->prepare("
    SELECT c.quantity, c.game_id, g.price, g.name
    FROM Cart c
    JOIN Games g ON c.game_id = g.id
    WHERE $where_clause
");
$cart_stmt->execute(['identifier' => $identifier]);
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

// ── Verify stock BEFORE opening transaction ───────────────────
foreach ($cart_items as $item) {
    $avail_stmt = $pdo->prepare('SELECT COUNT(*) FROM Game_Keys WHERE game_id = ? AND is_sold = 0');
    $avail_stmt->execute([$item['game_id']]);
    $available = (int)$avail_stmt->fetchColumn();

    if ($available < $item['quantity']) {
        $_SESSION['pay_error'] = htmlspecialchars($item['name']) . ' only has ' . $available . ' key(s) left in stock.';
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

    $order_stmt = $pdo->prepare('
        INSERT INTO Orders (user_id, guest_email, total_price, payment_method, status)
        VALUES (?, ?, ?, ?, ?)
    ');
    // Save the guest email ONLY if there is no user_id
    $order_stmt->execute([$user_id, $user_id ? null : $guest_email, $total, $method, 'completed']);
    $order_id = (int)$pdo->lastInsertId();

    if (!$order_id) {
        throw new Exception('Failed to create order record.');
    }

    $item_stmt = $pdo->prepare('
        INSERT INTO Order_Items (order_id, game_id, key_id, quantity, unit_price)
        VALUES (?, ?, ?, ?, ?)
    ');

    $key_fetch_stmt = $pdo->prepare('
        SELECT id FROM Game_Keys
        WHERE game_id = ? AND is_sold = 0
        LIMIT 1 FOR UPDATE
    ');

    $key_update_stmt = $pdo->prepare('UPDATE Game_Keys SET is_sold = 1 WHERE id = ?');

    foreach ($cart_items as $item) {
        for ($i = 0; $i < $item['quantity']; $i++) {
            $key_fetch_stmt->execute([$item['game_id']]);
            $key = $key_fetch_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$key) {
                throw new Exception('Sorry, "' . $item['name'] . '" just sold out. Please remove it from your cart.');
            }

            $key_update_stmt->execute([$key['id']]);
            $item_stmt->execute([$order_id, $item['game_id'], $key['id'], 1, $item['price']]);
        }
    }

    // Clear the cart using the dynamic clause
    $clear_stmt = $pdo->prepare("DELETE FROM Cart WHERE " . str_replace("c.", "", $where_clause));
    $clear_stmt->execute(['identifier' => $identifier]);

    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['pay_error'] = $e->getMessage();
    header('Location: checkout.php');
    exit;
}

// ── Success ───────────────────────────────────────────────────
$_SESSION['success'] = 'Payment successful! Your keys are ready.';

// If they are a guest, you might want to redirect them to a generic success page 
// or a page where they can check their order via email. For now, we will route them
// to the orders page, but you'll likely need to adapt that file next to handle guests.
header('Location: orders.php?new=1');
exit;
