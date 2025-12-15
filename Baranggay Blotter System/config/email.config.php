<?php
/**
 * Email Configuration
 * 
 * Configure Gmail SMTP settings here
 * To use: Create an App Password in your Gmail account
 * Steps:
 * 1. Go to https://myaccount.google.com/security
 * 2. Enable 2-Step Verification
 * 3. Generate App Password (select Mail and Windows Computer)
 * 4. Copy the 16-character password below
 */

// ============ FEATURE TOGGLES ============
define('ENABLE_SYSTEM_NOTIFICATIONS', true);  // Enable in-app system notifications
define('ENABLE_EMAIL_NOTIFICATIONS', true);   // Enable email notifications

// ============ EMAIL SETTINGS ============
define('EMAIL_FROM', 'ricafort.rutherford2023@gmail.com');
define('EMAIL_FROM_NAME', 'Barangay Blotter System');

// Alternative naming for compatibility
define('SMTP_FROM_EMAIL', 'ricafort.rutherford2023@gmail.com');
define('SMTP_FROM_NAME', 'Barangay Blotter System');

// ============ SMTP SETTINGS ============
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'ricafort.rutherford2023@gmail.com');
define('SMTP_PASSWORD', 'hpzngqkwxeuxgosr'); // 16-character app password from Gmail
define('SMTP_SECURE', 'tls'); // 'tls' or 'ssl'

// ============ EMAIL MODE (DEVELOPMENT VS PRODUCTION) ============
define('SAVE_EMAILS_TO_FILE', false); // Set to true to save emails to file instead of sending
define('EMAIL_DEBUG_MODE', true);     // Set to true for verbose SMTP debugging

// ============ SYSTEM CONFIGURATION ============
define('SYSTEM_URL', 'http://localhost/Web%20System%20pls'); // Base URL of the system

// ============ PHPMailer CONFIGURATION ============
define('PHPMAILER_PATH', __DIR__ . '/../'); // Path to PHPMailer files
