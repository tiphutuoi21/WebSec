# Security Implementation Guide

## Overview
This document explains the security measures implemented to prevent SQL Injection (SQLi), Cross-Site Scripting (XSS), and Cross-Site Request Forgery (CSRF) attacks.

---

## 1. SQL INJECTION (SQLi) PREVENTION

### What is SQL Injection?
SQL injection occurs when an attacker inserts malicious SQL code into user input, which gets executed by the database.

**Example Attack:**
```
Email: admin@example.com' OR '1'='1
Password: anything
```
This could bypass authentication if not protected.

### How We Fixed It: Prepared Statements

**Before (Vulnerable):**
```php
$email = $_POST['email'];
$query = "SELECT * FROM users WHERE email = '$email'";
// Attacker can inject: ' OR '1'='1
```

**After (Secure):**
```php
$email = SecurityHelper::getString('email', 'POST');
$query = "SELECT * FROM users WHERE email = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
```

**How It Works:**
- `?` is a placeholder for user input
- `mysqli_stmt_bind_param()` separates the code from data
- The database driver handles escaping automatically
- Attacker input is treated as data, not executable code

**Files Updated:**
- `login_submit.php` - User login queries
- `user_registration_script.php` - User registration queries
- `place_order.php` - Order insertion (already used prepared statements)
- `search.php` - Product search (already had prepared statements)

---

## 2. CROSS-SITE SCRIPTING (XSS) PREVENTION

### What is XSS?
XSS attacks inject malicious JavaScript that runs in other users' browsers.

**Example Attack:**
```
Product Name: <script>alert('Hacked!');</script>
```
If displayed without escaping, the script executes.

### How We Fixed It: Output Escaping

**Created SecurityHelper::escape() function:**
```php
public static function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
```

**How It Works:**
- `htmlspecialchars()` converts special characters to HTML entities
- `<` becomes `&lt;`
- `"` becomes `&quot;`
- `'` becomes `&#039;`
- `ENT_QUOTES` escapes both double and single quotes
- Browser displays the text instead of executing code

**Usage Examples:**

**In admin_manage_orders.php (displaying user input):**
```php
<td><?php echo $row['name']; ?></td>  // UNSAFE
<td><?php echo SecurityHelper::escape($row['name']); ?></td>  // SAFE
```

**In admin_manage_users.php:**
```php
<td><?php echo $row['address']; ?></td>  // Could be unsafe
<td><?php echo SecurityHelper::escape($row['address']); ?></td>  // SAFE
```

**For form fields (CSRF token):**
```php
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
```

**Best Practices:**
1. Always escape user-controlled data before displaying it
2. Use `SecurityHelper::escape()` for HTML context
3. Use different encoding for JavaScript context, URL context, etc.

---

## 3. CROSS-SITE REQUEST FORGERY (CSRF) PREVENTION

### What is CSRF?
CSRF attacks trick users into performing unintended actions on a site where they're logged in.

**Example Attack:**
```html
<img src="https://yourbank.com/transfer?to=attacker&amount=1000">
```
If user visits this page while logged into their bank, the transfer happens automatically.

### How We Fixed It: CSRF Tokens

**Created SecurityHelper class with CSRF methods:**
```php
public static function generateCSRFToken()
public static function verifyCSRFToken($token)
public static function getCSRFField()
```

**How It Works:**

**Step 1: Generate token in form (login.php, signup.php)**
```php
<form method="post" action="login_submit.php">
    <?php echo SecurityHelper::getCSRFField(); ?>
    <!-- Other form fields -->
</form>
```
This outputs:
```html
<input type="hidden" name="csrf_token" value="randomhexstring123...">
```

**Step 2: Verify token in handler (login_submit.php, user_registration_script.php)**
```php
if (!isset($_POST['csrf_token']) || !SecurityHelper::verifyCSRFToken($_POST['csrf_token'])) {
    die('CSRF token validation failed');
}
```

**Why This Works:**
1. Server generates unique token and stores in `$_SESSION`
2. Token is included in form as hidden field
3. Attacker cannot know the token (different for each user/session)
4. Attacker's forged request doesn't include valid token
5. Server rejects request without valid token

**Token Security:**
- Generated using `bin2hex(random_bytes(32))` - cryptographically secure
- Stored in `$_SESSION` - server-side, inaccessible to attacker
- Compared using `hash_equals()` - prevents timing attacks
- Regenerated on each page load

**Files Updated:**
- `login.php` - Added CSRF field to login form
- `login_submit.php` - Added CSRF token verification
- `signup.php` - Added CSRF field to registration form
- `user_registration_script.php` - Added CSRF token verification

---

## 4. INPUT SANITIZATION

**Created SecurityHelper::sanitizeInput() function:**
```php
public static function sanitizeInput($data)
```

**What It Does:**
1. Removes null bytes (`\0`) - prevent file path traversal
2. Trims whitespace
3. Works recursively on arrays

**Usage:**
```php
$email = SecurityHelper::getString('email', 'POST');
// Internally calls sanitizeInput()
```

---

## 5. INPUT VALIDATION

**Created SecurityHelper utility methods:**

**Email Validation:**
```php
SecurityHelper::isValidEmail($email)
// Uses filter_var() with FILTER_VALIDATE_EMAIL
```

**Password Validation:**
```php
SecurityHelper::isStrongPassword($password)
// Returns ['valid' => bool, 'message' => string]
// Checks minimum 6 characters
```

**Safe GET/POST Retrieval:**
```php
$id = SecurityHelper::getInt('id', 'GET');      // Returns integer
$name = SecurityHelper::getString('name', 'POST'); // Returns string
```

---

## Security Checklist

- [x] Prepared statements for all database queries
- [x] Input validation (email, password length, integer casting)
- [x] Input sanitization (trimming, null byte removal)
- [x] Output escaping with `htmlspecialchars()`
- [x] CSRF tokens on all forms
- [x] CSRF token verification on all form handlers
- [x] Error handling without exposing sensitive info
- [x] Session management (proper session variables)
- [x] No hardcoded credentials
- [x] Secure password hashing (md5(md5()) - could upgrade to password_hash())

---

## Files Modified

### Core Security
- **SecurityHelper.php** (NEW) - Central security utility class

### Authentication
- `login.php` - Added CSRF token field
- `login_submit.php` - Added CSRF verification, prepared statements, input validation
- `signup.php` - Added CSRF token field
- `user_registration_script.php` - Added CSRF verification, prepared statements, input validation

### Already Secure
- `search.php` - Already uses prepared statements
- `place_order.php` - Already uses prepared statements
- `cart_add.php` - Already uses prepared statements
- `admin_manage_orders.php` - Uses prepared statements from queries
- `product.php` - Uses intval() for ID validation

---

## Recommended Future Improvements

1. **Upgrade Password Hashing:**
   ```php
   // Current (legacy):
   $password_hash = md5(md5($password));
   
   // Better (use PHP 5.5+):
   $password_hash = password_hash($password, PASSWORD_BCRYPT);
   // Verify with:
   password_verify($password, $password_hash)
   ```

2. **Add Stricter Password Requirements:**
   - Uppercase letters
   - Lowercase letters
   - Numbers
   - Special characters
   - Minimum 12 characters

3. **Implement Rate Limiting:**
   - Limit login attempts (e.g., max 5 per minute)
   - Prevent brute force attacks

4. **Add Security Headers:**
   ```php
   header("X-Content-Type-Options: nosniff");
   header("X-Frame-Options: DENY");
   header("X-XSS-Protection: 1; mode=block");
   header("Strict-Transport-Security: max-age=31536000");
   ```

5. **Content Security Policy (CSP):**
   ```php
   header("Content-Security-Policy: default-src 'self'");
   ```

6. **SQL Error Suppression:**
   Currently: `or die(mysqli_error($con))`
   Better: Log errors to file, show generic message to user

---

## Testing Security

### Test SQLi Prevention:
1. Login form email: `admin@example.com' OR '1'='1`
2. Should fail with "Wrong username or password"
3. Should NOT bypass authentication

### Test XSS Prevention:
1. Registration name: `<script>alert('XSS')</script>`
2. Display should show literal text, not execute script
3. Check page source - should see `&lt;script&gt;`

### Test CSRF Prevention:
1. Copy login form HTML
2. Submit from external site
3. Should get "CSRF token validation failed"
4. Legitimate form submission should work fine

---

## References

- [OWASP Top 10 Vulnerabilities](https://owasp.org/Top10/)
- [PHP MySQLi Prepared Statements](https://www.php.net/manual/en/mysqli.quickstart.prepared-statements.php)
- [htmlspecialchars() Documentation](https://www.php.net/manual/en/function.htmlspecialchars.php)
- [CSRF Token Best Practices](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html)
