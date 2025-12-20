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
        echo "Incorrect email format. Redirecting you back to admin login page...";
        ?>
        <meta http-equiv="refresh" content="2;url=admin310817.php" />
        <?php
        exit();
    }
    
    // Hash password
    $password_hash = md5(md5($password));
    
    // Use prepared statement to prevent SQL injection
    $admin_authentication_query = "SELECT id, email, role_id FROM admins WHERE email = ? AND password = ?";
    $stmt = mysqli_prepare($con, $admin_authentication_query);
    
    if (!$stmt) {
        die('Database error: ' . htmlspecialchars(mysqli_error($con)));
    }
    
    mysqli_stmt_bind_param($stmt, "ss", $email, $password_hash);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) == 0) {
        // Log failed admin login attempt
        SecurityHelper::logSecurityEvent($con, 'failed_admin_login', 'Email: ' . $email);
        ?>
        <script>
            window.alert("Wrong email or password");
        </script>
        <meta http-equiv="refresh" content="1;url=admin310817.php" />
        <?php
    } else {
        $row = mysqli_fetch_array($result);
        
        // Create secure session for admin user
        SessionManager::createUserSession($con, $row['id'], $email, $row['role_id'], 'admin');
        
        // Log successful admin login
        SecurityHelper::logSecurityEvent($con, 'admin_login', 'Successful admin login');
        
        header('location: admin_dashboard.php');
        exit();
    }
    
    mysqli_stmt_close($stmt);
?>
