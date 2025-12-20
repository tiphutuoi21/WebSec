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
    
    // Validate password length
    $password_check = SecurityHelper::isStrongPassword($password);
    if (!$password_check['valid']) {
        echo htmlspecialchars($password_check['message']) . " Redirecting you back to login page...";
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
        if($row['email_verified'] == 0) {
            // Log unverified login attempt
            SecurityHelper::logSecurityEvent($con, 'unverified_login_attempt', 'Email: ' . $email);
            ?>
            <script>
                window.alert("Please verify your email before logging in. Check your email for the verification link.");
            </script>
            <meta http-equiv="refresh" content="1;url=login.php" />
            <?php
        } else {
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
