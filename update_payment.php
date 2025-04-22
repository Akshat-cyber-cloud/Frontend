<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['bookingId'])) {
        throw new Exception('Missing required field: bookingId');
    }
    
    $bookingId = $data['bookingId'];
    $paymentMethod = $data['paymentMethod'] ?? 'card';
    $status = 'completed';  // Always set to completed for now
    $transactionId = 'TXN_' . time() . rand(1000, 9999);

    // Start transaction
    $conn->begin_transaction();

    try {
        // Get booking amount
        $bookingSql = "SELECT total_amount FROM bookings WHERE id = ?";
        $bookingStmt = $conn->prepare($bookingSql);
        $bookingStmt->bind_param("s", $bookingId);
        $bookingStmt->execute();
        $bookingResult = $bookingStmt->get_result();
        
        if ($bookingResult->num_rows === 0) {
            throw new Exception('Booking not found');
        }
        
        $booking = $bookingResult->fetch_assoc();
        $amount = $booking['total_amount'];
        $bookingStmt->close();

        // Insert or update payment record
        $sql = "INSERT INTO payments (booking_id, amount, payment_method, status, transaction_id) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    payment_method = VALUES(payment_method),
                    status = VALUES(status),
                    transaction_id = VALUES(transaction_id),
                    amount = VALUES(amount)";
                    
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdsss", $bookingId, $amount, $paymentMethod, $status, $transactionId);
        
        if (!$stmt->execute()) {
            throw new Exception("Error processing payment: " . $stmt->error);
        }
        $stmt->close();
        
        // Update booking status
        $updateSql = "UPDATE bookings SET status = 'confirmed' WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("s", $bookingId);
        
        if (!$updateStmt->execute()) {
            throw new Exception("Error updating booking status: " . $updateStmt->error);
        }
        $updateStmt->close();

        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Payment processed successfully',
            'data' => [
                'bookingId' => $bookingId,
                'amount' => $amount,
                'status' => $status,
                'transactionId' => $transactionId
            ]
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Error in update_payment.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

if (isset($conn)) {
    $conn->close();
}
?> 