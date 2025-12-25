<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Check if user is logged in
    SecurityHelper::requireLogin();
    SecurityHelper::validateSessionTimeout($con);
    SecurityHelper::ensureOrderUidColumn($con);
    
    $user_id = SecurityHelper::getUserId();
    
    // Get cart items with prepared statement (prevents SQLi)
    $cart_query = "SELECT ci.id, ci.user_id, ci.item_id, ci.quantity, i.price 
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
        if (intval($row['user_id'] ?? 0) !== $user_id) {
            echo "<script>alert('Unauthorized cart access'); window.location.href='cart.php';</script>";
            exit();
        }
        
        $unit_price = floatval($row['price']);
        $quantity = intval($row['quantity']);
        $subtotal = round($unit_price * $quantity, 2); // avoid float drift
        $total_amount = round($total_amount + $subtotal, 2);
        $cart_items[] = array(
            'item_id' => intval($row['item_id']), 
            'quantity' => $quantity, 
            'unit_price' => $unit_price, 
            'subtotal' => $subtotal
        );
    }
    
    $total_amount = round(floatval($total_amount), 2);
    
    // Start transaction
    mysqli_begin_transaction($con);
    
    try {
        // Create order with status_id = 1 (pending) using prepared statement and UUID v7
        $order_uid = SecurityHelper::generateUuidV7();
        $insert_order = "INSERT INTO orders (user_id, total_amount, status_id, order_uid) VALUES (?, ?, 1, ?)";
        $stmt = mysqli_prepare($con, $insert_order);
        
        if(!$stmt){
            throw new Exception("Prepare failed: " . mysqli_error($con));
        }
        
        // Ensure proper type conversion
        $user_id = intval($user_id);
        $total_amount = floatval($total_amount);
        
        // Bind parameters: i = integer, d = double
        if(!mysqli_stmt_bind_param($stmt, "ids", $user_id, $total_amount, $order_uid)){
            error_log("Order bind failed: " . mysqli_error($con));
            throw new Exception("Failed to process order. Please try again.");
        }
        
        if(!mysqli_stmt_execute($stmt)){
            throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
        }
        
        // Get affected rows and insert ID BEFORE closing statement
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        if($affected_rows <= 0){
            throw new Exception("Insert failed: No rows affected. User ID: $user_id, Amount: $total_amount");
        }
        
        // Get the last inserted ID from connection
        $order_id = mysqli_insert_id($con);
        
        // If mysqli_insert_id fails, try alternative method
        if($order_id <= 0){
            $id_query = "SELECT MAX(id) as last_id FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 1";
            $id_stmt = mysqli_prepare($con, $id_query);
            mysqli_stmt_bind_param($id_stmt, "i", $user_id);
            mysqli_stmt_execute($id_stmt);
            $id_result = mysqli_stmt_get_result($id_stmt);
            $id_row = mysqli_fetch_assoc($id_result);
            mysqli_stmt_close($id_stmt);
            
            $order_id = intval($id_row['last_id'] ?? 0);
        }
        
        if($order_id <= 0){
            throw new Exception("Failed to determine order ID after successful insert");
        }
        
        // Add items to order
        foreach($cart_items as $item){
            $insert_item = "INSERT INTO order_items (order_id, item_id, quantity, unit_price, subtotal, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = mysqli_prepare($con, $insert_item);
            
            if(!$stmt){
                error_log("Order items prepare failed: " . mysqli_error($con));
                throw new Exception("Failed to add items to order. Please try again.");
            }
            
            $item_id = $item['item_id'];
            $quantity = $item['quantity'];
            $unit_price = $item['unit_price'];
            $subtotal = $item['subtotal'];
            
            mysqli_stmt_bind_param($stmt, "iiidd", $order_id, $item_id, $quantity, $unit_price, $subtotal);
            
            if(!mysqli_stmt_execute($stmt)){
                error_log("Order items execute failed: " . mysqli_stmt_error($stmt));
                throw new Exception("Failed to add items to order. Please try again.");
            }
            
            mysqli_stmt_close($stmt);
            
            // Reduce stock quantity for this item
            $update_stock = "UPDATE items SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?";
            $stock_stmt = mysqli_prepare($con, $update_stock);
            
            if(!$stock_stmt){
                error_log("Stock update prepare failed: " . mysqli_error($con));
                throw new Exception("Failed to update stock. Please try again.");
            }
            
            mysqli_stmt_bind_param($stock_stmt, "iii", $quantity, $item_id, $quantity);
            
            if(!mysqli_stmt_execute($stock_stmt)){
                error_log("Stock update execute failed: " . mysqli_stmt_error($stock_stmt));
                throw new Exception("Failed to update stock. Please try again.");
            }
            
            // Check if stock update affected rows (verify stock was available)
            $affected_stock_rows = mysqli_stmt_affected_rows($stock_stmt);
            mysqli_stmt_close($stock_stmt);
            
            if($affected_stock_rows <= 0){
                // Stock was insufficient, rollback entire transaction
                throw new Exception("Insufficient stock for item ID: $item_id. Required: $quantity");
            }
            
            error_log("Stock reduced for item $item_id: quantity $quantity decreased from inventory for order $order_id");
        }
        
        // Clear cart
        $clear_cart = "DELETE FROM cart_items WHERE user_id = ?";
        $stmt = mysqli_prepare($con, $clear_cart);
        
        if(!$stmt){
            error_log("Clear cart prepare failed: " . mysqli_error($con));
            throw new Exception("Failed to clear cart. Please try again.");
        }
        
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if(!mysqli_stmt_execute($stmt)){
            throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
        
        mysqli_commit($con);
        $_SESSION['order_id'] = $order_id;
        $_SESSION['order_uid'] = $order_uid;
        $_SESSION['order_total'] = $total_amount;
        echo "<script>alert('Order placed successfully!'); window.location.href='order_confirmation.php';</script>";
        
    } catch(Exception $e) {
        mysqli_rollback($con);
        // Log detailed error for admin, show generic message to user
        error_log("Order placement error: " . $e->getMessage());
        echo "<script>alert('An error occurred while placing your order. Please try again.'); window.location.href='cart.php';</script>";
    }
?>
