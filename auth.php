<?php
session_start();
require_once 'php/db_connect.php';
require_once 'sendEMail.php';

$error   = '';
$success = '';

// Determine initial mode
if (isset($_SESSION['pending_2fa'])) {
    $mode = 'verify_2fa';
} elseif (isset($_SESSION['pending_register'])) {
    $mode = 'verify_reg';
} else {
    $mode = 'login';
}

function generateCode() { return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT); }
function codeExpired()  { return !isset($_SESSION['code_expires']) || time() > $_SESSION['code_expires']; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'login';

    // ── REGISTER ──────────────────────────────────
    if ($mode === 'register') {
        $email  = trim($_POST['email'] ?? '');
        $pass   = $_POST['password'] ?? '';
        $repeat = $_POST['repeat_password'] ?? '';

        if (empty($email) || empty($pass) || empty($repeat)) {
            $error = 'All fields are required.';
        } elseif ($pass !== $repeat) {
            $error = 'Passwords do not match.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM Users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already exists.';
            } else {
                $code = generateCode();
                $_SESSION['pending_register'] = ['email' => $email, 'hash' => password_hash($pass, PASSWORD_BCRYPT)];
                $_SESSION['verify_code']  = $code;
                $_SESSION['code_expires'] = time() + 600;
                
                sendEmail($email, $email, 'Verification Code', "Your registration code is: $code");
                $mode = 'verify_reg';
            }
        }

    // ── LOGIN (With 2FA Check) ────────────────────
    } elseif ($mode === 'login') {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        
        $stmt = $pdo->prepare('SELECT id, email, password, role, is_active, two_factor_enabled FROM Users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password'])) {
            if ((int)$user['is_active'] === 0) {
                $error = 'Your account is suspended.';
            } else {
                // Check if 2FA is enabled
                if (isset($user['two_factor_enabled']) && $user['two_factor_enabled'] == 1) {
                    $code = generateCode();
                    $_SESSION['pending_2fa'] = [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ];
                    $_SESSION['verify_code']  = $code;
                    $_SESSION['code_expires'] = time() + 600;
                    
                    sendEmail($user['email'], $user['email'], 'Login Verification', "Your login code is: $code");
                    $mode = 'verify_2fa';
                } else {
                    // Direct login
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role']    = $user['role'];
                    header('Location: index.php'); exit;
                }
            }
        } else {
            $error = 'Invalid email or password.';
        }

    // ── VERIFY REGISTER ───────────────────────────
    } elseif ($mode === 'verify_reg') {
        $entered = trim($_POST['code'] ?? '');
        if ($entered === $_SESSION['verify_code'] && !codeExpired()) {
            $pending = $_SESSION['pending_register'];
            $ins = $pdo->prepare('INSERT INTO Users (email, password, role) VALUES (?, ?, "user")');
            $ins->execute([$pending['email'], $pending['hash']]);
            unset($_SESSION['pending_register'], $_SESSION['verify_code']);
            $success = 'Account created! Please login.'; $mode = 'login';
        } else {
            $error = 'Invalid or expired code.'; $mode = 'verify_reg';
        }

    // ── VERIFY 2FA (Login) ────────────────────────
    } elseif ($mode === 'verify_2fa') {
        $entered = trim($_POST['code'] ?? '');
        if ($entered === $_SESSION['verify_code'] && !codeExpired()) {
            $user = $_SESSION['pending_2fa'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            unset($_SESSION['pending_2fa'], $_SESSION['verify_code']);
            header('Location: index.php'); exit;
        } else {
            $error = 'Invalid or expired code.'; $mode = 'verify_2fa';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auth — Ghos</title>
    <link rel="stylesheet" href="css/auth.css">
    <style>
        .tabs { display:flex; border-radius:8px; overflow:hidden; margin-bottom:20px; border:1px solid #ddd; }
        .tab-btn { flex:1; padding:10px; border:none; cursor:pointer; background:#f5f5f5; font-weight:bold; }
        .tab-btn.active { background:#1a1a1a; color:white; }
        .form-section { display:none; }
        .form-section.active { display:block; }
        .alert { padding:10px; border-radius:5px; margin-bottom:15px; font-size:14px; }
        .alert-error { background:#fee2e2; color:#dc2626; }
        .alert-success { background:#dcfce7; color:#16a34a; }
        .code-input-wrap { display:flex; gap:10px; justify-content:center; margin:20px 0; }
        .code-digit { width:45px; height:50px; text-align:center; font-size:24px; font-weight:bold; border:2px solid #ddd; border-radius:8px; }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="auth-logo"><div class="logo-box">Ghos</div></div>
    
    <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

    <div class="form-section <?= $mode === 'login' ? 'active' : '' ?>" id="panel-login">
        <div class="tabs">
            <button class="tab-btn active">Login</button>
            <button class="tab-btn" onclick="switchMode('register')">Register</button>
        </div>
        <form method="POST">
            <input type="hidden" name="mode" value="login">
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <button type="submit" class="auth-btn">Sign In</button>
        </form>
    </div>

    <div class="form-section <?= $mode === 'register' ? 'active' : '' ?>" id="panel-register">
        <div class="tabs">
            <button class="tab-btn" onclick="switchMode('login')">Login</button>
            <button class="tab-btn active">Register</button>
        </div>
        <form method="POST">
            <input type="hidden" name="mode" value="register">
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <label>Repeat Password</label>
            <input type="password" name="repeat_password" required>
            <button type="submit" class="auth-btn">Create Account</button>
        </form>
    </div>

    <?php if ($mode === 'verify_reg' || $mode === 'verify_2fa'): ?>
    <div class="form-section active">
        <h2 style="text-align:center;">Verify Identity</h2>
        <p style="text-align:center; color:#666;">Enter the 6-digit code sent to your email.</p>
        <form method="POST" id="verify-form">
            <input type="hidden" name="mode" value="<?= $mode ?>">
            <input type="hidden" name="code" id="code-hidden">
            <div class="code-input-wrap">
                <?php for($i=0;$i<6;$i++): ?>
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric">
                <?php endfor; ?>
            </div>
            <button type="submit" class="auth-btn">Verify Code</button>
        </form>
        <div style="text-align:center; margin-top:15px;">
            <a href="auth.php" style="color:#666; font-size:13px;">Cancel and Start Over</a>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
    function switchMode(m) {
        document.getElementById('panel-login').classList.toggle('active', m === 'login');
        document.getElementById('panel-register').classList.toggle('active', m === 'register');
    }

    // Code Digit Auto-focus logic
    const digits = document.querySelectorAll('.code-digit');
    digits.forEach((d, i) => {
        d.addEventListener('input', () => {
            if (d.value && i < 5) digits[i+1].focus();
            syncCode();
        });
        d.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !d.value && i > 0) digits[i-1].focus();
        });
    });

    function syncCode() {
        const code = Array.from(digits).map(d => d.value).join('');
        document.getElementById('code-hidden').value = code;
    }
</script>
</body>
</html>
