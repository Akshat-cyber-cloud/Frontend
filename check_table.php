<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'ticket_booking';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully<br>";
    
    // Get table structure
    $stmt = $conn->query("DESCRIBE users");
    echo "<h3>Table Structure:</h3>";
    echo "<pre>";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";
    
    // Try to insert a test user
    $test_name = "test_user_" . time();
    $test_email = "test" . time() . "@test.com";
    $test_password = password_hash("test123", PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':name', $test_name);
    $stmt->bindParam(':email', $test_email);
    $stmt->bindParam(':password', $test_password);
    
    if ($stmt->execute()) {
        echo "<br>Test user inserted successfully with name: " . $test_name;
        
        // Verify the insert by selecting the user
        $select_sql = "SELECT * FROM users WHERE name = :name";
        $select_stmt = $conn->prepare($select_sql);
        $select_stmt->bindParam(':name', $test_name);
        $select_stmt->execute();
        
        echo "<h3>Inserted User Data:</h3>";
        echo "<pre>";
        print_r($select_stmt->fetch(PDO::FETCH_ASSOC));
        echo "</pre>";
    } else {
        echo "<br>Failed to insert test user";
    }
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?> 