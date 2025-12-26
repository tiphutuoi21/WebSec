<?php
// Initialize secure session FIRST (must be before any output or session operations)
// This must happen before mysqli_connect and any headers are sent
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/SessionManager.php';
    SessionManager::initializeSecureSession();
}

// Load security enhancements
require_once __DIR__ . '/SecurityEnhancements.php';

// Validate request size to prevent DoS
SecurityEnhancements::validateRequestSize();

// Now establish database connection with error handling
// Note: In production, credentials should be loaded from environment variables
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'store';

// Try connecting with secure user first (Privilege Escalation Prevention)
// This user should have limited privileges (SELECT, INSERT, UPDATE, DELETE only)
$con = @mysqli_connect($db_host, 'websec_user', 'WebSec@2024Secure', $db_name);

// If failed, fallback to root (Development/Migration compatibility)
if (!$con) {
    $db_user = getenv('DB_USER') ?: 'root';
    $db_pass = getenv('DB_PASS') ?: 'tuanduongne2004';
    $con = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);
}

if (!$con) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Service temporarily unavailable. Please try again later.");
}

// Set charset for database connection
mysqli_set_charset($con, "utf8mb4");

// Set database timezone to match PHP timezone (Asia/Ho_Chi_Minh = UTC+7)
// This ensures NOW() function uses same timezone as PHP time functions
mysqli_query($con, "SET time_zone = '+07:00'");

// Run pending database migrations (auto-patch unpatched database)
SecurityEnhancements::runPendingMigrations($con);

// Add security headers (prevent MIME sniffing, clickjacking, XSS)
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Content-Type: text/html; charset=utf-8');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    // Content Security Policy tuned for current assets (allows inline scripts/styles already present)
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'self';");
    // Enable HSTS when using HTTPS
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    if ($is_https) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
}
?>
