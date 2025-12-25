<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Check if user is logged in
    SecurityHelper::requireLogin();
    SecurityHelper::validateSessionTimeout($con);
    
    // Get order details by UUID if available, fallback to numeric ID
    if(!isset($_SESSION['order_uid']) && !isset($_SESSION['order_id'])){
        header('Location: index.php');
        exit();
    }
    
    $order_uid = $_SESSION['order_uid'] ?? null;
    $order_id = isset($_SESSION['order_id']) ? intval($_SESSION['order_id']) : 0;
    $user_id = SecurityHelper::getUserId();
    
    // Fetch order details with prepared statement and ownership validation
    if ($order_uid) {
        $order_query = "SELECT o.id, o.order_uid, o.created_at, o.total_amount, os.name as status_name
                       FROM orders o
                       INNER JOIN order_statuses os ON os.id = o.status_id
                       WHERE o.order_uid = ? AND o.user_id = ?";
        $stmt = mysqli_prepare($con, $order_query);
        mysqli_stmt_bind_param($stmt, "si", $order_uid, $user_id);
    } else {
        $order_query = "SELECT o.id, o.order_uid, o.created_at, o.total_amount, os.name as status_name
                       FROM orders o
                       INNER JOIN order_statuses os ON os.id = o.status_id
                       WHERE o.id = ? AND o.user_id = ?";
        $stmt = mysqli_prepare($con, $order_query);
        mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
    }
    mysqli_stmt_execute($stmt);
    $order_result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    
    if(mysqli_num_rows($order_result) == 0){
        header('Location: index.php');
        exit();
    }
    
    $order = mysqli_fetch_array($order_result);
    $order_id = intval($order['id']);
    $order_uid = $order['order_uid'] ?? $order_uid;
    
    // Fetch order items with prepared statement
    $items_query = "SELECT oi.item_id, oi.quantity, oi.unit_price, oi.subtotal, i.name, i.image
                   FROM order_items oi
                   INNER JOIN items i ON i.id = oi.item_id
                   WHERE oi.order_id = ?";
    
    $stmt = mysqli_prepare($con, $items_query);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $items_result = mysqli_stmt_get_result($stmt);
    $items = [];
    $computed_total = 0.00;
    while ($row = mysqli_fetch_assoc($items_result)) {
        $items[] = $row;
        $computed_total = round($computed_total + floatval($row['subtotal']), 2);
    }
    mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Order Confirmation - Lifestyle Store</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css">
        <script type="text/javascript" src="bootstrap/js/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="css/style.css" type="text/css">
        <style>
            .confirmation-box {
                background-color: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 5px;
                padding: 20px;
                margin-top: 20px;
            }
            .order-success {
                color: #27ae60;
                font-size: 18px;
                margin-bottom: 20px;
            }
            .order-details {
                background-color: white;
                padding: 15px;
                border-radius: 5px;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <?php require 'header.php'; ?>
        
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="confirmation-box">
                        <div class="text-center order-success">
                            <strong>✓ Order Placed Successfully!</strong>
                        </div>
                        
                        <div class="order-details">
                            <h4>Order Details</h4>
                            <hr>
                            <table class="table table-bordered">
                                <tr>
                                    <td><strong>Order ID:</strong></td>
                                    <td><?php echo htmlspecialchars($order_uid ?? $order['id']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Order Date:</strong></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td><span class="label label-info"><?php echo ucfirst($order['status_name']); ?></span></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Amount:</strong></td>
                                    <td><strong>₫ <?php echo number_format($computed_total, 0); ?></strong></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="order-details">
                            <h4>Items Ordered</h4>
                            <hr>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo intval($item['quantity']); ?></td>
                                            <td>₫ <?php echo number_format($item['unit_price'], 0); ?></td>
                                            <td>₫ <?php echo number_format($item['subtotal'], 0); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div style="margin-top: 20px; text-align: center;">
                            <p>Thank you for your purchase! Your order is being processed.</p>
                            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
                            <a href="index.php" class="btn btn-default">Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php require 'footer.php'; ?>
    </body>
</html>
<?php
    // Clear session variables
    unset($_SESSION['order_id']);
    unset($_SESSION['order_total']);
?>
