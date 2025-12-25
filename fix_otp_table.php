<?php
require 'connection.php';

echo "<h2>Fixing OTP Table Column Size</h2>";

// First, let's check the current table structure
echo "<h3>Current Table Structure:</h3>";
$check_query = "DESCRIBE password_reset_otp";
$result = mysqli_query($con, $check_query);

echo "<table border='1' cellpadding='5'>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h3>Applying Fixes:</h3>";

// Drop and recreate the table with correct structure
$drop_query = "DROP TABLE password_reset_otp";
$create_query = "CREATE TABLE `password_reset_otp` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `used_at` timestamp NULL DEFAULT NULL,
  KEY `idx_user_id` (`user_id`),
  KEY `idx_email` (`email`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci";

// Execute drop
if (mysqli_query($con, $drop_query)) {
    echo "<p>✓ Dropped old password_reset_otp table</p>";
} else {
    echo "<p style='color: red;'>✗ Error dropping table: " . htmlspecialchars(mysqli_error($con)) . "</p>";
}

// Execute create
if (mysqli_query($con, $create_query)) {
    echo "<p style='color: green;'><strong>✓ SUCCESS!</strong> Created new password_reset_otp table with correct column sizes.</p>";
    
    // Verify the change
    $verify_query = "DESCRIBE password_reset_otp";
    $result = mysqli_query($con, $verify_query);
    
    echo "<h3>New Table Structure:</h3>";
    echo "<table border='1' cellpadding='5'>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>Changes Made:</h3>";
    echo "<ul>";
    echo "<li><strong>otp column:</strong> Changed from VARCHAR(10) to VARCHAR(64) to store full SHA256 hashes</li>";
    echo "<li><strong>expires_at column:</strong> Added DEFAULT CURRENT_TIMESTAMP to fix MySQL strict mode issue</li>";
    echo "<li><strong>used_at column:</strong> Added DEFAULT NULL for consistency</li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li><a href='change_password.php'>Request a new OTP</a> via Change Password</li>";
    echo "<li>Check your email for the OTP code</li>";
    echo "<li>Try verifying the OTP in <a href='verify_otp_password.php'>Verify OTP</a></li>";
    echo "</ol>";
    
} else {
    echo "<p style='color: red;'><strong>✗ ERROR:</strong> " . htmlspecialchars(mysqli_error($con)) . "</p>";
}

echo "<p><a href='index.php'>← Home</a></p>";
?>
