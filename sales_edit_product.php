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
    
    $product_query = "SELECT * FROM items WHERE id = ?";
    $stmt = mysqli_prepare($con, $product_query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $product_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($product_result) == 0) {
        header('location: sales_manage_products.php');
        exit();
    }
    
    $product = mysqli_fetch_array($product_result);
    mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Edit Product - Sales Dashboard</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css">
        <script type="text/javascript" src="bootstrap/js/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="css/style.css" type="text/css">
        <script>
            // Allow only alphanumeric, spaces, and apostrophes
            function validateProductName(e) {
                const allowed = /^[a-zA-Z0-9\s']*$/;
                if (!allowed.test(e.target.value)) {
                    e.target.value = e.target.value.replace(/[^a-zA-Z0-9\s']/g, '');
                }
            }
            
            // Allow only digits and one period for price
            function validateNumericInput(e) {
                e.target.value = e.target.value.replace(/[^0-9.]/g, '');
                // Remove multiple periods
                const parts = e.target.value.split('.');
                if (parts.length > 2) {
                    e.target.value = parts[0] + '.' + parts.slice(1).join('');
                }
            }
            
            // Allow only digits (no periods or commas) for quantity
            function validateQuantity(e) {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
            }
            
            // Validate on form submission
            function validateProductForm() {
                const name = document.getElementById('name').value.trim();
                const price = document.getElementById('price').value.trim();
                const quantity = document.getElementById('stock_quantity').value.trim();
                const description = document.getElementById('description').value.trim();
                
                // Validate name
                if (!name) {
                    alert('Product name is required.');
                    return false;
                }
                if (!/^[a-zA-Z0-9\s']+$/.test(name)) {
                    alert('Product name can only contain letters, numbers, spaces, and apostrophes.');
                    return false;
                }
                
                // Validate price
                if (!price) {
                    alert('Price is required.');
                    return false;
                }
                if (!/^[0-9]+(\.[0-9]{1,2})?$/.test(price)) {
                    alert('Price must be a valid number.');
                    return false;
                }
                if (parseFloat(price) <= 0) {
                    alert('Price must be greater than 0.');
                    return false;
                }
                
                // Validate quantity
                if (!quantity) {
                    alert('Stock quantity is required.');
                    return false;
                }
                if (!/^[0-9]+$/.test(quantity)) {
                    alert('Stock quantity must be a whole number with no decimals.');
                    return false;
                }
                
                // Validate description if provided
                if (description && !/^[a-zA-Z0-9\s',.-]*$/.test(description)) {
                    alert('Description contains invalid characters. Only letters, numbers, spaces, apostrophes, commas, periods, and hyphens are allowed.');
                    return false;
                }
                
                return true;
            }
        </script>
    </head>
    <body>
        <div class="container">
            <div class="admin-nav">
                <h3>Sales Dashboard</h3>
                <a href="sales_dashboard.php">Dashboard</a>
                <a href="sales_manage_products.php">Manage Products</a>
                <a href="admin_logout.php">Logout</a>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <h2 class="admin-page-title">Edit Product</h2>
                    
                    <div class="admin-form">
                        <form action="sales_edit_product_submit.php" method="POST" enctype="multipart/form-data" onsubmit="return validateProductForm();">
                            <?php echo SecurityHelper::getCSRFField(); ?>
                            
                            <input type="hidden" name="id" value="<?php echo intval($product['id']); ?>">
                            
                            <div class="form-group">
                                <label for="name">Product Name:</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="255" oninput="validateProductName(event)" required>
                                <small style="color: #666;">Only letters, numbers, spaces, and apostrophes allowed</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="price">Price:</label>
                                <input type="text" class="form-control" id="price" name="price" value="<?php echo floatval($product['price']); ?>" oninput="validateNumericInput(event)" required>
                                <small style="color: #666;">Numbers only (e.g., 29.99)</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="stock_quantity">Stock Quantity:</label>
                                <input type="text" class="form-control" id="stock_quantity" name="stock_quantity" value="<?php echo intval($product['stock_quantity']); ?>" oninput="validateQuantity(event)" required>
                                <small style="color: #666;">Whole numbers only, no decimals</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description:</label>
                                <textarea class="form-control" id="description" name="description" rows="5" maxlength="1000" oninput="validateProductName(event)"><?php echo htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                <small style="color: #666;">Only letters, numbers, spaces, and apostrophes allowed</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="image">Product Image:</label>
                                <?php if (!empty($product['image'])): ?>
                                    <div style="margin-bottom: 10px;">
                                        <img src="img/products/<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" style="max-width: 150px; max-height: 150px;">
                                        <p><small>Current image</small></p>
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="image" name="image" accept=".png">
                                <small style="color: #666;">PNG format only. Leave blank to keep current image. Maximum file size: 5MB</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update Product</button>
                            <a href="sales_manage_products.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
