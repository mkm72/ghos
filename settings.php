<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

require_once 'php/db_connect.php';

$user_role    = $_SESSION['user_role'] ?? 'customer';
$is_logged_in = true;

$success = '';
$error   = '';

$stmt = $pdo->prepare('SELECT id, email, role FROM Users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: auth.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

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

    if ($action === 'change_password') {
        $current  = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password']      ?? '';
        $repeat   = $_POST['repeat_password']   ?? '';

        $row = $pdo->prepare('SELECT password FROM Users WHERE id = ?');
        $row->execute([$user['id']]);
        $row = $row->fetch();

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
        .settings-wrap {
            max-width: 680px;
            margin: 40px auto;
            padding: 0 20px 60px;
        }
        .settings-header { margin-bottom: 28px; }
        .settings-header h1 { font-size: 24px; font-weight: bold; color: #1a1a1a; margin-bottom: 4px; }
        .settings-header p  { font-size: 14px; color: #888; }

        .alert { font-size: 13px; padding: 11px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .alert-success { background: #f0fdf4; border: 1px solid #86efac; color: #15803d; }
        .alert-error   { background: #fff0f0; border: 1px solid #fca5a5; color: #b91c1c; }

        .settings-card { background: #ffffff; border: 1px solid #e0e0e0; border-radius: 10px; overflow: hidden; margin-bottom: 20px; }
        .card-header { padding: 14px 20px; border-bottom: 1px solid #e0e0e0; background: #f9f9f9; }
        .card-header h3 { font-size: 12px; font-weight: bold; color: #888; text-transform: uppercase; letter-spacing: 0.05em; }
        .card-body { padding: 20px; }

        .account-row { display: flex; align-items: center; gap: 16px; }
        .avatar { width: 54px; height: 54px; border-radius: 50%; background-color: #2563eb; color: white; font-size: 22px; font-weight: bold; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .account-meta strong { display: block; font-size: 15px; font-weight: bold; color: #1a1a1a; }
        .account-meta span   { font-size: 13px; color: #888; }

        .role-badge { display: inline-block; font-size: 11px; font-weight: bold; padding: 3px 10px; border-radius: 20px; margin-top: 5px; }
        .role-admin    { background: #fef3c7; color: #92400e; }
        .role-customer { background: #dbeafe; color: #1d4ed8; }
        .role-business { background: #dcfce7; color: #166534; }
        .role-user     { background: #dbeafe; color: #1d4ed8; }

        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: bold; color: #555; margin-bottom: 6px; }
        .form-group input { width: 100%; padding: 9px 12px; border: 1px solid #cccccc; border-radius: 6px; font-size: 14px; outline: none; transition: border-color 0.2s; background: #fff; }
        .form-group input:focus    { border-color: #2563eb; }
        .form-group input[readonly]{ background: #f9f9f9; color: #aaa; cursor: not-allowed; }

        .form-footer { display: flex; justify-content: flex-end; padding-top: 4px; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="settings-wrap">

    <div class="settings-header">
        <h1>Account Settings</h1>
        <p>Manage your GameHub account information</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Account Info -->
    <div class="settings-card">
        <div class="card-header"><h3>Account Info</h3></div>
        <div class="card-body">
            <div class="account-row">
                <div class="avatar"><?= strtoupper(substr($user['email'], 0, 1)) ?></div>
                <div class="account-meta">
                    <strong><?= htmlspecialchars($user['email']) ?></strong>
                    <span>
                        <span class="role-badge role-<?= htmlspecialchars($user['role']) ?>">
                            <?= ucfirst(htmlspecialchars($user['role'])) ?>
                        </span>
                    </span>
                </div>
            </div>
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
                    <input type="password" name="current_password" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" placeholder="Min. 6 characters" required>
                </div>
                <div class="form-group">
                    <label>Repeat New Password</label>
                    <input type="password" name="repeat_password" placeholder="••••••••" required>
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
</body>
</html>
