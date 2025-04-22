<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connection.php';

try {
    if (!isset($_GET['email'])) {
        throw new Exception('User email is required');
    }

    $userEmail = $_GET['email'];
    error_log("Fetching bookings for user: " . $userEmail);

    // Join with payments table to get payment information
    $sql = "SELECT 
                b.id as booking_id,
                b.event_id,
                b.event_type,
                b.event_name,
                b.event_date,
                b.ticket_type,
                b.quantity,
                b.total_amount,
                b.booking_date,
                b.status as booking_status,
                b.email,
                p.status as payment_status,
                p.payment_date,
                p.transaction_id
            FROM bookings b
            LEFT JOIN payments p ON b.id = p.booking_id
            WHERE b.email = ?
            ORDER BY b.booking_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        // Format the booking data
        $booking = [
            'id' => $row['booking_id'],
            'event_id' => $row['event_id'],
            'event_type' => $row['event_type'],
            'event_name' => $row['event_name'],
            'event_date' => $row['event_date'],
            'ticket_type' => $row['ticket_type'],
            'quantity' => $row['quantity'],
            'total_amount' => $row['total_amount'],
            'booking_date' => $row['booking_date'],
            'status' => $row['booking_status'],
            'payment_status' => $row['payment_status'] ?? 'pending',
            'payment_date' => $row['payment_date'],
            'transaction_id' => $row['transaction_id']
        ];
        
        $bookings[] = $booking;
    }
    
    error_log("Found " . count($bookings) . " bookings for user " . $userEmail);
    
    echo json_encode([
        'success' => true,
        'bookings' => $bookings
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_user_bookings.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Clean up
if (isset($stmt)) {
    $stmt->close();
}
if (isset($conn)) {
    $conn->close();
}
?> 