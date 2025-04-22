<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connection.php';

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['bookingId'])) {
        throw new Exception('Booking ID is required');
    }

    $bookingId = $data['bookingId'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First check if the booking exists
        $checkSql = "SELECT status FROM bookings WHERE id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $bookingId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $booking = $result->fetch_assoc();
        
        if (!$booking) {
            throw new Exception('Booking not found');
        }
        
        // Delete from payments table first (if exists)
        $deletePmtSql = "DELETE FROM payments WHERE booking_id = ?";
        $deletePmtStmt = $conn->prepare($deletePmtSql);
        $deletePmtStmt->bind_param("s", $bookingId);
        $deletePmtStmt->execute();
        
        // Then delete from bookings table
        $deleteBookingSql = "DELETE FROM bookings WHERE id = ?";
        $deleteBookingStmt = $conn->prepare($deleteBookingSql);
        $deleteBookingStmt->bind_param("s", $bookingId);
        $deleteBookingStmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Booking cancelled successfully'
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Clean up
if (isset($checkStmt)) $checkStmt->close();
if (isset($deletePmtStmt)) $deletePmtStmt->close();
if (isset($deleteBookingStmt)) $deleteBookingStmt->close();
if (isset($conn)) $conn->close();
?> 