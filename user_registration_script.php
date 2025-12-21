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
        
        // Send verification email
        if(MailHelper::sendVerificationEmail($email, $name, $verification_token)) {
            $_SESSION['email'] = $email;
            $_SESSION['id'] = $user_id;
            $_SESSION['pending_verification'] = true;
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
                                    <h3>Registration Successful!</h3>
                                </div>
                                <div class="panel-body">
                                    <p>Thank you for registering with Lifestyle Store!</p>
                                    <p>A verification email has been sent to <strong><?php echo htmlspecialchars($email); ?></strong></p>
                                    <p>Please check your email and click the verification link to activate your account.</p>
                                    <p><small>If you don't see the email, please check your spam folder.</small></p>
                                    <p style="margin-top: 20px;">
                                        <a href="index.php" class="btn btn-primary">Back to Home</a>
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
            // Delete user if email sending failed
            $delete_query = "DELETE FROM users WHERE id = ?";
            $delete_stmt = mysqli_prepare($con, $delete_query);
            mysqli_stmt_bind_param($delete_stmt, "i", $user_id);
            mysqli_stmt_execute($delete_stmt);
            
            ?>
            <script>
                window.alert("Error sending verification email. Please try again later.");
            </script>
            <meta http-equiv="refresh" content="1;url=signup.php" />
            <?php
        }
    } else {
        ?>
        <script>
            window.alert("Error during registration. Please try again.");
        </script>
        <meta http-equiv="refresh" content="1;url=signup.php" />
        <?php
    }
?>