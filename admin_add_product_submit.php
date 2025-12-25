<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Validate session and check admin access
    SecurityHelper::validateSessionTimeout($con);
    if(!isset($_SESSION['admin_email']) || intval($_SESSION['admin_role_id'] ?? 0) !== 1) {
        header('location: admin_login.php');
        exit();
    }
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !SecurityHelper::verifyCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    // Get and sanitize input
    $name = SecurityHelper::getString('name', 'POST');
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    
    // Validate inputs
    if (empty($name) || $price <= 0) {
        echo "Invalid product data";
        ?>
        <meta http-equiv="refresh" content="2;url=admin_add_product.php" />
        <?php
        exit();
    }
    
    // Use prepared statement to prevent SQL injection
    $add_product_query = "INSERT INTO items(name, price) VALUES (?, ?)";
    $stmt = mysqli_prepare($con, $add_product_query);
    
    if (!$stmt) {
        error_log("Add product error: " . mysqli_error($con));
        echo "Error adding product";
        ?>
        <meta http-equiv="refresh" content="2;url=admin_add_product.php" />
        <?php
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "sd", $name, $price);
    
    if(mysqli_stmt_execute($stmt)) {
        // Log security event
        SecurityHelper::logSecurityEvent($con, 'admin_add_product', 'Product: ' . $name);
        
        echo "Product added successfully!";
        ?>
        <meta http-equiv="refresh" content="2;url=admin_manage_products.php" />
        <?php
    } else {
        error_log("Add product execute error: " . mysqli_stmt_error($stmt));
        echo "Error adding product";
        ?>
        <meta http-equiv="refresh" content="2;url=admin_add_product.php" />
        <?php
    }
    
    mysqli_stmt_close($stmt);
?>
