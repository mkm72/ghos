<?php
session_start();
require_once 'php/db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();

$where_clause = $user_id ? "c.user_id = :identifier" : "c.session_id = :identifier";
$identifier   = $user_id ?: $session_id;

$cart_stmt = $pdo->prepare("
    SELECT c.id AS cart_id, c.quantity, c.game_id,
           g.name, g.price,
           i.filename AS cover_image
    FROM Cart c
    JOIN Games g ON c.game_id = g.id
    LEFT JOIN Game_Images i ON i.game_id = g.id AND i.is_cover = 1
    WHERE $where_clause
");
$cart_stmt->execute(['identifier' => $identifier]);
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
    <title>Checkout — Ghos</title>
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

        <div class="checkout-left">
            <!-- IMPORTANT: Form tag now wraps ALL inputs -->
            <form id="payForm" action="process_payment.php" method="POST">

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
                        <div class="form-group">
                            <label for="cardName">Cardholder Name</label>
                            <input type="text" id="cardName" name="card_name" placeholder="John Doe" autocomplete="cc-name">
                        </div>
                        <div class="form-group">
                            <label for="cardNumber">Card Number</label>
                            <div class="card-input-wrap">
                                <input type="text" id="cardNumber" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19" autocomplete="cc-number" oninput="formatCard(this)">
                                <span class="card-brand-tag" id="cardBrand"></span>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cardExpiry">Expiry Date</label>
                                <input type="text" id="cardExpiry" name="card_expiry" placeholder="MM / YY" maxlength="7" oninput="formatExpiry(this)">
                            </div>
                            <div class="form-group">
                                <label for="cardCvv">CVV</label>
                                <input type="text" id="cardCvv" name="card_cvv" placeholder="123" maxlength="4" oninput="this.value=this.value.replace(/\D/g,'')">
                            </div>
                        </div>
                        <input type="hidden" name="payment_method" value="card">
                    </div>
                </div>

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
                            <input type="email" id="checkoutEmail" name="email" placeholder="john@example.com"
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

            </form>

            <div class="security-row">
                <div class="security-badge">SSL Encrypted</div>
                <div class="security-badge">Verified Store</div>
                <div class="security-badge">Instant Delivery</div>
                <div class="security-badge">Official Keys</div>
            </div>

        </div>

        <div class="checkout-right">
            <div class="summary-box">
                <div class="summary-title">Order Summary</div>

                <?php foreach ($cart_items as $item): ?>
                    <div class="summary-item">
                        <div class="summary-item-img">
                            <?php if ($item['cover_image']): ?>
                                <img src="<?= htmlspecialchars(ltrim($item['cover_image'], '/')) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            <?php else: ?>
                                <div class="img-placeholder"></div>
                            <?php endif; ?>
                        </div>
                        <div class="summary-item-info">
                            <div class="summary-item-name"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="summary-item-qty">Qty: <?= (int)$item['quantity'] ?></div>
                        </div>
                        <div class="summary-item-price">
                            <span class="price-display" data-usd="<?= $item['price'] * $item['quantity'] ?>">
                                $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>

                <hr class="summary-divider">

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span class="price-display" data-usd="<?= $subtotal ?>">
                        $<?= number_format($subtotal, 2) ?>
                    </span>
                </div>
                <div class="summary-row">
                    <span>Tax</span>
                    <span class="price-display" data-usd="0">$0.00</span>
                </div>
                <div class="summary-row">
                    <span>Delivery</span>
                    <span class="free-tag">FREE</span>
                </div>

                <hr class="summary-divider">

                <div class="summary-total-row">
                    <span>Total</span>
                    <span class="summary-total-price price-display" data-usd="<?= $subtotal ?>">
                        $<?= number_format($subtotal, 2) ?>
                    </span>
                </div>

                <button class="pay-btn" id="payBtn" onclick="submitPay()">
                    Pay
                    <span class="price-display" data-usd="<?= $subtotal ?>">
                        $<?= number_format($subtotal, 2) ?>
                    </span>
                </button>

                <div class="secure-note">Your payment is 100% secure and encrypted</div>
            </div>
        </div>

    </div>

    <div class="footer">© 2026 Ghos. All rights reserved.</div>

</div>

<!-- Processing overlay (Replacing the fake success overlay) -->
<div class="success-overlay" id="processingOverlay">
    <div class="success-card">
        <div class="success-title">Processing Payment...</div>
        <div class="success-sub" style="margin-top: 10px;">
            Securely verifying your details.<br>
            Please do not close or refresh this page.
        </div>
        <!-- Loading Spinner -->
        <div style="margin: 20px auto; border: 4px solid #f1f5f9; border-top: 4px solid #8b5cf6; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite;"></div>
        <style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>
    </div>
</div>

<script>
    function formatCard(el) { 
        let val = el.value.replace(/\D/g, '').substring(0, 16); 
        el.value = val.replace(/(.{4})/g, '$1 ').trim(); 
    }
    
    function formatExpiry(el) { 
        let val = el.value.replace(/\D/g, '').substring(0, 4); 
        el.value = val.length >= 3 ? val.substring(0, 2) + ' / ' + val.substring(2) : val; 
    }
    
    function submitPay() {
        const name = document.getElementById('cardName').value.trim();
        const number = document.getElementById('cardNumber').value.replace(/\s/g, '');
        const expiry = document.getElementById('cardExpiry').value.replace(/[\s\/]/g, '');
        const cvv = document.getElementById('cardCvv').value.trim();
        const email = document.getElementById('checkoutEmail').value.trim();

        // 1. Validate frontend fields
        if (!name) return shakeField('cardName');
        if (number.length < 16) return shakeField('cardNumber');
        if (expiry.length < 4) return shakeField('cardExpiry');
        if (cvv.length < 3) return shakeField('cardCvv');
        if (!email) return shakeField('checkoutEmail');

        // 2. Lock the button and show the "Processing" overlay
        const btn = document.getElementById('payBtn');
        btn.disabled = true;
        btn.textContent = 'Processing...';
        document.getElementById('processingOverlay').classList.add('show');
        
        // 3. Immediately submit the form to the server to do the real checks
        document.getElementById('payForm').submit();
    }
    
    function shakeField(id) {
        const el = document.getElementById(id);
        el.classList.add('field-error'); 
        el.focus();
        setTimeout(() => el.classList.remove('field-error'), 2000);
        return false;
    }
</script>
</body>
</html>
