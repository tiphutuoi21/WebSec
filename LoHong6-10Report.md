# 🔒 BÁO CÁO PHÂN TÍCH BẢO MẬT - WEBSEC PROJECT

## 📋 Tổng Quan

Báo cáo này phân tích 5 loại lỗ hổng bảo mật chính và các biện pháp khắc phục đã được triển khai.

---

## 1️⃣ BUFFER OVERFLOWS (Lỗi Tràn Bộ Đệm)

### 🔴 Tác Nhân Gây Lỗi

| Tác Nhân | Mô Tả | File Bị Ảnh Hưởng |
|----------|-------|-------------------|
| Input không giới hạn | Người dùng gửi dữ liệu quá dài | `signup.php`, `login.php` |
| File upload lớn | Upload file vượt quá memory | `admin_add_product.php` |
| Request body lớn | POST data quá lớn | Tất cả form |
| SQL query dài | Query string không giới hạn | `ajax_search.php` |

### ✅ Biện Pháp Khắc Phục Đã Triển Khai

```php
// SecurityEnhancements.php - Input length validation
const MAX_NAME_LENGTH = 100;
const MAX_EMAIL_LENGTH = 255;
const MAX_PASSWORD_LENGTH = 128;
const MAX_ADDRESS_LENGTH = 500;
const MAX_FILE_SIZE = 5242880; // 5MB
const MAX_REQUEST_SIZE = 10485760; // 10MB

// Validate input length
public static function limitInputLength($input, $maxLength, $fieldName) {
    // Remove null bytes (common buffer overflow technique)
    $input = str_replace("\0", '', $input);
    
    if (strlen($input) > $maxLength) {
        return ['valid' => false, 'message' => "Exceeds max length"];
    }
    return ['valid' => true, 'value' => $input];
}

// Validate request size
public static function validateRequestSize() {
    $contentLength = intval($_SERVER['CONTENT_LENGTH'] ?? 0);
    if ($contentLength > self::MAX_REQUEST_SIZE) {
        http_response_code(413);
        die('Request too large');
    }
}
```

### 📁 Files Đã Cập Nhật
- [connection.php](connection.php) - Thêm validateRequestSize()
- [login_submit.php](login_submit.php) - Thêm limitInputLength()
- [SecurityEnhancements.php](SecurityEnhancements.php) - Class mới

---

## 2️⃣ PRIVILEGE ESCALATION (Leo Thang Đặc Quyền)

### 🔴 Tác Nhân Gây Lỗi

| Tác Nhân | Mô Tả | Nguy Cơ |
|----------|-------|---------|
| Session hijacking | Chiếm đoạt session ID | Truy cập trái phép admin |
| Role tampering | Thay đổi role_id trong session | Nâng quyền user lên admin |
| IDOR (Insecure Direct Object Reference) | Truy cập tài nguyên người khác | Xem/sửa đơn hàng người khác |
| Missing authorization checks | Không kiểm tra quyền | Sales manager xóa users |

### ✅ Biện Pháp Khắc Phục Đã Triển Khai

```php
// Permission matrix - Role-Based Access Control (RBAC)
private static $permissions = [
    ROLE_ADMIN => [
        'manage_users' => true,
        'delete_users' => true,
        'manage_products' => true,
        'system_settings' => true
    ],
    ROLE_SALES_MANAGER => [
        'manage_users' => false,
        'delete_users' => false,
        'manage_products' => true,
        'system_settings' => false
    ],
    ROLE_CUSTOMER => [
        // No admin permissions
    ]
];

// Check permission before action
public static function hasPermission($permission) {
    $roleId = intval($_SESSION['admin_role_id'] ?? ROLE_CUSTOMER);
    return self::$permissions[$roleId][$permission] ?? false;
}

// Session fingerprinting to prevent hijacking
public static function generateSessionFingerprint() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'unknown';
    return hash('sha256', $userAgent . $acceptLanguage);
}
```

### 📁 Đã Có Trong Project (SecurityHelper.php)
- `verifyResourceOwnership()` - Kiểm tra quyền sở hữu resource
- `requireAdmin()` - Yêu cầu quyền admin
- `getUserRole()` - Lấy role hiện tại
- Session regeneration on login

---

## 3️⃣ DENIAL OF SERVICE (Tấn Công Từ Chối Dịch Vụ)

### 🔴 Tác Nhân Gây Lỗi

| Tác Nhân | Mô Tả | Hậu Quả |
|----------|-------|---------|
| Brute force login | Thử đăng nhập liên tục | Server quá tải |
| Resource exhaustion | Upload nhiều file lớn | Hết disk/memory |
| Search spam | Gửi nhiều request tìm kiếm | Database overload |
| Session flooding | Tạo nhiều session | Memory exhaustion |

### ✅ Biện Pháp Khắc Phục Đã Triển Khai

```php
// Rate limiting configuration
const RATE_LIMIT_LOGIN = ['attempts' => 5, 'window' => 300];  // 5/5min
const RATE_LIMIT_API = ['attempts' => 100, 'window' => 60];   // 100/min
const RATE_LIMIT_SEARCH = ['attempts' => 30, 'window' => 60]; // 30/min
const RATE_LIMIT_SIGNUP = ['attempts' => 3, 'window' => 3600];// 3/hour

// Advanced rate limiting with database tracking
public static function checkAdvancedRateLimit($con, $action, $identifier) {
    // Count attempts within time window
    // Block if exceeded
    // Log security violation
}

// Exponential backoff for failed attempts
public static function throttleRequest($failedAttempts) {
    if ($failedAttempts > 3) {
        $delay = min(pow(2, $failedAttempts - 3), 30);
        sleep($delay); // Max 30 seconds
    }
}

// Upload limiting per session
public static function validateUploadLimits($file) {
    // Max 20 uploads per hour per user
    // File size validation
}
```

### 📁 Đã Có Trong Project (SecurityHelper.php)
- `checkRateLimit()` - Rate limiting cơ bản
- `recordFailedAttempt()` - Ghi nhận login thất bại
- `clearFailedAttempts()` - Xóa sau login thành công

---

## 4️⃣ UNPATCHED DATABASE (CSDL Không Được Vá)

### 🔴 Tác Nhân Gây Lỗi

| Tác Nhân | Mô Tả | Nguy Cơ |
|----------|-------|---------|
| Missing columns | Thiếu cột mới cần thiết | Lỗi runtime |
| No migration tracking | Không biết DB version nào | Khó upgrade |
| Manual patching | Patch thủ công dễ sai | Inconsistent |
| No rollback | Không thể quay lại | Data loss |

### ✅ Biện Pháp Khắc Phục Đã Triển Khai

```php
// Database migration tracking table
CREATE TABLE db_migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    applied_by VARCHAR(255),
    checksum VARCHAR(64)
);

// Apply migration with version control
public static function applyMigration($con, $version, $sql, $description) {
    // Check if already applied
    // Apply within transaction
    // Record in migrations table
    // Rollback on failure
}

// Auto-run pending migrations on connection
SecurityEnhancements::runPendingMigrations($con);
```

### 📁 Files Đã Cập Nhật
- [connection.php](connection.php) - Auto-run migrations
- [SecurityEnhancements.php](SecurityEnhancements.php) - Migration functions

### 📊 Migrations Được Định Nghĩa
| Version | Description |
|---------|-------------|
| 1.0.0 | Initial security tables |
| 1.0.1 | Add encrypted data support |
| 1.0.2 | Add rate limiting table |

---

## 5️⃣ UNENCRYPTED DATA (Dữ Liệu Không Mã Hóa)

### 🔴 Tác Nhân Gây Lỗi

| Tác Nhân | Mô Tả | Nguy Cơ |
|----------|-------|---------|
| Plaintext passwords | Lưu password dạng text | Account takeover |
| Unencrypted PII | Thông tin cá nhân không mã hóa | Data breach |
| Config in code | Credentials trong source code | Exposure via git |
| No HTTPS | Truyền data không mã hóa | MITM attack |

### ✅ Biện Pháp Khắc Phục Đã Triển Khai

```php
// AES-256-GCM encryption for sensitive data
public static function encryptData($plaintext) {
    $key = hex2bin(self::getEncryptionKey());
    $iv = random_bytes(12);
    $tag = '';
    
    $ciphertext = openssl_encrypt(
        $plaintext,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );
    
    return base64_encode($iv . $tag . $ciphertext);
}

// Password hashing with pepper + Argon2ID
public static function hashPassword($password) {
    $pepper = self::getEncryptionKey();
    $pepperedPassword = hash_hmac('sha256', $password, $pepper);
    return password_hash($pepperedPassword, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
}

// Data masking for display
public static function maskSensitiveData($data, $type) {
    switch ($type) {
        case 'email': return 'jo****@example.com';
        case 'phone': return '090****123';
        case 'card': return '**** **** **** 1234';
    }
}

// Store encrypted data in database
public static function storeEncryptedData($con, $dataType, $refId, $data) {
    $encrypted = self::encryptData($data);
    // INSERT INTO encrypted_data...
}
```

### 📁 Đã Có Trong Project
- **password_hash()** với BCRYPT - [user_registration_script.php](user_registration_script.php)
- **Prepared statements** - Tất cả các file
- **HTTPS headers** - [connection.php](connection.php)

### 🔐 Environment Variables (Khuyến Nghị)
```bash
# .env file (không commit vào git!)
ENCRYPTION_KEY=your-64-char-hex-key
DB_HOST=localhost
DB_USER=root
DB_PASS=your-password
DB_NAME=store
```

---

## 📊 TỔNG KẾT

| Lỗ Hổng | Trạng Thái Trước | Trạng Thái Sau |
|---------|------------------|----------------|
| Buffer Overflows | ⚠️ Chưa có validation | ✅ Input length limits |
| Privilege Escalation | ⚠️ Basic checks | ✅ RBAC + Session fingerprint |
| DoS Attack | ⚠️ Basic rate limiting | ✅ Advanced rate limiting + throttling |
| Unpatched Database | ❌ No versioning | ✅ Migration system |
| Unencrypted Data | ⚠️ Password hashed | ✅ AES-256-GCM + Argon2ID |

---

## 🚀 HƯỚNG DẪN TRIỂN KHAI

### 1. Copy files mới sang htdocs
```cmd
xcopy "d:\lap trinh kiem com\Web\WebSec" "C:\xampp\htdocs\WebSec\" /E /I /Y /Q
```

### 2. Tạo thư mục logs
```cmd
mkdir C:\xampp\htdocs\WebSec\logs
```

### 3. Tạo encryption key
```cmd
php -r "echo bin2hex(random_bytes(32));" > .encryption_key
```

### 4. Cập nhật .env
```
ENCRYPTION_KEY=your-generated-key
DB_HOST=localhost
DB_USER=root
DB_PASS=your-password
DB_NAME=store
```

### 5. Test migrations
Truy cập: http://localhost/WebSec/index.php
Migrations sẽ tự động chạy.

---

## 📚 TÀI LIỆU THAM KHẢO

- OWASP Top 10: https://owasp.org/www-project-top-ten/
- PHP Security Best Practices: https://www.php.net/manual/en/security.php
- MySQL Security: https://dev.mysql.com/doc/refman/8.0/en/security.html

---

*Báo cáo được tạo: 26/12/2025*
*Tác giả: GitHub Copilot Security Analysis*
