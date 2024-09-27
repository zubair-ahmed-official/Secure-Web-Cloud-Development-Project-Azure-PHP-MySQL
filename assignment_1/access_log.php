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


require 'View/AccessLogView.php';
require 'Controller/AccessLogController.php';
require 'Model/AccessLogModel.php';

$model = new AccessLogModel($db);

// Capture IP and URL
$ip_address = $_SERVER['REMOTE_ADDR'];
$requested_url = $_SERVER['REQUEST_URI'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

// Log the page access (log allowed or denied access depending on user role or conditions)
$model->logPageAccess($ip_address, $requested_url, $username, 1);
echo "<p><a href='secure_page.php'>Secure Page</a></p>";
$model = new AccessLogModel($db);
$controller = new AccessLogController($model);

$controller->displayLogs();
?>
