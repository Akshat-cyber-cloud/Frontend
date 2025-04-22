<?php
// Prevent direct output of errors
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'ticket_booking';
$port = 3307; // Using port 3307 as specified

try {
    // Create connection with port specification
    $conn = new mysqli($host, $username, $password, $database, $port);

    // Check connection
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Set charset to utf8mb4
    if (!$conn->set_charset("utf8mb4")) {
        error_log("Error setting charset: " . $conn->error);
        throw new Exception("Error setting charset: " . $conn->error);
    }

    // Return the connection
    return $conn;
} catch (Exception $e) {
    // Log the error
    error_log("Database connection error: " . $e->getMessage());
    
    // Return JSON error response
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]));
}

function getConnection() {
    global $host, $database, $username, $password, $port;
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$database;port=$port", $username, $password);
        // Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $conn;
    } catch(PDOException $e) {
        error_log("Connection failed: " . $e->getMessage());
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}

// For backward compatibility with existing code that uses $conn directly
try {
    $conn = new mysqli($host, $username, $password, $database, $port);
    
    // Check connection
    if ($conn->connect_error) {
        error_log("MySQLi Connection failed: " . $conn->connect_error);
        die("Connection failed: " . $conn->connect_error);
    }
} catch(Exception $e) {
    error_log("MySQLi Connection failed: " . $e->getMessage());
    die("Connection failed: " . $e->getMessage());
}
?> 