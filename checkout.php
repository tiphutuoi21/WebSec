<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Check if user is logged in
    SecurityHelper::requireLogin();
    SecurityHelper::validateSessionTimeout($con);
    SecurityHelper::ensureOrderUidColumn($con);
    
    $user_id = SecurityHelper::getUserId();
    
    // Get cart items for the user from the new cart_items table using prepared statement
    $cart_query = "SELECT ci.id, ci.user_id, ci.item_id, ci.quantity, i.price, i.name 
                   FROM cart_items ci
                   INNER JOIN items i ON i.id = ci.item_id
                   WHERE ci.user_id = ?";
    
    $stmt = mysqli_prepare($con, $cart_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $cart_result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    
    // Check if cart is empty
    if(mysqli_num_rows($cart_result) == 0){
        echo "<script>alert('Your cart is empty!'); window.location.href='cart.php';</script>";
        exit();
    }
    
    // Calculate total amount
    $total_amount = 0;
    $cart_items = array();
    
    while($row = mysqli_fetch_array($cart_result)){
        // Verify ownership
        if(intval($row['user_id']) !== $user_id){
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
            'subtotal' => $subtotal,
            'name' => $row['name']
        );
    }

    // Final total normalization to two decimals
    $total_amount = round($total_amount, 2);
    
    // Start transaction
    mysqli_begin_transaction($con);
    
    try {
        // Insert order into orders table with status_id = 1 (pending) and UUID v7
        $order_uid = SecurityHelper::generateUuidV7();
        $insert_order_query = "INSERT INTO orders (user_id, total_amount, status_id, order_uid) VALUES (?, ?, 1, ?)";
        
        $stmt = mysqli_prepare($con, $insert_order_query);
        if(!$stmt){
            throw new Exception("Prepare failed: " . mysqli_error($con));
        }
        
        // Ensure proper type conversion
        $user_id = intval($user_id);
        $total_amount = floatval($total_amount);
        
        // Bind parameters: i = integer, d = double
        if(!mysqli_stmt_bind_param($stmt, "ids", $user_id, $total_amount, $order_uid)){
            throw new Exception("Bind failed: " . mysqli_error($con));
        }
        
        if(!mysqli_stmt_execute($stmt)){
            throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
        }
        
        // Get the inserted order ID
        $order_id = mysqli_insert_id($con);
        mysqli_stmt_close($stmt);
        
        if($order_id <= 0){
            throw new Exception("Failed to get order ID after insert");
        }
        
        // Insert each cart item into order_items using prepared statement
        foreach($cart_items as $item){
            $insert_item_query = "INSERT INTO order_items (order_id, item_id, quantity, unit_price, subtotal, created_at)
                                 VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = mysqli_prepare($con, $insert_item_query);
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
        
        // Clear the cart items after successful order creation using prepared statement
        $clear_cart_query = "DELETE FROM cart_items WHERE user_id = ?";
        
        $stmt = mysqli_prepare($con, $clear_cart_query);
        if(!$stmt){
            throw new Exception("Prepare failed: " . mysqli_error($con));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if(!mysqli_stmt_execute($stmt)){
            throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
        
        // Commit transaction
        mysqli_commit($con);
        
        // Log successful order
        SecurityHelper::logSecurityEvent($con, 'order_placed', 'Order #' . $order_id . ' placed successfully');
        
        // Redirect to success page
        $_SESSION['order_id'] = $order_id;
        $_SESSION['order_uid'] = $order_uid;
        $_SESSION['order_total'] = $total_amount;
        header('Location: order_confirmation.php');
        
    } catch(Exception $e) {
        // Rollback on error
        mysqli_rollback($con);
        echo "<script>alert('Error processing order: " . addslashes($e->getMessage()) . "'); window.location.href='cart.php';</script>";
        exit();
    }
?>
