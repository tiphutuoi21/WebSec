<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Require admin login
    if(!isset($_SESSION['admin_email'])){
        header('location: admin310817.php');
        exit();
    }
    
    // Query orders aggregated by order ID
    $orders_query = "SELECT 
                        o.id as order_id,
                        u.name,
                        u.email,
                        GROUP_CONCAT(i.name SEPARATOR ', ') as product_names,
                        GROUP_CONCAT(oi.quantity SEPARATOR ', ') as quantities,
                        o.total_amount,
                        os.name as status_name,
                        o.created_at
                     FROM orders o
                     INNER JOIN users u ON u.id = o.user_id
                     INNER JOIN order_items oi ON oi.order_id = o.id
                     INNER JOIN items i ON i.id = oi.item_id
                     INNER JOIN order_statuses os ON os.id = o.status_id
                     GROUP BY o.id
                     ORDER BY o.created_at DESC";
    $orders_result = mysqli_query($con, $orders_query) or die(mysqli_error($con));
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/lifestyleStore.png" />
        <title>Manage Orders - Admin Dashboard</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css">
        <script type="text/javascript" src="bootstrap/js/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="css/style.css" type="text/css">
    </head>
    <body>
        <div class="container">
            <div style="background-color: #222; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <h3 style="color: white; display: inline;">Admin Dashboard (<?php echo ucfirst(str_replace('_', ' ', $_SESSION['admin_role'])); ?>)</h3>
                <a href="admin_dashboard.php" style="color: white; margin-right: 20px;">Dashboard</a>
                <a href="admin_manage_users.php" style="color: white; margin-right: 20px;">Manage Users</a>
                <a href="admin_manage_products.php" style="color: white; margin-right: 20px;">Manage Products</a>
                <a href="admin_logout.php" style="color: white; float: right;">Logout</a>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <h2>Manage Orders</h2>
                    <?php if(intval($_SESSION['admin_role_id']) !== 1): ?>
                        <div class="alert alert-info">
                            <strong>Note:</strong> You are viewing confirmed orders. Only Admin role can delete orders.
                        </div>
                    <?php endif; ?>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer Name</th>
                                <th>Email</th>
                                <th>Products & Quantity</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Order Date</th>
                                <?php if(intval($_SESSION['admin_role_id']) === 1): ?>
                                    <th>Action</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                while($row = mysqli_fetch_array($orders_result)){
                                    $status_colors = array(
                                        'pending' => 'warning',
                                        'confirmed' => 'info',
                                        'processing' => 'primary',
                                        'shipped' => 'success',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger',
                                        'returned' => 'danger'
                                    );
                                    $status_class = isset($status_colors[strtolower($row['status_name'])]) ? $status_colors[strtolower($row['status_name'])] : 'default';
                                    ?>
                                    <tr>
                                        <td><?php echo $row['order_id']; ?></td>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td><?php echo $row['product_names']; ?> (Qty: <?php echo $row['quantities']; ?>)</td>
                                        <td>Rs <?php echo number_format($row['total_amount'], 2); ?></td>
                                        <td><span class="label label-<?php echo $status_class; ?>"><?php echo ucfirst($row['status_name']); ?></span></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                                        <?php if(intval($_SESSION['admin_role_id']) === 1): ?>
                                            <td>
                                                <a href="admin_delete_order.php?id=<?php echo $row['order_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>
