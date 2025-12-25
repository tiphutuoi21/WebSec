<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Allow only POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: products.php');
        exit;
    }
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !SecurityHelper::verifyCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }

    // Check if user is logged in
    SecurityHelper::requireLogin();
    
    // Validate and sanitize inputs
    $item_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    // Validate quantity
    if ($quantity < 1) {
        $quantity = 1;
    }
    if ($quantity > 100) {
        $quantity = 100;
    }
    
    if ($item_id <= 0) {
        header('Location: products.php');
        exit;
    }
    
    // Get user_id from SecurityHelper (safer than $_SESSION)
    $user_id = SecurityHelper::getUserId();
    
    // Validate user_id is valid
    if ($user_id <= 0) {
        header('Location: login.php');
        exit;
    }
    
    // Verify user exists in database
    $verify_user = "SELECT id FROM users WHERE id = ?";
    $verify_stmt = mysqli_prepare($con, $verify_user);
    mysqli_stmt_bind_param($verify_stmt, "i", $user_id);
    mysqli_stmt_execute($verify_stmt);
    $verify_result = mysqli_stmt_get_result($verify_stmt);
    
    if (mysqli_num_rows($verify_result) === 0) {
        mysqli_stmt_close($verify_stmt);
        // User doesn't exist, clear session and redirect
        session_destroy();
        header('Location: login.php');
        exit;
    }
    mysqli_stmt_close($verify_stmt);
    
    // Check if item already exists in cart
    $check_query = "SELECT id, quantity FROM cart_items WHERE user_id = ? AND item_id = ?";
    $check_stmt = mysqli_prepare($con, $check_query);
    
    if ($check_stmt) {
        mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $item_id);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($result) > 0) {
            // Item already in cart, update quantity
            $row = mysqli_fetch_array($result);
            $new_quantity = $row['quantity'] + $quantity;
            
            if ($new_quantity > 100) {
                $new_quantity = 100;
            }
            
            $update_query = "UPDATE cart_items SET quantity = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($con, $update_query);
            
            if ($update_stmt) {
                mysqli_stmt_bind_param($update_stmt, "ii", $new_quantity, $row['id']);
                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);
            }
        } else {
            // New item, insert into cart_items table
            $insert_query = "INSERT INTO cart_items (user_id, item_id, quantity) VALUES (?, ?, ?)";
            $insert_stmt = mysqli_prepare($con, $insert_query);
            
            if ($insert_stmt) {
                mysqli_stmt_bind_param($insert_stmt, "iii", $user_id, $item_id, $quantity);
                if (!mysqli_stmt_execute($insert_stmt)) {
                    error_log("Cart insert error for user $user_id: " . mysqli_stmt_error($insert_stmt));
                }
                mysqli_stmt_close($insert_stmt);
            }
        }
        
        mysqli_stmt_close($check_stmt);
    }
    
    // Ensure session is written before redirect
    session_write_close();
    
    header('Location: products.php');
    exit;
?>