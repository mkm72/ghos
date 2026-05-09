<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
s
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

// Fallback if column doesn't exist yet
if (!$user) { session_destroy(); header('Location: auth.php'); exit; }
$twofa_enabled = (bool)($user['2fa_enabled'] ?? false);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Change Email ──────────────────────────────
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
                $pdo->prepare('UPDATE Users SET email = ? WHERE id = ?')->execute([$new_email, $user['id']]);
                $_SESSION['user_email'] = $new_email;
                $user['email']          = $new_email;
                $success = 'Email updated successfully.';
            }
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
        } elseif (strlen($new_pass) < 6) {
            $error = 'New password must be at least 6 characters.';
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
                // Send confirmation email
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
            // Column may not exist — show friendly message
            $error = 'To enable 2FA, run this SQL first: ALTER TABLE Users ADD COLUMN 2fa_enabled TINYINT(1) DEFAULT 0;';
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
    <link rel="stylesheet" href="css/navbar.css">
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

        /* password field with toggle */
        .pass-wrap { position:relative; }
        .pass-wrap input { padding-right:40px; }
        .pass-toggle { position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#aaa;font-size:15px; }
        .pass-toggle:hover { color:#555; }

        .form-footer { display:flex;justify-content:flex-end;padding-top:4px; }

        /* 2FA toggle row */
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

    <!-- 2FA -->
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

    <!-- Change Email -->
    <div class="settings-card">
        <div class="card-header"><h3>Change Email</h3></div>
        <div class="card-body">
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
                        <input type="password" name="new_password" id="cp2" placeholder="Min. 6 characters" required>
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
    btn.textContent = show ? '🙈' : '👁';
}
</script>
</body>
</html>
