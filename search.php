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
                padding: 20px;
                background-color: #f9f9f9;
                border-radius: 8px;
            }
            .search-form {
                display: flex;
                gap: 10px;
            }
            .search-form input {
                flex: 1;
                padding: 10px 15px;
                font-size: 16px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .search-form button {
                padding: 10px 30px;
                font-size: 16px;
            }
            .search-results-header {
                margin-top: 30px;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 2px solid #ddd;
            }
            .no-results {
                padding: 40px 20px;
                text-align: center;
                color: #999;
            }
            .search-info {
                color: #666;
                font-size: 14px;
                margin-top: 10px;
            }
        </style>
    </head>
    <body>
        <div>
            <?php require 'header.php'; ?>
            
            <div class="container">
                <div class="search-container">
                    <h2>Search Products</h2>
                    <form method="POST" action="search.php" class="search-form">
                        <input type="text" 
                               name="search" 
                               placeholder="Search by product name (cameras, watches, shirts...)" 
                               value="<?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?>" 
                               maxlength="255"
                               required>
                        <button type="submit" class="btn btn-primary">
                            <span class="glyphicon glyphicon-search"></span> Search
                        </button>
                        <?php if (!empty($search_query)): ?>
                            <a href="products.php" class="btn btn-default">
                                <span class="glyphicon glyphicon-remove"></span> Clear
                            </a>
                        <?php endif; ?>
                    </form>
                    <?php if (!empty($search_query)): ?>
                        <p class="search-info">
                            <?php 
                            if ($no_results) {
                                echo 'No results found for: <strong>' . htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8') . '</strong>';
                            } else {
                                echo 'Found <strong>' . count($search_results) . '</strong> product(s) matching: <strong>' . htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8') . '</strong>';
                            }
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <?php if ($no_results): ?>
                    <div class="no-results">
                        <h4>No Products Found</h4>
                        <p>Sorry, we couldn't find any products matching your search.</p>
                        <p>
                            <a href="products.php" class="btn btn-primary">
                                <span class="glyphicon glyphicon-arrow-left"></span> Back to All Products
                            </a>
                        </p>
                    </div>
                <?php elseif (!empty($search_results)): ?>
                    <div class="search-results-header">
                        <h3>Search Results (<?php echo count($search_results); ?> product<?php echo count($search_results) != 1 ? 's' : ''; ?>)</h3>
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
                                <div class="thumbnail">
                                    <a href="product.php?id=<?php echo intval($product['id']); ?>">
                                        <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                             onerror="this.src='img/default_product.jpg'">
                                    </a>
                                    <center>
                                        <div class="caption">
                                            <h4><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                            <p><strong>Price: Rs. <?php echo number_format($product['price'], 2); ?></strong></p>
                                            
                                            <a href="product.php?id=<?php echo intval($product['id']); ?>" class="btn btn-info btn-block">
                                                View Details
                                            </a>
                                            
                                            <?php if(!isset($_SESSION['email'])): ?>
                                                <a href="login.php" class="btn btn-primary btn-block" style="margin-top: 5px;">
                                                    Buy Now
                                                </a>
                                            <?php else: ?>
                                                <?php if(check_if_added_to_cart($product['id'])): ?>
                                                    <a href="#" class="btn btn-success btn-block" style="margin-top: 5px;" disabled>
                                                        ✓ Added to cart
                                                    </a>
                                                <?php else: ?>
                                                    <a href="product.php?id=<?php echo intval($product['id']); ?>" class="btn btn-primary btn-block" style="margin-top: 5px;">
                                                        Add to cart
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </center>
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
