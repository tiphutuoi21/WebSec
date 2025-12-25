<?php
    require 'connection.php';
    require 'config.php';
    require 'MailHelper.php';
    require 'SecurityHelper.php';

    // Ensure UUID column exists and prepare new user UUID
    SecurityHelper::ensureUserUidColumn($con);
    
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
    
    // Hash password using bcrypt
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
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
    $user_uuid = SecurityHelper::generateUuidV7();
    
    // TRY TO SEND VERIFICATION EMAIL FIRST - BEFORE creating account
    // This ensures we only create accounts if we can verify the email
    $email_sent = MailHelper::sendVerificationEmail($email, $name, $verification_token);
    
    if (!$email_sent) {
        // Email sending failed - DO NOT create account
        error_log("Email verification sending failed during registration for email: $email");
        
        // Show error message
        ?>
        <script>
            window.alert("Unable to send verification email. Please check:\n\n1. Your email address is correct\n2. Check your spam/junk folder\n3. Try again in a few minutes\n\nIf the problem persists, please contact support.");
        </script>
        <meta http-equiv="refresh" content="2;url=signup.php" />
        <?php
        exit();
    }
    
    // Email was sent successfully - NOW create the account
    $user_registration_query = "INSERT INTO users (name, email, password, contact, city, address, verification_token, token_expiry, email_verified, user_uid) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?)";
    $register_stmt = mysqli_prepare($con, $user_registration_query);
    
    if (!$register_stmt) {
        error_log("Database error during user registration: " . mysqli_error($con));
        ?>
        <script>
            window.alert("An error occurred during registration. Please try again.");
        </script>
        <meta http-equiv="refresh" content="2;url=signup.php" />
        <?php
        exit();
    }
    
    mysqli_stmt_bind_param($register_stmt, "sssssssss", $name, $email, $password_hash, $contact, $city, $address, $verification_token, $token_expiry, $user_uuid);
    
    if(mysqli_stmt_execute($register_stmt)) {
        $user_id = mysqli_insert_id($con);
        
        // Account created successfully and email was sent
        // Set session for pending verification
        $_SESSION['pending_verification'] = true;
        $_SESSION['pending_email'] = $email;
        
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
                                <p>Email xác thực đã được gửi đến <strong><?php echo htmlspecialchars($email); ?></strong></p>
                                <p>Vui lòng kiểm tra email của bạn và click vào link xác thực để kích hoạt tài khoản.</p>
                                <p><small style="color: #666;">Nếu không thấy email, vui lòng kiểm tra thư mục spam hoặc junk.</small></p>
                                <p style="margin-top: 20px;">
                                    <a href="index.php" class="btn btn-primary">Về Trang Chủ</a>
                                    <a href="login.php" class="btn btn-info">Đến Trang Đăng Nhập</a>
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
        // Registration failed - log error
        error_log("User registration insert failed: " . mysqli_error($con));
        ?>
        <script>
            window.alert("An error occurred during registration. Please try again.");
        </script>
        <meta http-equiv="refresh" content="2;url=signup.php" />
        <?php
    }
?>