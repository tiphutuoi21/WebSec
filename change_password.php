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
    $otp_sent = false;
    
    // Handle OTP request
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $message = 'CSRF token validation failed';
            $message_type = 'error';
        } else {
            $email = $_SESSION['email'];
            $user_id = $_SESSION['id'];
            
            // Get user data
            $user_query = "SELECT name FROM users WHERE id = ? AND email = ?";
            $stmt = mysqli_prepare($con, $user_query);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "is", $user_id, $email);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);
                
                if ($user) {
                    // Generate OTP
                    $otp = SecurityHelper::createPasswordChangeOTP($user_id, $email, $con);
                    
                    if ($otp) {
                        // Send OTP email
                        if (MailHelper::sendPasswordChangeOTP($email, $user['name'], $otp)) {
                            $message = 'OTP has been sent to your registered email. It will expire in 1 minute.';
                            $message_type = 'success';
                            $otp_sent = true;
                        } else {
                            $message = 'Failed to send OTP. Please try again.';
                            $message_type = 'error';
                        }
                    } else {
                        $message = 'Failed to generate OTP. Please try again.';
                        $message_type = 'error';
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
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Change Password - Figure Shop</title>
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
            
            .next-step-link {
                text-align: center;
                margin-top: 20px;
                padding: 15px;
                background-color: #e7f3ff;
                border-radius: 4px;
            }
            
            .next-step-link p {
                margin: 0 0 10px 0;
                color: #555;
            }
            
            .next-step-link a {
                color: #007bff;
                text-decoration: none;
                font-weight: bold;
            }
            
            .next-step-link a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <?php require 'header.php'; ?>
        <div class="container">
            <div class="password-change-container">
                <div class="page-header">
                    <h2>Change Password</h2>
                </div>
                
                <?php if (!empty($message)): ?>
                    <div class="alert-message alert-<?php echo htmlspecialchars($message_type); ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!$otp_sent): ?>
                    <div class="info-box">
                        <p><strong>Security Notice:</strong> For your account security, we'll send a One-Time Password (OTP) to your registered email. You'll need to enter this OTP to change your password.</p>
                    </div>
                    
                    <form method="POST" action="">
                        <?php echo SecurityHelper::getCSRFField(); ?>
                        
                        <div class="form-group">
                            <label for="email">Your Email</label>
                            <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" disabled>
                            <small class="form-text text-muted">OTP will be sent to this email</small>
                        </div>
                        
                        <div class="btn-container">
                            <button type="submit" class="btn btn-primary">Send OTP Code</button>
                            <a href="settings.php" class="btn btn-secondary" style="text-decoration: none; display: flex; align-items: center; justify-content: center;">Cancel</a>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="next-step-link">
                        <p><strong>✓ OTP sent successfully!</strong></p>
                        <p>Check your email for the 6-digit OTP code.</p>
                        <p>The code will expire in 1 minute.</p>
                        <a href="verify_otp_password.php">Enter OTP and Change Password →</a>
                    </div>
                <?php endif; ?>
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
