<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    if(!isset($_SESSION['email'])){
        header('Location: index.php');
        exit();
    }
    
    // Get and sanitize input
    $old_password = SecurityHelper::getString('oldPassword', 'POST');
    $new_password = SecurityHelper::getString('newPassword', 'POST');
    $retype_password = SecurityHelper::getString('retype', 'POST');
    $email = $_SESSION['email'];
    
    // Validate that all fields are provided
    if (empty($old_password) || empty($new_password) || empty($retype_password)) {
        ?>
        <script>
            window.alert("Vui lòng điền đầy đủ thông tin!");
        </script>
        <meta http-equiv="refresh" content="1;url=settings.php" />
        <?php
        exit();
    }
    
    // Check if new password and retype match
    if ($new_password !== $retype_password) {
        ?>
        <script>
            window.alert("Mật khẩu nhập lại không khớp!");
        </script>
        <meta http-equiv="refresh" content="1;url=settings.php" />
        <?php
        exit();
    }
    
    // Get user data for password validation
    $user_query = "SELECT id, name, email, contact, city, address FROM users WHERE email = ?";
    $stmt = mysqli_prepare($con, $user_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if (!$user) {
            ?>
            <script>
                window.alert("Không tìm thấy thông tin người dùng!");
            </script>
            <meta http-equiv="refresh" content="1;url=settings.php" />
            <?php
            exit();
        }
        
        // Validate new password strength with user data
        $userData = [
            'name' => $user['name'],
            'email' => $user['email'],
            'contact' => $user['contact'],
            'city' => $user['city'],
            'address' => $user['address']
        ];
        $password_check = SecurityHelper::isStrongPassword($new_password, $userData);
        if (!$password_check['valid']) {
            ?>
            <script>
                window.alert("<?php echo htmlspecialchars($password_check['message'], ENT_QUOTES); ?>");
            </script>
            <meta http-equiv="refresh" content="3;url=settings.php" />
            <?php
            exit();
        }
    } else {
        ?>
        <script>
            window.alert("Lỗi hệ thống! Vui lòng thử lại sau.");
        </script>
        <meta http-equiv="refresh" content="1;url=settings.php" />
        <?php
        exit();
    }
    
    // Hash new password with bcrypt
    $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
    
    // Verify old password using prepared statement
    $password_from_database_query = "SELECT password FROM users WHERE email = ?";
    $stmt = mysqli_prepare($con, $password_from_database_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        $password_correct = false;
        if ($row) {
            $stored_hash = $row['password'];
            // Use bcrypt password_verify() - no MD5 support
            $password_correct = password_verify($old_password, $stored_hash);
        }
        
        if (!$password_correct) {
            ?>
            <script>
                window.alert("Mật khẩu cũ không đúng!");
            </script>
            <meta http-equiv="refresh" content="1;url=settings.php" />
            <?php
        } else {
            // Check if new password is the same as current password
            $same_password = password_verify($new_password, $stored_hash);
            
            if ($same_password) {
                ?>
                <script>
                    window.alert("Mật khẩu mới không được trùng với mật khẩu hiện tại!");
                </script>
                <meta http-equiv="refresh" content="1;url=settings.php" />
                <?php
                exit();
            }
            
            // Note: Password history check is skipped for bcrypt since it generates unique hashes
            // The password_history table stores old passwords for audit purposes
            // but we don't prevent reuse since bcrypt hashes are always unique
            
            // Update password using prepared statement
            $update_password_query = "UPDATE users SET password = ? WHERE email = ?";
            $update_stmt = mysqli_prepare($con, $update_password_query);
            if ($update_stmt) {
                mysqli_stmt_bind_param($update_stmt, "ss", $new_password_hash, $email);
                if (mysqli_stmt_execute($update_stmt)) {
                    // Save old password to history (for audit purposes)
                    $user_id = $user['id'];
                    $insert_history_query = "INSERT INTO password_history (user_id, password_hash) VALUES (?, ?)";
                    $history_insert_stmt = mysqli_prepare($con, $insert_history_query);
                    if ($history_insert_stmt) {
                        // Store the old password hash from database
                        mysqli_stmt_bind_param($history_insert_stmt, "is", $user_id, $stored_hash);
                        mysqli_stmt_execute($history_insert_stmt);
                        mysqli_stmt_close($history_insert_stmt);
                    }
                    
                    // Keep only last 5 passwords in history (optional cleanup)
                    // Count total passwords for this user
                    $count_query = "SELECT COUNT(*) as total FROM password_history WHERE user_id = ?";
                    $count_stmt = mysqli_prepare($con, $count_query);
                    if ($count_stmt) {
                        mysqli_stmt_bind_param($count_stmt, "i", $user_id);
                        mysqli_stmt_execute($count_stmt);
                        $count_result = mysqli_stmt_get_result($count_stmt);
                        $count_row = mysqli_fetch_assoc($count_result);
                        mysqli_stmt_close($count_stmt);
                        
                        // If more than 5 passwords, delete the oldest ones
                        if ($count_row && $count_row['total'] > 5) {
                            // Get IDs to delete (oldest ones beyond the 5 most recent)
                            $delete_query = "SELECT id FROM password_history WHERE user_id = ? ORDER BY created_at ASC LIMIT ?";
                            $delete_stmt = mysqli_prepare($con, $delete_query);
                            if ($delete_stmt) {
                                $to_delete = $count_row['total'] - 5;
                                mysqli_stmt_bind_param($delete_stmt, "ii", $user_id, $to_delete);
                                mysqli_stmt_execute($delete_stmt);
                                $delete_result = mysqli_stmt_get_result($delete_stmt);
                                $delete_ids = [];
                                while ($delete_row = mysqli_fetch_assoc($delete_result)) {
                                    $delete_ids[] = intval($delete_row['id']);
                                }
                                mysqli_stmt_close($delete_stmt);
                                
                                // Delete old passwords
                                if (count($delete_ids) > 0) {
                                    $placeholders = implode(',', array_fill(0, count($delete_ids), '?'));
                                    $cleanup_query = "DELETE FROM password_history WHERE id IN ($placeholders)";
                                    $cleanup_stmt = mysqli_prepare($con, $cleanup_query);
                                    if ($cleanup_stmt) {
                                        $types = str_repeat('i', count($delete_ids));
                                        mysqli_stmt_bind_param($cleanup_stmt, $types, ...$delete_ids);
                                        mysqli_stmt_execute($cleanup_stmt);
                                        mysqli_stmt_close($cleanup_stmt);
                                    }
                                }
                            }
                        }
                    }
                    ?>
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <link rel="shortcut icon" href="img/avatar.png" />
                        <title>Đổi Mật Khẩu - Figure Shop</title>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css">
                        <link rel="stylesheet" href="css/style.css" type="text/css">
                    </head>
                    <body>
                        <div style="text-align: center; padding: 50px;">
                            <h2 style="color: #28a745;">Mật khẩu đã được cập nhật thành công!</h2>
                            <p>Bạn sẽ được chuyển hướng trong giây lát...</p>
                        </div>
                    </body>
                    </html>
                    <?php
                    ?>
                    <meta http-equiv="refresh" content="3;url=products.php" />
                    <?php
                } else {
                    ?>
                    <script>
                        window.alert("Lỗi khi cập nhật mật khẩu! Vui lòng thử lại sau.");
                    </script>
                    <meta http-equiv="refresh" content="1;url=settings.php" />
                    <?php
                }
                mysqli_stmt_close($update_stmt);
            } else {
                ?>
                <script>
                    window.alert("Lỗi hệ thống! Vui lòng thử lại sau.");
                </script>
                <meta http-equiv="refresh" content="1;url=settings.php" />
                <?php
            }
        }
    } else {
        ?>
        <script>
            window.alert("Lỗi hệ thống! Vui lòng thử lại sau.");
        </script>
        <meta http-equiv="refresh" content="1;url=settings.php" />
        <?php
    }
?>
