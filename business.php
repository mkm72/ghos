<?php
session_start();
require_once 'php/db_connect.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_role    = $_SESSION['role'] ?? $_SESSION['user_role'] ?? 'guest';

$success      = '';
$error        = '';
$field_errors = [];
$already_applied = false;
$already_business = false;

$is_admin = false;
// Check if already business/admin
if ($is_logged_in) {
    if ($user_role === 'business') {
        $already_business = true;
    } elseif ($user_role === 'admin') {
        $is_admin = true;
    }
}

// Check if already applied
if ($is_logged_in && !$already_business && !$is_admin) {
    try {
        $pdo->exec('CREATE TABLE IF NOT EXISTS Business_Applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            business_name VARCHAR(200) NOT NULL,
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            business_email VARCHAR(200),
            website VARCHAR(300),
            sales_volume VARCHAR(100),
            key_source VARCHAR(100),
            reason TEXT,
            status ENUM("pending","approved","rejected") DEFAULT "pending",
            created_at DATETIME DEFAULT NOW(),
            reviewed_at DATETIME NULL,
            FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE
        )');
        $chk = $pdo->prepare('SELECT status FROM Business_Applications WHERE user_id=? ORDER BY id DESC LIMIT 1');
        $chk->execute([$_SESSION['user_id']]);
        $prev = $chk->fetch();
        if ($prev) $already_applied = $prev['status'];
    } catch (\PDOException $e) {}
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_logged_in && !$already_business && !$is_admin) {
    $first_name   = trim($_POST['first_name'] ?? '');
    $last_name    = trim($_POST['last_name'] ?? '');
    $business_name= trim($_POST['business_name'] ?? '');
    $biz_email    = trim($_POST['business_email'] ?? '');
    $website      = trim($_POST['website'] ?? '');
    $sales_volume = trim($_POST['sales_volume'] ?? '');
    $key_source   = trim($_POST['key_source'] ?? '');
    $reason       = trim($_POST['reason'] ?? '');

    if ($already_applied === 'pending') {
        $error = 'You already have a pending application.';
    } else {
        if (!$first_name)   $field_errors['first_name']     = 'First name is required.';
        if (!$last_name)    $field_errors['last_name']      = 'Last name is required.';
        if (!$business_name)$field_errors['business_name']  = 'Company name is required.';
        if (!$biz_email)    $field_errors['business_email'] = 'Business email is required.';
        elseif (!filter_var($biz_email, FILTER_VALIDATE_EMAIL))
                            $field_errors['business_email'] = 'Please enter a valid email.';
        if (!$sales_volume) $field_errors['sales_volume']   = 'Please select a sales volume.';
        if (!$key_source)   $field_errors['key_source']     = 'Please select a key source.';

        if ($field_errors) {
            $error = 'Please fix the errors below.';
        } else {
            try {
                $ins = $pdo->prepare('INSERT INTO Business_Applications
                    (user_id, business_name, first_name, last_name, business_email, website, sales_volume, key_source, reason)
                    VALUES (?,?,?,?,?,?,?,?,?)');
                $ins->execute([
                    $_SESSION['user_id'], $business_name, $first_name, $last_name,
                    $biz_email, $website, $sales_volume, $key_source, $reason
                ]);
                $success = 'Your application has been submitted! We will review it within 24-48 hours.';
                $already_applied = 'pending';
            } catch (\PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Your Business — GameHub Online Store</title>
    <link rel="icon" type="image/png" href="images/logo/logo2.png">
    <link rel="stylesheet" href="css/navbar.css?v=2026.05.17.v2">
    <link rel="stylesheet" href="css/business.css">
    <style>
        .input-error { border: 2px solid #ef4444 !important; background: #fff5f5 !important; }
        .field-error { color: #dc2626; font-size: 12px; margin-top: 4px; font-weight: 500; }
        input.input-error:focus, select.input-error:focus { outline-color: #ef4444; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<!-- HERO -->
<div class="business-product">
    <h1>🚀 Become a Verified Seller</h1>
    <p>Join GameHub Online Store to reach millions of gamers worldwide. Register your business today to start selling game keys with industry-low fees and powerful tools.</p>
    <div class="hero-buttons">
        <?php if ($is_admin): ?>
            <a href="admin.php" class="btn-blue" style="font-size:15px;padding:12px 28px;">Go to Admin Panel</a>
        <?php elseif ($already_business): ?>
            <a href="business-dashboard.php" class="btn-blue" style="font-size:15px;padding:12px 28px;">Go to Dashboard</a>
        <?php else: ?>
            <a href="#register" class="btn-blue" style="font-size:15px;padding:12px 28px;">Apply Now</a>
            <a href="#pricing" class="btn-white" style="font-size:15px;padding:12px 28px;">View Seller Tiers</a>
        <?php endif; ?>
    </div>
</div>

<!-- WHY SELL -->
<div class="why-section">
    <h2 class="section-heading">Why Sell on GameHub Online Store?</h2>
    <p class="section-subheading">Everything you need to scale your digital game business</p>
    <div class="features-grid">
        <div class="feature-card"><div class="feature-icon">🌍</div><div class="feature-title">Global Reach</div><p class="feature-text">Get your game keys in front of a massive, active community of gamers looking for their next adventure.</p></div>
        <div class="feature-card"><div class="feature-icon">💸</div><div class="feature-title">Fast Payouts</div><p class="feature-text">Receive your earnings quickly and securely. Choose from direct bank transfer, PayPal, or crypto payouts.</p></div>
        <div class="feature-card"><div class="feature-icon">📊</div><div class="feature-title">Powerful Dashboard</div><p class="feature-text">Manage inventory, track real-time sales, and update prices easily through our comprehensive seller dashboard.</p></div>
        <div class="feature-card"><div class="feature-icon">🛡️</div><div class="feature-title">Seller Protection</div><p class="feature-text">Our advanced anti-fraud system protects you from chargebacks and ensures every transaction is secure.</p></div>
        <div class="feature-card"><div class="feature-icon">🤝</div><div class="feature-title">Dedicated Support</div><p class="feature-text">Get access to a dedicated merchant success manager to help you optimize your store and resolve issues.</p></div>
        <div class="feature-card"><div class="feature-icon">🔌</div><div class="feature-title">API Integration</div><p class="feature-text">Automate your stock management and pricing with our robust developer API.</p></div>
    </div>
</div>

<!-- PRICING -->
<div class="pricing-section" id="pricing">
    <div class="pricing-inner">
        <h2 class="section-heading">Seller Tiers</h2>
        <p class="section-subheading">Transparent pricing designed to help your business grow</p>
        <div class="pricing-grid">
            <div class="plan-card">
                <div class="plan-name">Basic Seller</div>
                <div class="plan-desc">Perfect for new merchants</div>
                <div class="plan-price">8% <span>fee / sale</span></div>
                <div class="plan-keys">No monthly subscription</div>
                <ul class="plan-features">
                    <li><span class="check">✓</span> List unlimited games</li>
                    <li><span class="check">✓</span> Standard seller dashboard</li>
                    <li><span class="check">✓</span> Email support</li>
                    <li><span class="check">✓</span> Standard payouts (14 days)</li>
                </ul>
                <a href="#register" class="plan-btn plan-btn-outline">Apply Now</a>
            </div>
            <div class="plan-card recommended">
                <div class="plan-badge">⭐ Most Popular</div>
                <div class="plan-name">Pro Seller</div>
                <div class="plan-desc">For established businesses</div>
                <div class="plan-price">5% <span>fee / sale</span></div>
                <div class="plan-keys">$29.99 / month</div>
                <ul class="plan-features">
                    <li><span class="check">✓</span> List unlimited games</li>
                    <li><span class="check">✓</span> Advanced analytics dashboard</li>
                    <li><span class="check">✓</span> Priority support response</li>
                    <li><span class="check">✓</span> Expedited payouts (3 days)</li>
                    <li><span class="check">✓</span> Featured listings</li>
                    <li><span class="check">✓</span> API access</li>
                </ul>
                <a href="#register" class="plan-btn plan-btn-blue">Apply for Pro</a>
            </div>
            <div class="plan-card">
                <div class="plan-name">Publisher</div>
                <div class="plan-desc">For game studios & publishers</div>
                <div class="plan-price">Custom <span>rates</span></div>
                <div class="plan-keys">Tailored partnership</div>
                <ul class="plan-features">
                    <li><span class="check">✓</span> Zero listing fees</li>
                    <li><span class="check">✓</span> Premium homepage placement</li>
                    <li><span class="check">✓</span> Dedicated success manager</li>
                    <li><span class="check">✓</span> Next-day payouts</li>
                    <li><span class="check">✓</span> Marketing campaign support</li>
                    <li><span class="check">✓</span> DRM & regional locking controls</li>
                </ul>
                <a href="#register" class="plan-btn plan-btn-outline">Contact Us</a>
            </div>
        </div>
    </div>
</div>

<!-- HOW IT WORKS -->
<div class="how-section">
    <h2 class="section-heading">How to Start Selling</h2>
    <p class="section-subheading">Get your storefront live in 4 simple steps</p>
    <div class="steps-grid">
        <div class="step-card"><div class="step-number">1</div><div class="step-title">Apply</div><p class="step-text">Fill out the business registration form below with your company details.</p></div>
        <div class="step-card"><div class="step-number">2</div><div class="step-title">Get Verified</div><p class="step-text">Our team reviews your application within 24-48 hours to ensure quality standards.</p></div>
        <div class="step-card"><div class="step-number">3</div><div class="step-title">Upload Keys</div><p class="step-text">Access your Seller Dashboard to add your games and bulk-upload your CD keys.</p></div>
        <div class="step-card"><div class="step-number">4</div><div class="step-title">Get Paid</div><p class="step-text">Watch your sales come in and withdraw your earnings directly to your bank account.</p></div>
    </div>
</div>

<!-- APPLICATION FORM -->
<div class="contact-section" id="register">
    <div class="contact-inner">
        <h2>Business Registration Application</h2>
        <p>Tell us about your company to begin the verification process.</p>

        <?php if ($success): ?>
        <div style="background:#f0fdf4;border:1px solid #86efac;color:#15803d;padding:16px 20px;border-radius:10px;margin-bottom:24px;font-weight:500;text-align:center;">
            ✅ <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div style="background:#fff0f0;border:1px solid #fca5a5;color:#b91c1c;padding:16px 20px;border-radius:10px;margin-bottom:24px;font-weight:500;">
            ⚠️ <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php if ($is_admin): ?>
        <!-- Admin -->
        <div style="text-align:center;padding:40px 20px;background:#f9f9f9;border-radius:12px;">
            <div style="font-size:48px;margin-bottom:12px;">👑</div>
            <div style="font-size:18px;font-weight:bold;color:#1a1a1a;margin-bottom:8px;">You're an Administrator!</div>
            <p style="color:#888;margin-bottom:20px;">Go to your Admin Panel to manage the store and review applications.</p>
            <a href="admin.php" class="btn-blue" style="padding:12px 28px;font-size:15px;">Open Admin Panel →</a>
        </div>

        <?php elseif ($already_business): ?>
        <!-- Already a seller -->
        <div style="text-align:center;padding:40px 20px;background:#f9f9f9;border-radius:12px;">
            <div style="font-size:48px;margin-bottom:12px;">🎉</div>
            <div style="font-size:18px;font-weight:bold;color:#1a1a1a;margin-bottom:8px;">You're already a verified seller!</div>
            <p style="color:#888;margin-bottom:20px;">Go to your dashboard to manage your listings and sales.</p>
            <a href="business-dashboard.php" class="btn-blue" style="padding:12px 28px;font-size:15px;">Open Dashboard →</a>
        </div>

        <?php elseif ($already_applied === 'pending'): ?>
        <!-- Pending -->
        <div style="text-align:center;padding:40px 20px;background:#fffbeb;border:1px solid #fde68a;border-radius:12px;">
            <div style="font-size:48px;margin-bottom:12px;">⏳</div>
            <div style="font-size:18px;font-weight:bold;color:#92400e;margin-bottom:8px;">Application Under Review</div>
            <p style="color:#78350f;">Your application is being reviewed. We'll notify you within 24-48 hours.</p>
        </div>

        <?php elseif ($already_applied === 'rejected'): ?>
        <!-- Rejected — allow reapply -->
        <div style="background:#fff0f0;border:1px solid #fca5a5;color:#991b1b;padding:14px 18px;border-radius:10px;margin-bottom:20px;">
            ❌ Your previous application was rejected. You may submit a new one.
        </div>
        <?php include_once ''; // fall through to form ?>
        <?php $already_applied = false; ?>
        <?php endif; ?>

        <?php if (!$already_business && !$is_admin && $already_applied !== 'pending'): ?>

        <?php if (!$is_logged_in): ?>
        <div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;padding:14px 18px;border-radius:10px;margin-bottom:20px;text-align:center;">
            You must <a href="auth.php" style="font-weight:bold;color:#1d4ed8;">log in</a> to submit a business application.
        </div>
        <?php endif; ?>

        <form method="POST" action="business.php#register" class="contact-form" <?= !$is_logged_in ? 'style="opacity:.5;pointer-events:none;"' : '' ?>>

            <div class="form-row">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="first_name" placeholder="Mohammed" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" class="<?= isset($field_errors['first_name']) ? 'input-error' : '' ?>">
                    <?php if(isset($field_errors['first_name'])): ?><div class="field-error"><?= $field_errors['first_name'] ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" placeholder="AlRashed" required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" class="<?= isset($field_errors['last_name']) ? 'input-error' : '' ?>">
                    <?php if(isset($field_errors['last_name'])): ?><div class="field-error"><?= $field_errors['last_name'] ?></div><?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Company Name *</label>
                    <input type="text" name="business_name" placeholder="Sony" required value="<?= htmlspecialchars($_POST['business_name'] ?? '') ?>" class="<?= isset($field_errors['business_name']) ? 'input-error' : '' ?>">
                    <?php if(isset($field_errors['business_name'])): ?><div class="field-error"><?= $field_errors['business_name'] ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Business Email *</label>
                    <input type="email" name="business_email" placeholder="you@company.com" required value="<?= htmlspecialchars($_POST['business_email'] ?? '') ?>" class="<?= isset($field_errors['business_email']) ? 'input-error' : '' ?>">
                    <?php if(isset($field_errors['business_email'])): ?><div class="field-error"><?= $field_errors['business_email'] ?></div><?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Company Website</label>
                    <input type="url" name="website" placeholder="https://yourstore.com" value="<?= htmlspecialchars($_POST['website'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Expected Monthly Sales Volume *</label>
                    <select name="sales_volume" required class="<?= isset($field_errors['sales_volume']) ? 'input-error' : '' ?>">
                        <option value="">Select volume...</option>
                        <?php foreach(['Less than $1,000','$1,000 - $10,000','$10,000 - $50,000','More than $50,000'] as $v): ?>
                            <option value="<?=$v?>" <?= ($_POST['sales_volume'] ?? '') === $v ? 'selected' : '' ?>><?=$v?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if(isset($field_errors['sales_volume'])): ?><div class="field-error"><?= $field_errors['sales_volume'] ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Primary Source of Keys *</label>
                    <select name="key_source" required class="<?= isset($field_errors['key_source']) ? 'input-error' : '' ?>">
                        <option value="">Select source...</option>
                        <?php foreach(['Official Publisher / Developer','Authorized Distributor','Retail Box Scans','Other'] as $s): ?>
                            <option value="<?=$s?>" <?= ($_POST['key_source'] ?? '') === $s ? 'selected' : '' ?>><?=$s?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if(isset($field_errors['key_source'])): ?><div class="field-error"><?= $field_errors['key_source'] ?></div><?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Additional Information</label>
                <textarea name="reason" placeholder="Tell us briefly about your business history and the types of games you plan to sell..."><?= htmlspecialchars($_POST['reason'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="form-submit">📝 Submit Application</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<!-- EXISTING SELLERS -->
<div class="existing-section">
    <h2 class="section-heading" style="text-align:center;">Already a Registered Seller?</h2>
    <div class="existing-grid">
        <div class="existing-card">
            <div class="existing-icon">📈</div>
            <div><div class="existing-title">Go to Dashboard</div><p class="existing-text">Access your Seller Panel to manage listings, view revenue, and track analytics.</p><a href="business-dashboard.php" class="existing-link">Open Dashboard →</a></div>
        </div>
        <div class="existing-card">
            <div class="existing-icon">➕</div>
            <div><div class="existing-title">Add New Games</div><p class="existing-text">Upload new CD keys or create fresh product listings for your upcoming titles.</p><a href="business-dashboard.php" class="existing-link">Manage Inventory →</a></div>
        </div>
        <div class="existing-card">
            <div class="existing-icon">💰</div>
            <div><div class="existing-title">Request Payout</div><p class="existing-text">View your available balance and withdraw your funds to your preferred payment method.</p><a href="business-dashboard.php" class="existing-link">Go to Billing →</a></div>
        </div>
        <div class="existing-card">
            <div class="existing-icon">📞</div>
            <div><div class="existing-title">Seller Support</div><p class="existing-text">Need help with an order or your account? Contact our dedicated merchant support team.</p><a href="mailto:sellers@ghos.shop" class="existing-link">Email Support →</a></div>
        </div>
    </div>
</div>

<div class="footer">
    © 2026 GameHub Online Store. All rights reserved. ·
    <a href="index.php" style="color:#888888;">Store</a> ·
    <a href="business.php" style="color:#888888;">Register Business</a>
</div>

<script src="js/navbar.js"></script>
</body>
</html>
