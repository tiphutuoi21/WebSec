<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    require 'MailHelper.php';
    
    if (!isset($_SESSION['email'])) {
        header('Location: index.php');
        exit();
    }
    
    $message = '';
    $message_type = '';
    $otp_verified = false;
    
    // Handle OTP verification and password change
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $message = 'CSRF token validation failed';
            $message_type = 'error';
        } else {
            $email = $_SESSION['email'];
            $user_id = $_SESSION['id'];
            $otp = SecurityHelper::getString('otp', 'POST');
            $new_password = SecurityHelper::getString('newPassword', 'POST');
            $retype_password = SecurityHelper::getString('retypePassword', 'POST');
            
            // Check if OTP field is filled
            if (!empty($otp) && empty($new_password) && empty($retype_password)) {
                // User is entering OTP, validate it
                $otp_result = SecurityHelper::verifyPasswordChangeOTP($user_id, $otp, $con);
                
                if ($otp_result['valid']) {
                    $message = $otp_result['message'];
                    $message_type = 'success';
                    $otp_verified = true;
                } else {
                    $message = $otp_result['message'];
                    $message_type = 'error';
                }
            } elseif (!empty($new_password) && !empty($retype_password)) {
                // User is changing password after OTP verification
                
                // Validate that passwords match
                if ($new_password !== $retype_password) {
                    $message = 'Passwords do not match';
                    $message_type = 'error';
                } elseif (empty($otp)) {
                    $message = 'OTP not verified. Please go back and verify OTP first.';
                    $message_type = 'error';
                } else {
                    // Verify OTP one more time before changing password
                    // Hash the OTP to match what's stored in database
                    $otp_hash = hash('sha256', $otp);
                    
                    $verify_query = "SELECT id FROM password_reset_otp 
                                   WHERE user_id = ? AND otp = ? AND is_used = 1";
                    $stmt = mysqli_prepare($con, $verify_query);
                    
                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, "is", $user_id, $otp_hash);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $otp_record = mysqli_fetch_assoc($result);
                        mysqli_stmt_close($stmt);
                        
                        if (!$otp_record) {
                            $message = 'OTP verification failed. Please request a new OTP.';
                            $message_type = 'error';
                        } else {
                            // Get user data for password validation
                            $user_query = "SELECT id, name, email, contact, city, address, password FROM users WHERE id = ? AND email = ?";
                            $user_stmt = mysqli_prepare($con, $user_query);
                            
                            if ($user_stmt) {
                                mysqli_stmt_bind_param($user_stmt, "is", $user_id, $email);
                                mysqli_stmt_execute($user_stmt);
                                $user_result = mysqli_stmt_get_result($user_stmt);
                                $user = mysqli_fetch_assoc($user_result);
                                mysqli_stmt_close($user_stmt);
                                
                                if ($user) {
                                    // Validate password strength with user data
                                    $userData = [
                                        'name' => $user['name'],
                                        'email' => $user['email'],
                                        'contact' => $user['contact'],
                                        'city' => $user['city'],
                                        'address' => $user['address']
                                    ];
                                    $password_check = SecurityHelper::isStrongPassword($new_password, $userData);
                                    
                                    if (!$password_check['valid']) {
                                        $message = $password_check['message'];
                                        $message_type = 'error';
                                    } else {
                                        // Check if new password is the same as current
                                        $stored_hash = $user['password'];
                                        $same_password = password_verify($new_password, $stored_hash);
                                        
                                        if ($same_password) {
                                            $message = 'New password must be different from your current password';
                                            $message_type = 'error';
                                        } else {
                                            // Hash new password
                                            $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
                                            
                                            // Update password
                                            $update_query = "UPDATE users SET password = ? WHERE id = ? AND email = ?";
                                            $update_stmt = mysqli_prepare($con, $update_query);
                                            
                                            if ($update_stmt) {
                                                mysqli_stmt_bind_param($update_stmt, "sis", $new_password_hash, $user_id, $email);
                                                
                                                if (mysqli_stmt_execute($update_stmt)) {
                                                    // Save old password to history
                                                    $insert_history_query = "INSERT INTO password_history (user_id, password_hash) VALUES (?, ?)";
                                                    $history_stmt = mysqli_prepare($con, $insert_history_query);
                                                    
                                                    if ($history_stmt) {
                                                        mysqli_stmt_bind_param($history_stmt, "is", $user_id, $stored_hash);
                                                        mysqli_stmt_execute($history_stmt);
                                                        mysqli_stmt_close($history_stmt);
                                                    }
                                                    
                                                    mysqli_stmt_close($update_stmt);
                                                    $message = 'Password changed successfully! Redirecting to settings...';
                                                    $message_type = 'success';
                                                    
                                                    // Redirect after 2 seconds
                                                    ?>
                                                    <script>
                                                        setTimeout(function() {
                                                            window.location.href = 'settings.php';
                                                        }, 2000);
                                                    </script>
                                                    <?php
                                                } else {
                                                    $message = 'Failed to update password. Please try again.';
                                                    $message_type = 'error';
                                                }
                                            } else {
                                                $message = 'Database error. Please try again.';
                                                $message_type = 'error';
                                            }
                                        }
                                    }
                                } else {
                                    $message = 'User not found. Please log in again.';
                                    $message_type = 'error';
                                }
                            } else {
                                $message = 'Database error. Please try again.';
                                $message_type = 'error';
                            }
                        }
                    } else {
                        $message = 'Database error. Please try again.';
                        $message_type = 'error';
                    }
                }
            } else {
                $message = 'Please fill all required fields';
                $message_type = 'error';
            }
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Verify OTP & Change Password - Figure Shop</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css">
        <script type="text/javascript" src="bootstrap/js/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="css/style.css" type="text/css">
        <style>
            .password-change-container {
                max-width: 600px;
                margin: 30px auto;
                padding: 30px;
                background-color: #f9f9f9;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            .page-header {
                margin-bottom: 30px;
                border-bottom: 3px solid #007bff;
                padding-bottom: 15px;
            }
            
            .page-header h2 {
                color: #333;
                margin: 0;
            }
            
            .info-box {
                background-color: #e7f3ff;
                border-left: 4px solid #007bff;
                padding: 15px;
                margin: 20px 0;
                border-radius: 4px;
            }
            
            .info-box p {
                margin: 0;
                color: #555;
                font-size: 14px;
            }
            
            .alert-message {
                margin: 20px 0;
                border-radius: 4px;
            }
            
            .alert-success {
                background-color: #d4edda;
                border: 1px solid #c3e6cb;
                color: #155724;
                padding: 12px;
                border-radius: 4px;
            }
            
            .alert-error {
                background-color: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
                padding: 12px;
                border-radius: 4px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-group label {
                font-weight: bold;
                color: #333;
                margin-bottom: 8px;
            }
            
            .form-group input {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 14px;
            }
            
            .form-group input:focus {
                outline: none;
                border-color: #007bff;
                box-shadow: 0 0 5px rgba(0,123,255,0.25);
            }
            
            .otp-input {
                text-align: center;
                font-size: 24px;
                letter-spacing: 10px;
                font-family: monospace;
            }
            
            .form-text {
                font-size: 12px;
                color: #666;
                margin-top: 5px;
            }
            
            .error-message {
                color: #dc3545;
                font-size: 12px;
                margin-top: 5px;
                display: none;
            }
            
            .btn-container {
                display: flex;
                gap: 10px;
                justify-content: space-between;
                margin-top: 20px;
            }
            
            .btn {
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                font-size: 14px;
                cursor: pointer;
                transition: all 0.3s;
            }
            
            .btn-primary {
                background-color: #007bff;
                color: white;
                flex: 1;
            }
            
            .btn-primary:hover {
                background-color: #0056b3;
                color: white;
            }
            
            .btn-secondary {
                background-color: #6c757d;
                color: white;
                flex: 1;
            }
            
            .btn-secondary:hover {
                background-color: #545b62;
                color: white;
            }
            
            .step-indicator {
                display: flex;
                justify-content: space-between;
                margin-bottom: 30px;
            }
            
            .step {
                flex: 1;
                text-align: center;
                padding: 10px;
                margin: 0 5px;
                border-radius: 4px;
                background-color: #f0f0f0;
                color: #666;
                font-size: 12px;
            }
            
            .step.active {
                background-color: #007bff;
                color: white;
                font-weight: bold;
            }
            
            .step.completed {
                background-color: #28a745;
                color: white;
            }
            
            .form-section {
                margin-bottom: 20px;
                padding-bottom: 20px;
                border-bottom: 1px solid #ddd;
            }
            
            .form-section:last-child {
                border-bottom: none;
            }
        </style>
        <script>
            function validatePassword() {
                var password = document.getElementById('newPassword').value;
                var errorDiv = document.getElementById('password-error');
                var errors = [];
                
                if (password.length === 0) {
                    errorDiv.style.display = 'none';
                    return;
                }
                
                if (password.length < 8) {
                    errors.push('At least 8 characters');
                }
                
                if (!/[A-Z]/.test(password)) {
                    errors.push('Uppercase letter (A-Z)');
                }
                
                if (!/[a-z]/.test(password)) {
                    errors.push('Lowercase letter (a-z)');
                }
                
                if (!/[0-9]/.test(password)) {
                    errors.push('Number (0-9)');
                }
                
                if (!/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/.test(password)) {
                    errors.push('Special character (!@#$%^&*, etc)');
                }
                
                if (errors.length > 0) {
                    errorDiv.style.display = 'block';
                    errorDiv.innerHTML = '<strong>Password must contain:</strong> ' + errors.join(', ');
                    errorDiv.style.color = '#DC143C';
                } else {
                    errorDiv.style.display = 'none';
                }
            }
            
            function validateRetype() {
                var password = document.getElementById('newPassword').value;
                var retype = document.getElementById('retypePassword').value;
                var errorDiv = document.getElementById('retype-error');
                
                if (retype.length === 0) {
                    errorDiv.style.display = 'none';
                    return;
                }
                
                if (password !== retype) {
                    errorDiv.style.display = 'block';
                    errorDiv.innerHTML = '<strong>Error:</strong> Passwords do not match';
                    errorDiv.style.color = '#DC143C';
                } else {
                    errorDiv.style.display = 'none';
                }
            }
        </script>
    </head>
    <body>
        <?php require 'header.php'; ?>
        <div class="container">
            <div class="password-change-container">
                <div class="page-header">
                    <h2>Verify OTP & Change Password</h2>
                </div>
                
                <?php if (!empty($message)): ?>
                    <div class="alert-message alert-<?php echo htmlspecialchars($message_type); ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <?php echo SecurityHelper::getCSRFField(); ?>
                    
                    <?php if (!$otp_verified): ?>
                        <!-- OTP Verification Step -->
                        <div class="form-section">
                            <div class="info-box">
                                <p>Enter the 6-digit code sent to your email. This code expires in 10 minutes.</p>
                            </div>
                            
                            <div class="form-group">
                                <label for="otp">One-Time Password (OTP)</label>
                                <input type="text" class="form-control otp-input" id="otp" name="otp" 
                                       maxlength="6" pattern="[0-9]{6}" placeholder="000000" required>
                                <small class="form-text">Enter the 6-digit code from your email</small>
                            </div>
                        </div>
                        
                        <div class="btn-container">
                            <button type="submit" class="btn btn-primary">Verify OTP</button>
                            <a href="change_password.php" class="btn btn-secondary" style="text-decoration: none; display: flex; align-items: center; justify-content: center;">Back</a>
                        </div>
                    <?php else: ?>
                        <!-- Password Change Step -->
                        <div class="form-section">
                            <div class="info-box">
                                <p><strong>✓ OTP Verified!</strong> Now enter your new password.</p>
                            </div>
                            
                            <div class="form-group">
                                <label for="newPassword">New Password</label>
                                <input type="password" class="form-control" id="newPassword" name="newPassword" 
                                       onkeyup="validatePassword()" required>
                                <div id="password-error" class="error-message"></div>
                                <small class="form-text">
                                    Password must contain: 8+ characters, uppercase, lowercase, number, and special character
                                </small>
                            </div>
                            
                            <div class="form-group">
                                <label for="retypePassword">Confirm New Password</label>
                                <input type="password" class="form-control" id="retypePassword" name="retypePassword" 
                                       onkeyup="validateRetype()" required>
                                <div id="retype-error" class="error-message"></div>
                            </div>
                        </div>
                        
                        <input type="hidden" name="otp" value="<?php echo htmlspecialchars($_POST['otp'] ?? ''); ?>">
                        
                        <div class="btn-container">
                            <button type="submit" class="btn btn-primary">Change Password</button>
                            <a href="change_password.php" class="btn btn-secondary" style="text-decoration: none; display: flex; align-items: center; justify-content: center;">Cancel</a>
                        </div>
                    <?php endif; ?>
                </form>
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
    </body>
</html>
