<?php
require_once 'db_connection.php';

// Sample events data
$events = [
    [
        'event_id' => 'rock-rev-2024',
        'event_type' => 'concert',
        'event_name' => 'Rock Revolution Festival',
        'description' => 'Experience the ultimate rock festival featuring top bands and emerging artists.',
        'venue' => 'Central Stadium, Delhi',
        'event_date' => '2024-12-15',
        'start_time' => '16:00:00',
        'end_time' => '23:00:00',
        'vip_price' => 2999.00,
        'premium_price' => 1999.00,
        'standard_price' => 999.00,
        'total_seats' => 5000,
        'available_seats' => 5000,
        'status' => 'upcoming'
    ],
    [
        'event_id' => 'pop-sens-2024',
        'event_type' => 'concert',
        'event_name' => 'Pop Sensation Live',
        'description' => 'Join us for an unforgettable night of pop music with chart-topping artists.',
        'venue' => 'Music Arena, Mumbai',
        'event_date' => '2024-12-20',
        'start_time' => '18:30:00',
        'end_time' => '22:30:00',
        'vip_price' => 1999.00,
        'premium_price' => 1499.00,
        'standard_price' => 799.00,
        'total_seats' => 3000,
        'available_seats' => 3000,
        'status' => 'upcoming'
    ],
    [
        'event_id' => 'classic-sym-2024',
        'event_type' => 'concert',
        'event_name' => 'Classical Symphony Night',
        'description' => 'A magical evening of classical masterpieces performed by renowned orchestra.',
        'venue' => 'Royal Concert Hall, Bangalore',
        'event_date' => '2024-12-25',
        'start_time' => '19:00:00',
        'end_time' => '22:00:00',
        'vip_price' => 1499.00,
        'premium_price' => 999.00,
        'standard_price' => 599.00,
        'total_seats' => 1500,
        'available_seats' => 1500,
        'status' => 'upcoming'
    ],
    [
        'event_id' => 'jaat-movie-premier',
        'event_type' => 'movie',
        'event_name' => 'Jaat - Premier Screening',
        'description' => 'Be the first to watch this action-packed blockbuster.',
        'venue' => 'PVR Cinemas, Delhi',
        'event_date' => '2024-11-10',
        'start_time' => '20:00:00',
        'end_time' => '23:00:00',
        'vip_price' => 599.00,
        'premium_price' => 399.00,
        'standard_price' => 299.00,
        'total_seats' => 300,
        'available_seats' => 300,
        'status' => 'upcoming'
    ],
    [
        'event_id' => 'ipl-mi-csk-2025',
        'event_type' => 'sport',
        'event_name' => 'IPL 2025: MI vs CSK',
        'description' => 'Watch the thrilling cricket match between Mumbai Indians and Chennai Super Kings.',
        'venue' => 'Wankhede Stadium, Mumbai',
        'event_date' => '2025-04-15',
        'start_time' => '19:30:00',
        'end_time' => '23:30:00',
        'vip_price' => 4999.00,
        'premium_price' => 2999.00,
        'standard_price' => 1499.00,
        'total_seats' => 8000,
        'available_seats' => 8000,
        'status' => 'upcoming'
    ]
];

// Insert events into the database
$successCount = 0;
$errorCount = 0;

foreach ($events as $event) {
    // Check if event already exists
    $checkSql = "SELECT event_id FROM events WHERE event_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $event['event_id']);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "Event {$event['event_name']} already exists.<br>";
        $checkStmt->close();
        continue;
    }
    
    $checkStmt->close();
    
    // Prepare insert statement
    $sql = "INSERT INTO events (
        event_id, event_type, event_name, description, venue, 
        event_date, start_time, end_time, 
        vip_price, premium_price, standard_price, 
        total_seats, available_seats, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param(
            "ssssssssdddiis",
            $event['event_id'],
            $event['event_type'],
            $event['event_name'],
            $event['description'],
            $event['venue'],
            $event['event_date'],
            $event['start_time'],
            $event['end_time'],
            $event['vip_price'],
            $event['premium_price'],
            $event['standard_price'],
            $event['total_seats'],
            $event['available_seats'],
            $event['status']
        );
        
        if ($stmt->execute()) {
            echo "Added event: {$event['event_name']}<br>";
            $successCount++;
        } else {
            echo "Error adding {$event['event_name']}: " . $stmt->error . "<br>";
            $errorCount++;
        }
        
        $stmt->close();
    } else {
        echo "Statement preparation failed: " . $conn->error . "<br>";
        $errorCount++;
    }
}

echo "<br>Summary: Added $successCount events successfully, $errorCount failed.";

$conn->close();
?> 