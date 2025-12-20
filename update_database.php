<?php
    require 'connection.php';
    
    echo "<h2>Updating Database Schema...</h2>";
    echo "<p>Checking and updating database tables...</p>";
    
    try {
        // Check if quantity column already exists
        $check_query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='users_items' AND COLUMN_NAME='quantity' AND TABLE_SCHEMA=DATABASE()";
        $check_result = mysqli_query($con, $check_query);
        
        if (mysqli_num_rows($check_result) == 0) {
            // Column doesn't exist, add it
            $alter_query = "ALTER TABLE users_items ADD COLUMN quantity int(11) DEFAULT 1 AFTER item_id";
            if (mysqli_query($con, $alter_query)) {
                echo "<p style='color: green;'><strong>✓ Successfully added quantity column to users_items table</strong></p>";
            } else {
                echo "<p style='color: red;'><strong>✗ Error adding quantity column: " . mysqli_error($con) . "</strong></p>";
            }
        } else {
            echo "<p style='color: blue;'><strong>ℹ Quantity column already exists in users_items table</strong></p>";
        }
        
        // Check and add other missing columns if needed
        $check_email_verified = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='users' AND COLUMN_NAME='email_verified' AND TABLE_SCHEMA=DATABASE()";
        $check_result2 = mysqli_query($con, $check_email_verified);
        
        if (mysqli_num_rows($check_result2) == 0) {
            $alter_users = "ALTER TABLE users ADD COLUMN email_verified BOOLEAN DEFAULT 0, ADD COLUMN verification_token VARCHAR(255), ADD COLUMN token_expiry DATETIME, ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            if (mysqli_query($con, $alter_users)) {
                echo "<p style='color: green;'><strong>✓ Successfully added email verification columns to users table</strong></p>";
            } else {
                echo "<p style='color: red;'><strong>✗ Error adding email columns: " . mysqli_error($con) . "</strong></p>";
            }
        } else {
            echo "<p style='color: blue;'><strong>ℹ Email verification columns already exist in users table</strong></p>";
        }
        
        // Check and add role column to admins table
        $check_role = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='admins' AND COLUMN_NAME='role' AND TABLE_SCHEMA=DATABASE()";
        $check_result3 = mysqli_query($con, $check_role);
        
        if (mysqli_num_rows($check_result3) == 0) {
            $alter_admins = "ALTER TABLE admins ADD COLUMN role enum('admin','sales_manager') DEFAULT 'sales_manager'";
            if (mysqli_query($con, $alter_admins)) {
                echo "<p style='color: green;'><strong>✓ Successfully added role column to admins table</strong></p>";
            } else {
                echo "<p style='color: red;'><strong>✗ Error adding role column: " . mysqli_error($con) . "</strong></p>";
            }
        } else {
            echo "<p style='color: blue;'><strong>ℹ Role column already exists in admins table</strong></p>";
        }
        
        // Add default sales manager account if not exists
        $check_sales = "SELECT id FROM admins WHERE email='sales@lifestylestore.com'";
        $check_result4 = mysqli_query($con, $check_sales);
        
        if (mysqli_num_rows($check_result4) == 0) {
            $insert_sales = "INSERT INTO admins (id, email, password, role) VALUES (2, 'sales@lifestylestore.com', '57f231b1ec41dc6641270cb09a56f897', 'sales_manager')";
            if (mysqli_query($con, $insert_sales)) {
                echo "<p style='color: green;'><strong>✓ Successfully added default Sales Manager account</strong></p>";
                echo "<p style='color: gray;'><strong>Sales Manager Credentials:</strong></p>";
                echo "<p>Email: sales@lifestylestore.com | Password: password</p>";
            } else {
                echo "<p style='color: red;'><strong>✗ Error adding sales manager: " . mysqli_error($con) . "</strong></p>";
            }
        } else {
            echo "<p style='color: blue;'><strong>ℹ Sales Manager account already exists</strong></p>";
        }
        
        echo "<hr>";
        echo "<p style='color: green;'><strong>✓ Database schema updated successfully!</strong></p>";
        echo "<p><a href='products.php' class='btn btn-primary'>Go to Products Page</a></p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'><strong>Error: " . htmlspecialchars($e->getMessage()) . "</strong></p>";
    }
?>
