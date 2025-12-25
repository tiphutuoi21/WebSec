<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Require admin login and admin role
    if(!isset($_SESSION['admin_email'])){
        header('location: admin_login.php');
        exit();
    }
    
    if(intval($_SESSION['admin_role_id']) !== 1){
        echo "<script>alert('Chỉ Admin mới có quyền sửa Sales'); window.location.href='admin_dashboard.php';</script>";
        exit();
    }
    
    $sales_id = intval($_GET['id']);
    
    // Get sales info
    $get_query = "SELECT id, email, is_active FROM admins WHERE id = ? AND role_id = 2";
    $get_stmt = mysqli_prepare($con, $get_query);
    mysqli_stmt_bind_param($get_stmt, "i", $sales_id);
    mysqli_stmt_execute($get_stmt);
    $get_result = mysqli_stmt_get_result($get_stmt);
    
    if(mysqli_num_rows($get_result) == 0){
        echo "<script>alert('Không tìm thấy tài khoản Sales'); window.location.href='admin_manage_sales.php';</script>";
        exit();
    }
    
    $sales_data = mysqli_fetch_array($get_result);
    mysqli_stmt_close($get_stmt);
    
    $error = '';
    $success = '';
    
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $email = SecurityHelper::getString('email', 'POST');
        $password = SecurityHelper::getString('password', 'POST');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Validation
        if(empty($email)){
            $error = 'Email không được để trống';
        } elseif(!SecurityHelper::isValidEmail($email)){
            $error = 'Email không hợp lệ';
        } else {
            // Check if email already exists (excluding current sales)
            $check_query = "SELECT id FROM admins WHERE email = ? AND id != ?";
            $check_stmt = mysqli_prepare($con, $check_query);
            mysqli_stmt_bind_param($check_stmt, "si", $email, $sales_id);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if(mysqli_num_rows($check_result) > 0){
                $error = 'Email này đã được sử dụng';
            } else {
                // Update sales account
                if(!empty($password)){
                    if(strlen($password) < 8){
                        $error = 'Mật khẩu phải có ít nhất 8 ký tự';
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                        $update_query = "UPDATE admins SET email = ?, password = ?, is_active = ? WHERE id = ?";
                        $update_stmt = mysqli_prepare($con, $update_query);
                        mysqli_stmt_bind_param($update_stmt, "ssii", $email, $hashed_password, $is_active, $sales_id);
                    }
                } else {
                    $update_query = "UPDATE admins SET email = ?, is_active = ? WHERE id = ?";
                    $update_stmt = mysqli_prepare($con, $update_query);
                    mysqli_stmt_bind_param($update_stmt, "sii", $email, $is_active, $sales_id);
                }
                
                if(isset($update_stmt) && mysqli_stmt_execute($update_stmt)){
                    $success = 'Cập nhật tài khoản Sales thành công!';
                    $sales_data['email'] = $email;
                    $sales_data['is_active'] = $is_active;
                } else {
                    $error = 'Lỗi khi cập nhật: ' . mysqli_error($con);
                }
                
                if(isset($update_stmt)){
                    mysqli_stmt_close($update_stmt);
                }
            }
            mysqli_stmt_close($check_stmt);
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Sửa Sales - Admin Dashboard</title>
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
                        <h3 class="admin-panel-heading">Sửa Tài Khoản Sales</h3>
                        
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
                                       value="<?php echo htmlspecialchars($sales_data['email']); ?>" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Mật Khẩu Mới (để trống nếu không đổi):</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       minlength="8" placeholder="Tối thiểu 8 ký tự">
                            </div>
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="is_active" value="1" 
                                           <?php echo $sales_data['is_active'] ? 'checked' : ''; ?>>
                                    Tài khoản hoạt động
                                </label>
                            </div>
                            
                            <div class="form-group">
                                <a href="admin_manage_sales.php" class="admin-btn-warning" style="text-decoration: none; display: inline-block; margin-right: 10px;">Hủy</a>
                                <button type="submit" class="admin-btn-primary">Cập Nhật</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>

