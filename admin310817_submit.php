<?php
    require 'connection.php';
    
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = md5(md5(mysqli_real_escape_string($con, $_POST['password'])));
    
    // Query with JOIN to get role information
    $admin_query = "SELECT a.id, a.email, a.role_id, r.name as role_name 
                    FROM admins a 
                    JOIN roles r ON a.role_id = r.id 
                    WHERE a.email='$email' AND a.password='$password' AND a.is_active=1";
    $admin_result = mysqli_query($con, $admin_query) or die(mysqli_error($con));
    $rows_fetched = mysqli_num_rows($admin_result);
    
    if($rows_fetched == 0){
        ?>
        <script>
            window.alert("Wrong email or password");
        </script>
        <meta http-equiv="refresh" content="1;url=admin310817.php" />
        <?php
    } else {
        $row = mysqli_fetch_array($admin_result);
        $_SESSION['admin_email'] = $email;
        $_SESSION['admin_id'] = $row['id'];
        $_SESSION['admin_role_id'] = $row['role_id'];
        $_SESSION['admin_role'] = $row['role_name'];
        
        // Update last login time
        $update_login = "UPDATE admins SET last_login=NOW() WHERE id='{$row['id']}'";
        mysqli_query($con, $update_login);
        
        header('location: admin_dashboard.php');
    }
?>
