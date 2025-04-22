<?php
// Set maximum execution time to 30 seconds
set_time_limit(30);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Start session
session_start();

// Enable error reporting and logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_log("Starting login process...");

$response = ['success' => false, 'message' => '', 'debug' => ''];

// Check if it's a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("Login request received from: " . $_SERVER['REMOTE_ADDR']);
    $rawInput = file_get_contents('php://input');
    error_log("Raw input: " . $rawInput);

    // Get JSON data with better error handling
    $data = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['message'] = 'Invalid JSON data received';
        $response['debug'] = json_last_error_msg();
        echo json_encode($response);
        exit;
    }

    // Validate required fields
    if (empty($data['email']) || empty($data['password'])) {
        $response['message'] = 'Email and password are required';
        $response['debug'] = 'Missing fields: ' . 
            (empty($data['email']) ? 'email ' : '') . 
            (empty($data['password']) ? 'password' : '');
        echo json_encode($response);
        exit;
    }

    $host = 'localhost';
    $dbname = 'ticket_booking';
    $username = 'root';
    $password = '';

    try {
        error_log("Attempting database connection...");
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        error_log("Database connection successful");
        
        // Get form data
        $email = trim($data['email']);
        $password = $data['password'];
        
        error_log("Checking credentials for email: " . $email);
        
        // Get user by email
        $sql = "SELECT id, name, email, password FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            error_log("Login successful for user: " . $user['name']);
            // Password is correct
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['name'];
            
            $response['success'] = true;
            $response['message'] = 'Login successful!';
            $response['username'] = $user['name'];
            $response['email'] = $user['email'];
        } else {
            error_log("Login failed for email: " . $email);
            $response['message'] = 'Invalid email or password';
            $response['debug'] = $user ? 'Password mismatch' : 'User not found';
        }
    } catch (PDOException $e) {
        error_log("Database error during login: " . $e->getMessage());
        $response['message'] = 'Database error occurred';
        $response['debug'] = $e->getMessage();
    }
}

error_log("Sending login response: " . json_encode($response));
echo json_encode($response);