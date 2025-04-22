<?php
header('Content-Type: text/html');
echo "<h1>Ticket Booking System Test</h1>";

try {
    // Test database connection
    require_once 'db_connection.php';
    
    echo "<div style='color: green;'>✓ Database connection successful</div>";
    
    // Check if tables exist
    $tables = ['bookings', 'events', 'payments', 'users'];
    $existingTables = [];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            $existingTables[] = $table;
            echo "<div style='color: green;'>✓ Table '$table' exists</div>";
        } else {
            echo "<div style='color: red;'>✗ Table '$table' does not exist</div>";
        }
    }
    
    // Count records in each table
    foreach ($existingTables as $table) {
        $result = $conn->query("SELECT COUNT(*) as count FROM $table");
        $row = $result->fetch_assoc();
        echo "<div>Table '$table' has {$row['count']} records</div>";
    }
    
    // Test paths
    echo "<h2>File Paths</h2>";
    $files = [
        'Frontend/concerts.html',
        'Frontend/book-ticket.html',
        'Frontend/payment.html',
        'Frontend/confirmation.html',
        'backend/handle_booking.php',
        'backend/update_payment.php',
        'backend/get_booking.php'
    ];
    
    foreach ($files as $file) {
        $fullPath = "../$file";
        if (file_exists($fullPath)) {
            echo "<div style='color: green;'>✓ File '$file' exists</div>";
        } else {
            echo "<div style='color: red;'>✗ File '$file' does not exist</div>";
        }
    }
    
    // Display URLs for testing
    echo "<h2>Test Links</h2>";
    $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/Ticket_Booking/';
    
    echo "<ul>";
    echo "<li><a href='{$baseUrl}Frontend/concerts.html' target='_blank'>Concerts Page</a></li>";
    echo "<li><a href='{$baseUrl}Frontend/book-ticket.html?eventId=rock-rev-2024&eventType=concert&eventName=Rock%20Revolution%20Festival' target='_blank'>Book Ticket (Rock Festival)</a></li>";
    echo "<li><a href='{$baseUrl}backend/add_sample_events.php' target='_blank'>Add Sample Events</a></li>";
    echo "</ul>";
    
    // Show recent bookings if any exist
    $result = $conn->query("SELECT * FROM bookings ORDER BY id DESC LIMIT 5");
    if ($result->num_rows > 0) {
        echo "<h2>Recent Bookings</h2>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Event</th><th>Name</th><th>Tickets</th><th>Amount</th><th>Status</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['event_name']}</td>";
            echo "<td>{$row['first_name']} {$row['last_name']}</td>";
            echo "<td>{$row['quantity']} x {$row['ticket_type']}</td>";
            echo "<td>₹{$row['total_amount']}</td>";
            echo "<td>{$row['booking_status']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<div>No bookings found</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
}

if (isset($conn)) {
    $conn->close();
}
?> 