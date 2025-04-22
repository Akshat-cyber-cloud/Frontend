<?php
// Test registration functionality
$testData = [
    'name' => 'testuser' . time(), // Add timestamp to make username unique
    'email' => 'testuser' . time() . '@example.com',
    'password' => 'password123'
];

// Set up cURL request
$ch = curl_init('http://localhost/Ticket_Booking/backend/handle_signup.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Output results
echo "Test Registration Results:\n";
echo "HTTP Status Code: " . $httpCode . "\n";
echo "Response: " . $response . "\n";

// Decode and print response in a readable format
$decoded = json_decode($response, true);
echo "\nDecoded Response:\n";
print_r($decoded); 