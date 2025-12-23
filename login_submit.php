<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !SecurityHelper::verifyCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    // Get and sanitize input
    $email = SecurityHelper::getString('email', 'POST');
    $password = SecurityHelper::getString('password', 'POST');
    
    // Validate email format
    if (!SecurityHelper::isValidEmail($email)) {
        echo "Incorrect email format. Redirecting you back to login page...";
        ?>
        <meta http-equiv="refresh" content="2;url=login.php" />
        <?php
        exit();
    }
    
    // Validate password is not empty (no strength check needed for login)
    if (empty($password)) {
        echo "Password cannot be empty. Redirecting you back to login page...";
        ?>
        <meta http-equiv="refresh" content="2;url=login.php" />
        <?php
        exit();
    }
    
    // Hash password (use prepared statement with hashed password)
    $password_hash = md5(md5($password));
    
    // Use prepared statement to prevent SQL injection
    $user_authentication_query = "SELECT id, email, email_verified FROM users WHERE email = ? AND password = ?";
    $stmt = mysqli_prepare($con, $user_authentication_query);
    
    if (!$stmt) {
        die('Database error: ' . htmlspecialchars(mysqli_error($con)));
    }
    
    mysqli_stmt_bind_param($stmt, "ss", $email, $password_hash);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) == 0) {
        // Log failed login attempt
        SecurityHelper::logSecurityEvent($con, 'failed_login_attempt', 'Email: ' . $email);
        ?>
        <script>
            window.alert("Wrong username or password");
        </script>
        <meta http-equiv="refresh" content="1;url=login.php" />
        <?php
    } else {
        $row = mysqli_fetch_array($result);
        
        // Check if email is verified
        // If email is not verified, check if PHPMailer is available
        // If PHPMailer is not available, auto-verify and allow login
        if($row['email_verified'] == 0) {
            // Check if PHPMailer is available
            $vendor_autoload = __DIR__ . '/vendor/autoload.php';
            $phpmailer_available = file_exists($vendor_autoload) && class_exists('PHPMailer\PHPMailer\PHPMailer');
            
            if ($phpmailer_available) {
                // PHPMailer is available - require email verification
                SecurityHelper::logSecurityEvent($con, 'unverified_login_attempt', 'Email: ' . $email);
                ?>
                <script>
                    window.alert("Vui lòng xác thực email trước khi đăng nhập. Kiểm tra email của bạn để tìm link xác thực.");
                </script>
                <meta http-equiv="refresh" content="1;url=login.php" />
                <?php
            } else {
                // PHPMailer not available - auto-verify and allow login
                $auto_verify_query = "UPDATE users SET email_verified = 1 WHERE id = ?";
                $auto_verify_stmt = mysqli_prepare($con, $auto_verify_query);
                if ($auto_verify_stmt) {
                    mysqli_stmt_bind_param($auto_verify_stmt, "i", $row['id']);
                    mysqli_stmt_execute($auto_verify_stmt);
                    mysqli_stmt_close($auto_verify_stmt);
                }
                
                // Create secure session for user
                SessionManager::createUserSession($con, $row['id'], $email, 3, 'customer');
                
                // Log successful login
                SecurityHelper::logSecurityEvent($con, 'customer_login', 'Successful login (auto-verified)');
                
                header('location: products.php');
                exit();
            }
        } else {
            // Email is verified - proceed with normal login
            // Create secure session for user
            SessionManager::createUserSession($con, $row['id'], $email, 3, 'customer');
            
            // Log successful login
            SecurityHelper::logSecurityEvent($con, 'customer_login', 'Successful login');
            
            header('location: products.php');
            exit();
        }
    }
    
    mysqli_stmt_close($stmt);
?>
