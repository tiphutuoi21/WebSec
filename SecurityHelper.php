<?php
/**
 * Security Helper Class
 * Provides functions for CSRF protection, XSS prevention, input validation, and session security
 */

require_once 'SessionManager.php';

class SecurityHelper {
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Get CSRF token field for forms
     */
    public static function getCSRFField() {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::generateCSRFToken(), ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * Escape output to prevent XSS
     * Used for displaying user data in HTML
     */
    public static function escape($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize user input
     * Removes potentially dangerous characters
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        // Remove null bytes
        $data = str_replace("\0", '', $data);
        
        // Trim whitespace
        $data = trim($data);
        
        return $data;
    }
    
    /**
     * Validate email
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Get safe integer from GET/POST
     */
    public static function getInt($key, $source = 'GET', $default = 0) {
        $source = strtoupper($source);
        $value = ($source === 'POST') ? ($_POST[$key] ?? $default) : ($_GET[$key] ?? $default);
        return intval($value);
    }
    
    /**
     * Get safe string from GET/POST
     */
    public static function getString($key, $source = 'GET', $default = '') {
        $source = strtoupper($source);
        $value = ($source === 'POST') ? ($_POST[$key] ?? $default) : ($_GET[$key] ?? $default);
        return self::sanitizeInput($value);
    }

    /**
     * Validate product name (letters, numbers, spaces, apostrophes)
     */
    public static function validateProductName($name) {
        $name = trim((string)$name);
        if ($name === '' || !preg_match('/^[a-zA-Z0-9\s\']+$/', $name)) {
            return ['valid' => false, 'message' => 'Tên sản phẩm không hợp lệ.'];
        }
        return ['valid' => true, 'value' => $name];
    }

    /**
     * Validate product price (positive number with up to 2 decimals)
     */
    public static function validateProductPrice($price) {
        $price = trim((string)$price);
        if ($price === '' || !preg_match('/^[0-9]+(\.[0-9]{1,2})?$/', $price)) {
            return ['valid' => false, 'message' => 'Giá phải là số hợp lệ.'];
        }
        $price_value = floatval($price);
        if ($price_value <= 0) {
            return ['valid' => false, 'message' => 'Giá phải lớn hơn 0.'];
        }
        return ['valid' => true, 'value' => $price_value];
    }

    /**
     * Validate stock quantity (non-negative integer)
     */
    public static function validateStockQuantity($quantity) {
        $quantity = trim((string)$quantity);
        if ($quantity === '' || !preg_match('/^[0-9]+$/', $quantity)) {
            return ['valid' => false, 'message' => 'Số lượng phải là số nguyên không âm.'];
        }
        $qty_value = intval($quantity);
        if ($qty_value < 0) {
            return ['valid' => false, 'message' => 'Số lượng không thể âm.'];
        }
        return ['valid' => true, 'value' => $qty_value];
    }

    /**
     * Validate product description (basic safe characters)
     */
    public static function validateProductDescription($description) {
        $description = trim((string)$description);
        if ($description === '') {
            return ['valid' => true, 'value' => ''];
        }
        if (!preg_match('/^[a-zA-Z0-9\s\',\-\.]*$/', $description)) {
            return ['valid' => false, 'message' => 'Mô tả chứa ký tự không hợp lệ.'];
        }
        return ['valid' => true, 'value' => $description];
    }

    /**
     * Normalize product image path (accepts basename or full path)
     */
    public static function normalizeProductImagePath($pathOrName) {
        if (empty($pathOrName)) {
            return null;
        }
        // If already points to an existing file, keep it.
        if (file_exists($pathOrName)) {
            return $pathOrName;
        }
        // Otherwise assume it is a basename stored in DB.
        $relative = 'img/products/' . ltrim($pathOrName, '/\\');
        return $relative;
    }

    /**
     * Validate and store uploaded image with strict checks
     */
    public static function validateImageUpload($file, $destDir = 'img/products/', $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif', 'webp'], $maxSizeBytes = 5242880, $required = false) {
        if (!isset($file) || !is_array($file)) {
            return $required ? ['valid' => false, 'message' => 'Tệp tải lên không hợp lệ.'] : ['valid' => true, 'path' => null, 'basename' => null, 'web_path' => null];
        }

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return $required ? ['valid' => false, 'message' => 'Vui lòng tải lên hình ảnh.'] : ['valid' => true, 'path' => null, 'basename' => null, 'web_path' => null];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'Lỗi tải lên tệp (mã ' . intval($file['error']) . ').'];
        }

        if ($file['size'] <= 0 || $file['size'] > $maxSizeBytes) {
            return ['valid' => false, 'message' => 'Kích thước tệp không hợp lệ hoặc vượt quá giới hạn.'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            return ['valid' => false, 'message' => 'Định dạng tệp không được phép.'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = $finfo ? finfo_file($finfo, $file['tmp_name']) : false;
        if ($finfo) {
            finfo_close($finfo);
        }

        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if ($mime_type === false || !in_array($mime_type, $allowed_mimes, true)) {
            return ['valid' => false, 'message' => 'Tệp phải là hình ảnh hợp lệ.'];
        }

        // Verify image structure
        if (@getimagesize($file['tmp_name']) === false) {
            return ['valid' => false, 'message' => 'Không thể đọc dữ liệu hình ảnh.'];
        }

        $normalizedDir = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR;
        if (!is_dir($normalizedDir)) {
            mkdir($normalizedDir, 0755, true);
        }

        $safeName = 'prod_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $targetPath = $normalizedDir . $safeName;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['valid' => false, 'message' => 'Không thể lưu tệp tải lên.'];
        }

        $webPath = str_replace(['\\', DIRECTORY_SEPARATOR], '/', rtrim($destDir, '/\\')) . '/' . $safeName;

        return [
            'valid' => true,
            'path' => $targetPath,
            'basename' => $safeName,
            'web_path' => $webPath,
            'mime' => $mime_type
        ];
    }

    /**
     * Build a client identifier for rate limiting fallbacks
     */
    public static function getClientIdentifier($suffix = '') {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown_ip';
        $session = session_id() ?: 'no_session';
        $identifier = $ip . '_' . $session;
        if ($suffix !== '') {
            $identifier = $suffix . '_' . $identifier;
        }
        return $identifier;
    }

    /**
     * Generate UUID v7 (time-ordered) string
     */
    public static function generateUuidV7() {
        $time = microtime(true);
        $seconds = (int) $time;
        $micro = (int) (($time - $seconds) * 1_000_000);

        // 60-bit timestamp: seconds since Unix epoch * 1e6 + micro part
        $unixTimeMicro = ($seconds * 1_000_000) + $micro;

        // Generate 74 bits of randomness split into needed parts
        $randomBytes = random_bytes(10);
        $rand1 = unpack('n', substr($randomBytes, 0, 2))[1]; // 16 bits
        $rand2 = unpack('n', substr($randomBytes, 2, 2))[1]; // 16 bits
        $rand3 = unpack('N', substr($randomBytes, 4, 4))[1]; // 32 bits
        $rand4 = unpack('n', substr($randomBytes, 8, 2))[1]; // 16 bits

        // Timestamp high/mid/low parts
        $timeHigh = $unixTimeMicro & 0xFFFFFFFF;
        $timeMid = ($unixTimeMicro >> 32) & 0xFFFF;
        $timeLow = ($unixTimeMicro >> 48) & 0x0FFF; // 12 bits

        // Set version (7)
        $timeLow |= 0x7000;

        // Set variant bits in rand2 (10xx xxxx ...)
        $rand2 = ($rand2 & 0x3FFF) | 0x8000;

        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%08x%04x',
            $timeLow,
            $timeMid,
            ($timeHigh >> 16) & 0xFFFF,
            $timeHigh & 0xFFFF,
            $rand2,
            $rand3,
            $rand4
        );
    }

    /**
     * Ensure orders table has order_uid column
     */
    public static function ensureOrderUidColumn($con) {
        $check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'order_uid'";
        $res = @mysqli_query($con, $check);
        if ($res && mysqli_num_rows($res) === 0) {
            @mysqli_query($con, "ALTER TABLE orders ADD COLUMN order_uid CHAR(36) NOT NULL UNIQUE AFTER id");
            @mysqli_query($con, "CREATE UNIQUE INDEX idx_orders_order_uid ON orders(order_uid)");
        }
        if ($res) {
            mysqli_free_result($res);
        }
    }

    /**
     * Ensure users table has user_uid column
     */
    public static function ensureUserUidColumn($con) {
        $check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'user_uid'";
        $res = @mysqli_query($con, $check);
        if ($res && mysqli_num_rows($res) === 0) {
            @mysqli_query($con, "ALTER TABLE users ADD COLUMN user_uid CHAR(36) NOT NULL UNIQUE AFTER id");
            @mysqli_query($con, "CREATE UNIQUE INDEX idx_users_user_uid ON users(user_uid)");
        }
        if ($res) {
            mysqli_free_result($res);
        }
    }

    /**
     * Ensure admins table has admin_uid column
     */
    public static function ensureAdminUidColumn($con) {
        $check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admins' AND COLUMN_NAME = 'admin_uid'";
        $res = @mysqli_query($con, $check);
        if ($res && mysqli_num_rows($res) === 0) {
            @mysqli_query($con, "ALTER TABLE admins ADD COLUMN admin_uid CHAR(36) NOT NULL UNIQUE AFTER id");
            @mysqli_query($con, "CREATE UNIQUE INDEX idx_admins_admin_uid ON admins(admin_uid)");
        }
        if ($res) {
            mysqli_free_result($res);
        }
    }

    /**
     * Ensure a user has a UUID value (returns the UUID)
     */
    public static function ensureUserUuid($con, $user_id) {
        self::ensureUserUidColumn($con);
        $user_id = intval($user_id);
        $uuid = null;

        $stmt = mysqli_prepare($con, "SELECT user_uid FROM users WHERE id = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($res)) {
                $uuid = $row['user_uid'] ?? null;
            }
            mysqli_stmt_close($stmt);
        }

        if (empty($uuid)) {
            $uuid = self::generateUuidV7();
            $update = mysqli_prepare($con, "UPDATE users SET user_uid = ? WHERE id = ? LIMIT 1");
            if ($update) {
                mysqli_stmt_bind_param($update, "si", $uuid, $user_id);
                mysqli_stmt_execute($update);
                mysqli_stmt_close($update);
            }
        }

        return $uuid;
    }

    /**
     * Ensure an admin has a UUID value (returns the UUID)
     */
    public static function ensureAdminUuid($con, $admin_id) {
        self::ensureAdminUidColumn($con);
        $admin_id = intval($admin_id);
        $uuid = null;

        $stmt = mysqli_prepare($con, "SELECT admin_uid FROM admins WHERE id = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $admin_id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($res)) {
                $uuid = $row['admin_uid'] ?? null;
            }
            mysqli_stmt_close($stmt);
        }

        if (empty($uuid)) {
            $uuid = self::generateUuidV7();
            $update = mysqli_prepare($con, "UPDATE admins SET admin_uid = ? WHERE id = ? LIMIT 1");
            if ($update) {
                mysqli_stmt_bind_param($update, "si", $uuid, $admin_id);
                mysqli_stmt_execute($update);
                mysqli_stmt_close($update);
            }
        }

        return $uuid;
    }
    
    /**
     * Validate password strength
     * Requirements:
     * - Minimum 8 characters (ideally 12-15+)
     * - Must contain uppercase (A-Z)
     * - Must contain lowercase (a-z)
     * - Must contain numbers (0-9)
     * - Must contain special characters (!, @, #, $, %, etc.)
     * - Cannot contain personal information (name, email, phone, address)
     * - Cannot be common words or sequences (123456, 113, 115, etc.)
     */
    public static function isStrongPassword($password, $userData = []) {
        // Minimum 8 characters
        if (strlen($password) < 8) {
            return ['valid' => false, 'message' => 'Mật khẩu phải có tối thiểu 8 ký tự (lý tưởng 12-15 ký tự)'];
        }
        
        // Must contain uppercase letters
        if (!preg_match('/[A-Z]/', $password)) {
            return ['valid' => false, 'message' => 'Mật khẩu phải chứa ít nhất một chữ cái viết hoa (A-Z)'];
        }
        
        // Must contain lowercase letters
        if (!preg_match('/[a-z]/', $password)) {
            return ['valid' => false, 'message' => 'Mật khẩu phải chứa ít nhất một chữ cái viết thường (a-z)'];
        }
        
        // Must contain numbers
        if (!preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'message' => 'Mật khẩu phải chứa ít nhất một số (0-9)'];
        }
        
        // Must contain special characters
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
            return ['valid' => false, 'message' => 'Mật khẩu phải chứa ít nhất một ký tự đặc biệt (!, @, #, $, %, v.v.)'];
        }
        
        // Check for personal information if provided
        if (!empty($userData)) {
            $name = isset($userData['name']) ? strtolower($userData['name']) : '';
            $email = isset($userData['email']) ? strtolower(explode('@', $userData['email'])[0]) : '';
            $contact = isset($userData['contact']) ? $userData['contact'] : '';
            $city = isset($userData['city']) ? strtolower($userData['city']) : '';
            $address = isset($userData['address']) ? strtolower($userData['address']) : '';
            
            $password_lower = strtolower($password);
            
            // Check name - only match complete words or significant parts
            if (!empty($name)) {
                $name_parts = explode(' ', $name);
                foreach ($name_parts as $part) {
                    $part_lower = strtolower(trim($part));
                    if (strlen($part_lower) >= 4) {
                        // For 4+ character names, check substring match
                        if (strpos($password_lower, $part_lower) !== false) {
                            return ['valid' => false, 'message' => 'Mật khẩu không được chứa tên của bạn'];
                        }
                    } elseif (strlen($part_lower) == 3) {
                        // For 3-character names, check as whole word only to avoid false positives
                        $name_pattern = '/\b' . preg_quote($part_lower, '/') . '\b/i';
                        if (preg_match($name_pattern, $password_lower)) {
                            return ['valid' => false, 'message' => 'Mật khẩu không được chứa tên của bạn'];
                        }
                    }
                }
            }
            
            // Check email username
            if (!empty($email) && strlen($email) >= 3 && strpos($password_lower, $email) !== false) {
                return ['valid' => false, 'message' => 'Mật khẩu không được chứa email của bạn'];
            }
            
            // Check phone number
            if (!empty($contact) && strlen($contact) >= 3) {
                // Remove common separators
                $contact_clean = preg_replace('/[\s\-\(\)]/', '', $contact);
                if (strlen($contact_clean) >= 3 && strpos($password, $contact_clean) !== false) {
                    return ['valid' => false, 'message' => 'Mật khẩu không được chứa số điện thoại của bạn'];
                }
            }
            
            // Check city - only match if city is a complete word or significant part
            if (!empty($city) && strlen($city) >= 4) {
                // Only check if city name is 4+ characters to avoid false positives
                if (strpos($password_lower, $city) !== false) {
                    return ['valid' => false, 'message' => 'Mật khẩu không được chứa tên thành phố của bạn'];
                }
            } elseif (!empty($city) && strlen($city) == 3) {
                // For 3-character city names, check as whole word only (with word boundaries)
                // This prevents false positives like "Nhu" matching in "Nhu123@@"
                $city_pattern = '/\b' . preg_quote($city, '/') . '\b/i';
                if (preg_match($city_pattern, $password_lower)) {
                    return ['valid' => false, 'message' => 'Mật khẩu không được chứa tên thành phố của bạn'];
                }
            }
        }
        
        // Check for common weak passwords
        $common_passwords = ['123456', '12345678', 'password', 'password123', 'qwerty', 'abc123', '111111', '113', '115'];
        if (in_array(strtolower($password), array_map('strtolower', $common_passwords))) {
            return ['valid' => false, 'message' => 'Mật khẩu này quá phổ biến và dễ đoán. Vui lòng chọn mật khẩu khác'];
        }
        
        // Check for sequential numbers
        if (preg_match('/123|234|345|456|567|678|789|987|876|765|654|543|432|321/', $password)) {
            return ['valid' => false, 'message' => 'Mật khẩu không được chứa chuỗi số liên tiếp dễ đoán'];
        }
        
        return ['valid' => true, 'message' => 'Mật khẩu hợp lệ'];
    }
    
    /**
     * ROLE-BASED ACCESS CONTROL
     */
    
    /**
     * Check if user has admin role
     */
    public static function isAdmin() {
        return isset($_SESSION['admin_role_id']) && intval($_SESSION['admin_role_id']) === 1;
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['email']) && isset($_SESSION['id']);
    }
    
    /**
     * Check if user is admin (for pages that require admin role)
     * If not, redirect to login
     */
    public static function requireAdmin() {
        if (!self::isAdmin()) {
            header('Location: admin_login.php');
            exit();
        }
    }
    
    /**
     * Check if user is logged in (for protected pages)
     * If not, redirect to login
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
    
    /**
     * Verify resource ownership
     * Check if user owns a specific resource (e.g., order, cart item)
     * $resource_type: 'order', 'cart_item', etc.
     * $resource_id: ID of the resource
     * $user_id: ID of the user to check ownership
     */
    public static function verifyResourceOwnership($con, $resource_type, $resource_id, $user_id) {
        $resource_id = intval($resource_id);
        $user_id = intval($user_id);
        
        switch ($resource_type) {
            case 'cart_item':
                $query = "SELECT user_id FROM cart_items WHERE id = ?";
                break;
            case 'order':
                $query = "SELECT user_id FROM orders WHERE id = ?";
                break;
            default:
                return false;
        }
        
        $stmt = mysqli_prepare($con, $query);
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $resource_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) === 0) {
            mysqli_stmt_close($stmt);
            return false;
        }
        
        $row = mysqli_fetch_array($result);
        mysqli_stmt_close($stmt);
        
        return intval($row['user_id']) === $user_id;
    }
    
    /**
     * Check user's role ID
     */
    public static function getUserRole() {
        return isset($_SESSION['admin_role_id']) ? intval($_SESSION['admin_role_id']) : null;
    }
    
    /**
     * Get current user ID
     */
    public static function getUserId() {
        return isset($_SESSION['id']) ? intval($_SESSION['id']) : null;
    }
    
    /**
     * Validate session and check for timeout
     * Destroys session if expired
     */
    public static function validateSessionTimeout($con) {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        if (!SessionManager::validateSession($con)) {
            // Redirect admins back to admin login, customers to normal login
            $login_target = (isset($_SESSION['session_type']) && $_SESSION['session_type'] === 'admin')
                ? 'admin_login.php'
                : 'login.php';
            header('Location: ' . $login_target . '?reason=session_expired');
            exit();
        }
        
        return true;
    }
    
    /**
     * Check if session is expiring soon (default: 5 minutes)
     * Useful for warning users before logout
     */
    public static function isSessionExpiringSoon() {
        return SessionManager::isSessionExpiringSoon();
    }
    
    /**
     * Get remaining session time in minutes
     */
    public static function getSessionTimeRemainingMinutes() {
        $seconds = SessionManager::getSessionTimeRemaining();
        return ceil($seconds / 60);
    }
    
    /**
     * Log security-related activities
     */
    public static function logSecurityEvent($con, $event_type, $details = '') {
        if (self::isLoggedIn()) {
            $user_id = self::getUserId();
            SessionManager::logSessionActivity($con, $user_id, $event_type, $details);
        }
    }
    
    /**
     * Check and implement rate limiting for login attempts
     * @param string $identifier Email or IP address to track
     * @param int $max_attempts Maximum attempts allowed
     * @param int $window_seconds Time window in seconds
     * @return bool True if within limit, false if exceeded
     */
    public static function checkRateLimit($identifier, $max_attempts = 5, $window_seconds = 300) {
        // Use database to track failed attempts
        // Create table if doesn't exist
        $create_table = "CREATE TABLE IF NOT EXISTS failed_login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            identifier VARCHAR(255) NOT NULL,
            attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_identifier (identifier),
            INDEX idx_time (attempt_time)
        )";
        
        try {
            @mysqli_query($GLOBALS['con'], $create_table);
        } catch (mysqli_sql_exception $e) {
            // Ignore permission denied errors if table exists
        }
        
        // Get current timestamp
        $cutoff_time = date('Y-m-d H:i:s', time() - $window_seconds);
        
        // Count recent failed attempts
        $check_query = "SELECT COUNT(*) as attempts FROM failed_login_attempts 
                       WHERE identifier = ? AND attempt_time > ?";
        $stmt = mysqli_prepare($GLOBALS['con'], $check_query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $identifier, $cutoff_time);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $attempts = intval($row['attempts'] ?? 0);
            mysqli_stmt_close($stmt);
            
            // Clean old attempts periodically
            if (rand(1, 100) === 1) {
                $cleanup_query = "DELETE FROM failed_login_attempts WHERE attempt_time < ?";
                $cleanup_stmt = mysqli_prepare($GLOBALS['con'], $cleanup_query);
                if ($cleanup_stmt) {
                    $old_cutoff = date('Y-m-d H:i:s', time() - (24 * 3600));
                    mysqli_stmt_bind_param($cleanup_stmt, "s", $old_cutoff);
                    mysqli_stmt_execute($cleanup_stmt);
                    mysqli_stmt_close($cleanup_stmt);
                }
            }
            
            return $attempts < $max_attempts;
        }
        
        return true;
    }
    
    /**
     * Record a failed login attempt for rate limiting
     * @param string $identifier Email or IP address
     */
    public static function recordFailedAttempt($identifier) {
        $insert_query = "INSERT INTO failed_login_attempts (identifier) VALUES (?)";
        $stmt = mysqli_prepare($GLOBALS['con'], $insert_query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $identifier);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    /**
     * Clear failed login attempts for an identifier (called on successful login)
     * @param string $identifier Email or IP address
     */
    public static function clearFailedAttempts($identifier) {
        $delete_query = "DELETE FROM failed_login_attempts WHERE identifier = ?";
        $stmt = mysqli_prepare($GLOBALS['con'], $delete_query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $identifier);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    /**
     * Generate a 6-digit OTP for 2FA password change
     * @return string 6-digit OTP
     */
    public static function generateOTP() {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Create OTP record in database for password change verification
     * @param int $user_id User ID
     * @param string $email User email
     * @param object $con MySQLi connection
     * @return string|false OTP string on success, false on failure
     */
    public static function createPasswordChangeOTP($user_id, $email, $con) {
        $otp = self::generateOTP();
        $otp_hash = hash('sha256', $otp); // Hash OTP for secure storage
        
        // Clear any existing unused OTPs for this user
        $clear_query = "DELETE FROM password_reset_otp WHERE user_id = ? AND is_used = 0";
        $clear_stmt = mysqli_prepare($con, $clear_query);
        if ($clear_stmt) {
            mysqli_stmt_bind_param($clear_stmt, "i", $user_id);
            mysqli_stmt_execute($clear_stmt);
            mysqli_stmt_close($clear_stmt);
        }
        
        // Insert new OTP with database-calculated expiration time (more reliable than PHP)
        // Uses CURRENT_TIMESTAMP + 10 minutes interval
        // Store the SHA256 hash instead of plaintext OTP
        $insert_query = "INSERT INTO password_reset_otp (user_id, email, otp, expires_at) 
                        VALUES (?, ?, ?, CURRENT_TIMESTAMP + INTERVAL 10 MINUTE)";
        $stmt = mysqli_prepare($con, $insert_query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iss", $user_id, $email, $otp_hash);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                error_log("OTP Created - User: $user_id, OTP Hash: " . substr($otp_hash, 0, 16) . "..., Expires in: 10 minutes");
                return $otp; // Return plaintext OTP to send via email
            }
            error_log("OTP Creation Failed - Insert failed for user: $user_id");
            mysqli_stmt_close($stmt);
        }
        
        return false;
    }
    
    /**
     * Verify OTP for password change
     * @param int $user_id User ID
     * @param string $otp OTP to verify (plaintext)
     * @param object $con MySQLi connection
     * @return array Array with success/message or false
     */
    public static function verifyPasswordChangeOTP($user_id, $otp, $con) {
        // Hash the submitted OTP to compare with stored hash
        $otp_hash = hash('sha256', $otp);
        
        error_log("OTP Verification Attempt - User ID: " . $user_id . ", OTP Length: " . strlen($otp) . ", OTP Hash: " . substr($otp_hash, 0, 16) . "...");
        
        // Check if OTP hash exists, hasn't expired, and hasn't been used
        $query = "SELECT id, expires_at FROM password_reset_otp 
                  WHERE user_id = ? AND otp = ? AND is_used = 0 AND expires_at > NOW()";
        $stmt = mysqli_prepare($con, $query);
        
        if (!$stmt) {
            error_log("Database prepare error: " . mysqli_error($con));
            return ['valid' => false, 'message' => 'Database error'];
        }
        
        // Compare hashes instead of plaintext
        mysqli_stmt_bind_param($stmt, "is", $user_id, $otp_hash);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $otp_record = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if (!$otp_record) {
            error_log("OTP Verification Failed - No matching record found");
            return ['valid' => false, 'message' => 'Invalid or expired OTP'];
        }
        
        // Mark OTP as used
        $update_query = "UPDATE password_reset_otp SET is_used = 1, used_at = NOW() WHERE id = ?";
        $update_stmt = mysqli_prepare($con, $update_query);
        
        if ($update_stmt) {
            $otp_id = $otp_record['id'];
            mysqli_stmt_bind_param($update_stmt, "i", $otp_id);
            mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);
            error_log("OTP Verification Success - OTP marked as used for user_id: " . $user_id);
        }
        
        return ['valid' => true, 'message' => 'OTP verified successfully'];
    }
}
?>

