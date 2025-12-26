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
    
    // Use prepared statement to prevent SQL injection
    $product_id = intval($_POST['id']);
    
    if ($product_id <= 0) {
        echo "<script>alert('Invalid product ID'); window.location.href='admin_manage_products.php';</script>";
        exit();
    }
    
    $delete_query = "DELETE FROM items WHERE id = ?";
    $stmt = mysqli_prepare($con, $delete_query);
    
    if (!$stmt) {
        error_log("Delete product error: " . mysqli_error($con));
        echo "<script>alert('Database error occurred'); window.location.href='admin_manage_products.php';</script>";
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Delete product execute error: " . mysqli_stmt_error($stmt));
        echo "<script>alert('Failed to delete product'); window.location.href='admin_manage_products.php';</script>";
        mysqli_stmt_close($stmt);
        exit();
    }
    
    mysqli_stmt_close($stmt);
    
    // Log security event
    SecurityEnhancements::logSecurityEvent($con, 'admin_delete_product', 'Product ID: ' . $product_id);
    
    header('location: admin_manage_products.php');
?>
