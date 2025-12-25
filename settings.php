<?php
    require 'connection.php';
    if(!isset($_SESSION['email'])){
        header('Location: index.php');
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
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Cài Đặt - Figure Shop</title>
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
        <script type="text/javascript">
            function validatePassword() {
                var password = document.getElementById('newPassword').value;
                var errorDiv = document.getElementById('password-error');
                var errors = [];
                
                if (password.length === 0) {
                    errorDiv.style.display = 'none';
                    return;
                }
                
                if (password.length < 8) {
                    errors.push('Mật khẩu phải có tối thiểu 8 ký tự');
                }
                
                if (!/[A-Z]/.test(password)) {
                    errors.push('Thiếu chữ cái viết hoa (A-Z)');
                }
                
                if (!/[a-z]/.test(password)) {
                    errors.push('Thiếu chữ cái viết thường (a-z)');
                }
                
                if (!/[0-9]/.test(password)) {
                    errors.push('Thiếu số (0-9)');
                }
                
                if (!/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/.test(password)) {
                    errors.push('Thiếu ký tự đặc biệt (!, @, #, $, %, v.v.)');
                }
                
                if (errors.length > 0) {
                    errorDiv.style.display = 'block';
                    errorDiv.innerHTML = '<strong>Lỗi:</strong> ' + errors.join(', ');
                    errorDiv.style.color = '#DC143C';
                } else {
                    errorDiv.style.display = 'none';
                }
            }
            
            function validateRetype() {
                var password = document.getElementById('newPassword').value;
                var retype = document.getElementById('retypePassword').value;
                var errorDiv = document.getElementById('retype-error');
                
                if (retype.length === 0) {
                    errorDiv.style.display = 'none';
                    return;
                }
                
                if (password !== retype) {
                    errorDiv.style.display = 'block';
                    errorDiv.innerHTML = '<strong>Lỗi:</strong> Mật khẩu nhập lại không khớp';
                    errorDiv.style.color = '#DC143C';
                } else {
                    errorDiv.style.display = 'none';
                }
            }
            
            document.addEventListener('DOMContentLoaded', function() {
                var passwordInput = document.getElementById('newPassword');
                var retypeInput = document.getElementById('retypePassword');
                
                if (passwordInput) {
                    passwordInput.addEventListener('input', validatePassword);
                    passwordInput.addEventListener('blur', validatePassword);
                }
                
                if (retypeInput) {
                    retypeInput.addEventListener('input', function() {
                        validateRetype();
                        validatePassword();
                    });
                    retypeInput.addEventListener('blur', validateRetype);
                }
            });
        </script>
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
                    <div class="col-xs-12 col-sm-10 col-md-8 col-lg-6 col-xs-offset-0 col-sm-offset-1 col-md-offset-2 col-lg-offset-3">
                        <div class="signup-container">
                            <div class="signup-header">
                                <h2><strong>ĐẶT LẠI MẬT KHẨU</strong></h2>
                            </div>
                            <div class="signup-body">
                                <div style="background-color: #e7f3ff; border-left: 4px solid #007bff; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                                    <p style="margin: 0; color: #555; font-size: 14px;">
                                        <strong>Enhanced Security:</strong> For your account protection, we now require email verification (OTP) before changing your password.
                                    </p>
                                </div>
                                
                                <a href="change_password.php" class="btn btn-primary btn-block" style="padding: 12px; text-decoration: none; display: inline-block; width: 100%; text-align: center; background-color: #007bff; color: white; border-radius: 4px; font-weight: bold;">
                                    <span class="glyphicon glyphicon-lock"></span> Đổi Mật Khẩu Với OTP
                                </a>
                                
                                <p style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd; color: #666; font-size: 12px;">
                                    Quá trình đổi mật khẩu:
                                </p>
                                <ol style="color: #666; font-size: 12px; margin: 10px 0;">
                                    <li>Nhấp vào nút trên</li>
                                    <li>Chúng tôi sẽ gửi mã OTP đến email của bạn</li>
                                    <li>Nhập mã OTP (hết hạn trong 1 phút)</li>
                                    <li>Thiết lập mật khẩu mới của bạn</li>
                                </ol>
                            </div>
                        </div>
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
                                                    ₫ <?php echo number_format($order['total_amount'], 0); ?>
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
                                                            echo '₫ ' . number_format($item['unit_price'], 0);
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
                   <p>Copyright &copy Figure Shop. All Rights Reserved. | Liên Hệ: +84 0854008327</p>
                   <p>Shop mô hình chính hãng - Nơi hội tụ đam mê sưu tầm</p>
               </center>
               </div>
           </footer>
        </div>
        
        <?php require 'hotline_widget.php'; ?>
    </body>
</html>
