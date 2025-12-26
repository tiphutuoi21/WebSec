<?php
mysqli_report(MYSQLI_REPORT_OFF);

echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;}</style>";
echo "<h2>Creating Database 'store'...</h2>";

// Connect without selecting a database
$con = @mysqli_connect("localhost", "root", "tuanduongne2004");

if (!$con) {
    echo "<p class='error'>✗ Connection failed: " . mysqli_connect_error() . "</p>";
    exit;
}

echo "<p class='success'>✓ Connected to MySQL</p>";

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (mysqli_query($con, $sql)) {
    echo "<p class='success'>✓ Database 'store' created successfully!</p>";
} else {
    echo "<p class='error'>✗ Error creating database: " . mysqli_error($con) . "</p>";
    mysqli_close($con);
    exit;
}

// Select the database
mysqli_select_db($con, "store");
echo "<p class='success'>✓ Selected database 'store'</p>";

// Import SQL file
echo "<hr><h3>Importing store.sql...</h3>";

$sqlFile = __DIR__ . '/store.sql';
if (!file_exists($sqlFile)) {
    echo "<p class='error'>✗ store.sql not found!</p>";
    exit;
}

$sql = file_get_contents($sqlFile);
echo "<p>File size: " . number_format(strlen($sql)) . " bytes</p>";

// Execute SQL statements
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
echo "<p>Successful: $success | Errors: $errors</p>";

// Show tables
echo "<hr><h3>Database Tables:</h3><ul>";
$result = mysqli_query($con, "SHOW TABLES");
while ($row = mysqli_fetch_array($result)) {
    $table = $row[0];
    $countResult = mysqli_query($con, "SELECT COUNT(*) as cnt FROM `$table`");
    $count = mysqli_fetch_assoc($countResult)['cnt'];
    echo "<li><strong>$table</strong>: $count rows</li>";
}
echo "</ul>";

mysqli_close($con);

echo "<hr>";
echo "<h2 class='success'>✓ Setup Complete!</h2>";
echo "<h3>Access Website:</h3>";
echo "<ul>";
echo "<li><a href='index.php' target='_blank'>Home Page</a></li>";
echo "<li><a href='products.php' target='_blank'>Products</a></li>";
echo "<li><a href='admin_login.php' target='_blank'>Admin Login</a></li>";
echo "<li><a href='login.php' target='_blank'>User Login</a></li>";
echo "</ul>";
?>
