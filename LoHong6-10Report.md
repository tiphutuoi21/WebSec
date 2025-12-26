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

## 🆕 CẬP NHẬT BẢO MẬT BỔ SUNG (v2.0)

Các cải thiện nâng cao đã được triển khai dựa trên phân tích chuyên sâu.

---

### 1️⃣ BUFFER OVERFLOWS - CẢI THIỆN

#### ✅ Cải Thiện Mới

| Tính Năng | Mô Tả | Mức Độ |
|-----------|-------|--------|
| Array Depth Validation | Ngăn stack overflow từ nested arrays | 🟡 Medium |
| Safe JSON Decode | Parse JSON với giới hạn kích thước và độ sâu | 🟡 Medium |
| Validate All Inputs | Kiểm tra tất cả POST/GET cùng lúc | 🔴 High |
| Resource Limits | Thiết lập giới hạn memory/execution PHP | 🟡 Medium |

```php
// Validate array depth to prevent stack overflow
public static function validateArrayDepth($array, $maxDepth = 5, $currentDepth = 0) {
    if ($currentDepth > $maxDepth) {
        return false;
    }
    if (is_array($array)) {
        foreach ($array as $value) {
            if (is_array($value)) {
                if (!self::validateArrayDepth($value, $maxDepth, $currentDepth + 1)) {
                    return false;
                }
            }
        }
    }
    return true;
}

// Safe JSON decode with limits
public static function safeJsonDecode($json, $maxLength = 65536, $maxDepth = 10) {
    if (strlen($json) > $maxLength) {
        return ['valid' => false, 'message' => 'JSON too large'];
    }
    $data = json_decode($json, true, $maxDepth);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['valid' => false, 'message' => json_last_error_msg()];
    }
    return ['valid' => true, 'data' => $data];
}

// Validate all POST/GET inputs at once
public static function validateAllInputLengths() {
    $limits = [
        'name' => self::MAX_NAME_LENGTH,
        'email' => self::MAX_EMAIL_LENGTH,
        'password' => self::MAX_PASSWORD_LENGTH,
        // ... more fields
    ];
    $inputs = array_merge($_POST, $_GET);
    foreach ($inputs as $key => $value) {
        if (is_string($value)) {
            $maxLen = $limits[$key] ?? 1000;
            if (strlen($value) > $maxLen) {
                self::logSecurityViolation('buffer_overflow_attempt', "Field: $key");
                return false;
            }
        }
    }
    return true;
}

// Set PHP resource limits
public static function setResourceLimits() {
    @ini_set('memory_limit', '128M');
    @ini_set('max_execution_time', 30);
    @ini_set('max_input_time', 30);
    @ini_set('max_input_vars', 1000);
    @ini_set('max_input_nesting_level', 5);
}
```

---

### 2️⃣ PRIVILEGE ESCALATION - CẢI THIỆN

#### ✅ Cải Thiện Mới

| Tính Năng | Mô Tả | Mức Độ |
|-----------|-------|--------|
| Re-authentication | Yêu cầu xác thực lại cho actions nhạy cảm | 🔴 High |
| IDOR Prevention | Kiểm tra quyền sở hữu resource (horizontal privilege) | 🔴 High |
| Privilege Audit Log | Ghi log tất cả thay đổi quyền | 🟡 Medium |
| Session Enforcement | Bắt buộc verify fingerprint mỗi request | 🔴 High |

```php
// Sensitive actions requiring re-authentication
const SENSITIVE_ACTIONS = [
    'delete_user',
    'change_role',
    'system_settings',
    'export_data',
    'delete_all_orders'
];

// Verify re-authentication for sensitive actions
public static function verifySensitiveAction($con, $action, $password) {
    if (!self::requiresReAuth($action)) {
        return true;
    }
    
    // Check if recently authenticated (within 5 minutes)
    if (isset($_SESSION['last_reauth']) && 
        (time() - $_SESSION['last_reauth']) < 300) {
        return true;
    }
    
    // Require password verification
    $userId = $_SESSION['id'] ?? 0;
    $stmt = mysqli_prepare($con, "SELECT password FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($row && self::verifyPassword($password, $row['password'])) {
        $_SESSION['last_reauth'] = time();
        return true;
    }
    return false;
}

// Horizontal privilege check - prevent IDOR
public static function canAccessResource($con, $resourceType, $resourceId, $userId = null) {
    $userId = $userId ?? ($_SESSION['id'] ?? 0);
    $roleId = $_SESSION['admin_role_id'] ?? self::ROLE_CUSTOMER;
    
    // Admin can access all
    if ($roleId === self::ROLE_ADMIN) {
        return true;
    }
    
    // Check ownership based on resource type
    $ownershipQueries = [
        'order' => "SELECT user_id FROM orders WHERE id = ?",
        'cart' => "SELECT user_id FROM cart_items WHERE id = ?",
        'address' => "SELECT user_id FROM user_addresses WHERE id = ?",
    ];
    
    if (!isset($ownershipQueries[$resourceType])) {
        return false;
    }
    
    $stmt = mysqli_prepare($con, $ownershipQueries[$resourceType]);
    mysqli_stmt_bind_param($stmt, "i", $resourceId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $row && intval($row['user_id']) === intval($userId);
}

// Enforce session fingerprint on every request
public static function enforceSessionIntegrity() {
    if (!isset($_SESSION['id'])) {
        return true; // Not logged in
    }
    
    if (!isset($_SESSION['session_fingerprint'])) {
        $_SESSION['session_fingerprint'] = self::generateSessionFingerprint();
        return true;
    }
    
    if (!self::verifySessionIntegrity()) {
        self::logSecurityViolation('session_hijacking_attempt', "...");
        session_destroy();
        return false;
    }
    return true;
}
```

---

### 3️⃣ DENIAL OF SERVICE - CẢI THIỆN

#### ✅ Cải Thiện Mới

| Tính Năng | Mô Tả | Mức Độ |
|-----------|-------|--------|
| IP Blacklist | Tự động ban IP sau nhiều violations | 🔴 High |
| Auto-blacklist | Tự động ban dựa trên violation count | 🔴 High |
| CAPTCHA Check | Kiểm tra xem có cần CAPTCHA không | 🟡 Medium |
| Query Timeout | Thiết lập timeout cho slow queries | 🟡 Medium |

```php
// IP Blacklist configuration
const BLACKLIST_THRESHOLD = 10;      // Violations before ban
const BLACKLIST_DURATION = 86400;    // 24 hours ban

// Check if IP is blacklisted
public static function isIPBlacklisted($con, $ip = null) {
    $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    
    self::ensureBlacklistTable($con);
    
    $stmt = mysqli_prepare($con, 
        "SELECT id FROM ip_blacklist 
         WHERE ip_address = ? AND expires_at > NOW() AND is_active = 1");
    mysqli_stmt_bind_param($stmt, "s", $ip);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $isBlacklisted = mysqli_num_rows($result) > 0;
    mysqli_stmt_close($stmt);
    
    return $isBlacklisted;
}

// Add IP to blacklist
public static function blacklistIP($con, $ip, $reason, $duration = null) {
    $duration = $duration ?? self::BLACKLIST_DURATION;
    
    self::ensureBlacklistTable($con);
    
    $stmt = mysqli_prepare($con, 
        "INSERT INTO ip_blacklist (ip_address, reason, expires_at, created_at) 
         VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND), NOW())
         ON DUPLICATE KEY UPDATE 
            expires_at = DATE_ADD(NOW(), INTERVAL ? SECOND),
            violation_count = violation_count + 1");
    mysqli_stmt_bind_param($stmt, "ssiss", $ip, $reason, $duration, $duration, $reason);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Auto-blacklist based on violation count
public static function checkAndAutoBlacklist($con, $ip = null) {
    $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    
    // Count recent violations
    $stmt = mysqli_prepare($con, 
        "SELECT COUNT(*) as count FROM security_violations 
         WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    mysqli_stmt_bind_param($stmt, "s", $ip);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (intval($row['count']) >= self::BLACKLIST_THRESHOLD) {
        self::blacklistIP($con, $ip, 'Auto-blacklisted: Too many violations');
        return true;
    }
    return false;
}

// CAPTCHA requirement check
public static function requiresCaptcha($con, $action, $identifier) {
    $limits = ['login' => 3, 'signup' => 2, 'password_reset' => 2];
    
    if (!isset($limits[$action])) return false;
    
    $key = "{$action}_{$identifier}";
    $stmt = mysqli_prepare($con, 
        "SELECT COUNT(*) as count FROM rate_limits 
         WHERE action_key = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    mysqli_stmt_bind_param($stmt, "s", $key);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return intval($row['count']) >= $limits[$action];
}

// Set query timeout for slow query detection
public static function setQueryTimeout($con, $seconds = 5) {
    @mysqli_query($con, "SET SESSION MAX_EXECUTION_TIME = " . ($seconds * 1000));
}
```

#### 📋 Database Tables Mới

```sql
-- IP Blacklist table
CREATE TABLE ip_blacklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL UNIQUE,
    reason VARCHAR(255),
    violation_count INT DEFAULT 1,
    expires_at DATETIME NOT NULL,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip (ip_address),
    INDEX idx_expires (expires_at)
);

-- Security violations table
CREATE TABLE security_violations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    violation_type VARCHAR(100) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip (ip_address),
    INDEX idx_created_at (created_at)
);

-- Privilege audit log table
CREATE TABLE privilege_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    status VARCHAR(50) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action)
);
```

---

### 4️⃣ UNPATCHED DATABASE - CẢI THIỆN

#### ✅ Cải Thiện Mới

| Tính Năng | Mô Tả | Mức Độ |
|-----------|-------|--------|
| Dry-run Mode | Test migration mà không apply | 🟡 Medium |

```php
// Dry-run migration (test without applying)
public static function dryRunMigration($con, $sql) {
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($s) => !empty($s)
    );
    
    $results = [];
    
    foreach ($statements as $statement) {
        if (stripos(trim($statement), 'SELECT') === 0) {
            $explain = @mysqli_query($con, "EXPLAIN $statement");
            $results[] = [
                'statement' => substr($statement, 0, 100) . '...',
                'valid' => $explain !== false,
                'type' => 'SELECT'
            ];
        } else {
            $stmt = @mysqli_prepare($con, $statement);
            $results[] = [
                'statement' => substr($statement, 0, 100) . '...',
                'valid' => $stmt !== false,
                'type' => 'DDL/DML',
                'error' => $stmt === false ? mysqli_error($con) : null
            ];
            if ($stmt) mysqli_stmt_close($stmt);
        }
    }
    
    return $results;
}
```

---

### 5️⃣ UNENCRYPTED DATA - CẢI THIỆN

#### ✅ Cải Thiện Mới

| Tính Năng | Mô Tả | Mức Độ |
|-----------|-------|--------|
| Key Versioning | Hỗ trợ key rotation | 🟡 Medium |
| Auto PII Encrypt | Tự động mã hóa PII fields | 🔴 High |
| Auto PII Decrypt | Tự động giải mã PII khi đọc | 🔴 High |
| Log Sanitization | Loại bỏ sensitive data khỏi logs | 🔴 High |
| Secure Logging | Ghi log an toàn với sanitization | 🟡 Medium |

```php
// Key version for rotation support
const KEY_VERSION_CURRENT = 1;

// PII fields that should always be encrypted
const PII_FIELDS = [
    'users' => ['phone', 'address', 'date_of_birth'],
    'orders' => ['shipping_address', 'billing_address']
];

// Auto-encrypt PII before saving
public static function encryptPIIFields($table, $data) {
    if (!isset(self::PII_FIELDS[$table])) {
        return $data;
    }
    
    $piiFields = self::PII_FIELDS[$table];
    
    foreach ($data as $field => $value) {
        if (in_array($field, $piiFields) && !empty($value)) {
            $data[$field] = self::encryptData($value);
            $data[$field . '_encrypted'] = true;
        }
    }
    
    return $data;
}

// Auto-decrypt PII when reading
public static function decryptPIIFields($table, $data) {
    if (!isset(self::PII_FIELDS[$table])) {
        return $data;
    }
    
    $piiFields = self::PII_FIELDS[$table];
    
    foreach ($data as $field => $value) {
        if (in_array($field, $piiFields) && !empty($value)) {
            $decrypted = self::decryptData($value);
            if ($decrypted !== false) {
                $data[$field] = $decrypted;
            }
        }
    }
    
    return $data;
}

// Sanitize logs to remove sensitive data
public static function sanitizeForLog($data) {
    $sensitivePatterns = [
        '/\b\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}\b/' => '[CARD REDACTED]',
        '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/' => '[EMAIL REDACTED]',
        '/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/' => '[PHONE REDACTED]',
        '/password=[^&\s]+/' => 'password=[REDACTED]',
        '/api[_-]?key[=:]\s*[a-zA-Z0-9]+/i' => 'api_key=[REDACTED]'
    ];
    
    if (is_array($data)) {
        $data = json_encode($data);
    }
    
    foreach ($sensitivePatterns as $pattern => $replacement) {
        $data = preg_replace($pattern, $replacement, $data);
    }
    
    return $data;
}

// Secure logging with sanitization
public static function secureLog($message, $context = []) {
    $sanitizedMessage = self::sanitizeForLog($message);
    $sanitizedContext = self::sanitizeForLog($context);
    
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $logEntry = sprintf(
        "[%s] %s | Context: %s\n",
        date('Y-m-d H:i:s'),
        $sanitizedMessage,
        $sanitizedContext
    );
    
    error_log($logEntry, 3, $logDir . '/app.log');
}
```

---

### 🎯 HÀM KHỞI TẠO BẢO MẬT TỔNG HỢP

```php
/**
 * Initialize all security measures
 * Gọi hàm này ở đầu mỗi request
 */
public static function initialize($con) {
    // Set resource limits
    self::setResourceLimits();
    
    // Validate request size
    self::validateRequestSize();
    
    // Validate all input lengths
    if (!self::validateAllInputLengths()) {
        http_response_code(400);
        die('Invalid input detected');
    }
    
    // Validate array depth for POST data
    if (!self::validateArrayDepth($_POST)) {
        http_response_code(400);
        die('Invalid request structure');
    }
    
    // Check IP blacklist
    if (self::isIPBlacklisted($con)) {
        http_response_code(403);
        die('Access denied');
    }
    
    // Enforce session integrity
    if (!self::enforceSessionIntegrity()) {
        header('Location: login.php?error=session_expired');
        exit();
    }
    
    // Run pending migrations
    self::runPendingMigrations($con);
    
    return true;
}
```

#### 💡 Cách Sử Dụng

```php
// Trong connection.php hoặc đầu mỗi file PHP
require_once 'SecurityEnhancements.php';

$con = mysqli_connect($servername, $username, $password, $database);

// Khởi tạo tất cả biện pháp bảo mật
SecurityEnhancements::initialize($con);
```

---

## 📊 TỔNG KẾT CẬP NHẬT v2.0

| Lỗ Hổng | v1.0 | v2.0 (Mới) |
|---------|------|------------|
| Buffer Overflows | Input limits | + Array depth, JSON safe, All inputs validation |
| Privilege Escalation | RBAC, Fingerprint | + Re-auth, IDOR check, Audit log |
| DoS Attack | Rate limiting | + IP Blacklist, Auto-ban, CAPTCHA check |
| Unpatched Database | Migration system | + Dry-run mode |
| Unencrypted Data | AES-256 + Argon2ID | + Key versioning, Auto PII encrypt, Log sanitize |

### 📁 Files Đã Cập Nhật

| File | Thay Đổi |
|------|----------|
| [SecurityEnhancements.php](SecurityEnhancements.php) | Thêm ~400 dòng code mới với các cải thiện |

### 🗃️ Database Tables Mới

| Table | Mục Đích |
|-------|----------|
| `ip_blacklist` | Lưu danh sách IP bị ban |
| `security_violations` | Ghi log các vi phạm bảo mật |
| `privilege_audit_log` | Audit log cho thay đổi quyền |

---

## 📚 TÀI LIỆU THAM KHẢO

- OWASP Top 10: https://owasp.org/www-project-top-ten/
- PHP Security Best Practices: https://www.php.net/manual/en/security.php
- MySQL Security: https://dev.mysql.com/doc/refman/8.0/en/security.html

---

