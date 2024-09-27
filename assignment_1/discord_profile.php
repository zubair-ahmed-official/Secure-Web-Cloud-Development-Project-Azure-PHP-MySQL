<?php
session_start();
require 'vendor/autoload.php';  // Include Guzzle
use GuzzleHttp\Client;

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

if (!isset($_SESSION['username'])) {
    header("Location: login_page.php");
    exit();
}

if (!isset($_SESSION['access_token'])) {
    echo "Access token not found.";
    exit();
}

$accessToken = $_SESSION['access_token'];

function getUserInfo($accessToken) {
    $client = new Client();
    $url = 'https://discord.com/api/v10/users/@me'; // Discord API endpoint for user info
    
    $headers = [
        'Authorization' => "Bearer {$accessToken}",
    ];

    try {
        $response = $client->request('GET', $url, ["headers" => $headers]);
        $userInfo = json_decode($response->getBody(), true);
        return $userInfo;
    } catch (Exception $e) {
        echo 'Error fetching user info: ' . $e->getMessage();
        return null;
    }
}

function getUserGuilds($accessToken) {
    $client = new Client();
    $url = 'https://discord.com/api/v10/users/@me/guilds'; // Discord API endpoint for user guilds
    
    $headers = [
        'Authorization' => "Bearer {$accessToken}",
    ];

    try {
        $response = $client->request('GET', $url, ["headers" => $headers]);
        $guilds = json_decode($response->getBody(), true);
        return $guilds;
    } catch (Exception $e) {
        echo 'Error fetching user guilds: ' . $e->getMessage();
        return null;
    }
}

$userInfo = getUserInfo($accessToken);
$guilds = getUserGuilds($accessToken);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discord Profile</title>
    <link rel="stylesheet" href="css/discord_profile.css"> <!-- Link to the CSS file -->
</head>
<body>
    <h1>Discord Profile</h1>
    <?php if ($userInfo): ?>
        <p>Username: <?php echo htmlspecialchars($userInfo['username']); ?></p>
        <p><img src="https://cdn.discordapp.com/avatars/<?php echo htmlspecialchars($userInfo['id']) . '/' . htmlspecialchars($userInfo['avatar']); ?>.png" alt="Profile Picture" width="100" height="100"></p>
    <?php else: ?>
        <p>Failed to get user information.</p>
    <?php endif; ?>

    <h2>Guilds</h2>
    <?php if ($guilds): ?>
        <ul>
            <?php foreach ($guilds as $guild): ?>
                <li><?php echo htmlspecialchars($guild['name']); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Failed to get user guilds.</p>
    <?php endif; ?>
    <p><a href='secure_page.php'>Secure Page</a>
    <p><a href="logout.php" class="logout-button">Logout</a></p></body>
</html>
