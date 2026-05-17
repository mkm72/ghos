# GHOS — GameHub Online Store

GHOS is a modern, secure, and feature-rich e-commerce platform dedicated to digital game keys. Built with a focus on security and user experience, it provides a seamless flow from game discovery to secure checkout.

##  Key Features

###  Advanced Authentication & Security
*   **Multi-Mode Auth System**: Unified login and registration with stylized email verification.
*   **Two-Factor Authentication (2FA)**: Opt-in email-based 2FA for account logins.
*   **Secure Password Reset**: Full "Forgot Password" flow utilizing 6-digit timed verification codes.
*   **Dual-Email Verification**: Updating an email address requires 2FA confirmation from both the old and new email accounts to prevent account hijacking.
*   **Session Management**: Secure guest and user session handling with automatic cart transfer upon login.

###  Gaming Experience
*   **Dynamic Game Catalog**: Browse a wide variety of games with detailed descriptions and pricing.
*   **Integrated Search**: Real-time game search functionality.
*   **Shopping Cart**: Intuitive cart system with guest support.

###   Administrative Tools
*   **Business Dashboard**: Dedicated interface for managing listings and sales.
*   **Role-Based Access Control**: Different permissions for Customers, Business users, and Administrators.

##  Tech Stack

*   **Frontend**: CSS, JavaScript (ES6+)
*   **Backend**: PHP
*   **Database**: MySQL
*   **Email**: Integrated with Resend API for transactional emails.

##  Project Structure

```text
├── css/                # Module-specific stylesheets (auth, cart, index, etc.)
├── database/           # SQL schema and initial data
├── images/             # Game assets (image)
├── php/                # Core backend logic and DB connection
├── auth.php            # Main authentication controller
├── settings.php        # User account and security management
├── product.php         # Individual game detail pages
└── index.php           # Storefront home
```

##  Installation

1.  **Clone the repository**:
    ```bash
    git clone https://github.com/mkm72/ghos.git
    ```
2.  **Database Setup**:
    *   Import `database/ghos.sql` into your MySQL environment.
    *   Update `php/db_connect.php` with your database credentials.
3.  **Email Configuration**:
    *   Open `sendEMail.php`.
    *   Replace `'your_resend_api_key'` with your actual [Resend](https://resend.com) API key.
4.  **Web Server**:
    *   Deploy to a PHP-enabled web server (e.g., Apache, Nginx).
    *   Ensure `.htaccess` is supported if using Apache.


---
*Built with by the GHOS Team.*

