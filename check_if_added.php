<?php
/**
 * Check if Product is Already in Cart
 * Used in product pages to disable "Add to Cart" button if item is already in cart
 */

function check_if_added_to_cart($item_id) {
    // If user is not logged in, item is not in cart
    if (!isset($_SESSION['id'])) {
        return false;
    }
    
    $user_id = $_SESSION['id'];
    global $con;
    
    // Check if item exists in cart_items for this user
    $check_query = "SELECT id FROM cart_items WHERE user_id = ? AND item_id = ?";
    $stmt = mysqli_prepare($con, $check_query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $item_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $exists = mysqli_num_rows($result) > 0;
        mysqli_stmt_close($stmt);
        return $exists;
    }
    
    return false;
}
?>
