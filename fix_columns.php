<?php
header('Content-Type: text/html');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Adding Missing Status Columns</h1>";

// Include database connection
include 'db_connection.php';

try {
    $conn = getConnection();
    echo "<p style='color:green'>✓ Database connection successful</p>";
    
    // Add status column to bookings table
    echo "<h2>Adding Status Column to Bookings Table</h2>";
    try {
        // Check if bookings table exists
        $stmt = $conn->prepare("SHOW TABLES LIKE 'bookings'");
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Check if status column exists
            $stmt = $conn->prepare("SHOW COLUMNS FROM bookings LIKE 'status'");
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                echo "<p style='color:green'>✓ Status column already exists in bookings table</p>";
            } else {
                // Add status column
                $conn->exec("ALTER TABLE bookings ADD COLUMN status VARCHAR(50) DEFAULT 'pending'");
                echo "<p style='color:green'>✓ Added status column to bookings table</p>";
                
                // Update existing records to have a status
                $conn->exec("UPDATE bookings SET status = 'confirmed' WHERE status IS NULL");
                echo "<p style='color:green'>✓ Updated existing bookings with default status</p>";
            }
        } else {
            echo "<p style='color:red'>✗ Bookings table does not exist!</p>";
            echo "<p>Create the bookings table first using fix_database.php</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Error working with bookings table: " . $e->getMessage() . "</p>";
    }
    
    // Add status column to payments table
    echo "<h2>Adding Status Column to Payments Table</h2>";
    try {
        // Check if payments table exists
        $stmt = $conn->prepare("SHOW TABLES LIKE 'payments'");
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Check if status column exists
            $stmt = $conn->prepare("SHOW COLUMNS FROM payments LIKE 'status'");
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                echo "<p style='color:green'>✓ Status column already exists in payments table</p>";
            } else {
                // Add status column
                $conn->exec("ALTER TABLE payments ADD COLUMN status VARCHAR(50) DEFAULT 'pending'");
                echo "<p style='color:green'>✓ Added status column to payments table</p>";
                
                // Update existing records to have a status
                $conn->exec("UPDATE payments SET status = 'completed' WHERE status IS NULL");
                echo "<p style='color:green'>✓ Updated existing payments with default status</p>";
            }
        } else {
            echo "<p style='color:red'>✗ Payments table does not exist!</p>";
            echo "<p>Create the payments table first using fix_database.php</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Error working with payments table: " . $e->getMessage() . "</p>";
    }
    
    // Show table structures
    echo "<h2>Current Table Structures</h2>";
    
    // Show bookings structure
    echo "<h3>Bookings Table Structure</h3>";
    $stmt = $conn->prepare("SHOW TABLES LIKE 'bookings'");
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $stmt = $conn->prepare("DESCRIBE bookings");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Show sample data
        $stmt = $conn->prepare("SELECT * FROM bookings LIMIT 5");
        $stmt->execute();
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($bookings) > 0) {
            echo "<h4>Sample Booking Data</h4>";
            echo "<table border='1' cellpadding='5'>";
            
            // Table header
            echo "<tr>";
            foreach (array_keys($bookings[0]) as $key) {
                echo "<th>" . htmlspecialchars($key) . "</th>";
            }
            echo "</tr>";
            
            // Table data
            foreach ($bookings as $booking) {
                echo "<tr>";
                foreach ($booking as $value) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>No bookings found in the database</p>";
        }
    }
    
    // Show payments structure
    echo "<h3>Payments Table Structure</h3>";
    $stmt = $conn->prepare("SHOW TABLES LIKE 'payments'");
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $stmt = $conn->prepare("DESCRIBE payments");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Show sample data
        $stmt = $conn->prepare("SELECT * FROM payments LIMIT 5");
        $stmt->execute();
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($payments) > 0) {
            echo "<h4>Sample Payment Data</h4>";
            echo "<table border='1' cellpadding='5'>";
            
            // Table header
            echo "<tr>";
            foreach (array_keys($payments[0]) as $key) {
                echo "<th>" . htmlspecialchars($key) . "</th>";
            }
            echo "</tr>";
            
            // Table data
            foreach ($payments as $payment) {
                echo "<tr>";
                foreach ($payment as $value) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>No payments found in the database</p>";
        }
    }
    
    echo "<p><a href='test_cancel_booking.php' style='color:blue'>Go to cancel_booking.php test page</a></p>";
    echo "<p><a href='fix_database.php' style='color:blue'>Go back to fix_database.php</a></p>";
    echo "<p><a href='../Frontend/my-bookings.html' style='color:blue'>Go to My Bookings page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Database error: " . $e->getMessage() . "</p>";
}
?> 