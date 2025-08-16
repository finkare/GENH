<?php

// Start the session
session_start();

// --- Database Configuration ---
// Credentials from the project brief
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'u191663925_flix9hub');
define('DB_PASSWORD', 'Kalachand@1974');
define('DB_NAME', 'u191663925_flix9hub');

// --- Email Configuration (for OTP and other notifications) ---
// These are placeholders and will need to be configured with actual SMTP credentials later.
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_USERNAME', 'noreply@flix9hub.com');
define('MAIL_PASSWORD', 'your_smtp_password');
define('MAIL_PORT', 587);
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM_ADDRESS', 'noreply@flix9hub.com');
define('MAIL_FROM_NAME', 'Flix9 Hub');

// --- Site & File Path Configuration ---
define('SITE_URL', 'http://localhost:8000'); // Base URL of the site
define('UPLOADS_PATH', __DIR__ . '/uploads'); // Directory for user document uploads

?>
