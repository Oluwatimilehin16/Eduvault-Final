<?php
include 'connection.php';
session_start();

// Ensure student is logged in
if (!isset($_SESSION['student_id'])) {
    header('location:login.php');
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch purchased books/videos
$purchases_query = mysqli_query($conn, "SELECT p.*, purchases.purchase_date
    FROM purchases 
    JOIN products p ON purchases.product_id = p.id 
    WHERE purchases.student_id = '$student_id' 
    ORDER BY purchases.purchase_date DESC") or die('Query failed.');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Library</title>
    <link rel="stylesheet" href="library.css"> <!-- Link to styling -->
</head>
<body>

<?php include 'student_header.php'; ?>  

<section class="library-container">
    <h2>Your Library</h2>

    <div class="library-box-container">
        <?php if (mysqli_num_rows($purchases_query) > 0): ?>
            <?php while ($book = mysqli_fetch_assoc($purchases_query)): ?>
                <div class="library-box">
                    <img src="uploads/covers/<?php echo $book['cover_img']; ?>" alt="Book Cover">
                    <h3><?php echo $book['title']; ?></h3>
                    <p>Category: <?php echo ucfirst($book['category']); ?></p>
                    <p>Purchased on: <?php echo date("F j, Y", strtotime($book['purchase_date'])); ?></p>
                    
                    <?php if ($book['category'] == 'ebook'): ?>
                        <a href="view_book.php?file=<?php echo urlencode($book['file_path']); ?>" class="btn">Read Book</a>
                    <?php else: ?>
                        <a href="view_video.php?id=<?php echo $book['id']; ?>" class="btn">Watch Now</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty">You have not purchased any books or videos yet.</p>
        <?php endif; ?>
    </div>
</section>

<script src="script.js"></script>
</body>
</html>
