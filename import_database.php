<?php
// Import database using existing connection
require_once 'connection.php';

echo "<style>body { font-family: Arial; margin: 20px; } h2 { color: #333; } .success { color: green; } .error { color: red; } .info { color: blue; }</style>";

echo "<h2>Database Import Script</h2>";

// Check if tables exist
$result = mysqli_query($con, "SHOW TABLES");
$tables = [];
while ($row = mysqli_fetch_array($result)) {
    $tables[] = $row[0];
}

echo "<p class='info'>Current tables: " . count($tables) . "</p>";

if (count($tables) > 0) {
    echo "<h3>Existing Tables:</h3><ul>";
    foreach ($tables as $table) {
        $countResult = mysqli_query($con, "SELECT COUNT(*) as cnt FROM `$table`");
        $count = mysqli_fetch_assoc($countResult)['cnt'];
        echo "<li><strong>$table</strong>: $count rows</li>";
    }
    echo "</ul>";
    echo "<p class='success'>✓ Database already has data!</p>";
    echo "<hr>";
    echo "<h3>Test Links:</h3>";
    echo "<ul>";
    echo "<li><a href='index.php'>Home Page</a></li>";
    echo "<li><a href='products.php'>Products Page</a></li>";
    echo "<li><a href='admin_login.php'>Admin Login</a></li>";
    echo "<li><a href='login.php'>User Login</a></li>";
    echo "</ul>";
} else {
    echo "<h3 class='error'>Database is empty. Importing store.sql...</h3>";
    
    // Read SQL file
    $sqlFile = __DIR__ . '/store.sql';
    if (!file_exists($sqlFile)) {
        echo "<p class='error'>✗ Error: store.sql not found!</p>";
        exit;
    }
    
    $sql = file_get_contents($sqlFile);
    echo "<p class='info'>✓ Reading store.sql (" . number_format(strlen($sql)) . " bytes)</p>";
    
    // Execute SQL
    echo "<p class='info'>Importing data...</p>";
    
    // Split and execute
    $statements = explode(';', $sql);
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        if (mysqli_query($con, $statement)) {
            $success++;
        } else {
            $error = mysqli_error($con);
            if (stripos($error, 'already exists') === false) {
                $errors++;
            }
        }
    }
    
    echo "<p class='success'>✓ Import complete!</p>";
    echo "<p>Success: $success | Errors: $errors</p>";
    
    // Show tables again
    $result = mysqli_query($con, "SHOW TABLES");
    echo "<h3>Tables created:</h3><ul>";
    while ($row = mysqli_fetch_array($result)) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    
    echo "<hr>";
    echo "<p><a href='index.php'>Go to Home Page</a></p>";
}

mysqli_close($con);
?>
