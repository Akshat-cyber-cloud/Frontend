<?php
$host = 'localhost';
$dbname = 'ticket_booking';
$username = 'root';
$password = '';

try {
    // Create connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully to database: $dbname<br>";
    
    // Check if users table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'users'");
    if($stmt->rowCount() > 0) {
        echo "Users table exists<br>";
        
        // Show table structure
        $stmt = $conn->query("DESCRIBE users");
        echo "<br>Table structure:<br>";
        echo "<pre>";
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
        echo "</pre>";
        
        // Count users
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<br>Number of users in table: " . $count['count'];
    } else {
        echo "Users table does not exist<br>";
    }
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?> 