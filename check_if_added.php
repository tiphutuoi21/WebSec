<?php
    
    function check_if_added_to_cart($item_id){
        require 'connection.php';
        
        if(!isset($_SESSION['id'])){
            return 0;
        }
        
        $user_id = $_SESSION['id'];
        
        // Check in the new cart_items table
        $product_check_query = "SELECT * FROM cart_items WHERE item_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($con, $product_check_query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $item_id, $user_id);
            mysqli_stmt_execute($stmt);
            $product_check_result = mysqli_stmt_get_result($stmt);
            $num_rows = mysqli_num_rows($product_check_result);
            mysqli_stmt_close($stmt);
            
            if($num_rows >= 1) return 1;
        }
        
        return 0;
    }
?>
