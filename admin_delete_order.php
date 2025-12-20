<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Require admin role
    SecurityHelper::requireAdmin();
    
    $order_id = intval($_GET['id']);
    
    // Verify order exists before attempting to delete
    $verify_query = "SELECT id FROM orders WHERE id = ?";
    $stmt = mysqli_prepare($con, $verify_query);
    
    if (!$stmt) {
        echo "<script>alert('Database error'); window.location.href='admin_manage_orders.php';</script>";
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $verify_result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    
    if (mysqli_num_rows($verify_result) === 0) {
        echo "<script>alert('Order not found'); window.location.href='admin_manage_orders.php';</script>";
        exit();
    }
    
    // Delete associated order items first (prepared statement)
    $delete_items_query = "DELETE FROM order_items WHERE order_id = ?";
    $stmt = mysqli_prepare($con, $delete_items_query);
    
    if (!$stmt) {
        echo "<script>alert('Database error'); window.location.href='admin_manage_orders.php';</script>";
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    // Then delete the order itself (prepared statement)
    $delete_query = "DELETE FROM orders WHERE id = ?";
    $stmt = mysqli_prepare($con, $delete_query);
    
    if (!$stmt) {
        echo "<script>alert('Database error'); window.location.href='admin_manage_orders.php';</script>";
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    header('location: admin_manage_orders.php');
?>
