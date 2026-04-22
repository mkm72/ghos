<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'Ghos_db'); // change this
define('DB_USER', 'root'); // change this
define('DB_PASS', ''); // change this
define('DB_CHARSET', 'utf8mb4'); // utf8mb4 is full UTF-8 handles Arabic, emojis, special characters.

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw errors instead of silently failing
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // return rows as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false, // use real prepared statements
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Don't expose DB details to the browser
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
