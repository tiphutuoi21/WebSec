<?php
    require 'connection.php';
    
    echo "<h2>Database Schema Update</h2>";
    
    // Array of ALTER statements to add email verification columns
    $alter_statements = [
        "ALTER TABLE users ADD COLUMN email_verified BOOLEAN DEFAULT 0",
        "ALTER TABLE users ADD COLUMN verification_token VARCHAR(255)",
        "ALTER TABLE users ADD COLUMN token_expiry DATETIME",
        "ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ];
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($alter_statements as $sql) {
        echo "<p><strong>Executing:</strong> " . htmlspecialchars($sql) . "</p>";
        
        if (mysqli_query($con, $sql)) {
            echo "<p style='color: green;'><strong>✅ Success</strong></p>";
            $success_count++;
        } else {
            $error = mysqli_error($con);
            // Check if column already exists
            if (strpos($error, 'Duplicate column name') !== false) {
                echo "<p style='color: orange;'><strong>⚠️  Column already exists (skipped)</strong></p>";
                $success_count++;
            } else {
                echo "<p style='color: red;'><strong>❌ Error:</strong> " . htmlspecialchars($error) . "</p>";
                $error_count++;
            }
        }
        echo "<hr>";
    }
    
    echo "<h3>Summary</h3>";
    echo "<p><strong>Successful/Skipped:</strong> $success_count</p>";
    echo "<p><strong>Errors:</strong> $error_count</p>";
    
    if ($error_count === 0) {
        echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✅ Database schema updated successfully!</p>";
        echo "<p><a href='javascript:history.back()'>Go Back</a></p>";
    } else {
        echo "<p style='color: red; font-size: 18px; font-weight: bold;'>❌ Some errors occurred. Please check the output above.</p>";
        echo "<p><a href='javascript:history.back()'>Go Back</a></p>";
    }
    
    mysqli_close($con);
?>
