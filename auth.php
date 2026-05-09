<?php
session_start();
require_once 'php/db_connect.php';
require_once 'sendEMail.php';

$error   = '';
$success = '';
$mode    = isset($_SESSION['pending_register']) ? 'verify' : (isset($_SESSION['pending_login']) ? 'login_verify' : 'login');

function generateCode() { return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT); }
function codeExpired($key = 'code_expires')  { return !isset($_SESSION[$key]) || time() > $_SESSION[$key]; }

function sendVerificationCodeEmail($email, $code, $subject, $title, $message) {
    sendEmail($email, $email, $subject, "
        <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto;padding:20px;'>
            <div style='text-align:center;margin-bottom:24px;'>
                <div style='display:inline-block;background:#1a1a1a;color:white;font-size:22px;font-weight:bold;padding:10px 24px;border-radius:8px;letter-spacing:2px;'>Ghos</div>
            </div>
            <h2 style='color:#1a1a1a;'>$title</h2>
            <p style='color:#555;font-size:15px;'>$message Expires in <strong>10 minutes</strong>.</p>
            <div style='text-align:center;margin:28px 0;'>
                <div style='display:inline-block;background:#f4f4f4;border:2px dashed #ccc;border-radius:12px;padding:18px 36px;'>
                    <span style='font-size:36px;font-weight:bold;letter-spacing:10px;color:#1a1a1a;'>$code</span>
                </div>
            </div>
            <p style='color:#999;font-size:13px;'>If you did not request this code, ignore this email.</p>
            <hr style='border:none;border-top:1px solid #e0e0e0;margin:28px 0 16px;'>
            <p style='font-size:12px;color:#999;'>GameHub Online Store — <a href='https://ghos.shop' style='color:#999;'>ghos.shop</a></p>
        </div>");
}

function completeLogin($user) {
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['role']       = $user['role'];
    $_SESSION['user_role']  = $user['role'];
    unset($_SESSION['pending_login'], $_SESSION['login_verify_code'], $_SESSION['login_code_expires'], $_SESSION['login_code_attempts']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'login';

    // REGISTER
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
                sendVerificationCodeEmail(
                    $email,
                    $code,
                    'Your GameHub verification code',
                    'Verify your email',
                    'Use the code below to complete your registration.'
                );
                $mode = 'verify';
            }
        }

    // VERIFY REGISTER
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
