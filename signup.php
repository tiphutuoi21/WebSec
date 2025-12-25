<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    if(isset($_SESSION['email'])){
        header('Location: products.php');
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Đăng Ký - Figure Shop</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- latest compiled and minified CSS -->
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css">
        <!-- jquery library -->
        <script type="text/javascript" src="bootstrap/js/jquery-3.2.1.min.js"></script>
        <!-- Latest compiled and minified javascript -->
        <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
        <!-- External CSS -->
        <link rel="stylesheet" href="css/style.css" type="text/css">
        <script type="text/javascript">
            function validatePassword() {
                var password = document.getElementById('password').value;
                var errorDiv = document.getElementById('password-error');
                var errors = [];
                
                if (password.length === 0) {
                    errorDiv.style.display = 'none';
                    return;
                }
                
                if (password.length < 8) {
                    errors.push('Mật khẩu phải có tối thiểu 8 ký tự');
                }
                
                if (!/[A-Z]/.test(password)) {
                    errors.push('Thiếu chữ cái viết hoa (A-Z)');
                }
                
                if (!/[a-z]/.test(password)) {
                    errors.push('Thiếu chữ cái viết thường (a-z)');
                }
                
                if (!/[0-9]/.test(password)) {
                    errors.push('Thiếu số (0-9)');
                }
                
                if (!/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/.test(password)) {
                    errors.push('Thiếu ký tự đặc biệt (!, @, #, $, %, v.v.)');
                }
                
                if (errors.length > 0) {
                    errorDiv.style.display = 'block';
                    errorDiv.innerHTML = '<strong>Lỗi:</strong> ' + errors.join(', ');
                    errorDiv.style.color = '#DC143C';
                } else {
                    errorDiv.style.display = 'none';
                }
            }
            
            document.addEventListener('DOMContentLoaded', function() {
                var passwordInput = document.getElementById('password');
                if (passwordInput) {
                    passwordInput.addEventListener('input', validatePassword);
                    passwordInput.addEventListener('blur', validatePassword);
                }
            });
        </script>
    </head>
    <body>
        <div>
            <?php
                require 'header.php';
            ?>
            <br><br>
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-sm-10 col-md-8 col-lg-6 col-xs-offset-0 col-sm-offset-1 col-md-offset-2 col-lg-offset-3">
                        <div class="signup-container">
                            <div class="signup-header">
                                <h2><strong>ĐĂNG KÝ</strong></h2>
                            </div>
                            <div class="signup-body">
                                <form method="post" action="user_registration_script.php">
                                    <?php echo SecurityHelper::getCSRFField(); ?>
                                    <div class="form-group">
                                        <input type="text" class="form-control signup-input" name="name" placeholder="Họ và tên" required="true">
                                    </div>
                                    <div class="form-group">
                                        <input type="email" class="form-control signup-input" name="email" placeholder="Email" required="true" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$">
                                    </div> 
                                    <div class="form-group">
                                        <input type="password" class="form-control signup-input" name="password" id="password" placeholder="Mật khẩu" required="true">
                                        <div id="password-error" style="margin-top: 8px; color: #DC143C; font-size: 13px; display: none;"></div>
                                    </div>
                                    <div class="form-group"> 
                                        <input type="tel" class="form-control signup-input" name="contact" placeholder="Số điện thoại" required="true">
                                    </div>
                                    <div class="form-group">
                                        <input type="text" class="form-control signup-input" name="city" placeholder="Thành phố" required="true">
                                    </div>
                                    <div class="form-group">
                                        <input type="text" class="form-control signup-input" name="address" placeholder="Địa chỉ" required="true">
                                    </div>
                                    <div class="form-group">
                                        <input type="submit" class="btn btn-signup btn-block" value="Đăng Ký">
                                    </div>
                                </form>
                            </div>
                            <div class="signup-footer">
                                <p>Đã có tài khoản? <a href="login.php" class="login-link">Đăng nhập</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br><br><br><br><br><br>
           <footer class="footer">
               <div class="container">
               <center>
                   <p>Copyright &copy Figure Shop. All Rights Reserved. | Liên Hệ: +84 0854008327</p>
                   <p>Shop mô hình chính hãng - Nơi hội tụ đam mê sưu tầm</p>
               </center>
               </div>
           </footer>

        </div>
        
        <?php require 'hotline_widget.php'; ?>
    </body>
</html>
