<?php
// Database connection
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

// Initialize variables for error messages
$username_error = '';
$password_error = '';
$custom_data_error = '';
$extra_info_error = '';
$registration_error = ''; // Initialize registration_error

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $user_username = trim($_POST['username']);
    $user_password = trim($_POST['password']);
    $custom_data = trim($_POST['custom_data']);
    $extra_info = trim($_POST['extra_info']);
    $role = "Basic";

    // Username validation
    if (empty($user_username)) {
        $username_error = "Username is required.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $user_username)) {
        $username_error = "Invalid username format. Only alphanumeric characters and underscores are allowed.";
    } else {
        // Check for existing username
        $check_sql = "SELECT * FROM users WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $user_username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $username_error = "Username already exists.";
        }
        $check_stmt->close();
    }

    // Password validation
    if (empty($user_password)) {
        $password_error = "Password is required.";
    } elseif (strlen($user_password) < 8) {
        $password_error = "Password must be at least 8 characters long.";
    }

    // Custom data validation
    if (empty($custom_data)) {
        $custom_data_error = "Custom data is required.";
    }

    // Extra info validation
    if (empty($extra_info)) {
        $extra_info_error = "Extra info is required.";
    }

    // Only proceed to database insertion if there are no errors
    if (empty($username_error) && empty($password_error) && empty($custom_data_error) && empty($extra_info_error)) {
        // Encrypt password with bcrypt
        $hashed_password = password_hash($user_password, PASSWORD_BCRYPT);

        // Encrypt custom data
        $encryption_key = "secret_key"; // Use a strong key in practice
        $encrypted_custom_data = openssl_encrypt($custom_data, "AES-128-ECB", $encryption_key);

        // Insert into the database
        $sql = "INSERT INTO users (username, password, custom_data, extra_info, role)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $user_username, $hashed_password, $encrypted_custom_data, $extra_info, $role);

        if ($stmt->execute()) {
            echo "Registration successful!";
            echo "<br><a href='login_page.php'>Login</a>";
        } else {
            $registration_error = "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        // Display error messages
        $registration_error = "Registration failed. Please check the errors above.";
	echo '<!DOCTYPE html>';
echo '<html lang="en">';
echo '<head>';
echo '    <meta charset="UTF-8">';
echo '    <meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo '    <title>Registration Page</title>';
echo '    <link rel="stylesheet" href="css/registration_page.css">';
echo '</head>';
echo '<body>';
echo '    <div class="registration-container">';
if (!empty($registration_error)) {
    echo '        <div class="error-message">' . $registration_error . '</div>';
}
echo '        <h2>Register</h2>';
echo '        <form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="POST">';
echo '            <label for="username">Username:</label>';
echo '            <input type="text" name="username" value="' . htmlspecialchars($user_username) . '">';
echo '            <span class="error-message">' . $username_error . '</span><br>';
echo '            <label for="password">Password:</label>';
echo '            <input type="password" name="password">';
echo '            <span class="error-message">' . $password_error . '</span><br>';
echo '            <label for="custom_data">Custom Data:</label>';
echo '            <input type="text" name="custom_data" value="' . htmlspecialchars($custom_data) . '">';
echo '            <span class="error-message">' . $custom_data_error . '</span><br>';
echo '            <label for="extra_info">Extra Info:</label>';
echo '            <input type="text" name="extra_info" value="' . htmlspecialchars($extra_info) . '">';
echo '            <span class="error-message">' . $extra_info_error . '</span><br>';
echo '            <button type="submit">Register</button>';
echo '        </form>';
echo '    </div>';
echo '</body>';
echo '</html>';

    }
}

$conn->close();
?>

