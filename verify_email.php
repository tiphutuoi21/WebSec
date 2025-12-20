<?php
    require 'connection.php';
    require 'config.php';
    
    $error = '';
    $success = '';
    
    if(isset($_GET['token'])) {
        $token = mysqli_real_escape_string($con, $_GET['token']);
        
        // Prepare statement to prevent SQL injection
        $verify_query = "SELECT id, email, name FROM users WHERE verification_token = ? AND token_expiry > NOW() AND email_verified = 0";
        $stmt = mysqli_prepare($con, $verify_query);
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_array($result);
            $user_id = $user['id'];
            
            // Update user to verified
            $update_query = "UPDATE users SET email_verified = 1, verification_token = NULL, token_expiry = NULL WHERE id = ?";
            $update_stmt = mysqli_prepare($con, $update_query);
            mysqli_stmt_bind_param($update_stmt, "i", $user_id);
            
            if(mysqli_stmt_execute($update_stmt)) {
                $success = "Email verified successfully! You can now login.";
            } else {
                $error = "An error occurred. Please try again.";
            }
        } else {
            $error = "Invalid or expired verification link.";
        }
    } else {
        $error = "No verification token provided.";
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/lifestyleStore.png" />
        <title>Email Verification - Lifestyle Store</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css">
        <script type="text/javascript" src="bootstrap/js/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="css/style.css" type="text/css">
    </head>
    <body>
        <div class="container" style="margin-top: 100px;">
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <div class="panel <?php echo $success ? 'panel-success' : 'panel-danger'; ?>">
                        <div class="panel-heading">
                            <h3>Email Verification</h3>
                        </div>
                        <div class="panel-body">
                            <?php if($success): ?>
                                <div class="alert alert-success">
                                    <?php echo $success; ?>
                                </div>
                                <p><a href="login.php" class="btn btn-primary">Go to Login</a></p>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <?php echo $error; ?>
                                </div>
                                <p><a href="signup.php" class="btn btn-primary">Back to Sign Up</a></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
