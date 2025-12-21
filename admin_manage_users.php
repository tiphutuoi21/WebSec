<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Require admin login
    if(!isset($_SESSION['admin_email'])){
        header('location: admin310817.php');
        exit();
    }
    
    $users_query = "select * from users";
    $users_result = mysqli_query($con, $users_query) or die(mysqli_error($con));
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Manage Users - Admin Dashboard</title>
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
                    <h2 class="admin-page-title">Quản Lý Người Dùng</h2>
                    <?php if(intval($_SESSION['admin_role_id']) !== 1): ?>
                        <div class="alert alert-info admin-alert">
                            <strong>Lưu ý:</strong> Bạn chỉ có thể xem danh sách người dùng. Chỉ Admin mới có quyền xóa người dùng.
                        </div>
                    <?php endif; ?>
                    <div class="admin-table">
                        <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>City</th>
                                <th>Address</th>
                                <?php if(intval($_SESSION['admin_role_id']) === 1): ?>
                                    <th>Action</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                while($row = mysqli_fetch_array($users_result)){
                                    ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['contact']); ?></td>
                                        <td><?php echo htmlspecialchars($row['city']); ?></td>
                                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                                        <?php if(intval($_SESSION['admin_role_id']) === 1): ?>
                                            <td>
                                                <a href="admin_delete_user.php?id=<?php echo $row['id']; ?>" class="admin-btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa người dùng này?');">Xóa</a>
                                            </td>
                                        <?php endif; ?>
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
