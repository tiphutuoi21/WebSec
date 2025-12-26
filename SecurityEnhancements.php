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
    public static function logSecurityViolation($type, $details = '') {
        $logEntry = sprintf(
            "[%s] SECURITY VIOLATION - Type: %s, IP: %s, User: %s, Details: %s\n",
            date('Y-m-d H:i:s'),
            $type,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SESSION['email'] ?? 'anonymous',
            $details
        );
        
        error_log($logEntry, 3, __DIR__ . '/logs/security.log');
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
        
        @mysqli_query($con, $query);
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
        
        return @mysqli_query($con, $query);
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
        $stmt = mysqli_prepare($con, $checkQuery);
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
}
?>
