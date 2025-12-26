<?php
    require 'connection.php';
    require 'SecurityHelper.php';

    // Ensure UUID column exists for users
    SecurityHelper::ensureUserUidColumn($con);
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !SecurityHelper::verifyCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    // ===== BUFFER OVERFLOW PREVENTION =====
    // Validate input lengths before processing
    $emailCheck = SecurityEnhancements::limitInputLength($_POST['email'] ?? '', SecurityEnhancements::MAX_EMAIL_LENGTH, 'Email');
    $passwordCheck = SecurityEnhancements::limitInputLength($_POST['password'] ?? '', SecurityEnhancements::MAX_PASSWORD_LENGTH, 'Password');
    
    if (!$emailCheck['valid'] || !$passwordCheck['valid']) {
        SecurityEnhancements::logSecurityViolation('buffer_overflow_attempt', 'Login input too long');
        echo "Invalid input length. Redirecting...";
        ?>
        <meta http-equiv="refresh" content="2;url=login.php" />
        <?php
        exit();
    }
    
    // Get and sanitize input
    $email = SecurityHelper::getString('email', 'POST');
    $password = SecurityHelper::getString('password', 'POST');
    
    // ===== DoS PREVENTION - Advanced Rate Limiting =====
    if (!SecurityEnhancements::checkAdvancedRateLimit($con, 'login', $email)) {
        // Too many failed attempts - add delay
        SecurityEnhancements::throttleRequest(6); // 32 second delay
        error_log("Rate limit exceeded for login: " . $email);
        ?>
        <script>
            window.alert("Too many failed login attempts. Please try again in 5 minutes.");
        </script>
        <meta http-equiv="refresh" content="1;url=login.php" />
        <?php
        exit();
    }
    
    // Check rate limiting (legacy - keep for backwards compatibility)
    if (!SecurityHelper::checkRateLimit($email, 5, 300)) {
        // Too many failed attempts
        error_log("Rate limit exceeded for login: " . $email);
        ?>
        <script>
            window.alert("Too many failed login attempts. Please try again in 5 minutes.");
        </script>
        <meta http-equiv="refresh" content="1;url=login.php" />
        <?php
        exit();
    }
    
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
    
    // Use prepared statement to prevent SQL injection
    // Note: We fetch the stored hash and verify it with password_verify() instead of hashing in SQL
    $user_authentication_query = "SELECT id, email, email_verified, password, user_uid FROM users WHERE email = ?";
    $stmt = mysqli_prepare($con, $user_authentication_query);
    
    if (!$stmt) {
        error_log("Database query error in login_submit.php: " . mysqli_error($con));
        echo "An error occurred during login. Please try again later.";
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) == 0) {
        // Log failed login attempt
        SecurityHelper::logSecurityEvent($con, 'failed_login_attempt', 'Email: ' . $email);
        SecurityHelper::recordFailedAttempt($email);
        ?>
        <script>
            window.alert("Wrong username or password");
        </script>
        <meta http-equiv="refresh" content="1;url=login.php" />
        <?php
    } else {
        $row = mysqli_fetch_array($result);
        
        // Verify password using bcrypt
        $stored_hash = $row['password'];
        $password_correct = password_verify($password, $stored_hash);
        
        if (!$password_correct) {
            // Log failed login attempt
            SecurityHelper::logSecurityEvent($con, 'failed_login_attempt', 'Email: ' . $email);
            SecurityHelper::recordFailedAttempt($email);
            ?>
            <script>
                window.alert("Wrong username or password");
            </script>
            <meta http-equiv="refresh" content="1;url=login.php" />
            <?php
        } else {
            // Check if email is verified - REQUIRED for login
            if($row['email_verified'] == 0) {
                // Email not verified - block login
                SecurityHelper::logSecurityEvent($con, 'unverified_login_attempt', 'Email: ' . $email);
                ?>
                <script>
                    window.alert("Vui lòng xác thực email trước khi đăng nhập. Kiểm tra email của bạn để tìm link xác thực. Nếu bạn không nhận được email, vui lòng kiểm tra thư mục spam hoặc đăng ký lại.");
                </script>
                <meta http-equiv="refresh" content="1;url=login.php" />
                <?php
                exit();
            } else {
                // Email is verified - proceed with normal login
                // Ensure user has UUID
                $user_uuid = $row['user_uid'] ?? null;
                if (empty($user_uuid)) {
                    $user_uuid = SecurityHelper::ensureUserUuid($con, $row['id']);
                }

                // Create secure session for user
                if (SessionManager::createUserSession($con, $row['id'], $email, 3, 'customer', $user_uuid)) {
                    // Clear failed login attempts on successful login
                    SecurityHelper::clearFailedAttempts($email);
                    
                    // Log successful login
                    SecurityHelper::logSecurityEvent($con, 'customer_login', 'Successful login');
                    
                    // Ensure session is written before redirect
                    session_write_close();
                    
                    header('Location: products.php');
                    exit();
                } else {
                    // Session creation failed
                    error_log("Session creation failed for user: " . $email);
                    ?>
                    <script>
                        window.alert("Session creation failed. Please try again.");
                    </script>
                    <meta http-equiv="refresh" content="2;url=login.php" />
                    <?php
                }
            }
        }
    }
    
    mysqli_stmt_close($stmt);
?>
