<?php
require 'connection.php';
require 'SecurityHelper.php';

// Allow admin (role 1) and sales (role 2)
SecurityHelper::validateSessionTimeout($con);
if (!isset($_SESSION['admin_email']) || !isset($_SESSION['admin_role_id']) || !in_array(intval($_SESSION['admin_role_id']), [1, 2], true)) {
    header('location: admin_login.php');
    exit();
}

$role_id = intval($_SESSION['admin_role_id']);
$is_admin = ($role_id === 1);

$filter_email = '';
if (isset($_GET['user'])) {
    // Sanitize user filter to prevent wildcard injection and strip control chars
    $filter_email = SecurityHelper::getString('user', 'GET') ?? '';
    $filter_email = trim($filter_email);
    $filter_email = substr($filter_email, 0, 255);
    $filter_email = preg_replace('/[\x00-\x1F\x7F]/', '', $filter_email);
}

// Build query with optional user email filter
$sql = "SELECT o.id, o.order_uid, o.total_amount, o.created_at, os.name AS status_name, u.email AS user_email
        FROM orders o
        INNER JOIN users u ON u.id = o.user_id
        INNER JOIN order_statuses os ON os.id = o.status_id
        WHERE 1";
$params = [];
$types = '';

if ($filter_email !== '') {
    // Escape LIKE wildcards to avoid broad matches
    $like_value = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $filter_email);
    // Escape clause uses literal backslash in SQL; needs double escaping in PHP string
    $sql .= " AND u.email LIKE ? ESCAPE '\\\\'";
    $params[] = '%' . $like_value . '%';
    $types .= 's';
}

$sql .= " ORDER BY o.created_at DESC LIMIT 200";

$stmt = mysqli_prepare($con, $sql);
if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="shortcut icon" href="img/avatar.png" />
    <title>View Orders</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css">
    <script type="text/javascript" src="bootstrap/js/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="css/style.css" type="text/css">
    <style>
        .orders-filter { margin: 20px 0; }
        .orders-table th { background: #f9f9f9; }
    </style>
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
                <h2 class="admin-page-title">Orders</h2>
                <form class="form-inline orders-filter" method="GET" action="admin_orders.php">
                    <div class="form-group">
                        <label for="user">Filter by user email:</label>
                        <input type="text" class="form-control" id="user" name="user" value="<?php echo htmlspecialchars($filter_email); ?>" placeholder="user@example.com" maxlength="255">
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="admin_orders.php" class="btn btn-default">Clear</a>
                </form>

                <div class="admin-table">
                    <table class="table table-bordered orders-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Order UID</th>
                                <th>User Email</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="7" style="text-align:center; padding:20px; color:#666;">No orders found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?php echo intval($order['id']); ?></td>
                                        <td><?php echo htmlspecialchars($order['order_uid']); ?></td>
                                        <td><?php echo htmlspecialchars($order['user_email']); ?></td>
                                        <td>₫ <?php echo number_format($order['total_amount'], 0); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst($order['status_name'])); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <a class="admin-btn-primary btn-sm" href="admin_order_details.php?id=<?php echo intval($order['id']); ?>">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
