<?php
    require 'connection.php';
    require 'config.php';
    require 'MailHelper.php';
    require 'SecurityHelper.php';
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !SecurityHelper::verifyCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    // Get and sanitize input
    $name = SecurityHelper::getString('name', 'POST');
    $email = SecurityHelper::getString('email', 'POST');
    $password = SecurityHelper::getString('password', 'POST');
    $contact = SecurityHelper::getString('contact', 'POST');
    $city = SecurityHelper::getString('city', 'POST');
    $address = SecurityHelper::getString('address', 'POST');
    
    // Validate email format
    if (!SecurityHelper::isValidEmail($email)) {
        echo "Incorrect email format. Redirecting you back to registration page...";
        ?>
        <meta http-equiv="refresh" content="2;url=signup.php" />
        <?php
        exit();
    }
    
    // Validate password strength with user data
    $userData = [
        'name' => $name,
        'email' => $email,
        'contact' => $contact,
        'city' => $city,
        'address' => $address
    ];
    $password_check = SecurityHelper::isStrongPassword($password, $userData);
    if (!$password_check['valid']) {
        echo htmlspecialchars($password_check['message']) . " Redirecting you back to registration page...";
        ?>
        <meta http-equiv="refresh" content="3;url=signup.php" />
        <?php
        exit();
    }
    
    // Hash password
    $password_hash = md5(md5($password));
    
    // Check for duplicate email using prepared statement
    $duplicate_query = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($con, $duplicate_query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $duplicate_result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($duplicate_result) > 0) {
        ?>
        <script>
            window.alert("Email already exists in our database!");
        </script>
        <meta http-equiv="refresh" content="1;url=signup.php" />
        <?php
        exit();
    }
    
    // Generate verification token
    $verification_token = bin2hex(random_bytes(32));
    $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Use prepared statement for user registration
    $user_registration_query = "INSERT INTO users (name, email, password, contact, city, address, verification_token, token_expiry, email_verified) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";
    $register_stmt = mysqli_prepare($con, $user_registration_query);
    
    if (!$register_stmt) {
        die('Database error: ' . htmlspecialchars(mysqli_error($con)));
    }
    
    mysqli_stmt_bind_param($register_stmt, "ssssssss", $name, $email, $password_hash, $contact, $city, $address, $verification_token, $token_expiry);
    
    if(mysqli_stmt_execute($register_stmt)) {
        $user_id = mysqli_insert_id($con);
        
        // Try to send verification email
        $email_sent = MailHelper::sendVerificationEmail($email, $name, $verification_token);
        
        // If email sending failed (e.g., PHPMailer not available), auto-verify the account
        if (!$email_sent) {
            // Auto-verify email if email service is not available
            $auto_verify_query = "UPDATE users SET email_verified = 1 WHERE id = ?";
            $auto_verify_stmt = mysqli_prepare($con, $auto_verify_query);
            if ($auto_verify_stmt) {
                mysqli_stmt_bind_param($auto_verify_stmt, "i", $user_id);
                if (!mysqli_stmt_execute($auto_verify_stmt)) {
                    error_log("Failed to auto-verify email for user ID: $user_id - " . mysqli_error($con));
                }
                mysqli_stmt_close($auto_verify_stmt);
            }
        }
        
        $_SESSION['email'] = $email;
        $_SESSION['id'] = $user_id;
        $_SESSION['pending_verification'] = !$email_sent ? false : true;
        
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css">
            <link rel="stylesheet" href="css/style.css" type="text/css">
        </head>
        <body>
            <div class="container" style="margin-top: 100px;">
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <div class="panel panel-success">
                            <div class="panel-heading">
                                <h3>Đăng Ký Thành Công!</h3>
                            </div>
                            <div class="panel-body">
                                <p>Cảm ơn bạn đã đăng ký với Figure Shop!</p>
                                <?php if ($email_sent): ?>
                                    <p>Email xác thực đã được gửi đến <strong><?php echo htmlspecialchars($email); ?></strong></p>
                                    <p>Vui lòng kiểm tra email và click vào link xác thực để kích hoạt tài khoản.</p>
                                    <p><small>Nếu không thấy email, vui lòng kiểm tra thư mục spam.</small></p>
                                <?php else: ?>
                                    <p>Tài khoản của bạn đã được tạo thành công!</p>
                                    <p><strong>Tài khoản đã được tự động kích hoạt.</strong> Bạn có thể đăng nhập ngay bây giờ.</p>
                                <?php endif; ?>
                                <p style="margin-top: 20px;">
                                    <a href="index.php" class="btn btn-primary">Về Trang Chủ</a>
                                    <?php if (!$email_sent): ?>
                                        <a href="login.php" class="btn btn-success">Đăng Nhập Ngay</a>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    } else {
        ?>
        <script>
            window.alert("Error during registration. Please try again.");
        </script>
        <meta http-equiv="refresh" content="1;url=signup.php" />
        <?php
    }
?>