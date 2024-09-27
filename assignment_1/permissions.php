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
    header("Location: login.php");
    exit();
}
echo "Welcome, " . $_SESSION['username'] . "! You are logged in.";
echo "<br><a href='logout.php' class='logout-button'>Logout</a>";
echo "<p><a href='secure_page.php'>Secure Page</a></p>";

// Check if the logged-in user is an admin
$sql = "SELECT role FROM users WHERE username = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['role'] != 'admin') {
    // Return a 403 Forbidden status code
    header('HTTP/1.0 403 Forbidden');
    
    // Display an access denied message
    echo "HTTP/1.0 403 Forbidden <br>"; 
    echo "Access denied. You do not have permission to view this page.";
    
    // Stop further script execution
    exit();
}

// Fetch all registered users and their roles
$sql = "SELECT id, username, role FROM users";
$result = $db->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage User Roles</title>
    <link rel="stylesheet" href="css/permission.css"> <!-- Link to the CSS file -->
</head>
<body>
    <h2>Manage User Roles</h2>
    <table>
        <tr><th>Username</th><th>Role</th><th>Action</th></tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['role']); ?></td>
                <?php if ($row['role'] != 'admin'): ?>
                    <td>
                        <form action='update_role.php' method='post'>
                            <input type='hidden' name='user_id' value='<?php echo $row['id']; ?>'>
                            <select name='new_role'>
                                <option value='basic'>Basic</option>
                                <option value='moderator'>Moderator</option>
                                <option value='admin'>Admin</option>
                            </select>
                            <input type='submit' value='Update'>
                        </form>
                    </td>
                <?php else: ?>
                    <td>Cannot modify admin</td>
                <?php endif; ?>
            </tr>
        <?php endwhile; ?>
    </table>
    

    <?php
    $db->close();
    ?>
</body>
</html>
