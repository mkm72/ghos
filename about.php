<?php
session_start();
require_once 'php/db_connect.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_role    = $_SESSION['role'] ?? 'guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us — GameHub Online Store</title>
    <link rel="icon" type="image/png" href="images/logo/logo2.png">
    <link rel="stylesheet" href="css/navbar.css?v=2026.05.17.v2">
    <link rel="stylesheet" href="css/business.css">
    <style>
        .about-wrap { max-width: 900px; margin: 40px auto; padding: 0 20px 60px; }
        .section-box { background: white; border: 1px solid #e0e0e0; border-radius: 12px; padding: 30px; margin-bottom: 24px; }
        .section-box h2 { font-size: 20px; color: #1a1a1a; margin-bottom: 16px; border-bottom: 2px solid #2563eb; display: inline-block; padding-bottom: 4px; }
        .points-list { list-style: none; }
        .points-list li { margin-bottom: 16px; font-size: 15px; color: #444; line-height: 1.6; }
        .map-section { margin-top: 20px; }
        .map-section iframe { width: 100%; height: 350px; border: none; border-radius: 10px; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="business-product">
    <h1>About Our Platform</h1>
    <p>Mission, vision, and the technological foundation of the GameHub Online Store platform.</p>
</div>

<div class="about-wrap">

    <!-- Contact Us & Location (Task 11) -->
    <div class="section-box" id="contact">
        <h2>Find Us</h2>
        <p><strong>Address:</strong> Imam Abdulrahman Bin Faisal University, Dammam, Saudi Arabia</p>
        <p><strong>Support Email:</strong> support@ghos.shop</p>
        <div class="map-section">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1754.1487865674999!2d50.194878487615526!3d26.39430784899561!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e49ef811304efab%3A0xe664343a49ebbf2b!2sCollege%20of%20Computer%20Science%20and%20Information%20Technology!5e0!3m2!1sen!2ssa!4v1776965850652!5m2!1sen!2ssa" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>

    <!-- Issues Solved (Task 17-1) -->
    <div class="section-box">
        <h2>Solutions for Modern Gaming</h2>
        <ul class="points-list">
            <li>
                <strong>Operational Efficiency:</strong> Traditional retail faces massive overhead. 
                GameHub optimizes this by providing a digital-first marketplace with zero physical storage costs.
            </li>
            <li>
                <strong>Global Marketplace:</strong> We bridge the gap between regional sellers and global buyers, 
                enabling instant distribution regardless of location.
            </li>
            <li>
                <strong>Instant Access:</strong> Eliminating waiting times. Our platform delivers activation 
                codes the moment a transaction is verified.
            </li>
        </ul>
    </div>

    <!-- Tech Features (Task 17-2) -->
    <div class="section-box">
        <h2>Core Technological Features</h2>
        <ul class="points-list">
            <li>
                <strong>Adaptive Pricing:</strong> Real-time currency conversion (SAR/USD) built on a 
                robust JavaScript engine for seamless international shopping.
            </li>
            <li>
                <strong>Account Security:</strong> Multi-layered protection including Two-Factor Authentication (2FA) 
                and encrypted credential management.
            </li>
            <li>
                <strong>Intelligent Management:</strong> A centralized dashboard for inventory control, sales analytics, 
                and user moderation powered by PHP and MySQL.
            </li>
        </ul>
    </div>

    <!-- Challenges (Task 17-3) -->
    <div class="section-box">
        <h2>Future Challenges</h2>
        <ul class="points-list">
            <li>
                <strong>Cybersecurity:</strong> As a digital asset platform, we prioritize evolving our defense 
                systems against sophisticated emerging threats.
            </li>
            <li>
                <strong>Scalability:</strong> Handling rapid growth while maintaining 100% server uptime 
                requires continuous infrastructure optimization.
            </li>
        </ul>
    </div>

    <!-- Tools Used (Task 17-4) -->
    <div class="section-box">
        <h2>Development Stack</h2>
        <ul class="points-list">
            <li>
                <strong>Technologies:</strong> The platform is built using a modern stack including PHP for server-side logic, 
                MySQL for normalized data storage, and JavaScript/CSS for a dynamic user interface.
            </li>
        </ul>
    </div>

</div>

<div class="footer">
    © 2026 GameHub Online Store. All rights reserved. ·
    <a href="index.php" style="color:#888888;">Store</a> ·
    <a href="about.php" style="color:#888888;">About Us</a>
</div>

<script src="js/navbar.js"></script>
</body>
</html>