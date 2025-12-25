<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Require login and validate session
    SecurityHelper::requireLogin();
    SecurityHelper::validateSessionTimeout($con);
    
    $user_id = SecurityHelper::getUserId();
    
    // Query the new cart_items table
    $user_products_query = "SELECT ci.id, ci.item_id, ci.quantity, i.name, i.price 
                           FROM cart_items ci 
                           INNER JOIN items i ON i.id = ci.item_id 
                           WHERE ci.user_id = $user_id
                           ORDER BY ci.id DESC";
    $user_products_result = mysqli_query($con, $user_products_query) or die(mysqli_error($con));
    $no_of_user_products = mysqli_num_rows($user_products_result);
    
    $sum = 0;
    if($no_of_user_products == 0){
        // Cart is empty - no alert needed, will show empty cart message
    } else {
        while($row = mysqli_fetch_array($user_products_result)){
            $item_total = $row['price'] * $row['quantity'];
            $sum = $sum + $item_total; 
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Giỏ Hàng - Figure Shop</title>
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
    </head>
    <body>
        <div>
            <?php 
               require 'header.php';
            ?>
            <br>
            <div class="container">
                <div class="cart-header-section">
                    <h1 class="cart-title">Giỏ Hàng Của Bạn</h1>
                    <p class="cart-subtitle">Kiểm tra và thanh toán đơn hàng của bạn</p>
                </div>
                
                <?php if($no_of_user_products == 0): ?>
                    <div class="cart-empty">
                        <div class="cart-empty-icon">
                            <span class="glyphicon glyphicon-shopping-cart" style="font-size: 80px; color: var(--dark-gray);"></span>
                        </div>
                        <h3>Giỏ hàng của bạn đang trống</h3>
                        <p>Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm!</p>
                        <a href="products.php" class="btn btn-product-buy" style="display: inline-block; width: auto; padding: 12px 40px; margin-top: 20px;">
                            <span class="glyphicon glyphicon-th"></span> Tiếp Tục Mua Sắm
                        </a>
                    </div>
                <?php else: ?>
                    <div class="cart-table-wrapper">
                        <table class="table cart-table">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Tên Sản Phẩm</th>
                                    <th>Số Lượng</th>
                                    <th>Đơn Giá</th>
                                    <th>Thành Tiền</th>
                                    <th>Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                               <?php 
                                $user_products_result = mysqli_query($con, $user_products_query) or die(mysqli_error($con));
                                $no_of_user_products = mysqli_num_rows($user_products_result);
                                $counter = 1;
                                while($row = mysqli_fetch_array($user_products_result)){
                                    $item_total = $row['price'] * $row['quantity'];
                                 ?>
                                <tr>
                                    <td><?php echo $counter ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['name'])?></strong></td>
                                    <td><?php echo $row['quantity']?></td>
                                    <td><?php echo number_format($row['price'], 0, ',', '.'); ?> VNĐ</td>
                                    <td><strong><?php echo number_format($item_total, 0, ',', '.'); ?> VNĐ</strong></td>
                                    <td>
                                        <form action="cart_remove.php" method="POST" style="display:inline-block;">
                                            <?php echo SecurityHelper::getCSRFField(); ?>
                                            <input type="hidden" name="id" value="<?php echo intval($row['id']); ?>">
                                            <button type="submit" class="btn btn-cart-remove" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">
                                                <span class="glyphicon glyphicon-trash"></span> Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                               <?php $counter = $counter + 1;
                               }?>
                                <tr class="cart-total-row">
                                    <td colspan="4" style="text-align: right;">
                                        <strong style="font-size: 20px;">Tổng Cộng:</strong>
                                    </td>
                                    <td colspan="2">
                                        <strong style="font-size: 24px; color: var(--primary-red);">
                                            <?php echo number_format($sum, 0, ',', '.'); ?> VNĐ
                                        </strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="cart-actions">
                            <a href="products.php" class="btn btn-cart-continue">
                                <span class="glyphicon glyphicon-arrow-left"></span> Tiếp Tục Mua Sắm
                            </a>
                            <a href="place_order.php" class="btn btn-cart-checkout">
                                <span class="glyphicon glyphicon-ok"></span> Đặt Hàng Ngay
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <br><br><br><br><br><br><br><br><br><br>
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
