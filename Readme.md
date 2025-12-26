# 🛒 WebSec - Website Bán Hàng Figure

Website thương mại điện tử bán figure được xây dựng bằng PHP và MySQL, tích hợp các tính năng bảo mật nâng cao.

---

## 📋 Yêu Cầu Hệ Thống

| Thành phần | Phiên bản yêu cầu |
|------------|-------------------|
| XAMPP | 7.4 trở lên (khuyến nghị 8.x) |
| PHP | 7.4 - 8.x |
| MySQL/MariaDB | 5.7 trở lên |
| Trình duyệt | Chrome, Firefox, Edge (phiên bản mới nhất) |

---

## 🚀 Hướng Dẫn Cài Đặt Trên Windows

### Bước 1: Cài Đặt XAMPP

1. **Tải XAMPP** từ: https://www.apachefriends.org/download.html
   - Chọn phiên bản **PHP 8.x** (khuyến nghị)
   
2. **Cài đặt XAMPP**:
   - Chạy file installer đã tải về
   - Chọn đường dẫn cài đặt (mặc định: `C:\xampp`)
   - Tick chọn các thành phần: **Apache**, **MySQL**, **PHP**, **phpMyAdmin**
   - Hoàn tất cài đặt

3. **Khởi động XAMPP**:
   - Mở **XAMPP Control Panel** (tìm trong Start Menu)
   - Click **Start** cho **Apache**
   - Click **Start** cho **MySQL**
   - Đảm bảo cả hai hiển thị màu xanh (running)

---

### Bước 2: Tải Source Code

#### Cách 1: Clone bằng Git (Khuyến nghị)

```cmd
cd C:\xampp\htdocs
git clone https://github.com/tiphutuoi21/WebSec.git
```

#### Cách 2: Tải ZIP

1. Truy cập: https://github.com/tiphutuoi21/WebSec
2. Click nút **Code** → **Download ZIP**
3. Giải nén file ZIP vào `C:\xampp\htdocs\WebSec`

**📁 Cấu trúc thư mục đúng:**
```
C:\xampp\htdocs\
└── WebSec\
    ├── index.php
    ├── connection.php
    ├── store.sql
    ├── bootstrap\
    ├── css\
    ├── img\
    └── ...
```

---

### Bước 3: Cài Đặt PHPMailer

PHPMailer được sử dụng để gửi email xác thực và đặt lại mật khẩu.

#### Cách 1: Sử dụng Composer (Nếu đã cài Composer)

```cmd
cd C:\xampp\htdocs\WebSec
composer install
```

#### Cách 2: Tải thủ công (Không cần Composer)

1. Tải PHPMailer từ: https://github.com/PHPMailer/PHPMailer/releases
2. Giải nén và copy thư mục vào:
   ```
   C:\xampp\htdocs\WebSec\vendor\phpmailer\phpmailer\
   ```
3. Đảm bảo cấu trúc thư mục:
   ```
   vendor\
   └── phpmailer\
       └── phpmailer\
           └── src\
               ├── PHPMailer.php
               ├── SMTP.php
               └── Exception.php
   ```

---

### Bước 4: Tạo Database

#### Cách 1: Sử dụng phpMyAdmin (Giao diện đồ họa - Khuyến nghị)

1. Mở trình duyệt, truy cập: **http://localhost/phpmyadmin**

2. **Tạo database mới**:
   - Click **New** ở menu bên trái
   - Nhập tên database: `store`
   - Chọn Collation: `utf8mb4_unicode_ci`
   - Click **Create**

3. **Import dữ liệu**:
   - Chọn database `store` vừa tạo ở menu bên trái
   - Click tab **Import** ở menu trên
   - Click **Choose File** → Chọn file `store.sql` từ thư mục WebSec
   - Scroll xuống dưới, click **Import**
   - Đợi thông báo "Import has been successfully finished"

#### Cách 2: Sử dụng Command Line

1. Mở **Command Prompt** (Run as Administrator)

2. Chạy các lệnh sau:

```cmd
cd C:\xampp\mysql\bin

REM Đăng nhập MySQL (nhập password nếu có, hoặc Enter nếu không có password)
mysql -u root -p

REM Trong MySQL shell, chạy:
CREATE DATABASE store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE store;
SOURCE C:/xampp/htdocs/WebSec/store.sql;
EXIT;
```

---

### Bước 5: Cấu Hình Kết Nối Database

1. Mở file `C:\xampp\htdocs\WebSec\connection.php` bằng Notepad hoặc VS Code

2. Chỉnh sửa thông tin kết nối phù hợp với cài đặt MySQL của bạn:

```php
<?php
$servername = "localhost";
$username = "root";           // Username MySQL (mặc định là root)
$password = "";               // Password MySQL (mặc định để trống)
$database = "store";          // Tên database

$con = mysqli_connect($servername, $username, $password, $database);

if (!$con) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

mysqli_set_charset($con, "utf8mb4");
?>
```

**⚠️ Lưu ý về Password MySQL:**
- Nếu bạn đặt password cho MySQL khi cài XAMPP, hãy điền vào biến `$password`
- Nếu không đặt password (mặc định), để trống: `$password = "";`

---

### Bước 6: Cấu Hình Email (Tùy chọn)

Nếu bạn muốn sử dụng tính năng gửi email (xác thực tài khoản, quên mật khẩu):

1. Tạo file `.env` trong thư mục WebSec với nội dung:

```env
# Cấu hình SMTP (Ví dụ dùng Gmail)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM=your-email@gmail.com
MAIL_FROM_NAME=WebSec Store
```

2. **Đối với Gmail**, bạn cần tạo App Password:
   - Truy cập: https://myaccount.google.com/security
   - Bật **Xác minh 2 bước** (2-Step Verification)
   - Vào: https://myaccount.google.com/apppasswords
   - Tạo App Password cho "Mail" → Windows Computer
   - Copy password 16 ký tự và dán vào `MAIL_PASSWORD`

---

### Bước 7: Chạy Website

1. **Đảm bảo XAMPP đang chạy**:
   - Apache: ✅ Running (màu xanh)
   - MySQL: ✅ Running (màu xanh)

2. **Mở trình duyệt và truy cập**:

   | Trang | URL |
   |-------|-----|
   | 🏠 Trang chủ | http://localhost/WebSec/ |
   | 🔐 Đăng nhập | http://localhost/WebSec/login.php |
   | 📝 Đăng ký | http://localhost/WebSec/signup.php |
   | 👤 Admin Login | http://localhost/WebSec/admin_login.php |
   | 🔧 Admin Panel | http://localhost/WebSec/admin310817.php |

---

## 👤 Tài Khoản Mặc Định

### Tài khoản Admin

| Thông tin | Giá trị |
|-----------|---------|
| URL | http://localhost/WebSec/admin_login.php |
| Username | `admin` |
| Password | `admin123` |

### Tạo Tài Khoản User Mới

1. Truy cập: http://localhost/WebSec/signup.php
2. Điền thông tin đăng ký
3. Xác thực email (nếu đã cấu hình SMTP)
4. Đăng nhập tại: http://localhost/WebSec/login.php

**⚠️ QUAN TRỌNG**: Hãy đổi mật khẩu admin ngay sau khi đăng nhập lần đầu!

---

## 🛠️ Xử Lý Lỗi Thường Gặp

### ❌ Lỗi: "Connection failed" hoặc "Access denied"

**Nguyên nhân**: Sai thông tin kết nối database

**Giải pháp**:
1. Kiểm tra MySQL đã chạy trong XAMPP Control Panel
2. Kiểm tra lại username/password trong `connection.php`
3. Đảm bảo database `store` đã được tạo

---

### ❌ Lỗi: "Table doesn't exist"

**Nguyên nhân**: Chưa import file `store.sql`

**Giải pháp**:
1. Vào phpMyAdmin: http://localhost/phpmyadmin
2. Chọn database `store`
3. Tab Import → Chọn file `store.sql` → Import

---

### ❌ Lỗi: Port 80 hoặc 3306 bị chiếm

**Nguyên nhân**: Có ứng dụng khác đang sử dụng port

**Giải pháp cho Port 80 (Apache)**:
- Tắt Skype, VMware, hoặc IIS nếu đang chạy
- Hoặc đổi port Apache:
  1. Mở XAMPP → Click **Config** bên cạnh Apache → Chọn **httpd.conf**
  2. Tìm `Listen 80` và đổi thành `Listen 8080`
  3. Lưu file và restart Apache
  4. Truy cập: http://localhost:8080/WebSec/

**Giải pháp cho Port 3306 (MySQL)**:
- Tắt MySQL Workbench hoặc các MySQL server khác đang chạy

---

### ❌ Lỗi: Không gửi được email

**Nguyên nhân**: Chưa cấu hình SMTP hoặc sai thông tin

**Giải pháp**:
1. Kiểm tra file `.env` đã tạo đúng
2. Đảm bảo đã bật 2FA và tạo App Password (với Gmail)
3. Kiểm tra firewall không chặn port 587

---

### ❌ Lỗi: Trang trắng hoặc lỗi 500

**Nguyên nhân**: Lỗi PHP hoặc thiếu extension

**Giải pháp**:
1. Kiểm tra error log: `C:\xampp\apache\logs\error.log`
2. Bật hiển thị lỗi trong `php.ini`:
   - Mở XAMPP → Apache → Config → php.ini
   - Tìm `display_errors = Off` → Đổi thành `display_errors = On`
   - Restart Apache

---

## 📁 Cấu Trúc Thư Mục Dự Án

```
WebSec/
├── 📄 index.php              # Trang chủ
├── 📄 connection.php         # Kết nối database
├── 📄 config.php             # Cấu hình chung
├── 📄 store.sql              # Database schema
├── 📄 .env                   # Biến môi trường (tự tạo)
│
├── 📁 bootstrap/             # Bootstrap CSS/JS
├── 📁 css/                   # Custom CSS
├── 📁 img/                   # Hình ảnh
│   └── products/             # Hình sản phẩm
├── 📁 vendor/                # PHPMailer & dependencies
│
├── 🔐 Trang User
│   ├── login.php             # Đăng nhập
│   ├── signup.php            # Đăng ký
│   ├── products.php          # Danh sách sản phẩm
│   ├── product.php           # Chi tiết sản phẩm
│   ├── cart.php              # Giỏ hàng
│   ├── checkout.php          # Thanh toán
│   └── settings.php          # Cài đặt tài khoản
│
├── 🔧 Trang Admin
│   ├── admin_login.php       # Đăng nhập admin
│   ├── admin_dashboard.php   # Dashboard
│   ├── admin_manage_*.php    # Quản lý sản phẩm/user/orders
│   └── admin310817.php       # Admin panel chính
│
└── 🛡️ Security
    ├── SecurityEnhancements.php  # Bảo mật nâng cao
    ├── SecurityHelper.php        # Helper functions
    └── SessionManager.php        # Quản lý session
```

