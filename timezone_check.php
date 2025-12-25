<?php
require 'connection.php';

echo "<h2>Timezone Diagnostic Information</h2>";
echo "<hr>";

echo "<h3>1. Server/PHP Information:</h3>";
echo "<p><strong>Current PHP Timezone:</strong> " . date_default_timezone_get() . "</p>";
echo "<p><strong>PHP Time Now:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>PHP Timestamp:</strong> " . time() . "</p>";

// Get server timezone from system
$server_tz = exec('powershell -Command "[System.TimeZone]::CurrentTimeZone.StandardName"');
echo "<p><strong>Windows System Timezone:</strong> " . htmlspecialchars($server_tz) . "</p>";

echo "<hr>";
echo "<h3>2. MySQL Database Information:</h3>";

// Get MySQL timezone settings
$tz_query = "SELECT @@global.time_zone, @@session.time_zone, NOW() as db_now, CURDATE() as db_date";
$tz_result = mysqli_query($con, $tz_query);
$tz_row = mysqli_fetch_assoc($tz_result);

echo "<p><strong>MySQL Global Timezone:</strong> " . htmlspecialchars($tz_row['@@global.time_zone']) . "</p>";
echo "<p><strong>MySQL Session Timezone:</strong> " . htmlspecialchars($tz_row['@@session.time_zone']) . "</p>";
echo "<p><strong>MySQL NOW():</strong> " . $tz_row['db_now'] . "</p>";
echo "<p><strong>MySQL CURDATE():</strong> " . $tz_row['db_date'] . "</p>";

echo "<hr>";
echo "<h3>3. OTP in Database:</h3>";

// Check current user's OTP
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
    $otp_query = "SELECT id, created_at, expires_at, 
                         TIMESTAMPDIFF(MINUTE, NOW(), expires_at) as minutes_remaining
                  FROM password_reset_otp 
                  WHERE user_id = ? AND is_used = 0
                  ORDER BY created_at DESC LIMIT 1";
    
    $stmt = mysqli_prepare($con, $otp_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $otp_result = mysqli_stmt_get_result($stmt);
    
    if ($otp_row = mysqli_fetch_assoc($otp_result)) {
        echo "<p><strong>Latest OTP Created:</strong> " . $otp_row['created_at'] . "</p>";
        echo "<p><strong>Latest OTP Expires:</strong> " . $otp_row['expires_at'] . "</p>";
        echo "<p><strong>Minutes Until Expiry:</strong> " . $otp_row['minutes_remaining'] . "</p>";
        
        if ($otp_row['minutes_remaining'] > 0) {
            echo "<p style='color: green;'><strong>✓ OTP is VALID</strong></p>";
        } else {
            echo "<p style='color: red;'><strong>✗ OTP has EXPIRED</strong></p>";
        }
    } else {
        echo "<p>No valid OTP found for this user.</p>";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "<p><a href='login.php'>Login</a> to see OTP information.</p>";
}

echo "<hr>";
echo "<h3>Recommendation:</h3>";
echo "<p>Based on the timezone information above, update the following in config.php:</p>";
echo "<pre>date_default_timezone_set('YOUR_ACTUAL_TIMEZONE');</pre>";

echo "<p>Common timezones:</p>";
echo "<ul>";
echo "<li><strong>Vietnam:</strong> date_default_timezone_set('Asia/Ho_Chi_Minh');</li>";
echo "<li><strong>Bangkok/Thailand:</strong> date_default_timezone_set('Asia/Bangkok');</li>";
echo "<li><strong>UTC:</strong> date_default_timezone_set('UTC');</li>";
echo "</ul>";

echo "<p><a href='change_password.php'>← Change Password</a> | <a href='index.php'>Home</a></p>";
?>
