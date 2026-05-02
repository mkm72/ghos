<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}
require_once 'php/db_connect.php';

$user_id = (int)$_SESSION['user_id'];

$cart_stmt = $pdo->prepare('
    SELECT c.id AS cart_id, c.quantity, c.game_id,
           g.name, g.price,
           i.filename AS cover_image
    FROM Cart c
    JOIN Games g ON c.game_id = g.id
    LEFT JOIN Game_Images i ON i.game_id = g.id AND i.is_cover = 1
    WHERE c.user_id = ?
');$cart_stmt->execute([$user_id]);
$cart_items = $cart_stmt->fetchAll();

if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$pay_error = $_SESSION['pay_error'] ?? null;
unset($_SESSION['pay_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — GameHub Online Store</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/checkout.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="breadcrumb-bar">
    <a href="cart.php">Back to Cart</a>
    <span class="breadcrumb-sep">›</span>
    <span>Checkout</span>
</div>

<div class="page-wrapper">

    <!-- Step indicator -->
    <div class="steps">
        <div class="step done">
            <div class="step-num">&#10003;</div>
            Cart
        </div>
        <div class="step-line"></div>
        <div class="step active">
            <div class="step-num">2</div>
            Payment
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-num">3</div>
            Confirmation
        </div>
    </div>

    <?php if ($pay_error): ?>
        <div class="alert-error"><?= htmlspecialchars($pay_error) ?></div>
    <?php endif; ?>

    <div class="checkout-layout">

        <!-- LEFT: Payment form -->
        <div class="checkout-left">

            <!-- Card details -->
            <div class="panel">
                <div class="panel-header">
                    <span class="panel-title">Card Details</span>
                    <div class="accepted-cards">
                        <span class="card-chip visa">VISA</span>
                        <span class="card-chip mc">MC</span>
                        <span class="card-chip mada">mada</span>
                    </div>
                    <span class="ssl-tag">SSL Secured</span>
                </div>
                <div class="panel-body">
                    <form id="payForm" action="process_payment.php" method="POST" onsubmit="return handlePay(event)">

                        <div class="form-group">
                            <label for="cardName">Cardholder Name</label>
                            <input type="text" id="cardName" name="card_name"
                                   placeholder="John Doe" autocomplete="cc-name">
                        </div>

                        <div class="form-group">
                            <label for="cardNumber">Card Number</label>
                            <div class="card-input-wrap">
                                <input type="text" id="cardNumber" name="card_number"
                                       placeholder="1234 5678 9012 3456"
                                       maxlength="19" autocomplete="cc-number"
                                       oninput="formatCard(this)">
                                <span class="card-brand-tag" id="cardBrand"></span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="cardExpiry">Expiry Date</label>
                                <input type="text" id="cardExpiry" name="card_expiry"
                                       placeholder="MM / YY" maxlength="7"
                                       oninput="formatExpiry(this)">
                            </div>
                            <div class="form-group">
                                <label for="cardCvv">CVV</label>
                                <input type="text" id="cardCvv" name="card_cvv"
                                       placeholder="123" maxlength="4"
                                       oninput="this.value=this.value.replace(/\D/g,'')">
                            </div>
                        </div>

                        <input type="hidden" name="payment_method" value="card">
                    </form>
                </div>
            </div>

            <!-- Billing info -->
            <div class="panel">
                <div class="panel-header">
                    <span class="panel-title">Billing Information</span>
                </div>
                <div class="panel-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" placeholder="John">
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" placeholder="Doe">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="john@example.com"
                               value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Country</label>
                            <select name="country">
                                <option>Saudi Arabia</option>
                                <option>United Arab Emirates</option>
                                <option>Kuwait</option>
                                <option>Egypt</option>
                                <option>United States</option>
                                <option>United Kingdom</option>
                                <option>Germany</option>
                                <option>France</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>ZIP / Postal Code</label>
                            <input type="text" name="zip" placeholder="12345">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security strip -->
            <div class="security-row">
                <div class="security-badge">SSL Encrypted</div>
                <div class="security-badge">Verified Store</div>
                <div class="security-badge">Instant Delivery</div>
                <div class="security-badge">Official Keys</div>
            </div>

        </div>

       <!-- RIGHT: Order summary -->
        <div class="checkout-right">
            <div class="summary-box">
                <div class="summary-title">Order Summary</div>

                <?php foreach ($cart_items as $item): ?>
                    <div class="summary-item">
                        <div class="summary-item-img">
                            <?php if ($item['cover_image']): ?>
                                <img src="<?= htmlspecialchars(ltrim($item['cover_image'], '/')) ?>"
                                     alt="<?= htmlspecialchars($item['name']) ?>">
                            <?php else: ?>
                                <div class="img-placeholder"></div>
                            <?php endif; ?>
                        </div>
                        <div class="summary-item-info">
                            <div class="summary-item-name"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="summary-item-qty">Qty: <?= (int)$item['quantity'] ?></div>
                        </div>
                        <div class="summary-item-price">
                            <!-- Wrapped the item total -->
                            <span class="price-display" data-usd="<?= $item['price'] * $item['quantity'] ?>">
                                $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>

                <hr class="summary-divider">

                <div class="summary-row">
                    <span>Subtotal</span>
                    <!-- Wrapped the subtotal -->
                    <span class="price-display" data-usd="<?= $subtotal ?>">
                        $<?= number_format($subtotal, 2) ?>
                    </span>
                </div>
                <div class="summary-row">
                    <span>Tax</span>
                    <!-- Wrapped the tax -->
                    <span class="price-display" data-usd="0">
                        $0.00
                    </span>
                </div>
                <div class="summary-row">
                    <span>Delivery</span>
                    <span class="free-tag">FREE</span>
                </div>

                <hr class="summary-divider">

                <div class="summary-total-row">
                    <span>Total</span>
                    <!-- Wrapped the total price -->
                    <span class="summary-total-price price-display" data-usd="<?= $subtotal ?>">
                        $<?= number_format($subtotal, 2) ?>
                    </span>
                </div>

                <button class="pay-btn" id="payBtn" onclick="submitPay()">
                    Pay 
                    <!-- Wrapped the button price -->
                    <span class="price-display" data-usd="<?= $subtotal ?>">
                        $<?= number_format($subtotal, 2) ?>
                    </span>
                </button>

                <div class="secure-note">Your payment is 100% secure and encrypted</div>
            </div>
        </div>
<div class="footer">© 2026 GameHub Online Store. All rights reserved.</div>

<!-- Success overlay -->
<div class="success-overlay" id="successOverlay">
    <div class="success-card">
        <div class="success-title">Payment Successful</div>
        <div class="success-sub">
            Your game keys are ready.<br>
            Redirecting to your orders...
        </div>
        <div class="countdown" id="countdown"></div>
        <a href="orders.php?new=1" class="success-btn">View My Keys</a>
    </div>
</div>

<script>
    /* ── Card number: groups of 4 + brand detect ── */
    function formatCard(el) {
        let val = el.value.replace(/\D/g, '').substring(0, 16);
        el.value = val.replace(/(.{4})/g, '$1 ').trim();

        const brand = document.getElementById('cardBrand');
        if      (val.startsWith('4')) brand.textContent = 'VISA';
        else if (val.startsWith('5')) brand.textContent = 'MC';
        else if (val.startsWith('6')) brand.textContent = 'mada';
        else                          brand.textContent = '';
    }

    /* ── Expiry: MM / YY ── */
    function formatExpiry(el) {
        let val = el.value.replace(/\D/g, '').substring(0, 4);
        el.value = val.length >= 3 ? val.substring(0, 2) + ' / ' + val.substring(2) : val;
    }

    /* ── Validate & submit ── */
    function submitPay() {
        const name   = document.getElementById('cardName').value.trim();
        const number = document.getElementById('cardNumber').value.replace(/\s/g, '');
        const expiry = document.getElementById('cardExpiry').value.replace(/\s|\//g, '');
        const cvv    = document.getElementById('cardCvv').value.trim();

        if (!name)              return shakeField('cardName',   'Cardholder name is required.');
        if (number.length < 16) return shakeField('cardNumber', 'Enter a valid 16-digit card number.');
        if (expiry.length < 4)  return shakeField('cardExpiry', 'Enter the expiry date.');
        if (cvv.length < 3)     return shakeField('cardCvv',    'Enter the CVV.');

        const btn = document.getElementById('payBtn');
        btn.disabled = true;
        btn.textContent = 'Processing...';

        setTimeout(() => {
            showSuccess();
            setTimeout(() => document.getElementById('payForm').submit(), 2500);
        }, 1200);
    }

    function handlePay(e) { e.preventDefault(); }

    function shakeField(id, msg) {
        const el = document.getElementById(id);
        el.classList.add('field-error');
        el.focus();
        setTimeout(() => el.classList.remove('field-error'), 2000);
        return false;
    }

    function showSuccess() {
        document.getElementById('successOverlay').classList.add('show');
        let secs = 3;
        const cd = document.getElementById('countdown');
        cd.textContent = 'Redirecting in ' + secs + 's';
        const iv = setInterval(() => {
            secs--;
            cd.textContent = secs > 0 ? 'Redirecting in ' + secs + 's' : '';
            if (secs <= 0) clearInterval(iv);
        }, 1000);
    }
</script>

</body>
</html>
