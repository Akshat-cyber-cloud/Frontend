<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get form data
    $eventId = $_POST['eventId'];
    $eventType = $_POST['eventType'];
    $eventName = $_POST['eventName'];
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $ticketType = $_POST['ticketType'];
    $quantity = (int)$_POST['quantity'];
    $bookingDate = $_POST['date'];

    // Fetch event details from the events table
    $eventQuery = "SELECT * FROM events WHERE event_id = ?";
    $eventStmt = $conn->prepare($eventQuery);
    $eventStmt->bind_param("s", $eventId);
    $eventStmt->execute();
    $eventResult = $eventStmt->get_result();
    
    if ($eventResult->num_rows === 0) {
        // If event not found, use default values
        $prices = [
            'vip' => 2999,
            'premium' => 1999,
            'standard' => 999
        ];
        $eventName = $eventName; // Use the provided event name
    } else {
        // Use event details from database
        $event = $eventResult->fetch_assoc();
        $prices = [
            'vip' => $event['vip_price'],
            'premium' => $event['premium_price'],
            'standard' => $event['standard_price']
        ];
        $eventName = $event['event_name'];
        
        // Check if there are enough seats available
        if ($event['available_seats'] < $quantity) {
            throw new Exception("Not enough seats available. Only {$event['available_seats']} remaining.");
        }
        
        // Update available seats
        $newAvailableSeats = $event['available_seats'] - $quantity;
        $updateSeatsQuery = "UPDATE events SET available_seats = ? WHERE event_id = ?";
        $updateSeatsStmt = $conn->prepare($updateSeatsQuery);
        $updateSeatsStmt->bind_param("is", $newAvailableSeats, $eventId);
        $updateSeatsStmt->execute();
        $updateSeatsStmt->close();
    }
    
    $eventStmt->close();
    
    // Calculate total amount
    $totalAmount = $prices[$ticketType] * $quantity;

    // Prepare SQL statement for booking
    $sql = "INSERT INTO bookings (
        event_id, 
        event_type, 
        event_name, 
        first_name, 
        last_name, 
        email, 
        phone, 
        ticket_type, 
        quantity, 
        booking_date, 
        total_amount
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssisd",
        $eventId,
        $eventType,
        $eventName,
        $firstName,
        $lastName,
        $email,
        $phone,
        $ticketType,
        $quantity,
        $bookingDate,
        $totalAmount
    );

    if ($stmt->execute()) {
        $bookingId = $stmt->insert_id;
        
        // We'll create payment records only during payment processing
        // Removed the code that creates an initial payment record
        
        echo json_encode([
            'success' => true,
            'message' => 'Booking successful!',
            'bookingId' => $bookingId,
            'totalAmount' => $totalAmount,
            'redirect' => '../Frontend/payment.html?bookingId=' . $bookingId
        ]);
    } else {
        throw new Exception("Error executing query: " . $stmt->error);
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?> 