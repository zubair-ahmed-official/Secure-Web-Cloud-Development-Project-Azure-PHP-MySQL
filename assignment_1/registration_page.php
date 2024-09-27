<?php 
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$servername = $_ENV['DB_SERVER'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$dbname = $_ENV['DB_NAME'];
$db = new mysqli($servername, $username, $password, $dbname);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

require 'Model/AccessLogModel.php';
$model = new AccessLogModel($db);

// Capture IP and URL
$ip_address = $_SERVER['REMOTE_ADDR'];
$requested_url = $_SERVER['REQUEST_URI'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

// Log the page access (log allowed or denied access depending on user role or conditions)
$model->logPageAccess($ip_address, $requested_url, $username, 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/registration_page.css">
    <title>Registration Page</title>
</head>
<body>
       <div class="registration-container">
        <h2>Register</h2>
        <form action="register.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" name="username" ><br>
            <label for="password">Password:</label>
            <input type="password" name="password" ><br>
            <label for="custom_data">Custom Data:</label>
            <input type="text" name="custom_data" ><br>
            <label for="extra_info">Enter your IP address:</label>
            <input type="text" name="extra_info" ><br>
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
