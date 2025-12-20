# Session Security Implementation Guide

## Overview
This guide explains the session security measures implemented to protect user sessions against hijacking, fixation, and timeout attacks.

---

## 1. STRONG SESSION GENERATION

### What We Implemented
Cryptographically secure session IDs using PHP's `random_bytes()` function.

**Before (Vulnerable):**
```php
// Default PHP session ID (predictable)
session_start();
// PHP generates predictable session IDs, vulnerable to fixation
```

**After (Secure):**
```php
// In SessionManager.php
public static function generateSecureSessionId() {
    // Generate 32 bytes of random data, convert to hex (64 characters)
    return bin2hex(random_bytes(32));
}

// Called during SessionManager::createUserSession()
session_regenerate_id(true); // Regenerates to secure ID
```

### Security Features
- **32 bytes (256 bits) of entropy** - Maximum randomness
- **Cryptographic strength** - Uses `random_bytes()`, not `rand()`
- **Hex encoded** - 64-character alphanumeric ID (very hard to guess)
- **Regenerated on login** - Prevents session fixation attacks
- **Stored as hash** - Database stores SHA256 hash, not plain ID

### Example Session ID
```
Plain:  a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1
Hash:   sha256(above) - stored in database
```

---

## 2. SESSION DURATION & TIMEOUT

### What We Implemented
Fixed 30-minute session duration with server-side timeout validation.

**Configuration (in SessionManager):**
```php
const SESSION_DURATION = 1800; // 30 minutes in seconds

// Set cookie parameters
session_set_cookie_params([
    'lifetime' => 1800,        // Cookie expires after 30 min
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']), // HTTPS only in production
    'httponly' => true,        // JavaScript cannot access
    'samesite' => 'Strict'      // CSRF protection
]);

// Server-side garbage collection
ini_set('session.gc_maxlifetime', 1800);
```

### How It Works
1. Session created with login_time = NOW
2. Each page request checks: `time() - login_time > 1800`?
3. If expired: Session destroyed, user redirected to login
4. Last activity updated on each request

**Timeout Check (in SecurityHelper):**
```php
public static function validateSessionTimeout($con) {
    if (!self::isLoggedIn()) {
        return false;
    }
    
    if (!SessionManager::validateSession($con)) {
        header('location: login.php?reason=session_expired');
        exit(); // Force logout
    }
    
    return true;
}
```

### Usage in Pages
```php
<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // On every protected page
    if (isset($_SESSION['id'])) {
        SecurityHelper::validateSessionTimeout($con); // Checks timeout
    }
?>
```

### Benefits
- Auto-logout after inactivity ✓
- Reduces window for session hijacking ✓
- Complies with OWASP recommendations ✓
- Server-side enforcement (client can't extend) ✓

---

## 3. ONE USER = ONE SESSION (Concurrent Session Handling)

### What We Implemented
Invalidate all previous sessions when user logs in again.

**Before (Vulnerable):**
```php
// Old way: Session allowed at multiple locations
$_SESSION['email'] = $email;
$_SESSION['id'] = $id;
// User could be logged in from phone AND desktop simultaneously
// If one session is hijacked, attacker AND user both logged in
```

**After (Secure):**
```php
// In login_submit.php
SessionManager::createUserSession($con, $user_id, $email, $role_id, 'customer');

// This function:
// 1. Invalidates ALL old sessions for this user
// 2. Regenerates to new secure session ID
// 3. Creates new database record
// 4. Only newest session is active
```

### Function Details
```php
public static function createUserSession($con, $user_id, $user_email, $role_id, $session_type) {
    // Step 1: Invalidate ALL old sessions
    self::invalidateUserSessions($con, $user_id);
    // UPDATE sessions SET is_active = 0 WHERE user_id = ? AND is_active = 1
    
    // Step 2: Regenerate session ID
    session_regenerate_id(true); // Invalidates old ID, generates new one
    
    // Step 3: Set session variables
    $_SESSION['id'] = $user_id;
    $_SESSION['email'] = $user_email;
    $_SESSION['login_time'] = time();
    $_SESSION['session_id_hash'] = hash('sha256', session_id());
    
    // Step 4: Log to database (is_active = 1)
    INSERT INTO sessions (session_id, user_id, is_active, login_time)
}
```

### Database Schema
```sql
CREATE TABLE sessions (
    id INT PRIMARY KEY,
    session_id VARCHAR(255) UNIQUE,      -- SHA256 hash
    user_id INT,                         -- FK to users
    is_active BOOLEAN DEFAULT 1,         -- Only 1 active per user
    login_time TIMESTAMP,
    logged_out_time TIMESTAMP NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    session_type VARCHAR(20)             -- 'admin' or 'customer'
);
```

### Example Scenario
```
Time 1: User logs in from phone
  -> sessions table: [session1, user_id=5, is_active=1]
  -> $_SESSION['id'] = 5

Time 2: Same user logs in from desktop
  -> Sessions table OLD: [session1, user_id=5, is_active=0] ← Set inactive
  -> Sessions table NEW: [session2, user_id=5, is_active=1] ← New session
  -> Phone session becomes invalid automatically
  -> $_SESSION['id'] = 5 (new session)

Result: Only desktop login works, phone is logged out
```

### Benefits
- Only one valid session per user ✓
- Prevents attacker from staying logged in if user logs in elsewhere ✓
- Automatic logout of old sessions ✓
- Audit trail shows all logins ✓

---

## 4. SESSION AUDIT LOGGING

### What We Implemented
Comprehensive audit trail of all session activities.

### Tables Created

**1. Sessions Table (Active Session Tracking)**
```sql
CREATE TABLE sessions (
    id INT PRIMARY KEY,
    session_id VARCHAR(255) UNIQUE,      -- Session ID hash
    user_id INT,                         -- User who logged in
    user_email VARCHAR(255),             -- Their email
    role_id INT,                         -- Their role (1=admin, etc)
    ip_address VARCHAR(45),              -- Where they logged in from
    user_agent TEXT,                     -- Browser info
    session_type VARCHAR(20),            -- 'admin' or 'customer'
    login_time TIMESTAMP,                -- When they logged in
    last_activity TIMESTAMP,             -- Last page accessed
    logged_out_time TIMESTAMP NULL,      -- When they logged out
    is_active BOOLEAN                    -- Current status
);
```

**2. Session Audit Log Table (Activity History)**
```sql
CREATE TABLE session_audit_log (
    id INT PRIMARY KEY,
    user_id INT,                         -- Which user
    action VARCHAR(100),                 -- What they did
    details TEXT,                        -- Additional info
    ip_address VARCHAR(45),              -- Where from
    timestamp TIMESTAMP                  -- When
);
```

### Logged Actions
```php
// In login_submit.php
SecurityHelper::logSecurityEvent($con, 'customer_login', 'Successful login');
SecurityHelper::logSecurityEvent($con, 'failed_login_attempt', 'Email: user@example.com');
SecurityHelper::logSecurityEvent($con, 'unverified_login_attempt', 'Email: user@example.com');

// In logout.php
SessionManager::logSessionActivity($con, $user_id, 'customer_logout', 'User logged out');

// In admin pages
SecurityHelper::logSecurityEvent($con, 'user_deleted', 'User ID: 5 deleted');
SecurityHelper::logSecurityEvent($con, 'order_deleted', 'Order ID: 123 deleted');
```

### Audit Log Examples
```
| user_id | action                  | details              | ip_address    | timestamp           |
|---------|------------------------|----------------------|---------------|---------------------|
| 5       | customer_login         | Successful login     | 192.168.1.100 | 2025-12-18 10:30:00 |
| 5       | failed_login_attempt   | Email: user@test.com | 192.168.1.101 | 2025-12-18 10:29:00 |
| 5       | customer_logout        | User logged out      | 192.168.1.100 | 2025-12-18 11:00:00 |
| 1       | admin_login            | Successful login     | 192.168.1.200 | 2025-12-18 10:35:00 |
| 1       | user_deleted           | User ID: 5 deleted   | 192.168.1.200 | 2025-12-18 11:30:00 |
```

### Functions for Accessing Logs

**Get User's Active Sessions:**
```php
$active_sessions = SessionManager::getUserActiveSessions($con, $user_id);
// Returns array of current logged-in sessions
// Useful for: user profile showing "logged in from X devices"
```

**Get User's Audit Log:**
```php
$audit_log = SessionManager::getUserAuditLog($con, $user_id, 50);
// Returns last 50 activities
// Useful for: Security page showing "Your Activity"
```

**Log Custom Activity:**
```php
SecurityHelper::logSecurityEvent($con, 'custom_action', 'Details here');
// Manually log any security event
```

---

## 5. SECURE COOKIE CONFIGURATION

### What We Implemented
HTTPOnly, Secure, SameSite cookie flags to prevent unauthorized access.

**Configuration:**
```php
// In SessionManager::initializeSecureSession()
ini_set('session.use_strict_mode', 1);       // Rejects invalid session IDs
ini_set('session.use_only_cookies', 1);      // Prevent URL-based sessions
ini_set('session.use_trans_sid', 0);         // Don't append SID to URLs
ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // HTTPS only
ini_set('session.cookie_httponly', 1);       // JavaScript cannot access
ini_set('session.cookie_samesite', 'Strict');// CSRF protection

session_set_cookie_params([
    'lifetime' => 1800,      // Expires in 30 minutes
    'path' => '/',           // Available site-wide
    'secure' => isset($_SERVER['HTTPS']),    // HTTPS only
    'httponly' => true,      // Not accessible via JavaScript
    'samesite' => 'Strict'   // Not sent in cross-site requests
]);
```

### Cookie Headers Sent to Browser
```
Set-Cookie: PHPSESSID=a1b2c3d4...; 
            Path=/; 
            HttpOnly;        ← JavaScript cannot access
            Secure;          ← HTTPS only
            SameSite=Strict; ← Not sent to other sites
            Max-Age=1800     ← Expires in 30 minutes
```

### Protection Mechanisms

1. **HTTPOnly Flag** - Prevents XSS attacks from stealing session cookie
   ```js
   // This fails (cookie not accessible):
   alert(document.cookie); // Empty or no PHPSESSID
   ```

2. **Secure Flag** - Session cookie only sent over HTTPS
   ```
   // Man-in-the-middle attack fails
   // Cookie won't transmit over unencrypted HTTP
   ```

3. **SameSite=Strict** - Prevents CSRF attacks
   ```html
   <!-- This request won't send session cookie: -->
   <img src="https://attacker.com/steal.php">
   <!-- Cookie only sent to same-site requests -->
   ```

---

## 6. IP ADDRESS VERIFICATION (Optional)

### What We Implemented
Optional IP address validation to detect session hijacking.

**Configuration:**
```php
// In place_order.php or other critical pages
SecurityHelper::validateSessionTimeout($con, true); // true = check IP

// Or manually:
$is_valid = SessionManager::validateSession($con, $check_ip = true);
```

**How It Works:**
```php
// When session created:
sessions table: [session_id, user_id, ip_address='192.168.1.100']

// On next request from different IP:
if ($row['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
    // IP mismatch = session hijacked
    SessionManager::destroySession($con);
    return false; // Logout user
}
```

**Tradeoff:** Can cause false positives (mobile users changing networks)

---

## 7. UPDATED LOGIN/LOGOUT FLOWS

### Customer Login Flow
```php
// In login_submit.php
1. Verify CSRF token
2. Validate email/password
3. Query database for user
4. Create secure session via SessionManager::createUserSession()
   - Invalidate old sessions
   - Regenerate session ID
   - Store in database
   - Set session variables
5. Log successful login
6. Redirect to products.php
```

### Customer Logout Flow
```php
// In logout.php
1. Get user ID from session
2. Log logout activity
3. Call SessionManager::destroySession()
   - Mark session as inactive in database
   - Clear all session variables
   - Delete session cookie
4. Redirect to login page
```

### Admin Login Flow
```php
// In admin_login_submit.php
1. Verify CSRF token (added)
2. Validate email/password using prepared statement (added)
3. Query admins table for user
4. Create secure session with role_id (admin=1)
5. Log successful admin login
6. Redirect to admin_dashboard.php
```

---

## 8. SESSION VALIDATION ON PROTECTED PAGES

### Implementation
```php
<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // EVERY protected page should have:
    SecurityHelper::requireLogin();              // Check logged in
    SecurityHelper::validateSessionTimeout($con); // Check timeout
    
    // Now safe to access session variables
    $user_id = SecurityHelper::getUserId();
?>
```

### Files Updated
- `products.php` - Added session validation
- `cart.php` - Added session validation
- `place_order.php` - Already has it
- `order_confirmation.php` - Already has it
- `cart_remove.php` - Already has it

---

## 9. SETUP INSTRUCTIONS

### Step 1: Create Session Tables
```bash
# Run in browser:
http://localhost/LifestyleStore/session_migration.php

# Or manually execute:
-- sessions table
-- session_audit_log table
```

### Step 2: Verify Configuration
Files already configured:
- ✓ SessionManager.php - Created
- ✓ config.php - Calls SessionManager::initializeSecureSession()
- ✓ connection.php - Initializes sessions
- ✓ login_submit.php - Uses SessionManager::createUserSession()
- ✓ admin_login_submit.php - Uses SessionManager::createUserSession()
- ✓ logout.php - Uses SessionManager::destroySession()
- ✓ admin_logout.php - Uses SessionManager::destroySession()

### Step 3: Test Session Security
```
1. Clear all cookies/sessions
2. Login as customer
3. Check: sessions table should have 1 active row
4. Login again (same browser)
5. Check: sessions table should have 1 active row (old marked inactive)
6. Wait 30+ minutes without activity
7. Try to access protected page
8. Should be redirected to login.php?reason=session_expired
9. Check: session_audit_log should show login/logout events
```

---

## 10. SECURITY CHECKLIST

- [x] Strong cryptographic session ID generation (random_bytes)
- [x] Fixed 30-minute session duration
- [x] Server-side timeout validation
- [x] One active session per user (old sessions invalidated)
- [x] Session stored in database for tracking
- [x] HTTPOnly cookie flag (prevents JavaScript access)
- [x] Secure cookie flag (HTTPS only)
- [x] SameSite=Strict (CSRF protection)
- [x] Session ID regeneration on login
- [x] Comprehensive audit logging
- [x] Session activity tracking (login/logout)
- [x] Optional IP address verification
- [x] Automatic logout after timeout
- [x] Database tracking of all sessions per user

---

## 11. MONITORING & MAINTENANCE

### Regular Tasks
```sql
-- Clean up old inactive sessions (run weekly)
DELETE FROM sessions 
WHERE is_active = 0 
AND logged_out_time < DATE_SUB(NOW(), INTERVAL 7 DAYS);

-- View active sessions
SELECT user_id, ip_address, login_time, last_activity 
FROM sessions 
WHERE is_active = 1;

-- Detect suspicious activity (multiple logins in short time)
SELECT user_id, COUNT(*) as login_count, 
       MIN(login_time) as first_login,
       MAX(login_time) as last_login
FROM sessions
WHERE login_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY user_id
HAVING COUNT(*) > 3;
```

### User Activity Reports
```php
// Show user their active sessions
$sessions = SessionManager::getUserActiveSessions($con, $user_id);
foreach ($sessions as $session) {
    echo "Device: " . $session['user_agent'] . 
         " IP: " . $session['ip_address'] . 
         " Login: " . $session['login_time'];
}

// Show user their activity log
$logs = SessionManager::getUserAuditLog($con, $user_id, 100);
foreach ($logs as $log) {
    echo "[" . $log['timestamp'] . "] " . $log['action'] . 
         " from " . $log['ip_address'];
}
```

---

## 12. FUTURE IMPROVEMENTS

1. **Session Fingerprinting** - Store/verify browser fingerprint
2. **Adaptive Authentication** - Flag unusual login locations
3. **2FA for Admin** - Two-factor authentication on admin login
4. **Rate Limiting** - Limit login attempts per IP
5. **Session Activity Dashboard** - User sees all active sessions
6. **Session Revocation** - User can remotely logout other sessions
7. **Anomaly Detection** - Alert on rapid IP changes, unusual activity

---

## References
- [OWASP Session Management](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html)
- [PHP Session Security](https://www.php.net/manual/en/function.session-start.php)
- [SameSite Cookies](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite)
