<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Require admin login
    if(!isset($_SESSION['admin_email'])){
        header('location: admin310817.php');
        exit();
    }
    
    $products_query = "select * from items";
    $products_result = mysqli_query($con, $products_query) or die(mysqli_error($con));
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Manage Products - Admin Dashboard</title>
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
                <h3>Admin Dashboard (<?php echo ucfirst(str_replace('_', ' ', $_SESSION['admin_role'])); ?>)</h3>
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="admin_manage_users.php">Manage Users</a>
                <a href="admin_manage_products.php">Manage Products</a>
                <?php if(intval($_SESSION['admin_role_id']) === 1): ?>
                    <a href="admin_manage_sales.php">Manage Sales</a>
                <?php endif; ?>
                <a href="admin_logout.php">Logout</a>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <h2 class="admin-page-title">Quản Lý Sản Phẩm</h2>
                    <a href="admin_add_product.php" class="admin-btn-primary">Thêm Sản Phẩm Mới</a>
                    
                    <div class="admin-table">
                        <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                while($row = mysqli_fetch_array($products_result)){
                                    ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo number_format($row['price'], 0, ',', '.'); ?> VNĐ</td>
                                        <td>
                                            <a href="admin_edit_product.php?id=<?php echo $row['id']; ?>" class="admin-btn-warning btn-sm">Sửa</a>
                                            <a href="admin_delete_product.php?id=<?php echo $row['id']; ?>" class="admin-btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">Xóa</a>
                                        </td>
                                    </tr>
                                    <?php
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
