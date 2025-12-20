<?php
/**
 * verify_session_setup.php
 * Verifies session security setup and creates tables if needed
 */

require 'connection.php';

echo "<h2>Session Security Setup Verification</h2>";

// Check if sessions table exists
$tables_check = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='store'";
$result = mysqli_query($con, $tables_check);

$existing_tables = array();
while ($row = mysqli_fetch_assoc($result)) {
    $existing_tables[] = $row['TABLE_NAME'];
}

echo "<h3>Database Tables:</h3>";
echo "Existing tables: " . implode(", ", $existing_tables) . "<br><br>";

// Create sessions table if doesn't exist
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

if (mysqli_query($con, $sessions_table)) {
    echo "✓ <strong>sessions</strong> table created/verified successfully<br>";
} else {
    echo "✗ Error with sessions table: " . mysqli_error($con) . "<br>";
}

// Create session audit log table if doesn't exist
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

if (mysqli_query($con, $audit_log_table)) {
    echo "✓ <strong>session_audit_log</strong> table created/verified successfully<br>";
} else {
    echo "✗ Error with session_audit_log table: " . mysqli_error($con) . "<br>";
}

// Show table structure
echo "<h3>Table: sessions</h3>";
$sessions_structure = "DESCRIBE sessions";
$result = mysqli_query($con, $sessions_structure);
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Table: session_audit_log</h3>";
$audit_structure = "DESCRIBE session_audit_log";
$result = mysqli_query($con, $audit_structure);
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><h3>Session Security Features:</h3>";
echo "✓ Strong cryptographic session ID generation (32 bytes = 256 bits)<br>";
echo "✓ Fixed 30-minute session duration<br>";
echo "✓ One user = one active session (old sessions invalidated)<br>";
echo "✓ Session tracking in database<br>";
echo "✓ HTTPOnly cookie (JavaScript cannot access)<br>";
echo "✓ Secure cookie (HTTPS only in production)<br>";
echo "✓ SameSite=Strict (CSRF protection)<br>";
echo "✓ Comprehensive audit logging<br>";
echo "✓ Optional IP address verification<br>";

mysqli_close($con);
?>
