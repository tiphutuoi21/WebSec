<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Require admin login and admin role
    if(!isset($_SESSION['admin_email'])){
        header('location: admin310817.php');
        exit();
    }
    
    if(intval($_SESSION['admin_role_id']) !== 1){
        echo "<script>alert('Chỉ Admin mới có quyền xóa Sales'); window.location.href='admin_dashboard.php';</script>";
        exit();
    }
    
    $sales_id = intval($_GET['id']);
    
    // Prevent deleting self
    $current_admin_id = isset($_SESSION['admin_id']) ? intval($_SESSION['admin_id']) : 0;
    if($sales_id === $current_admin_id){
        echo "<script>alert('Bạn không thể xóa tài khoản của chính mình'); window.location.href='admin_manage_sales.php';</script>";
        exit();
    }
    
    // Verify sales exists and is a sales manager
    $verify_query = "SELECT id FROM admins WHERE id = ? AND role_id = 2";
    $verify_stmt = mysqli_prepare($con, $verify_query);
    mysqli_stmt_bind_param($verify_stmt, "i", $sales_id);
    mysqli_stmt_execute($verify_stmt);
    $verify_result = mysqli_stmt_get_result($verify_stmt);
    
    if(mysqli_num_rows($verify_result) == 0){
        echo "<script>alert('Không tìm thấy tài khoản Sales'); window.location.href='admin_manage_sales.php';</script>";
        exit();
    }
    mysqli_stmt_close($verify_stmt);
    
    // Delete sales account
    $delete_query = "DELETE FROM admins WHERE id = ? AND role_id = 2 LIMIT 1";
    $delete_stmt = mysqli_prepare($con, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, "i", $sales_id);
    
    if(mysqli_stmt_execute($delete_stmt)){
        header('location: admin_manage_sales.php?deleted=1');
    } else {
        echo "<script>alert('Lỗi khi xóa tài khoản'); window.location.href='admin_manage_sales.php';</script>";
    }
    
    mysqli_stmt_close($delete_stmt);
    exit();
?>

