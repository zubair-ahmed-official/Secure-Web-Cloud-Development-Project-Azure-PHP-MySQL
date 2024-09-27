<?php
require 'vendor/autoload.php'; // Load Composer's autoloader

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();

$servername = $_ENV['DB_SERVER']; // Environment variable
$username = $_ENV['DB_USERNAME']; // Environment variable
$password = $_ENV['DB_PASSWORD']; // Environment variable
$dbname = $_ENV['DB_NAME']; // Environment variable
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate and sanitize input
$user_username = trim($_POST['username']);
$user_password = trim($_POST['password']);

if (empty($user_username) || empty($user_password)) {
    die("Both username and password are required. Please fill in all fields. <a href='login_page.php'>Try Again </a>");
}

// Further sanitization (if necessary)
$user_username = filter_var($user_username, FILTER_SANITIZE_STRING);

if (!preg_match('/^[a-zA-Z0-9_]+$/', $user_username)) {
    die("Invalid username format. Only alphanumeric characters and underscores are allowed. <a href='login_page.php'>Try Again </a>");
}

// Fetch user from the database
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Verify the password
    if (password_verify($user_password, $user['password'])) {
        // Password matches, start session and redirect to the secure page
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $user['username'];

        // Get user IP address
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_id = $user['id']; // Assuming 'id' is a column in your users table
        
        // Insert access log
        $log_sql = "INSERT INTO access_log (user_id, username, ip_address) VALUES (?, ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("iss", $user_id, $user_username, $ip_address);
        $log_stmt->execute();
        $log_stmt->close();

        echo "Login successful! Redirecting to secure page...";
        header("refresh:2; url=secure_page.php");
    } else {
        echo "Invalid password!";
        echo "<br><a href='login_page.php'>Try Again</a>";
    }
} else {
    echo "User not found!";
    echo "<br><a href='login_page.php'>Try Again</a>";
}

$stmt->close();
$conn->close();
?>
