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
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}


require 'Model/AccessLogModel.php';
$model = new AccessLogModel($db);

// Capture IP and URL
$ip_address = $_SERVER['REMOTE_ADDR'];
$requested_url = $_SERVER['REQUEST_URI'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

// Log the page access (log allowed or denied access depending on user role or conditions)
$model->logPageAccess($ip_address, $requested_url, $username, 1);

// Check if the user has already linked their Discord account
$username = $_SESSION['username'];
$sql = "SELECT * FROM discord_oauth WHERE username = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$oauth_data = $result->fetch_assoc();

// If the user has already linked Discord, display their info
if ($oauth_data) {
    echo "<h2>Discord Account Linked!</h2>";
    echo "<p>Discord Username: " . $oauth_data['discord_username'] . "</p>";
    echo "<img src='https://cdn.discordapp.com/avatars/" . $oauth_data['discord_user_id'] . "/" . $oauth_data['discord_avatar'] . ".png' alt='Discord Avatar' />";
    display_discord_guilds($oauth_data['discord_access_token']);
    exit();
}

// If not linked, show the OAuth link
$client_id = "1287741099560931358";
$redirect_uri = "https://lab-d00a6b41-7f81-4587-a3ab-fa25e5f6d9cf.australiaeast.cloudapp.azure.com:7008/assignment_1/discord_callback.php";  // Modify to your actual callback URL
$scopes = "identify guilds";
$oauth_url = "https://discord.com/oauth2/authorize?client_id=1287741099560931358&response_type=code&redirect_uri=https%3A%2F%2Flab-d00a6b41-7f81-4587-a3ab-fa25e5f6d9cf.australiaeast.cloudapp.azure.com%3A7008%2Fassignment_1%2Fdiscord_callback.php&scope=identify+guilds";

echo "<h2>Link Your Discord Account</h2>";
echo "<p><a href='$oauth_url'>Click here to connect your Discord account</a></p>";
?>
