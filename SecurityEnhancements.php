<?php
/**
 * SecurityEnhancements.php
 * Enhanced security measures for WebSec project
 * Addresses: Buffer Overflows, Privilege Escalation, DoS, Unpatched DB, Unencrypted Data
 */

class SecurityEnhancements {
    
    // ==================== 1. BUFFER OVERFLOW PREVENTION ====================
    
    /**
     * Input length validation constants
     * Prevents buffer overflow by limiting input sizes
     */
    const MAX_NAME_LENGTH = 100;
    const MAX_EMAIL_LENGTH = 255;
    const MAX_PASSWORD_LENGTH = 128;
    const MAX_ADDRESS_LENGTH = 500;
    const MAX_PHONE_LENGTH = 20;
    const MAX_SEARCH_LENGTH = 255;
    const MAX_DESCRIPTION_LENGTH = 2000;
    const MAX_FILE_SIZE = 5242880; // 5MB
    const MAX_REQUEST_SIZE = 10485760; // 10MB
    
    /**
     * Validate and truncate input to prevent buffer overflow
     */
    public static function limitInputLength($input, $maxLength, $fieldName = 'input') {
        if (!is_string($input)) {
            return ['valid' => false, 'message' => "$fieldName must be a string", 'value' => ''];
        }
        
        // Remove null bytes (common buffer overflow technique)
        $input = str_replace("\0", '', $input);
        
        // Check length
        if (strlen($input) > $maxLength) {
            return [
                'valid' => false, 
                'message' => "$fieldName exceeds maximum length of $maxLength characters",
                'value' => substr($input, 0, $maxLength)
            ];
        }
        
        return ['valid' => true, 'value' => $input];
    }
    
    /**
     * Validate request size to prevent memory exhaustion
     */
    public static function validateRequestSize() {
        $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? intval($_SERVER['CONTENT_LENGTH']) : 0;
        
        if ($contentLength > self::MAX_REQUEST_SIZE) {
            http_response_code(413); // Payload Too Large
            die('Request too large');
        }
        
        return true;
    }
    
    /**
     * Safe file read with size limit
     */
    public static function safeFileRead($filepath, $maxBytes = 1048576) {
        if (!file_exists($filepath) || !is_readable($filepath)) {
            return false;
        }
        
        $fileSize = filesize($filepath);
        if ($fileSize > $maxBytes) {
            return false; // File too large
        }
        
        return file_get_contents($filepath, false, null, 0, $maxBytes);
    }
    
    // ==================== 2. PRIVILEGE ESCALATION PREVENTION ====================
    
    /**
     * Role hierarchy for access control
     */
    const ROLE_ADMIN = 1;
    const ROLE_SALES_MANAGER = 2;
    const ROLE_CUSTOMER = 3;
    
    /**
     * Permission matrix - defines what each role can do
     */
    private static $permissions = [
        self::ROLE_ADMIN => [
            'manage_users' => true,
            'delete_users' => true,
            'manage_products' => true,
            'delete_products' => true,
            'manage_orders' => true,
            'delete_orders' => true,
            'manage_staff' => true,
            'view_reports' => true,
            'system_settings' => true
        ],
        self::ROLE_SALES_MANAGER => [
            'manage_users' => false,
            'delete_users' => false,
            'manage_products' => true,
            'delete_products' => false,
            'manage_orders' => true,
            'delete_orders' => false,
            'manage_staff' => false,
            'view_reports' => true,
            'system_settings' => false
        ],
        self::ROLE_CUSTOMER => [
            'manage_users' => false,
            'delete_users' => false,
            'manage_products' => false,
            'delete_products' => false,
            'manage_orders' => false,
            'delete_orders' => false,
            'manage_staff' => false,
            'view_reports' => false,
            'system_settings' => false
        ]
    ];
    
    /**
     * Check if current user has permission
     */
    public static function hasPermission($permission) {
        $roleId = isset($_SESSION['admin_role_id']) ? intval($_SESSION['admin_role_id']) : self::ROLE_CUSTOMER;
        
        if (!isset(self::$permissions[$roleId])) {
            return false;
        }
        
        return isset(self::$permissions[$roleId][$permission]) && 
               self::$permissions[$roleId][$permission] === true;
    }
    
    /**
     * Require specific permission or redirect
     */
    public static function requirePermission($permission, $redirectUrl = 'admin_login.php') {
        if (!self::hasPermission($permission)) {
            self::logSecurityViolation('permission_denied', "Attempted: $permission");
            header("Location: $redirectUrl?error=access_denied");
            exit();
        }
    }
    
    /**
     * Validate role change (prevent privilege escalation)
     */
    public static function canChangeRole($currentUserRole, $targetRole) {
        // Only admin can assign roles
        if ($currentUserRole !== self::ROLE_ADMIN) {
            return false;
        }
        
        // Cannot create super-admin from non-admin
        if ($targetRole < self::ROLE_ADMIN) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Verify session integrity (prevent session hijacking)
     */
    public static function verifySessionIntegrity() {
        // Check if session variables are tampered
        if (!isset($_SESSION['session_fingerprint'])) {
            return false;
        }
        
        $currentFingerprint = self::generateSessionFingerprint();
        return hash_equals($_SESSION['session_fingerprint'], $currentFingerprint);
    }
    
    /**
     * Generate session fingerprint
     */
    public static function generateSessionFingerprint() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'unknown';
        
        return hash('sha256', $userAgent . $acceptLanguage);
    }
    
    /**
     * Log security violation
     */
    public static function logSecurityViolation($type, $details = '', $con = null) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user = $_SESSION['email'] ?? 'anonymous';
        
        // 1. Log to file (Backup)
        $logEntry = sprintf(
            "[%s] SECURITY VIOLATION - Type: %s, IP: %s, User: %s, Details: %s\n",
            date('Y-m-d H:i:s'),
            $type,
            $ip,
            $user,
            $details
        );
        error_log($logEntry, 3, __DIR__ . '/logs/security.log');

        // 2. Log to Database (Primary)
        if ($con) {
            self::ensureSecurityViolationsTable($con);
            $stmt = mysqli_prepare($con, 
                "INSERT INTO security_violations (ip_address, violation_type, details, created_at) 
                 VALUES (?, ?, ?, NOW())");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sss", $ip, $type, $details);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }

    /**
     * Log general security event (Audit Trail)
     */
    public static function logSecurityEvent($con, $eventType, $details = '') {
        $userId = $_SESSION['id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        // Ensure table exists
        $query = "CREATE TABLE IF NOT EXISTS security_audit_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_type VARCHAR(100) NOT NULL,
            user_id INT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            details TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_event_type (event_type),
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB";
        
        try {
            @mysqli_query($con, $query);
        } catch (Exception $e) {}

        // Insert log
        $stmt = mysqli_prepare($con, 
            "INSERT INTO security_audit_log (event_type, user_id, ip_address, user_agent, details, created_at) 
             VALUES (?, ?, ?, ?, ?, NOW())");
             
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sisss", $eventType, $userId, $ip, $userAgent, $details);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    // ==================== 3. DENIAL OF SERVICE (DoS) PREVENTION ====================
    
    /**
     * Rate limiting configuration
     */
    const RATE_LIMIT_LOGIN = ['attempts' => 5, 'window' => 300]; // 5 attempts / 5 min
    const RATE_LIMIT_API = ['attempts' => 100, 'window' => 60];  // 100 requests / min
    const RATE_LIMIT_SEARCH = ['attempts' => 30, 'window' => 60]; // 30 searches / min
    const RATE_LIMIT_SIGNUP = ['attempts' => 3, 'window' => 3600]; // 3 signups / hour per IP
    
    /**
     * Advanced rate limiting with IP + User Agent
     */
    public static function checkAdvancedRateLimit($con, $action, $identifier = null) {
        $limits = [
            'login' => self::RATE_LIMIT_LOGIN,
            'api' => self::RATE_LIMIT_API,
            'search' => self::RATE_LIMIT_SEARCH,
            'signup' => self::RATE_LIMIT_SIGNUP
        ];
        
        if (!isset($limits[$action])) {
            return true;
        }
        
        $limit = $limits[$action];
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = $identifier ? "{$action}_{$identifier}_{$ip}" : "{$action}_{$ip}";
        
        // Create rate limiting table if not exists
        self::ensureRateLimitTable($con);
        
        // Clean old entries
        $cutoff = date('Y-m-d H:i:s', time() - $limit['window']);
        $cleanQuery = "DELETE FROM rate_limits WHERE action_key = ? AND created_at < ?";
        $stmt = mysqli_prepare($con, $cleanQuery);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $key, $cutoff);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        // Count current attempts
        $countQuery = "SELECT COUNT(*) as count FROM rate_limits WHERE action_key = ?";
        $stmt = mysqli_prepare($con, $countQuery);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $key);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $count = intval($row['count'] ?? 0);
            mysqli_stmt_close($stmt);
            
            if ($count >= $limit['attempts']) {
                self::logSecurityViolation('rate_limit_exceeded', "Action: $action, Key: $key");
                return false;
            }
        }
        
        // Record this attempt
        $insertQuery = "INSERT INTO rate_limits (action_key, ip_address) VALUES (?, ?)";
        $stmt = mysqli_prepare($con, $insertQuery);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $key, $ip);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        return true;
    }
    
    /**
     * Ensure rate limit table exists
     */
    private static function ensureRateLimitTable($con) {
        $query = "CREATE TABLE IF NOT EXISTS rate_limits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            action_key VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_action_key (action_key),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB";
        
        try {
            @mysqli_query($con, $query);
        } catch (mysqli_sql_exception $e) {
            // Ignore permission denied errors if table exists
        }
    }
    
    /**
     * Request throttling - add delay for suspicious activity
     */
    public static function throttleRequest($failedAttempts) {
        if ($failedAttempts > 3) {
            // Exponential backoff: 2^n seconds, max 30 seconds
            $delay = min(pow(2, $failedAttempts - 3), 30);
            sleep($delay);
        }
    }
    
    /**
     * Validate file upload to prevent resource exhaustion
     */
    public static function validateUploadLimits($file) {
        // Check total upload size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return ['valid' => false, 'message' => 'File too large'];
        }
        
        // Check file count in session
        if (!isset($_SESSION['upload_count'])) {
            $_SESSION['upload_count'] = 0;
            $_SESSION['upload_reset_time'] = time();
        }
        
        // Reset counter every hour
        if (time() - $_SESSION['upload_reset_time'] > 3600) {
            $_SESSION['upload_count'] = 0;
            $_SESSION['upload_reset_time'] = time();
        }
        
        // Max 20 uploads per hour
        if ($_SESSION['upload_count'] >= 20) {
            return ['valid' => false, 'message' => 'Upload limit reached'];
        }
        
        $_SESSION['upload_count']++;
        return ['valid' => true];
    }
    
    // ==================== 4. DATABASE PATCHING / VERSIONING ====================
    
    /**
     * Database version table
     */
    public static function ensureDbVersionTable($con) {
        $query = "CREATE TABLE IF NOT EXISTS db_migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            version VARCHAR(50) NOT NULL UNIQUE,
            description TEXT,
            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            applied_by VARCHAR(255),
            checksum VARCHAR(64),
            INDEX idx_version (version)
        ) ENGINE=InnoDB";
        
        try {
            return @mysqli_query($con, $query);
        } catch (mysqli_sql_exception $e) {
            // If permission denied (1142), we assume we are running as a restricted user.
            // We cannot create the table, but we should check if it exists.
            // If it doesn't exist, subsequent queries will fail, but we avoid the Fatal Error here.
            return false;
        }
    }
    
    /**
     * Get current database version
     */
    public static function getCurrentDbVersion($con) {
        self::ensureDbVersionTable($con);
        
        $query = "SELECT version FROM db_migrations ORDER BY id DESC LIMIT 1";
        $result = @mysqli_query($con, $query);
        
        if ($result && $row = mysqli_fetch_assoc($result)) {
            return $row['version'];
        }
        
        return '0.0.0';
    }
    
    /**
     * Apply database migration
     */
    public static function applyMigration($con, $version, $sql, $description = '') {
        // Check if already applied
        $checkQuery = "SELECT id FROM db_migrations WHERE version = ?";
        
        try {
            $stmt = @mysqli_prepare($con, $checkQuery);
            if (!$stmt) {
                return ['success' => false, 'message' => 'Migration table not accessible'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Migration table not accessible: ' . $e->getMessage()];
        }

        mysqli_stmt_bind_param($stmt, "s", $version);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            mysqli_stmt_close($stmt);
            return ['success' => true, 'message' => 'Already applied'];
        }
        mysqli_stmt_close($stmt);
        
        // Apply migration
        mysqli_begin_transaction($con);
        
        try {
            if (mysqli_query($con, $sql)) {
                // Record migration
                $checksum = hash('sha256', $sql);
                $appliedBy = $_SESSION['email'] ?? 'system';
                
                $insertQuery = "INSERT INTO db_migrations (version, description, applied_by, checksum) VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($con, $insertQuery);
                mysqli_stmt_bind_param($stmt, "ssss", $version, $description, $appliedBy, $checksum);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                
                mysqli_commit($con);
                return ['success' => true, 'message' => "Migration $version applied"];
            } else {
                throw new Exception(mysqli_error($con));
            }
        } catch (Exception $e) {
            mysqli_rollback($con);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Available migrations
     */
    public static function getAvailableMigrations() {
        return [
            '1.0.0' => [
                'description' => 'Initial security tables',
                'sql' => "
                    CREATE TABLE IF NOT EXISTS security_audit_log (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        event_type VARCHAR(100) NOT NULL,
                        user_id INT,
                        ip_address VARCHAR(45),
                        user_agent TEXT,
                        details TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_event_type (event_type),
                        INDEX idx_user_id (user_id),
                        INDEX idx_created_at (created_at)
                    ) ENGINE=InnoDB
                "
            ],
            '1.0.1' => [
                'description' => 'Add encrypted data support',
                'sql' => "
                    CREATE TABLE IF NOT EXISTS encrypted_data (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        data_type VARCHAR(50) NOT NULL,
                        reference_id INT NOT NULL,
                        encrypted_value TEXT NOT NULL,
                        iv VARCHAR(32) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_data_type (data_type),
                        INDEX idx_reference_id (reference_id)
                    ) ENGINE=InnoDB
                "
            ],
            '1.0.2' => [
                'description' => 'Add rate limiting table',
                'sql' => "
                    CREATE TABLE IF NOT EXISTS rate_limits (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        action_key VARCHAR(255) NOT NULL,
                        ip_address VARCHAR(45) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_action_key (action_key),
                        INDEX idx_created_at (created_at)
                    ) ENGINE=InnoDB
                "
            ]
        ];
    }
    
    /**
     * Run all pending migrations
     */
    public static function runPendingMigrations($con) {
        // Ensure migrations table exists FIRST
        self::ensureDbVersionTable($con);
        
        $results = [];
        $migrations = self::getAvailableMigrations();
        
        foreach ($migrations as $version => $migration) {
            $result = self::applyMigration($con, $version, $migration['sql'], $migration['description']);
            $results[$version] = $result;
        }
        
        return $results;
    }
    
    // ==================== 5. DATA ENCRYPTION ====================
    
    /**
     * Encryption key (should be stored in environment variable in production)
     */
    private static function getEncryptionKey() {
        // Check for environment variable first
        $key = getenv('ENCRYPTION_KEY');
        if ($key) {
            return $key;
        }
        
        // Check for .env file
        $envFile = __DIR__ . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, 'ENCRYPTION_KEY=') === 0) {
                    return substr($line, strlen('ENCRYPTION_KEY='));
                }
            }
        }
        
        // Generate and store new key if not exists
        $keyFile = __DIR__ . '/.encryption_key';
        if (file_exists($keyFile)) {
            return trim(file_get_contents($keyFile));
        }
        
        // Generate new key
        $newKey = bin2hex(random_bytes(32));
        file_put_contents($keyFile, $newKey);
        chmod($keyFile, 0600); // Restrict access
        
        return $newKey;
    }
    
    /**
     * Encrypt sensitive data using AES-256-GCM
     */
    public static function encryptData($plaintext) {
        $key = hex2bin(self::getEncryptionKey());
        $iv = random_bytes(12); // GCM uses 12 bytes IV
        $tag = '';
        
        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '', // AAD
            16  // Tag length
        );
        
        if ($ciphertext === false) {
            return false;
        }
        
        // Return IV + Tag + Ciphertext as base64
        return base64_encode($iv . $tag . $ciphertext);
    }
    
    /**
     * Decrypt sensitive data
     */
    public static function decryptData($encryptedData) {
        $key = hex2bin(self::getEncryptionKey());
        $data = base64_decode($encryptedData);
        
        if (strlen($data) < 28) { // 12 (IV) + 16 (tag) = 28 minimum
            return false;
        }
        
        $iv = substr($data, 0, 12);
        $tag = substr($data, 12, 16);
        $ciphertext = substr($data, 28);
        
        $plaintext = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        return $plaintext;
    }
    
    /**
     * Hash sensitive data for storage (one-way)
     */
    public static function hashSensitiveData($data) {
        return hash('sha256', $data . self::getEncryptionKey());
    }
    
    /**
     * Encrypt and store sensitive data in database
     */
    public static function storeEncryptedData($con, $dataType, $referenceId, $data) {
        $encrypted = self::encryptData($data);
        if ($encrypted === false) {
            return false;
        }
        
        // Extract IV for storage
        $decoded = base64_decode($encrypted);
        $iv = bin2hex(substr($decoded, 0, 12));
        
        $query = "INSERT INTO encrypted_data (data_type, reference_id, encrypted_value, iv) 
                  VALUES (?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE encrypted_value = ?, iv = ?, updated_at = NOW()";
        
        $stmt = mysqli_prepare($con, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sissss", $dataType, $referenceId, $encrypted, $iv, $encrypted, $iv);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        
        return false;
    }
    
    /**
     * Retrieve and decrypt data from database
     */
    public static function getEncryptedData($con, $dataType, $referenceId) {
        $query = "SELECT encrypted_value FROM encrypted_data WHERE data_type = ? AND reference_id = ?";
        $stmt = mysqli_prepare($con, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $dataType, $referenceId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            
            if ($row) {
                return self::decryptData($row['encrypted_value']);
            }
        }
        
        return false;
    }
    
    /**
     * Mask sensitive data for display
     */
    public static function maskSensitiveData($data, $type = 'general') {
        switch ($type) {
            case 'email':
                $parts = explode('@', $data);
                if (count($parts) === 2) {
                    $name = $parts[0];
                    $masked = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 2));
                    return $masked . '@' . $parts[1];
                }
                break;
            
            case 'phone':
                return substr($data, 0, 3) . '****' . substr($data, -3);
            
            case 'card':
                return '**** **** **** ' . substr($data, -4);
            
            default:
                $len = strlen($data);
                if ($len <= 4) {
                    return str_repeat('*', $len);
                }
                return substr($data, 0, 2) . str_repeat('*', $len - 4) . substr($data, -2);
        }
        
        return $data;
    }
    
    /**
     * Secure password storage with pepper
     */
    public static function hashPassword($password) {
        $pepper = self::getEncryptionKey();
        $pepperedPassword = hash_hmac('sha256', $password, $pepper);
        return password_hash($pepperedPassword, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }
    
    /**
     * Verify peppered password
     */
    public static function verifyPassword($password, $hash) {
        $pepper = self::getEncryptionKey();
        $pepperedPassword = hash_hmac('sha256', $password, $pepper);
        return password_verify($pepperedPassword, $hash);
    }
    
    // ==================== ENHANCED IMPROVEMENTS ====================
    
    // ==================== 1. BUFFER OVERFLOW - ENHANCED ====================
    
    /**
     * Validate array depth to prevent stack overflow
     */
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
    
    /**
     * Safe JSON decode with depth limit
     */
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
    
    /**
     * Validate all POST/GET inputs at once
     */
    public static function validateAllInputLengths() {
        $limits = [
            'name' => self::MAX_NAME_LENGTH,
            'email' => self::MAX_EMAIL_LENGTH,
            'password' => self::MAX_PASSWORD_LENGTH,
            'address' => self::MAX_ADDRESS_LENGTH,
            'phone' => self::MAX_PHONE_LENGTH,
            'contact' => self::MAX_PHONE_LENGTH,
            'search' => self::MAX_SEARCH_LENGTH,
            'description' => self::MAX_DESCRIPTION_LENGTH,
            'city' => self::MAX_NAME_LENGTH,
            'product_name' => self::MAX_NAME_LENGTH,
        ];
        
        $inputs = array_merge($_POST, $_GET);
        
        foreach ($inputs as $key => $value) {
            if (is_string($value)) {
                $maxLen = $limits[$key] ?? 1000; // Default 1000 chars
                if (strlen($value) > $maxLen) {
                    self::logSecurityViolation('buffer_overflow_attempt', 
                        "Field: $key, Length: " . strlen($value) . ", Max: $maxLen");
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * Set PHP memory and execution limits
     */
    public static function setResourceLimits() {
        @ini_set('memory_limit', '128M');
        @ini_set('max_execution_time', 30);
        @ini_set('max_input_time', 30);
        @ini_set('max_input_vars', 1000);
        @ini_set('max_input_nesting_level', 5);
    }
    
    // ==================== 2. PRIVILEGE ESCALATION - ENHANCED ====================
    
    /**
     * Sensitive actions requiring re-authentication
     */
    const SENSITIVE_ACTIONS = [
        'delete_user',
        'change_role',
        'system_settings',
        'export_data',
        'delete_all_orders'
    ];
    
    /**
     * Check if action requires re-authentication
     */
    public static function requiresReAuth($action) {
        return in_array($action, self::SENSITIVE_ACTIONS);
    }
    
    /**
     * Verify re-authentication for sensitive actions
     */
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
            self::logPrivilegeAction($con, $action, 'reauth_success');
            return true;
        }
        
        self::logPrivilegeAction($con, $action, 'reauth_failed');
        return false;
    }
    
    /**
     * Horizontal privilege check - prevent IDOR
     */
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
        if (!$stmt) return false;
        
        mysqli_stmt_bind_param($stmt, "i", $resourceId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if (!$row || intval($row['user_id']) !== intval($userId)) {
            self::logSecurityViolation('horizontal_privilege_attempt', 
                "Resource: $resourceType, ID: $resourceId, User: $userId");
            return false;
        }
        
        return true;
    }
    
    /**
     * Log privilege-related actions
     */
    public static function logPrivilegeAction($con, $action, $status, $details = '') {
        $userId = $_SESSION['id'] ?? 0;
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        self::ensurePrivilegeLogTable($con);
        
        $stmt = mysqli_prepare($con, 
            "INSERT INTO privilege_audit_log (user_id, action, status, details, ip_address, created_at) 
             VALUES (?, ?, ?, ?, ?, NOW())");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "issss", $userId, $action, $status, $details, $ip);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    /**
     * Ensure privilege log table exists
     */
    private static function ensurePrivilegeLogTable($con) {
        $query = "CREATE TABLE IF NOT EXISTS privilege_audit_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action VARCHAR(100) NOT NULL,
            status VARCHAR(50) NOT NULL,
            details TEXT,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_action (action),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB";
        
        try {
            @mysqli_query($con, $query);
        } catch (mysqli_sql_exception $e) {
            // Ignore permission denied errors if table exists
        }
    }
    
    /**
     * Enforce session fingerprint on every request
     */
    public static function enforceSessionIntegrity() {
        if (!isset($_SESSION['id'])) {
            return true; // Not logged in
        }
        
        if (!isset($_SESSION['session_fingerprint'])) {
            // First request after login - set fingerprint
            $_SESSION['session_fingerprint'] = self::generateSessionFingerprint();
            return true;
        }
        
        if (!self::verifySessionIntegrity()) {
            self::logSecurityViolation('session_hijacking_attempt', 
                "Expected: " . $_SESSION['session_fingerprint'] . 
                ", Got: " . self::generateSessionFingerprint());
            
            // Destroy compromised session
            session_destroy();
            return false;
        }
        
        return true;
    }
    
    // ==================== 3. DoS PREVENTION - ENHANCED ====================
    
    /**
     * IP Blacklist configuration
     */
    const BLACKLIST_THRESHOLD = 10;      // Violations before ban
    const BLACKLIST_DURATION = 86400;    // 24 hours ban
    
    /**
     * Check if IP is blacklisted
     */
    public static function isIPBlacklisted($con, $ip = null) {
        $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        
        self::ensureBlacklistTable($con);
        
        $stmt = mysqli_prepare($con, 
            "SELECT id FROM ip_blacklist 
             WHERE ip_address = ? AND expires_at > NOW() AND is_active = 1");
        if (!$stmt) return false;
        
        mysqli_stmt_bind_param($stmt, "s", $ip);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $isBlacklisted = mysqli_num_rows($result) > 0;
        mysqli_stmt_close($stmt);
        
        return $isBlacklisted;
    }
    
    /**
     * Add IP to blacklist
     */
    public static function blacklistIP($con, $ip, $reason, $duration = null) {
        $duration = $duration ?? self::BLACKLIST_DURATION;
        
        self::ensureBlacklistTable($con);
        
        $stmt = mysqli_prepare($con, 
            "INSERT INTO ip_blacklist (ip_address, reason, expires_at, created_at) 
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND), NOW())
             ON DUPLICATE KEY UPDATE 
                expires_at = DATE_ADD(NOW(), INTERVAL ? SECOND),
                reason = ?,
                violation_count = violation_count + 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssiss", $ip, $reason, $duration, $duration, $reason);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        self::logSecurityViolation('ip_blacklisted', "IP: $ip, Reason: $reason");
    }
    
    /**
     * Auto-blacklist based on violation count
     */
    public static function checkAndAutoBlacklist($con, $ip = null) {
        $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        
        self::ensureSecurityViolationsTable($con);
        
        // Count recent violations
        $stmt = mysqli_prepare($con, 
            "SELECT COUNT(*) as count FROM security_violations 
             WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        if (!$stmt) return false;
        
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
    
    /**
     * Ensure blacklist table exists
     */
    private static function ensureBlacklistTable($con) {
        $query = "CREATE TABLE IF NOT EXISTS ip_blacklist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL UNIQUE,
            reason VARCHAR(255),
            violation_count INT DEFAULT 1,
            expires_at DATETIME NOT NULL,
            is_active TINYINT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ip (ip_address),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB";
        
        try {
            @mysqli_query($con, $query);
        } catch (mysqli_sql_exception $e) {
            // Ignore permission denied errors if table exists
        }
    }
    
    /**
     * Ensure security violations table exists
     */
    private static function ensureSecurityViolationsTable($con) {
        $query = "CREATE TABLE IF NOT EXISTS security_violations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            violation_type VARCHAR(100) NOT NULL,
            details TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ip (ip_address),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB";
        
        try {
            @mysqli_query($con, $query);
        } catch (mysqli_sql_exception $e) {
            // Ignore permission denied errors if table exists
        }
    }
    
    /**
     * CAPTCHA requirement check
     */
    public static function requiresCaptcha($con, $action, $identifier) {
        $limits = [
            'login' => 3,
            'signup' => 2,
            'password_reset' => 2
        ];
        
        if (!isset($limits[$action])) {
            return false;
        }
        
        $key = "{$action}_{$identifier}";
        
        self::ensureRateLimitTable($con);
        
        $stmt = mysqli_prepare($con, 
            "SELECT COUNT(*) as count FROM rate_limits 
             WHERE action_key = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
        if (!$stmt) return false;
        
        mysqli_stmt_bind_param($stmt, "s", $key);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return intval($row['count']) >= $limits[$action];
    }
    
    /**
     * Set query timeout for slow query detection
     */
    public static function setQueryTimeout($con, $seconds = 5) {
        @mysqli_query($con, "SET SESSION MAX_EXECUTION_TIME = " . ($seconds * 1000));
    }
    
    // ==================== 4. DATABASE PATCHING - ENHANCED ====================
    
    /**
     * Dry-run migration (test without applying)
     */
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
    
    // ==================== 5. DATA ENCRYPTION - ENHANCED ====================
    
    /**
     * Key version for rotation support
     */
    const KEY_VERSION_CURRENT = 1;
    
    /**
     * PII fields that should always be encrypted
     */
    const PII_FIELDS = [
        'users' => ['phone', 'address', 'date_of_birth'],
        'orders' => ['shipping_address', 'billing_address']
    ];
    
    /**
     * Auto-encrypt PII before saving
     */
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
    
    /**
     * Auto-decrypt PII when reading
     */
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
    
    /**
     * Sanitize logs to remove sensitive data
     */
    public static function sanitizeForLog($data) {
        $sensitivePatterns = [
            // Credit card
            '/\b\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}\b/' => '[CARD REDACTED]',
            // Email
            '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/' => '[EMAIL REDACTED]',
            // Phone
            '/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/' => '[PHONE REDACTED]',
            // Password in URLs
            '/password=[^&\s]+/' => 'password=[REDACTED]',
            // API keys
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
    
    /**
     * Secure logging with sanitization
     */
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
    
    /**
     * Initialize all security measures
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
}
?>
