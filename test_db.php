<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    // Step 1: Try basic connection without database
    echo json_encode(['status' => 'Testing connection...']) . "\n";
    
    $host = 'localhost';
    $port = 3307;
    $user = 'root';
    $pass = '';
    
    // Test basic connection
    $pdo = new PDO(
        "mysql:host=$host;port=$port",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo json_encode(['status' => 'Basic connection successful']) . "\n";
    
    // Step 2: Create database if not exists
    $dbname = 'ticket_booking';
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    echo json_encode(['status' => 'Database created/verified']) . "\n";
    
    // Step 3: Connect to the database
    $pdo->exec("USE `$dbname`");
    echo json_encode(['status' => 'Connected to database']) . "\n";
    
    // Step 4: Create tables
    $pdo->exec("DROP TABLE IF EXISTS payments");
    $pdo->exec("DROP TABLE IF EXISTS bookings");
    $pdo->exec("DROP TABLE IF EXISTS users");
    
    // Create users table
    $sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo json_encode(['status' => 'Users table created']) . "\n";
    
    // Create bookings table
    $sql = "CREATE TABLE bookings (
        id VARCHAR(50) PRIMARY KEY,
        event_id VARCHAR(50) NOT NULL,
        event_type VARCHAR(50) NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        event_name VARCHAR(255) NOT NULL,
        event_date DATE NOT NULL,
        ticket_type VARCHAR(50) NOT NULL,
        quantity INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(20) DEFAULT 'pending'
    )";
    $pdo->exec($sql);
    echo json_encode(['status' => 'Bookings table created']) . "\n";
    
    // Create payments table
    $sql = "CREATE TABLE payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id VARCHAR(255) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50),
        status VARCHAR(50) DEFAULT 'pending',
        transaction_id VARCHAR(255),
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
    )";
    $pdo->exec($sql);
    echo json_encode(['status' => 'Payments table created']) . "\n";
    
    // Test user insertion
    $name = 'Test User';
    $email = 'test@example.com';
    $password = password_hash('Password123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $password]);
    echo json_encode(['status' => 'Test user created']) . "\n";
    
    // Final verification
    $stmt = $pdo->query("SELECT * FROM users WHERE email = 'test@example.com'");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'Database setup complete', 'test_user' => $user['name']]) . "\n";
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'trace' => $e->getTraceAsString()
    ]) . "\n";
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'trace' => $e->getTraceAsString()
    ]) . "\n";
} 