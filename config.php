<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$timeout_duration = 1800; // 30 minutes

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout_duration)) {
    session_unset();
    session_destroy();
    
    // Prevent infinite redirect loop
    if (basename($_SERVER['PHP_SELF']) !== "login.php") {
        header("Location: login.php");
        exit();
    }
}

// Update session activity timestamp
$_SESSION['LAST_ACTIVITY'] = time();

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$database = "eduvault";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>




