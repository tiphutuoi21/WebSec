<?php
    require 'connection.php';
    if(!isset($_SESSION['email'])){
        header('location:index.php');
    }
    
    // Fetch user's previous orders
    $user_id = isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;
    $user_orders = array();
    
    if ($user_id > 0) {
        $order_query = "SELECT o.id, o.total_amount, o.status_id, o.created_at, os.name as status_name 
                        FROM orders o 
                        LEFT JOIN order_statuses os ON o.status_id = os.id 
                        WHERE o.user_id = ? 
                        ORDER BY o.created_at DESC";
        $stmt = mysqli_prepare($con, $order_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($order = mysqli_fetch_assoc($result)) {
                $user_orders[] = $order;
            }
            mysqli_stmt_close($stmt);
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/lifestyleStore.png" />
        <title>Lifestyle Store</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- latest compiled and minified CSS -->
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css">
        <!-- jquery library -->
        <script type="text/javascript" src="bootstrap/js/jquery-3.2.1.min.js"></script>
        <!-- Latest compiled and minified javascript -->
        <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
        <!-- External CSS -->
        <link rel="stylesheet" href="css/style.css" type="text/css">
        <style>
            .settings-container {
                margin-top: 30px;
                margin-bottom: 50px;
            }
            .orders-section {
                margin-top: 40px;
                padding-top: 30px;
                border-top: 2px solid #ddd;
            }
            .order-card {
                background-color: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 15px;
                transition: box-shadow 0.3s;
            }
            .order-card:hover {
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }
            .order-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
                flex-wrap: wrap;
            }
            .order-id {
                font-weight: bold;
                color: #333;
                font-size: 16px;
            }
            .order-date {
                color: #666;
                font-size: 14px;
                margin-top: 5px;
            }
            .order-amount {
                font-size: 18px;
                font-weight: bold;
                color: #d9534f;
            }
            .order-status {
                display: inline-block;
                padding: 5px 12px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: bold;
                text-transform: uppercase;
                margin-top: 5px;
            }
            .status-confirmed {
                background-color: #d9edf7;
                color: #31708f;
            }
            .status-pending {
                background-color: #fcf8e3;
                color: #8a6d3b;
            }
            .status-shipped {
                background-color: #d4edda;
                color: #155724;
            }
            .status-delivered {
                background-color: #c3e6cb;
                color: #155724;
            }
            .no-orders {
                padding: 40px 20px;
                text-align: center;
                color: #999;
                background-color: #f9f9f9;
                border-radius: 8px;
            }
            .view-details-btn {
                margin-top: 10px;
            }
        </style>
    </head>
    <body>
        <div>
            <?php
                require 'header.php';
            ?>
            <br>
            <div class="container settings-container">
                <div class="row">
                    <div class="col-xs-4 col-xs-offset-4">
                        <h1>Change Password</h1>
                        <form method="post" action="setting_script.php">
                            <div class="form-group">
                                <input type="password" class="form-control" name="oldPassword" placeholder="Old Password">
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control" name="newPassword" placeholder="New Password">
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control" name="retype" placeholder="Re-type new password">
                            </div>
                            <div class="form-group">
                                <input type="submit" class="btn btn-primary" value="Change">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Previous Orders Section -->
            <div class="container settings-container">
                <div class="orders-section">
                    <h2>Your Previous Orders</h2>
                    
                    <?php if (empty($user_orders)): ?>
                        <div class="no-orders">
                            <p>You haven't placed any orders yet.</p>
                            <p>
                                <a href="products.php" class="btn btn-primary">
                                    <span class="glyphicon glyphicon-shopping-cart"></span> Start Shopping
                                </a>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-md-12">
                                <?php foreach ($user_orders as $order): ?>
                                    <div class="order-card">
                                        <div class="order-header">
                                            <div>
                                                <div class="order-id">Order #<?php echo intval($order['id']); ?></div>
                                                <div class="order-date">
                                                    <?php echo date('F d, Y \a\t g:i A', strtotime($order['created_at'])); ?>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="order-amount">
                                                    Rs. <?php echo number_format($order['total_amount'], 2); ?>
                                                </div>
                                                <div>
                                                    <?php
                                                        $status_name = $order['status_name'] ? strtolower($order['status_name']) : 'unknown';
                                                        $status_class = 'status-' . str_replace(' ', '-', $status_name);
                                                    ?>
                                                    <span class="order-status <?php echo htmlspecialchars($status_class); ?>">
                                                        <?php echo htmlspecialchars($order['status_name'] ?? 'Unknown'); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Order Items -->
                                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                                            <?php
                                                $items_query = "SELECT oi.quantity, oi.unit_price, i.name 
                                                               FROM order_items oi 
                                                               JOIN items i ON oi.item_id = i.id 
                                                               WHERE oi.order_id = ?";
                                                $items_stmt = mysqli_prepare($con, $items_query);
                                                if ($items_stmt) {
                                                    $order_id = intval($order['id']);
                                                    mysqli_stmt_bind_param($items_stmt, "i", $order_id);
                                                    mysqli_stmt_execute($items_stmt);
                                                    $items_result = mysqli_stmt_get_result($items_stmt);
                                                    
                                                    $item_count = mysqli_num_rows($items_result);
                                                    if ($item_count > 0) {
                                                        echo '<h5>Items in this order:</h5>';
                                                        echo '<ul style="margin-bottom: 0;">';
                                                        while ($item = mysqli_fetch_assoc($items_result)) {
                                                            echo '<li>';
                                                            echo htmlspecialchars($item['name']) . ' - ';
                                                            echo 'Qty: ' . intval($item['quantity']) . ' × ';
                                                            echo 'Rs. ' . number_format($item['unit_price'], 2);
                                                            echo '</li>';
                                                        }
                                                        echo '</ul>';
                                                    }
                                                    mysqli_stmt_close($items_stmt);
                                                }
                                            ?>
                                        </div>
                                        
                                        <div class="view-details-btn">
                                            <a href="order_details.php?id=<?php echo intval($order['id']); ?>" class="btn btn-info btn-sm">
                                                <span class="glyphicon glyphicon-eye-open"></span> View Order Details
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <br><br><br><br><br>
           <footer class="footer">
               <div class="container">
               <center>
                   <p>Copyright &copy Lifestyle Store. All Rights Reserved. | Contact Us: +91 90000 00000</p>
                   <p>This website is developed by Sajal Agrawal</p>
               </center>
               </div>
           </footer>
        </div>
    </body>
</html>
