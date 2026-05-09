<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'php/db_connect.php';
require_once 'sendEMail.php';

$error   = '';
$success = '';
$mode    = isset($_SESSION['pending_register']) ? 'verify' : 'login';

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
                $_SESSION['pending_register'] = ['email' => $email, 'hash' => password_hash($pass, PASSWORD_BCRYPT)];
                $_SESSION['verify_code']      = $code;
                $_SESSION['code_expires']     = time() + 600;
                $_SESSION['code_attempts']    = 0;
                sendEmail($email, $email, 'Your GameHub verification code', "
                    <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto;padding:20px;'>
                        <div style='text-align:center;margin-bottom:24px;'>
                            <div style='display:inline-block;background:#1a1a1a;color:white;font-size:22px;font-weight:bold;padding:10px 24px;border-radius:8px;letter-spacing:2px;'>Ghos</div>
                        </div>
                        <h2 style='color:#1a1a1a;'>Verify your email</h2>
                        <p style='color:#555;font-size:15px;'>Use the code below to complete your registration. Expires in <strong>10 minutes</strong>.</p>
                        <div style='text-align:center;margin:28px 0;'>
                            <div style='display:inline-block;background:#f4f4f4;border:2px dashed #ccc;border-radius:12px;padding:18px 36px;'>
                                <span style='font-size:36px;font-weight:bold;letter-spacing:10px;color:#1a1a1a;'>$code</span>
                            </div>
                        </div>
                        <p style='color:#999;font-size:13px;'>If you did not register on GameHub, ignore this email.</p>
                        <hr style='border:none;border-top:1px solid #e0e0e0;margin:28px 0 16px;'>
                        <p style='font-size:12px;color:#999;'>GameHub Online Store — <a href='https://ghos.shop' style='color:#999;'>ghos.shop</a></p>
                    </div>");
                $mode = 'verify';
            }
        }

    // ── VERIFY ────────────────────────────────────
    } elseif ($mode === 'verify') {
        $entered = trim($_POST['code'] ?? '');
        if (!isset($_SESSION['pending_register'])) {
            $error = 'Session expired. Please register again.'; $mode = 'register';
        } elseif (codeExpired()) {
            unset($_SESSION['pending_register'], $_SESSION['verify_code'], $_SESSION['code_expires'], $_SESSION['code_attempts']);
            $error = 'Code expired. Please register again.'; $mode = 'register';
        } elseif ($_SESSION['code_attempts'] >= 5) {
            unset($_SESSION['pending_register'], $_SESSION['verify_code'], $_SESSION['code_expires'], $_SESSION['code_attempts']);
            $error = 'Too many wrong attempts. Please register again.'; $mode = 'register';
        } elseif ($entered !== $_SESSION['verify_code']) {
            $_SESSION['code_attempts']++;
            $left  = 5 - $_SESSION['code_attempts'];
            $error = "Wrong code. $left attempt(s) remaining."; $mode = 'verify';
        } else {
            $pending = $_SESSION['pending_register'];
            $stmt = $pdo->prepare('SELECT id FROM Users WHERE email = ?');
            $stmt->execute([$pending['email']]);
            if ($stmt->fetch()) {
                $error = 'An account with that email already exists.'; $mode = 'register';
            } else {
                $ins = $pdo->prepare('INSERT INTO Users (email, password, role) VALUES (?, ?, ?)');
                $ins->execute([$pending['email'], $pending['hash'], 'user']);
                $new_id = $pdo->lastInsertId();
                $claim  = $pdo->prepare('UPDATE Orders SET user_id = ? WHERE guest_email = ? AND user_id IS NULL');
                $claim->execute([$new_id, $pending['email']]);
                unset($_SESSION['pending_register'], $_SESSION['verify_code'], $_SESSION['code_expires'], $_SESSION['code_attempts']);
                $success = 'Account created! You can now log in.'; $mode = 'login';
            }
        }

    // ── RESEND ────────────────────────────────────
    } elseif ($mode === 'resend') {
        if (!isset($_SESSION['pending_register'])) {
            $error = 'Session expired. Please register again.'; $mode = 'register';
        } else {
            $email = $_SESSION['pending_register']['email'];
            $code  = generateCode();
            $_SESSION['verify_code']   = $code;
            $_SESSION['code_expires']  = time() + 600;
            $_SESSION['code_attempts'] = 0;
            sendEmail($email, $email, 'Your new GameHub verification code', "
                <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto;padding:20px;'>
                    <div style='text-align:center;margin-bottom:24px;'>
                        <div style='display:inline-block;background:#1a1a1a;color:white;font-size:22px;font-weight:bold;padding:10px 24px;border-radius:8px;letter-spacing:2px;'>Ghos</div>
                    </div>
                    <h2 style='color:#1a1a1a;'>New Verification Code</h2>
                    <p style='color:#555;font-size:15px;'>Here is your new code. Expires in <strong>10 minutes</strong>.</p>
                    <div style='text-align:center;margin:28px 0;'>
                        <div style='display:inline-block;background:#f4f4f4;border:2px dashed #ccc;border-radius:12px;padding:18px 36px;'>
                            <span style='font-size:36px;font-weight:bold;letter-spacing:10px;color:#1a1a1a;'>$code</span>
                        </div>
                    </div>
                    <hr style='border:none;border-top:1px solid #e0e0e0;margin:28px 0 16px;'>
                    <p style='font-size:12px;color:#999;'>GameHub Online Store — <a href='https://ghos.shop' style='color:#999;'>ghos.shop</a></p>
                </div>");
            $success = 'A new code has been sent to your email.'; $mode = 'verify';
        }

    // ── LOGIN ─────────────────────────────────────
    } elseif ($mode === 'login') {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        if (empty($email) || empty($pass)) {
            $error = 'Email and password are required.';
        } else {
            $stmt = $pdo->prepare('SELECT id, email, password, role, is_active FROM Users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user && password_verify($pass, $user['password'])) {
                if ((int)$user['is_active'] === 0) {
                    $error = 'Your account has been suspended. Please contact us via Discord or email.';
                } else {
                    $_SESSION['user_id']    = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['role']       = $user['role'];
                    $_SESSION['user_role']  = $user['role'];
                    header('Location: index.php');
                    exit;
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
<link rel="stylesheet" href="css/auth.css">
<style>
    .tabs { display:flex;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;margin-bottom:20px; }
    .tab-btn { flex:1;padding:9px;border:none;background:#f9f9f9;font-size:13px;font-weight:bold;color:#777;cursor:pointer; }
    .tab-btn.active { background:#1a1a1a;color:white; }
    .alert { font-size:13px;padding:10px 12px;border-radius:8px;margin-bottom:14px; }
    .alert-error   { background:#fff0f0;border:1px solid #fca5a5;color:#b91c1c; }
    .alert-success { background:#f0fdf4;border:1px solid #86efac;color:#15803d; }
    .form-section { display:none; }
    .form-section.active { display:block; }

    /* Password field wrapper */
    .pass-wrap { position:relative;margin-bottom:16px; }
    .pass-wrap input { width:95%;margin-bottom:0; }
    .pass-toggle {
        position:absolute;right:12px;top:50%;transform:translateY(-50%);
        background:none;border:none;cursor:pointer;padding:4px;
        color:#aaa;font-size:16px;line-height:1;
    }
    .pass-toggle:hover { color:#555; }

    /* Strength meter */
    .strength-wrap { margin-bottom:16px; }
    .strength-bar-track { height:5px;background:#e0e0e0;border-radius:99px;overflow:hidden;margin-bottom:6px; }
    .strength-bar-fill  { height:100%;width:0%;border-radius:99px;transition:width .3s,background-color .3s; }
    .strength-label { font-size:12px;font-weight:bold;margin-bottom:6px;color:#999; }
    .strength-hints { display:flex;flex-wrap:wrap;gap:5px; }
    .hint { font-size:11px;padding:3px 8px;border-radius:99px;background:#f0f0f0;color:#999;transition:all .2s; }
    .hint.met { background:#dcfce7;color:#15803d; }

    /* Code input */
    .code-input-wrap { display:flex;gap:8px;justify-content:center;margin:20px 0; }
    .code-digit { width:44px;height:52px;text-align:center;font-size:22px;font-weight:bold;border:2px solid #e0e0e0;border-radius:8px;outline:none;transition:border-color .2s; }
    .code-digit:focus { border-color:#1a1a1a; }
    .code-meta { text-align:center;font-size:13px;color:#888;margin-bottom:16px; }
    .resend-link { background:none;border:none;color:#555;font-size:13px;cursor:pointer;text-decoration:underline;padding:0; }
    .resend-link:hover { color:#1a1a1a; }
    #countdown { font-weight:bold;color:#1a1a1a; }
</style>
</head>
<body>

<?php if ($error && strpos($error, 'suspended') !== false): ?>
<div style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;">
    <div style="background:white;padding:24px;border-radius:10px;text-align:center;width:300px;">
        <div style="font-size:40px;margin-bottom:10px;">🚫</div>
        <h3 style="margin-bottom:8px;">Account Suspended</h3>
        <p style="font-size:14px;color:#888;">Please contact us via Discord or email.</p>
        <button onclick="this.closest('div').parentElement.remove()" style="margin-top:14px;padding:8px 20px;background:#1a1a1a;color:white;border:none;border-radius:6px;cursor:pointer;">OK</button>
    </div>
</div>
<?php endif; ?>

<div class="auth-logo">
    <div class="logo-box">Ghos</div>
    <h1 id="page-title">
        <?= $mode === 'register' ? 'Create Account' : ($mode === 'verify' ? 'Verify Email' : 'Welcome Back') ?>
    </h1>
    <p id="page-sub">
        <?= $mode === 'register' ? 'Join GameHub Online Store today' :
           ($mode === 'verify'   ? 'Enter the code sent to your email' :
                                   'Sign in to your GameHub account') ?>
    </p>
</div>

<div class="auth-card">

    <?php if ($mode !== 'verify'): ?>
    <div class="tabs">
        <button class="tab-btn <?= $mode === 'login'    ? 'active' : '' ?>" onclick="switchMode('login')"    type="button" id="tab-login">Login</button>
        <button class="tab-btn <?= $mode === 'register' ? 'active' : '' ?>" onclick="switchMode('register')" type="button" id="tab-register">Register</button>
    </div>
    <?php endif; ?>

    <?php if ($error):   ?><div class="alert alert-error"  ><?= htmlspecialchars($error)   ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <!-- ── LOGIN ── -->
    <div class="form-section <?= $mode === 'login' ? 'active' : '' ?>" id="panel-login">
        <form method="POST" action="auth.php">
            <input type="hidden" name="mode" value="login">
            <label>Email</label>
            <input type="email" name="email" placeholder="your.email@example.com" required
                   value="<?= $mode === 'login' ? htmlspecialchars($_POST['email'] ?? '') : '' ?>">
            <label>Password</label>
            <div class="pass-wrap">
                <input type="password" name="password" id="login-pass" placeholder="••••••••" required>
                <button type="button" class="pass-toggle" onclick="togglePass('login-pass', this)" title="Show/hide password">👁</button>
            </div>
            <button class="auth-btn" type="submit">Login</button>
        </form>
        <div class="auth-link">Don't have an account? <a onclick="switchMode('register')" href="#">Register here</a></div>
        <div class="demo-box">
            <strong>Demo Credentials</strong>
            <p>Admin: <code>admin@ghos.com</code> / <code>123456</code><br>User: Register a new account</p>
        </div>
    </div>

    <!-- ── REGISTER ── -->
    <div class="form-section <?= $mode === 'register' ? 'active' : '' ?>" id="panel-register">
        <form method="POST" action="auth.php">
            <input type="hidden" name="mode" value="register">
            <label>Email</label>
            <input type="email" name="email" placeholder="your.email@example.com" required
                   value="<?= $mode === 'register' ? htmlspecialchars($_POST['email'] ?? '') : '' ?>">
            <label>Password</label>
            <div class="pass-wrap">
                <input type="password" name="password" id="reg-pass" placeholder="Min. 8 characters" required oninput="checkStrength(this.value)">
                <button type="button" class="pass-toggle" onclick="togglePass('reg-pass', this)" title="Show/hide password">👁</button>
            </div>
            <div class="strength-wrap">
                <div class="strength-bar-track"><div class="strength-bar-fill" id="strength-bar"></div></div>
                <div class="strength-label" id="strength-label">Enter a password</div>
                <div class="strength-hints">
                    <span class="hint" id="hint-length">8+ chars</span>
                    <span class="hint" id="hint-number">Number</span>
                    <span class="hint" id="hint-special">Special char</span>
                    <span class="hint" id="hint-upper">Uppercase</span>
                </div>
            </div>
            <label>Repeat Password</label>
            <div class="pass-wrap">
                <input type="password" name="repeat_password" id="reg-pass2" placeholder="••••••••" required>
                <button type="button" class="pass-toggle" onclick="togglePass('reg-pass2', this)" title="Show/hide password">👁</button>
            </div>
            <button class="auth-btn" type="submit">Send Verification Code</button>
        </form>
        <div class="auth-link">Already have an account? <a onclick="switchMode('login')" href="#">Login here</a></div>
    </div>

    <!-- ── VERIFY ── -->
    <?php if ($mode === 'verify'): ?>
    <div class="form-section active" id="panel-verify">
        <p style="text-align:center;font-size:14px;color:#555;margin-bottom:4px;">
            Code sent to <strong><?= htmlspecialchars($_SESSION['pending_register']['email'] ?? '') ?></strong>
        </p>
        <div class="code-meta">Expires in <span id="countdown"></span></div>
        <form method="POST" action="auth.php" id="verify-form">
            <input type="hidden" name="mode" value="verify">
            <input type="hidden" name="code" id="code-hidden">
            <div class="code-input-wrap">
                <?php for($i=0;$i<6;$i++): ?>
                <input class="code-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <?php endfor; ?>
            </div>
            <button class="auth-btn" type="submit" id="verify-btn" disabled>Verify &amp; Create Account</button>
        </form>
        <div style="text-align:center;margin-top:14px;">
            <form method="POST" action="auth.php" style="display:inline;">
                <input type="hidden" name="mode" value="resend">
                <button type="submit" class="resend-link">Resend code</button>
            </form>
            &nbsp;·&nbsp;
            <a href="auth.php" style="font-size:13px;color:#555;">Start over</a>
        </div>
    </div>
    <?php endif; ?>

</div>

<a href="index.php" class="back-link">← Back to Store</a>

<script>
// ── Tab switch ───────────────────────────────────
const labels = {
    login:    { h1: 'Welcome Back',   p: 'Sign in to your GameHub account' },
    register: { h1: 'Create Account', p: 'Join GameHub Online Store today'  }
};
function switchMode(mode) {
    ['login','register'].forEach(m => {
        document.getElementById('panel-' + m)?.classList.toggle('active', m === mode);
        document.getElementById('tab-'   + m)?.classList.toggle('active', m === mode);
    });
    document.getElementById('page-title').textContent = labels[mode].h1;
    document.getElementById('page-sub').textContent   = labels[mode].p;
}

// ── Show/hide password ───────────────────────────
function togglePass(inputId, btn) {
    const input = document.getElementById(inputId);
    const show  = input.type === 'password';
    input.type  = show ? 'text' : 'password';
    btn.textContent = show ? '︶' : '👁';
}

// ── Password strength ────────────────────────────
function checkStrength(val) {
    const bar   = document.getElementById('strength-bar');
    const label = document.getElementById('strength-label');
    const checks = {
        length:  val.length >= 8,
        number:  /[0-9]/.test(val),
        special: /[^a-zA-Z0-9]/.test(val),
        upper:   /[A-Z]/.test(val),
    };
    Object.entries(checks).forEach(([k, v]) =>
        document.getElementById('hint-' + k)?.classList.toggle('met', v));
    const score  = Object.values(checks).filter(Boolean).length;
    const levels = [
        { pct:'0%',   color:'#e0e0e0', text:'Enter a password', tc:'#999'    },
        { pct:'25%',  color:'#ef4444', text:'Weak',             tc:'#ef4444' },
        { pct:'50%',  color:'#f97316', text:'Fair',             tc:'#f97316' },
        { pct:'75%',  color:'#eab308', text:'Medium',           tc:'#eab308' },
        { pct:'100%', color:'#22c55e', text:'Strong',           tc:'#22c55e' },
    ];
    const lv = val.length === 0 ? levels[0] : levels[score];
    bar.style.width = lv.pct; bar.style.backgroundColor = lv.color;
    label.textContent = lv.text; label.style.color = lv.tc;
}

// ── Code digits ──────────────────────────────────
const digits = document.querySelectorAll('.code-digit');
const hidden = document.getElementById('code-hidden');
const btn    = document.getElementById('verify-btn');

if (digits.length) {
    digits.forEach((el, i) => {
        el.addEventListener('input', () => {
            el.value = el.value.replace(/[^0-9]/g,'').slice(-1);
            if (el.value && i < digits.length - 1) digits[i+1].focus();
            syncCode();
        });
        el.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !el.value && i > 0) digits[i-1].focus();
        });
        el.addEventListener('paste', e => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g,'').slice(0,6);
            [...pasted].forEach((ch, j) => { if (digits[j]) digits[j].value = ch; });
            if (digits[pasted.length-1]) digits[pasted.length-1].focus();
            syncCode();
        });
    });
    function syncCode() {
        const code = [...digits].map(d => d.value).join('');
        if (hidden) hidden.value = code;
        if (btn) btn.disabled = code.length < 6;
    }
}

// ── Countdown ────────────────────────────────────
const countdownEl = document.getElementById('countdown');
if (countdownEl) {
    const expires = <?= isset($_SESSION['code_expires']) ? (int)$_SESSION['code_expires'] : 0 ?>;
    function tick() {
        const left = expires - Math.floor(Date.now() / 1000);
        if (left <= 0) {
            countdownEl.textContent = '00:00';
            countdownEl.style.color = '#ef4444';
            if (btn) btn.disabled = true;
            return;
        }
        const m = String(Math.floor(left/60)).padStart(2,'0');
        const s = String(left%60).padStart(2,'0');
        countdownEl.textContent = m + ':' + s;
        countdownEl.style.color = left <= 60 ? '#ef4444' : '#1a1a1a';
        setTimeout(tick, 1000);
    }
    tick();
}
</script>
</body>
</html>
