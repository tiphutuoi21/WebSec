<?php
// Quick email test script
require 'config.php';
require 'MailHelper.php';

echo "<h2>Email Configuration Test</h2>";

// Check environment variables
echo "<h3>Configuration Check:</h3>";
echo "GMAIL_EMAIL: " . (empty(GMAIL_EMAIL) ? "❌ NOT SET" : "✓ " . GMAIL_EMAIL) . "<br>";
echo "GMAIL_PASSWORD: " . (empty(GMAIL_PASSWORD) ? "❌ NOT SET" : "✓ SET (length: " . strlen(GMAIL_PASSWORD) . ")") . "<br>";
echo "GMAIL_FROM_NAME: " . (empty(GMAIL_FROM_NAME) ? "❌ NOT SET" : "✓ " . GMAIL_FROM_NAME) . "<br>";
echo "SITE_URL: " . (empty(SITE_URL) ? "❌ NOT SET" : "✓ " . SITE_URL) . "<br>";

// Check PHPMailer
echo "<h3>PHPMailer Check:</h3>";
if (file_exists('vendor/autoload.php')) {
    echo "✓ Composer autoload found<br>";
    require 'vendor/autoload.php';
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "✓ PHPMailer class available<br>";
    } else {
        echo "❌ PHPMailer class NOT found<br>";
    }
} else {
    echo "❌ Composer autoload NOT found<br>";
}

// Test sending email
echo "<h3>Test Email Send:</h3>";

if (!empty(GMAIL_EMAIL) && !empty(GMAIL_PASSWORD)) {
    try {
        $test_email = "test.verification@example.com";
        $test_name = "Test User";
        $test_token = bin2hex(random_bytes(32));
        
        // Try sending
        $result = MailHelper::sendVerificationEmail($test_email, $test_name, $test_token);
        
        if ($result) {
            echo "✓ Test email sent successfully!<br>";
            echo "Email would be sent to: " . htmlspecialchars($test_email) . "<br>";
            echo "Check the actual email inbox for a test message.<br>";
        } else {
            echo "❌ Test email failed to send<br>";
            echo "Check server error logs for details.<br>";
        }
    } catch (Exception $e) {
        echo "❌ Exception: " . htmlspecialchars($e->getMessage()) . "<br>";
    }
} else {
    echo "❌ Gmail credentials not properly configured<br>";
}

// Check recent signup attempts
echo "<h3>Recent Signup Records:</h3>";
require 'connection.php';

if ($con) {
    $query = "SELECT id, email, name, email_verified, created_at FROM users ORDER BY created_at DESC LIMIT 5";
    $result = mysqli_query($con, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Email</th><th>Name</th><th>Verified</th><th>Created</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . ($row['email_verified'] ? "✓ YES" : "❌ NO") . "</td>";
            echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No users in database yet.<br>";
    }
} else {
    echo "❌ Cannot connect to database<br>";
}
?>
