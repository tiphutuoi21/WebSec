<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    if(!isset($_SESSION['admin_email'])){
        header('location: admin310817.php');
        exit();
    }
    
    $id = intval($_POST['id']);
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $stock_quantity = isset($_POST['stock_quantity']) ? intval($_POST['stock_quantity']) : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    
    // Sanitize input
    $name = mysqli_real_escape_string($con, $name);
    $category = mysqli_real_escape_string($con, $category);
    $description = mysqli_real_escape_string($con, $description);
    
    // Get current image path
    $current_image_query = "SELECT image FROM items WHERE id = ?";
    $current_stmt = mysqli_prepare($con, $current_image_query);
    mysqli_stmt_bind_param($current_stmt, "i", $id);
    mysqli_stmt_execute($current_stmt);
    $current_result = mysqli_stmt_get_result($current_stmt);
    $current_row = mysqli_fetch_array($current_result);
    $current_image = $current_row['image'] ?? null;
    mysqli_stmt_close($current_stmt);
    
    $image_path = $current_image; // Keep current image by default
    
    // Handle image upload if new image is provided
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];
        
        if(in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            // Create uploads directory if it doesn't exist
            $upload_dir = 'img/products/';
            if(!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = 'product_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $file_name;
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if exists
                if($current_image && file_exists($current_image)) {
                    @unlink($current_image);
                }
                $image_path = $upload_path;
            }
        }
    }
    
    // Check and add columns if they don't exist
    $check_image = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'items' AND COLUMN_NAME = 'image'";
    $result_image = mysqli_query($con, $check_image);
    if(mysqli_num_rows($result_image) == 0) {
        mysqli_query($con, "ALTER TABLE items ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER category");
    }
    
    $check_is_new = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'items' AND COLUMN_NAME = 'is_new'";
    $result_is_new = mysqli_query($con, $check_is_new);
    if(mysqli_num_rows($result_is_new) == 0) {
        mysqli_query($con, "ALTER TABLE items ADD COLUMN is_new BOOLEAN DEFAULT 0 AFTER is_active");
    }
    
    // Update product with prepared statement
    $query = "UPDATE items SET name = ?, price = ?, category = ?, description = ?, stock_quantity = ?, image = ?, is_new = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);
    if(!$stmt) {
        echo "<div style='text-align: center; padding: 50px;'>";
        echo "<h2 style='color: red;'>✗ Lỗi khi chuẩn bị câu lệnh SQL: " . mysqli_error($con) . "</h2>";
        echo "<a href='admin_edit_product.php?id=" . $id . "'>Quay lại</a>";
        echo "</div>";
        exit();
    }
    mysqli_stmt_bind_param($stmt, "sdssissi", $name, $price, $category, $description, $stock_quantity, $image_path, $is_new, $id);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "<div style='text-align: center; padding: 50px;'>";
        echo "<h2 style='color: green;'>✓ Cập nhật sản phẩm thành công!</h2>";
        echo "<p>Đang chuyển hướng...</p>";
        echo "</div>";
        echo "<meta http-equiv='refresh' content='2;url=admin_manage_products.php' />";
    } else {
        echo "<div style='text-align: center; padding: 50px;'>";
        echo "<h2 style='color: red;'>✗ Lỗi khi cập nhật sản phẩm: " . mysqli_stmt_error($stmt) . "</h2>";
        echo "<a href='admin_edit_product.php?id=" . $id . "'>Quay lại</a>";
        echo "</div>";
    }
    
    mysqli_stmt_close($stmt);
?>
