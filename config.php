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
$host = getenv("DB_HOST");
$db   = getenv("DB_NAME");
$user = getenv("DB_USER");
$pass = getenv("DB_PASS");

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>




