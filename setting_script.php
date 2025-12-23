<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    if(!isset($_SESSION['email'])){
        header('location:index.php');
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
    
    // Hash passwords
    $old_password_hash = md5(md5($old_password));
    $new_password_hash = md5(md5($new_password));
    
    // Verify old password using prepared statement
    $password_from_database_query = "SELECT password FROM users WHERE email = ?";
    $stmt = mysqli_prepare($con, $password_from_database_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if ($row && $row['password'] == $old_password_hash) {
            // Check if new password is the same as current password
            if ($new_password_hash == $old_password_hash) {
                ?>
                <script>
                    window.alert("Mật khẩu mới không được trùng với mật khẩu hiện tại!");
                </script>
                <meta http-equiv="refresh" content="1;url=settings.php" />
                <?php
                exit();
            }
            
            // Check password history - prevent reusing old passwords
            // First, ensure password_history table exists
            $check_table = "SHOW TABLES LIKE 'password_history'";
            $table_result = mysqli_query($con, $check_table);
            if (mysqli_num_rows($table_result) == 0) {
                // Create password_history table if it doesn't exist
                $create_table = "CREATE TABLE IF NOT EXISTS `password_history` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `user_id` int(11) NOT NULL,
                    `password_hash` varchar(255) NOT NULL,
                    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `user_id` (`user_id`),
                    KEY `created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
                mysqli_query($con, $create_table);
            }
            
            $user_id = $user['id'];
            $check_history_query = "SELECT COUNT(*) as count FROM password_history WHERE user_id = ? AND password_hash = ?";
            $history_stmt = mysqli_prepare($con, $check_history_query);
            if ($history_stmt) {
                mysqli_stmt_bind_param($history_stmt, "is", $user_id, $new_password_hash);
                mysqli_stmt_execute($history_stmt);
                $history_result = mysqli_stmt_get_result($history_stmt);
                $history_row = mysqli_fetch_assoc($history_result);
                mysqli_stmt_close($history_stmt);
                
                if ($history_row && $history_row['count'] > 0) {
                    ?>
                    <script>
                        window.alert("Mật khẩu mới không được trùng với mật khẩu đã từng sử dụng trước đây!");
                    </script>
                    <meta http-equiv="refresh" content="1;url=settings.php" />
                    <?php
                    exit();
                }
            }
            
            // Update password using prepared statement
            $update_password_query = "UPDATE users SET password = ? WHERE email = ?";
            $update_stmt = mysqli_prepare($con, $update_password_query);
            if ($update_stmt) {
                mysqli_stmt_bind_param($update_stmt, "ss", $new_password_hash, $email);
                if (mysqli_stmt_execute($update_stmt)) {
                    // Save old password to history
                    $user_id = $user['id'];
                    $insert_history_query = "INSERT INTO password_history (user_id, password_hash) VALUES (?, ?)";
                    $history_insert_stmt = mysqli_prepare($con, $insert_history_query);
                    if ($history_insert_stmt) {
                        mysqli_stmt_bind_param($history_insert_stmt, "is", $user_id, $old_password_hash);
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
        } else {
            ?>
            <script>
                window.alert("Mật khẩu cũ không đúng!");
            </script>
            <meta http-equiv="refresh" content="1;url=settings.php" />
            <?php
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