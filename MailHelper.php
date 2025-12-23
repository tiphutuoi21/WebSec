<?php
require __DIR__ . '/config.php';

// Check if PHPMailer is available via Composer
$vendor_autoload = __DIR__ . '/vendor/autoload.php';
$phpmailer_available = false;

if (file_exists($vendor_autoload)) {
    require $vendor_autoload;
    // Check if PHPMailer classes exist
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $phpmailer_available = true;
    }
}

class MailHelper {
    
    public static function sendVerificationEmail($email, $name, $token) {
        // Check if PHPMailer is available
        global $phpmailer_available;
        if (!isset($phpmailer_available) || !$phpmailer_available) {
            // PHPMailer not available - log and return false
            error_log("PHPMailer not available - email verification skipped for: $email");
            return false;
        }
        
        // Check if class exists before using it
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            error_log("PHPMailer class not found - email verification skipped for: $email");
            return false;
        }
        
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = GMAIL_EMAIL;
            $mail->Password = GMAIL_PASSWORD;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => false
                )
            );
            
            // Recipients
            $mail->setFrom(GMAIL_EMAIL, GMAIL_FROM_NAME);
            $mail->addAddress($email, $name);
            $mail->addReplyTo(GMAIL_EMAIL, GMAIL_FROM_NAME);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Email Verification - Figure Shop';
            
            $verification_link = SITE_URL . '/verify_email.php?token=' . urlencode($token);
            
            $mail->Body = self::getEmailTemplate($name, $verification_link);
            $mail->AltBody = "Please verify your email by clicking this link: " . $verification_link;
            
            $mail->send();
            return true;
            
        } catch (\Exception $e) {
            error_log("PHPMailer Error: " . (isset($mail) ? $mail->ErrorInfo : $e->getMessage()));
            return false;
        }
    }
    
    public static function sendWelcomeEmail($email, $name) {
        // Check if PHPMailer is available
        global $phpmailer_available;
        if (!isset($phpmailer_available) || !$phpmailer_available) {
            // PHPMailer not available - log and return false
            error_log("PHPMailer not available - welcome email skipped for: $email");
            return false;
        }
        
        // Check if class exists before using it
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            error_log("PHPMailer class not found - welcome email skipped for: $email");
            return false;
        }
        
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = GMAIL_EMAIL;
            $mail->Password = GMAIL_PASSWORD;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => false
                )
            );
            
            $mail->setFrom(GMAIL_EMAIL, GMAIL_FROM_NAME);
            $mail->addAddress($email, $name);
            $mail->addReplyTo(GMAIL_EMAIL, GMAIL_FROM_NAME);
            $mail->isHTML(true);
            $mail->Subject = 'Welcome to Figure Shop!';
            $mail->Body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9; }
                        .header { background-color: #dc143c; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                        .content { padding: 20px; background-color: white; }
                        .footer { background-color: #f9f9f9; padding: 10px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 5px 5px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>Figure Shop</h1>
                        </div>
                        <div class='content'>
                            <h2>Welcome, " . htmlspecialchars($name) . "!</h2>
                            <p>Thank you for registering with Figure Shop.</p>
                            <p>Your account has been successfully created and verified.</p>
                            <p>You can now login and start shopping for amazing products.</p>
                            <p>Best regards,<br/>Figure Shop Team</p>
                        </div>
                        <div class='footer'>
                            <p>&copy; Figure Shop. All Rights Reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            $mail->AltBody = "Welcome to Figure Shop, " . htmlspecialchars($name) . "!";
            
            $mail->send();
            return true;
            
        } catch (\Exception $e) {
            error_log("PHPMailer Error: " . (isset($mail) ? $mail->ErrorInfo : $e->getMessage()));
            return false;
        }
    }
    
    private static function getEmailTemplate($name, $verification_link) {
        return "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9; }
                    .header { background-color: #337ab7; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                    .content { padding: 20px; background-color: white; }
                    .button { display: inline-block; background-color: #337ab7; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin-top: 20px; font-weight: bold; }
                    .footer { background-color: #f9f9f9; padding: 10px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 5px 5px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Figure Shop</h1>
                    </div>
                    <div class='content'>
                        <h2>Welcome, " . htmlspecialchars($name) . "!</h2>
                        <p>Thank you for registering with Figure Shop.</p>
                        <p>Please verify your email address by clicking the button below:</p>
                        <a href='" . htmlspecialchars($verification_link) . "' class='button'>Verify Email</a>
                        <p>Or copy and paste this link in your browser:</p>
                        <p><small>" . htmlspecialchars($verification_link) . "</small></p>
                        <p><strong>This link will expire in 24 hours.</strong></p>
                        <p>If you did not register with us, please ignore this email.</p>
                        <p style='margin-top: 30px; color: #999; font-size: 11px;'>
                            This is an automated message, please do not reply to this email.
                        </p>
                    </div>
                    <div class='footer'>
                        <p>&copy; Figure Shop. All Rights Reserved.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }
}
?>

