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
        <link rel="shortcut icon" href="img/lifestyleStore.png" />
        <title><?php echo htmlspecialchars($product['name']); ?> - Lifestyle Store</title>
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
            .product-image-wrapper {
                text-align: center;
                padding: 20px;
                background-color: #f9f9f9;
                border-radius: 8px;
            }
            .product-image {
                max-width: 100%;
                max-height: 500px;
                margin: 0 auto;
            }
            .product-details {
                padding: 20px;
            }
            .product-price {
                font-size: 32px;
                color: #d9534f;
                font-weight: bold;
                margin: 20px 0;
            }
            .product-name {
                font-size: 28px;
                margin-bottom: 15px;
            }
            .quantity-selector {
                margin: 30px 0;
            }
            .quantity-selector label {
                font-weight: bold;
                margin-right: 15px;
            }
            .quantity-input {
                width: 80px;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 16px;
            }
            .btn-add-to-cart {
                margin-top: 30px;
                padding: 12px 30px;
                font-size: 18px;
            }
            .breadcrumb {
                margin-top: 20px;
                margin-bottom: 30px;
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
                                
                                $image_path = isset($product_images[$product['name']]) ? $product_images[$product['name']] : 'img/default_product.jpg';
                                
                                if (file_exists($image_path)) {
                                    echo '<img src="' . htmlspecialchars($image_path) . '" alt="' . htmlspecialchars($product['name']) . '" class="product-image">';
                                } else {
                                    echo '<div style="padding: 50px; background-color: #eee; text-align: center;"><p>Product Image Not Available</p></div>';
                                }
                            ?>
                        </div>
                    </div>
                    
                    <!-- Product Details -->
                    <div class="col-md-6">
                        <div class="product-details">
                            <h1 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h1>
                            
                            <div class="product-price">
                                Rs. <?php echo number_format($product['price'], 2); ?>
                            </div>
                            
                            <hr>
                            
                            <p>
                                <strong>Product ID:</strong> #<?php echo $product['id']; ?>
                            </p>
                            
                            <p>
                                <strong>Availability:</strong> <span class="label label-success">In Stock</span>
                            </p>
                            
                            <div class="quantity-selector">
                                <label for="quantity">Select Quantity:</label>
                                <input type="number" id="quantity" name="quantity" class="quantity-input" value="1" min="1" max="100">
                            </div>
                            
                            <div>
                                <?php if(!isset($_SESSION['email'])){  ?>
                                    <p><a href="login.php" class="btn btn-primary btn-lg btn-block">Login to Purchase</a></p>
                                    <p class="text-center text-muted">Please login to add items to your cart</p>
                                    <?php
                                    }
                                    else{
                                        if(check_if_added_to_cart($product_id)){
                                            echo '<a href="#" class="btn btn-success btn-lg btn-block disabled">Already in Cart</a>';
                                        }else{
                                            ?>
                                            <button type="button" class="btn btn-primary btn-lg btn-add-to-cart btn-block" onclick="addToCart(<?php echo $product_id; ?>)">
                                                <span class="glyphicon glyphicon-shopping-cart"></span> Add to Cart
                                            </button>
                                            <?php
                                        }
                                    }
                                    ?>
                            </div>
                            
                            <hr>
                            
                            <div class="well">
                                <h4>Why Shop with Us?</h4>
                                <ul>
                                    <li>Fast and secure checkout</li>
                                    <li>Quality guaranteed products</li>
                                    <li>Reliable customer service</li>
                                    <li>Easy returns and exchanges</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Related Products Section -->
                <div class="row" style="margin-top: 50px;">
                    <div class="col-md-12">
                        <h3>Continue Shopping</h3>
                        <p><a href="products.php" class="btn btn-default">View All Products</a></p>
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
