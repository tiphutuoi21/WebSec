<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Validate session and check admin access
    SecurityHelper::validateSessionTimeout($con);
    if(!isset($_SESSION['admin_email']) || intval($_SESSION['admin_role_id'] ?? 0) !== 1) {
        header('location: admin_login.php');
        exit();
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Add Product - Admin Dashboard</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css">
        <script type="text/javascript" src="bootstrap/js/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="css/style.css" type="text/css">
    </head>
    <body>
        <div class="container">
            <div style="background-color: #222; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <h3 style="color: white; display: inline;">Admin Dashboard</h3>
                <a href="admin_manage_products.php" style="color: white; margin-right: 20px;">Back to Products</a>
                <a href="admin_logout.php" style="color: white; float: right;">Logout</a>
            </div>
            
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3>Add New Product</h3>
                        </div>
                        <div class="panel-body">
                            <form method="post" action="admin_add_product_submit.php">
                                <?php echo SecurityHelper::getCSRFField(); ?>
                                <div class="form-group">
                                    <label>Product Name</label>
                                    <input type="text" class="form-control" name="name" placeholder="Enter product name" required>
                                </div>
                                <div class="form-group">
                                    <label>Price</label>
                                    <input type="number" class="form-control" name="price" placeholder="Enter product price" required>
                                </div>
                                <div class="form-group">
                                    <input type="submit" value="Add Product" class="btn btn-primary">
                                    <a href="admin_manage_products.php" class="btn btn-default">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
