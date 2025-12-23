<?php
    require 'connection.php';
    require 'check_if_added.php';
    
    // Get search query from POST/GET
    $search_query = '';
    $search_results = array();
    $no_results = false;
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
        // Sanitize and validate search input
        $search_query = trim($_POST['search']);
        
        // Check if search query is not empty
        if (strlen($search_query) > 0) {
            // Validate search length (max 255 characters)
            if (strlen($search_query) > 255) {
                $search_query = substr($search_query, 0, 255);
            }
            
            // Prepare statement to prevent SQL injection
            $query = "SELECT * FROM items WHERE name LIKE ? ORDER BY name ASC";
            $stmt = mysqli_prepare($con, $query);
            
            if ($stmt) {
                // Add wildcards to search term for partial matching
                $search_term = "%" . $search_query . "%";
                mysqli_stmt_bind_param($stmt, "s", $search_term);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) > 0) {
                    while ($product = mysqli_fetch_assoc($result)) {
                        $search_results[] = $product;
                    }
                } else {
                    $no_results = true;
                }
                
                mysqli_stmt_close($stmt);
            }
        } else {
            $no_results = true;
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Tìm Kiếm Sản Phẩm - Figure Shop</title>
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
            .search-container {
                margin: 30px 0;
                padding: 30px;
                background: linear-gradient(135deg, var(--primary-black) 0%, var(--dark-gray) 50%, var(--primary-red) 100%);
                border-radius: 12px;
                border: 3px solid var(--primary-yellow);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            }
            
            .search-form {
                display: flex;
                gap: 10px;
                max-width: 700px;
                margin: 0 auto;
            }
            
            .search-form input {
                flex: 1;
                padding: 14px 20px;
                font-size: 16px;
                border: 2px solid var(--primary-yellow);
                border-radius: 8px 0 0 8px;
                background: rgba(255, 255, 255, 0.95);
            }
            
            .search-form input:focus {
                border-color: var(--primary-yellow);
                box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
                background: #fff;
                outline: none;
            }
            
            .search-form button {
                padding: 14px 30px;
                font-size: 16px;
                background: var(--primary-yellow);
                color: var(--primary-black);
                border: 2px solid var(--primary-yellow);
                border-radius: 0 8px 8px 0;
                font-weight: bold;
                transition: all 0.3s ease;
            }
            
            .search-form button:hover {
                background: var(--dark-yellow);
                border-color: var(--dark-yellow);
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(255, 215, 0, 0.4);
            }
            
            .search-results-header {
                margin-top: 40px;
                margin-bottom: 30px;
                padding: 20px;
                background: linear-gradient(135deg, var(--primary-black) 0%, var(--dark-gray) 100%);
                border-radius: 10px;
                border-left: 5px solid var(--primary-yellow);
            }
            
            .search-results-header h2 {
                color: var(--primary-yellow);
                margin: 0;
                font-weight: 900;
            }
            
            .search-info {
                color: #fff;
                font-size: 16px;
                margin-top: 10px;
                opacity: 0.9;
            }
            
            .no-results {
                padding: 60px 20px;
                text-align: center;
                background: #f8f8f8;
                border-radius: 12px;
                border: 2px solid var(--dark-gray);
            }
            
            .no-results-icon {
                font-size: 80px;
                color: var(--dark-gray);
                margin-bottom: 20px;
            }
            
            .no-results h3 {
                color: var(--primary-black);
                margin-bottom: 15px;
            }
            
            .no-results p {
                color: var(--dark-gray);
                font-size: 16px;
                margin-bottom: 25px;
            }
        </style>
    </head>
    <body>
        <div>
            <?php require 'header.php'; ?>
            
            <div class="container">
                <div class="search-container">
                    <h2 style="color: var(--primary-yellow); text-align: center; margin-bottom: 25px; font-weight: 900; font-size: 32px;">Tìm Kiếm Sản Phẩm</h2>
                    <form method="POST" action="search.php" class="search-form">
                        <input type="text" 
                               name="search" 
                               placeholder="Tìm kiếm mô hình..." 
                               value="<?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?>" 
                               maxlength="255"
                               required>
                        <button type="submit" class="btn btn-primary">
                            <span class="glyphicon glyphicon-search"></span> Tìm Kiếm
                        </button>
                    </form>
                    <?php if (!empty($search_query)): ?>
                        <p class="search-info" style="text-align: center; margin-top: 20px;">
                            <?php 
                            if ($no_results) {
                                echo 'Không tìm thấy kết quả cho: <strong>' . htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8') . '</strong>';
                            } else {
                                echo 'Tìm thấy <strong>' . count($search_results) . '</strong> sản phẩm cho: <strong>' . htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8') . '</strong>';
                            }
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <?php if ($no_results): ?>
                    <div class="no-results">
                        <div class="no-results-icon">
                            <span class="glyphicon glyphicon-search"></span>
                        </div>
                        <h3>Không Tìm Thấy Sản Phẩm</h3>
                        <p>Xin lỗi, chúng tôi không tìm thấy sản phẩm nào phù hợp với từ khóa của bạn.</p>
                        <p>
                            <a href="products.php" class="btn btn-product-buy" style="display: inline-block; width: auto; padding: 12px 40px;">
                                <span class="glyphicon glyphicon-arrow-left"></span> Xem Tất Cả Sản Phẩm
                            </a>
                        </p>
                    </div>
                <?php elseif (!empty($search_results)): ?>
                    <div class="search-results-header">
                        <h2>Kết Quả Tìm Kiếm (<?php echo count($search_results); ?> sản phẩm)</h2>
                    </div>
                    
                    <div class="row">
                        <?php foreach ($search_results as $product): ?>
                            <?php
                                // Map product names to image files (same as product.php)
                                $product_images = array(
                                    'Cannon EOS' => 'img/cannon_eos.jpg',
                                    'Sony DSLR' => 'img/sony_dslr.jpeg',
                                    'Olympus DSLR' => 'img/olympus.jpg',
                                    'Titan Model #301' => 'img/titan301.jpg',
                                    'Titan Model #201' => 'img/titan201.jpg',
                                    'HMT Milan' => 'img/hmt.JPG',
                                    'Favre Lueba #111' => 'img/favreleuba.jpg',
                                    'Raymond' => 'img/raymond.jpg',
                                    'Charles' => 'img/charles.jpg',
                                    'HXR' => 'img/HXR.jpg',
                                    'PINK' => 'img/pink.jpg'
                                );
                                
                                $image_path = isset($product_images[$product['name']]) ? $product_images[$product['name']] : 'img/default_product.jpg';
                            ?>
                            <div class="col-md-3 col-sm-6">
                                <div class="product-card">
                                    <div class="product-image-container">
                                        <a href="product.php?id=<?php echo intval($product['id']); ?>">
                                            <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                                 class="product-card-image"
                                                 onerror="this.src='img/camera.jpg'">
                                        </a>
                                        <?php if (!empty($product['stock_quantity']) && $product['stock_quantity'] < 5): ?>
                                            <span class="product-badge product-badge-low">Sắp hết</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-card-body">
                                        <h3 class="product-card-title"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                        <div class="product-card-price">
                                            <?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ
                                        </div>
                                        <?php if (!empty($product['stock_quantity'])): ?>
                                            <div class="product-card-stock">
                                                <span class="glyphicon glyphicon-check"></span> Còn lại: <?php echo $product['stock_quantity']; ?> sản phẩm
                                            </div>
                                        <?php endif; ?>
                                        <div class="product-card-actions">
                                            <a href="product.php?id=<?php echo intval($product['id']); ?>" class="btn btn-product-detail">Xem Chi Tiết</a>
                                            <?php if(!isset($_SESSION['email'])): ?>
                                                <a href="login.php" class="btn btn-product-buy">Mua Ngay</a>
                                            <?php else:
                                                if(check_if_added_to_cart($product['id'])):
                                                    echo '<a href="cart.php" class="btn btn-product-added disabled">Đã thêm vào giỏ</a>';
                                                else:
                                            ?>
                                                <a href="product.php?id=<?php echo intval($product['id']); ?>" class="btn btn-product-add">Thêm vào giỏ</a>
                                            <?php 
                                                endif;
                                            endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <br><br><br><br>
            <footer class="footer">
                <div class="container">
                    <center>
                        <p>Copyright &copy Figure Shop. All Rights Reserved. | Liên Hệ: + 84 0854008327</p>
                        <p>Shop mô hình chính hãng - Nơi hội tụ đam mê sưu tầm</p>
                    </center>
                </div>
            </footer>
        </div>
    </body>
</html>
