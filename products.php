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
                <div class="page-header-section">
                    <div class="page-header-content">
                        <h1 class="page-title"><?php echo htmlspecialchars($page_title); ?></h1>
                        <p class="page-subtitle"><?php echo htmlspecialchars($page_description); ?></p>
                        
                        <!-- Search Bar -->
                        <div class="page-search-wrapper">
                            <form method="POST" action="search.php" class="page-search-form">
                                <div class="input-group page-search-group">
                                    <input type="text" 
                                           class="form-control live-search-input page-search-input" 
                                           id="productsSearchInput"
                                           placeholder="Tìm kiếm mô hình..." 
                                           name="search"
                                           maxlength="255"
                                           required>
                                    <span class="input-group-btn">
                                        <button class="btn btn-page-search" type="submit">
                                            <span class="glyphicon glyphicon-search"></span> Tìm Kiếm
                                        </button>
                                    </span>
                                    <div id="productsSearchResults" class="live-search-results"></div>
                                </div>
                            </form>
                        </div>
                    </div>
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
                                <div class="product-card">
                                    <div class="product-image-container">
                                        <a href="product.php?id=<?php echo intval($product['id']); ?>">
                                            <?php 
                                            // Use product image from database if available
                                            if (!empty($product['image']) && file_exists($product['image'])) {
                                                $image_path = $product['image'];
                                            } else {
                                                // Try to find image by product name
                                                $image_path = 'img/' . strtolower(str_replace(' ', '_', $product['name'])) . '.jpg';
                                                if (!file_exists($image_path)) {
                                                    // Try with different extensions
                                                    $possible_paths = array(
                                                        'img/' . strtolower(str_replace(' ', '_', $product['name'])) . '.png',
                                                        'img/' . strtolower(str_replace('#', '', str_replace(' ', '_', $product['name']))) . '.jpg',
                                                        'img/camera.jpg' // Default fallback
                                                    );
                                                    $image_path = 'img/camera.jpg';
                                                    foreach($possible_paths as $path) {
                                                        if(file_exists($path)) {
                                                            $image_path = $path;
                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                            ?>
                                            <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-card-image">
                                        </a>
                                        <?php if (!empty($product['stock_quantity']) && $product['stock_quantity'] < 5): ?>
                                            <span class="product-badge product-badge-low">Sắp hết</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-card-body">
                                        <h3 class="product-card-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                        <div class="product-card-price">
                                            <?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ
                                        </div>
                                        <?php if (!empty($product['stock_quantity'])): ?>
                                            <div class="product-card-stock">
                                                <span class="glyphicon glyphicon-check"></span> Còn lại: <?php echo $product['stock_quantity']; ?> sản phẩm
                                            </div>
                                        <?php endif; ?>
                                        <div class="product-card-actions">
                                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-product-detail">Xem Chi Tiết</a>
                                            <?php if(!isset($_SESSION['email'])): ?>
                                                <a href="login.php" class="btn btn-product-buy">Mua Ngay</a>
                                            <?php else:
                                                if(check_if_added_to_cart($product['id'])):
                                                    echo '<a href="#" class="btn btn-product-added disabled">Đã thêm vào giỏ</a>';
                                                else:
                                            ?>
                                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-product-add">Thêm vào giỏ</a>
                                            <?php 
                                                endif;
                                            endif; ?>
                                        </div>
                                    </div>
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
        
        <?php require 'hotline_widget.php'; ?>
        
        <script type="text/javascript">
        (function() {
            var searchInput = document.getElementById('productsSearchInput');
            var resultsDiv = document.getElementById('productsSearchResults');
            var searchTimeout;
            
            if (searchInput && resultsDiv) {
                searchInput.addEventListener('input', function() {
                    var query = this.value.trim();
                    
                    clearTimeout(searchTimeout);
                    
                    if (query.length < 2) {
                        resultsDiv.innerHTML = '';
                        resultsDiv.style.display = 'none';
                        return;
                    }
                    
                    searchTimeout = setTimeout(function() {
                        var xhr = new XMLHttpRequest();
                        xhr.open('GET', 'ajax_search.php?q=' + encodeURIComponent(query), true);
                        xhr.onreadystatechange = function() {
                            if (xhr.readyState === 4 && xhr.status === 200) {
                                try {
                                    var results = JSON.parse(xhr.responseText);
                                    displaySearchResults(results, resultsDiv, query);
                                } catch (e) {
                                    resultsDiv.innerHTML = '';
                                    resultsDiv.style.display = 'none';
                                }
                            }
                        };
                        xhr.send();
                    }, 300);
                });
                
                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
                        resultsDiv.style.display = 'none';
                    }
                });
                
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        resultsDiv.style.display = 'none';
                    }
                });
            }
            
            function displaySearchResults(results, container, query) {
                if (results.length === 0) {
                    container.innerHTML = '<div class="live-search-item no-results">Không tìm thấy sản phẩm</div>';
                    container.style.display = 'block';
                    return;
                }
                
                var html = '';
                results.forEach(function(item) {
                    html += '<a href="product.php?id=' + item.id + '" class="live-search-item">';
                    html += '<div class="live-search-item-name">' + highlightText(item.name, query) + '</div>';
                    html += '<div class="live-search-item-info">';
                    html += '<span class="live-search-item-price">' + item.price + ' VNĐ</span>';
                    if (item.category) {
                        html += '<span class="live-search-item-category">' + item.category + '</span>';
                    }
                    html += '</div>';
                    html += '</a>';
                });
                
                container.innerHTML = html;
                container.style.display = 'block';
            }
            
            function highlightText(text, query) {
                if (!query) return text;
                var regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                return text.replace(regex, '<strong>$1</strong>');
            }
        })();
        </script>
    </body>
</html>
