<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Require admin login
    if(!isset($_SESSION['admin_email'])){
        header('location: admin310817.php');
        exit();
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/lifestyleStore.png" />
        <title>Admin Dashboard - Lifestyle Store</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css">
        <script type="text/javascript" src="bootstrap/js/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="css/style.css" type="text/css">
        <style>
            .admin-nav {
                background-color: #222;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 4px;
            }
            .admin-nav a {
                color: white;
                margin-right: 20px;
                text-decoration: none;
            }
            .admin-nav a:hover {
                color: #337ab7;
            }
            .dashboard-section {
                margin-top: 30px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="admin-nav">
                <h3 style="color: white; display: inline;">Admin Dashboard (<?php echo ucfirst(str_replace('_', ' ', $_SESSION['admin_role'])); ?>)</h3>
                <a href="admin_manage_users.php">Manage Users</a>
                <a href="admin_manage_orders.php">Manage Orders</a>
                <a href="admin_manage_products.php">Manage Products</a>
                <a href="admin_logout.php" style="float: right;">Logout</a>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3>Welcome to Admin Dashboard</h3>
                        </div>
                        <div class="panel-body">
                            <p>Logged in as: <strong><?php echo $_SESSION['admin_email']; ?></strong></p>
                            <p>Role: <span class="label <?php echo intval($_SESSION['admin_role_id']) === 1 ? 'label-danger' : 'label-warning'; ?>"><?php echo ucfirst(str_replace('_', ' ', $_SESSION['admin_role'])); ?></span></p>
                            <hr>
                            <h4>Your Permissions:</h4>
                            <ul>
                                <li>✓ View Users</li>
                                <li>✓ View Orders</li>
                                <li>✓ Add Products</li>
                                <li>✓ Edit Products</li>
                                <li>✓ Delete Products</li>
                                <?php if(intval($_SESSION['admin_role_id']) === 1): ?>
                                    <li>✓ Delete Users</li>
                                    <li>✓ Delete Orders</li>
                                    <li>✓ Manage Staff Accounts</li>
                                <?php else: ?>
                                    <li>✗ Delete Users (Admin only)</li>
                                    <li>✗ Delete Orders (Admin only)</li>
                                    <li>✗ Manage Staff Accounts (Admin only)</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
