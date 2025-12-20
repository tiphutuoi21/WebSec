<?php
    require 'connection.php';
    
    if(!isset($_SESSION['admin_email'])){
        header('location: admin310817.php');
    }
    
    $product_id = $_GET['id'];
    $delete_query = "delete from items where id='$product_id'";
    $delete_result = mysqli_query($con, $delete_query) or die(mysqli_error($con));
    
    header('location: admin_manage_products.php');
?>
