<?php
// Initialize secure session (must be before any other code)
require_once __DIR__ . '/SessionManager.php';
SessionManager::initializeSecureSession();

$con=mysqli_connect("localhost","root","","store") or die(mysqli_error($con));
?>
