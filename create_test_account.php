<?php
// Create a test user account for WebSec
require_once __DIR__ . '/connection.php';

// Simple UUID v4 generator
function uuidv4(): string {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// Config for the test account
$name     = 'Test User';
$email    = 'testuser@example.com';
$password = 'Test@12345';
$contact  = '0999999999';
$city     = 'Ho Chi Minh';
$address  = '123 Test Street';

// Check if email already exists
$check = mysqli_prepare($con, 'SELECT id FROM users WHERE email = ? LIMIT 1');
mysqli_stmt_bind_param($check, 's', $email);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);

header('Content-Type: text/plain; charset=utf-8');

echo "=== Create Test Account ===\n\n";

echo "Target email: {$email}\n";
if (mysqli_stmt_num_rows($check) > 0) {
    echo "Account already exists. No changes made.\n";
    exit;
}
mysqli_stmt_close($check);

$userUid = uuidv4();
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = mysqli_prepare(
    $con,
    'INSERT INTO users (user_uid, name, email, password, contact, city, address, email_verified) VALUES (?, ?, ?, ?, ?, ?, ?, 1)'
);

if (!$stmt) {
    echo "Failed to prepare statement: " . mysqli_error($con) . "\n";
    exit;
}

mysqli_stmt_bind_param($stmt, 'sssssss', $userUid, $name, $email, $hash, $contact, $city, $address);

if (mysqli_stmt_execute($stmt)) {
    echo "✅ Test account created successfully!\n\n";
    echo "Email: {$email}\n";
    echo "Password: {$password}\n";
    echo "User UID: {$userUid}\n";
} else {
    echo "❌ Failed to create account: " . mysqli_error($con) . "\n";
}

mysqli_stmt_close($stmt);
mysqli_close($con);
?>
