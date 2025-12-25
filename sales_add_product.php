<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Validate session and check sales access (role_id = 2 only)
    SecurityHelper::validateSessionTimeout($con);
    if(!isset($_SESSION['admin_email']) || !isset($_SESSION['admin_role_id']) || $_SESSION['admin_role_id'] !== 2) {
        header('location: admin_login.php');
        exit();
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Add Product - Sales Dashboard</title>
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
            
            // Allow only digits (0-9) for numeric fields
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
                    <h2 class="admin-page-title">Add New Product</h2>
                    
                    <div class="admin-form">
                        <form action="sales_add_product_submit.php" method="POST" enctype="multipart/form-data" onsubmit="return validateProductForm();">
                            <?php echo SecurityHelper::getCSRFField(); ?>
                            
                            <div class="form-group">
                                <label for="name">Product Name:</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter product name" maxlength="255" oninput="validateProductName(event)" required>
                                <small style="color: #666;">Only letters, numbers, spaces, and apostrophes allowed</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="price">Price:</label>
                                <input type="text" class="form-control" id="price" name="price" placeholder="0.00" oninput="validateNumericInput(event)" required>
                                <small style="color: #666;">Numbers only (e.g., 29.99)</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="stock_quantity">Stock Quantity:</label>
                                <input type="text" class="form-control" id="stock_quantity" name="stock_quantity" placeholder="0" oninput="validateQuantity(event)" required>
                                <small style="color: #666;">Whole numbers only, no decimals</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description:</label>
                                <textarea class="form-control" id="description" name="description" placeholder="Enter product description" rows="5" maxlength="1000" oninput="validateProductName(event)"></textarea>
                                <small style="color: #666;">Only letters, numbers, spaces, and apostrophes allowed</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="image">Product Image:</label>
                                <input type="file" class="form-control" id="image" name="image" accept=".png" required>
                                <small style="color: #666;">Only PNG format is accepted. Maximum file size: 5MB</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Add Product</button>
                            <a href="sales_manage_products.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
