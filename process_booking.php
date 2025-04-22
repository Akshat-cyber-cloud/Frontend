<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Function to log errors
function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, __DIR__ . '/error.log');
}

try {
    // Include database connection
    $conn = require_once 'db_connection.php';
    
    // Get POST data
    $postData = file_get_contents('php://input');
    $data = json_decode($postData, true);
    
    logError("Received request data: " . print_r($data, true));

    // Required fields
    $requiredFields = ['eventId', 'firstName', 'lastName', 'email', 'phone', 'ticketType', 'quantity', 'eventDate', 'eventName', 'totalAmount', 'eventType'];
    
    // Validate required fields
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: " . $field);
        }
    }

    // Generate unique booking ID
    $bookingId = uniqid('BK');
    
    // Prepare SQL statement
    $sql = "INSERT INTO bookings (id, event_id, event_type, first_name, last_name, email, phone, event_name, event_date, ticket_type, quantity, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    logError("Preparing SQL statement: " . $sql);
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("ssssssssssid", 
        $bookingId,
        $data['eventId'],
        $data['eventType'],
        $data['firstName'],
        $data['lastName'],
        $data['email'],
        $data['phone'],
        $data['eventName'],
        $data['eventDate'],
        $data['ticketType'],
        $data['quantity'],
        $data['totalAmount']
    );

    // Execute the statement
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    // Close statement
    $stmt->close();
    
    // Close connection
    $conn->close();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Booking successful',
        'data' => [
            'bookingId' => $bookingId,
            'eventName' => $data['eventName'],
            'eventType' => $data['eventType'],
            'eventDate' => $data['eventDate'],
            'ticketType' => $data['ticketType'],
            'quantity' => $data['quantity'],
            'totalAmount' => $data['totalAmount'],
            'email' => $data['email']
        ]
    ]);

} catch (Exception $e) {
    logError("Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 