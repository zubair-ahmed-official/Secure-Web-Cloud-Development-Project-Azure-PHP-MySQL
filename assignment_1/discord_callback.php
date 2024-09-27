<?php
require 'vendor/autoload.php'; // Load Composer's autoloader

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();

$servername = $_ENV['DB_SERVER']; // Environment variable
$username = $_ENV['DB_USERNAME']; // Environment variable
$password = $_ENV['DB_PASSWORD']; // Environment variable
$dbname = $_ENV['DB_NAME']; // Environment variable
$db = new mysqli($servername, $username, $password, $dbname);
//var_dump($_ENV['DB_SERVER'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_NAME']);

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login_page.php");
    exit();
}


use GuzzleHttp\Client;

// Database connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

require 'Controller/DiscordController.php';

$discordController = new DiscordController($db);
$discordController->handleCallback();

// Close the database connection

$db->close();
?>
