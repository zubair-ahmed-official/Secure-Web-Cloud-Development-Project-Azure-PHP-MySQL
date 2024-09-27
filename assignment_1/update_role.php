<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$db = new mysqli('localhost', 'root', '1310192448#Vic', 'assignment1');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Check if the logged-in user is an admin
$username = $_SESSION['username'];
$sql = "SELECT role FROM users WHERE username = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['role'] != 'admin') {
    echo "Access denied.";
    exit();
}

// Update the user's role
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['new_role'];

    // Update role only if the new role is valid and the user isn't updating an admin
    $valid_roles = ['basic', 'moderator', 'admin'];
    if (in_array($new_role, $valid_roles)) {
        $sql = "UPDATE users SET role = ? WHERE id = ? AND role != 'admin'";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("si", $new_role, $user_id);
        if ($stmt->execute()) {
            echo "Role updated successfully.";
        } else {
            echo "Error updating role.";
        }
    } else {
        echo "Invalid role selected.";
    }
}

// Redirect back to the permissions page
header("Location: permissions.php");
exit();
?>
