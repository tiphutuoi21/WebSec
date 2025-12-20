<?php
    require 'connection.php';
    
    // This page is deprecated. Redirect to the new checkout process
    if(!isset($_SESSION['email']) || !isset($_SESSION['id'])){
        header('location: login.php');
        exit();
    }
    
    // If this page is accessed directly with an order ID parameter,
    // redirect to order confirmation
    if(isset($_GET['id']) && isset($_SESSION['id'])){
        // The user is trying to use the old success.php flow
        // Redirect them to checkout to use the new proper order process
        header('location: checkout.php');
        exit();
    }
    
    // If no ID is provided, redirect to cart
    header('location: cart.php');
?>
