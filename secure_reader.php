<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Ensure user is logged in
if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access!");
}

if (!isset($_GET['file'])) {
    die("Invalid request!");
}

$file = basename($_GET['file']); // Prevent directory traversal
$file_path = $file;

// Check if file exists
if (!file_exists($file_path)) {
    die("File not found!");
}

// Secure PDF delivery
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"$file\"");
header("Content-Length: " . filesize($file_path));
header("X-Content-Type-Options: nosniff");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
readfile($file_path);
exit();
