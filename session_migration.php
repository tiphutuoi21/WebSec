<?php
/**
 * session_migration.php
 * Creates necessary tables for session management and audit logging
 * Run this once to initialize the tables
 */

require 'connection.php';

// Create sessions table
$sessions_table = "CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    user_email VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    session_type VARCHAR(20) NOT NULL DEFAULT 'customer',
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    logged_out_time TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT 1,
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_is_active (is_active),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

// Create session audit log table
$audit_log_table = "CREATE TABLE IF NOT EXISTS session_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_timestamp (timestamp),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

// Execute table creation queries
if (mysqli_query($con, $sessions_table)) {
    echo "✓ Sessions table created/verified successfully<br>";
} else {
    echo "✗ Error creating sessions table: " . mysqli_error($con) . "<br>";
}

if (mysqli_query($con, $audit_log_table)) {
    echo "✓ Session audit log table created/verified successfully<br>";
} else {
    echo "✗ Error creating session audit log table: " . mysqli_error($con) . "<br>";
}

// Create index for faster lookups
$cleanup_query = "DELETE FROM sessions WHERE is_active = 0 AND logged_out_time < DATE_SUB(NOW(), INTERVAL 7 DAY)";
if (mysqli_query($con, $cleanup_query)) {
    echo "✓ Cleaned up old inactive sessions<br>";
}

mysqli_close($con);
echo "<br>Session tables ready!";
?>
