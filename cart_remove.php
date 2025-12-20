<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Check if user is logged in
    SecurityHelper::requireLogin();
    
    $cart_item_id = intval($_GET['id']);
    $user_id = SecurityHelper::getUserId();
    
    // Verify the cart item belongs to this user (prevent direct object reference abuse)
    if (!SecurityHelper::verifyResourceOwnership($con, 'cart_item', $cart_item_id, $user_id)) {
        echo "<script>alert('Unauthorized access'); window.location.href='cart.php';</script>";
        exit();
    }
    
    // Use prepared statement to prevent SQL injection
    $delete_query = "DELETE FROM cart_items WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($con, $delete_query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $cart_item_id, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    header('location: cart.php');
?>
