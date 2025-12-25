<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Allow only POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: cart.php');
        exit;
    }
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !SecurityHelper::verifyCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }

    // Check if user is logged in
    SecurityHelper::requireLogin();
    
    $cart_item_id = intval($_POST['id']);
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
    
    header('Location: cart.php');
?>
