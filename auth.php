<?php
session_start();
require_once 'php/db_connect.php';

$error = ''; $success = ''; $mode = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode  = $_POST['mode'] ?? 'login';
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($mode === 'register') {
        $repeat = $_POST['repeat_password'] ?? '';
        if (empty($email) || empty($pass) || empty($repeat)) { $error = 'All fields are required.'; }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = 'Invalid email address.'; }
        elseif (strlen($pass) < 6) { $error = 'Password must be at least 6 characters.'; }
        elseif ($pass !== $repeat) { $error = 'Passwords do not match.'; }
        else {
            $stmt = $pdo->prepare('SELECT id FROM Users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'An account with that email already exists.';
            } else {
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $ins  = $pdo->prepare('INSERT INTO Users (email, password, role) VALUES (?, ?, ?)');
                $ins->execute([$email, $hash, 'user']);
                
                // LINK PAST GUEST ORDERS
                $new_user_id = $pdo->lastInsertId();
                $claim_stmt = $pdo->prepare('UPDATE Orders SET user_id = ? WHERE guest_email = ? AND user_id IS NULL');
                $claim_stmt->execute([$new_user_id, $email]);

                $success = 'Account created! You can now log in.';
                $mode    = 'login';
            }
        }
    } else {
        if (empty($email) || empty($pass)) { $error = 'Email and password are required.'; }
        else {
            $stmt = $pdo->prepare('SELECT id, email, password, role FROM Users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user && password_verify($pass, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                header('Location: index.php'); exit;
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}
?>
<!-- The rest of your HTML remains exactly the same as the original auth.php file -->
