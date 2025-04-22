<?php
header('Content-Type: text/html');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Check</h1>";

$host = 'localhost';
$dbname = 'ticket_booking';
$username = 'root';
$password = ''; // Ensure this is correct for your database setup

try {
    // Establish database connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green;'>✓ Connected to database successfully!</p>";

    // --- Display Table Structure ---
    echo "<h2>Table Structure: `users`</h2>";
    try {
        $stmt = $conn->query("DESCRIBE users");
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-bottom: 15px; font-family: sans-serif; font-size: 0.9em;'>";
        echo "<tr style='background-color: #f2f2f2;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            // Handle potential NULL default value explicitly
            echo "<td>" . ($row['Default'] === null ? '<i>NULL</i>' : htmlspecialchars($row['Default'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch (PDOException $e) {
        echo "<p style='color:red;'>✗ Error fetching table structure: " . htmlspecialchars($e->getMessage()) . "</p>";
    }

    // --- Display Registered Users ---
    echo "<h2>Registered Users:</h2>";
    try {
        // Select user data, including the password hash
        $stmt = $conn->query("SELECT id, name, email, password, created_at FROM users ORDER BY id DESC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($users) > 0) {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-bottom: 15px; font-family: sans-serif; font-size: 0.9em;'>";
            echo "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Name</th><th>Email</th><th>Password Hash (truncated)</th><th>Created At</th></tr>";
            foreach($users as $user) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                echo "<td>" . htmlspecialchars($user['name']) . "</td>";
                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                // Display truncated password hash or an 'EMPTY' message if missing
                $password_display = !empty($user['password'])
                    ? substr(htmlspecialchars($user['password']), 0, 15) . '...'
                    : '<span style="color:red; font-weight:bold;">EMPTY</span>';
                echo "<td>" . $password_display . "</td>";
                echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            // Message if no users are found
            echo "<p style='color:orange;'>No users found in the database.</p>";
        }
        // Display the total count of users found
        $count = count($users);
        echo "<p>Total users displayed: " . $count . "</p>";

    } catch (PDOException $e) {
        // Error handling for the user query
        echo "<p style='color:red;'>✗ Error fetching users: " . htmlspecialchars($e->getMessage()) . "</p>";
    }

} catch(PDOException $e) {
    // Catch database connection errors specifically
    echo "<p style='color:red;'>✗ Database Connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    // Log the connection error to the PHP error log
    error_log('Database connection error in check_users.php: ' . $e->getMessage());
}

// --- Display PHP Error Log ---
echo "<h2>Recent PHP Errors (from php_errors.log):</h2>";
$log_file = __DIR__ . '/php_errors.log'; // Path to the log file

// Check if the log file exists and is readable
if (file_exists($log_file) && is_readable($log_file)) {
    $errors = file_get_contents($log_file);
    // Check if reading was successful and the log is not empty
    if ($errors !== false && !empty(trim($errors))) {
        // Display errors in a formatted block
        echo "<pre style='background-color: #f0f0f0; border: 1px solid #ccc; padding: 10px; max-height: 300px; overflow-y: auto; font-size: 0.85em; white-space: pre-wrap; word-wrap: break-word;'>" . htmlspecialchars(trim($errors)) . "</pre>";
    } else {
        // Message if the log file is empty
        echo "<p>Error log file exists but is empty.</p>";
    }
} else {
    // Message if the log file is missing or unreadable
    echo "<p>Error log file ('".htmlspecialchars($log_file)."') not found or not readable.</p>";
}

echo '<p style="margin-top: 20px; font-style: italic;">End of script.</p>';
?> 