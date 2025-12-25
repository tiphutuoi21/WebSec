<?php
    require 'connection.php';
    
    // Log logout activity
    if (isset($_SESSION['id'])) {
        SessionManager::logSessionActivity($con, $_SESSION['id'], 'admin_logout', 'Admin logged out');
    }
    
    // Destroy session securely
    SessionManager::destroySession($con);
    
    header('location: admin_login.php');
    exit();
?>
