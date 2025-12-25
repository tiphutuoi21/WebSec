Đây là nội dung file **`README.md`** hoàn chỉnh, được thiết kế theo chuẩn tài liệu kỹ thuật chuyên nghiệp. Bạn có thể copy toàn bộ nội dung dưới đây và lưu vào một file tên là `README.md` trong thư mục dự án của mình.

---

# 🛡️ Web Security Setup Guide: ModSecurity & Database Hardening

Tài liệu này hướng dẫn chi tiết cách thiết lập hệ thống tường lửa ứng dụng web (**WAF**) và bảo mật tầng dữ liệu cho dự án trên môi trường **XAMPP**.

---

## 1. Cài đặt ModSecurity 2 cho Apache

Cài đặt Modsecurity2 bằng cách tải modules tìm từ mạng.
https://www.apachelounge.com/download/additional/
Lựa đúng module hợp bản Apache và hệ điều hành của bạn. Sai thì thua, copy đúng file theo readme của modsec là đc.
Nếu không được thì hãy tìm người đẹp trai nhất Vĩnh Long giúp đỡ. 

### Bước 1: Kích hoạt Module

Mở file cấu hình chính của Apache: `D:\xampp\apache\conf\httpd.conf` và đảm bảo các dòng sau đã được bật (không có dấu `#` ở đầu):

```apache
LoadModule unique_id_module modules/mod_unique_id.so
LoadModule security2_module modules/mod_security2.so

```

### Bước 2: Cấu hình nạp Rule

Thêm đoạn mã sau vào cuối file `httpd.conf` để Apache nạp các quy tắc bảo mật:

```apache
<IfModule security2_module>
    # Cấu hình cơ bản của ModSecurity
    Include conf/extra/modsecurity.conf
    
    # Cấu hình bộ luật OWASP CRS 4
    Include conf/extra/owasp-crs/crs-setup.conf
    Include conf/extra/owasp-crs/rules/*.conf
</IfModule>

```

---

## 2. Thiết lập OWASP Core Rule Set (CRS) v4.x

### Bước 1: Cài đặt bộ luật

1. Tải bộ luật từ GitHub OWASP CRS.
2. Giải nén vào thư mục: `D:\xampp\apache\conf\extra\owasp-crs\`.
3. Đổi tên file `crs-setup.conf.example` thành `crs-setup.conf`.

### Bước 2: Chỉnh mức độ bảo vệ (Paranoia Level 2)

Mở file `crs-setup.conf`, tìm đến khối lệnh `id:900000` và cấu hình như sau:

```apache
SecAction \
    "id:900000,\
    phase:1,\
    pass,\
    t:none,\
    nolog,\
    tag:'OWASP_CRS',\
    ver:'OWASP_CRS/4.x',\
    setvar:tx.blocking_paranoia_level=2"

```

> **Lưu ý:** Mức 2 (PL2) là lựa chọn tối ưu để chặn các cuộc tấn công nâng cao mà không gây lỗi "nhầm" cho các chức năng web thông thường.

---

## 3. Quy tắc chặn Path Traversal (Truy cập thư mục)

Mở file `crs-setup.conf`, tìm đến khối lệnh `id:900240` và cấu hình như sau:

SecAction \
    "id:900240,\
    phase:1,\
    pass,\
    t:none,\
    nolog,\
    tag:'OWASP_CRS',\
    ver:'OWASP_CRS/4.22.0-dev',\
    setvar:'tx.restricted_extensions=.ani/ .asa/ .asax/ .ascx/ .back/ .backup/ .bak/ .bck/ .bk/ .bkp/ .bat/ .cdx/ .cer/ .cfg/ .cmd/ .cnf/ .com/ .compositefont/ .config/ .conf/ .copy/ .crt/ .cs/ .csproj/ .csr/ .dat/ .db/ .dbf/ .dist/ .dll/ .dos/ .dpkg-dist/ .drv/ .gadget/ .hta/ .htr/ .htw/ .ida/ .idc/ .idq/ .inc/ .inf/ .ini/ .jse/ .key/ .licx/ .lnk/ .log/ .mdb/ .msc/ .ocx/ .old/ .pass/ .pdb/ .pfx/ .pif/ .pem/ .pol/ .prf/ .printer/ .pwd/ .rdb/ .rdp/ .reg/ .resources/ .resx/ .sav/ .save/ .scr/ .sct/ .sh/ .shs/ .sql/ .sqlite/ .sqlite3/ .swp/ .sys/ .temp/ .tlb/ .tmp/ .vb/ .vbe/ .vbs/ .vbproj/ .vsdisco/ .vxd/ .webinfo/ .ws/ .wsc/ .wsf/ .wsh/ .xsd/ .xsx/'"


## 4. Bảo mật tầng Dữ liệu (Database Hardening)

Bước này nhằm giảm thiểu rủi ro về việc mysql bật tắt 10 lần thì 11 lần lỗi khi sử dụng

Thay thế user `root` bằng một user có đặc quyền hạn chế để giảm thiểu thiệt hại nếu mã nguồn bị xâm nhập.

### Bước 1: Tạo User 'mychos' trong MariaDB

Mở **XAMPP Shell** và thực hiện:

```sql
-- Đăng nhập quyền root
mysql -u root

-- Tạo user và cấp quyền cho cả localhost và 127.0.0.1
CREATE USER 'mychos'@'localhost' IDENTIFIED BY 'MatKhau_Cua_Ban';
CREATE USER 'mychos'@'127.0.0.1' IDENTIFIED BY 'MatKhau_Cua_Ban';

-- Chỉ cấp quyền trên Database cụ thể (database 'store')
GRANT ALL PRIVILEGES ON store.* TO 'mychos'@'localhost';
GRANT ALL PRIVILEGES ON store.* TO 'mychos'@'127.0.0.1';

FLUSH PRIVILEGES;

```

### Bước 2: Cấu hình PHP

Cập nhật file kết nối (ví dụ: `connection.php`) với thông tin mới:

```php
// Thay đổi Host, User, Pass theo thông tin bạn đã tạo
$servername = "127.0.0.1"; // Khuyên dùng IP này thay cho 'localhost' để tránh lỗi quyền
$username   = "mychos"; // Tên user bạn vừa tạo (Ví dụ: dev_user)
$password   = "MatKhau_Cua_Ban"; // Mật khẩu bạn đã đặt
$dbname     = "store";        // Tên database vẫn giữ nguyên là 'store'

$con = mysqli_connect($servername, $username, $password, $dbname) or die(mysqli_error($con));

```

---

## 5. Danh sách các bài Test (Penetration Testing)

Sử dụng các chuỗi sau trên trình duyệt để kiểm tra tính hiệu quả của hệ thống:

| Mục tiêu | Payload mẫu | Kết quả mong đợi |
| --- | --- | --- |
| **SQL Injection** | `products.php?id=1' OR '1'='1' --` | **403 Forbidden** |
| **Path Traversal** | `products.php?file=../../../../windows/win.ini` | **403 Forbidden** |
| **XSS** | `search.php?q=<script>alert('XSS')</script>` | **403 Forbidden** |
| **PHP Wrapper** | `products.php?page=php://filter/resource=config.php` | **403 Forbidden** |

---

## 6. Theo dõi Nhật ký (Logs)

Mọi cuộc tấn công bị chặn sẽ được ghi lại tại:

* **Log bảo mật:** `D:\xampp\apache\logs\modsec_audit.log`
* **Log lỗi Apache:** `D:\xampp\apache\logs\error.log`

---

> **Lưu ý cuối cùng:** Luôn nhấn **STOP** trong XAMPP Control Panel trước khi tắt máy để tránh lỗi hỏng dữ liệu (**LSN in the future**).

---

