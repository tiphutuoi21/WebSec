<?php
    require 'connection.php';
    
    // Check if user is logged in
    if(!isset($_SESSION['email'])){
        header('Location: login.php');
        exit();
    }
    
    $user_id = intval($_SESSION['id']);
    
    // Get order ID from URL parameter
    if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
        header('Location: settings.php');
        exit();
    }
    
    $order_id = intval($_GET['id']);
    
    // Fetch order details with prepared statement and verify ownership
    $order_query = "SELECT o.id, o.created_at, o.total_amount, o.status_id, os.name as status_name
                   FROM orders o
                   INNER JOIN order_statuses os ON os.id = o.status_id
                   WHERE o.id = ? AND o.user_id = ?";
    
    $stmt = mysqli_prepare($con, $order_query);
    if (!$stmt) {
        header('Location: settings.php');
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
    mysqli_stmt_execute($stmt);
    $order_result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($order_result) == 0){
        // Order doesn't exist or doesn't belong to this user
        mysqli_stmt_close($stmt);
        header('Location: settings.php');
        exit();
    }
    
    $order = mysqli_fetch_assoc($order_result);
    mysqli_stmt_close($stmt);
    
    // Fetch order items with prepared statement
    $items_query = "SELECT oi.item_id, oi.quantity, oi.unit_price, oi.subtotal, i.name, i.price
                   FROM order_items oi
                   INNER JOIN items i ON i.id = oi.item_id
                   WHERE oi.order_id = ?
                   ORDER BY oi.id ASC";
    
    $stmt = mysqli_prepare($con, $items_query);
    if (!$stmt) {
        header('Location: settings.php');
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $items_result = mysqli_stmt_get_result($stmt);
    $order_items = array();
    $computed_total = 0.00;
    
    while($item = mysqli_fetch_assoc($items_result)){
        $order_items[] = $item;
        $computed_total = round($computed_total + floatval($item['subtotal']), 2);
    }
    mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Order #<?php echo intval($order['id']); ?> - Lifestyle Store</title>
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
            .order-detail-container {
                margin-top: 30px;
                margin-bottom: 50px;
            }
            .order-header {
                background-color: #f9f9f9;
                padding: 25px;
                border-radius: 8px;
                margin-bottom: 30px;
                border-left: 5px solid #5cb85c;
            }
            .order-title {
                font-size: 28px;
                margin-bottom: 15px;
                color: #333;
            }
            .order-info {
                display: flex;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 30px;
                margin-top: 15px;
            }
            .info-block {
                flex: 1;
                min-width: 200px;
            }
            .info-label {
                font-size: 12px;
                color: #999;
                text-transform: uppercase;
                font-weight: bold;
                margin-bottom: 5px;
            }
            .info-value {
                font-size: 18px;
                color: #333;
                font-weight: bold;
            }
            .order-status {
                display: inline-block;
                padding: 8px 15px;
                border-radius: 25px;
                font-size: 13px;
                font-weight: bold;
                text-transform: uppercase;
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
            .items-section {
                margin-bottom: 30px;
            }
            .items-table {
                width: 100%;
                border-collapse: collapse;
                background-color: white;
                border: 1px solid #ddd;
                border-radius: 8px;
                overflow: hidden;
            }
            .items-table thead {
                background-color: #f9f9f9;
                border-bottom: 2px solid #ddd;
            }
            .items-table th {
                padding: 15px;
                text-align: left;
                font-weight: bold;
                color: #333;
            }
            .items-table td {
                padding: 15px;
                border-bottom: 1px solid #eee;
            }
            .items-table tr:last-child td {
                border-bottom: none;
            }
            .item-name {
                font-weight: 500;
                color: #333;
            }
            .summary-section {
                background-color: #f9f9f9;
                padding: 25px;
                border-radius: 8px;
                margin-bottom: 30px;
            }
            .summary-row {
                display: flex;
                justify-content: space-between;
                padding: 10px 0;
                border-bottom: 1px solid #eee;
            }
            .summary-row:last-child {
                border-bottom: none;
                font-size: 20px;
                font-weight: bold;
                color: #d9534f;
                padding-top: 15px;
                margin-top: 15px;
                border-top: 2px solid #ddd;
            }
            .back-button {
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div>
            <?php require 'header.php'; ?>
            
            <div class="container order-detail-container">
                <!-- Back Button -->
                <div class="back-button">
                    <a href="settings.php" class="btn btn-default">
                        <span class="glyphicon glyphicon-arrow-left"></span> Back to Settings
                    </a>
                </div>
                
                <!-- Order Header -->
                <div class="order-header">
                    <div class="order-title">Order #<?php echo intval($order['id']); ?></div>
                    <div class="order-info">
                        <div class="info-block">
                            <div class="info-label">Order Date</div>
                            <div class="info-value"><?php echo date('F d, Y', strtotime($order['created_at'])); ?></div>
                        </div>
                        <div class="info-block">
                            <div class="info-label">Order Time</div>
                            <div class="info-value"><?php echo date('g:i A', strtotime($order['created_at'])); ?></div>
                        </div>
                        <div class="info-block">
                            <div class="info-label">Status</div>
                            <div>
                                <?php
                                    $status_name = strtolower($order['status_name']);
                                    $status_class = 'status-' . str_replace(' ', '-', $status_name);
                                ?>
                                <span class="order-status <?php echo htmlspecialchars($status_class); ?>">
                                    <?php echo htmlspecialchars($order['status_name']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="items-section">
                    <h3>Items in This Order</h3>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th style="width: 100px; text-align: center;">Quantity</th>
                                <th style="width: 120px; text-align: right;">Unit Price</th>
                                <th style="width: 120px; text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($order_items as $item): ?>
                                <tr>
                                    <td class="item-name"><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td style="text-align: center;"><?php echo intval($item['quantity']); ?></td>
                                    <td style="text-align: right;">₫ <?php echo number_format($item['unit_price'], 0); ?></td>
                                    <td style="text-align: right;"><strong>₫ <?php echo number_format($item['subtotal'], 0); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Order Summary -->
                <div class="summary-section">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>₫ <?php echo number_format($computed_total, 0); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span>Free</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax:</span>
                        <span>Included</span>
                    </div>
                    <div class="summary-row">
                        <span>Total:</span>
                        <span>₫ <?php echo number_format($computed_total, 0); ?></span>
                    </div>
                </div>
                
                <!-- Actions -->
                <div style="margin-bottom: 30px;">
                    <a href="products.php" class="btn btn-primary">
                        <span class="glyphicon glyphicon-shopping-cart"></span> Continue Shopping
                    </a>
                    <a href="settings.php" class="btn btn-default">
                        <span class="glyphicon glyphicon-list"></span> View All Orders
                    </a>
                </div>
            </div>
            
            <br><br><br><br>
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
