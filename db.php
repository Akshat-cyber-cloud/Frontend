<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        $host = 'localhost';
        $port = 3307;
        $dbname = 'ticket_booking';
        $user = 'root';
        $pass = '';
        
        try {
            // First try connecting without database
            $tempPdo = new PDO(
                "mysql:host=$host;port=$port",
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Create database if it doesn't exist
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
            
            // Now connect with the database
            $pdo = new PDO(
                "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
        } catch (PDOException $e) {
            error_log("Connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }
    }
    
    return $pdo;
} 