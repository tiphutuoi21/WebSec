<?php
    require 'connection.php';
    
    if(!isset($_SESSION['admin_email'])){
        header('location: admin310817.php');
    }
    
    $id = mysqli_real_escape_string($con, $_POST['id']);
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $price = mysqli_real_escape_string($con, $_POST['price']);
    
    $update_query = "update items set name='$name', price='$price' where id='$id'";
    $update_result = mysqli_query($con, $update_query) or die(mysqli_error($con));
    
    echo "Product updated successfully!";
    ?>
    <meta http-equiv="refresh" content="2;url=admin_manage_products.php" />
    <?php
?>
