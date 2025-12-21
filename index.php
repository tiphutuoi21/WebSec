<?php
session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Figure Shop - Shop Mô Hình</title>
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
           <div id="bannerImage">
               <div class="container">
                   <center>
                   <div id="bannerContent">
                       <img src="img/avatar.png" alt="Figure Shop Logo" class="banner-logo" style="max-width: 150px; margin-bottom: 20px; border-radius: 50%; border: 3px solid var(--primary-yellow); box-shadow: 0 4px 8px rgba(0,0,0,0.5);">
                       <h1>Figure Shop</h1>
                       <p>Shop mô hình chính hãng - Nơi hội tụ đam mê sưu tầm</p>
                       <a href="products.php" class="btn btn-danger">Mua Ngay</a>
                   </div>
                   </center>
               </div>
           </div>
           <div class="container">
               <div class="row">
                   <div class="col-xs-4">
                       <div  class="thumbnail">
                           <a href="products.php?category=new">
                                <img src="img/camera.jpg" alt="Hàng Mới Về" style="height: 200px; object-fit: cover;">
                           </a>
                           <center>
                                <div class="caption">
                                        <p id="autoResize">Hàng Mới Về</p>
                                        <p>Những mô hình mới nhất vừa về kho</p>
                                </div>
                           </center>
                       </div>
                   </div>
                   <div class="col-xs-4">
                       <div class="thumbnail">
                           <a href="products.php?category=bestseller">
                               <img src="img/watch.jpg" alt="Best Seller" style="height: 200px; object-fit: cover;">
                           </a>
                           <center>
                                <div class="caption">
                                    <p id="autoResize">Best Seller</p>
                                    <p>Sản phẩm bán chạy nhất</p>
                                </div>
                           </center>
                       </div>
                   </div>
                   <div class="col-xs-4">
                       <div class="thumbnail">
                           <a href="products.php">
                               <img src="img/shirt.jpg" alt="Tất Cả Sản Phẩm" style="height: 200px; object-fit: cover;">
                           </a>
                           <center>
                               <div class="caption">
                                   <p id="autoResize">Tất Cả Sản Phẩm</p>
                                   <p>Bộ sưu tập mô hình đầy đủ nhất</p>
                               </div>
                           </center>
                       </div>
                   </div>
               </div>
           </div>
            <br><br> <br><br><br><br>
           <footer class="footer"> 
               <div class="container">
               <center>
                   <p>Copyright &copy Figure Shop. All Rights Reserved. | Liên Hệ: +84 0854008327</p>
                   <p>Shop mô hình chính hãng - Nơi hội tụ đam mê sưu tầm</p>
               </center>
               </div>
           </footer>
        </div>
    </body>
</html>