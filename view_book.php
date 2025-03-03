<?php
include 'connection.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access!");
}

// Validate the file
if (!isset($_GET['file'])) {
    die("Invalid request!");
}

$file = basename($_GET['file']); // Prevent directory traversal
$file_path = "uploads/" . $file;

// Check if the file exists
if (!file_exists($file_path)) {
    die("File not found!");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Book Viewer</title>
    <link rel="stylesheet" href="view_book.css">
    <script>
        document.addEventListener("contextmenu", function(event) {
            event.preventDefault(); // Disable right-click
        });

        document.addEventListener("keydown", function(event) {
            if (event.ctrlKey && (event.key === "s" || event.key === "u" || event.key === "p" || event.key === "c")) {
                event.preventDefault(); // Block Save, View Source, Print, Copy
            }
            if (event.key === "F12") {
                event.preventDefault(); // Block Developer Tools
            }
        });

        function blockScreenshots() {
            const video = document.createElement("video");
            video.autoplay = true;
            video.playsInline = true;
            video.style.position = "absolute";
            video.style.top = "0";
            video.style.left = "0";
            video.style.width = "1px";
            video.style.height = "1px";
            document.body.appendChild(video);
        }

        window.onload = blockScreenshots;
    </script>
</head>
<body>
    <div class="viewer-container">
        <div class="toolbar">
            <input type="text" id="searchText" placeholder="Search text...">
            <button onclick="searchInPDF()">Search</button>
        </div>
        <iframe id="pdfViewer" src="secure_reader.php?file=<?php echo urlencode($file); ?>" style="overflow-y: scroll;"></iframe>
        <div class="watermark">EduVault - Student ID: <?php echo $_SESSION['student_id']; ?></div>
    </div>
    <script src="search.js"></script>
</body>
</html>
