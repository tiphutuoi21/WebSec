<?php
    require 'connection.php';
    require 'check_if_added.php';
    
    // Get product ID from URL
    $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($product_id <= 0) {
        header('location: products.php');
        exit;
    }
    
    // Fetch product details using prepared statement
    $query = "SELECT * FROM items WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        header('location: products.php');
        exit;
    }
    
    $product = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title><?php echo htmlspecialchars($product['name']); ?> - Figure Shop</title>
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
            .product-detail-container {
                margin-top: 30px;
                margin-bottom: 50px;
            }
            
            .breadcrumb {
                background: transparent;
                padding: 15px 0;
                margin-bottom: 20px;
            }
            
            .breadcrumb a {
                color: var(--primary-red);
                text-decoration: none;
            }
            
            .breadcrumb a:hover {
                color: var(--dark-red);
                text-decoration: underline;
            }
            
            .breadcrumb > li + li:before {
                content: "› ";
                color: var(--dark-gray);
                padding: 0 8px;
            }
            
            .product-image-wrapper {
                text-align: center;
                padding: 30px;
                background: linear-gradient(135deg, #f8f8f8 0%, #fff 100%);
                border-radius: 12px;
                border: 3px solid var(--primary-yellow);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            }
            
            .product-image {
                max-width: 100%;
                max-height: 500px;
                margin: 0 auto;
                border-radius: 8px;
                transition: transform 0.3s ease;
            }
            
            .product-image:hover {
                transform: scale(1.05);
            }
            
            .product-details {
                padding: 30px;
                background: #fff;
                border-radius: 12px;
                border: 2px solid var(--dark-gray);
            }
            
            .product-name {
                font-size: 32px;
                font-weight: 900;
                color: var(--primary-black);
                margin-bottom: 20px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            
            .product-price {
                font-size: 36px;
                color: var(--primary-red);
                font-weight: 900;
                margin: 25px 0;
                padding: 15px 0;
                border-top: 2px solid var(--primary-yellow);
                border-bottom: 2px solid var(--primary-yellow);
            }
            
            .product-description {
                font-size: 16px;
                color: var(--dark-gray);
                line-height: 1.8;
                margin: 25px 0;
            }
            
            .product-info {
                background: #f8f8f8;
                padding: 20px;
                border-radius: 8px;
                margin: 25px 0;
                border-left: 4px solid var(--primary-red);
            }
            
            .product-info-item {
                display: flex;
                justify-content: space-between;
                padding: 10px 0;
                border-bottom: 1px solid #ddd;
            }
            
            .product-info-item:last-child {
                border-bottom: none;
            }
            
            .product-info-label {
                font-weight: 700;
                color: var(--primary-black);
            }
            
            .product-info-value {
                color: var(--dark-gray);
            }
            
            .quantity-selector {
                margin: 30px 0;
                padding: 20px;
                background: #f8f8f8;
                border-radius: 8px;
            }
            
            .quantity-selector label {
                font-weight: 700;
                margin-right: 15px;
                color: var(--primary-black);
                font-size: 16px;
            }
            
            .quantity-input {
                width: 100px;
                padding: 10px;
                border: 2px solid var(--dark-gray);
                border-radius: 6px;
                font-size: 18px;
                text-align: center;
                font-weight: bold;
            }
            
            .quantity-input:focus {
                border-color: var(--primary-red);
                box-shadow: 0 0 8px rgba(220, 20, 60, 0.3);
                outline: none;
            }
            
            .btn-add-to-cart {
                margin-top: 30px;
                padding: 15px 40px;
                font-size: 20px;
                font-weight: 900;
                background: var(--primary-red);
                color: #fff;
                border: none;
                border-radius: 8px;
                text-transform: uppercase;
                letter-spacing: 1px;
                transition: all 0.3s ease;
                width: 100%;
            }
            
            .btn-add-to-cart:hover {
                background: var(--dark-red);
                transform: translateY(-3px);
                box-shadow: 0 6px 20px rgba(220, 20, 60, 0.4);
            }
            
            .btn-buy-now {
                margin-top: 15px;
                padding: 15px 40px;
                font-size: 20px;
                font-weight: 900;
                background: var(--primary-yellow);
                color: var(--primary-black);
                border: none;
                border-radius: 8px;
                text-transform: uppercase;
                letter-spacing: 1px;
                transition: all 0.3s ease;
                width: 100%;
            }
            
            .btn-buy-now:hover {
                background: var(--dark-yellow);
                transform: translateY(-3px);
                box-shadow: 0 6px 20px rgba(255, 215, 0, 0.4);
            }
        </style>
    </head>
    <body>
        <div>
            <?php require 'header.php'; ?>
            
            <div class="container product-detail-container">
                <!-- Breadcrumb -->
                <ol class="breadcrumb">
                    <li><a href="products.php">Products</a></li>
                    <li class="active"><?php echo htmlspecialchars($product['name']); ?></li>
                </ol>
                
                <div class="row">
                    <!-- Product Image -->
                    <div class="col-md-6">
                        <div class="product-image-wrapper">
                            <?php
                                // Map product names to image files
                                $product_images = array(
                                    'Cannon EOS' => 'img/cannon_eos.jpg',
                                    'Sony DSLR' => 'img/sony_dslr.jpeg',
                                    'Olympus DSLR' => 'img/olympus.jpg',
                                    'Titan Model #301' => 'img/titan_301.jpg',
                                    'Titan Model #201' => 'img/titan_201.jpg',
                                    'HMT Milan' => 'img/hmt_milan.jpg',
                                    'Favre Lueba #111' => 'img/favre_lueba.jpg',
                                    'Raymond' => 'img/raymond.jpg',
                                    'Charles' => 'img/charles.jpg',
                                    'HXR' => 'img/hxr.jpg',
                                    'PINK' => 'img/pink.jpg'
                                );
                                
                                // Priority 1: Use product image from database if available
                                if (!empty($product['image']) && file_exists($product['image'])) {
                                    $image_path = $product['image'];
                                } 
                                // Priority 2: Try mapping by product name
                                elseif (isset($product_images[$product['name']]) && file_exists($product_images[$product['name']])) {
                                    $image_path = $product_images[$product['name']];
                                } 
                                // Priority 3: Try to find image by product name pattern
                                else {
                                    $possible_paths = array(
                                        'img/' . strtolower(str_replace(' ', '_', $product['name'])) . '.jpg',
                                        'img/' . strtolower(str_replace(' ', '_', $product['name'])) . '.png',
                                        'img/' . strtolower(str_replace('#', '', str_replace(' ', '_', $product['name']))) . '.jpg',
                                        'img/' . strtolower(str_replace(' ', '_', $product['name'])) . '.jpeg',
                                        'img/camera.jpg' // Default fallback
                                    );
                                    
                                    $image_path = 'img/camera.jpg'; // Default fallback
                                    foreach($possible_paths as $path) {
                                        if(file_exists($path)) {
                                            $image_path = $path;
                                            break;
                                        }
                                    }
                                }
                                
                                // Display image
                                if (file_exists($image_path)) {
                                    echo '<img src="' . htmlspecialchars($image_path) . '" alt="' . htmlspecialchars($product['name']) . '" class="product-image">';
                                } else {
                                    echo '<div style="padding: 50px; background-color: #eee; text-align: center; border-radius: 8px;">';
                                    echo '<p style="color: #666; font-size: 16px;">Hình ảnh sản phẩm chưa có</p>';
                                    echo '<p style="color: #999; font-size: 14px;">Vui lòng liên hệ admin để cập nhật hình ảnh</p>';
                                    echo '</div>';
                                }
                            ?>
                        </div>
                    </div>
                    
                    <!-- Product Details -->
                    <div class="col-md-6">
                        <div class="product-details">
                            <h1 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h1>
                            
                            <div class="product-price">
                                <?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ
                            </div>
                            
                            <div class="product-info">
                                <div class="product-info-item">
                                    <span class="product-info-label">Mã sản phẩm:</span>
                                    <span class="product-info-value">#<?php echo $product['id']; ?></span>
                                </div>
                                <?php if (!empty($product['category'])): ?>
                                <div class="product-info-item">
                                    <span class="product-info-label">Danh mục:</span>
                                    <span class="product-info-value"><?php echo htmlspecialchars($product['category']); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="product-info-item">
                                    <span class="product-info-label">Tình trạng:</span>
                                    <span class="product-info-value">
                                        <?php if (!empty($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                                            <span style="color: #28a745; font-weight: bold;">Còn hàng (<?php echo $product['stock_quantity']; ?> sản phẩm)</span>
                                        <?php else: ?>
                                            <span style="color: var(--primary-red); font-weight: bold;">Hết hàng</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <?php if (!empty($product['description'])): ?>
                            <div class="product-description">
                                <h4 style="color: var(--primary-black); margin-bottom: 15px;">Mô tả sản phẩm:</h4>
                                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="quantity-selector">
                                <label for="quantity">Số lượng:</label>
                                <input type="number" id="quantity" name="quantity" class="quantity-input" value="1" min="1" max="<?php echo !empty($product['stock_quantity']) ? $product['stock_quantity'] : 100; ?>">
                            </div>
                            
                            <div>
                                <?php if(!isset($_SESSION['email'])): ?>
                                    <a href="login.php" class="btn btn-add-to-cart">
                                        <span class="glyphicon glyphicon-lock"></span> Đăng Nhập Để Mua
                                    </a>
                                    <p style="text-align: center; margin-top: 15px; color: var(--dark-gray);">
                                        Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng
                                    </p>
                                <?php else:
                                    if(check_if_added_to_cart($product_id)):
                                        echo '<a href="cart.php" class="btn btn-product-added" style="width: 100%; padding: 15px; font-size: 18px; text-align: center; display: block;">';
                                        echo '<span class="glyphicon glyphicon-ok"></span> Đã Thêm Vào Giỏ - Xem Giỏ Hàng';
                                        echo '</a>';
                                    else:
                                ?>
                                    <button type="button" class="btn btn-add-to-cart" onclick="addToCart(<?php echo $product_id; ?>)">
                                        <span class="glyphicon glyphicon-shopping-cart"></span> Thêm Vào Giỏ Hàng
                                    </button>
                                    <a href="checkout.php?id=<?php echo $product_id; ?>&quantity=1" class="btn btn-buy-now">
                                        <span class="glyphicon glyphicon-flash"></span> Mua Ngay
                                    </a>
                                <?php 
                                    endif;
                                endif; ?>
                            </div>
                            
                            <div class="product-features" style="margin-top: 40px; padding: 25px; background: linear-gradient(135deg, #f8f8f8 0%, #fff 100%); border-radius: 10px; border-left: 4px solid var(--primary-yellow);">
                                <h4 style="color: var(--primary-red); margin-bottom: 20px; font-weight: 900;">Tại Sao Chọn Figure Shop?</h4>
                                <ul style="list-style: none; padding: 0;">
                                    <li style="padding: 8px 0; display: flex; align-items: center;">
                                        <span class="glyphicon glyphicon-ok-circle" style="color: var(--primary-red); margin-right: 10px;"></span>
                                        Thanh toán nhanh chóng và an toàn
                                    </li>
                                    <li style="padding: 8px 0; display: flex; align-items: center;">
                                        <span class="glyphicon glyphicon-ok-circle" style="color: var(--primary-red); margin-right: 10px;"></span>
                                        Sản phẩm chính hãng, chất lượng đảm bảo
                                    </li>
                                    <li style="padding: 8px 0; display: flex; align-items: center;">
                                        <span class="glyphicon glyphicon-ok-circle" style="color: var(--primary-red); margin-right: 10px;"></span>
                                        Dịch vụ khách hàng chuyên nghiệp
                                    </li>
                                    <li style="padding: 8px 0; display: flex; align-items: center;">
                                        <span class="glyphicon glyphicon-ok-circle" style="color: var(--primary-red); margin-right: 10px;"></span>
                                        Đổi trả dễ dàng
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Related Products Section -->
                <div class="row" style="margin-top: 50px;">
                    <div class="col-md-12">
                        <div style="text-align: center; padding: 30px; background: linear-gradient(135deg, var(--primary-black) 0%, var(--dark-gray) 100%); border-radius: 12px; border: 3px solid var(--primary-yellow);">
                            <h3 style="color: var(--primary-yellow); margin-bottom: 20px; font-weight: 900;">Tiếp Tục Mua Sắm</h3>
                            <a href="products.php" class="btn btn-product-buy" style="display: inline-block; width: auto; padding: 12px 40px;">
                                <span class="glyphicon glyphicon-th"></span> Xem Tất Cả Sản Phẩm
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            function addToCart(productId) {
                var quantity = document.getElementById('quantity').value;
                
                // Validate quantity
                if (quantity <= 0 || quantity > 100) {
                    alert('Please enter a valid quantity between 1 and 100');
                    return;
                }
                
                // Redirect to cart_add.php with quantity parameter
                window.location.href = 'cart_add.php?id=' + productId + '&quantity=' + quantity;
            }
            
            // Ensure quantity input is always numeric and positive
            document.getElementById('quantity').addEventListener('change', function() {
                if (this.value < 1) {
                    this.value = 1;
                }
                if (this.value > 100) {
                    this.value = 100;
                }
            });
        </script>
    </body>
</html>
