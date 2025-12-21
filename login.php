<?php
    require 'connection.php';
    require 'SecurityHelper.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Đăng Nhập - Figure Shop</title>
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
    </head>
    <body>
        <div>
            <?php
                require 'header.php';
            ?>
            <br><br><br>
           <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-sm-8 col-md-6 col-lg-4 col-xs-offset-0 col-sm-offset-2 col-md-offset-3 col-lg-offset-4">
                        <div class="login-container">
                            <div class="login-header">
                                <h2><strong>ĐĂNG NHẬP</strong></h2>
                            </div>
                            <div class="login-body">
                                <p class="login-text">Đăng nhập để mua sắm</p>
                                <form method="post" action="login_submit.php">
                                    <?php echo SecurityHelper::getCSRFField(); ?>
                                    <div class="form-group">
                                        <input type="email" class="form-control login-input" name="email" placeholder="Email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$" required>
                                    </div>
                                    <div class="form-group">
                                        <input type="password" class="form-control login-input" name="password" placeholder="Mật khẩu" required>
                                    </div>
                                    <div class="form-group">
                                        <input type="submit" value="Đăng Nhập" class="btn btn-login btn-block">
                                    </div>
                                </form>
                            </div>
                            <div class="login-footer">
                                <p>Chưa có tài khoản? <a href="signup.php" class="register-link">Đăng ký ngay</a></p>
                            </div>
                        </div>
                    </div>
                </div>
           </div>
           <br><br><br><br><br>
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
