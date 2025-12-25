<?php
// Initialize secure session FIRST (must be before any output or session operations)
// This must happen before mysqli_connect and any headers are sent
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/SessionManager.php';
    SessionManager::initializeSecureSession();
}

// Thay đổi Host, User, Pass theo thông tin bạn đã tạo
$servername = "127.0.0.1"; // Khuyên dùng IP này thay cho 'localhost' để tránh lỗi quyền
$username   = "mychos"; // Tên user bạn vừa tạo (Ví dụ: dev_user)
$password   = ""; // Mật khẩu bạn đã đặt
$dbname     = "store";        // Tên database vẫn giữ nguyên là 'store'

$con = mysqli_connect($servername, $username, $password, $dbname) or die(mysqli_error($con));

// Set charset for database connection
mysqli_set_charset($con, "utf8");

// Set database timezone to match PHP timezone (Asia/Ho_Chi_Minh = UTC+7)
// This ensures NOW() function uses same timezone as PHP time functions
mysqli_query($con, "SET time_zone = '+07:00'");

// Add security headers (prevent MIME sniffing, clickjacking, XSS)
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Content-Type: text/html; charset=utf-8');
    // Content Security Policy tuned for current assets (allows inline scripts/styles already present)
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'self';");
    // Enable HSTS when using HTTPS
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    if ($is_https) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}
?>
