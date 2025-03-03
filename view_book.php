<?php
include 'connection.php';
session_start();

if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access!");
}

// Validate book ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request!");
}

$book_id = intval($_GET['id']);

// Fetch book details based on the correct ID
$sql = "SELECT title, file_path FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

if (!$book) {
    die("Book not found!");
}

// Ensure correct file path is used
$title = htmlspecialchars($book['title']);
$file_path = "uploads/" . basename($book['file_path']); // Ensure no directory issues

// Debugging: Log the book details (optional, remove in production)
error_log("Book ID: $book_id | Title: $title | File Path: $file_path");

if (!file_exists($file_path)) {

    die("File not found: " . $file_path);

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - Secure Book Viewer</title>
    <link rel="stylesheet" href="view_book.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
</head>
<body>
    <div class="viewer-container">
        <h2 class="book-title"><?php echo $title; ?></h2>
        <canvas id="pdfCanvas" data-url="<?php echo $file_path . '?t=' . time(); ?>"></canvas>
        <div class="pagination">
            <button id="prevPage">Previous</button>
            <span>Page <span id="pageNum">1</span> of <span id="pageCount">?</span></span>
            <button id="nextPage">Next</button>
        </div>

        <div id="watermark" style="display: None;">EduVault - Student ID: <?php echo $_SESSION['student_id']; ?></div>
        <div class="pdf-watermark" >EduVault - Student ID: <?php echo $_SESSION['student_id']; ?></div>
    </div>



    <script src="js/view_book.js"></script>
</body>
</html>
