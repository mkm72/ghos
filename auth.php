<?php
session_start();
require_once 'php/db_connect.php';
require_once 'sendEMail.php';

$error   = '';
$success = '';

// Determine Mode
if (isset($_SESSION['pending_2fa'])) {
    $mode = 'verify_2fa';
} elseif (isset($_SESSION['pending_register'])) {
    $mode = 'verify';
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
                $error = 'An account with that email already exists.';
            } else {
                $code = generateCode();
                $_SESSION['pending_register'] = ['email' => $email, 'hash' => password_hash($pass, PASSWORD_BCRYPT)];
                $_SESSION['verify_code']      = $code;
                $_SESSION['code_expires']     = time() + 600;
                $_SESSION['code_attempts']    = 0;
                sendEmail($email, $email, 'Your Verification Code', "Your code is: $code");
                $mode = 'verify';
            }
        }

    // ── LOGIN (With 2FA Check) ────────────────────
    } elseif ($mode === 'login') {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        if (empty($email) || empty($pass)) {
            $error = 'Email and password are required.';
        } else {
            $stmt = $pdo->prepare('SELECT id, email, password, role, is_active, two_factor_enabled FROM Users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($pass, $user['password'])) {
                if ((int)$user['is_active'] === 0) {
                    $error = 'Your account has been suspended.';
                } else {
                    // Check 2FA Status
                    if (isset($user['two_factor_enabled']) && $user['two_factor_enabled'] == 1) {
                        $code = generateCode();
                        $_SESSION['pending_2fa'] = [
                            'id' => $user['id'],
                            'email' => $user['email'],
                            'role' => $user['role']
                        ];
                        $_SESSION['verify_code']  = $code;
                        $_SESSION['code_expires'] = time() + 600;
                        $_SESSION['code_attempts'] = 0;

                        sendEmail($user['email'], $user['email'], 'Login Verification Code', "Your login code is: $code");
                        $mode = 'verify_2fa';
                    } else {
                        $_SESSION['user_id']    = $user['id'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['role']       = $user['role'];
                        header('Location: index.php'); exit;
                    }
                }
            } else {
                $error = 'Invalid email or password.';
            }
        }

    // ── VERIFY (Register or 2FA) ──────────────────
    } elseif ($mode === 'verify' || $mode === 'verify_2fa') {
        $entered = trim($_POST['code'] ?? '');
        if (codeExpired()) {
            $error = 'Code expired.';
            unset($_SESSION['pending_register'], $_SESSION['pending_2fa']);
            $mode = 'login';
        } elseif ($entered !== $_SESSION['verify_code']) {
            $error = 'Wrong code.';
        } else {
            if ($mode === 'verify') {
                $pending = $_SESSION['pending_register'];
                $ins = $pdo->prepare('INSERT INTO Users (email, password, role) VALUES (?, ?, ?)');
                $ins->execute([$pending['email'], $pending['hash'], 'user']);
                unset($_SESSION['pending_register']);
                $success = 'Account created! Now login.';
                $mode = 'login';
            } else {
                $pending = $_SESSION['pending_2fa'];
                $_SESSION['user_id']    = $pending['id'];
                $_SESSION['user_email'] = $pending['email'];
                $_SESSION['role']       = $pending['role'];
                unset($_SESSION['pending_2fa']);
                header('Location: index.php'); exit;
            }
            unset($_SESSION['verify_code'], $_SESSION['code_expires']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GameHub — Auth</title>
<link rel="stylesheet" href="css/auth.css">
<style>
    .tabs { display:flex;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;margin-bottom:20px; }
    .tab-btn { flex:1;padding:9px;border:none;background:#f9f9f9;font-size:13px;font-weight:bold;color:#777;cursor:pointer; }
    .tab-btn.active { background:#1a1a1a;color:white; }
    .alert { font-size:13px;padding:10px 12px;border-radius:8px;margin-bottom:14px; }
    .alert-error { background:#fff0f0;border:1px solid #fca5a5;color:#b91c1c; }
    .alert-success { background:#f0fdf4;border:1px solid #86efac;color:#15803d; }
    .form-section { display:none; }
    .form-section.active { display:block; }
    .pass-wrap { position:relative;margin-bottom:16px; }
    .pass-wrap input { width:100%; box-sizing: border-box; }
    .pass-toggle { position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#aaa; }
    .strength-bar-track { height:5px;background:#e0e0e0;border-radius:99px;margin:6px 0; }
    .strength-bar-fill { height:100%;width:0%;transition:0.3s;border-radius:99px; }
    .hint { font-size:11px;padding:2px 8px;border-radius:10px;background:#eee;margin-right:4px; }
    .hint.met { background:#dcfce7;color:#15803d; }
    .code-input-wrap { display:flex;gap:8px;justify-content:center;margin:20px 0; }
    .code-digit { width:40px;height:50px;text-align:center;font-size:20px;font-weight:bold;border:2px solid #ddd;border-radius:8px; }
</style>
</head>
<body>

<div class="auth-logo">
    <div class="logo-box">Ghos</div>
    <h1 id="page-title"><?= ($mode == 'verify' || $mode == 'verify_2fa') ? 'Verify Email' : ($mode == 'register' ? 'Create Account' : 'Welcome Back') ?></h1>
</div>

<div class="auth-card">
    <?php if ($mode !== 'verify' && $mode !== 'verify_2fa'): ?>
    <div class="tabs">
        <button class="tab-btn <?= $mode === 'login' ? 'active' : '' ?>" onclick="switchMode('login')">Login</button>
        <button class="tab-btn <?= $mode === 'register' ? 'active' : '' ?>" onclick="switchMode('register')">Register</button>
    </div>
    <?php endif; ?>

    <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

    <div class="form-section <?= $mode === 'login' ? 'active' : '' ?>" id="panel-login">
        <form method="POST">
            <input type="hidden" name="mode" value="login">
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Password</label>
            <div class="pass-wrap">
                <input type="password" name="password" id="login-pass" required>
                <button type="button" class="pass-toggle" onclick="togglePass('login-pass')">👁</button>
            </div>
            <button class="auth-btn" type="submit">Login</button>
        </form>
    </div>

    <div class="form-section <?= $mode === 'register' ? 'active' : '' ?>" id="panel-register">
        <form method="POST">
            <input type="hidden" name="mode" value="register">
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Password</label>
            <div class="pass-wrap">
                <input type="password" name="password" id="reg-pass" oninput="checkStrength(this.value)" required>
            </div>
            <div class="strength-bar-track"><div class="strength-bar-fill" id="strength-bar"></div></div>
            <div style="margin-bottom:15px;"><span class="hint" id="hint-length">8+ chars</span><span class="hint" id="hint-number">Number</span></div>
            <label>Repeat Password</label>
            <input type="password" name="repeat_password" required>
            <button class="auth-btn" type="submit">Register</button>
        </form>
    </div>

    <?php if ($mode === 'verify' || $mode === 'verify_2fa'): ?>
    <div class="form-section active">
        <p style="text-align:center;">Enter code sent to email. Expires in: <span id="countdown"></span></p>
        <form method="POST">
            <input type="hidden" name="mode" value="<?= $mode ?>">
            <input type="hidden" name="code" id="code-hidden">
            <div class="code-input-wrap">
                <?php for($i=0;$i<6;$i++): ?><input class="code-digit" type="text" maxlength="1" inputmode="numeric"><?php endfor; ?>
            </div>
            <button class="auth-btn" type="submit">Verify</button>
        </form>
        <div style="text-align:center;margin-top:10px;"><a href="auth.php" style="font-size:13px;color:#666;">Start Over</a></div>
    </div>
    <?php endif; ?>
</div>

<script>
function switchMode(m) {
    document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));
    document.getElementById('panel-' + m).classList.add('active');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
}

function togglePass(id) {
    const p = document.getElementById(id);
    p.type = p.type === 'password' ? 'text' : 'password';
}

function checkStrength(v) {
    const bar = document.getElementById('strength-bar');
    const score = (v.length >= 8 ? 50 : 0) + (/[0-9]/.test(v) ? 50 : 0);
    bar.style.width = score + '%';
    bar.style.backgroundColor = score === 100 ? '#22c55e' : '#ef4444';
    document.getElementById('hint-length').classList.toggle('met', v.length >= 8);
    document.getElementById('hint-number').classList.toggle('met', /[0-9]/.test(v));
}

// Code Digits
const digits = document.querySelectorAll('.code-digit');
digits.forEach((d, i) => {
    d.addEventListener('input', () => {
        if (d.value && i < 5) digits[i+1].focus();
        document.getElementById('code-hidden').value = Array.from(digits).map(x => x.value).join('');
    });
});

// Countdown
const expires = <?= $_SESSION['code_expires'] ?? 0 ?>;
if(expires > 0) {
    setInterval(() => {
        const left = expires - Math.floor(Date.now() / 1000);
        if(left > 0) {
            const m = Math.floor(left/60);
            const s = left%60;
            document.getElementById('countdown').textContent = `${m}:${s < 10 ? '0'+s : s}`;
        }
    }, 1000);
}
</script>
</body>
</html>
