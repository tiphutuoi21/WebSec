<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Validate session and check sales access (role_id = 2 only)
    SecurityHelper::validateSessionTimeout($con);
    if(!isset($_SESSION['admin_email']) || !isset($_SESSION['admin_role_id']) || $_SESSION['admin_role_id'] !== 2) {
        header('location: admin_login.php');
        exit();
    }
    
    $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($product_id <= 0) {
        header('location: sales_manage_products.php');
        exit();
    }
    
    // Get product info before deletion
    $get_product_query = "SELECT name, image FROM items WHERE id = ?";
    $stmt = mysqli_prepare($con, $get_product_query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $product_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($product_result) == 0) {
        header('location: sales_manage_products.php');
        exit();
    }
    
    $product = mysqli_fetch_array($product_result);
    mysqli_stmt_close($stmt);
    
    // Delete product image if exists
    $deletePath = SecurityHelper::normalizeProductImagePath($product['image']);
    if (!empty($deletePath) && file_exists($deletePath)) {
        @unlink($deletePath);
    }
    
    // Delete product from database using prepared statement
    $delete_query = "DELETE FROM items WHERE id = ?";
    $stmt = mysqli_prepare($con, $delete_query);
    
    if (!$stmt) {
        die('Database error: ' . htmlspecialchars(mysqli_error($con)));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    
    if (mysqli_stmt_execute($stmt)) {
        SecurityEnhancements::logSecurityEvent($con, 'product_deleted', 'Product: ' . $product['name']);
        ?>
        <script>
            window.alert("Product deleted successfully!");
        </script>
        <meta http-equiv="refresh" content="1;url=sales_manage_products.php" />
        <?php
    } else {
        die('Error deleting product: ' . htmlspecialchars(mysqli_error($con)));
    }
    
    mysqli_stmt_close($stmt);
?>
