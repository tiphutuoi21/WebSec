<?php
    require 'connection.php';
    
    if(!isset($_SESSION['admin_email'])){
        header('location: admin310817.php');
    }
    
    require 'SecurityHelper.php';
    
    $product_id = intval($_GET['id']);
    $product_query = "SELECT * FROM items WHERE id = ?";
    $stmt = mysqli_prepare($con, $product_query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $product_result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($product_result) == 0) {
        echo "<script>alert('Không tìm thấy sản phẩm'); window.location.href='admin_manage_products.php';</script>";
        exit();
    }
    
    $product = mysqli_fetch_array($product_result);
    mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Edit Product - Admin Dashboard</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css">
        <script type="text/javascript" src="bootstrap/js/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="css/style.css" type="text/css">
    </head>
    <body>
        <div class="container">
            <div style="background-color: #222; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <h3 style="color: white; display: inline;">Admin Dashboard</h3>
                <a href="admin_manage_products.php" style="color: white; margin-right: 20px;">Back to Products</a>
                <a href="admin_logout.php" style="color: white; float: right;">Logout</a>
            </div>
            
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3>Edit Product</h3>
                        </div>
                        <div class="panel-body">
                            <form method="post" action="admin_edit_product_submit.php" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                
                                <div class="form-group">
                                    <label>Tên Sản Phẩm:</label>
                                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Giá (VNĐ):</label>
                                    <input type="number" class="form-control" name="price" value="<?php echo $product['price']; ?>" min="0" step="1000" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Danh Mục:</label>
                                    <input type="text" class="form-control" name="category" value="<?php echo htmlspecialchars($product['category'] ?? ''); ?>" placeholder="Ví dụ: Mô hình, Figure, Nendoroid">
                                </div>
                                
                                <div class="form-group">
                                    <label>Mô Tả:</label>
                                    <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label>Số Lượng Tồn Kho:</label>
                                    <input type="number" class="form-control" name="stock_quantity" value="<?php echo $product['stock_quantity'] ?? 0; ?>" min="0">
                                </div>
                                
                                <div class="form-group">
                                    <label>Hình Ảnh Sản Phẩm:</label>
                                    <?php if(!empty($product['image']) && file_exists($product['image'])): ?>
                                        <div style="margin-bottom: 10px;">
                                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Current Image" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
                                            <p><small>Hình ảnh hiện tại: <?php echo htmlspecialchars($product['image']); ?></small></p>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <small class="help-block">Chọn file hình ảnh mới (JPG, PNG, GIF). Để trống nếu không đổi hình. Kích thước tối đa: 5MB</small>
                                    <div id="imagePreview" style="margin-top: 10px; display: none;">
                                        <img id="previewImg" src="" alt="Preview" style="max-width: 300px; max-height: 300px; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="is_new" value="1" <?php echo (!empty($product['is_new']) && $product['is_new']) ? 'checked' : ''; ?>>
                                        Đánh dấu là hàng mới về
                                    </label>
                                </div>
                                
                                <div class="form-group">
                                    <input type="submit" value="Cập Nhật Sản Phẩm" class="btn btn-primary">
                                    <a href="admin_manage_products.php" class="btn btn-default">Hủy</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            document.getElementById('image').addEventListener('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('previewImg').src = e.target.result;
                        document.getElementById('imagePreview').style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    document.getElementById('imagePreview').style.display = 'none';
                }
            });
        </script>
    </body>
</html>
