<?php
    require 'connection.php';
    
    if(!isset($_SESSION['admin_email'])){
        header('location: admin310817.php');
    }
    
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $price = mysqli_real_escape_string($con, $_POST['price']);
    
    $add_product_query = "insert into items(name, price) values ('$name', '$price')";
    $add_product_result = mysqli_query($con, $add_product_query) or die(mysqli_error($con));
    
    echo "Product added successfully!";
    ?>
    <meta http-equiv="refresh" content="2;url=admin_manage_products.php" />
    <?php
?>
