<?php
include 'connection.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access!");
}

if (!isset($_GET['file'])) {
    die("Invalid request!");
}

$file = basename($_GET['file']); // Prevent directory traversal
$file_path = "uploads/" . $file;

// Check if file exists
if (!file_exists($file_path)) {
    die("File not found!");
}

// Force inline viewing and block download
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"$file\"");
header("Content-Length: " . filesize($file_path));
header("X-Content-Type-Options: nosniff");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
header("Content-Security-Policy: default-src 'self'; script-src 'none'; style-src 'self'; img-src 'self';");

readfile($file_path);
exit();
