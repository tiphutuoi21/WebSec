<?php
require 'config.php';
require 'MailHelper.php';

echo "<h2>PHPMailer Email Test</h2>";

// Test 1: Check if autoloader is loaded
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✅ Vendor autoloader exists<br>";
} else {
    echo "❌ Vendor autoloader NOT found<br>";
}

// Test 2: Check environment variables
echo "<h3>Configuration Check:</h3>";
echo "Gmail Email: " . (GMAIL_EMAIL ? "✅ " . substr(GMAIL_EMAIL, 0, 5) . "..." : "❌ NOT SET") . "<br>";
echo "Gmail Password: " . (GMAIL_PASSWORD ? "✅ Set" : "❌ NOT SET") . "<br>";
echo "From Name: " . (GMAIL_FROM_NAME ? "✅ " . GMAIL_FROM_NAME : "❌ NOT SET") . "<br>";
echo "Site URL: " . (SITE_URL ? "✅ " . SITE_URL : "❌ NOT SET") . "<br>";

// Test 3: Try sending test email
echo "<h3>Send Test Email:</h3>";

if (!GMAIL_EMAIL || !GMAIL_PASSWORD) {
    echo "<p style='color: red;'><strong>⚠️ Configuration incomplete!</strong></p>";
    echo "<p>Please update your .env file with:</p>";
    echo "<pre>GMAIL_EMAIL=your-email@gmail.com
GMAIL_PASSWORD=your-app-password
GMAIL_FROM_NAME=Lifestyle Store
SITE_URL=http://localhost/LifestyleStore</pre>";
    echo "<p><a href='javascript:history.back()'>Go Back</a></p>";
} else {
    $test_email = 'test@example.com';
    $test_token = bin2hex(random_bytes(16));
    
    echo "<p>Sending test email to: <strong>$test_email</strong></p>";
    echo "<p>Token: <strong>$test_token</strong></p>";
    
    $result = MailHelper::sendVerificationEmail($test_email, 'Test User', $test_token);
    
    if ($result) {
        echo "<p style='color: green;'><strong>✅ Email sent successfully!</strong></p>";
        echo "<p>The test email was sent. Check the destination email inbox (or spam folder).</p>";
        echo "<p><a href='javascript:history.back()'>Go Back</a></p>";
    } else {
        echo "<p style='color: red;'><strong>❌ Email sending failed!</strong></p>";
        echo "<p>Check your .env configuration and Gmail credentials.</p>";
        echo "<p>Make sure you:</p>";
        echo "<ul>";
        echo "<li>Have enabled 2-Factor Authentication on Gmail</li>";
        echo "<li>Generated an <a href='https://support.google.com/accounts/answer/185833' target='_blank'>App Password</a></li>";
        echo "<li>Used the 16-character app password (not your account password)</li>";
        echo "<li>Checked firewall/antivirus isn't blocking port 587</li>";
        echo "</ul>";
        echo "<p><a href='javascript:history.back()'>Go Back</a></p>";
    }
}
?>
