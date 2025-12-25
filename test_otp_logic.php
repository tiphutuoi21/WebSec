<?php
require 'connection.php';
require 'SecurityHelper.php';

echo "<h2>OTP Logic Debug Test</h2>";

if (!isset($_SESSION['id'])) {
    echo "<p><strong>ERROR: You are not logged in. <a href='login.php'>Login first</a></strong></p>";
    exit();
}

$user_id = $_SESSION['id'];

// Check if user submitted an OTP to test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_otp'])) {
    $submitted_otp = $_POST['test_otp'];
    $submitted_otp_trimmed = trim($submitted_otp);
    $submitted_otp_hash = hash('sha256', $submitted_otp_trimmed);
    
    echo "<h3>Testing OTP Verification Logic</h3>";
    echo "<p><strong>Submitted OTP:</strong> " . htmlspecialchars($submitted_otp_trimmed) . " (length: " . strlen($submitted_otp_trimmed) . ")</p>";
    echo "<p><strong>SHA256 Hash:</strong> " . htmlspecialchars($submitted_otp_hash) . "</p>";
    
    // Get the latest OTP record from database
    $db_query = "SELECT id, otp, expires_at, is_used, 
                        CASE WHEN expires_at > NOW() THEN 'NOT EXPIRED' ELSE 'EXPIRED' END as exp_check
                 FROM password_reset_otp 
                 WHERE user_id = ? 
                 ORDER BY created_at DESC LIMIT 1";
    
    $stmt = mysqli_prepare($con, $db_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $otp_record = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    echo "<h4>Database Record:</h4>";
    if ($otp_record) {
        echo "<p><strong>Stored Hash:</strong> " . htmlspecialchars($otp_record['otp']) . "</p>";
        echo "<p><strong>Is Used:</strong> " . ($otp_record['is_used'] ? "YES" : "NO") . "</p>";
        echo "<p><strong>Expiry Check:</strong> " . $otp_record['exp_check'] . "</p>";
        echo "<p><strong>Expires At:</strong> " . $otp_record['expires_at'] . "</p>";
        
        echo "<h4>Hash Comparison:</h4>";
        echo "<p><strong>Submitted Hash:</strong> " . htmlspecialchars($submitted_otp_hash) . "</p>";
        echo "<p><strong>DB Hash:        </strong> " . htmlspecialchars($otp_record['otp']) . "</p>";
        
        if ($submitted_otp_hash === $otp_record['otp']) {
            echo "<p style='color: green; font-weight: bold;'>✓ HASHES MATCH!</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>✗ HASHES DO NOT MATCH</p>";
            echo "<p>This indicates the OTP values are different or have extra/missing characters.</p>";
        }
        
        echo "<h4>Verification Check Simulation:</h4>";
        
        // Simulate the exact database query from verifyPasswordChangeOTP
        $verify_query = "SELECT id FROM password_reset_otp 
                        WHERE user_id = ? AND otp = ? AND is_used = 0 AND expires_at > NOW()";
        $verify_stmt = mysqli_prepare($con, $verify_query);
        
        if ($verify_stmt) {
            mysqli_stmt_bind_param($verify_stmt, "is", $user_id, $submitted_otp_hash);
            mysqli_stmt_execute($verify_stmt);
            $verify_result = mysqli_stmt_get_result($verify_stmt);
            $verify_row = mysqli_fetch_assoc($verify_result);
            mysqli_stmt_close($verify_stmt);
            
            if ($verify_row) {
                echo "<p style='color: green;'><strong>✓ Query would SUCCEED - OTP is valid</strong></p>";
            } else {
                echo "<p style='color: red;'><strong>✗ Query would FAIL - checking why...</strong></p>";
                
                // Check each condition separately
                echo "<h4>Condition Checks:</h4>";
                
                // Check 1: user_id match
                $check1 = mysqli_query($con, "SELECT 1 FROM password_reset_otp WHERE user_id = $user_id LIMIT 1");
                echo "<p>1. User ID matches: " . (mysqli_num_rows($check1) > 0 ? "✓ YES" : "✗ NO") . "</p>";
                
                // Check 2: hash match
                $check2_query = "SELECT 1 FROM password_reset_otp WHERE user_id = $user_id AND otp = '" . mysqli_real_escape_string($con, $submitted_otp_hash) . "' LIMIT 1";
                $check2 = mysqli_query($con, $check2_query);
                echo "<p>2. Hash matches stored hash: " . (mysqli_num_rows($check2) > 0 ? "✓ YES" : "✗ NO") . "</p>";
                
                // Check 3: not used
                $check3_query = "SELECT 1 FROM password_reset_otp WHERE user_id = $user_id AND otp = '" . mysqli_real_escape_string($con, $submitted_otp_hash) . "' AND is_used = 0 LIMIT 1";
                $check3 = mysqli_query($con, $check3_query);
                echo "<p>3. OTP not used (is_used = 0): " . (mysqli_num_rows($check3) > 0 ? "✓ YES" : "✗ NO") . "</p>";
                
                // Check 4: not expired
                $check4_query = "SELECT 1 FROM password_reset_otp WHERE user_id = $user_id AND otp = '" . mysqli_real_escape_string($con, $submitted_otp_hash) . "' AND expires_at > NOW() LIMIT 1";
                $check4 = mysqli_query($con, $check4_query);
                echo "<p>4. OTP not expired (expires_at > NOW()): " . (mysqli_num_rows($check4) > 0 ? "✓ YES" : "✗ NO") . "</p>";
            }
        }
    } else {
        echo "<p style='color: red;'><strong>No OTP record found in database for this user.</strong></p>";
    }
    
    echo "<hr>";
}

echo "<h3>Test the OTP Verification Logic:</h3>";
echo "<form method='POST'>";
echo "<p><label>Enter OTP: <input type='text' name='test_otp' maxlength='6' pattern='[0-9]{6}' placeholder='000000' required></label></p>";
echo "<p><button type='submit'>Test OTP Logic</button></p>";
echo "</form>";

echo "<p><a href='change_password.php'>← Request New OTP</a> | <a href='index.php'>Home</a></p>";
?>
