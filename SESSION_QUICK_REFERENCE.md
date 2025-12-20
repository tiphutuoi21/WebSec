# Session Security - Quick Reference Guide

## 🚀 Quick Start

### 1. Set Up Database Tables (One-time)
```
Visit in browser: http://localhost/LifestyleStore/verify_session_setup.php
This will create the necessary tables automatically
```

### 2. Verify Installation
```
Visit in browser: http://localhost/LifestyleStore/verify_session_setup.php
Should show:
✓ sessions table created
✓ session_audit_log table created
```

### 3. Test Session Flow
```
1. Clear all cookies
2. Login as customer → products.php loads
3. Logout → session marked inactive
4. Try accessing protected page → redirects to login
```

---

## 📝 Code Snippets for Your Pages

### Add Session Validation to Protected Pages
```php
<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Validates: (1) User logged in? (2) Session not expired?
    SecurityHelper::validateSessionTimeout($con);
    
    // Now safe to use session variables
    $user_id = SecurityHelper::getUserId();
?>
```

### Show Session Expiry Warning
```php
<?php
    if (SecurityHelper::isSessionExpiringSoon()) {
        $minutes_left = SecurityHelper::getSessionTimeRemainingMinutes();
        echo "<div class='alert alert-warning'>
              Your session expires in $minutes_left minutes
              </div>";
    }
?>
```

### Log Security Events
```php
<?php
    SecurityHelper::logSecurityEvent($con, 'action_name', 'Optional details');
    // Examples:
    // SecurityHelper::logSecurityEvent($con, 'profile_updated', 'Email changed');
    // SecurityHelper::logSecurityEvent($con, 'file_downloaded', 'document.pdf');
?>
```

### Get User's Active Sessions
```php
<?php
    $sessions = SessionManager::getUserActiveSessions($con, $user_id);
    foreach ($sessions as $session) {
        echo "IP: " . $session['ip_address'] . 
             " | Time: " . $session['login_time'];
    }
?>
```

---

## 🔑 Session Security Features at a Glance

| Feature | Benefit | Status |
|---------|---------|--------|
| 256-bit cryptographic session IDs | Impossible to guess | ✅ Active |
| 30-minute session timeout | Auto-logout | ✅ Active |
| One session per user | Old logins invalidated | ✅ Active |
| HTTPOnly cookies | XSS protection | ✅ Active |
| Secure cookies | HTTPS only | ✅ Active |
| SameSite=Strict | CSRF protection | ✅ Active |
| Session audit logging | Forensics trail | ✅ Active |
| IP tracking | Suspicious activity detection | ✅ Active |
| Activity logging | User behavior tracking | ✅ Active |

---

## 🔍 SQL Queries for Monitoring

### See Who's Currently Logged In
```sql
SELECT user_id, user_email, ip_address, login_time, last_activity 
FROM sessions 
WHERE is_active = 1 
ORDER BY login_time DESC;
```

### View Specific User's Activity
```sql
SELECT action, details, ip_address, timestamp 
FROM session_audit_log 
WHERE user_id = 5 
ORDER BY timestamp DESC 
LIMIT 20;
```

### Find Suspicious Activity (Multiple Logins)
```sql
SELECT user_id, COUNT(*) as login_count, 
       MIN(login_time) as first_login,
       MAX(login_time) as last_login
FROM sessions
WHERE login_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY user_id
HAVING COUNT(*) > 3;
```

### View Failed Login Attempts
```sql
SELECT user_id, action, details, ip_address, timestamp 
FROM session_audit_log 
WHERE action LIKE '%failed%' 
ORDER BY timestamp DESC 
LIMIT 10;
```

### Clean Up Old Sessions (Run Weekly)
```sql
DELETE FROM sessions 
WHERE is_active = 0 
AND logged_out_time < DATE_SUB(NOW(), INTERVAL 7 DAYS);
```

---

## ⏱️ Session Duration

- **Login Time:** Recorded when user logs in
- **Expiry Time:** 30 minutes from login
- **Check:** Happens on every page access
- **Auto-Logout:** Automatic redirect when expired
- **Message:** User redirected to `login.php?reason=session_expired`

---

## 🔓 Logout Process

When user clicks logout:
1. Get user_id from $_SESSION
2. Mark session as inactive in database
3. Log logout event with IP and timestamp
4. Clear all session variables
5. Delete session cookie
6. Redirect to login page

Result: **User fully logged out**, session data preserved for audit trail

---

## 🛡️ Protection Against

✅ **Session Fixation** - New ID regenerated on login
✅ **Session Hijacking** - 30-minute timeout + IP tracking
✅ **Brute Force** - Cryptographic IDs impossible to guess
✅ **Concurrent Abuse** - Only 1 session per user
✅ **Cookie Theft** - HTTPOnly + Secure flags
✅ **CSRF Attacks** - SameSite=Strict flag
✅ **Timeout Bypass** - Server-side validation
✅ **Activity Concealment** - Complete audit trail

---

## 📊 Database Tables

### sessions
Stores active/inactive user sessions
- Shows: who is logged in, from where, when
- Tracks: login_time, last_activity, IP address, device info

### session_audit_log
Stores complete activity history
- Shows: what actions users performed, when, from where
- Tracks: login attempts, logouts, security events

---

## 🚨 Common Issues & Fixes

### Issue: "Session Invalid" on every page
**Solution:** Make sure `connection.php` is included before any HTML
```php
<?php
    require 'connection.php';  // Must be FIRST
    // ... rest of code
?>
```

### Issue: Session not timing out after 30 minutes
**Solution:** Check that `validateSessionTimeout()` is called on protected pages
```php
SecurityHelper::validateSessionTimeout($con);
```

### Issue: Can stay logged in from multiple devices
**Solution:** This is now fixed - only 1 session per user allowed

### Issue: No audit log entries
**Solution:** Check that security events are being logged
```php
SecurityHelper::logSecurityEvent($con, 'event_name', 'details');
```

---

## 📈 Performance Considerations

- **sessions table:** 1 row per login/logout, indexed by user_id
- **session_audit_log table:** 1 row per action, indexed by user_id and timestamp
- **Auto-cleanup:** Old sessions deleted after 7 days
- **Query cost:** Minimal (simple indexed lookups)

**Recommendation:** Archive audit logs monthly for compliance

---

## 🔐 Security Levels Achieved

| Level | Description | Status |
|-------|-------------|--------|
| 🟢 Basic | Login/Logout | ✅ |
| 🟢 Intermediate | Session timeout | ✅ |
| 🟢 Intermediate | Audit logging | ✅ |
| 🟡 Advanced | One session per user | ✅ |
| 🟡 Advanced | Cryptographic IDs | ✅ |
| 🟡 Advanced | IP tracking | ✅ |
| 🔴 Enterprise | 2FA | Not yet |
| 🔴 Enterprise | Anomaly detection | Not yet |

---

## 📞 Support

For issues or questions:
1. Check [SESSION_SECURITY_GUIDE.md](SESSION_SECURITY_GUIDE.md) for detailed docs
2. Run [verify_session_setup.php](verify_session_setup.php) to check setup
3. Review database tables with SQL queries above
4. Check session_audit_log for activity tracking

---

## 🎓 Learning Resources

- [SESSION_SECURITY_GUIDE.md](SESSION_SECURITY_GUIDE.md) - Full documentation
- [SESSION_SECURITY_IMPLEMENTATION.md](SESSION_SECURITY_IMPLEMENTATION.md) - Implementation details
- [SESSION_SECURITY_CHECKLIST.md](SESSION_SECURITY_CHECKLIST.md) - Verification checklist
- [ACCESS_CONTROL_GUIDE.md](ACCESS_CONTROL_GUIDE.md) - Access control documentation

---

**Last Updated:** December 18, 2025
**Status:** ✅ Production Ready
**Security Level:** Intermediate-Advanced
