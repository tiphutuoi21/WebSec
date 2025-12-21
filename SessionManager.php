<?php
/**
 * SessionManager.php
 * Handles secure session generation, validation, timeout, and audit logging
 */

class SessionManager {
    
    // Session duration: 30 minutes
    const SESSION_DURATION = 1800;
    
    // Session table name for audit logging
    const SESSION_TABLE = 'sessions';
    
    /**
     * Initialize secure session settings
     * Must be called before session_start()
     * Safe to call multiple times - only configures if session not already active
     */
    public static function initializeSecureSession() {
        // Check if session is already active
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Session already started, nothing to do
            return;
        }
        
        // Only configure session settings if session hasn't started yet
        if (session_status() === PHP_SESSION_NONE) {
            // Session configuration
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_trans_sid', 0);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // Only HTTPS in production
            ini_set('session.cookie_httponly', 1); // Prevent JS access to session cookie
            ini_set('session.cookie_samesite', 'Strict'); // CSRF protection
            ini_set('session.gc_maxlifetime', self::SESSION_DURATION); // Server-side garbage collection
            
            // Set cookie parameters
            session_set_cookie_params([
                'lifetime' => self::SESSION_DURATION,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            
            // Start session with generated ID
            session_start();
        }
        
        // Regenerate session ID on login to prevent session fixation attacks
        // This should be called explicitly in login_submit.php
    }
    
    /**
     * Generate a strong, cryptographically secure session ID
     * Uses random_bytes for maximum entropy
     */
    public static function generateSecureSessionId() {
        // Generate 32 bytes of random data and convert to hex (64 characters)
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Create a new secure session for a user
     * Invalidates all old sessions for that user (1 user = 1 session)
     * 
     * @param mysqli $con - Database connection
     * @param int $user_id - User ID
     * @param string $user_email - User email
     * @param int $role_id - User role ID (1=admin, 2=sales_manager, 3=customer)
     * @param string $session_type - 'admin' or 'customer'
     * @return bool - True if successful
     */
    public static function createUserSession($con, $user_id, $user_email, $role_id, $session_type = 'customer') {
        $user_id = intval($user_id);
        $role_id = intval($role_id);
        
        // Step 1: Invalidate all old sessions for this user
        self::invalidateUserSessions($con, $user_id);
        
        // Step 2: Regenerate session ID (prevents session fixation)
        session_regenerate_id(true);
        $new_session_id = session_id();
        
        // Step 3: Set session variables
        $_SESSION['id'] = $user_id;
        $_SESSION['email'] = $user_email;
        $_SESSION['role_id'] = $role_id;
        $_SESSION['session_type'] = $session_type;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['session_id_hash'] = hash('sha256', $new_session_id); // Store hash for verification
        
        // For admin sessions
        if ($session_type === 'admin') {
            $_SESSION['admin_email'] = $user_email;
            $_SESSION['admin_role_id'] = $role_id;
            $_SESSION['admin_role'] = self::getRoleName($role_id);
        }
        
        // Step 4: Log session creation to database
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        
        $insert_query = "INSERT INTO " . self::SESSION_TABLE . " 
                        (session_id, user_id, user_email, role_id, ip_address, user_agent, session_type, login_time, last_activity, is_active) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 1)";
        
        $stmt = mysqli_prepare($con, $insert_query);
        if (!$stmt) {
            // Table might not exist - log but don't fail
            error_log("Session insert prepare error: " . mysqli_error($con) . " - Continuing without database session tracking");
            // Still return true because session variables are set
            return true;
        }
        
        $session_id_hash = hash('sha256', $new_session_id);
        mysqli_stmt_bind_param($stmt, "siiisss", $session_id_hash, $user_id, $user_email, $role_id, $ip_address, $user_agent, $session_type);
        
        if (!mysqli_stmt_execute($stmt)) {
            // Table might not exist - log but don't fail
            error_log("Session insert execute error: " . mysqli_stmt_error($stmt) . " - Continuing without database session tracking");
            mysqli_stmt_close($stmt);
            // Still return true because session variables are set
            return true;
        }
        
        mysqli_stmt_close($stmt);
        return true;
    }
    
    /**
     * Invalidate all sessions for a specific user
     * Called when user logs in (forces single-session-per-user)
     * 
     * @param mysqli $con - Database connection
     * @param int $user_id - User ID
     */
    public static function invalidateUserSessions($con, $user_id) {
        $user_id = intval($user_id);
        
        $update_query = "UPDATE " . self::SESSION_TABLE . " SET is_active = 0, logged_out_time = NOW() WHERE user_id = ? AND is_active = 1";
        
        $stmt = mysqli_prepare($con, $update_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    /**
     * Validate current session
     * Checks:
     * - Session ID matches server record
     * - Session not expired
     * - IP address hasn't changed (optional but recommended)
     * 
     * @param mysqli $con - Database connection
     * @param bool $check_ip - Whether to verify IP matches (default: true)
     * @return bool - True if session is valid
     */
    public static function validateSession($con, $check_ip = true) {
        // Check if session variables exist
        if (!isset($_SESSION['id']) || !isset($_SESSION['email'])) {
            return false;
        }
        
        $user_id = intval($_SESSION['id']);
        
        // Check session timeout
        if (isset($_SESSION['login_time'])) {
            $session_age = time() - $_SESSION['login_time'];
            if ($session_age > self::SESSION_DURATION) {
                self::destroySession($con, $user_id);
                return false;
            }
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        
        // Try to verify session in database (if table exists)
        // If table doesn't exist, fall back to PHP session validation
        if (isset($_SESSION['session_id_hash'])) {
            $session_id_hash = $_SESSION['session_id_hash'];
            
            // Check if sessions table exists
            $check_table = "SHOW TABLES LIKE '" . self::SESSION_TABLE . "'";
            $table_check = mysqli_query($con, $check_table);
            
            if ($table_check && mysqli_num_rows($table_check) > 0) {
                // Table exists - verify in database
                $verify_query = "SELECT session_id FROM " . self::SESSION_TABLE . " 
                                WHERE user_id = ? AND is_active = 1 AND session_id = ?";
                
                $stmt = mysqli_prepare($con, $verify_query);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "is", $user_id, $session_id_hash);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $is_valid = mysqli_num_rows($result) > 0;
                    mysqli_stmt_close($stmt);
                    
                    if (!$is_valid) {
                        return false;
                    }
                }
            }
        }
        
        // If we get here, session is valid (either verified in DB or using PHP session fallback)
        // Optional: Verify IP hasn't changed (only if sessions table exists)
        if ($check_ip) {
            $check_table = "SHOW TABLES LIKE '" . self::SESSION_TABLE . "'";
            $table_check = mysqli_query($con, $check_table);
            
            if ($table_check && mysqli_num_rows($table_check) > 0) {
                $ip_query = "SELECT ip_address FROM " . self::SESSION_TABLE . " WHERE user_id = ? AND is_active = 1";
                $stmt = mysqli_prepare($con, $ip_query);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "i", $user_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    
                    if ($row = mysqli_fetch_array($result)) {
                        // If IP changed, session is compromised
                        if ($row['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
                            self::destroySession($con, $user_id);
                            mysqli_stmt_close($stmt);
                            return false;
                        }
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        }
        
        return true;
    }
    
    /**
     * Destroy a session
     * Called on logout or session expiry
     * 
     * @param mysqli $con - Database connection
     * @param int $user_id - User ID (optional, uses session if not provided)
     */
    public static function destroySession($con, $user_id = null) {
        // Get user ID from session if not provided
        if ($user_id === null) {
            $user_id = isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;
        } else {
            $user_id = intval($user_id);
        }
        
        // Mark session as inactive in database
        if ($user_id > 0) {
            $update_query = "UPDATE " . self::SESSION_TABLE . " 
                           SET is_active = 0, logged_out_time = NOW() 
                           WHERE user_id = ? AND is_active = 1";
            
            $stmt = mysqli_prepare($con, $update_query);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        
        // Clear session variables
        $_SESSION = array();
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
    }
    
    /**
     * Get all active sessions for a user
     * 
     * @param mysqli $con - Database connection
     * @param int $user_id - User ID
     * @return array - Array of active sessions
     */
    public static function getUserActiveSessions($con, $user_id) {
        $user_id = intval($user_id);
        
        $query = "SELECT session_id, ip_address, user_agent, login_time, last_activity, session_type 
                  FROM " . self::SESSION_TABLE . " 
                  WHERE user_id = ? AND is_active = 1 
                  ORDER BY login_time DESC";
        
        $stmt = mysqli_prepare($con, $query);
        if (!$stmt) {
            return array();
        }
        
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $sessions = array();
        while ($row = mysqli_fetch_array($result)) {
            $sessions[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $sessions;
    }
    
    /**
     * Log session activity (for audit trail)
     * 
     * @param mysqli $con - Database connection
     * @param int $user_id - User ID
     * @param string $action - Action name (login, logout, page_access, etc.)
     * @param string $details - Additional details (optional)
     */
    public static function logSessionActivity($con, $user_id, $action, $details = '') {
        $user_id = intval($user_id);
        $ip_address = $_SERVER['REMOTE_ADDR'];
        
        $log_query = "INSERT INTO session_audit_log (user_id, action, details, ip_address, timestamp) 
                     VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($con, $log_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "isss", $user_id, $action, $details, $ip_address);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    /**
     * Get audit log for a user
     * 
     * @param mysqli $con - Database connection
     * @param int $user_id - User ID
     * @param int $limit - Number of records to return (default: 50)
     * @return array - Audit log records
     */
    public static function getUserAuditLog($con, $user_id, $limit = 50) {
        $user_id = intval($user_id);
        $limit = intval($limit);
        
        $query = "SELECT id, action, details, ip_address, timestamp 
                  FROM session_audit_log 
                  WHERE user_id = ? 
                  ORDER BY timestamp DESC 
                  LIMIT ?";
        
        $stmt = mysqli_prepare($con, $query);
        if (!$stmt) {
            return array();
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $logs = array();
        while ($row = mysqli_fetch_array($result)) {
            $logs[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $logs;
    }
    
    /**
     * Check if session is approaching expiry
     * 
     * @param int $warning_threshold - Minutes before expiry to warn (default: 5)
     * @return bool - True if approaching expiry
     */
    public static function isSessionExpiringSoon($warning_threshold = 5) {
        if (!isset($_SESSION['login_time'])) {
            return false;
        }
        
        $session_age = time() - $_SESSION['login_time'];
        $threshold_seconds = $warning_threshold * 60;
        $remaining_time = self::SESSION_DURATION - $session_age;
        
        return $remaining_time <= $threshold_seconds;
    }
    
    /**
     * Get remaining session time in seconds
     * 
     * @return int - Seconds remaining
     */
    public static function getSessionTimeRemaining() {
        if (!isset($_SESSION['login_time'])) {
            return 0;
        }
        
        $session_age = time() - $_SESSION['login_time'];
        $remaining = self::SESSION_DURATION - $session_age;
        
        return max(0, $remaining);
    }
    
    /**
     * Get role name from role ID
     * 
     * @param int $role_id - Role ID
     * @return string - Role name
     */
    private static function getRoleName($role_id) {
        $role_id = intval($role_id);
        
        switch ($role_id) {
            case 1:
                return 'admin';
            case 2:
                return 'sales_manager';
            case 3:
                return 'customer';
            default:
                return 'unknown';
        }
    }
}
?>
