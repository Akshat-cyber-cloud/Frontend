<?php
header('Content-Type: text/html');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing Cancel Booking Functionality</h1>";

// Test database connection
include 'db_connection.php';
echo "<h2>Database Connection</h2>";
try {
    $conn = getConnection();
    echo "<p style='color:green'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test database tables existence
echo "<h2>Database Tables</h2>";
$tables = ["bookings", "payments"];
foreach ($tables as $table) {
    try {
        $stmt = $conn->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->rowCount() > 0) {
            echo "<p style='color:green'>✓ Table '{$table}' exists</p>";
            
            // Check columns in the table
            $stmt = $conn->prepare("DESCRIBE {$table}");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "<p>Columns: " . implode(", ", $columns) . "</p>";
            
            // Count records
            $stmt = $conn->prepare("SELECT COUNT(*) FROM {$table}");
            $stmt->execute();
            $count = $stmt->fetchColumn();
            echo "<p>Records count: {$count}</p>";
        } else {
            echo "<p style='color:red'>✗ Table '{$table}' does not exist</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Error checking table '{$table}': " . $e->getMessage() . "</p>";
    }
}

// Test sample booking cancellation
echo "<h2>Test Cancel Booking</h2>";

// Get a sample booking to test with
try {
    $stmt = $conn->prepare("SELECT id, user_email FROM bookings LIMIT 1");
    $stmt->execute();
    $booking = $stmt->fetch();
    
    if ($booking) {
        echo "<p>Test booking found: ID = {$booking['id']}, Email = {$booking['user_email']}</p>";
        
        // Simulate the cancel_booking.php request
        echo "<h3>Testing cancel_booking.php with sample data</h3>";
        echo "<pre>";
        
        // Save the original file content
        $original_content = file_get_contents('cancel_booking.php');
        
        // Create a temporary modified version for testing
        $test_content = preg_replace(
            '/\$data = json_decode\(file_get_contents\(\'php:\/\/input\'\), true\);/',
            '$data = ["bookingId" => "' . $booking['id'] . '", "userEmail" => "' . $booking['user_email'] . '"];',
            $original_content
        );
        
        // Also add debugging
        $test_content = str_replace(
            'throw new Exception(\'Booking not found or unauthorized\');',
            'throw new Exception(\'Booking not found or unauthorized. Searched for booking ID: \' . $bookingId . \' with email: \' . $userEmail);',
            $test_content
        );
        
        // Save to a temporary file
        file_put_contents('temp_cancel_test.php', $test_content);
        
        // Execute the test
        ob_start();
        include 'temp_cancel_test.php';
        $result = ob_get_clean();
        
        // Delete temporary file
        unlink('temp_cancel_test.php');
        
        // Output the result
        echo "API Response: " . $result;
        echo "</pre>";
    } else {
        echo "<p style='color:orange'>No bookings found in the database to test with.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error testing cancel functionality: " . $e->getMessage() . "</p>";
}

// Check cancel_booking.php file
echo "<h2>Cancel Booking File Check</h2>";
if (file_exists('cancel_booking.php')) {
    echo "<p style='color:green'>✓ cancel_booking.php file exists</p>";
    $size = filesize('cancel_booking.php');
    echo "<p>File size: {$size} bytes</p>";
    
    // Check file permissions
    $perms = substr(sprintf('%o', fileperms('cancel_booking.php')), -4);
    echo "<p>File permissions: {$perms}</p>";
} else {
    echo "<p style='color:red'>✗ cancel_booking.php file does not exist</p>";
}

echo "<h2>Recommendations</h2>";
echo "<ul>";
echo "<li>Make sure the booking ID is correctly passed from the frontend</li>";
echo "<li>Check that the cancel_booking.php file accepts POST requests properly</li>";
echo "<li>Verify database tables have the correct structure with 'status' columns</li>";
echo "<li>Add proper logging in the PHP file to diagnose issues</li>";
echo "</ul>";

?> 