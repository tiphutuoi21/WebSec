<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Validate session and check sales access (role_id = 2 only)
    SecurityHelper::validateSessionTimeout($con);
    if(!isset($_SESSION['admin_email']) || !isset($_SESSION['admin_role_id']) || $_SESSION['admin_role_id'] !== 2) {
        header('location: admin_login.php');
        exit();
    }

    // Rate limit sales product writes
    $salesWriteKey = 'product_write_' . ($_SESSION['admin_email'] ?? SecurityHelper::getClientIdentifier());
    if (!SecurityHelper::checkRateLimit($salesWriteKey, 15, 300)) {
        ?>
        <script>
            window.alert("Too many product updates. Please wait a few minutes.");
        </script>
        <meta http-equiv="refresh" content="1;url=sales_manage_products.php" />
        <?php
        exit();
    }
    SecurityHelper::recordFailedAttempt($salesWriteKey);
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !SecurityHelper::verifyCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    $product_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if ($product_id <= 0) {
        ?>
        <script>
            window.alert("Invalid product ID.");
        </script>
        <meta http-equiv="refresh" content="1;url=sales_manage_products.php" />
        <?php
        exit();
    }
    
    // Get current product data
    $get_product_query = "SELECT * FROM items WHERE id = ?";
    $stmt = mysqli_prepare($con, $get_product_query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $product_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($product_result) == 0) {
        ?>
        <script>
            window.alert("Product not found.");
        </script>
        <meta http-equiv="refresh" content="1;url=sales_manage_products.php" />
        <?php
        exit();
    }
    
    $product = mysqli_fetch_array($product_result);
    mysqli_stmt_close($stmt);
    
    $name = SecurityHelper::getString('name', 'POST');
    $price_raw = isset($_POST['price']) ? trim($_POST['price']) : '';
    $stock_quantity_raw = isset($_POST['stock_quantity']) ? trim($_POST['stock_quantity']) : '';
    $description_raw = SecurityHelper::getString('description', 'POST');
    
    // Centralized validation
    $nameCheck = SecurityHelper::validateProductName($name);
    if (!$nameCheck['valid']) {
        ?>
        <script>
            window.alert("<?php echo htmlspecialchars($nameCheck['message']); ?>");
        </script>
        <meta http-equiv="refresh" content="1;url=sales_edit_product.php?id=<?php echo intval($product_id); ?>" />
        <?php
        exit();
    }

    $priceCheck = SecurityHelper::validateProductPrice($price_raw);
    if (!$priceCheck['valid']) {
        ?>
        <script>
            window.alert("<?php echo htmlspecialchars($priceCheck['message']); ?>");
        </script>
        <meta http-equiv="refresh" content="1;url=sales_edit_product.php?id=<?php echo intval($product_id); ?>" />
        <?php
        exit();
    }

    $stockCheck = SecurityHelper::validateStockQuantity($stock_quantity_raw);
    if (!$stockCheck['valid']) {
        ?>
        <script>
            window.alert("<?php echo htmlspecialchars($stockCheck['message']); ?>");
        </script>
        <meta http-equiv="refresh" content="1;url=sales_edit_product.php?id=<?php echo intval($product_id); ?>" />
        <?php
        exit();
    }

    $descCheck = SecurityHelper::validateProductDescription($description_raw);
    if (!$descCheck['valid']) {
        ?>
        <script>
            window.alert("<?php echo htmlspecialchars($descCheck['message']); ?>");
        </script>
        <meta http-equiv="refresh" content="1;url=sales_edit_product.php?id=<?php echo intval($product_id); ?>" />
        <?php
        exit();
    }

    $price = $priceCheck['value'];
    $stock_quantity = $stockCheck['value'];
    $description = $descCheck['value'];
    
    $image_path = SecurityHelper::normalizeProductImagePath($product['image']);
    
    // Handle image upload (optional)
    if (isset($_FILES['image'])) {
        $uploadResult = SecurityHelper::validateImageUpload(
            $_FILES['image'],
            'img/products/',
            ['png'],
            5 * 1024 * 1024,
            false
        );

        if (!$uploadResult['valid']) {
            ?>
            <script>
                window.alert("<?php echo htmlspecialchars($uploadResult['message']); ?>");
            </script>
            <meta http-equiv="refresh" content="1;url=sales_edit_product.php?id=<?php echo intval($product_id); ?>" />
            <?php
            exit();
        }

        if (!empty($uploadResult['web_path'])) {
            $existingPath = SecurityHelper::normalizeProductImagePath($product['image']);
            if (!empty($existingPath) && file_exists($existingPath)) {
                @unlink($existingPath);
            }
            $image_path = $uploadResult['web_path'];
        }
    }
    
    // Update product in database using prepared statement
    $update_query = "UPDATE items SET name = ?, price = ?, description = ?, image = ?, stock_quantity = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $update_query);
    
    if (!$stmt) {
        die('Database error: ' . htmlspecialchars(mysqli_error($con)));
    }
    
    mysqli_stmt_bind_param($stmt, "sdssii", $name, $price, $description, $image_path, $stock_quantity, $product_id);
    
    if (mysqli_stmt_execute($stmt)) {
        SecurityEnhancements::logSecurityEvent($con, 'product_updated', 'Product: ' . $name);
        ?>
        <script>
            window.alert("Product updated successfully!");
        </script>
        <meta http-equiv="refresh" content="1;url=sales_manage_products.php" />
        <?php
    } else {
        die('Error updating product: ' . htmlspecialchars(mysqli_error($con)));
    }
    
    mysqli_stmt_close($stmt);
?>
