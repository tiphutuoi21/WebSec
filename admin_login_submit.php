<?php
    require 'connection.php';
    require 'SecurityHelper.php';

    // Ensure UUID column exists for admins
    SecurityHelper::ensureAdminUidColumn($con);
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !SecurityHelper::verifyCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    // Get and sanitize input
    $email = SecurityHelper::getString('email', 'POST');
    $password = SecurityHelper::getString('password', 'POST');
    
    // Check rate limiting for admin login (stricter: 3 attempts in 5 minutes)
    $admin_rate_limit_key = 'admin_' . $email;
    if (!SecurityHelper::checkRateLimit($admin_rate_limit_key, 3, 300)) {
        // Too many failed attempts - use generic error message to prevent user enumeration
        error_log("Admin rate limit exceeded for login: " . $email);
        SecurityHelper::recordFailedAttempt($admin_rate_limit_key);
        ?>
        <script>
            window.alert("Login attempt failed. Please try again later.");
        </script>
        <meta http-equiv="refresh" content="1;url=admin_login.php" />
        <?php
        exit();
    }
    
    // Validate email format
    if (!SecurityHelper::isValidEmail($email)) {
        echo "Incorrect email format. Redirecting you back to admin login page...";
        ?>
        <meta http-equiv="refresh" content="2;url=admin_login.php" />
        <?php
        exit();
    }
    
    // Use prepared statement to prevent SQL injection
    // Note: We fetch the stored hash and verify it with password_verify() instead of hashing in SQL
    $admin_authentication_query = "SELECT id, email, role_id, password, admin_uid FROM admins WHERE email = ?";
    $stmt = mysqli_prepare($con, $admin_authentication_query);
    
    if (!$stmt) {
        die('Database error: ' . htmlspecialchars(mysqli_error($con)));
    }
    
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) == 0) {
        // Log failed admin login attempt
        SecurityHelper::logSecurityEvent($con, 'failed_admin_login', 'Email: ' . $email);
        $admin_rate_limit_key = 'admin_' . $email;
        SecurityHelper::recordFailedAttempt($admin_rate_limit_key);
        ?>
        <script>
            window.alert("Login failed. Please check your credentials.");
        </script>
        <meta http-equiv="refresh" content="1;url=admin_login.php" />
        <?php
    } else {
        $row = mysqli_fetch_array($result);
        
        // Verify password using bcrypt
        $password_correct = password_verify($password, $row['password']);
        
        if (!$password_correct) {
            // Log failed login attempt
            SecurityHelper::logSecurityEvent($con, 'failed_admin_login', 'Email: ' . $email);
            $admin_rate_limit_key = 'admin_' . $email;
            SecurityHelper::recordFailedAttempt($admin_rate_limit_key);
            ?>
            <script>
                window.alert("Login failed. Please check your credentials.");
            </script>
            <meta http-equiv="refresh" content="1;url=admin_login.php" />
            <?php
        } else {
            // Ensure admin has UUID
            $admin_uuid = $row['admin_uid'] ?? null;
            if (empty($admin_uuid)) {
                $admin_uuid = SecurityHelper::ensureAdminUuid($con, $row['id']);
            }

            // Create secure session for admin user
            SessionManager::createUserSession($con, $row['id'], $email, $row['role_id'], 'admin', $admin_uuid);
            
            // Clear failed login attempts on successful login
            $admin_rate_limit_key = 'admin_' . $email;
            SecurityHelper::clearFailedAttempts($admin_rate_limit_key);
            
            // Log successful admin login
            SecurityHelper::logSecurityEvent($con, 'admin_login', 'Successful admin login');
            
            // Redirect based on role
            if (intval($row['role_id']) === 2) {
                header('Location: sales_dashboard.php');
            } else {
                header('Location: admin_dashboard.php');
            }
            exit();
        }
    }
    
    mysqli_stmt_close($stmt);
?>
