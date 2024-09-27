<?php
require 'vendor/autoload.php'; // Load Composer's autoloader

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();

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

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

echo '<link rel="stylesheet" href="css/secure_page.css">'; // Link to the CSS file
echo "Welcome, " . $_SESSION['username'] . "! You are logged in.";

// Logout form
echo "<form action='logout.php' method='POST'>
        <input type='submit' value='Logout'>
    </form>";

// Fetch user data from the database
$sql = "SELECT custom_data, extra_info, role FROM users WHERE username = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $encrypted_data = $user['custom_data'];
    $extra_info = $user['extra_info'];  // Either IP address, message, or self-descriptive word
    $role = $user['role']; // Get user role from the fetched data

    // Decrypt the custom data
    $decryption_key = 'secret_key';  // Use the same key used during encryption
    $custom_data = openssl_decrypt($encrypted_data, 'AES-128-CBC', $decryption_key);

    echo "<h2>Welcome to the Secure Page</h2>";
    echo "<p>Your custom data: $custom_data</p>";

    // Permissions Page link
    // Display buttons instead of plain links
echo '<div class="button-container">';
if ($role === 'admin') {
    echo '<a href="permissions.php" class="btn">Permissions Page</a>';
}

// Access Log Page button for admin or moderator
if ($role === 'admin' || $role === 'moderator') {
    echo '<a href="access_log.php" class="btn" >Access Log</a>';
}

// Discord Account Link button
echo '<a href="discord_link.php" class="btn">Discord Account Link</a>';
echo '</div>';

    // Use Guzzle to fetch location data
   $client = new \GuzzleHttp\Client();

$response = $client->request('GET', 'https://ip-to-location1.p.rapidapi.com/myip?ip=' . $extra_info, [
    'headers' => [
        'x-rapidapi-host' => 'ip-to-location1.p.rapidapi.com',
        'x-rapidapi-key' => 'd60ce79246msh58b788e14e48ee9p1c2f70jsnbd4b8d62b231',
    ],
]);

// Parse the response as JSON
$locationData = json_decode($response->getBody(), true);

// Extract the desired data
$ip = isset($locationData['ip']) ? $locationData['ip'] : 'N/A';
$city = isset($locationData['geo']['city']) ? $locationData['geo']['city'] : 'N/A';
$region = isset($locationData['geo']['region']) ? $locationData['geo']['region'] : 'N/A';
$country = isset($locationData['geo']['country']) ? $locationData['geo']['country'] : 'N/A';
$timezone = isset($locationData['geo']['timezone']) ? $locationData['geo']['timezone'] : 'N/A';
$geo = isset($locationData['geo']['ll']) 
        ? $locationData['geo']['ll'][0] . ', ' . $locationData['geo']['ll'][1] 
        : 'N/A';

// Display the data with styling
echo '<div class="location-info">';
echo "<p><strong>IP Address:</strong> $ip</p>";
echo "<p><strong>City:</strong> $city</p>";
echo "<p><strong>Region:</strong> $region</p>";
echo "<p><strong>Country:</strong> $country</p>";
echo "<p><strong>Timezone:</strong> $timezone</p>";
echo "<p><strong>Geographical Coordinates:</strong> $geo</p>";
echo '</div>';
    

} else {
    echo "User data not found.";
}

$stmt->close();
$db->close();
?>
