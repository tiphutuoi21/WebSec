<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    require 'check_if_added.php';
    
    // Validate session and check for timeout
    if (isset($_SESSION['id'])) {
        SecurityHelper::validateSessionTimeout($con);
    }
    
    // Get category filter
    $category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';
    $page_title = 'Tất Cả Sản Phẩm';
    $page_description = 'Bộ sưu tập mô hình đầy đủ nhất';
    
    // Build query based on category
    if ($category_filter == 'new') {
        // Products created in last 30 days
        $query = "SELECT * FROM items WHERE is_active = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY created_at DESC";
        $page_title = 'Hàng Mới Về';
        $page_description = 'Những mô hình mới nhất vừa về kho';
    } elseif ($category_filter == 'bestseller') {
        // Bestseller - products with most orders
        $query = "SELECT i.*, COALESCE(SUM(oi.quantity), 0) as total_sold 
                  FROM items i 
                  LEFT JOIN order_items oi ON i.id = oi.item_id 
                  WHERE i.is_active = 1 
                  GROUP BY i.id 
                  ORDER BY total_sold DESC, i.created_at DESC 
                  LIMIT 50";
        $page_title = 'Best Seller';
        $page_description = 'Sản phẩm bán chạy nhất';
    } elseif ($category_filter == 'preorder') {
        // Products with category = 'Preorder' or 'Order'
        $query = "SELECT * FROM items WHERE is_active = 1 AND (category LIKE '%Order%' OR category LIKE '%Preorder%' OR category LIKE '%Pre-order%') ORDER BY name ASC";
        $page_title = 'Hàng Order';
        $page_description = 'Đặt trước các mô hình độc quyền';
    } else {
        // All products
        $query = "SELECT * FROM items WHERE is_active = 1 ORDER BY created_at DESC";
        $page_title = 'Tất Cả Sản Phẩm';
        $page_description = 'Bộ sưu tập mô hình đầy đủ nhất';
    }
    
    $result = mysqli_query($con, $query) or die(mysqli_error($con));
    $products = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title><?php echo htmlspecialchars($page_title); ?> - Figure Shop</title>
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
            <div class="container">
                <div class="jumbotron">
                    <h1><?php echo htmlspecialchars($page_title); ?></h1>
                    <p><?php echo htmlspecialchars($page_description); ?></p>
                    
                    <!-- Search Bar in Jumbotron -->
                    <hr>
                    <form method="POST" action="search.php" style="margin-top: 20px;">
                        <div class="input-group" style="margin-bottom: 10px;">
                            <input type="text" 
                                   class="form-control" 
                                   placeholder="Tìm kiếm mô hình..." 
                                   name="search"
                                   maxlength="255"
                                   style="font-size: 16px; padding: 10px;"
                                   required>
                            <span class="input-group-btn">
                                <button class="btn btn-warning" type="submit" style="padding: 10px 20px; font-size: 16px;">
                                    <span class="glyphicon glyphicon-search"></span> Tìm Kiếm
                                </button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
            <div class="container">
                <?php if (count($products) > 0): ?>
                    <div class="row">
                        <?php 
                        $col_count = 0;
                        foreach ($products as $product): 
                            $col_count++;
                            if ($col_count > 4) {
                                echo '</div><div class="row">';
                                $col_count = 1;
                            }
                        ?>
                            <div class="col-md-3 col-sm-6">
                                <div class="thumbnail">
                                    <a href="product.php?id=<?php echo $product['id']; ?>">
                                        <?php 
                                        // Try to get image, fallback to default
                                        $image_path = 'img/' . strtolower(str_replace(' ', '_', $product['name'])) . '.jpg';
                                        if (!file_exists($image_path)) {
                                            // Use first available image as fallback
                                            $image_path = 'img/camera.jpg';
                                        }
                                        ?>
                                        <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height: 200px; object-fit: cover; width: 100%;">
                                    </a>
                                    <center>
                                        <div class="caption">
                                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                            <p>Giá: <?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</p>
                                            <?php if (!empty($product['stock_quantity'])): ?>
                                                <p><small>Còn lại: <?php echo $product['stock_quantity']; ?> sản phẩm</small></p>
                                            <?php endif; ?>
                                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-info btn-block">Xem Chi Tiết</a>
                                            <?php if(!isset($_SESSION['email'])): ?>
                                                <p><a href="login.php" role="button" class="btn btn-primary btn-block">Mua Ngay</a></p>
                                            <?php else:
                                                if(check_if_added_to_cart($product['id'])):
                                                    echo '<a href="#" class="btn btn-block btn-success disabled">Đã thêm vào giỏ</a>';
                                                else:
                                            ?>
                                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-block btn-primary" name="add" value="add">Thêm vào giỏ</a>
                                            <?php 
                                                endif;
                                            endif; ?>
                                        </div>
                                    </center>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info" style="margin-top: 20px;">
                        <h4>Không có sản phẩm nào</h4>
                        <p>Hiện tại không có sản phẩm nào trong danh mục này. Vui lòng quay lại sau!</p>
                        <a href="products.php" class="btn btn-danger">Xem Tất Cả Sản Phẩm</a>
                    </div>
                <?php endif; ?>
            </div>
            <br><br><br><br><br><br><br><br>
           <footer class="footer">
               <div class="container">
               <center>
                   <p>Copyright &copy Figure Shop. All Rights Reserved. | Liên Hệ: +84 90000 00000</p>
                   <p>Shop mô hình chính hãng - Nơi hội tụ đam mê sưu tầm</p>
               </center>
               </div>
           </footer>
        </div>
    </body>
</html>
