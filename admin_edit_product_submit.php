<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Validate session and check admin access
    SecurityHelper::validateSessionTimeout($con);
    if(!isset($_SESSION['admin_email']) || intval($_SESSION['admin_role_id'] ?? 0) !== 1) {
        header('location: admin_login.php');
        exit();
    }

    // Rate limit admin product write actions
    $adminWriteKey = 'product_write_' . ($_SESSION['admin_email'] ?? SecurityHelper::getClientIdentifier());
    if (!SecurityHelper::checkRateLimit($adminWriteKey, 20, 300)) {
        echo "<div style='text-align: center; padding: 40px; color: red;'>Too many product updates. Please wait a few minutes.</div>";
        exit();
    }
    SecurityHelper::recordFailedAttempt($adminWriteKey);
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !SecurityHelper::verifyCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    $id = intval($_POST['id']);
    $name = SecurityHelper::getString('name', 'POST');
    $price_raw = $_POST['price'] ?? '';
    $category = SecurityHelper::getString('category', 'POST');
    $description_raw = SecurityHelper::getString('description', 'POST');
    $stock_quantity_raw = $_POST['stock_quantity'] ?? '';
    $is_new = isset($_POST['is_new']) ? 1 : 0;

    // Field validation using centralized helpers
    $nameCheck = SecurityHelper::validateProductName($name);
    if (!$nameCheck['valid']) {
        echo "<div style='text-align: center; padding: 40px; color: red;'>" . htmlspecialchars($nameCheck['message']) . "</div>";
        exit();
    }
    $priceCheck = SecurityHelper::validateProductPrice($price_raw);
    if (!$priceCheck['valid']) {
        echo "<div style='text-align: center; padding: 40px; color: red;'>" . htmlspecialchars($priceCheck['message']) . "</div>";
        exit();
    }
    $stockCheck = SecurityHelper::validateStockQuantity($stock_quantity_raw);
    if (!$stockCheck['valid']) {
        echo "<div style='text-align: center; padding: 40px; color: red;'>" . htmlspecialchars($stockCheck['message']) . "</div>";
        exit();
    }
    $descCheck = SecurityHelper::validateProductDescription($description_raw);
    if (!$descCheck['valid']) {
        echo "<div style='text-align: center; padding: 40px; color: red;'>" . htmlspecialchars($descCheck['message']) . "</div>";
        exit();
    }

    $price = $priceCheck['value'];
    $stock_quantity = $stockCheck['value'];
    $description = $descCheck['value'];
    
    // Get current image path
    $current_image_query = "SELECT image FROM items WHERE id = ?";
    $current_stmt = mysqli_prepare($con, $current_image_query);
    mysqli_stmt_bind_param($current_stmt, "i", $id);
    mysqli_stmt_execute($current_stmt);
    $current_result = mysqli_stmt_get_result($current_stmt);
    $current_row = mysqli_fetch_array($current_result);
    $current_image = $current_row['image'] ?? null;
    mysqli_stmt_close($current_stmt);
    
    $image_path = SecurityHelper::normalizeProductImagePath($current_image); // Keep current image by default
    
    // Handle image upload if new image is provided (optional)
    if(isset($_FILES['image'])) {
        $uploadResult = SecurityHelper::validateImageUpload(
            $_FILES['image'],
            'img/products/',
            ['png', 'jpg', 'jpeg', 'gif', 'webp'],
            5 * 1024 * 1024,
            false
        );

        if (!$uploadResult['valid']) {
            echo "<div style='text-align: center; padding: 40px; color: red;'>" . htmlspecialchars($uploadResult['message']) . "</div>";
            exit();
        }

        if (!empty($uploadResult['web_path'])) {
            $existingPath = SecurityHelper::normalizeProductImagePath($current_image);
            if($existingPath && file_exists($existingPath)) {
                @unlink($existingPath);
            }
            $image_path = $uploadResult['web_path'];
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
        error_log("Update product error: " . mysqli_error($con));
        echo "<div style='text-align: center; padding: 50px;'>";
        echo "<h2 style='color: red;'>✗ An error occurred while updating the product.</h2>";
        echo "<a href='admin_edit_product.php?id=" . intval($id) . "'>Go Back</a>";
        echo "</div>";
        exit();
    }
    mysqli_stmt_bind_param($stmt, "sdssissi", $name, $price, $category, $description, $stock_quantity, $image_path, $is_new, $id);
    
    if(mysqli_stmt_execute($stmt)) {
        // Log security event
        SecurityEnhancements::logSecurityEvent($con, 'admin_edit_product', 'Product ID: ' . $id);
        
        echo "<div style='text-align: center; padding: 50px;'>";
        echo "<h2 style='color: green;'>✓ Cập nhật sản phẩm thành công!</h2>";
        echo "<p>Đang chuyển hướng...</p>";
        echo "</div>";
        echo "<meta http-equiv='refresh' content='2;url=admin_manage_products.php' />";
    } else {
        error_log("Update product execute error: " . mysqli_stmt_error($stmt));
        echo "<div style='text-align: center; padding: 50px;'>";
        echo "<h2 style='color: red;'>✗ Lỗi khi cập nhật sản phẩm. Vui lòng thử lại.</h2>";
        echo "<a href='admin_edit_product.php?id=" . intval($id) . "'>Quay lại</a>";
        echo "</div>";
    }
    
    mysqli_stmt_close($stmt);
?>
