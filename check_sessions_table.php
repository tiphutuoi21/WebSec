<?php
/**
 * Check and Create Sessions Table
 * This script will check if the sessions table exists and create it if needed
 */

require 'connection.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Check Sessions Table</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #dc143c;
            border-bottom: 3px solid #ffd700;
            padding-bottom: 10px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #17a2b8;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 Kiểm Tra Bảng Sessions</h1>";

// Check if sessions table exists
$check_table = "SHOW TABLES LIKE 'sessions'";
$result = mysqli_query($con, $check_table);

if (mysqli_num_rows($result) == 0) {
    // Table doesn't exist - create it
    echo "<div class='info'>Bảng 'sessions' chưa tồn tại. Đang tạo bảng...</div>";
    
    $create_table = "CREATE TABLE IF NOT EXISTS sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id VARCHAR(255) NOT NULL,
        user_id INT NOT NULL,
        user_email VARCHAR(255) NOT NULL,
        role_id INT NOT NULL,
        ip_address VARCHAR(45),
        user_agent TEXT,
        session_type VARCHAR(20) DEFAULT 'customer',
        login_time DATETIME NOT NULL,
        last_activity DATETIME NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_session_id (session_id),
        INDEX idx_is_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (mysqli_query($con, $create_table)) {
        echo "<div class='success'>
            <strong>✅ Đã tạo bảng 'sessions' thành công!</strong>
        </div>";
    } else {
        echo "<div class='error'>
            <strong>❌ Lỗi khi tạo bảng:</strong> " . mysqli_error($con) . "
        </div>";
    }
} else {
    echo "<div class='success'>
        <strong>✅ Bảng 'sessions' đã tồn tại!</strong>
    </div>";
    
    // Check table structure
    $describe = "DESCRIBE sessions";
    $desc_result = mysqli_query($con, $describe);
    
    if ($desc_result) {
        echo "<div class='info'>
            <strong>Cấu trúc bảng sessions:</strong>
            <table border='1' cellpadding='5' style='margin-top: 10px; width: 100%; border-collapse: collapse;'>
                <tr style='background: #dc143c; color: white;'>
                    <th>Field</th>
                    <th>Type</th>
                    <th>Null</th>
                    <th>Key</th>
                </tr>";
        
        while ($row = mysqli_fetch_assoc($desc_result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table></div>";
    }
}

// Check if session_audit_log table exists (optional)
$check_audit = "SHOW TABLES LIKE 'session_audit_log'";
$audit_result = mysqli_query($con, $check_audit);

if (mysqli_num_rows($audit_result) == 0) {
    echo "<div class='info'>Bảng 'session_audit_log' chưa tồn tại (không bắt buộc).</div>";
} else {
    echo "<div class='success'>✅ Bảng 'session_audit_log' đã tồn tại!</div>";
}

echo "<div class='info'>
    <strong>📝 Lưu ý:</strong>
    <p>Sau khi kiểm tra xong, bạn có thể xóa file này để bảo mật.</p>
    <p>Nếu bảng sessions đã được tạo, hãy thử đăng nhập lại.</p>
</div>";

echo "</div></body></html>";
?>

