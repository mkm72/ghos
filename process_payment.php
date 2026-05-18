<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
require_once 'php/db_connect.php';
// Include your email sending function
require_once 'sendEMail.php'; 

$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();
$where_clause = $user_id ? "c.user_id = :identifier" : "c.session_id = :identifier";
$identifier   = $user_id ?: $session_id;

$method  = $_POST['payment_method'] ?? 'card';

// ── Validate card fields ──────────────────────────────────────
if ($method === 'card') {
    $card_name   = trim($_POST['card_name']   ?? '');
    $card_number = preg_replace('/[\s\-]+/', '', $_POST['card_number'] ?? '');
    $card_expiry = preg_replace('/[\s\/]+/', '', $_POST['card_expiry'] ?? '');
    $card_cvv    = trim($_POST['card_cvv']    ?? '');

    // Basic format validation
    if (!$card_name || !preg_match('/^\d{16}$/', $card_number) || !preg_match('/^\d{4}$/', $card_expiry) || !preg_match('/^\d{3,4}$/', $card_cvv)) {
        $_SESSION['pay_error'] = 'Please check your card details and try again.';
        header('Location: checkout.php'); exit;
    }

    // Expiry validation (MMYY)
    $month = (int)substr($card_expiry, 0, 2);
    $year  = (int)substr($card_expiry, 2, 2);
    $curMonth = (int)date('m');
    $curYear  = (int)date('y');

    if ($month < 1 || $month > 12) {
        $_SESSION['pay_error'] = 'Invalid expiration month.';
        header('Location: checkout.php'); exit;
    }
    if ($year < $curYear || ($year === $curYear && $month < $curMonth)) {
        $_SESSION['pay_error'] = 'The card has expired.';
        header('Location: checkout.php'); exit;
    }
}

// ── Validate Guest Email & Check for Existing Account ─────────
$guest_email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$user_id) {
    if (!$guest_email) {
        $_SESSION['pay_error'] = 'A valid email address is required for guest checkout.';
        header('Location: checkout.php'); exit;
    }

    $check_email = $pdo->prepare('SELECT id FROM Users WHERE email = ?');
    $check_email->execute([$guest_email]);
    if ($check_email->fetch()) {
        $_SESSION['pay_error'] = 'An account with this email already exists. Please log in first.';
        header('Location: checkout.php'); exit;
    }
}

// ── Fetch cart ────────────────────────────────────────────────
$cart_stmt = $pdo->prepare("SELECT c.quantity, c.game_id, g.price, g.name FROM Cart c JOIN Games g ON c.game_id = g.id WHERE $where_clause");
$cart_stmt->execute(['identifier' => $identifier]);
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) { header('Location: cart.php'); exit; }

// ── Verify stock BEFORE opening transaction ───────────────────
foreach ($cart_items as $item) {
    $avail_stmt = $pdo->prepare('SELECT COUNT(*) FROM Game_Keys WHERE game_id = ? AND is_sold = 0');
    $avail_stmt->execute([$item['game_id']]);
    if ((int)$avail_stmt->fetchColumn() < $item['quantity']) {
        $_SESSION['pay_error'] = htmlspecialchars($item['name']) . ' is out of stock.';
        header('Location: checkout.php'); exit;
    }
}

$total = 0;
foreach ($cart_items as $item) { $total += $item['price'] * $item['quantity']; }

// ── Create order ──────────────────────────────────────────────
try {
    $pdo->beginTransaction();

    $order_stmt = $pdo->prepare('INSERT INTO Orders (user_id, guest_email, total_price, payment_method, status) VALUES (?, ?, ?, ?, ?)');
    $order_stmt->execute([$user_id, $user_id ? null : $guest_email, $total, $method, 'completed']);
    $order_id = (int)$pdo->lastInsertId();

    $item_stmt = $pdo->prepare('INSERT INTO Order_Items (order_id, game_id, key_id, quantity, unit_price) VALUES (?, ?, ?, ?, ?)');
    // UPDATE: Fetch the 'key_code' along with the 'id'
    $key_fetch_stmt = $pdo->prepare('SELECT id, key_code FROM Game_Keys WHERE game_id = ? AND is_sold = 0 LIMIT 1 FOR UPDATE');
    $key_update_stmt = $pdo->prepare('UPDATE Game_Keys SET is_sold = 1 WHERE id = ?');

    $purchased_keys = []; // Array to hold the keys for the email

    foreach ($cart_items as $item) {
        for ($i = 0; $i < $item['quantity']; $i++) {
            $key_fetch_stmt->execute([$item['game_id']]);
            $key = $key_fetch_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$key) {
                throw new Exception('Sorry, "' . $item['name'] . '" just sold out.');
            }

            $key_update_stmt->execute([$key['id']]);
            $item_stmt->execute([$order_id, $item['game_id'], $key['id'], 1, $item['price']]);

            // Save the game name and key code for the email body
            $purchased_keys[] = [
                'game_name' => $item['name'],
                'key_code' => $key['key_code']
            ];
        }
    }

    $clear_stmt = $pdo->prepare("DELETE FROM Cart WHERE " . str_replace("c.", "", $where_clause));
    $clear_stmt->execute(['identifier' => $identifier]);

    // ── Update Past Purchases Cookie ────────────────────────
    $past_purchases = isset($_COOKIE['past_purchases']) ? explode(',', $_COOKIE['past_purchases']) : [];
    foreach ($cart_items as $item) {
        if (!in_array($item['game_id'], $past_purchases)) {
            $past_purchases[] = $item['game_id'];
        }
    }
    // Keep only the last 5 unique purchases
    $past_purchases = array_slice($past_purchases, -5);
    setcookie('past_purchases', implode(',', $past_purchases), time() + (86400 * 30), "/"); // 30 days
    // ────────────────────────────────────────────────────────

    $pdo->commit();

    // ── Send Emails ───────────────────────────────────────────
    $recipient_email = $user_id ? ($_SESSION['user_email'] ?? '') : $guest_email;

    if ($recipient_email) {
        // Email 1: Order Confirmation
        $subject_conf = "Order Confirmation #$order_id — GameHub";
        $body_conf = "
            <div style='font-family:Arial,sans-serif;padding:20px;max-width:600px;'>
                <div style='margin-bottom:24px;'>
                    <img src='https://ghos.shop/images/logo/logo2.png' alt='Ghos Logo' style='height: 70px; border-radius: 8px;'>
                </div>
                <h2>Thank you for your order!</h2>
                <p>Your payment of <strong>$" . number_format($total, 2) . "</strong> has been successfully processed.</p>
                <p><strong>Order ID:</strong> #$order_id</p>
                <p style='color:#555;'>We will send your game activation keys in a separate email shortly.</p>
            </div>
        ";
        sendEmail($recipient_email, $recipient_email, $subject_conf, $body_conf);

        // Email 2: Game Keys
        $subject_keys = "Your Game Keys (Order #$order_id) — GameHub";
        $body_keys = "
            <div style='font-family:Arial,sans-serif;padding:20px;max-width:600px;'>
                <div style='margin-bottom:24px;'>
                    <img src='https://ghos.shop/images/logo/logo2.png' alt='Ghos Logo' style='height: 70px; border-radius: 8px;'>
                </div>
                <h2>Your Digital Keys</h2>
                <p>Here are the activation codes for your recent purchase:</p>
                <ul style='list-style:none;padding:0;'>
        ";
        
        foreach ($purchased_keys as $pk) {
            $body_keys .= "
                <li style='margin-bottom:15px;background:#f9f9f9;padding:15px;border:1px solid #e0e0e0;border-radius:8px;'>
                    <strong style='display:block;margin-bottom:5px;'>" . htmlspecialchars($pk['game_name']) . "</strong>
                    <code style='font-size:18px;color:#2563eb;letter-spacing:1px;font-weight:bold;'>" . htmlspecialchars($pk['key_code']) . "</code>
                </li>
            ";
        }
        
        $body_keys .= "
                </ul>
                <p style='color:#555;margin-top:20px;'>Keep these keys safe and activate them on the corresponding platforms. Enjoy your games!</p>
            </div>
        ";
        sendEmail($recipient_email, $recipient_email, $subject_keys, $body_keys);
    }

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['pay_error'] = $e->getMessage();
    header('Location: checkout.php'); exit;
}

// ── Success ───────────────────────────────────────────────────
if ($user_id) {
    $_SESSION['success'] = 'Payment successful! Your keys are ready.';
    header('Location: orders.php?new=1');
} else {
    $_SESSION['guest_success'] = 'We have emailed you the order confirmation and activation codes.';
    header('Location: index.php');
}
exit;
