<?php
    require 'connection.php';
    
    // Check if user is logged in
    if(!isset($_SESSION['user_email'])){
        header('location: login.php');
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Get cart items for the user from the new cart_items table
    $cart_query = "SELECT ci.id, ci.item_id, ci.quantity, i.price, i.name 
                   FROM cart_items ci
                   INNER JOIN items i ON i.id = ci.item_id
                   WHERE ci.user_id = $user_id";
    $cart_result = mysqli_query($con, $cart_query) or die(mysqli_error($con));
    
    // Check if cart is empty
    if(mysqli_num_rows($cart_result) == 0){
        echo "<script>alert('Your cart is empty!'); window.location.href='cart.php';</script>";
        exit();
    }
    
    // Calculate total amount
    $total_amount = 0;
    $cart_items = array();
    
    while($row = mysqli_fetch_array($cart_result)){
        $subtotal = $row['price'] * $row['quantity'];
        $total_amount += $subtotal;
        $cart_items[] = array(
            'item_id' => $row['item_id'],
            'quantity' => $row['quantity'],
            'unit_price' => $row['price'],
            'subtotal' => $subtotal,
            'name' => $row['name']
        );
    }
    
    // Start transaction
    mysqli_begin_transaction($con);
    
    try {
        // Insert order into orders table with status_id = 2 (confirmed)
        $insert_order_query = "INSERT INTO orders (user_id, total_amount, status_id, created_at, updated_at)
                              VALUES ($user_id, $total_amount, 2, NOW(), NOW())";
        
        if(!mysqli_query($con, $insert_order_query)){
            throw new Exception(mysqli_error($con));
        }
        
        // Get the inserted order ID
        $order_id = mysqli_insert_id($con);
        
        // Insert each cart item into order_items
        foreach($cart_items as $item){
            $insert_item_query = "INSERT INTO order_items (order_id, item_id, quantity, unit_price, subtotal, created_at)
                                 VALUES ($order_id, {$item['item_id']}, {$item['quantity']}, {$item['unit_price']}, {$item['subtotal']}, NOW())";
            
            if(!mysqli_query($con, $insert_item_query)){
                throw new Exception(mysqli_error($con));
            }
        }
        
        // Clear the cart items after successful order creation
        $clear_cart_query = "DELETE FROM cart_items WHERE user_id = $user_id";
        if(!mysqli_query($con, $clear_cart_query)){
            throw new Exception(mysqli_error($con));
        }
        
        // Commit transaction
        mysqli_commit($con);
        
        // Redirect to success page
        $_SESSION['order_id'] = $order_id;
        $_SESSION['order_total'] = $total_amount;
        header('location: order_confirmation.php');
        
    } catch(Exception $e) {
        // Rollback on error
        mysqli_rollback($con);
        echo "<script>alert('Error processing order: " . addslashes($e->getMessage()) . "'); window.location.href='cart.php';</script>";
        exit();
    }
?>
