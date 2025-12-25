<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Validate session and check admin access
    SecurityHelper::validateSessionTimeout($con);
    if(!isset($_SESSION['admin_email']) || intval($_SESSION['admin_role_id'] ?? 0) !== 1) {
        header('location: admin_login.php');
        exit();
    }
    
    $sales_query = "SELECT a.id, a.email, a.role_id, a.is_active, a.last_login, a.created_at, r.name as role_name 
                    FROM admins a 
                    LEFT JOIN roles r ON a.role_id = r.id 
                    WHERE a.role_id = 2 
                    ORDER BY a.created_at DESC";
    $sales_result = mysqli_query($con, $sales_query) or die(mysqli_error($con));
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Quản Lý Sales - Admin Dashboard</title>
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
                <a href="admin_orders.php">View Orders</a>
                <a href="admin_manage_users.php">Manage Users</a>
                <a href="admin_manage_products.php">Manage Products</a>
                <a href="admin_manage_sales.php">Manage Sales</a>
                <a href="admin_logout.php">Logout</a>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <h2 class="admin-page-title">Quản Lý Tài Khoản Sales</h2>
                    <?php if(isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                        <div class="alert alert-success admin-alert">
                            Xóa tài khoản Sales thành công!
                        </div>
                    <?php endif; ?>
                    <a href="admin_add_sales.php" class="admin-btn-primary">Thêm Sales Mới</a>
                    
                    <div class="admin-table">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Email</th>
                                    <th>Trạng Thái</th>
                                    <th>Đăng Nhập Cuối</th>
                                    <th>Ngày Tạo</th>
                                    <th>Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    if(mysqli_num_rows($sales_result) > 0){
                                        while($row = mysqli_fetch_array($sales_result)){
                                            ?>
                                            <tr>
                                                <td><?php echo $row['id']; ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td>
                                                    <?php if($row['is_active']): ?>
                                                        <span class="admin-label admin-label-customer">Hoạt Động</span>
                                                    <?php else: ?>
                                                        <span class="admin-label admin-label-danger">Vô Hiệu</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $row['last_login'] ? date('d/m/Y H:i', strtotime($row['last_login'])) : 'Chưa đăng nhập'; ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                                <td>
                                                    <a href="admin_edit_sales.php?id=<?php echo $row['id']; ?>" class="admin-btn-warning btn-sm">Sửa</a>
                                                    <a href="admin_delete_sales.php?id=<?php echo $row['id']; ?>" class="admin-btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa tài khoản này?');">Xóa</a>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="6" style="text-align: center; padding: 30px; color: #666;">
                                                Chưa có tài khoản Sales nào
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

