<?php
// Check database status
$con = @mysqli_connect("localhost", "root", "", "store");

if (!$con) {
    echo "<h2>Database Connection Failed</h2>";
    echo "<p>Error: " . mysqli_connect_error() . "</p>";
    exit;
}

echo "<h2>Database Connection: OK</h2>";

// Check if tables exist
$result = mysqli_query($con, "SHOW TABLES");
$tables = [];
while ($row = mysqli_fetch_array($result)) {
    $tables[] = $row[0];
}

echo "<h3>Tables in database: " . count($tables) . "</h3>";

if (count($tables) == 0) {
    echo "<p style='color: red;'>⚠️ Database is empty! Need to import store.sql</p>";
    echo "<a href='import_database.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Import Database Now</a>";
} else {
    echo "<ul>";
    foreach ($tables as $table) {
        $countResult = mysqli_query($con, "SELECT COUNT(*) as cnt FROM `$table`");
        $count = mysqli_fetch_assoc($countResult)['cnt'];
        echo "<li><strong>$table</strong>: $count rows</li>";
    }
    echo "</ul>";
    
    echo "<hr>";
    echo "<h3>Access Website:</h3>";
    echo "<ul>";
    echo "<li><a href='index.php'>Home Page</a></li>";
    echo "<li><a href='products.php'>Products</a></li>";
    echo "<li><a href='admin_login.php'>Admin Login</a></li>";
    echo "</ul>";
}

mysqli_close($con);
?>
