# System Verification & Setup Checklist

## CRITICAL FIRST STEP: Database Migration

Before doing anything else, you MUST run the database migration script to create all required tables.

### Step 1: Run Database Migration
1. Open your browser
2. Go to: `http://localhost/LifestyleStore/database_migration.php`
3. You should see a green success message with all tables listed
4. If you see any errors, copy the error and check below for troubleshooting

---

## Database Verification Checklist

After running the migration, verify all tables exist:

### Open phpMyAdmin → LifestyleStore Database

**Check these tables exist:**
- [ ] `roles` - Should have 3 rows (id: 1=admin, 2=sales_manager, 3=customer)
- [ ] `order_statuses` - Should have 7 rows (pending, confirmed, processing, shipped, delivered, cancelled, returned)
- [ ] `admins` - Should have `role_id` INT column (FK to roles.id) - NOT an enum named `role`
- [ ] `cart_items` - Shopping cart (user_id, item_id, quantity)
- [ ] `orders` - Confirmed orders (user_id, total_amount, status_id)
- [ ] `order_items` - Order details (order_id, item_id, quantity, unit_price, subtotal)
- [ ] `items` - Product catalog
- [ ] `users` - Customer accounts
- [ ] `users_items` - Legacy (should still exist for backward compatibility, but not used)

### Verify Admin Role Setup

**In phpMyAdmin, run this SQL:**
```sql
-- Check roles table
SELECT * FROM roles;
-- Should return 3 rows

-- Check admin account
SELECT id, email, role_id FROM admins WHERE email = 'admin@lifestylestore.com';
-- role_id should be 1 (if not, run the UPDATE below)

-- IF admin doesn't have role_id = 1, run this:
UPDATE admins SET role_id = 1 WHERE email = 'admin@lifestylestore.com';

-- IF you have a sales manager account, set role_id = 2:
UPDATE admins SET role_id = 2 WHERE email = 'sales@lifestylestore.com';
```

---

## Admin Privileges Test

### Test 1: Admin Login & Full Permissions
1. Go to: `http://localhost/LifestyleStore/admin310817.php`
2. Login with: `admin@lifestylestore.com` / `[your password]`
3. Check dashboard shows: "Role: admin" with red label
4. Verify you see all permissions listed:
   - [ ] View Users
   - [ ] View Orders
   - [ ] Add Products
   - [ ] Edit Products
   - [ ] Delete Products
   - [ ] Delete Users
   - [ ] Delete Orders

### Test 2: Manage Orders
1. Click "Manage Orders" in admin dashboard
2. Should show a table with these columns:
   - [ ] Order ID
   - [ ] Customer Name
   - [ ] Email
   - [ ] Product
   - [ ] Quantity
   - [ ] Unit Price
   - [ ] Subtotal
   - [ ] Status (should show color-coded badges)
   - [ ] Order Date
   - [ ] Action (Delete button - only visible to admins)

**Expected:** You should see a "Delete" button for each order

### Test 3: Manage Users
1. Click "Manage Users"
2. Should show user table with:
   - [ ] ID, Name, Email, Contact, City, Address columns
   - [ ] Delete button visible (since role_id = 1)

### Test 4: Role Restriction
If you create a sales_manager account:
1. Update admin role: `UPDATE admins SET role_id = 2 WHERE email = 'sales@lifestylestore.com';`
2. Login as sales manager
3. Go to "Manage Orders" → Should see message: "Only Admin role can delete orders"
4. Delete button should NOT be visible

---

## Customer Order Flow Test

### Test 5: Customer Adds Items to Cart
1. Logout (or use incognito window)
2. Login as a customer
3. Click a product → "Add to Cart"
4. Verify no errors, redirects to products page
5. Repeat for 2-3 products with different quantities

### Test 6: View Cart
1. Click "Cart" or shopping cart icon
2. Should show:
   - [ ] Item Number, Name, Quantity, Unit Price, Total columns
   - [ ] Each item shows correct quantity and subtotal
   - [ ] Total Amount calculated correctly
   - [ ] "Proceed to Checkout" button visible

### Test 7: Checkout Process (CRITICAL)
1. Click "Proceed to Checkout"
2. Should see "Order Placed Successfully!" message
3. Should show:
   - [ ] Order ID
   - [ ] Order Date
   - [ ] Status (should say "Confirmed")
   - [ ] Total Amount
   - [ ] List of items purchased
4. Should redirect automatically to order confirmation page

### Test 8: Admin Sees Customer Orders
1. Logout customer
2. Login as admin
3. Click "Manage Orders"
4. Should see the order you just created:
   - [ ] Shows customer name
   - [ ] Shows all items ordered
   - [ ] Shows order total
   - [ ] Status shows "Confirmed" (blue label)
5. Admin can click "Delete" to remove order (if role_id = 1)

---

## Common Issues & Solutions

### Issue: "Table doesn't exist" error when adding to cart
**Solution:** Run database_migration.php again
```
http://localhost/LifestyleStore/database_migration.php
```

### Issue: Admin doesn't have delete buttons
**Solution:** Check admin role_id in database
```sql
SELECT role_id FROM admins WHERE email = 'admin@lifestylestore.com';
-- Should return 1, if not run:
UPDATE admins SET role_id = 1 WHERE email = 'admin@lifestylestore.com';
```

### Issue: "Undefined index: admin_role_id" error on admin pages
**Solution:** 
1. Clear browser cookies for localhost
2. Logout
3. Login again to refresh session
4. If still occurs, check admin310817_submit.php is reading role_id correctly

### Issue: Cart items not saving
**Solution:** 
1. Verify user is logged in (check $_SESSION['id'] exists)
2. Verify cart_items table exists: `SHOW TABLES LIKE 'cart_items';`
3. Check browser console for JavaScript errors

### Issue: Checkout doesn't create order
**Solution:**
1. Check orders and order_items tables exist
2. Look in browser developer tools → Network tab → Check checkout.php response
3. Check PHP error logs: `C:\xampp\apache\logs\`

### Issue: "Order Confirmation" page shows "Access Denied"
**Solution:**
1. Don't access order_confirmation.php directly - always go through checkout.php
2. Checkout.php sets the session variables needed for order_confirmation.php

---

## File Changes Summary

### Modified Files (Authorization)
- `admin_dashboard.php` - Fixed role comparison
- `admin_delete_user.php` - Added role check
- `admin_manage_users.php` - Fixed role visibility
- `admin_manage_orders.php` - REWRITTEN: queries new orders table, added role check
- `admin_delete_order.php` - REWRITTEN: new table, added role authorization

### Modified Files (Cart/Orders)
- `cart.php` - Uses new cart_items table
- `cart_add.php` - Uses new cart_items table, prevents duplicates
- `cart_remove.php` - Uses new cart_items table
- `check_if_added.php` - Uses new cart_items table

### New Files (Checkout)
- `checkout.php` - Converts cart to confirmed orders
- `order_confirmation.php` - Shows order details after checkout

### Deprecated/Redirected
- `success.php` - Now redirects to checkout.php

---

## Key Session Variables

After admin login, these are set:
```php
$_SESSION['admin_email']      // Admin email
$_SESSION['admin_id']         // Admin ID  
$_SESSION['admin_role_id']    // Role ID (1, 2, or 3) ← Use this for authorization!
$_SESSION['admin_role']       // Role name (admin, sales_manager, customer)
```

All role checks use: `intval($_SESSION['admin_role_id']) === 1`

---

## Role Permissions Matrix

|  | Admin | Sales Manager | Customer |
|---|-------|---------------|----------|
| View Products | ✓ | ✓ | ✓ |
| View Users | ✓ | ✓ | ✗ |
| Add Users | ✓ | ✗ | ✗ |
| Delete Users | ✓ | ✗ | ✗ |
| Add Products | ✓ | ✓ | ✗ |
| Edit Products | ✓ | ✓ | ✗ |
| Delete Products | ✓ | ✗ | ✗ |
| View Orders | ✓ | ✓ | ✓ (own) |
| Delete Orders | ✓ | ✗ | ✗ |

---

## Final Verification

**Before considering the system "ready":**

- [ ] Database migration ran successfully
- [ ] All 9 required tables exist with correct columns
- [ ] Admin has role_id = 1 in admins table
- [ ] roles table has 3 rows (admin, sales_manager, customer)
- [ ] order_statuses table has 7 rows
- [ ] Admin can login and see all permissions
- [ ] Admin can see "Manage Orders" page without errors
- [ ] Customer can add items to cart
- [ ] Customer can checkout and see order confirmation
- [ ] Admin can see customer orders in "Manage Orders"
- [ ] Admin can delete orders (if role_id = 1)
- [ ] Sales Manager (if created) cannot delete users/orders/products

---

## Support Notes

If you encounter any issues:
1. Check the PHP error logs: `C:\xampp\apache\logs\error.log`
2. Check MySQL error logs: `C:\xampp\mysql\data\`
3. Enable error display in PHP: Set `display_errors = On` in php.ini
4. Check browser console (F12 → Console tab) for JavaScript errors
5. Use phpMyAdmin to verify database structure and data

---

**Last Updated:** After implementing fixes for admin privileges and customer order integration

**Status:** ✓ Ready for testing
