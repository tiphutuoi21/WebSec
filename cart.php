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
        ?>
        <script>
        window.alert("No items in the cart!!");
        </script>
    <?php
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
                <h2>Shopping Cart</h2>
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr>
                            <th>Item Number</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                       <?php 
                        $user_products_result = mysqli_query($con, $user_products_query) or die(mysqli_error($con));
                        $no_of_user_products = mysqli_num_rows($user_products_result);
                        $counter = 1;
                        while($row = mysqli_fetch_array($user_products_result)){
                            $item_total = $row['price'] * $row['quantity'];
                         ?>
                        <tr>
                            <td><?php echo $counter ?></td>
                            <td><?php echo $row['name']?></td>
                            <td><?php echo $row['quantity']?></td>
                            <td>Rs <?php echo $row['price']?></td>
                            <td>Rs <?php echo $item_total?></td>
                            <td><a href='cart_remove.php?id=<?php echo $row['id'] ?>' class="btn btn-sm btn-danger">Remove</a></td>
                        </tr>
                       <?php $counter = $counter + 1;
                       }?>
                        <tr>
                            <td colspan="4" style="text-align: right;"><strong>Total Amount:</strong></td>
                            <td><strong>Rs <?php echo number_format($sum, 2);?>/-</strong></td>
                            <td>
                                <?php if($no_of_user_products > 0): ?>
                                    <a href="place_order.php" class="btn btn-success">Place Order</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
