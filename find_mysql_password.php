<?php
// Disable mysqli exception reporting
mysqli_report(MYSQLI_REPORT_OFF);

echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;}</style>";
echo "<h2>Finding MySQL Password...</h2>";

$passwords = [
    "kali" => "kali",
    "" => "Empty (no password)",
    "root" => "root",
    "password" => "password", 
    "admin" => "admin",
    "mysql" => "mysql",
    "123456" => "123456"
];

$found = false;
$correctPassword = "";

foreach ($passwords as $pass => $label) {
    echo "Testing: <strong>$label</strong>... ";
    
    $con = @mysqli_connect("localhost", "root", $pass);
    
    if ($con) {
        echo "<span class='success'>✓ SUCCESS!</span><br>";
        $correctPassword = $pass;
        $found = true;
        mysqli_close($con);
        break;
    } else {
        echo "<span class='error'>✗ Failed</span><br>";
    }
}

if ($found) {
    echo "<hr>";
    echo "<h3 class='success'>Password Found!</h3>";
    echo "<p>MySQL root password is: <strong>" . ($correctPassword === "" ? "(empty)" : $correctPassword) . "</strong></p>";
    
    // Update connection.php
    $connFile = __DIR__ . '/connection.php';
    $content = file_get_contents($connFile);
    
    // Find and replace the connection line
    $pattern = '/\$con = mysqli_connect\("localhost", "root", ".*?", "store"\)/';
    $replacement = '$con = mysqli_connect("localhost", "root", "' . $correctPassword . '", "store")';
    
    $newContent = preg_replace($pattern, $replacement, $content);
    
    if (file_put_contents($connFile, $newContent)) {
        echo "<p class='success'>✓ Updated connection.php with correct password!</p>";
        echo "<hr>";
        echo "<h3>Next Steps:</h3>";
        echo "<ol>";
        echo "<li><a href='import_database.php'>Import Database</a></li>";
        echo "<li><a href='index.php'>Go to Home Page</a></li>";
        echo "</ol>";
    } else {
        echo "<p class='error'>✗ Could not update connection.php. Please update manually.</p>";
        echo "<p>Change line 10 in connection.php to:</p>";
        echo "<code>\$con = mysqli_connect(\"localhost\", \"root\", \"$correctPassword\", \"store\");</code>";
    }
} else {
    echo "<hr>";
    echo "<h3 class='error'>Password Not Found</h3>";
    echo "<p>None of the common passwords worked. Please check MySQL configuration.</p>";
}
?>
