## 📦 Cài Đặt

### Bước 1: Clone Repository

```bash
git clone https://github.com/tiphutuoi21/WebSec.git
cd WebSec
```

### Bước 2: Cài Đặt Dependencies (Nếu sử dụng Composer)

```bash
composer install
```

Hoặc tải thủ công PHPMailer nếu không dùng Composer:
- Download PHPMailer từ: https://github.com/PHPMailer/PHPMailer
- Giải nén vào thư mục `vendor/phpmailer/phpmailer/`

### Bước 3: Cấu Hình Permissions

```bash
# Trên Linux/macOS
chmod 755 -R .
chmod 777 -R img/  # Nếu có upload ảnh sản phẩm
```

## 🗄️ Cấu Hình Database

### Bước 1: Tạo Database

```sql
CREATE DATABASE websec_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Bước 2: Import Database Schema

```bash
mysql -u root -p websec_store < store.sql
```

Hoặc sử dụng phpMyAdmin:
1. Truy cập phpMyAdmin
2. Chọn database `websec_store`
3. Chọn tab "Import"
4. Upload file `store.sql`

### Bước 3: Cấu Hình Kết Nối Database

Chỉnh sửa file `connection.php`:

```php
<?php
$servername = "localhost";
$username = "root";        // Username MySQL của bạn
$password = "";            // Password MySQL của bạn
$database = "websec_store"; // Tên database

$con = mysqli_connect($servername, $username, $password, $database);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
```

### Bước 4: Chạy Migration Scripts (Nếu cần)

```bash
php setup_database.php
php database_migration.php
php session_migration.php
php create_password_history_table.php
```

## 📧 Cấu Hình Email

Chỉnh sửa file `config.php` hoặc `MailHelper.php`:

```php
<?php
// Cấu hình SMTP
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-app-password');
define('MAIL_PORT', 587);
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM', 'your-email@gmail.com');
define('MAIL_FROM_NAME', 'WebSec Store');
?>
```

**Lưu ý**: Nếu dùng Gmail, bạn cần:
1. Bật xác thực 2 bước
2. Tạo App Password tại: https://myaccount.google.com/apppasswords
3. Sử dụng App Password thay vì mật khẩu thường

## 🚀 Chạy Ứng Dụng

### Sử dụng XAMPP/WAMP

1. Copy toàn bộ thư mục dự án vào:
   - XAMPP: `C:\xampp\htdocs\WebSec`
   - WAMP: `C:\wamp64\www\WebSec`

2. Start Apache và MySQL

3. Truy cập:
   - Frontend: `http://localhost/WebSec`
   - Admin: `http://localhost/WebSec/admin_login.php`

### Sử dụng PHP Built-in Server (Development)

```bash
cd WebSec
php -S localhost:8000
```

Truy cập: `http://localhost:8000`

### Sử dụng Docker (Optional)

Tạo file `docker-compose.yml`:

```yaml
version: '3.8'
services:
  web:
    image: php:7.4-apache
    ports:
      - "8080:80"
    volumes:
      - ./:/var/www/html
    depends_on:
      - db
  
  db:
    image: mysql:5.7
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: websec_store
    ports:
      - "3306:3306"
    volumes:
      - ./store.sql:/docker-entrypoint-initdb.d/store.sql
```

Chạy:
```bash
docker-compose up -d
```

## 👤 Tài Khoản Mặc Định

### Admin Account
```
URL: /admin_login.php hoặc /admin310817.php
Username: admin
Password: admin123
```

### Test User Account (Nếu có trong database)
```
Email: user@test.com
Password: user123
```

**⚠️ QUAN TRỌNG**: Đổi mật khẩu admin ngay sau khi đăng nhập lần đầu!

## ✨ Tính Năng Chính

### Người Dùng (User)
- ✅ Đăng ký tài khoản với xác thực email
- ✅ Đăng nhập/Đăng xuất
- ✅ Xem danh sách sản phẩm
- ✅ Tìm kiếm sản phẩm (AJAX)
- ✅ Thêm sản phẩm vào giỏ hàng
- ✅ Quản lý giỏ hàng
- ✅ Đặt hàng và thanh toán
- ✅ Xem lịch sử đơn hàng
- ✅ Cập nhật thông tin cá nhân

### Quản Trị Viên (Admin)
- ✅ Dashboard thống kê
- ✅ Quản lý sản phẩm (CRUD)
- ✅ Quản lý người dùng
- ✅ Quản lý đơn hàng
- ✅ Quản lý khuyến mãi/giảm giá
- ✅ Xem báo cáo doanh thu

### Tính Năng Bảo Mật
- 🔒 Session Management với database
- 🔒 Password hashing (bcrypt/password_hash)
- 🔒 Password history tracking
- 🔒 CSRF Protection
- 🔒 XSS Prevention
- 🔒 SQL Injection Prevention (Prepared Statements)
- 🔒 Email Verification
- 🔒 Access Control (Role-based)
- 🔒 Secure Password Reset
- 🔒 Session Timeout
- 🔒 Brute Force Protection