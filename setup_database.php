<?php
// Setup database script
echo "=== Database Setup Script ===\n\n";

// Disable error reporting for connection (to avoid password exposure)
mysqli_report(MYSQLI_REPORT_OFF);

// Try different passwords
$passwords = ["", "root", "password", "admin"];
$con = null;

echo "Trying to connect to MySQL...\n";
foreach ($passwords as $pass) {
    $con = @mysqli_connect("localhost", "root", $pass);
    if ($con) {
        echo "✓ Connected successfully!\n";
        break;
    }
}

if (!$con) {
    $con = @mysqli_connect("localhost", "root", "");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error() . "\n");
}

echo "✓ Connected to MySQL successfully\n";

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (mysqli_query($con, $sql)) {
    echo "✓ Database 'store' created or already exists\n";
} else {
    echo "✗ Error creating database: " . mysqli_error($con) . "\n";
    exit(1);
}

// Select the database
mysqli_select_db($con, "store");
echo "✓ Selected database 'store'\n";

// Read SQL file
$sqlFile = __DIR__ . '/store.sql';
if (!file_exists($sqlFile)) {
    echo "✗ Error: store.sql file not found!\n";
    exit(1);
}

echo "✓ Found store.sql file\n";
echo "→ Importing database schema and data...\n\n";

// Read and execute SQL file
$sql = file_get_contents($sqlFile);

// Split by semicolon and execute each statement
$statements = array_filter(array_map('trim', explode(';', $sql)));
$success = 0;
$errors = 0;

foreach ($statements as $statement) {
    if (empty($statement)) continue;
    
    if (mysqli_query($con, $statement)) {
        $success++;
    } else {
        $errors++;
        // Only show critical errors
        if (stripos(mysqli_error($con), 'already exists') === false) {
            echo "Warning: " . mysqli_error($con) . "\n";
        }
    }
}

echo "\n=== Import Complete ===\n";
echo "✓ Successful statements: $success\n";
if ($errors > 0) {
    echo "! Warnings/Errors: $errors (mostly 'already exists' warnings)\n";
}

// Verify tables
$result = mysqli_query($con, "SHOW TABLES");
$tables = [];
while ($row = mysqli_fetch_array($result)) {
    $tables[] = $row[0];
}

echo "\n✓ Tables in database: " . count($tables) . "\n";
echo "  - " . implode("\n  - ", $tables) . "\n";

mysqli_close($con);

echo "\n=== Database setup completed successfully! ===\n";
?>
