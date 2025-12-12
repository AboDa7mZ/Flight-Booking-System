<?php
/**
 * Application Configuration
 * Flight Booking System
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Application settings
define('APP_NAME', 'Flight Booking System');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/Flight%20Booking%20Website');

// Security settings
define('JWT_SECRET_KEY', 'your-secret-key-change-this-in-production');
define('JWT_EXPIRATION_TIME', 3600 * 24); // 24 hours

// Password hashing
define('PASSWORD_COST', 10);

// Pagination
define('RESULTS_PER_PAGE', 20);

// File upload settings
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-email-password');
define('SMTP_FROM_EMAIL', 'noreply@flightbooking.com');
define('SMTP_FROM_NAME', 'Flight Booking System');

// Payment gateway (example for Stripe)
define('STRIPE_SECRET_KEY', 'your-stripe-secret-key');
define('STRIPE_PUBLISHABLE_KEY', 'your-stripe-publishable-key');

// API settings
define('API_RATE_LIMIT', 100); // requests per hour
define('API_VERSION', 'v1');

// Timezone
date_default_timezone_set('UTC');

// CORS settings
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Content type
header('Content-Type: application/json; charset=UTF-8');
?>
