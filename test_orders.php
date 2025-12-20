<?php
    require 'connection.php';
    
    echo "<h2>Database Status Check</h2>";
    
    // Check if orders table has any data
    $check_orders = "SELECT COUNT(*) as count FROM orders";
    $result = mysqli_query($con, $check_orders);
    $row = mysqli_fetch_array($result);
    echo "<p>Total orders in database: " . $row['count'] . "</p>";
    
    // Check if order_items table has any data
    $check_items = "SELECT COUNT(*) as count FROM order_items";
    $result = mysqli_query($con, $check_items);
    $row = mysqli_fetch_array($result);
    echo "<p>Total order items in database: " . $row['count'] . "</p>";
    
    // Check cart_items
    $check_cart = "SELECT COUNT(*) as count FROM cart_items";
    $result = mysqli_query($con, $check_cart);
    $row = mysqli_fetch_array($result);
    echo "<p>Total cart items in database: " . $row['count'] . "</p>";
    
    // List recent orders
    echo "<h3>Recent Orders:</h3>";
    $recent = "SELECT o.id, u.name, o.total_amount, os.name as status, o.created_at FROM orders o 
               INNER JOIN users u ON u.id = o.user_id 
               INNER JOIN order_statuses os ON os.id = o.status_id 
               ORDER BY o.created_at DESC LIMIT 5";
    $result = mysqli_query($con, $recent);
    
    if(mysqli_num_rows($result) > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr>";
        while($row = mysqli_fetch_array($result)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['name'] . "</td>";
            echo "<td>Rs " . number_format($row['total_amount'], 2) . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>No orders found in database yet.</p>";
    }
?>
