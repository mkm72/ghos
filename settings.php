<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

require_once 'php/db_connect.php';
require_once 'sendEMail.php';

$user_role    = $_SESSION['user_role'] ?? $_SESSION['role'] ?? 'user';
$is_logged_in = true;

$success = '';
$error   = '';

// Fetch user — include 2fa_enabled column
$stmt = $pdo->prepare('SELECT id, email, role, 2fa_enabled FROM Users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) { session_destroy(); header('Location: auth.php'); exit; }
$twofa_enabled = (bool)($user['2fa_enabled'] ?? false);

function generateCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

if (isset($_GET['cancel_email'])) {
    unset($_SESSION['pending_email_change']);
    header('Location: settings.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Change Email (Initial Request) ────────────
    if ($action === 'change_email') {
        $new_email = trim($_POST['new_email'] ?? '');
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $check = $pdo->prepare('SELECT id FROM Users WHERE email = ? AND id != ?');
            $check->execute([$new_email, $user['id']]);
            if ($check->fetch()) {
                $error = 'That email is already in use.';
            } else {
                $code_old = generateCode();
                $code_new = generateCode();
                
                $_SESSION['pending_email_change'] = [
                    'new_email' => $new_email,
                    'old_email' => $user['email'],
                    'code_old'  => $code_old,
                    'code_new'  => $code_new,
                    'expires'   => time() + 600
                ];

                // Send to OLD email
                sendEmail(
                    $user['email'],
                    $user['email'],
                    'Confirm Email Change (Old Address)',
                    "
                    <div style='font-family: Arial, sans-serif; max-width: 500px; margin: auto; padding: 20px;'>
                        <div style='text-align:center; margin-bottom: 24px;'>
                            <img src='https://ghos.shop/images/logo/logo2.png' alt='Ghos Logo' style='height: 80px; border-radius: 8px;'>
                        </div>
                        <h2 style='color:#1a1a1a;'>Confirm your email change</h2>
                        <p style='color:#555; font-size:15px;'>You are requesting to change your GameHub email to <strong>$new_email</strong>.</p>
                        <p style='color:#555; font-size:15px;'>Enter the code below in the settings page to authorize this change from your current address:</p>
                        <div style='text-align:center; margin: 28px 0;'>
                            <div style='display:inline-block; background:#f4f4f4; border:2px dashed #ccc; border-radius:12px; padding:18px 36px;'>
                                <span style='font-size:36px; font-weight:bold; letter-spacing:10px; color:#1a1a1a;'>$code_old</span>
                            </div>
                        </div>
                        <p style='color:#ef4444; font-size:13px;'>If you did not request this, please secure your account immediately.</p>
                        <hr style='border:none; border-top:1px solid #e0e0e0; margin:28px 0 16px;'>
                        <p style='font-size:12px; color:#999; margin:0;'>GameHub Online Store — <a href='https://ghos.shop' style='color:#999;'>ghos.shop</a></p>
                    </div>"
                );

                // Send to NEW email
                sendEmail(
                    $new_email,
                    $new_email,
                    'Confirm Email Change (New Address)',
                    "
                    <div style='font-family: Arial, sans-serif; max-width: 500px; margin: auto; padding: 20px;'>
                        <div style='text-align:center; margin-bottom: 24px;'>
                            <img src='https://ghos.shop/images/logo/logo2.png' alt='Ghos Logo' style='height: 80px; border-radius: 8px;'>
                        </div>
                        <h2 style='color:#1a1a1a;'>Confirm your new email</h2>
                        <p style='color:#555; font-size:15px;'>You are setting this address as your new GameHub account email.</p>
                        <p style='color:#555; font-size:15px;'>Enter the code below in the settings page to verify this new address:</p>
                        <div style='text-align:center; margin: 28px 0;'>
                            <div style='display:inline-block; background:#f4f4f4; border:2px dashed #ccc; border-radius:12px; padding:18px 36px;'>
                                <span style='font-size:36px; font-weight:bold; letter-spacing:10px; color:#1a1a1a;'>$code_new</span>
                            </div>
                        </div>
                        <hr style='border:none; border-top:1px solid #e0e0e0; margin:28px 0 16px;'>
                        <p style='font-size:12px; color:#999; margin:0;'>GameHub Online Store — <a href='https://ghos.shop' style='color:#999;'>ghos.shop</a></p>
                    </div>"
                );

                $success = 'Verification codes have been sent to both your old and new email addresses.';
            }
        }
    }

    // ── Verify Email Change ───────────────────────
    if ($action === 'verify_email_change') {
        $entered_old = trim($_POST['code_old'] ?? '');
        $entered_new = trim($_POST['code_new'] ?? '');
        $pending = $_SESSION['pending_email_change'] ?? null;

        if (!$pending) {
            $error = 'No pending email change found.';
        } elseif (time() > $pending['expires']) {
            $error = 'Verification codes have expired. Please try again.';
            unset($_SESSION['pending_email_change']);
        } elseif ($entered_old !== $pending['code_old'] || $entered_new !== $pending['code_new']) {
            $error = 'One or both verification codes are incorrect.';
        } else {
            $new_email = $pending['new_email'];
            $pdo->prepare('UPDATE Users SET email = ? WHERE id = ?')->execute([$new_email, $user['id']]);
            
            $_SESSION['user_email'] = $new_email;
            $user['email']          = $new_email;
            unset($_SESSION['pending_email_change']);
            $success = 'Email updated successfully to ' . htmlspecialchars($new_email);
        }
    }

    // ── Change Password ───────────────────────────
    if ($action === 'change_password') {
        $current  = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password']      ?? '';
        $repeat   = $_POST['repeat_password']   ?? '';

        $stmt_pw = $pdo->prepare('SELECT password FROM Users WHERE id = ?');
        $stmt_pw->execute([$user['id']]);
        $row = $stmt_pw->fetch();

        if (!password_verify($current, $row['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new_pass) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($new_pass !== $repeat) {
            $error = 'New passwords do not match.';
        } else {
            $hash = password_hash($new_pass, PASSWORD_BCRYPT);
            $pdo->prepare('UPDATE Users SET password = ? WHERE id = ?')->execute([$hash, $user['id']]);
            $success = 'Password updated successfully.';
        }
    }

    // ── Toggle 2FA ────────────────────────────────
    if ($action === 'toggle_2fa') {
        $new_val = $twofa_enabled ? 0 : 1;
        try {
            $pdo->prepare('UPDATE Users SET 2fa_enabled = ? WHERE id = ?')->execute([$new_val, $user['id']]);
            $twofa_enabled = (bool)$new_val;
            $user['2fa_enabled'] = $new_val;
            if ($new_val) {
                $success = '2FA enabled. You will receive a code by email on each login.';
                sendEmail($user['email'], $user['email'], 'Two-Factor Authentication Enabled — GameHub',
                    "<div style='font-family:Arial,sans-serif;max-width:500px;margin:auto;padding:20px;'>
                        <div style='text-align:center;margin-bottom:20px;'>
                            <div style='display:inline-block;background:#1a1a1a;color:white;font-size:20px;font-weight:bold;padding:10px 24px;border-radius:8px;'>Ghos</div>
                        </div>
                        <h2 style='color:#16a34a;'>2FA Enabled</h2>
                        <p style='color:#555;font-size:14px;'>Two-factor authentication has been <strong>enabled</strong> on your account. You will be asked for an email code each time you log in.</p>
                        <p style='color:#999;font-size:12px;'>If you did not do this, please change your password immediately.</p>
                    </div>");
            } else {
                $success = '2FA disabled.';
            }
        } catch (\PDOException $e) {
            $error = 'Error updating 2FA settings.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings — GameHub Online Store</title>
    <link rel="icon" type="image/png" href="images/logo/logo2.png">
    <link rel="stylesheet" href="css/navbar.css?v=2026.05.17.v2">
    <style>
        .settings-wrap { max-width:680px;margin:40px auto;padding:0 20px 60px; }
        .settings-header { margin-bottom:28px; }
        .settings-header h1 { font-size:24px;font-weight:bold;color:#1a1a1a;margin-bottom:4px; }
        .settings-header p  { font-size:14px;color:#888; }

        .alert { font-size:13px;padding:11px 16px;border-radius:8px;margin-bottom:20px;font-weight:500; }
        .alert-success { background:#f0fdf4;border:1px solid #86efac;color:#15803d; }
        .alert-error   { background:#fff0f0;border:1px solid #fca5a5;color:#b91c1c; }

        .settings-card { background:#fff;border:1px solid #e0e0e0;border-radius:10px;overflow:hidden;margin-bottom:20px; }
        .card-header { padding:14px 20px;border-bottom:1px solid #e0e0e0;background:#f9f9f9; }
        .card-header h3 { font-size:12px;font-weight:bold;color:#888;text-transform:uppercase;letter-spacing:.05em; }
        .card-body { padding:20px; }

        .account-row { display:flex;align-items:center;gap:16px; }
        .avatar { width:54px;height:54px;border-radius:50%;background:#2563eb;color:white;font-size:22px;font-weight:bold;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
        .account-meta strong { display:block;font-size:15px;font-weight:bold;color:#1a1a1a; }
        .role-badge { display:inline-block;font-size:11px;font-weight:bold;padding:3px 10px;border-radius:20px;margin-top:5px; }
        .role-admin    { background:#fef3c7;color:#92400e; }
        .role-customer { background:#dbeafe;color:#1d4ed8; }
        .role-business { background:#dcfce7;color:#166534; }
        .role-user     { background:#dbeafe;color:#1d4ed8; }

        .form-group { margin-bottom:16px; }
        .form-group label { display:block;font-size:13px;font-weight:bold;color:#555;margin-bottom:6px; }
        .form-group input { width:100%;padding:9px 12px;border:1px solid #ccc;border-radius:6px;font-size:14px;outline:none;transition:border-color .2s; }
        .form-group input:focus    { border-color:#2563eb; }
        .form-group input[readonly]{ background:#f9f9f9;color:#aaa;cursor:not-allowed; }

        .pass-wrap { position:relative; }
        .pass-wrap input { padding-right:40px; }
        .pass-toggle { position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#aaa;font-size:15px; }
        .pass-toggle:hover { color:#555; }

        .form-footer { display:flex;justify-content:flex-end;padding-top:4px; }

        .twofa-row { display:flex;align-items:center;justify-content:space-between;gap:16px; }
        .twofa-info strong { display:block;font-size:14px;color:#1a1a1a;margin-bottom:3px; }
        .twofa-info span   { font-size:13px;color:#888; }
        .toggle-switch { position:relative;width:48px;height:26px;flex-shrink:0; }
        .toggle-switch input { opacity:0;width:0;height:0; }
        .toggle-slider {
            position:absolute;inset:0;background:#ddd;border-radius:26px;cursor:pointer;transition:.3s;
        }
        .toggle-slider::before {
            content:'';position:absolute;height:20px;width:20px;left:3px;bottom:3px;
            background:white;border-radius:50%;transition:.3s;
        }
        .toggle-switch input:checked + .toggle-slider { background:#16a34a; }
        .toggle-switch input:checked + .toggle-slider::before { transform:translateX(22px); }
        .twofa-badge-on  { font-size:11px;font-weight:bold;padding:3px 9px;border-radius:20px;background:#dcfce7;color:#16a34a; }
        .twofa-badge-off { font-size:11px;font-weight:bold;padding:3px 9px;border-radius:20px;background:#f3f4f6;color:#888; }

        /* 2FA Code Inputs */
        .code-input-wrap { display: flex; gap: 8px; justify-content: flex-start; margin: 10px 0 16px; }
        .code-digit { width: 40px; height: 48px; text-align: center; font-size: 20px; font-weight: bold; border: 2px solid #e0e0e0; border-radius: 8px; outline: none; transition: border-color 0.2s; }
        .code-digit:focus { border-color: #1a1a1a; }
        #countdown { font-weight: bold; color: #1a1a1a; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="settings-wrap">
    <div class="settings-header">
        <h1>Account Settings</h1>
        <p>Manage your GameHub account information</p>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <!-- Account Info -->
    <div class="settings-card">
        <div class="card-header"><h3>Account Info</h3></div>
        <div class="card-body">
            <div class="account-row">
                <div class="avatar"><?= strtoupper(substr($user['email'], 0, 1)) ?></div>
                <div class="account-meta">
                    <strong><?= htmlspecialchars($user['email']) ?></strong>
                    <span class="role-badge role-<?= htmlspecialchars($user['role']) ?>">
                        <?= ucfirst(htmlspecialchars($user['role'])) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2FA Toggle -->
    <div class="settings-card">
        <div class="card-header">
            <h3>Two-Factor Authentication
                <?php if ($twofa_enabled): ?>
                    <span class="twofa-badge-on" style="margin-left:8px;">ON</span>
                <?php else: ?>
                    <span class="twofa-badge-off" style="margin-left:8px;">OFF</span>
                <?php endif; ?>
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="settings.php">
                <input type="hidden" name="action" value="toggle_2fa">
                <div class="twofa-row">
                    <div class="twofa-info">
                        <strong>Email verification on login</strong>
                        <span>When enabled, you'll receive a 6-digit code by email each time you log in.</span>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                        <label class="toggle-switch">
                            <input type="checkbox" <?= $twofa_enabled ? 'checked' : '' ?> onchange="this.form.submit()">
                            <span class="toggle-slider"></span>
                        </label>
                        <span style="font-size:11px;color:#aaa;"><?= $twofa_enabled ? 'Enabled' : 'Disabled' ?></span>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Email Card -->
    <div class="settings-card">
        <div class="card-header"><h3>Change Email</h3></div>
        <div class="card-body">
            <?php if (isset($_SESSION['pending_email_change'])): $pending = $_SESSION['pending_email_change']; ?>
                <form method="POST" action="settings.php" id="verify-email-form">
                    <input type="hidden" name="action" value="verify_email_change">
                    <input type="hidden" name="code_old" id="code_old_hidden">
                    <input type="hidden" name="code_new" id="code_new_hidden">
                    
                    <p style="font-size:13px; color:#555; margin-bottom:15px;">
                        We've sent verification codes to both your old and new email addresses. They expire in <span id="countdown"></span>.
                    </p>
                    
                    <div class="form-group">
                        <label>Code sent to: <strong><?= htmlspecialchars($pending['old_email']) ?></strong></label>
                        <div class="code-input-wrap" data-target="old">
                            <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                            <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                            <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                            <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                            <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                            <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Code sent to: <strong><?= htmlspecialchars($pending['new_email']) ?></strong></label>
                        <div class="code-input-wrap" data-target="new">
                            <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                            <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                            <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                            <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                            <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                            <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                        </div>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:24px;">
                        <a href="?cancel_email=1" style="font-size:13px; color:#b91c1c; text-decoration:none;">Cancel Change</a>
                        <button type="submit" class="btn-blue" id="verify-btn" disabled>Verify & Update Email</button>
                    </div>
                </form>
            <?php else: ?>
                <form method="POST" action="settings.php">
                    <input type="hidden" name="action" value="change_email">
                    <div class="form-group">
                        <label>Current Email</label>
                        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>New Email</label>
                        <input type="email" name="new_email" placeholder="new.email@example.com" required>
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn-blue">Update Email</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Change Password -->
    <div class="settings-card">
        <div class="card-header"><h3>Change Password</h3></div>
        <div class="card-body">
            <form method="POST" action="settings.php">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group">
                    <label>Current Password</label>
                    <div class="pass-wrap">
                        <input type="password" name="current_password" id="cp1" placeholder="••••••••" required>
                        <button type="button" class="pass-toggle" onclick="togglePass('cp1',this)">👁</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <div class="pass-wrap">
                        <input type="password" name="new_password" id="cp2" placeholder="Min. 8 characters" required>
                        <button type="button" class="pass-toggle" onclick="togglePass('cp2',this)">👁</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Repeat New Password</label>
                    <div class="pass-wrap">
                        <input type="password" name="repeat_password" id="cp3" placeholder="••••••••" required>
                        <button type="button" class="pass-toggle" onclick="togglePass('cp3',this)">👁</button>
                    </div>
                </div>
                <div class="form-footer">
                    <button type="submit" class="btn-blue">Update Password</button>
                </div>
            </form>
        </div>
    </div>

</div>

<div class="footer">© 2026 GameHub Online Store. All rights reserved.</div>
<script src="js/navbar.js"></script>
<script>
function togglePass(id, btn) {
    const input = document.getElementById(id);
    const show  = input.type === 'password';
    input.type  = show ? 'text' : 'password';
    btn.textContent = show ? '︶' : '👁';
}

// Code Digit Handling
const wraps = document.querySelectorAll('.code-input-wrap');
if (wraps.length) {
    wraps.forEach(wrap => {
        const type = wrap.dataset.target; // 'old' or 'new'
        const inputs = wrap.querySelectorAll('.code-digit');
        const hidden = document.getElementById('code_' + type + '_hidden');

        inputs.forEach((el, i) => {
            el.addEventListener('input', () => {
                el.value = el.value.replace(/[^0-9]/g, '').slice(-1);
                if (el.value && i < inputs.length - 1) inputs[i + 1].focus();
                syncCodes();
            });
            el.addEventListener('keydown', e => {
                if (e.key === 'Backspace' && !el.value && i > 0) inputs[i - 1].focus();
            });
            el.addEventListener('paste', e => {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                [...pasted].forEach((ch, j) => { if (inputs[j]) inputs[j].value = ch; });
                if (inputs[pasted.length - 1]) inputs[pasted.length - 1].focus();
                syncCodes();
            });
        });
    });

    function syncCodes() {
        const btn = document.getElementById('verify-btn');
        let allFilled = true;
        
        ['old', 'new'].forEach(type => {
            const wrap = document.querySelector(`.code-input-wrap[data-target="${type}"]`);
            const hidden = document.getElementById(`code_${type}_hidden`);
            const code = [...wrap.querySelectorAll('.code-digit')].map(d => d.value).join('');
            hidden.value = code;
            if (code.length < 6) allFilled = false;
        });

        btn.disabled = !allFilled;
    }
}

// Countdown
const countdownEl = document.getElementById('countdown');
if (countdownEl) {
    const expires = <?= isset($_SESSION['pending_email_change']) ? (int)$_SESSION['pending_email_change']['expires'] : 0 ?>;
    function tick() {
        const left = expires - Math.floor(Date.now() / 1000);
        if (left <= 0) {
            countdownEl.textContent = '00:00';
            countdownEl.style.color = '#ef4444';
            document.getElementById('verify-btn').disabled = true;
            return;
        }
        const m = String(Math.floor(left / 60)).padStart(2, '0');
        const s = String(left % 60).padStart(2, '0');
        countdownEl.textContent = m + ':' + s;
        countdownEl.style.color = left <= 60 ? '#ef4444' : '#1a1a1a';
        setTimeout(tick, 1000);
    }
    tick();
}
</script>
</body>
</html>
