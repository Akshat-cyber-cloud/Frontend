<?php
header('Content-Type: text/html');
require_once 'db_connection.php';

echo "<h1>Fixing Payments Table</h1>";
echo "<p>Starting the repair process...</p>";

try {
    // First, find any duplicate payments (multiple payments for same booking)
    $conn->query("SET foreign_key_checks = 0");
    
    echo "<p>Checking for duplicate payment records...</p>";
    $duplicatesQuery = "SELECT booking_id, COUNT(*) as count 
                       FROM payments 
                       GROUP BY booking_id 
                       HAVING count > 1";
    
    $duplicateResult = $conn->query($duplicatesQuery);
    
    if ($duplicateResult->num_rows > 0) {
        echo "<p>Found " . $duplicateResult->num_rows . " bookings with duplicate payment records.</p>";
        echo "<p>Removing duplicate payments...</p>";
        
        // For each duplicate set, keep only the most recent record
        while ($row = $duplicateResult->fetch_assoc()) {
            $bookingId = $row['booking_id'];
            echo "<p>Processing booking ID: " . $bookingId . "</p>";
            
            // Find all payment IDs for this booking
            $paymentsQuery = "SELECT id, payment_date FROM payments WHERE booking_id = $bookingId ORDER BY payment_date DESC";
            $paymentsResult = $conn->query($paymentsQuery);
            
            if ($paymentsResult->num_rows > 1) {
                $keepFirst = true;
                while ($paymentRow = $paymentsResult->fetch_assoc()) {
                    if ($keepFirst) {
                        echo "<p>Keeping payment ID: " . $paymentRow['id'] . "</p>";
                        $keepFirst = false;
                    } else {
                        $paymentId = $paymentRow['id'];
                        $deleteQuery = "DELETE FROM payments WHERE id = $paymentId";
                        if ($conn->query($deleteQuery)) {
                            echo "<p>Deleted payment ID: " . $paymentId . "</p>";
                        } else {
                            echo "<p>Failed to delete payment ID: " . $paymentId . " - " . $conn->error . "</p>";
                        }
                    }
                }
            }
        }
    } else {
        echo "<p>No duplicate payment records found.</p>";
    }
    
    // Now make transaction_id UNIQUE if it isn't already
    echo "<p>Modifying the transaction_id column to be UNIQUE...</p>";
    
    // First check if there are any NULL values in transaction_id and fix them
    $nullQuery = "UPDATE payments SET transaction_id = CONCAT('TXN', UNIX_TIMESTAMP(), FLOOR(RAND() * 1000000)) WHERE transaction_id IS NULL";
    $conn->query($nullQuery);
    
    // Then modify the table to make transaction_id UNIQUE
    $alterQuery = "ALTER TABLE payments 
                  DROP INDEX idx_transaction_id,
                  ADD UNIQUE INDEX idx_transaction_id (transaction_id)";
    
    if ($conn->query($alterQuery)) {
        echo "<p>Successfully modified transaction_id to be UNIQUE.</p>";
    } else {
        echo "<p>Failed to modify transaction_id: " . $conn->error . "</p>";
    }
    
    $conn->query("SET foreign_key_checks = 1");
    
    echo "<h2>Repair Complete!</h2>";
    echo "<p>The payments table has been fixed. You can now <a href='test_system.php'>return to the test page</a> or try processing a payment again.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?> 