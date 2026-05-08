<?php
session_start();
require_once 'php/db_connect.php';

function sendEmail($toEmail, $toName, $subject, $body) {
    $apiKey = 'your_resend_api_key'; // replace with your real Resend API key

    $data = [
        'from'    => 'noreply@ghos.shop',
        'to'      => [$toEmail],
        'subject' => $subject,
        'html'    => $body
    ];

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$error   = '';
$success = '';
$mode    = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode  = $_POST['mode'] ?? 'login';
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($mode === 'register') {
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
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $ins  = $pdo->prepare('INSERT INTO Users (email, password, role) VALUES (?, ?, ?)');
                $ins->execute([$email, $hash, 'user']);

                // --- CLAIM GUEST ORDERS ---
                $new_user_id = $pdo->lastInsertId();
                $claim_stmt = $pdo->prepare('UPDATE Orders SET user_id = ? WHERE guest_email = ? AND user_id IS NULL');
                $claim_stmt->execute([$new_user_id, $email]);
                // --------------------------

                // --- SEND WELCOME EMAIL ---
                sendEmail(
                    $email,
                    $email,
                    'Welcome to GameHub! 🎮',
                    "
                    <div style='font-family: Arial, sans-serif; max-width: 500px; margin: auto; padding: 20px;'>
                        <div style='text-align:center; margin-bottom: 24px;'>
                            <div style='display:inline-block; background:#1a1a1a; color:white;
                                        font-size:22px; font-weight:bold; padding:10px 24px;
                                        border-radius:8px; letter-spacing:2px;'>
                                Ghos
                            </div>
                        </div>
                        <h2 style='color:#1a1a1a; margin-bottom:8px;'>Welcome to GameHub! 🎮</h2>
                        <p style='color:#555; font-size:15px;'>
                            Your account has been created successfully.
                        </p>
                        <div style='background:#f9f9f9; border:1px solid #e0e0e0;
                                    border-radius:8px; padding:14px 18px; margin: 20px 0;'>
                            <p style='margin:0; font-size:14px; color:#333;'>
                                <strong>Email:</strong> $email
                            </p>
                        </div>
                        <p style='color:#555; font-size:14px;'>
                            You can now log in and start exploring our store.
                        </p>
                        <a href='https://ghos.shop/auth.php'
                           style='display:inline-block; margin-top:10px; padding:11px 24px;
                                  background:#1a1a1a; color:white; border-radius:8px;
                                  text-decoration:none; font-size:14px; font-weight:bold;'>
                            Go to Login →
                        </a>
                        <hr style='border:none; border-top:1px solid #e0e0e0; margin:28px 0 16px;'>
                        <p style='font-size:12px; color:#999; margin:0;'>
                            GameHub Online Store — <a href='https://ghos.shop' style='color:#999;'>ghos.shop</a>
                        </p>
                    </div>
                    "
                );
                // --------------------------

                $success = 'Account created! You can now log in.';
                $mode    = 'login';
            }
        }

    } else {
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
    .tabs {
        display: flex;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .tab-btn {
        flex: 1;
        padding: 9px;
        border: none;
        background: #f9f9f9;
        font-size: 13px;
        font-weight: bold;
        color: #777777;
        cursor: pointer;
    }
    .tab-btn.active {
        background: #1a1a1a;
        color: white;
    }
    .alert {
        font-size: 13px;
        padding: 10px 12px;
        border-radius: 8px;
        margin-bottom: 14px;
    }
    .alert-error   { background: #fff0f0; border: 1px solid #fca5a5; color: #b91c1c; }
    .alert-success { background: #f0fdf4; border: 1px solid #86efac; color: #15803d; }
    .form-section          { display: none; }
    .form-section.active   { display: block; }

    /* Password Strength Meter */
    .strength-wrap { margin-top: -8px; margin-bottom: 16px; }
    .strength-bar-track {
        height: 5px;
        background: #e0e0e0;
        border-radius: 99px;
        overflow: hidden;
        margin-bottom: 6px;
    }
    .strength-bar-fill {
        height: 100%;
        width: 0%;
        border-radius: 99px;
        transition: width 0.3s ease, background-color 0.3s ease;
    }
    .strength-label {
        font-size: 12px;
        font-weight: bold;
        margin-bottom: 6px;
        color: #999;
    }
    .strength-hints { display: flex; flex-wrap: wrap; gap: 5px; }
    .hint {
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 99px;
        background: #f0f0f0;
        color: #999;
        transition: all 0.2s;
    }
    .hint.met { background: #dcfce7; color: #15803d; }
</style>
</head>
<body>

<?php if ($error && strpos($error, 'suspended') !== false): ?>
<div style="
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
">
    <div style="
        background: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        width: 300px;
    ">
        <h3>Account Suspended</h3>
        <p style="font-size:14px;">
            Please contact us via Discord or email.
        </p>
        <button onclick="this.closest('div').parentElement.remove()"
            style="margin-top:10px; padding:6px 15px;">
            OK
        </button>
    </div>
</div>
<?php endif; ?>

<div class="auth-logo">
    <div class="logo-box">Ghos</div>
    <h1 id="page-title"><?= $mode === 'register' ? 'Create Account' : 'Welcome Back' ?></h1>
    <p id="page-sub"><?= $mode === 'register' ? 'Join GameHub Online Store today' : 'Sign in to your GameHub account' ?></p>
</div>

<div class="auth-card">

    <div class="tabs">
        <button class="tab-btn <?= $mode === 'login'    ? 'active' : '' ?>" onclick="switchMode('login')"    type="button" id="tab-login">Login</button>
        <button class="tab-btn <?= $mode === 'register' ? 'active' : '' ?>" onclick="switchMode('register')" type="button" id="tab-register">Register</button>
    </div>

    <?php if ($error):   ?><div class="alert alert-error"  ><?= htmlspecialchars($error)   ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <!-- LOGIN -->
    <div class="form-section <?= $mode === 'login' ? 'active' : '' ?>" id="panel-login">
        <form method="POST" action="auth.php">
            <input type="hidden" name="mode" value="login">
            <label>Email</label>
            <input type="email" name="email" placeholder="your.email@example.com" required
                   value="<?= $mode === 'login' ? htmlspecialchars($_POST['email'] ?? '') : '' ?>">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
            <button class="auth-btn" type="submit">Login</button>
        </form>
        <div class="auth-link">Don't have an account? <a onclick="switchMode('register')" href="#">Register here</a></div>
        <div class="demo-box">
            <strong>Demo Credentials</strong>
            <p>Admin: <code>admin@gamestore.com</code> / <code>admin123</code><br>
               User: Register a new account</p>
        </div>
    </div>

    <!-- REGISTER -->
    <div class="form-section <?= $mode === 'register' ? 'active' : '' ?>" id="panel-register">
        <form method="POST" action="auth.php">
            <input type="hidden" name="mode" value="register">
            <label>Email</label>
            <input type="email" name="email" placeholder="your.email@example.com" required
                   value="<?= $mode === 'register' ? htmlspecialchars($_POST['email'] ?? '') : '' ?>">
            <label>Password</label>
            <input type="password" name="password" id="reg-password" placeholder="Min. 8 characters" required oninput="checkStrength(this.value)">
            <div class="strength-wrap">
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
            <input type="password" name="repeat_password" placeholder="••••••••" required>
            <button class="auth-btn" type="submit">Register</button>
        </form>
        <div class="auth-link">Already have an account? <a onclick="switchMode('login')" href="#">Login here</a></div>
    </div>

</div>

<a href="index.php" class="back-link">← Back to Store</a>

<script>
    const labels = {
        login:    { h1: 'Welcome Back',   p: 'Sign in to your GameHub account' },
        register: { h1: 'Create Account', p: 'Join GameHub Online Store today'  }
    };
    function switchMode(mode) {
        ['login','register'].forEach(m => {
            document.getElementById('panel-' + m).classList.toggle('active', m === mode);
            document.getElementById('tab-'   + m).classList.toggle('active', m === mode);
        });
        document.getElementById('page-title').textContent = labels[mode].h1;
        document.getElementById('page-sub').textContent   = labels[mode].p;
    }

    function checkStrength(val) {
        const bar   = document.getElementById('strength-bar');
        const label = document.getElementById('strength-label');

        const hasLength  = val.length >= 8;
        const hasNumber  = /[0-9]/.test(val);
        const hasSpecial = /[^a-zA-Z0-9]/.test(val);
        const hasUpper   = /[A-Z]/.test(val);

        document.getElementById('hint-length') .classList.toggle('met', hasLength);
        document.getElementById('hint-number') .classList.toggle('met', hasNumber);
        document.getElementById('hint-special').classList.toggle('met', hasSpecial);
        document.getElementById('hint-upper')  .classList.toggle('met', hasUpper);

        const score = [hasLength, hasNumber, hasSpecial, hasUpper].filter(Boolean).length;

        const levels = [
            { pct: '0%',   color: '#e0e0e0', text: 'Enter a password', textColor: '#999'     },
            { pct: '25%',  color: '#ef4444', text: '🔴 Weak',          textColor: '#ef4444'  },
            { pct: '50%',  color: '#f97316', text: '🟠 Fair',          textColor: '#f97316'  },
            { pct: '75%',  color: '#eab308', text: '🟡 Medium',        textColor: '#eab308'  },
            { pct: '100%', color: '#22c55e', text: '🟢 Strong',        textColor: '#22c55e'  },
        ];

        const level = val.length === 0 ? levels[0] : levels[score];
        bar.style.width           = level.pct;
        bar.style.backgroundColor = level.color;
        label.textContent         = level.text;
        label.style.color         = level.textColor;
    }
</script>

</body>
</html>
