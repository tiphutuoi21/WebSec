<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Check if user is logged in
    SecurityHelper::requireLogin();
    
    $user_id = SecurityHelper::getUserId();
    
    // Get cart items with prepared statement (prevents SQLi)
    $cart_query = "SELECT ci.id, ci.item_id, ci.quantity, i.price 
                   FROM cart_items ci
                   INNER JOIN items i ON i.id = ci.item_id
                   WHERE ci.user_id = ?";
    
    $stmt = mysqli_prepare($con, $cart_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $cart_result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    
    if(mysqli_num_rows($cart_result) == 0){
        echo "<script>alert('Your cart is empty!'); window.location.href='cart.php';</script>";
        exit();
    }
    
    // Calculate total and verify ownership
    $total_amount = 0;
    $cart_items = array();
    
    while($row = mysqli_fetch_array($cart_result)){
        // Double-check ownership (defense in depth)
        if (intval($row['user_id'] ?? 0) !== $user_id && !SecurityHelper::verifyResourceOwnership($con, 'cart_item', $row['id'], $user_id)) {
            echo "<script>alert('Unauthorized cart access'); window.location.href='cart.php';</script>";
            exit();
        }
        
        $subtotal = floatval($row['price']) * intval($row['quantity']);
        $total_amount += $subtotal;
        $cart_items[] = array(
            'item_id' => intval($row['item_id']), 
            'quantity' => intval($row['quantity']), 
            'unit_price' => floatval($row['price']), 
            'subtotal' => $subtotal
        );
    }
    
    $total_amount = floatval($total_amount);
    
    // Start transaction
    mysqli_begin_transaction($con);
    
    try {
        // Create order with status_id = 1 (pending) using prepared statement
        $insert_order = "INSERT INTO orders (user_id, total_amount, status_id, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW())";
        $stmt = mysqli_prepare($con, $insert_order);
        
        if(!$stmt){
            throw new Exception("Prepare failed: " . mysqli_error($con));
        }
        
        mysqli_stmt_bind_param($stmt, "id", $user_id, $total_amount);
        
        if(!mysqli_stmt_execute($stmt)){
            throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
        }
        
        $order_id = mysqli_insert_id($con);
        mysqli_stmt_close($stmt);
        
        if($order_id <= 0){
            throw new Exception("Failed to get order ID");
        }
        
        // Add items to order
        foreach($cart_items as $item){
            $insert_item = "INSERT INTO order_items (order_id, item_id, quantity, unit_price, subtotal, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = mysqli_prepare($con, $insert_item);
            
            if(!$stmt){
                throw new Exception("Prepare failed: " . mysqli_error($con));
            }
            
            $item_id = $item['item_id'];
            $quantity = $item['quantity'];
            $unit_price = $item['unit_price'];
            $subtotal = $item['subtotal'];
            
            mysqli_stmt_bind_param($stmt, "iiidd", $order_id, $item_id, $quantity, $unit_price, $subtotal);
            
            if(!mysqli_stmt_execute($stmt)){
                throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
            }
            
            mysqli_stmt_close($stmt);
        }
        
        // Clear cart
        $clear_cart = "DELETE FROM cart_items WHERE user_id = ?";
        $stmt = mysqli_prepare($con, $clear_cart);
        
        if(!$stmt){
            throw new Exception("Prepare failed: " . mysqli_error($con));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if(!mysqli_stmt_execute($stmt)){
            throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
        
        mysqli_commit($con);
        echo "<script>alert('Order #$order_id placed successfully!'); window.location.href='products.php';</script>";
        
    } catch(Exception $e) {
        mysqli_rollback($con);
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location.href='cart.php';</script>";
    }
?>
