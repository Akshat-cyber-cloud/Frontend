<?php
// Force display of errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>PHP Test Page</h1>";
echo "<p>PHP is working if you can see this message.</p>";

// Test database connection
try {
    $host = 'localhost';
    $dbname = 'ticket_booking';
    $username = 'root';
    $password = '';

    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green;'>Database connection successful!</p>";
    
    // Test users table
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if($result->rowCount() > 0) {
        echo "<p style='color:green;'>Users table exists!</p>";
    } else {
        echo "<p style='color:red;'>Users table does not exist!</p>";
    }
} catch(PDOException $e) {
    echo "<p style='color:red;'>Connection failed: " . $e->getMessage() . "</p>";
}

// Show PHP info
echo "<h2>PHP Information:</h2>";
phpinfo(); 