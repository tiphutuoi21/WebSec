<?php
/**
 * Auto Verify Users Script
 * This script will verify all users who registered when PHPMailer was not available
 * 
 * SECURITY WARNING: Delete this file after use!
 */

require 'connection.php';

// Check if PHPMailer is available
$vendor_autoload = __DIR__ . '/vendor/autoload.php';
$phpmailer_available = file_exists($vendor_autoload) && class_exists('PHPMailer\PHPMailer\PHPMailer');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Auto Verify Users</title>
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
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #17a2b8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #dc143c;
            color: white;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔐 Auto Verify Users</h1>";

if ($phpmailer_available) {
    echo "<div class='info'>
        <strong>ℹ️ PHPMailer đã được cài đặt</strong>
        <p>Nếu bạn muốn tự động verify tất cả users, hãy xóa PHPMailer hoặc chạy script này để verify users chưa verify.</p>
    </div>";
}

// Get all unverified users
$query = "SELECT id, email, name, created_at FROM users WHERE email_verified = 0";
$result = mysqli_query($con, $query);

if (!$result) {
    echo "<div class='error'>Lỗi: " . mysqli_error($con) . "</div>";
} else {
    $unverified_count = mysqli_num_rows($result);
    
    if ($unverified_count == 0) {
        echo "<div class='success'>
            <strong>✅ Tất cả users đã được verify!</strong>
        </div>";
    } else {
        echo "<div class='info'>
            <strong>Tìm thấy $unverified_count user(s) chưa verify</strong>
        </div>";
        
        // Verify all users
        $verify_query = "UPDATE users SET email_verified = 1 WHERE email_verified = 0";
        $verify_result = mysqli_query($con, $verify_query);
        
        if ($verify_result) {
            $affected_rows = mysqli_affected_rows($con);
            echo "<div class='success'>
                <strong>✅ Đã tự động verify $affected_rows user(s)!</strong>
                <p>Tất cả users giờ đã có thể đăng nhập.</p>
            </div>";
            
            // Show list of verified users
            echo "<h3>Danh sách users đã được verify:</h3>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Email</th><th>Tên</th><th>Ngày tạo</th></tr>";
            
            mysqli_data_seek($result, 0);
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='error'>Lỗi khi verify users: " . mysqli_error($con) . "</div>";
        }
    }
}

echo "<div class='info'>
    <strong>🔒 Bảo mật:</strong>
    <p>File này có thể được sử dụng để verify tất cả users. Sau khi sử dụng, hãy xóa file này ngay lập tức!</p>
</div>";

echo "</div></body></html>";
?>

