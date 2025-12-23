<?php
    require 'connection.php';
    
    echo "<h2>Tạo bảng lịch sử mật khẩu</h2>";
    
    // Check if table exists
    $check_table = "SHOW TABLES LIKE 'password_history'";
    $result = mysqli_query($con, $check_table);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color: orange;'>⚠️ Bảng password_history đã tồn tại.</p>";
    } else {
        // Create password_history table
        $create_table = "CREATE TABLE `password_history` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `password_hash` varchar(255) NOT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
        
        if (mysqli_query($con, $create_table)) {
            echo "<p style='color: green;'>✅ Đã tạo bảng password_history thành công!</p>";
            
            // Insert current passwords into history for existing users
            $users_query = "SELECT id, password FROM users";
            $users_result = mysqli_query($con, $users_query);
            
            $inserted_count = 0;
            while ($user = mysqli_fetch_assoc($users_result)) {
                $insert_history = "INSERT INTO password_history (user_id, password_hash) VALUES (?, ?)";
                $stmt = mysqli_prepare($con, $insert_history);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "is", $user['id'], $user['password']);
                    if (mysqli_stmt_execute($stmt)) {
                        $inserted_count++;
                    }
                    mysqli_stmt_close($stmt);
                }
            }
            
            echo "<p style='color: green;'>✅ Đã thêm {$inserted_count} mật khẩu hiện tại vào lịch sử.</p>";
        } else {
            echo "<p style='color: red;'>❌ Lỗi: " . htmlspecialchars(mysqli_error($con)) . "</p>";
        }
    }
    
    echo "<p><a href='settings.php'>Quay lại trang cài đặt</a></p>";
?>

