<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connection.php';

try {
    // Get all bookings from the database
    $sql = "SELECT 
                b.*, 
                p.status as payment_status 
            FROM bookings b 
            LEFT JOIN payments p ON b.id = p.booking_id 
            ORDER BY b.booking_date DESC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Error executing query: " . $conn->error);
    }
    
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'total_bookings' => count($bookings),
        'bookings' => $bookings
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 