<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Require admin login and admin role
    if(!isset($_SESSION['admin_email'])){
        header('location: admin310817.php');
        exit();
    }
    
    if(intval($_SESSION['admin_role_id']) !== 1){
        echo "<script>alert('Chỉ Admin mới có quyền thêm Sales'); window.location.href='admin_dashboard.php';</script>";
        exit();
    }
    
    $error = '';
    $success = '';
    
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $email = SecurityHelper::getString($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validation
        if(empty($email) || empty($password) || empty($confirm_password)){
            $error = 'Vui lòng điền đầy đủ thông tin';
        } elseif(!SecurityHelper::isValidEmail($email)){
            $error = 'Email không hợp lệ';
        } elseif($password !== $confirm_password){
            $error = 'Mật khẩu xác nhận không khớp';
        } elseif(strlen($password) < 8){
            $error = 'Mật khẩu phải có ít nhất 8 ký tự';
        } else {
            // Check if email already exists
            $check_query = "SELECT id FROM admins WHERE email = ?";
            $check_stmt = mysqli_prepare($con, $check_query);
            mysqli_stmt_bind_param($check_stmt, "s", $email);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if(mysqli_num_rows($check_result) > 0){
                $error = 'Email này đã được sử dụng';
            } else {
                // Hash password
                $hashed_password = md5(md5($password));
                
                // Insert new sales account (role_id = 2 for sales_manager)
                $insert_query = "INSERT INTO admins (email, password, role_id, is_active) VALUES (?, ?, 2, 1)";
                $insert_stmt = mysqli_prepare($con, $insert_query);
                mysqli_stmt_bind_param($insert_stmt, "ss", $email, $hashed_password);
                
                if(mysqli_stmt_execute($insert_stmt)){
                    $success = 'Thêm tài khoản Sales thành công!';
                    // Clear form
                    $email = '';
                } else {
                    $error = 'Lỗi khi thêm tài khoản: ' . mysqli_error($con);
                }
                mysqli_stmt_close($insert_stmt);
            }
            mysqli_stmt_close($check_stmt);
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Thêm Sales - Admin Dashboard</title>
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
                <a href="admin_manage_sales.php">Manage Sales</a>
                <a href="admin_logout.php">Logout</a>
            </div>
            
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="admin-panel">
                        <h3 class="admin-panel-heading">Thêm Tài Khoản Sales Mới</h3>
                        
                        <?php if($error): ?>
                            <div class="alert alert-danger admin-alert">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($success): ?>
                            <div class="alert alert-success admin-alert">
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" 
                                       required placeholder="sales@figureshop.com">
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Mật Khẩu:</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required minlength="8" placeholder="Tối thiểu 8 ký tự">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Xác Nhận Mật Khẩu:</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       required minlength="8" placeholder="Nhập lại mật khẩu">
                            </div>
                            
                            <div class="form-group">
                                <a href="admin_manage_sales.php" class="admin-btn-warning" style="text-decoration: none; display: inline-block; margin-right: 10px;">Hủy</a>
                                <button type="submit" class="admin-btn-primary">Thêm Sales</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>

