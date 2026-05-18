<?php
// implications.php — Project Implications Page
// Author: GameHub Team
// CIS 423 — Web-based Systems, Term 2 2025-26
session_start();
$user_role   = $_SESSION['role'] ?? 'guest';
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Implications — GameHub Online Store</title>
    <meta name="description" content="Business and technological implications of the GameHub Online Store project for CIS 423.">
    <link rel="icon" type="image/png" href="images/logo/logo2.png">
    <link rel="stylesheet" href="css/navbar.css?v=2026.05.17.v2">
    <link rel="stylesheet" href="css/business.css">
    <style>
        /* ── Page-specific styles ── */
        .impl-hero {
            background: linear-gradient(135deg, #1e3a5f 0%, #0c1a35 100%);
            padding: 70px 30px;
            text-align: center;
        }
        .impl-hero h1 {
            font-size: 38px;
            font-weight: bold;
            color: #ffffff;
            margin-bottom: 14px;
        }
        .impl-hero p {
            font-size: 16px;
            color: #93c5fd;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.7;
        }
        .impl-hero .course-badge {
            display: inline-block;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: #bfdbfe;
            font-size: 13px;
            font-weight: bold;
            padding: 6px 18px;
            border-radius: 20px;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }

        /* ── Section wrapper ── */
        .impl-section {
            max-width: 1000px;
            margin: 0 auto;
            padding: 55px 24px;
        }
        .impl-section + .impl-section {
            padding-top: 0;
        }

        /* ── Section title with numbered circle ── */
        .impl-title-row {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
        }
        .impl-num {
            width: 46px;
            height: 46px;
            min-width: 46px;
            border-radius: 50%;
            background: #2563eb;
            color: white;
            font-size: 20px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .impl-title {
            font-size: 22px;
            font-weight: bold;
            color: #1a1a1a;
        }
        .impl-subtitle {
            font-size: 14px;
            color: #888;
            margin-top: 3px;
        }

        /* ── Problem cards (Point 1) ── */
        .problem-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .problem-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-left: 4px solid #ef4444;
            border-radius: 10px;
            padding: 22px 20px;
        }
        .problem-card .prob-icon {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .problem-card h3 {
            font-size: 15px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 8px;
        }
        .problem-card p {
            font-size: 13px;
            color: #666;
            line-height: 1.6;
        }

        /* ── Tech feature cards (Point 2) ── */
        .tech-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }
        .tech-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-top: 4px solid #2563eb;
            border-radius: 10px;
            padding: 22px 18px;
            text-align: center;
        }
        .tech-card .tech-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .tech-card h3 {
            font-size: 14px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 6px;
        }
        .tech-card p {
            font-size: 12px;
            color: #666;
            line-height: 1.5;
        }
        .tech-badge {
            display: inline-block;
            background: #eff6ff;
            color: #2563eb;
            font-size: 11px;
            font-weight: bold;
            padding: 2px 10px;
            border-radius: 20px;
            margin-top: 8px;
        }

        /* ── Challenge cards (Point 3) ── */
        .challenge-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .challenge-item {
            background: white;
            border: 1px solid #e0e0e0;
            border-left: 4px solid #f59e0b;
            border-radius: 10px;
            padding: 18px 20px;
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }
        .challenge-item .ch-icon {
            font-size: 26px;
            flex-shrink: 0;
        }
        .challenge-item h3 {
            font-size: 15px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 5px;
        }
        .challenge-item p {
            font-size: 13px;
            color: #666;
            line-height: 1.6;
        }

        /* ── Tools section (Point 4) ── */
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
        }
        .tool-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 22px 18px;
            text-align: center;
        }
        .tool-name {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 6px;
        }
        .tool-desc {
            font-size: 13px;
            color: #555;
            line-height: 1.5;
        }

        /* ── Divider band ── */
        .section-band {
            background: #f0f4ff;
            padding: 55px 24px;
        }
        .section-band .impl-section {
            padding: 0;
        }

        /* ── Summary table ── */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
            font-size: 14px;
        }
        .summary-table th {
            background: #1e3a5f;
            color: white;
            padding: 12px 16px;
            text-align: left;
            font-weight: bold;
        }
        .summary-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f0f0f0;
            color: #444;
            vertical-align: top;
        }
        .summary-table tr:last-child td { border-bottom: none; }
        .summary-table tr:hover td { background: #f8faff; }

        @media (max-width: 640px) {
            .impl-hero h1 { font-size: 26px; }
            .impl-title { font-size: 18px; }
            .problem-grid, .tech-grid, .tools-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<!-- ══ HERO ══ -->
<div class="impl-hero">
    <div class="course-badge">CIS 423 — Web-based Systems · Term 2 2025-26</div>
    <h1>📋 Project Implications</h1>
    <p>An analysis of the business impact, technological solutions, potential challenges, and development tools used in building the GameHub Online Store.</p>
</div>

<!-- ══ POINT 1: Problems Solved ══ -->
<div class="impl-section">
    <div class="impl-title-row">
        <div class="impl-num">1</div>
        <div>
            <div class="impl-title">Problems Faced by the Organization</div>
            <div class="impl-subtitle">Issues that existed before the web system and are now solved by GameHub</div>
        </div>
    </div>

    <div class="problem-grid">
        <div class="problem-card">
            <div class="prob-icon">🏪</div>
            <h3>No Online Presence</h3>
            <p>Physical game shops can only serve customers locally. Without a website, the business is limited to a small geographic area and loses potential customers who shop online.</p>
        </div>
        <div class="problem-card">
            <div class="prob-icon">📦</div>
            <h3>Manual Inventory Management</h3>
            <p>Tracking game stock manually using spreadsheets or paper leads to errors, overselling, and difficulty knowing how many units are available at any given time.</p>
        </div>
        <div class="problem-card">
            <div class="prob-icon">⏳</div>
            <h3>Slow Order Processing</h3>
            <p>Traditional stores require customers to visit in person or call to place an order, causing delays, long wait times, and poor customer experience compared to instant digital delivery.</p>
        </div>
        <div class="problem-card">
            <div class="prob-icon">📊</div>
            <h3>No Sales Analytics</h3>
            <p>Without a system, managers have no visibility into which products sell best, total revenue, or customer trends — making data-driven decisions impossible.</p>
        </div>
        <div class="problem-card">
            <div class="prob-icon">🔐</div>
            <h3>Insecure Product Management</h3>
            <p>No secure admin interface means anyone could potentially modify product listings. There is no way to verify who made changes or restrict access to authorized staff only.</p>
        </div>
        <div class="problem-card">
            <div class="prob-icon">🌍</div>
            <h3>Limited Market Reach</h3>
            <p>Physical stores cannot reach international customers. A web-based system allows the business to sell digital game keys globally, 24/7, with no geographic restrictions.</p>
        </div>
    </div>
</div>

<!-- ══ POINT 2: Tech Features ══ -->
<div class="section-band">
<div class="impl-section">
    <div class="impl-title-row">
        <div class="impl-num">2</div>
        <div>
            <div class="impl-title">Technological Features Implemented</div>
            <div class="impl-subtitle">How the website's features directly address the above problems</div>
        </div>
    </div>

    <div class="tech-grid">
        <div class="tech-card">
            <div class="tech-icon">🛒</div>
            <h3>Shopping Cart with Sessions</h3>
            <p>PHP sessions store cart data per user. Guests can shop without an account; logged-in users' carts persist across devices.</p>
            <span class="tech-badge">PHP Sessions</span>
        </div>
        <div class="tech-card">
            <div class="tech-icon">🗄️</div>
            <h3>Normalized Database</h3>
            <p>MySQL database with Games, Game_Keys, Orders, and Users tables. Foreign keys ensure data integrity and prevent orphaned records.</p>
            <span class="tech-badge">MySQL / PDO</span>
        </div>
        <div class="tech-card">
            <div class="tech-icon">🔑</div>
            <h3>Digital Key Inventory</h3>
            <p>Each game key is a row in Game_Keys with an <code>is_sold</code> flag. Stock is checked in real-time before every purchase.</p>
            <span class="tech-badge">Real-time Stock</span>
        </div>
        <div class="tech-card">
            <div class="tech-icon">🔒</div>
            <h3>Secure Admin Panel</h3>
            <p>Admin pages check <code>$_SESSION['role'] === 'admin'</code> on every request. Non-admins receive a 403 error. Passwords are hashed with bcrypt.</p>
            <span class="tech-badge">Session Auth</span>
        </div>
        <div class="tech-card">
            <div class="tech-icon">🍪</div>
            <h3>Past Purchases (Cookies)</h3>
            <p>After checkout, purchased game IDs are saved in a cookie (30 days). Returning customers see a "Recent Purchases" section on the homepage.</p>
            <span class="tech-badge">Browser Cookies</span>
        </div>
        <div class="tech-card">
            <div class="tech-icon">✅</div>
            <h3>JavaScript Form Validation</h3>
            <p>All forms validate input on the client side before submission — including card details, quantity limits, password strength, and required fields.</p>
            <span class="tech-badge">JavaScript</span>
        </div>
        <div class="tech-card">
            <div class="tech-icon">📧</div>
            <h3>Email Delivery System</h3>
            <p>After payment, customers automatically receive an order confirmation and their game activation keys via email using PHP's mail function.</p>
            <span class="tech-badge">PHP Mail</span>
        </div>
        <div class="tech-card">
            <div class="tech-icon">🗺️</div>
            <h3>Google Maps Integration</h3>
            <p>The Contact Us modal embeds a live Google Maps iframe showing the exact store location (IAU College of Computer Science).</p>
            <span class="tech-badge">Google Maps API</span>
        </div>
        <div class="tech-card">
            <div class="tech-icon">🖼️</div>
            <h3>Image Upload for Products</h3>
            <p>Admins can upload cover images for games directly through the admin panel. Images are validated for type and size before being stored.</p>
            <span class="tech-badge">File Upload (PHP)</span>
        </div>
    </div>
</div>
</div>

<!-- ══ POINT 3: New Challenges ══ -->
<div class="impl-section">
    <div class="impl-title-row">
        <div class="impl-num">3</div>
        <div>
            <div class="impl-title">Additional Challenges Introduced by the System</div>
            <div class="impl-subtitle">New problems that may arise as a result of running this web system</div>
        </div>
    </div>

    <div class="challenge-list">
        <div class="challenge-item">
            <div class="ch-icon">🔐</div>
            <div>
                <h3>Cybersecurity Risks</h3>
                <p>Running an online store introduces risks such as SQL injection, session hijacking, brute-force attacks on the admin login, and unauthorized access to customer data. Continuous security monitoring and updates are required.</p>
            </div>
        </div>
        <div class="challenge-item">
            <div class="ch-icon">🔑</div>
            <div>
                <h3>Key Fraud and Chargebacks</h3>
                <p>Digital game keys can be purchased with stolen credit cards. Once a key is delivered and used, a chargeback leaves the store at a loss. Implementing fraud detection and payment verification is necessary.</p>
            </div>
        </div>
        <div class="challenge-item">
            <div class="ch-icon">⚙️</div>
            <div>
                <h3>Server Reliability and Uptime</h3>
                <p>A physical shop has fixed opening hours; a website is expected to be online 24/7. Any downtime means lost sales and damaged reputation. Hosting, backups, and error handling must be maintained consistently.</p>
            </div>
        </div>
        <div class="challenge-item">
            <div class="ch-icon">📈</div>
            <div>
                <h3>Scalability as Traffic Grows</h3>
                <p>If the store becomes popular, the current single-server PHP/MySQL setup may struggle under high load. Database queries may slow down, and the system may need optimization or migration to a more scalable architecture.</p>
            </div>
        </div>
        <div class="challenge-item">
            <div class="ch-icon">⚖️</div>
            <div>
                <h3>Legal and Compliance Issues</h3>
                <p>Selling digital products online requires compliance with consumer protection laws (e.g., refund policies), data protection regulations (e.g., GDPR), and the terms of service of game publishers whose keys are being resold.</p>
            </div>
        </div>
        <div class="challenge-item">
            <div class="ch-icon">📦</div>
            <div>
                <h3>Inventory Depletion Without Restocking Automation</h3>
                <p>When game keys run out, products go out of stock and sales stop. There is no automated restock alert or supplier integration — admins must manually add new keys, which can be slow and cause stockouts.</p>
            </div>
        </div>
    </div>
</div>

<!-- ══ POINT 4: Tools Used ══ -->
<div class="section-band">
<div class="impl-section">
    <div class="impl-title-row">
        <div class="impl-num">4</div>
        <div>
            <div class="impl-title">How the Tools Helped Design the System</div>
            <div class="impl-subtitle">The technologies chosen and how each contributed to the final product</div>
        </div>
    </div>

    <div class="tools-grid">
        <div class="tool-card">
            <div class="tool-name">PHP</div>
            <div class="tool-desc">Server-side scripting language used to handle all business logic — from processing payments to managing sessions, sending emails, and generating dynamic HTML pages from database content.</div>
        </div>
        <div class="tool-card">
            <div class="tool-name">MySQL</div>
            <div class="tool-desc">Relational database used to store all application data (games, keys, users, orders). Its support for foreign keys and transactions ensured data integrity during the checkout process.</div>
        </div>
        <div class="tool-card">
            <div class="tool-name">PDO</div>
            <div class="tool-desc">PHP Data Objects (PDO) was used for all database queries with prepared statements, which prevents SQL injection attacks and makes the code more secure and maintainable.</div>
        </div>
        <div class="tool-card">
            <div class="tool-name">HTML & CSS</div>
            <div class="tool-desc">HTML5 semantic elements (nav, main, form) provided accessible page structure. Custom CSS created a consistent, modern design across all pages without relying on external frameworks.</div>
        </div>
        <div class="tool-card">
            <div class="tool-name">JavaScript</div>
            <div class="tool-desc">Enabled real-time form validation, dynamic search filtering, interactive modals (Help window, Contact Us), sortable admin tables, and the cart quantity controls — all without page reloads.</div>
        </div>
        <div class="tool-card">
            <div class="tool-name">PHP Sessions</div>
            <div class="tool-desc">Used to maintain user state across pages — tracking who is logged in, their role (admin/user), and their shopping cart contents throughout their visit.</div>
        </div>
        <div class="tool-card">
            <div class="tool-name">Cookies</div>
            <div class="tool-desc">Browser cookies persist past purchase history for 30 days, allowing the homepage to show returning customers a personalized "Recent Purchases" section without requiring a login.</div>
        </div>
        <div class="tool-card">
            <div class="tool-name">Google Maps API</div>
            <div class="tool-desc">The free Google Maps embed API was used to display an interactive map of the store's location inside the Contact Us modal, improving user trust and navigation.</div>
        </div>
        <div class="tool-card">
            <div class="tool-name">Git / GitHub</div>
            <div class="tool-desc">Version control allowed the team to collaborate on code, track changes, resolve merge conflicts, and maintain a history of all edits — essential for a multi-member project.</div>
        </div>
    </div>

    <div style="margin-top: 40px;">
        <h3 style="font-size: 18px; font-weight: bold; color: #1a1a1a; margin-bottom: 16px;">Summary Table</h3>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Point</th>
                    <th>Description</th>
                    <th>Example in GameHub</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>1. Problems Solved</strong></td>
                    <td>Issues the organization faced before the system</td>
                    <td>No online presence, manual inventory, limited reach</td>
                </tr>
                <tr>
                    <td><strong>2. Tech Features</strong></td>
                    <td>Technologies implemented to solve those problems</td>
                    <td>Sessions, cookies, MySQL, JS validation, email keys</td>
                </tr>
                <tr>
                    <td><strong>3. New Challenges</strong></td>
                    <td>Problems introduced by going digital</td>
                    <td>Cybersecurity risks, chargebacks, server uptime</td>
                </tr>
                <tr>
                    <td><strong>4. Tools Used</strong></td>
                    <td>How development tools shaped the system design</td>
                    <td>PHP/PDO for security, MySQL for integrity, JS for UX</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</div>

<!-- ══ FOOTER ══ -->
<div class="footer">
    © 2026 GameHub Online Store · CIS 423 Project ·
    <a href="index.php" style="color:#888;">Back to Store</a>
</div>

</body>
</html>
