<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Connect to MySQL without selecting a database
    $conn = new mysqli('localhost', 'root', '');

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/setup_database.sql');

    // Execute multiple SQL statements
    if ($conn->multi_query($sql)) {
        $response = [
            'status' => 'success',
            'message' => 'Database and tables created successfully'
        ];
    } else {
        throw new Exception("Error executing SQL: " . $conn->error);
    }

    // Close the connection
    $conn->close();

} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT);
?> 