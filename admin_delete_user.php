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
    
    $user_id = intval($_POST['id']);
    
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
        error_log("Delete user error: " . mysqli_error($con));
        echo "<script>alert('An error occurred'); window.location.href='admin_manage_users.php';</script>";
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    // Log security event
    SecurityHelper::logSecurityEvent($con, 'admin_delete_user', 'User ID: ' . $user_id);
    
    header('location: admin_manage_users.php');
?>
