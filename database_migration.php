<?php
    require 'connection.php';
    
    echo "<h2>Database Migration - Schema Redesign</h2>";
    echo "<p>Updating database to follow ecommerce best practices...</p>";
    echo "<hr>";
    
    try {
        // 1. Create roles table
        $roles_check = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='roles'";
        $roles_result = mysqli_query($con, $roles_check);
        
        if (mysqli_num_rows($roles_result) == 0) {
            $create_roles = "CREATE TABLE `roles` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name` varchar(50) NOT NULL UNIQUE,
                `description` varchar(255),
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
            
            if (mysqli_query($con, $create_roles)) {
                echo "<p style='color: green;'><strong>✓ Created roles table</strong></p>";
                
                // Insert default roles
                $insert_roles = "INSERT INTO `roles` (`id`, `name`, `description`) VALUES 
                    (1, 'admin', 'Full administrative access - can delete users, orders, and manage staff'),
                    (2, 'sales_manager', 'Sales management - can manage products and view orders, but cannot delete users or manage staff'),
                    (3, 'customer', 'Regular customer - can browse, search products and make purchases')";
                
                if (mysqli_query($con, $insert_roles)) {
                    echo "<p style='color: green;'><strong>✓ Inserted default roles (admin, sales_manager, customer)</strong></p>";
                } else {
                    echo "<p style='color: orange;'><strong>⚠ Roles table exists, skipping role insertion</strong></p>";
                }
            } else {
                echo "<p style='color: red;'><strong>✗ Error creating roles table: " . mysqli_error($con) . "</strong></p>";
            }
        } else {
            echo "<p style='color: blue;'><strong>ℹ Roles table already exists</strong></p>";
        }
        
        // 2. Create order_statuses table
        $statuses_check = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='order_statuses'";
        $statuses_result = mysqli_query($con, $statuses_check);
        
        if (mysqli_num_rows($statuses_result) == 0) {
            $create_statuses = "CREATE TABLE `order_statuses` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name` varchar(50) NOT NULL UNIQUE,
                `description` varchar(255),
                `color` varchar(20),
                `is_active` boolean DEFAULT 1,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
            
            if (mysqli_query($con, $create_statuses)) {
                echo "<p style='color: green;'><strong>✓ Created order_statuses table</strong></p>";
                
                // Insert default order statuses
                $insert_statuses = "INSERT INTO `order_statuses` (`id`, `name`, `description`, `color`, `is_active`) VALUES 
                    (1, 'pending', 'Item added to cart, awaiting checkout', 'warning', 1),
                    (2, 'confirmed', 'Order has been confirmed and payment received', 'info', 1),
                    (3, 'processing', 'Order is being prepared for shipment', 'primary', 1),
                    (4, 'shipped', 'Order has been dispatched and is in transit', 'success', 1),
                    (5, 'delivered', 'Order has been successfully delivered', 'success', 1),
                    (6, 'cancelled', 'Order has been cancelled by customer or admin', 'danger', 1),
                    (7, 'returned', 'Order has been returned by customer', 'secondary', 1)";
                
                if (mysqli_query($con, $insert_statuses)) {
                    echo "<p style='color: green;'><strong>✓ Inserted default order statuses (7 statuses)</strong></p>";
                } else {
                    echo "<p style='color: orange;'><strong>⚠ Order statuses table exists, skipping status insertion</strong></p>";
                }
            } else {
                echo "<p style='color: red;'><strong>✗ Error creating order_statuses table: " . mysqli_error($con) . "</strong></p>";
            }
        } else {
            echo "<p style='color: blue;'><strong>ℹ Order statuses table already exists</strong></p>";
        }
        
        // 3. Create cart_items table
        $cart_check = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='cart_items'";
        $cart_result = mysqli_query($con, $cart_check);
        
        if (mysqli_num_rows($cart_result) == 0) {
            $create_cart = "CREATE TABLE `cart_items` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `user_id` int(11) NOT NULL,
                `item_id` int(11) NOT NULL,
                `quantity` int(11) NOT NULL DEFAULT 1,
                `added_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
                FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
            
            if (mysqli_query($con, $create_cart)) {
                echo "<p style='color: green;'><strong>✓ Created cart_items table</strong></p>";
            } else {
                echo "<p style='color: red;'><strong>✗ Error creating cart_items table: " . mysqli_error($con) . "</strong></p>";
            }
        } else {
            echo "<p style='color: blue;'><strong>ℹ Cart_items table already exists</strong></p>";
        }
        
        // 4. Create orders table
        $orders_check = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders'";
        $orders_result = mysqli_query($con, $orders_check);
        
        if (mysqli_num_rows($orders_result) == 0) {
            $create_orders = "CREATE TABLE `orders` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `user_id` int(11) NOT NULL,
                `total_amount` decimal(10, 2) NOT NULL,
                `status_id` int(11) NOT NULL DEFAULT 2,
                `notes` text,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
                FOREIGN KEY (`status_id`) REFERENCES `order_statuses` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
            
            if (mysqli_query($con, $create_orders)) {
                echo "<p style='color: green;'><strong>✓ Created orders table</strong></p>";
            } else {
                echo "<p style='color: red;'><strong>✗ Error creating orders table: " . mysqli_error($con) . "</strong></p>";
            }
        } else {
            echo "<p style='color: blue;'><strong>ℹ Orders table already exists</strong></p>";
        }
        
        // 5. Create order_items table
        $order_items_check = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='order_items'";
        $order_items_result = mysqli_query($con, $order_items_check);
        
        if (mysqli_num_rows($order_items_result) == 0) {
            $create_order_items = "CREATE TABLE `order_items` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `order_id` int(11) NOT NULL,
                `item_id` int(11) NOT NULL,
                `quantity` int(11) NOT NULL DEFAULT 1,
                `unit_price` decimal(10, 2) NOT NULL,
                `subtotal` decimal(10, 2) NOT NULL,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
                FOREIGN KEY (`item_id`) REFERENCES `items` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
            
            if (mysqli_query($con, $create_order_items)) {
                echo "<p style='color: green;'><strong>✓ Created order_items table</strong></p>";
            } else {
                echo "<p style='color: red;'><strong>✗ Error creating order_items table: " . mysqli_error($con) . "</strong></p>";
            }
        } else {
            echo "<p style='color: blue;'><strong>ℹ Order_items table already exists</strong></p>";
        }
        
        // 6. Update admins table to use role_id
        $check_role_id = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='admins' AND COLUMN_NAME='role_id' AND TABLE_SCHEMA=DATABASE()";
        $check_role_id_result = mysqli_query($con, $check_role_id);
        
        if (mysqli_num_rows($check_role_id_result) == 0) {
            // Check if old role column exists
            $check_old_role = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='admins' AND COLUMN_NAME='role' AND TABLE_SCHEMA=DATABASE()";
            $check_old_role_result = mysqli_query($con, $check_old_role);
            
            if (mysqli_num_rows($check_old_role_result) > 0) {
                // Migrate from old role enum to role_id
                $migrate_role = "ALTER TABLE admins 
                    DROP COLUMN role, 
                    ADD COLUMN role_id int(11) DEFAULT 2 AFTER password,
                    ADD COLUMN is_active boolean DEFAULT 1,
                    ADD COLUMN last_login datetime,
                    ADD FOREIGN KEY (role_id) REFERENCES roles(id)";
                
                if (mysqli_query($con, $migrate_role)) {
                    echo "<p style='color: green;'><strong>✓ Migrated admins table to use role_id (role_id now references roles table)</strong></p>";
                } else {
                    echo "<p style='color: red;'><strong>✗ Error migrating admins table: " . mysqli_error($con) . "</strong></p>";
                }
            } else {
                // role_id doesn't exist and neither does old role column, add it
                $add_role_id = "ALTER TABLE admins ADD COLUMN role_id int(11) DEFAULT 2 AFTER password, ADD COLUMN is_active boolean DEFAULT 1, ADD COLUMN last_login datetime";
                
                if (mysqli_query($con, $add_role_id)) {
                    echo "<p style='color: green;'><strong>✓ Added role_id column to admins table</strong></p>";
                } else {
                    echo "<p style='color: red;'><strong>✗ Error adding role_id to admins: " . mysqli_error($con) . "</strong></p>";
                }
            }
        } else {
            echo "<p style='color: blue;'><strong>ℹ Admins table already has role_id column</strong></p>";
        }
        
        // 7. Update items table with enhanced fields
        $check_item_sku = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='items' AND COLUMN_NAME='sku' AND TABLE_SCHEMA=DATABASE()";
        $check_item_sku_result = mysqli_query($con, $check_item_sku);
        
        if (mysqli_num_rows($check_item_sku_result) == 0) {
            $alter_items = "ALTER TABLE items 
                MODIFY price decimal(10, 2),
                ADD COLUMN description text AFTER name,
                ADD COLUMN stock_quantity int(11) DEFAULT 0,
                ADD COLUMN sku varchar(50) UNIQUE,
                ADD COLUMN category varchar(100),
                ADD COLUMN is_active boolean DEFAULT 1,
                ADD COLUMN updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
            
            if (mysqli_query($con, $alter_items)) {
                echo "<p style='color: green;'><strong>✓ Enhanced items table with sku, category, stock_quantity, and is_active columns</strong></p>";
            } else {
                echo "<p style='color: red;'><strong>✗ Error enhancing items table: " . mysqli_error($con) . "</strong></p>";
            }
        } else {
            echo "<p style='color: blue;'><strong>ℹ Items table already has enhanced schema</strong></p>";
        }
        
        // 8. Update users table with is_active and updated_at
        $check_user_active = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='users' AND COLUMN_NAME='is_active' AND TABLE_SCHEMA=DATABASE()";
        $check_user_active_result = mysqli_query($con, $check_user_active);
        
        if (mysqli_num_rows($check_user_active_result) == 0) {
            $alter_users = "ALTER TABLE users 
                ADD COLUMN is_active boolean DEFAULT 1 AFTER token_expiry,
                ADD COLUMN updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at";
            
            if (mysqli_query($con, $alter_users)) {
                echo "<p style='color: green;'><strong>✓ Enhanced users table with is_active and updated_at columns</strong></p>";
            } else {
                echo "<p style='color: red;'><strong>✗ Error enhancing users table: " . mysqli_error($con) . "</strong></p>";
            }
        } else {
            echo "<p style='color: blue;'><strong>ℹ Users table already has is_active column</strong></p>";
        }
        
        echo "<hr>";
        echo "<h3 style='color: green;'>✓ Database migration completed successfully!</h3>";
        echo "<p><strong>New Tables Created:</strong></p>";
        echo "<ul>";
        echo "<li><strong>roles</strong> - Admin role definitions (admin, sales_manager, customer)</li>";
        echo "<li><strong>order_statuses</strong> - Order status definitions (7 statuses: pending, confirmed, processing, shipped, delivered, cancelled, returned)</li>";
        echo "<li><strong>orders</strong> - Confirmed orders table</li>";
        echo "<li><strong>order_items</strong> - Individual items in orders</li>";
        echo "<li><strong>cart_items</strong> - Shopping cart items</li>";
        echo "</ul>";
        echo "<p><strong>Enhanced Existing Tables:</strong></p>";
        echo "<ul>";
        echo "<li><strong>admins</strong> - Now uses role_id (FK to roles table) instead of enum</li>";
        echo "<li><strong>items</strong> - Added: sku, description, stock_quantity, category, is_active, updated_at</li>";
        echo "<li><strong>users</strong> - Added: is_active, updated_at</li>";
        echo "<li><strong>users_items</strong> - Kept for backward compatibility</li>";
        echo "</ul>";
        echo "<p><a href='admin310817.php' class='btn btn-primary'>Go to Admin Login</a></p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'><strong>Error: " . htmlspecialchars($e->getMessage()) . "</strong></p>";
    }
?>
