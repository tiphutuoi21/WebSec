<?php
    require 'connection.php';
    
    // Validate and sanitize inputs
    $item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;
    
    // Validate quantity
    if ($quantity < 1) {
        $quantity = 1;
    }
    if ($quantity > 100) {
        $quantity = 100;
    }
    
    if ($item_id <= 0 || !isset($_SESSION['id'])) {
        header('location: products.php');
        exit;
    }
    
    $user_id = $_SESSION['id'];
    
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
                mysqli_stmt_execute($insert_stmt);
                mysqli_stmt_close($insert_stmt);
            }
        }
        
        mysqli_stmt_close($check_stmt);
    }
    
    header('location: products.php');
?>