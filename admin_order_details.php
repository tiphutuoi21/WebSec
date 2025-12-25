<?php
require 'connection.php';
require 'SecurityHelper.php';

// Allow admin (role 1) and sales (role 2)
SecurityHelper::validateSessionTimeout($con);
if (!isset($_SESSION['admin_email']) || !isset($_SESSION['admin_role_id']) || !in_array(intval($_SESSION['admin_role_id']), [1, 2], true)) {
    header('location: admin_login.php');
    exit();
}

$is_admin = intval($_SESSION['admin_role_id']) === 1;
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order_uid = isset($_GET['uid']) ? trim($_GET['uid']) : '';

if ($order_id <= 0 && $order_uid === '') {
    header('Location: admin_orders.php');
    exit();
}

// Fetch order header
if ($order_uid !== '') {
    $order_sql = "SELECT o.id, o.order_uid, o.total_amount, o.created_at, os.name AS status_name, u.email AS user_email
                  FROM orders o
                  INNER JOIN users u ON u.id = o.user_id
                  INNER JOIN order_statuses os ON os.id = o.status_id
                  WHERE o.order_uid = ?";
    $stmt = mysqli_prepare($con, $order_sql);
    mysqli_stmt_bind_param($stmt, 's', $order_uid);
} else {
    $order_sql = "SELECT o.id, o.order_uid, o.total_amount, o.created_at, os.name AS status_name, u.email AS user_email
                  FROM orders o
                  INNER JOIN users u ON u.id = o.user_id
                  INNER JOIN order_statuses os ON os.id = o.status_id
                  WHERE o.id = ?";
    $stmt = mysqli_prepare($con, $order_sql);
    mysqli_stmt_bind_param($stmt, 'i', $order_id);
}

mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($order_result) === 0) {
    mysqli_stmt_close($stmt);
    header('Location: admin_orders.php');
    exit();
}
$order = mysqli_fetch_assoc($order_result);
mysqli_stmt_close($stmt);
$order_id = intval($order['id']);
$order_uid = $order['order_uid'];

// Fetch items
$item_sql = "SELECT oi.item_id, oi.quantity, oi.unit_price, oi.subtotal, i.name, i.image
             FROM order_items oi
             INNER JOIN items i ON i.id = oi.item_id
             WHERE oi.order_id = ?";
$item_stmt = mysqli_prepare($con, $item_sql);
mysqli_stmt_bind_param($item_stmt, 'i', $order_id);
mysqli_stmt_execute($item_stmt);
$item_res = mysqli_stmt_get_result($item_stmt);
$items = [];
$computed_total = 0.00;
while ($row = mysqli_fetch_assoc($item_res)) {
    $items[] = $row;
    $computed_total = round($computed_total + floatval($row['subtotal']), 2);
}
mysqli_stmt_close($item_stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="shortcut icon" href="img/avatar.png" />
    <title>Order #<?php echo $order_id; ?> Details</title>
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
            <h3><?php echo $is_admin ? 'Admin Dashboard' : 'Sales Dashboard'; ?></h3>
            <a href="<?php echo $is_admin ? 'admin_dashboard.php' : 'sales_dashboard.php'; ?>">Dashboard</a>
            <?php if ($is_admin): ?>
                <a href="admin_manage_users.php">Manage Users</a>
                <a href="admin_manage_products.php">Manage Products</a>
                <a href="admin_manage_sales.php">Manage Sales</a>
            <?php else: ?>
                <a href="sales_manage_products.php">Manage Products</a>
            <?php endif; ?>
            <a href="admin_orders.php" class="active">View Orders</a>
            <a href="admin_logout.php">Logout</a>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h2 class="admin-page-title">Order #<?php echo $order_id; ?></h2>
                <p><strong>Order UID:</strong> <?php echo htmlspecialchars($order_uid); ?></p>
                <p><strong>User Email:</strong> <?php echo htmlspecialchars($order['user_email']); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($order['status_name'])); ?></p>
                <p><strong>Created:</strong> <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
                <p><strong>Total (computed):</strong> ₫ <?php echo number_format($computed_total, 0); ?></p>

                <div class="admin-table" style="margin-top:20px;">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th style="width:100px; text-align:center;">Qty</th>
                                <th style="width:140px; text-align:right;">Unit Price</th>
                                <th style="width:160px; text-align:right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="4" style="text-align:center; padding:20px; color:#666;">No items found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td style="text-align:center;"><?php echo intval($item['quantity']); ?></td>
                                        <td style="text-align:right;">₫ <?php echo number_format($item['unit_price'], 0); ?></td>
                                        <td style="text-align:right;"><strong>₫ <?php echo number_format($item['subtotal'], 0); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <a href="admin_orders.php" class="btn btn-default">Back to Orders</a>
            </div>
        </div>
    </div>
</body>
</html>
