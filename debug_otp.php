<?php
// OTP Verification Debug Script
require 'connection.php';
require 'SecurityHelper.php';

echo "<h2>OTP Verification Diagnostic Tool</h2>";

if (!isset($_SESSION['id'])) {
    echo "<p><strong>ERROR: You are not logged in. <a href='login.php'>Login first</a></strong></p>";
    exit();
}

$user_id = $_SESSION['id'];
$message = '';

// Check if user submitted an OTP to test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_otp'])) {
    $test_otp = $_POST['test_otp'];
    $test_otp_clean = trim($test_otp);
    $test_otp_hash = hash('sha256', $test_otp_clean);
    
    echo "<h3 style='color: blue;'>Testing OTP: " . htmlspecialchars($test_otp_clean) . "</h3>";
    
    // Check what's actually in the database
    $db_query = "SELECT id, otp, is_used, expires_at, CASE WHEN expires_at > NOW() THEN 'VALID' ELSE 'EXPIRED' END as expiry_status FROM password_reset_otp WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
    $db_stmt = mysqli_prepare($con, $db_query);
    mysqli_stmt_bind_param($db_stmt, "i", $user_id);
    mysqli_stmt_execute($db_stmt);
    $db_result = mysqli_stmt_get_result($db_stmt);
    $latest_otp = mysqli_fetch_assoc($db_result);
    mysqli_stmt_close($db_stmt);
    
    echo "<h4>Database Information:</h4>";
    if ($latest_otp) {
        echo "<p><strong>Latest OTP Hash in DB:</strong> " . htmlspecialchars(substr($latest_otp['otp'], 0, 32) . "...") . "</p>";
        echo "<p><strong>OTP Hash Length:</strong> " . strlen($latest_otp['otp']) . " characters (SHA256 = 64 chars)</p>";
        echo "<p><strong>Expiry Status:</strong> " . $latest_otp['expiry_status'] . "</p>";
        echo "<p><strong>Is Used:</strong> " . ($latest_otp['is_used'] ? "YES (already verified)" : "NO") . "</p>";
        echo "<p><strong>Expires At:</strong> " . $latest_otp['expires_at'] . "</p>";
    } else {
        echo "<p style='color: red;'><strong>ERROR: No OTP found in database for this user!</strong></p>";
    }
    
    echo "<h4>Your Input:</h4>";
    echo "<p><strong>You entered:</strong> " . htmlspecialchars($test_otp_clean) . "</p>";
    echo "<p><strong>Input Length:</strong> " . strlen($test_otp_clean) . " characters</p>";
    echo "<p><strong>Your Input Hash:</strong> " . htmlspecialchars(substr($test_otp_hash, 0, 32) . "...") . "</p>";
    
    if ($latest_otp) {
        echo "<h4>Comparison:</h4>";
        $matches = ($test_otp_hash === $latest_otp['otp']);
        echo "<p><strong>Hash Match:</strong> " . ($matches ? "✓ YES" : "✗ NO") . "</p>";
    }
    
    echo "<h4>Verification Result:</h4>";
    // Now actually try to verify
    $verify_result = SecurityHelper::verifyPasswordChangeOTP($user_id, $test_otp_clean, $con);
    if ($verify_result['valid']) {
        echo "<p style='color: green; font-size: 16px; font-weight: bold;'>✓ VERIFICATION SUCCESSFUL: " . htmlspecialchars($verify_result['message']) . "</p>";
    } else {
        echo "<p style='color: red; font-size: 16px; font-weight: bold;'>✗ VERIFICATION FAILED: " . htmlspecialchars($verify_result['message']) . "</p>";
    }
    
    echo "<hr>";
}

// Show all OTP records
$query = "SELECT id, user_id, email, otp, created_at, expires_at, is_used, used_at FROM password_reset_otp WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

echo "<h3>OTP Records for User ID: " . htmlspecialchars($user_id) . "</h3>";
echo "<p style='color: #666; font-style: italic;'>Note: OTPs are hashed with SHA256 for security - plaintext values are not stored in the database.</p>";

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>OTP Hash (first 32 chars)</th>";
    echo "<th>Created</th>";
    echo "<th>Expires At</th>";
    echo "<th>Is Used</th>";
    echo "<th>Time Remaining</th>";
    echo "<th>Status</th>";
    echo "</tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $created = new DateTime($row['created_at']);
        $expires = new DateTime($row['expires_at']);
        $now = new DateTime();
        $interval = $now->diff($expires);
        
        $is_expired = $expires < $now;
        $is_used = $row['is_used'] == 1;
        
        $status = "<span style='color: green;'>✓ VALID</span>";
        if ($is_expired) {
            $status = "<span style='color: red;'>✗ EXPIRED</span>";
        } elseif ($is_used) {
            $status = "<span style='color: orange;'>⚠ ALREADY USED</span>";
        }
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td><strong style='font-size: 12px; font-family: monospace; background-color: #f0f0f0; padding: 5px;'>" . htmlspecialchars(substr($row['otp'], 0, 32)) . "...</strong></td>";
        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
        echo "<td>" . htmlspecialchars($row['expires_at']) . "</td>";
        echo "<td>" . ($row['is_used'] ? "YES" : "NO") . "</td>";
        echo "<td>";
        if ($is_expired) {
            echo "<span style='color: red;'>EXPIRED " . abs($interval->i) . " min ago</span>";
        } else {
            echo $interval->format('%i min %s sec');
        }
        echo "</td>";
        echo "<td>" . $status . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'><strong>No OTP records found for this user.</strong></p>";
    echo "<p>Go to <a href='change_password.php'>Change Password</a> to request a new OTP.</p>";
}

mysqli_stmt_close($stmt);

echo "<hr>";
echo "<h3>Manual OTP Test Form:</h3>";
echo "<p>To test OTP verification, enter the 6-digit code you received via email:</p>";
echo "<form method='POST'>";
echo "<p>";
echo "<label>Enter OTP to test: <input type='text' name='test_otp' maxlength='6' pattern='[0-9]{6}' placeholder='000000' required></label>";
echo "<button type='submit'>Test OTP</button>";
echo "</p>";
echo "</form>";

echo "<hr>";
echo "<h3>How It Works:</h3>";
echo "<ul>";
echo "<li>When you request an OTP, it's generated and hashed with SHA256 before storing in the database</li>";
echo "<li>The plaintext OTP is sent to your email</li>";
echo "<li>When you submit the OTP for verification, it's hashed again and compared with the stored hash</li>";
echo "<li>This prevents anyone with database access from seeing actual OTP values</li>";
echo "</ul>";

echo "<p><a href='change_password.php'>← Request New OTP</a> | <a href='index.php'>Home</a></p>";
?>
