<?php
// Start the session
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Option 1: Display logout message
echo "You have been logged out. Redirecting to the login page...";
header("refresh:3; url=login_page.php"); // Redirect after 3 seconds

// Option 2: Redirect directly to the login page without a message
// header("Location: login_page.php");
// exit();
?>
