<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Validate session and check sales access (role_id = 2 only)
    SecurityHelper::validateSessionTimeout($con);
    if(!isset($_SESSION['admin_email']) || !isset($_SESSION['admin_role_id']) || $_SESSION['admin_role_id'] !== 2) {
        header('location: admin_login.php');
        exit();
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Sales Dashboard</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css">
        <script type="text/javascript" src="bootstrap/js/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="css/style.css" type="text/css">
    </head>
    <body>
        <div class="container">
            <div class="admin-nav">
                <h3>Sales Dashboard</h3>
                <a href="sales_dashboard.php">Dashboard</a>
                <a href="sales_manage_products.php">Manage Products</a>
                <a href="admin_orders.php">View Orders</a>
                <a href="admin_logout.php">Logout</a>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="admin-panel">
                        <h3 class="admin-panel-heading">Welcome to Sales Dashboard</h3>
                        <p style="font-size: 16px; margin-bottom: 15px;">Logged in as: <strong><?php echo htmlspecialchars($_SESSION['admin_email']); ?></strong></p>
                        <p style="font-size: 16px; margin-bottom: 20px;">
                            Role: 
                            <span class="admin-label admin-label-sales">
                                Sales Staff
                            </span>
                        </p>
                        <hr style="border-color: #e0e0e0;">
                        <h4 style="color: #1a1a1a; font-weight: 600; margin-bottom: 15px;">Your Privileges:</h4>
                        <ul style="list-style: none; padding: 0;">
                            <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">✓ View Products</li>
                            <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">✓ Add Products</li>
                            <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">✓ Edit Products</li>
                            <li style="padding: 8px 0;">✓ Delete Products</li>
                        </ul>
                        <hr style="border-color: #e0e0e0; margin-top: 25px;">
                        <h4 style="color: #1a1a1a; font-weight: 600; margin-bottom: 15px; margin-top: 25px;">What You Cannot Do:</h4>
                        <ul style="list-style: none; padding: 0;">
                            <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0; color: #999;">✗ Manage Users (Admin Only)</li>
                            <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0; color: #999;">✗ Manage Sales Staff (Admin Only)</li>
                            <li style="padding: 8px 0; color: #999;">✗ View System Logs (Admin Only)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
