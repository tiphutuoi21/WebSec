<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    // Require admin login
    if(!isset($_SESSION['admin_email'])){
        header('location: admin310817.php');
        exit();
    }
    
    $users_query = "select * from users";
    $users_result = mysqli_query($con, $users_query) or die(mysqli_error($con));
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/avatar.png" />
        <title>Manage Users - Admin Dashboard</title>
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
                <h3 style="color: white; display: inline;">Admin Dashboard (<?php echo ucfirst(str_replace('_', ' ', $_SESSION['admin_role'])); ?>)</h3>
                <a href="admin_dashboard.php" style="color: white; margin-right: 20px;">Dashboard</a>
                <a href="admin_manage_orders.php" style="color: white; margin-right: 20px;">Manage Orders</a>
                <a href="admin_manage_products.php" style="color: white; margin-right: 20px;">Manage Products</a>
                <a href="admin_logout.php" style="color: white; float: right;">Logout</a>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <h2>Manage Users</h2>
                    <?php if(intval($_SESSION['admin_role_id']) !== 1): ?>
                        <div class="alert alert-info">
                            <strong>Note:</strong> You are viewing the user list. Only Admin role can delete users.
                        </div>
                    <?php endif; ?>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>City</th>
                                <th>Address</th>
                                <?php if(intval($_SESSION['admin_role_id']) === 1): ?>
                                    <th>Action</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                while($row = mysqli_fetch_array($users_result)){
                                    ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td><?php echo $row['contact']; ?></td>
                                        <td><?php echo $row['city']; ?></td>
                                        <td><?php echo $row['address']; ?></td>
                                        <?php if(intval($_SESSION['admin_role_id']) === 1): ?>
                                            <td>
                                                <a href="admin_delete_user.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>
