<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get database connection
    $conn = require_once 'db_connection.php';
    
    // Get booking ID from query parameter
    $bookingId = isset($_GET['id']) ? $_GET['id'] : null;

    if (!$bookingId) {
        throw new Exception('Booking ID is required');
    }

    // Prepare and execute query to get booking details with payment status
    $stmt = $conn->prepare("
        SELECT 
            b.*,
            p.status as payment_status,
            p.payment_method,
            p.transaction_id,
            p.payment_date
        FROM bookings b
        LEFT JOIN payments p ON b.id = p.booking_id
        WHERE b.id = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $bookingId);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        
        // Format the response
        echo json_encode([
            'success' => true,
            'booking' => [
                'id' => $booking['id'],
                'event_id' => $booking['event_id'],
                'event_type' => $booking['event_type'],
                'event_name' => $booking['event_name'],
                'first_name' => $booking['first_name'],
                'last_name' => $booking['last_name'],
                'email' => $booking['email'],
                'phone' => $booking['phone'],
                'event_date' => $booking['event_date'],
                'ticket_type' => $booking['ticket_type'],
                'quantity' => $booking['quantity'],
                'total_amount' => $booking['total_amount'],
                'booking_date' => $booking['booking_date'],
                'status' => $booking['status'],
                'payment_status' => $booking['payment_status'] ?? 'pending',
                'payment_method' => $booking['payment_method'] ?? null,
                'transaction_id' => $booking['transaction_id'] ?? null
            ]
        ]);
    } else {
        throw new Exception('Booking not found');
    }
} catch (Exception $e) {
    error_log("Error in get_booking.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching booking details: ' . $e->getMessage()
    ]);
}

// Close database connection
if (isset($conn)) {
    $conn->close();
}
?> 