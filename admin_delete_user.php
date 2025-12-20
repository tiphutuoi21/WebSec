<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Require admin role
    SecurityHelper::requireAdmin();
    
    $user_id = intval($_GET['id']);
    
    // Verify user exists before attempting to delete
    $verify_query = "SELECT id FROM users WHERE id = ?";
    $stmt = mysqli_prepare($con, $verify_query);
    
    if (!$stmt) {
        echo "<script>alert('Database error'); window.location.href='admin_manage_users.php';</script>";
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $verify_result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    
    if (mysqli_num_rows($verify_result) === 0) {
        echo "<script>alert('User not found'); window.location.href='admin_manage_users.php';</script>";
        exit();
    }
    
    // Prevent deleting self
    if ($user_id === SecurityHelper::getUserId()) {
        echo "<script>alert('You cannot delete your own admin account'); window.location.href='admin_manage_users.php';</script>";
        exit();
    }
    
    // Delete user with prepared statement
    $delete_query = "DELETE FROM users WHERE id = ?";
    $stmt = mysqli_prepare($con, $delete_query);
    
    if (!$stmt) {
        echo "<script>alert('Database error'); window.location.href='admin_manage_users.php';</script>";
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    header('location: admin_manage_users.php');
?>
