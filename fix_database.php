<?php
header('Content-Type: text/html');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Structure Diagnostics and Fixes</h1>";

// Include database connection
include 'db_connection.php';

try {
    $conn = getConnection();
    echo "<p style='color:green'>✓ Database connection successful</p>";
    
    // Check bookings table
    echo "<h2>Bookings Table Diagnostics</h2>";
    $stmt = $conn->prepare("SHOW TABLES LIKE 'bookings'");
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green'>✓ Bookings table exists</p>";
        
        // Check columns in bookings table
        $stmt = $conn->prepare("DESCRIBE bookings");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p>Existing columns: " . implode(", ", $columns) . "</p>";
        
        // Check for email column
        $emailColumnExists = false;
        $emailColumnName = "";
        foreach ($columns as $column) {
            if (in_array($column, ['email', 'user_email', 'customer_email'])) {
                $emailColumnExists = true;
                $emailColumnName = $column;
                break;
            }
        }
        
        if ($emailColumnExists) {
            echo "<p style='color:green'>✓ Email column exists as '{$emailColumnName}'</p>";
        } else {
            echo "<p style='color:red'>✗ No email column found in bookings table</p>";
            echo "<p>Would you like to add an email column? <a href='?add_email_column=1' style='color:blue'>Click here to add</a></p>";
            
            if (isset($_GET['add_email_column'])) {
                try {
                    $conn->exec("ALTER TABLE bookings ADD COLUMN email VARCHAR(255) AFTER id");
                    echo "<p style='color:green'>✓ Added 'email' column to bookings table</p>";
                } catch (Exception $e) {
                    echo "<p style='color:red'>✗ Error adding email column: " . $e->getMessage() . "</p>";
                }
            }
        }
        
        // Check for user_email column
        $stmt = $conn->prepare("SHOW COLUMNS FROM bookings LIKE 'user_email'");
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            // Add user_email column if it doesn't exist
            $conn->exec("ALTER TABLE bookings ADD COLUMN user_email VARCHAR(255) NOT NULL AFTER id");
            echo "<p style='color:green'>✓ Added user_email column to bookings table</p>";
        } else {
            echo "<p style='color:green'>✓ user_email column already exists</p>";
        }
        
        // Check for status column
        $statusColumnExists = in_array('status', $columns);
        if ($statusColumnExists) {
            echo "<p style='color:green'>✓ Status column exists</p>";
            
            // Get status column values
            $stmt = $conn->prepare("SELECT DISTINCT status FROM bookings");
            $stmt->execute();
            $statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "<p>Status values: " . (empty($statuses) ? "none" : implode(", ", $statuses)) . "</p>";
            
        } else {
            echo "<p style='color:red'>✗ No status column found in bookings table</p>";
            echo "<p>Would you like to add a status column? <a href='?add_status_column=1' style='color:blue'>Click here to add</a></p>";
            
            if (isset($_GET['add_status_column'])) {
                try {
                    $conn->exec("ALTER TABLE bookings ADD COLUMN status VARCHAR(50) DEFAULT 'pending'");
                    echo "<p style='color:green'>✓ Added 'status' column to bookings table</p>";
                } catch (Exception $e) {
                    echo "<p style='color:red'>✗ Error adding status column: " . $e->getMessage() . "</p>";
                }
            }
        }
        
        // Sample booking data
        $stmt = $conn->prepare("SELECT * FROM bookings LIMIT 1");
        $stmt->execute();
        $sampleBooking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($sampleBooking) {
            echo "<h3>Sample Booking Data</h3>";
            echo "<pre>" . print_r($sampleBooking, true) . "</pre>";
        } else {
            echo "<p>No bookings found in the database</p>";
        }
        
    } else {
        echo "<p style='color:red'>✗ Bookings table does not exist!</p>";
        echo "<p>Would you like to create the bookings table? <a href='?create_bookings_table=1' style='color:blue'>Click here to create</a></p>";
        
        if (isset($_GET['create_bookings_table'])) {
            try {
                $sql = "CREATE TABLE bookings (
                    id VARCHAR(50) PRIMARY KEY,
                    email VARCHAR(255) NOT NULL,
                    event_name VARCHAR(255) NOT NULL,
                    event_date DATETIME,
                    ticket_type VARCHAR(50),
                    quantity INT DEFAULT 1,
                    total_amount DECIMAL(10, 2),
                    booking_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                    status VARCHAR(50) DEFAULT 'pending'
                )";
                
                $conn->exec($sql);
                echo "<p style='color:green'>✓ Created bookings table</p>";
            } catch (Exception $e) {
                echo "<p style='color:red'>✗ Error creating bookings table: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // Check payments table
    echo "<h2>Payments Table Diagnostics</h2>";
    $stmt = $conn->prepare("SHOW TABLES LIKE 'payments'");
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green'>✓ Payments table exists</p>";
        
        // Check columns in payments table
        $stmt = $conn->prepare("DESCRIBE payments");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p>Existing columns: " . implode(", ", $columns) . "</p>";
        
        // Check for booking_id column
        $bookingIdExists = in_array('booking_id', $columns);
        if (!$bookingIdExists) {
            echo "<p style='color:red'>✗ No booking_id column found in payments table</p>";
            echo "<p>Would you like to add a booking_id column? <a href='?add_booking_id=1' style='color:blue'>Click here to add</a></p>";
            
            if (isset($_GET['add_booking_id'])) {
                try {
                    $conn->exec("ALTER TABLE payments ADD COLUMN booking_id VARCHAR(50) AFTER id");
                    echo "<p style='color:green'>✓ Added 'booking_id' column to payments table</p>";
                } catch (Exception $e) {
                    echo "<p style='color:red'>✗ Error adding booking_id column: " . $e->getMessage() . "</p>";
                }
            }
        }
        
        // Check for status column
        $statusColumnExists = in_array('status', $columns);
        if (!$statusColumnExists) {
            echo "<p style='color:red'>✗ No status column found in payments table</p>";
            echo "<p>Would you like to add a status column? <a href='?add_payment_status=1' style='color:blue'>Click here to add</a></p>";
            
            if (isset($_GET['add_payment_status'])) {
                try {
                    $conn->exec("ALTER TABLE payments ADD COLUMN status VARCHAR(50) DEFAULT 'pending'");
                    echo "<p style='color:green'>✓ Added 'status' column to payments table</p>";
                } catch (Exception $e) {
                    echo "<p style='color:red'>✗ Error adding status column: " . $e->getMessage() . "</p>";
                }
            }
        }
    } else {
        echo "<p style='color:red'>✗ Payments table does not exist!</p>";
        echo "<p>Would you like to create the payments table? <a href='?create_payments_table=1' style='color:blue'>Click here to create</a></p>";
        
        if (isset($_GET['create_payments_table'])) {
            try {
                $sql = "CREATE TABLE payments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    booking_id VARCHAR(50) NOT NULL,
                    amount DECIMAL(10, 2) NOT NULL,
                    payment_method VARCHAR(50),
                    transaction_id VARCHAR(100),
                    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                    status VARCHAR(50) DEFAULT 'pending'
                )";
                
                $conn->exec($sql);
                echo "<p style='color:green'>✓ Created payments table</p>";
            } catch (Exception $e) {
                echo "<p style='color:red'>✗ Error creating payments table: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    echo "<h2>Test Booking Creation</h2>";
    echo "<p>You can create a test booking to verify the database structure:</p>";
    echo "<form method='post' action=''>
        <input type='hidden' name='create_test_booking' value='1'>
        <label>Email: <input type='email' name='email' value='test@example.com'></label><br>
        <label>Event Name: <input type='text' name='event_name' value='Test Event'></label><br>
        <button type='submit'>Create Test Booking</button>
    </form>";
    
    if (isset($_POST['create_test_booking'])) {
        try {
            // First check if the bookings table exists
            $stmt = $conn->prepare("SHOW TABLES LIKE 'bookings'");
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                echo "<p style='color:red'>Cannot create test booking: bookings table doesn't exist</p>";
            } else {
                // Check email column name
                $stmt = $conn->prepare("DESCRIBE bookings");
                $stmt->execute();
                $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                $emailColumn = 'email';
                if (in_array('user_email', $columns)) $emailColumn = 'user_email';
                elseif (in_array('customer_email', $columns)) $emailColumn = 'customer_email';
                
                // Create test booking
                $bookingId = 'test-' . time();
                $email = $_POST['email'];
                $eventName = $_POST['event_name'];
                
                $sql = "INSERT INTO bookings (id, $emailColumn, event_name, event_date, ticket_type, quantity, total_amount, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    $bookingId,
                    $email,
                    $eventName,
                    date('Y-m-d H:i:s', strtotime('+1 month')),
                    'Standard',
                    2,
                    199.99,
                    'confirmed'
                ]);
                
                echo "<p style='color:green'>✓ Created test booking with ID: $bookingId</p>";
                
                // Create test payment if payments table exists
                $stmt = $conn->prepare("SHOW TABLES LIKE 'payments'");
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $sql = "INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status)
                            VALUES (?, ?, ?, ?, ?)";
                            
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        $bookingId,
                        199.99,
                        'Credit Card',
                        'txn-' . time(),
                        'completed'
                    ]);
                    
                    echo "<p style='color:green'>✓ Created test payment record for booking ID: $bookingId</p>";
                }
            }
        } catch (Exception $e) {
            echo "<p style='color:red'>✗ Error creating test booking: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Database error: " . $e->getMessage() . "</p>";
}
?> 