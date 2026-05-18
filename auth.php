<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
require_once 'php/db_connect.php';
require_once 'sendEMail.php';

if (isset($_GET['cancel'])) {
    unset($_SESSION['pending_register'], $_SESSION['reset_email'], $_SESSION['reset_code'], $_SESSION['reset_expires'], $_SESSION['reset_attempts'], $_SESSION['reset_verified']);
    header('Location: auth.php'); exit;
}

$error   = '';
$success = '';
$mode = 'login';

// Determine initial mode based on session state
if (isset($_SESSION['pending_register']) && $_SESSION['pending_register']) {
    $mode = 'verify';
} elseif (isset($_SESSION['2fa_user']) && $_SESSION['2fa_user']) {
    $mode = 'login_verify';
} elseif (isset($_SESSION['reset_email'])) {
    if (isset($_SESSION['reset_verified']) && $_SESSION['reset_verified']) {
        $mode = 'new_password';
    } else {
        $mode = 'forgot_verify';
    }
}

function generateCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function codeExpired($type = 'register') {
    if ($type === 'register') {
        return !isset($_SESSION['code_expires']) || time() > $_SESSION['code_expires'];
    } elseif ($type === 'reset') {
        return !isset($_SESSION['reset_expires']) || time() > $_SESSION['reset_expires'];
    } elseif ($type === '2fa') {
        return !isset($_SESSION['2fa_expires']) || time() > $_SESSION['2fa_expires'];
    }
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'login';

    if ($mode === 'forgot') {
        $email = trim($_POST['email'] ?? '');
        if (empty($email)) {
            $error = 'Email is required.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM Users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $code = generateCode();
                $_SESSION['reset_email']   = $email;
                $_SESSION['reset_code']    = $code;
                $_SESSION['reset_expires'] = time() + 600;
                $_SESSION['reset_attempts'] = 0;
                $_SESSION['reset_verified'] = false;

                sendEmail(
                    $email,
                    $email,
                    'Password Reset Code',
                    "
                    <div style='font-family: Arial, sans-serif; max-width: 500px; margin: auto; padding: 20px;'>
                        <div style='text-align:center; margin-bottom: 24px;'>
                            <img src='https://ghos.shop/images/logo/logo2.png' alt='Ghos Logo' style='height: 80px; border-radius: 8px;'>
                        </div>
                        <h2 style='color:#1a1a1a;'>Reset your password</h2>
                        <p style='color:#555; font-size:15px;'>Use the code below to reset your password. It expires in <strong>10 minutes</strong>.</p>
                        <div style='text-align:center; margin: 28px 0;'>
                            <div style='display:inline-block; background:#f4f4f4; border:2px dashed #ccc; border-radius:12px; padding:18px 36px;'>
                                <span style='font-size:36px; font-weight:bold; letter-spacing:10px; color:#1a1a1a;'>$code</span>
                            </div>
                        </div>
                        <hr style='border:none; border-top:1px solid #e0e0e0; margin:28px 0 16px;'>
                        <p style='font-size:12px; color:#999; margin:0;'>GameHub Online Store — <a href='https://ghos.shop' style='color:#999;'>ghos.shop</a></p>
                    </div>"
                );
                $mode = 'forgot_verify';
            } else {
                $success = 'If an account exists with that email, a code has been sent.';
                $mode = 'login';
            }
        }

    } elseif ($mode === 'forgot_verify') {
        $entered = trim($_POST['code'] ?? '');
        if (!isset($_SESSION['reset_email'])) {
            $error = 'Session expired.';
            $mode = 'login';
        } elseif (codeExpired('reset')) {
            unset($_SESSION['reset_email'], $_SESSION['reset_code'], $_SESSION['reset_expires'], $_SESSION['reset_attempts']);
            $error = 'Code expired.';
            $mode = 'login';
        } elseif ($_SESSION['reset_attempts'] >= 5) {
            unset($_SESSION['reset_email'], $_SESSION['reset_code'], $_SESSION['reset_expires'], $_SESSION['reset_attempts']);
            $error = 'Too many attempts.';
            $mode = 'login';
        } elseif ($entered !== $_SESSION['reset_code']) {
            $_SESSION['reset_attempts']++;
            $error = 'Invalid code.';
            $mode = 'forgot_verify';
        } else {
            $_SESSION['reset_verified'] = true;
            $mode = 'new_password';
        }

    } elseif ($mode === 'reset_password') {
        $pass = $_POST['password'] ?? '';
        $repeat = $_POST['repeat_password'] ?? '';

        if (!isset($_SESSION['reset_email']) || !$_SESSION['reset_verified']) {
            $error = 'Unauthorized access.';
            $mode = 'login';
        } elseif (strlen($pass) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif (!preg_match('/[0-9]/', $pass)) {
            $error = 'Password must contain at least one number.';
        } elseif (!preg_match('/[^a-zA-Z0-9]/', $pass)) {
            $error = 'Password must contain at least one special character.';
        } elseif ($pass !== $repeat) {
            $error = 'Passwords do not match.';
        } else {
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('UPDATE Users SET password = ? WHERE email = ?');
            $stmt->execute([$hash, $_SESSION['reset_email']]);
            
            unset($_SESSION['reset_email'], $_SESSION['reset_code'], $_SESSION['reset_expires'], $_SESSION['reset_attempts'], $_SESSION['reset_verified']);
            $success = 'Password reset successfully! You can now log in.';
            $mode = 'login';
        }

    } elseif ($mode === 'register') {
        $email  = trim($_POST['email'] ?? '');
        $pass   = $_POST['password'] ?? '';
        $repeat = $_POST['repeat_password'] ?? '';

        if (empty($email) || empty($pass) || empty($repeat)) {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } elseif (strlen($pass) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif (!preg_match('/[0-9]/', $pass)) {
            $error = 'Password must contain at least one number.';
        } elseif (!preg_match('/[^a-zA-Z0-9]/', $pass)) {
            $error = 'Password must contain at least one special character.';
        } elseif ($pass !== $repeat) {
            $error = 'Passwords do not match.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM Users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'An account with that email already exists.';
            } else {
                $code = generateCode();
                $_SESSION['pending_register'] = [
                    'email' => $email,
                    'hash'  => password_hash($pass, PASSWORD_BCRYPT),
                ];
                $_SESSION['verify_code']    = $code;
                $_SESSION['code_expires']   = time() + 600;
                $_SESSION['code_attempts']  = 0;

               sendEmail(
                    $email,
                    $email,
                    'Your GameHub verification code',
                    "
                    <div style='font-family: Arial, sans-serif; max-width: 500px; margin: auto; padding: 20px;'>
                        <div style='text-align:center; margin-bottom: 24px;'>
                            <img src='https://ghos.shop/images/logo/logo2.png' alt='Ghos Logo' style='height: 80px; border-radius: 8px;'>
                        </div>
                        <h2 style='color:#1a1a1a; margin-bottom:8px;'>Verify your email</h2>
                        <p style='color:#555; font-size:15px;'>Use the code below to complete your registration. It expires in <strong>10 minutes</strong>.</p>
                        <div style='text-align:center; margin: 28px 0;'>
                            <div style='display:inline-block; background:#f4f4f4; border:2px dashed #ccc; border-radius:12px; padding:18px 36px;'>
                                <span style='font-size:36px; font-weight:bold; letter-spacing:10px; color:#1a1a1a;'>$code</span>
                            </div>
                        </div>
                        <p style='color:#999; font-size:13px;'>If you did not try to register on GameHub, you can safely ignore this email.</p>
                        <hr style='border:none; border-top:1px solid #e0e0e0; margin:28px 0 16px;'>
                        <p style='font-size:12px; color:#999; margin:0;'>GameHub Online Store — <a href='https://ghos.shop' style='color:#999;'>ghos.shop</a></p>
                    </div>"
                );
                $mode = 'verify';
            }
        }

    } elseif ($mode === 'verify') {
        $entered = trim($_POST['code'] ?? '');

        if (!isset($_SESSION['pending_register'])) {
            $error = 'Session expired. Please register again.';
            $mode  = 'register';
        } elseif (codeExpired('register')) {
            unset($_SESSION['pending_register'], $_SESSION['verify_code'], $_SESSION['code_expires'], $_SESSION['code_attempts']);
            $error = 'Code expired. Please register again.';
            $mode  = 'register';
        } elseif ($_SESSION['code_attempts'] >= 5) {
            unset($_SESSION['pending_register'], $_SESSION['verify_code'], $_SESSION['code_expires'], $_SESSION['code_attempts']);
            $error = 'Too many wrong attempts. Please register again.';
            $mode  = 'register';
        } elseif ($entered !== $_SESSION['verify_code']) {
            $_SESSION['code_attempts']++;
            $left  = 5 - $_SESSION['code_attempts'];
            $error = "Wrong code. $left attempt(s) remaining.";
            $mode  = 'verify';
        } else {
            $pending = $_SESSION['pending_register'];
            $email   = $pending['email'];
            $hash    = $pending['hash'];

            $stmt = $pdo->prepare('SELECT id FROM Users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'An account with that email already exists.';
                $mode  = 'register';
            } else {
                $ins = $pdo->prepare('INSERT INTO Users (email, password, role) VALUES (?, ?, ?)');
                $ins->execute([$email, $hash, 'user']);
                $new_user_id = $pdo->lastInsertId();

                $claim_stmt = $pdo->prepare('UPDATE Orders SET user_id = ? WHERE guest_email = ? AND user_id IS NULL');
                $claim_stmt->execute([$new_user_id, $email]);

                unset($_SESSION['pending_register'], $_SESSION['verify_code'], $_SESSION['code_expires'], $_SESSION['code_attempts']);
                $success = 'Account created! You can now log in.';
                $mode    = 'login';
            }
        }

    } elseif ($mode === 'resend') {
        if (!isset($_SESSION['pending_register'])) {
            $error = 'Session expired. Please register again.';
            $mode  = 'register';
        } else {
            $email = $_SESSION['pending_register']['email'];
            $code  = generateCode();
            $_SESSION['verify_code']   = $code;
            $_SESSION['code_expires']  = time() + 600;
            $_SESSION['code_attempts'] = 0;

            sendEmail(
                $email,
                $email,
                'Your new GameHub verification code',
                "
                <div style='font-family: Arial, sans-serif; max-width: 500px; margin: auto; padding: 20px;'>
                    <div style='text-align:center; margin-bottom: 24px;'>
                        <img src='https://ghos.shop/images/logo/logo2.png' alt='Ghos Logo' style='height: 80px; border-radius: 8px;'>
                    </div>
                    <h2 style='color:#1a1a1a;'>New Verification Code</h2>
                    <p style='color:#555; font-size:15px;'>Here is your new code. It expires in <strong>10 minutes</strong>.</p>
                    <div style='text-align:center; margin: 28px 0;'>
                        <div style='display:inline-block; background:#f4f4f4; border:2px dashed #ccc; border-radius:12px; padding:18px 36px;'>
                            <span style='font-size:36px; font-weight:bold; letter-spacing:10px; color:#1a1a1a;'>$code</span>
                        </div>
                    </div>
                    <hr style='border:none; border-top:1px solid #e0e0e0; margin:28px 0 16px;'>
                    <p style='font-size:12px; color:#999; margin:0;'>GameHub Online Store — <a href='https://ghos.shop' style='color:#999;'>ghos.shop</a></p>
                </div>"
            );

            $success = 'A new code has been sent to your email.';
            $mode    = 'verify';
        }

    } elseif ($mode === 'login_verify') {
        $entered = trim($_POST['code'] ?? '');

        if (!isset($_SESSION['2fa_user'])) {
            $error = 'Session expired. Please log in again.';
            $mode  = 'login';
        } elseif (codeExpired('2fa')) {
            unset($_SESSION['2fa_user'], $_SESSION['2fa_code'], $_SESSION['2fa_expires'], $_SESSION['2fa_attempts']);
            $error = 'Code expired. Please log in again.';
            $mode  = 'login';
        } elseif ($_SESSION['2fa_attempts'] >= 5) {
            unset($_SESSION['2fa_user'], $_SESSION['2fa_code'], $_SESSION['2fa_expires'], $_SESSION['2fa_attempts']);
            $error = 'Too many wrong attempts. Please log in again.';
            $mode  = 'login';
        } elseif ($entered !== $_SESSION['2fa_code']) {
            $_SESSION['2fa_attempts']++;
            $left  = 5 - $_SESSION['2fa_attempts'];
            $error = "Wrong code. $left attempt(s) remaining.";
            $mode  = 'login_verify';
        } else {
            $user = $_SESSION['2fa_user'];
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role']       = $user['role'];

            // Transfer cart items from guest session to user account
            $session_id = session_id();
            $stmt_transfer = $pdo->prepare("UPDATE Cart SET user_id = ?, session_id = NULL WHERE session_id = ? AND user_id IS NULL");
            $stmt_transfer->execute([$user['id'], $session_id]);

            unset($_SESSION['2fa_user'], $_SESSION['2fa_code'], $_SESSION['2fa_expires'], $_SESSION['2fa_attempts']);
            header('Location: index.php');
            exit;
        }

    } elseif ($mode === 'login') {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        if (empty($email) || empty($pass)) {
            $error = 'Email and password are required.';
        } else {
            $stmt = $pdo->prepare('SELECT id, email, password, role, is_active, 2fa_enabled FROM Users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($pass, $user['password'])) {
                if ((int)$user['is_active'] === 0) {
                    $error = 'Your account has been suspended. Please contact us via Discord or email.';
                } else {
                    if (!empty($user['2fa_enabled']) && (int)$user['2fa_enabled'] === 1) {
                        $code = generateCode();
                        $_SESSION['2fa_user'] = [
                            'id'    => $user['id'],
                            'email' => $user['email'],
                            'role'  => $user['role']
                        ];
                        $_SESSION['2fa_code'] = $code;
                        $_SESSION['2fa_expires'] = time() + 600;
                        $_SESSION['2fa_attempts'] = 0;

                        sendEmail(
                            $user['email'],
                            $user['email'],
                            'Your Login Verification Code',
                            "<h2>Your login code is:</h2><h1>$code</h1><p>Expires in 10 minutes.</p>"
                        );
                        $mode = 'login_verify';
                    } else {
                        $_SESSION['user_id']    = $user['id'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['role']       = $user['role'];

                        // Transfer cart items from guest session to user account
                        $session_id = session_id();
                        $stmt_transfer = $pdo->prepare("UPDATE Cart SET user_id = ?, session_id = NULL WHERE session_id = ? AND user_id IS NULL");
                        $stmt_transfer->execute([$user['id'], $session_id]);

                        header('Location: index.php');
                        exit;
                    }
                }
            } else {
                $error = 'Invalid email or password.';
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
<title>GameHub — Sign In / Register</title>
<link rel="icon" type="image/png" href="images/logo/logo2.png">
<link rel="stylesheet" href="css/auth.css">
<style>
    .tabs { display: flex; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; margin-bottom: 20px; }
    .tab-btn { flex: 1; padding: 9px; border: none; background: #f9f9f9; font-size: 13px; font-weight: bold; color: #777777; cursor: pointer; }
    .tab-btn.active { background: #1a1a1a; color: white; }
    .alert { font-size: 13px; padding: 10px 12px; border-radius: 8px; margin-bottom: 14px; }
    .alert-error   { background: #fff0f0; border: 1px solid #fca5a5; color: #b91c1c; }
    .alert-success { background: #f0fdf4; border: 1px solid #86efac; color: #15803d; }
    .form-section        { display: none; }
    .form-section.active { display: block; }
    .strength-wrap { margin-top: -8px; margin-bottom: 16px; }
    .strength-bar-track { height: 5px; background: #e0e0e0; border-radius: 99px; overflow: hidden; margin-bottom: 6px; }
    .strength-bar-fill { height: 100%; width: 0%; border-radius: 99px; transition: width 0.3s ease, background-color 0.3s ease; }
    .strength-label { font-size: 12px; font-weight: bold; margin-bottom: 6px; color: #999; }
    .strength-hints { display: flex; flex-wrap: wrap; gap: 5px; }
    .hint { font-size: 11px; padding: 3px 8px; border-radius: 99px; background: #f0f0f0; color: #999; transition: all 0.2s; }
    .hint.met { background: #dcfce7; color: #15803d; }
    .code-input-wrap { display: flex; gap: 8px; justify-content: center; margin: 20px 0; }
    .code-digit { width: 44px; height: 52px; text-align: center; font-size: 22px; font-weight: bold; border: 2px solid #e0e0e0; border-radius: 8px; outline: none; transition: border-color 0.2s; }
    .code-digit:focus { border-color: #1a1a1a; }
    .code-meta { text-align: center; font-size: 13px; color: #888; margin-bottom: 16px; }
    .resend-link { background: none; border: none; color: #555; font-size: 13px; cursor: pointer; text-decoration: underline; padding: 0; }
    .resend-link:hover { color: #1a1a1a; }
    #countdown { font-weight: bold; color: #1a1a1a; }
    .password-wrap { position:relative !important; margin-bottom:16px !important; background:none !important; border:none !important; padding:0 !important; box-shadow:none !important; }
    .password-wrap input[type="password"], .password-wrap input[type="text"] { margin-bottom:0 !important; padding-right:42px !important; width:100% !important; box-sizing:border-box !important; }
    .toggle-password { position:absolute !important; right:12px !important; top:50% !important; transform:translateY(-50%) !important; background:none !important; border:none !important; color:#aaaaaa !important; cursor:pointer !important; padding:0 !important; margin:0 !important; width:auto !important; height:auto !important; display:flex !important; align-items:center !important; line-height:0 !important; box-shadow:none !important; border-radius:0 !important; outline:none !important; }
    .toggle-password:hover { color:#555555 !important; background:none !important; }
</style>
</head>
<body>

<?php if ($error && strpos($error, 'suspended') !== false): ?>
<div style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex; align-items:center;justify-content:center;z-index:9999;">
    <div style="background:white;padding:20px;border-radius:10px;text-align:center;width:300px;">
        <h3>Account Suspended</h3>
        <p style="font-size:14px;">Please contact us via Discord or email.</p>
        <button onclick="this.closest('div').parentElement.remove()" style="margin-top:10px;padding:6px 15px;">OK</button>
    </div>
</div>
<?php endif; ?>

<div class="auth-logo">
    <img src="images/logo/logo2.png" alt="Ghos Logo" style="height: 100px; width: auto; margin-bottom: 20px; border-radius: 12px;">
    <h1 id="page-title">
        <?php
        if ($mode === 'register') echo 'Create Account';
        elseif ($mode === 'verify') echo 'Verify Email';
        elseif ($mode === 'login_verify') echo 'Two-Factor Auth';
        elseif ($mode === 'forgot') echo 'Forgot Password';
        elseif ($mode === 'forgot_verify') echo 'Verify Code';
        elseif ($mode === 'new_password') echo 'New Password';
        else echo 'Welcome Back';
        ?>
    </h1>
    <p id="page-sub">
        <?php
        if ($mode === 'register') echo 'Join GameHub Online Store today';
        elseif ($mode === 'verify') echo 'Enter the code sent to your email';
        elseif ($mode === 'login_verify') echo 'Enter the code sent to your email';
        elseif ($mode === 'forgot') echo 'Enter your email to receive a reset code';
        elseif ($mode === 'forgot_verify') echo 'Enter the code sent to your email';
        elseif ($mode === 'new_password') echo 'Choose a strong new password';
        else echo 'Sign in to your GameHub account';
        ?>
    </p>
</div>

<div class="auth-card">
    <?php if (!in_array($mode, ['verify', 'login_verify', 'forgot_verify', 'new_password'])): ?>
    <div class="tabs">
        <button class="tab-btn <?= in_array($mode, ['login', 'forgot']) ? 'active' : '' ?>" onclick="switchMode('login')"    type="button" id="tab-login">Login</button>
        <button class="tab-btn <?= $mode === 'register' ? 'active' : '' ?>" onclick="switchMode('register')" type="button" id="tab-register">Register</button>
    </div>
    <?php endif; ?>

    <?php if ($error):   ?><div class="alert alert-error"  ><?= htmlspecialchars($error)   ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <!-- Login Panel -->
    <div class="form-section <?= $mode === 'login' ? 'active' : '' ?>" id="panel-login">
        <form method="POST" action="auth.php">
            <input type="hidden" name="mode" value="login">
            <label>Email</label>
            <input type="email" name="email" placeholder="your.email@example.com" required value="<?= $mode === 'login' ? htmlspecialchars($_POST['email'] ?? '') : '' ?>">
            
            <label>Password</label>
            <div class="password-wrap">
                <input type="password" name="password" placeholder="••••••••" required>
                <button type="button" class="toggle-password" onclick="togglePass(this)">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </button>
            </div>
            
            <div style="text-align: right; margin-top: -10px; margin-bottom: 20px;">
                <a href="#" onclick="switchMode('forgot')" style="font-size: 13px; color: #555; text-decoration: none;">Forgot Password?</a>
            </div>

            <button class="auth-btn" type="submit">Login</button>
        </form>
        <div class="auth-link">Don't have an account? <a onclick="switchMode('register')" href="#">Register here</a></div>
    </div>

    <!-- Register Panel -->
    <div class="form-section <?= $mode === 'register' ? 'active' : '' ?>" id="panel-register">
        <form method="POST" action="auth.php">
            <input type="hidden" name="mode" value="register">
            <label>Email</label>
            <input type="email" name="email" placeholder="your.email@example.com" required value="<?= $mode === 'register' ? htmlspecialchars($_POST['email'] ?? '') : '' ?>">
            
            <label>Password</label>
            <div class="password-wrap">
                <input type="password" name="password" id="reg-password" placeholder="Min. 8 characters" required oninput="checkStrength(this.value)">
                <button type="button" class="toggle-password" onclick="togglePass(this)">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </button>
            </div>
            
            <div class="strength-wrap" style="margin-top: 8px;">
                <div class="strength-bar-track">
                    <div class="strength-bar-fill" id="strength-bar"></div>
                </div>
                <div class="strength-label" id="strength-label">Enter a password</div>
                <div class="strength-hints">
                    <span class="hint" id="hint-length">8+ characters</span>
                    <span class="hint" id="hint-number">Number</span>
                    <span class="hint" id="hint-special">Special character</span>
                    <span class="hint" id="hint-upper">Uppercase</span>
                </div>
            </div>
            
            <label>Repeat Password</label>
            <div class="password-wrap">
                <input type="password" name="repeat_password" placeholder="••••••••" required>
                <button type="button" class="toggle-password" onclick="togglePass(this)">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </button>
            </div>

            <button class="auth-btn" type="submit" style="margin-top: 15px;">Send Verification Code</button>
        </form>
        <div class="auth-link">Already have an account? <a onclick="switchMode('login')" href="#">Login here</a></div>
    </div>

    <!-- Forgot Request Panel -->
    <div class="form-section <?= $mode === 'forgot' ? 'active' : '' ?>" id="panel-forgot">
        <form method="POST" action="auth.php">
            <input type="hidden" name="mode" value="forgot">
            <label>Email</label>
            <input type="email" name="email" placeholder="your.email@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            
            <button class="auth-btn" type="submit" style="margin-top: 10px;">Send Reset Code</button>
        </form>
        <div style="text-align:center; margin-top:14px;">
            <a href="#" onclick="switchMode('login')" style="font-size:13px; color:#555;">← Back to Login</a>
        </div>
    </div>

    <!-- Forgot Verify Panel -->
    <?php if ($mode === 'forgot_verify'): ?>
    <div class="form-section active" id="panel-forgot-verify">
        <p style="text-align:center; font-size:14px; color:#555; margin-bottom:4px;">
            Code sent to <strong><?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?></strong>
        </p>
        <div class="code-meta">
            Expires in <span id="countdown"></span>
        </div>

        <form method="POST" action="auth.php" id="forgot-verify-form">
            <input type="hidden" name="mode" value="forgot_verify">
            <input type="hidden" name="code" id="code-hidden">
            <div class="code-input-wrap">
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
            </div>
            <button class="auth-btn" type="submit" id="verify-btn" disabled>Verify Code</button>
        </form>

        <div style="text-align:center; margin-top:14px;">
            <a href="?cancel=1" style="font-size:13px; color:#555;">Start over</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- New Password Panel -->
    <?php if ($mode === 'new_password'): ?>
    <div class="form-section active" id="panel-new-password">
        <form method="POST" action="auth.php">
            <input type="hidden" name="mode" value="reset_password">
            <label>New Password</label>
            <div class="password-wrap">
                <input type="password" name="password" id="reset-password" placeholder="Min. 8 characters" required oninput="checkStrength(this.value)">
                <button type="button" class="toggle-password" onclick="togglePass(this)">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </button>
            </div>

            <div class="strength-wrap" style="margin-top: 8px;">
                <div class="strength-bar-track">
                    <div class="strength-bar-fill" id="strength-bar"></div>
                </div>
                <div class="strength-label" id="strength-label">Enter a password</div>
            </div>
            
            <label>Repeat New Password</label>
            <div class="password-wrap">
                <input type="password" name="repeat_password" placeholder="••••••••" required>
                <button type="button" class="toggle-password" onclick="togglePass(this)">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </button>
            </div>

            <button class="auth-btn" type="submit" style="margin-top: 15px;">Reset Password</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- 2FA Panel -->
    <?php if ($mode === 'login_verify'): ?>
    <div class="form-section active" id="panel-login-verify">
        <p style="text-align:center; font-size:14px; color:#555; margin-bottom:4px;">
            Code sent to <strong><?= htmlspecialchars($_SESSION['2fa_user']['email'] ?? '') ?></strong>
        </p>
        <div class="code-meta">
            Expires in <span id="countdown"></span>
        </div>

        <form method="POST" action="auth.php" id="login-verify-form">
            <input type="hidden" name="mode" value="login_verify">
            <input type="hidden" name="code" id="code-hidden">
            <div class="code-input-wrap">
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
            </div>
            <button class="auth-btn" type="submit" id="verify-btn" disabled>Verify &amp; Login</button>
        </form>

        <div style="text-align:center; margin-top:14px;">
            <a href="?cancel=1" style="font-size:13px; color:#555;">← Back to Login</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Registration Verify Panel -->
    <?php if ($mode === 'verify'): ?>
    <div class="form-section active" id="panel-verify">
        <p style="text-align:center; font-size:14px; color:#555; margin-bottom:4px;">
            Code sent to <strong><?= htmlspecialchars($_SESSION['pending_register']['email'] ?? '') ?></strong>
        </p>
        <div class="code-meta">
            Expires in <span id="countdown"></span>
        </div>

        <form method="POST" action="auth.php" id="verify-form">
            <input type="hidden" name="mode" value="verify">
            <input type="hidden" name="code" id="code-hidden">
            <div class="code-input-wrap">
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
            </div>
            <button class="auth-btn" type="submit" id="verify-btn" disabled>Verify &amp; Create Account</button>
        </form>

        <div style="text-align:center; margin-top:14px;">
            <form method="POST" action="auth.php" style="display:inline;">
                <input type="hidden" name="mode" value="resend">
                <button type="submit" class="resend-link">Resend code</button>
            </form>
            &nbsp;·&nbsp;
            <a href="?cancel=1" style="font-size:13px; color:#555;">Start over</a>
        </div>
    </div>
    <?php endif; ?>

</div>

<a href="index.php" class="back-link">← Back to Store</a>

<script>
function switchMode(mode) {
    const labels = {
        login:    { h1: 'Welcome Back',   p: 'Sign in to your GameHub account' },
        register: { h1: 'Create Account', p: 'Join GameHub Online Store today'  },
        forgot:   { h1: 'Forgot Password', p: 'Enter your email to receive a reset code' }
    };
    
    // Toggle active panels
    const panels = ['login', 'register', 'forgot'];
    panels.forEach(p => {
        const el = document.getElementById('panel-' + p);
        if (el) el.classList.toggle('active', p === mode);
    });

    // Toggle active tabs
    const tabLogin = document.getElementById('tab-login');
    const tabRegister = document.getElementById('tab-register');
    if (tabLogin) tabLogin.classList.toggle('active', mode === 'login' || mode === 'forgot');
    if (tabRegister) tabRegister.classList.toggle('active', mode === 'register');

    // Update text
    if (labels[mode]) {
        document.getElementById('page-title').textContent = labels[mode].h1;
        document.getElementById('page-sub').textContent   = labels[mode].p;
    }
}

function checkStrength(val) {
    const bar   = document.getElementById('strength-bar');
    const label = document.getElementById('strength-label');
    const hasLength  = val.length >= 8;
    const hasNumber  = /[0-9]/.test(val);
    const hasSpecial = /[^a-zA-Z0-9]/.test(val);
    const hasUpper   = /[A-Z]/.test(val);
    
    const hints = {
        'hint-length': hasLength,
        'hint-number': hasNumber,
        'hint-special': hasSpecial,
        'hint-upper': hasUpper
    };

    for (const [id, met] of Object.entries(hints)) {
        const el = document.getElementById(id);
        if (el) el.classList.toggle('met', met);
    }

    const score  = [hasLength, hasNumber, hasSpecial, hasUpper].filter(Boolean).length;
    const levels = [
        { pct: '0%',   color: '#e0e0e0', text: 'Enter a password', textColor: '#999'    },
        { pct: '25%',  color: '#ef4444', text: 'Weak',             textColor: '#ef4444' },
        { pct: '50%',  color: '#f97316', text: 'Fair',             textColor: '#f97316' },
        { pct: '75%',  color: '#eab308', text: 'Medium',           textColor: '#eab308' },
        { pct: '100%', color: '#22c55e', text: 'Strong',           textColor: '#22c55e' },
    ];
    const level = val.length === 0 ? levels[0] : levels[score];
    if (bar) {
        bar.style.width = level.pct;
        bar.style.backgroundColor = level.color;
    }
    if (label) {
        label.textContent = level.text;
        label.style.color = level.textColor;
    }
}

const eyeOpen = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
const eyeClosed = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;

function togglePass(btn) {
    const input = btn.previousElementSibling;
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = eyeClosed;
    } else {
        input.type = 'password';
        btn.innerHTML = eyeOpen;
    }
}

const digits  = document.querySelectorAll('.code-digit');
const hidden  = document.getElementById('code-hidden');
const btn     = document.getElementById('verify-btn');

if (digits.length) {
    digits.forEach((el, i) => {
        el.addEventListener('input', () => {
            el.value = el.value.replace(/[^0-9]/g, '').slice(-1);
            if (el.value && i < digits.length - 1) digits[i + 1].focus();
            syncCode();
        });
        el.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !el.value && i > 0) digits[i - 1].focus();
        });
        el.addEventListener('paste', e => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').slice(0, 6);
            [...pasted].forEach((ch, j) => { if (digits[j]) digits[j].value = ch; });
            if (digits[pasted.length - 1]) digits[pasted.length - 1].focus();
            syncCode();
        });
    });

    function syncCode() {
        const code = [...digits].map(d => d.value).join('');
        if (hidden) hidden.value = code;
        if (btn) btn.disabled = code.length < 6;
    }
}

const countdownEl = document.getElementById('countdown');
if (countdownEl) {
    const expires = <?= 
        isset($_SESSION['code_expires']) ? (int)$_SESSION['code_expires'] : 
        (isset($_SESSION['2fa_expires']) ? (int)$_SESSION['2fa_expires'] : 
        (isset($_SESSION['reset_expires']) ? (int)$_SESSION['reset_expires'] : 0)) 
    ?>;
    function tick() {
        const left = expires - Math.floor(Date.now() / 1000);
        if (left <= 0) {
            countdownEl.textContent = '00:00';
            countdownEl.style.color = '#ef4444';
            if (btn) btn.disabled = true;
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
