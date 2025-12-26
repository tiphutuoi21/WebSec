<?php
// Setup database script for WebSec
echo "=== Database Setup Script ===\n\n";

// Disable error reporting for connection
mysqli_report(MYSQLI_REPORT_OFF);

// Try different passwords
$passwords = ["", "root", "password", "admin"];
$con = null;

echo "Step 1: Trying to connect to MySQL...\n";
foreach ($passwords as $pass) {
    $con = @mysqli_connect("localhost", "root", $pass);
    if ($con) {
        echo "✓ Connected successfully" . ($pass ? " with password" : " without password") . "!\n\n";
        break;
    }
}

if (!$con) {
    die("✗ Connection failed: Could not connect to MySQL with any common password.\n" .
        "Please check your MySQL installation or set the correct password.\n");
}

// Create database
echo "Step 2: Creating database 'store'...\n";
$sql = "CREATE DATABASE IF NOT EXISTS store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (mysqli_query($con, $sql)) {
    echo "✓ Database 'store' created or already exists\n\n";
} else {
    echo "✗ Error creating database: " . mysqli_error($con) . "\n";
    mysqli_close($con);
    exit(1);
}

// Select the database
mysqli_select_db($con, "store");
echo "Step 3: Selected database 'store'\n\n";

// Read SQL file
$sqlFile = __DIR__ . '/store.sql';
if (!file_exists($sqlFile)) {
    echo "✗ Error: store.sql file not found at: $sqlFile\n";
    mysqli_close($con);
    exit(1);
}

echo "Step 4: Reading store.sql file...\n";
$sql = file_get_contents($sqlFile);
echo "✓ File read successfully (" . number_format(strlen($sql)) . " bytes)\n\n";

echo "Step 5: Importing database schema and data...\n";
echo "(This may take a moment...)\n\n";

// Split by semicolon and execute each statement
$statements = array_filter(array_map('trim', explode(';', $sql)));
$success = 0;
$errors = 0;
$skipped = 0;

foreach ($statements as $statement) {
    if (empty($statement)) continue;
    
    // Skip comments
    if (strpos($statement, '--') === 0 || strpos($statement, '/*') === 0) {
        continue;
    }
    
    if (mysqli_query($con, $statement)) {
        $success++;
    } else {
        $error = mysqli_error($con);
        // Ignore "already exists" errors
        if (stripos($error, 'already exists') !== false) {
            $skipped++;
        } else {
            $errors++;
            // Only show first few critical errors
            if ($errors <= 3) {
                echo "Warning: " . $error . "\n";
            }
        }
    }
}

echo "\n=== Import Summary ===\n";
echo "✓ Successful: $success statements\n";
if ($skipped > 0) {
    echo "⊙ Skipped: $skipped (already exists)\n";
}
if ($errors > 0) {
    echo "✗ Errors: $errors\n";
}

// Verify tables
echo "\nStep 6: Verifying installation...\n";
$result = mysqli_query($con, "SHOW TABLES");
if ($result) {
    $tables = [];
    while ($row = mysqli_fetch_array($result)) {
        $tables[] = $row[0];
    }
    
    echo "✓ Database has " . count($tables) . " tables:\n";
    foreach ($tables as $table) {
        // Count rows
        $countResult = mysqli_query($con, "SELECT COUNT(*) as cnt FROM `$table`");
        $count = mysqli_fetch_assoc($countResult)['cnt'];
        echo "  • $table ($count rows)\n";
    }
}

mysqli_close($con);

echo "\n" . str_repeat("=", 50) . "\n";
echo "SUCCESS! Database setup completed!\n";
echo str_repeat("=", 50) . "\n";
echo "\nYou can now access the website at:\n";
echo "→ http://localhost/WebSec\n";
echo "→ Admin: http://localhost/WebSec/admin_login.php\n";
?>
