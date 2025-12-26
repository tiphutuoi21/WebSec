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
            window.alert("Too many product submissions. Please wait a few minutes.");
        </script>
        <meta http-equiv="refresh" content="1;url=sales_add_product.php" />
        <?php
        exit();
    }
    SecurityHelper::recordFailedAttempt($salesWriteKey);
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !SecurityHelper::verifyCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
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
        <meta http-equiv="refresh" content="1;url=sales_add_product.php" />
        <?php
        exit();
    }

    $priceCheck = SecurityHelper::validateProductPrice($price_raw);
    if (!$priceCheck['valid']) {
        ?>
        <script>
            window.alert("<?php echo htmlspecialchars($priceCheck['message']); ?>");
        </script>
        <meta http-equiv="refresh" content="1;url=sales_add_product.php" />
        <?php
        exit();
    }

    $stockCheck = SecurityHelper::validateStockQuantity($stock_quantity_raw);
    if (!$stockCheck['valid']) {
        ?>
        <script>
            window.alert("<?php echo htmlspecialchars($stockCheck['message']); ?>");
        </script>
        <meta http-equiv="refresh" content="1;url=sales_add_product.php" />
        <?php
        exit();
    }

    $descCheck = SecurityHelper::validateProductDescription($description_raw);
    if (!$descCheck['valid']) {
        ?>
        <script>
            window.alert("<?php echo htmlspecialchars($descCheck['message']); ?>");
        </script>
        <meta http-equiv="refresh" content="1;url=sales_add_product.php" />
        <?php
        exit();
    }

    $price = $priceCheck['value'];
    $stock_quantity = $stockCheck['value'];
    $description = $descCheck['value'];
    
    // Handle image upload (required)
    $uploadResult = SecurityHelper::validateImageUpload(
        $_FILES['image'] ?? null,
        'img/products/',
        ['png'],
        5 * 1024 * 1024,
        true
    );

    if (!$uploadResult['valid']) {
        ?>
        <script>
            window.alert("<?php echo htmlspecialchars($uploadResult['message']); ?>");
        </script>
        <meta http-equiv="refresh" content="1;url=sales_add_product.php" />
        <?php
        exit();
    }

    $image_path = $uploadResult['web_path'];
    
    // Insert product into database using prepared statement
    $insert_query = "INSERT INTO items (name, price, description, image, stock_quantity) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $insert_query);
    
    if (!$stmt) {
        die('Database error: ' . htmlspecialchars(mysqli_error($con)));
    }
    
    mysqli_stmt_bind_param($stmt, "sdssi", $name, $price, $description, $image_path, $stock_quantity);
    
    if (mysqli_stmt_execute($stmt)) {
        SecurityEnhancements::logSecurityEvent($con, 'product_added', 'Product: ' . $name);
        ?>
        <script>
            window.alert("Product added successfully!");
        </script>
        <meta http-equiv="refresh" content="1;url=sales_manage_products.php" />
        <?php
    } else {
        die('Error adding product: ' . htmlspecialchars(mysqli_error($con)));
    }
    
    mysqli_stmt_close($stmt);
?>
