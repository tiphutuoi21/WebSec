<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    if(isset($_SESSION['email'])){
        header('location: products.php');
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
                var strengthDiv = document.getElementById('password-strength');
                var requirements = [];
                
                if (password.length < 8) {
                    requirements.push('<span style="color: red;">✗ Tối thiểu 8 ký tự</span>');
                } else {
                    requirements.push('<span style="color: green;">✓ Tối thiểu 8 ký tự</span>');
                }
                
                if (!/[A-Z]/.test(password)) {
                    requirements.push('<span style="color: red;">✗ Có chữ hoa (A-Z)</span>');
                } else {
                    requirements.push('<span style="color: green;">✓ Có chữ hoa (A-Z)</span>');
                }
                
                if (!/[a-z]/.test(password)) {
                    requirements.push('<span style="color: red;">✗ Có chữ thường (a-z)</span>');
                } else {
                    requirements.push('<span style="color: green;">✓ Có chữ thường (a-z)</span>');
                }
                
                if (!/[0-9]/.test(password)) {
                    requirements.push('<span style="color: red;">✗ Có số (0-9)</span>');
                } else {
                    requirements.push('<span style="color: green;">✓ Có số (0-9)</span>');
                }
                
                if (!/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/.test(password)) {
                    requirements.push('<span style="color: red;">✗ Có ký tự đặc biệt</span>');
                } else {
                    requirements.push('<span style="color: green;">✓ Có ký tự đặc biệt</span>');
                }
                
                strengthDiv.innerHTML = requirements.join('<br>');
            }
            
            document.addEventListener('DOMContentLoaded', function() {
                var passwordInput = document.getElementById('password');
                if (passwordInput) {
                    passwordInput.addEventListener('input', validatePassword);
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
                                        <input type="password" class="form-control signup-input" name="password" id="password" placeholder="Mật khẩu (tối thiểu 8 ký tự: A-Z, a-z, 0-9, ký tự đặc biệt)" required="true" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':\"\\|,.<>\/?]).{8,}$" minlength="8">
                                        <small class="password-requirements" style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                                            <strong>Yêu cầu:</strong> Tối thiểu 8 ký tự, có chữ hoa, chữ thường, số và ký tự đặc biệt. Không chứa thông tin cá nhân.
                                        </small>
                                        <div id="password-strength" style="margin-top: 8px;"></div>
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
    </body>
</html>
