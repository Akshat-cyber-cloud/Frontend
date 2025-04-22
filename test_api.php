<?php
// Set headers for cross-origin requests
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// For OPTIONS preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request info
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$requestHeaders = getallheaders();
$requestBody = file_get_contents('php://input');

try {
    // Basic server information
    $serverInfo = [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'],
        'server_protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown',
        'https' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'
    ];

    // Connection test info
    include_once 'db_connection.php';
    $dbConnected = false;
    $dbError = null;
    
    try {
        $conn = getConnection();
        $dbConnected = true;
    } catch (Exception $e) {
        $dbError = $e->getMessage();
    }
    
    // API call information
    $requestData = [
        'method' => $method,
        'uri' => $requestUri,
        'headers' => $requestHeaders,
        'get_params' => $_GET,
        'post_params' => $_POST,
        'raw_body' => $requestBody
    ];
    
    if (!empty($requestBody)) {
        $jsonData = json_decode($requestBody, true);
        $requestData['json_body'] = $jsonData !== null ? $jsonData : 'Invalid JSON';
    }
    
    // Check booking tables
    $bookingTablesInfo = [];
    if ($dbConnected) {
        // Check bookings table
        try {
            $stmt = $conn->prepare("SHOW TABLES LIKE 'bookings'");
            $stmt->execute();
            $bookingTablesInfo['bookings_exists'] = $stmt->rowCount() > 0;
            
            if ($bookingTablesInfo['bookings_exists']) {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM bookings");
                $stmt->execute();
                $bookingTablesInfo['bookings_count'] = $stmt->fetchColumn();
                
                $stmt = $conn->prepare("DESCRIBE bookings");
                $stmt->execute();
                $bookingTablesInfo['bookings_columns'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }
        } catch (Exception $e) {
            $bookingTablesInfo['bookings_error'] = $e->getMessage();
        }
        
        // Check payments table
        try {
            $stmt = $conn->prepare("SHOW TABLES LIKE 'payments'");
            $stmt->execute();
            $bookingTablesInfo['payments_exists'] = $stmt->rowCount() > 0;
            
            if ($bookingTablesInfo['payments_exists']) {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM payments");
                $stmt->execute();
                $bookingTablesInfo['payments_count'] = $stmt->fetchColumn();
                
                $stmt = $conn->prepare("DESCRIBE payments");
                $stmt->execute();
                $bookingTablesInfo['payments_columns'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }
        } catch (Exception $e) {
            $bookingTablesInfo['payments_error'] = $e->getMessage();
        }
    }
    
    // Put it all together
    $response = [
        'success' => true,
        'message' => 'API test successful',
        'timestamp' => date('Y-m-d H:i:s'),
        'server' => $serverInfo,
        'database' => [
            'connected' => $dbConnected,
            'error' => $dbError
        ],
        'booking_tables' => $bookingTablesInfo,
        'request' => $requestData
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'API test failed',
        'error' => $e->getMessage()
    ]);
}
?> 