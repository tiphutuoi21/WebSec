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
            
            // Check name
            if (!empty($name)) {
                $name_parts = explode(' ', $name);
                foreach ($name_parts as $part) {
                    if (strlen($part) >= 3 && strpos($password_lower, strtolower($part)) !== false) {
                        return ['valid' => false, 'message' => 'Mật khẩu không được chứa tên của bạn'];
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
            
            // Check city
            if (!empty($city) && strlen($city) >= 3 && strpos($password_lower, $city) !== false) {
                return ['valid' => false, 'message' => 'Mật khẩu không được chứa tên thành phố của bạn'];
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
            header('location: admin310817.php');
            exit();
        }
    }
    
    /**
     * Check if user is logged in (for protected pages)
     * If not, redirect to login
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('location: login.php');
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
            header('location: login.php?reason=session_expired');
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
}
?>
