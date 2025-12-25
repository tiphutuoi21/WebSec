<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Validate session and check sales access (role_id = 2 only)
    SecurityHelper::validateSessionTimeout($con);
    if(!isset($_SESSION['admin_email']) || !isset($_SESSION['admin_role_id']) || $_SESSION['admin_role_id'] !== 2) {
        header('location: admin_login.php');
        exit();
    }
    
    $products_query = "SELECT * FROM items";
    $products_result = mysqli_query($con, $products_query) or die(mysqli_error($con));
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Manage Products - Sales Dashboard</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css">
        <script type="text/javascript" src="bootstrap/js/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="css/style.css" type="text/css">
    </head>
    <body>
        <div class="container">
            <div class="admin-nav">
                <h3>Sales Dashboard</h3>
                <a href="sales_dashboard.php">Dashboard</a>
                <a href="sales_manage_products.php">Manage Products</a>
                <a href="admin_orders.php">View Orders</a>
                <a href="admin_logout.php">Logout</a>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <h2 class="admin-page-title">Manage Products</h2>
                    <a href="sales_add_product.php" class="admin-btn-primary">Add New Product</a>
                    
                    <div class="admin-table">
                        <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                while($row = mysqli_fetch_array($products_result)){
                                    echo "<tr>";
                                    echo "<td>" . intval($row['id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "</td>";
                                    echo "<td>" . intval($row['stock_quantity']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['price'], ENT_QUOTES, 'UTF-8') . "</td>";
                                    echo "<td>";
                                    echo "<a href='sales_edit_product.php?id=" . intval($row['id']) . "' class='admin-btn-edit'>Edit</a> ";
                                    echo "<a href='sales_delete_product.php?id=" . intval($row['id']) . "' class='admin-btn-delete' onclick='return confirm(\"Are you sure?\");'>Delete</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            ?>
                        </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
