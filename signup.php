<?php
session_start();
require_once __DIR__ . '/config/database.php';

use App\Config\Database;
use PDO;
use PDOException;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        // Check if username or email already exists
        $check_sql = "SELECT COUNT(*) FROM users WHERE username = :username OR email = :email";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(':username', $username);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();
        
        if ($check_stmt->fetchColumn() > 0) {
            $error = "Username or email already exists!";
        } else {
            $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            
            if ($stmt->execute()) {
                // Store user info in session
                $_SESSION['user_id'] = $conn->lastInsertId();
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                
                // Redirect to welcome page
                header("Location: welcome.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="../Frontend/styles.css">
    <style>
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: var(--dark-surface);
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: var(--dark-text);
        }
        input {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--dark-border);
            border-radius: 4px;
            background: var(--dark-surface-2);
            color: var(--dark-text);
        }
        button {
            width: 100%;
            padding: 10px;
            background: var(--dark-primary);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: var(--dark-primary-hover);
        }
        .error {
            color: #ff4444;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sign Up</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Sign Up</button>
        </form>
        
        <p style="margin-top: 15px; text-align: center;">
            Already have an account? <a href="login.php" style="color: var(--dark-primary);">Login here</a>
        </p>
    </div>
</body>
</html> 