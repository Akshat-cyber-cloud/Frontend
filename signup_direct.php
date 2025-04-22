<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$host = 'localhost';
$dbname = 'ticket_booking';
$username = 'root';
$password = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Debug: Print form data
        echo "Form submitted with data:<br>";
        echo "Username: " . $_POST['username'] . "<br>";
        echo "Email: " . $_POST['email'] . "<br>";
        
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Database connected successfully<br>";
        
        // Get form data
        $name = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        // Debug: Print SQL query
        echo "Checking for existing user...<br>";
        
        // Check if name or email already exists
        $check_sql = "SELECT COUNT(*) FROM users WHERE name = :name OR email = :email";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(':name', $name);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();
        
        $count = $check_stmt->fetchColumn();
        echo "Found {$count} existing users with same name/email<br>";
        
        if ($count > 0) {
            $error = "Username or email already exists!";
        } else {
            echo "Attempting to insert new user...<br>";
            // Insert new user
            $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            
            if ($stmt->execute()) {
                $user_id = $conn->lastInsertId();
                echo "User inserted successfully with ID: " . $user_id . "<br>";
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $name;
                
                // Redirect to welcome page
                header("Location: welcome.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
                echo "Insert failed<br>";
            }
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        echo "Error: " . $e->getMessage() . "<br>";
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
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #FFFFFF;
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: #1E1E1E;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #2563eb;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #333333;
            border-radius: 4px;
            background: #242424;
            color: #FFFFFF;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #1d4ed8;
        }
        .error {
            color: #ff4444;
            margin-bottom: 15px;
            text-align: center;
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
        
        <p style="text-align: center; margin-top: 15px;">
            Already have an account? <a href="login.php" style="color: #2563eb;">Login here</a>
        </p>
    </div>
    
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            if (username.length < 3) {
                alert('Username must be at least 3 characters');
                e.preventDefault();
                return;
            }
            
            if (password.length < 6) {
                alert('Password must be at least 6 characters');
                e.preventDefault();
                return;
            }
        });
    </script>
</body>
</html> 