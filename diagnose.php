<?php
header('Content-Type: text/html');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Diagnostic</h1>";

$host = 'localhost';
$dbname = 'ticket_booking';
$username = 'root';
$password = '';

try {
    // Test database connection
    echo "<h2>Attempting Database Connection...</h2>";
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green;'>✓ Successfully connected to database: $dbname</p>";
    
    // Check if users table exists
    echo "<h2>Checking Users Table...</h2>";
    $stmt = $conn->prepare("SHOW TABLES LIKE 'users'");
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p style='color:green;'>✓ Users table exists</p>";
        
        // Get column structure
        echo "<h2>Table Structure:</h2>";
        $stmt = $conn->prepare("DESCRIBE users");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . $column['Default'] . "</td>";
            echo "<td>" . $column['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check for existing users
        echo "<h2>Existing Users:</h2>";
        $stmt = $conn->prepare("SELECT * FROM users LIMIT 10");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($users) > 0) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr>";
            foreach (array_keys($users[0]) as $header) {
                echo "<th>" . htmlspecialchars($header) . "</th>";
            }
            echo "</tr>";
            
            foreach ($users as $user) {
                echo "<tr>";
                foreach ($user as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color:orange;'>ⓘ No users found in the database.</p>";
        }
        
        // Test INSERT and DELETE functionality
        echo "<h2>Testing INSERT Functionality:</h2>";
        
        // Generate random test data
        $testName = 'test_user_' . time();
        $testEmail = 'test_' . time() . '@example.com';
        $testPassword = password_hash('test123', PASSWORD_DEFAULT);
        
        try {
            // Attempt to insert a test user
            $sql = "INSERT INTO users (name, email, password, created_at) VALUES (:name, :email, :password, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':name', $testName);
            $stmt->bindParam(':email', $testEmail);
            $stmt->bindParam(':password', $testPassword);
            
            if ($stmt->execute()) {
                $testUserId = $conn->lastInsertId();
                echo "<p style='color:green;'>✓ Test user created successfully with ID: $testUserId</p>";
                
                // Clean up the test user
                $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
                $stmt->bindParam(':id', $testUserId);
                if ($stmt->execute()) {
                    echo "<p style='color:green;'>✓ Test user deleted successfully</p>";
                }
            }
        } catch (PDOException $e) {
            echo "<p style='color:red;'>✗ Error testing INSERT: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p style='color:red;'>✗ Users table does not exist!</p>";
        
        // Check if there are any tables
        echo "<h3>Available Tables:</h3>";
        $stmt = $conn->prepare("SHOW TABLES");
        $stmt->execute();
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>" . $table . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color:red;'>No tables found in database.</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>✗ Connection failed: " . $e->getMessage() . "</p>";
}
?> 