<?php
// Load environment variables from .env file
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') === false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Define constants only if not already defined
if (!defined('GMAIL_EMAIL')) {
    define('GMAIL_EMAIL', $_ENV['GMAIL_EMAIL'] ?? '');
}
if (!defined('GMAIL_PASSWORD')) {
    define('GMAIL_PASSWORD', $_ENV['GMAIL_PASSWORD'] ?? '');
}
if (!defined('GMAIL_FROM_NAME')) {
    define('GMAIL_FROM_NAME', $_ENV['GMAIL_FROM_NAME'] ?? 'Lifestyle Store');
}
if (!defined('SITE_URL')) {
    define('SITE_URL', $_ENV['SITE_URL'] ?? 'http://localhost/LifestyleStore');
}
?>
