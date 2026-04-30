<?php
session_start();
require_once 'php/db_connect.php';

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
        } elseif (strlen($pass) < 6) {
            $error = 'Password must be at least 6 characters.';
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
                $success = 'Account created! You can now log in.';
                $mode    = 'login';
            }
        }

    } else {
        if (empty($email) || empty($pass)) {
            $error = 'Email and password are required.';
        } else {
            $stmt = $pdo->prepare('SELECT id, email, password, role FROM Users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user && password_verify($pass, $user['password'])) {
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role']  = $user['role'];
                header('Location: index.php');
                exit;
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
</style>
</head>
<body>

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
            <input type="password" name="password" placeholder="Min. 6 characters" required>
            <label>Repeat Password</label>
            <input type="password" name="repeat_password" placeholder="••••••••" required>
            <button class="auth-btn" type="submit">Register</button>
        </form>
        <div class="auth-link">Already have an account? <a onclick="switchMode('login')" href="#">Login here</a></div>
    </div>

</div>

<a href="index.html" class="back-link">← Back to Store</a>

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
</script>

</body>
</html>
