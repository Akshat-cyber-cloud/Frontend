<?php
header('Content-Type: text/html');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test data
$testData = [
    'name' => 'test_user_' . time(),
    'email' => 'test_' . time() . '@example.com',
    'password' => 'test123456'
];

echo "<h1>Registration API Test</h1>";
echo "<h2>Test Data:</h2>";
echo "<pre>";
print_r($testData);
echo "</pre>";

// Initialize cURL
$ch = curl_init('http://localhost/Ticket_Booking/backend/handle_signup.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

// Execute the request
echo "<h2>Sending Request...</h2>";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "<h2>HTTP Status Code:</h2>";
echo "<p>" . $httpCode . "</p>";

if ($curlError) {
    echo "<h2>cURL Error:</h2>";
    echo "<p style='color:red;'>" . $curlError . "</p>";
}

echo "<h2>Response:</h2>";
echo "<pre>";
echo htmlspecialchars($response);
echo "</pre>";

// Try to decode JSON response
$decoded = json_decode($response, true);
if ($decoded) {
    echo "<h2>Decoded Response:</h2>";
    echo "<pre>";
    print_r($decoded);
    echo "</pre>";
}

// Check for created users - verification
echo "<h2>Verifying Database Entry:</h2>";
try {
    $host = 'localhost';
    $dbname = 'ticket_booking';
    $username = 'root';
    $password = '';

    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $testData['email']);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<p style='color:green;'>✓ User was successfully created in the database!</p>";
        echo "<pre>";
        print_r($user);
        echo "</pre>";
    } else {
        echo "<p style='color:red;'>✗ User was NOT found in the database.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red;'>Database Error: " . $e->getMessage() . "</p>";
}
?> 