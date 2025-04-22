<?php
// Force display of errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set maximum execution time to 30 seconds
set_time_limit(30);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Start session
session_start();

// Enable error logging with more details
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_log("Starting signup process...");

$response = ['success' => false, 'message' => '', 'debug' => ''];

try {
    // Log incoming request with more details
    error_log("Signup request received from: " . $_SERVER['REMOTE_ADDR']);
    $rawInput = file_get_contents('php://input');
    error_log("Raw input: " . $rawInput);

    // Check if it's a POST request
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception('Only POST method is allowed');
    }

    // Get JSON data with better error handling
    $data = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data received: ' . json_last_error_msg());
    }

    // Log received data (excluding password)
    $logData = $data;
    unset($logData['password']);
    error_log("Received data: " . json_encode($logData));

    // Validate required fields
    if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
        throw new Exception('All fields are required');
    }

    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Validate password length
    if (strlen($data['password']) < 6) {
        throw new Exception('Password must be at least 6 characters long');
    }

    // Database connection
    $host = 'localhost';
    $dbname = 'ticket_booking';
    $username = 'root';
    $password = '';

    error_log("Attempting database connection...");
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Database connection successful");
    
    // Get form data
    $name = trim($data['name']);
    $email = trim($data['email']);
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Check if name or email already exists
    error_log("Checking for existing user with name: $name and email: $email");
    $check_sql = "SELECT COUNT(*) FROM users WHERE name = :name OR email = :email";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bindParam(':name', $name);
    $check_stmt->bindParam(':email', $email);
    $check_stmt->execute();
    
    $existingCount = $check_stmt->fetchColumn();
    error_log("Found $existingCount existing users with matching name or email");
    
    if ($existingCount > 0) {
        // Check specifically which field exists
        $check_name_sql = "SELECT COUNT(*) FROM users WHERE name = :name";
        $check_email_sql = "SELECT COUNT(*) FROM users WHERE email = :email";
        
        $name_stmt = $conn->prepare($check_name_sql);
        $email_stmt = $conn->prepare($check_email_sql);
        
        $name_stmt->bindParam(':name', $name);
        $email_stmt->bindParam(':email', $email);
        
        $name_stmt->execute();
        $email_stmt->execute();
        
        $name_exists = $name_stmt->fetchColumn() > 0;
        $email_exists = $email_stmt->fetchColumn() > 0;
        
        if ($name_exists && $email_exists) {
            throw new Exception('Both username and email are already registered. Please use different credentials.');
        } elseif ($name_exists) {
            throw new Exception('Username is already taken. Please choose a different username.');
        } else {
            throw new Exception('Email is already registered. Please use a different email address.');
        }
    }
    
    // Insert new user
    error_log("Attempting to insert new user...");
    $sql = "INSERT INTO users (name, email, password, created_at) VALUES (:name, :email, :password, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashedPassword);
    
    if ($stmt->execute()) {
        $userId = $conn->lastInsertId();
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $name;
        
        $response['success'] = true;
        $response['message'] = 'Registration successful!';
        $response['username'] = $name;
        error_log("User successfully created with ID: $userId");
    } else {
        throw new Exception('Failed to insert user into database');
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log("Error: " . $e->getMessage());
    if ($e instanceof PDOException) {
        error_log("Database Error: " . $e->getMessage());
        $response['message'] = 'Database error occurred. Please try again later.';
    }
}

// Send response
error_log("Sending response: " . json_encode($response));
echo json_encode($response); 