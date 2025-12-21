<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Require admin login
    if(!isset($_SESSION['admin_email'])){
        header('location: admin310817.php');
        exit();
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Admin Dashboard - Figure Shop</title>
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
                    <div class="admin-panel">
                        <h3 class="admin-panel-heading">Chào Mừng Đến Admin Dashboard</h3>
                        <p style="font-size: 16px; margin-bottom: 15px;">Đăng nhập với: <strong><?php echo htmlspecialchars($_SESSION['admin_email']); ?></strong></p>
                        <p style="font-size: 16px; margin-bottom: 20px;">
                            Vai trò: 
                            <span class="admin-label <?php echo intval($_SESSION['admin_role_id']) === 1 ? 'admin-label-admin' : 'admin-label-sales'; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $_SESSION['admin_role'])); ?>
                            </span>
                        </p>
                        <hr style="border-color: #e0e0e0;">
                        <h4 style="color: #1a1a1a; font-weight: 600; margin-bottom: 15px;">Quyền Của Bạn:</h4>
                        <ul style="list-style: none; padding: 0;">
                            <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">✓ Xem Người Dùng</li>
                            <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">✓ Xem Đơn Hàng</li>
                            <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">✓ Thêm Sản Phẩm</li>
                            <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">✓ Sửa Sản Phẩm</li>
                            <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">✓ Xóa Sản Phẩm</li>
                            <?php if(intval($_SESSION['admin_role_id']) === 1): ?>
                                <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">✓ Xóa Người Dùng</li>
                                <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">✓ Xóa Đơn Hàng</li>
                                <li style="padding: 8px 0;">✓ Quản Lý Tài Khoản Sales</li>
                            <?php else: ?>
                                <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0; color: #999;">✗ Xóa Người Dùng (Chỉ Admin)</li>
                                <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0; color: #999;">✗ Xóa Đơn Hàng (Chỉ Admin)</li>
                                <li style="padding: 8px 0; color: #999;">✗ Quản Lý Tài Khoản Sales (Chỉ Admin)</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
