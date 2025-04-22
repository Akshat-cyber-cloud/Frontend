<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: signup_direct.php");
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link rel="stylesheet" href="../Frontend/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #FFFFFF;
            margin: 0;
            padding: 0;
        }
        .welcome-container {
            max-width: 800px;
            margin: 100px auto;
            padding: 40px;
            background: #1E1E1E;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
            text-align: center;
        }
        h1 {
            color: #2563eb;
            margin-bottom: 20px;
            font-size: 2.5em;
        }
        .username {
            color: #2563eb;
            font-weight: bold;
        }
        p {
            font-size: 1.2em;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.1em;
            transition: background 0.3s;
            margin: 0 10px;
        }
        .btn:hover {
            background: #1d4ed8;
        }
        .success-icon {
            font-size: 5em;
            color: #2563eb;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="success-icon">✓</div>
        <h1>Welcome, <span class="username"><?php echo htmlspecialchars($username); ?></span>!</h1>
        <p>Your account has been successfully created and your data has been stored in the database. You can now start booking tickets for your favorite events.</p>
        <div>
            <a href="../Frontend/index.html" class="btn">Go to Homepage</a>
        </div>
    </div>
</body>
</html> 