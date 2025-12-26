<!DOCTYPE html>
<html>
<head>
    <title>MySQL Password Test</title>
    <style>
        body { font-family: Arial; padding: 20px; max-width: 600px; margin: 0 auto; }
        input[type="password"] { padding: 10px; width: 300px; font-size: 16px; }
        button { padding: 10px 20px; font-size: 16px; background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
        .result { margin-top: 20px; padding: 15px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h2>MySQL Password Finder</h2>
    <p>Please enter your MySQL root password:</p>
    
    <form method="POST">
        <input type="password" name="mysql_password" placeholder="Enter password" autofocus>
        <button type="submit">Test Password</button>
    </form>
    
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        mysqli_report(MYSQLI_REPORT_OFF);
        
        $password = $_POST['mysql_password'] ?? '';
        
        echo "<div class='result'>";
        echo "<h3>Testing password...</h3>";
        
        $con = @mysqli_connect("localhost", "root", $password);
        
        if ($con) {
            echo "<div class='success'>";
            echo "<h3>✓ SUCCESS!</h3>";
            echo "<p>Password works! Updating connection.php...</p>";
            
            mysqli_close($con);
            
            // Update connection.php in both locations
            $files = [
                __DIR__ . '/connection.php',
                'C:/xampp/htdocs/WebSec/connection.php'
            ];
            
            foreach ($files as $connFile) {
                if (file_exists($connFile)) {
                    $content = file_get_contents($connFile);
                    $pattern = '/\$con = mysqli_connect\("localhost", "root", ".*?", "store"\)/';
                    $replacement = '$con = mysqli_connect("localhost", "root", "' . addslashes($password) . '", "store")';
                    $newContent = preg_replace($pattern, $replacement, $content);
                    
                    if (file_put_contents($connFile, $newContent)) {
                        echo "<p>✓ Updated: $connFile</p>";
                    }
                }
            }
            
            echo "<hr>";
            echo "<h3>Next Steps:</h3>";
            echo "<ol>";
            echo "<li><a href='import_database.php'>Import Database</a></li>";
            echo "<li><a href='index.php'>Go to Home Page</a></li>";
            echo "</ol>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<h3>✗ Failed</h3>";
            echo "<p>Password incorrect. Please try again.</p>";
            echo "<p>Error: " . mysqli_connect_error() . "</p>";
            echo "</div>";
        }
        echo "</div>";
    }
    ?>
    
    <hr>
    <h3>Common Passwords to Try:</h3>
    <ul>
        <li>Empty (just click Test without typing anything)</li>
        <li>root</li>
        <li>kali</li>
        <li>password</li>
        <li>admin</li>
        <li>Your Windows username</li>
    </ul>
</body>
</html>
