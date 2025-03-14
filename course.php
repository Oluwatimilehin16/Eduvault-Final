<?php
include 'connection.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch books grouped by section
$books_query = mysqli_query($conn, "SELECT * FROM products ORDER BY section, title") or die('Query failed');

// Get current student ID
$student_id = $_SESSION['student_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses</title>
    <link rel="stylesheet" href="course.css">
</head>
<body>

<header>
        <div class="logo">
        <a href="homepage.php"><img src="./assets/logo.png" alt="EduVault"></a>
        </div>
        <!-- Search Bar -->
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search for books..." onkeyup="searchBooks()">
            <button onclick="searchBooks()">Search</button>
        </div>

        <div class="nav">
        <nav>
            <ul>
                <li><a href="homepage.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="course.php">Courses</a></li>
                <li><a href="library.php">Library</a></li>
            </ul>
        </nav>
        <img src="./assets/ham.png" id="menu-btn" alt="Menu">
    </div>

    </header>
<section class="courses">
<?php 
if (mysqli_num_rows($books_query) > 0) {
    $current_section = null;

    while ($book = mysqli_fetch_assoc($books_query)) {
        if ($current_section !== $book['section']) {
            if ($current_section !== null) echo '</div></div>'; // Close previous section
            $current_section = $book['section'];
            echo "<h2 class='section-title'>" . ucfirst($current_section) . "</h2>";
            echo "<div class='show-products'><div class='box-container1'>"; // Open new section
        }

        $has_purchased = false;
        if ($student_id) {
            $purchase_check = mysqli_query($conn, "SELECT * FROM purchases WHERE student_id = '$student_id' AND product_id = '{$book['id']}' AND status = 'paid'");
            $has_purchased = mysqli_num_rows($purchase_check) > 0;
        }
?>
        <div class="course">
            <img src="uploads/covers/<?php echo $book['cover_img']; ?>" alt="Cover">
            <h3><?php echo $book['title']; ?></h3>
            <p><?php echo $book['description']; ?></p>
            <p class="price">₦<?php echo number_format($book['price'], 2); ?></p>

            <?php if ($student_id): ?>
                <?php if ($has_purchased): ?>
                    <a href="library.php" class="btn library-btn">Go to Library</a>
                <?php else: ?>
                    <a href="checkout.php?id=<?php echo $book['id']; ?>" class="btn buy-btn">Buy Now</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php" class="btn login-btn">Login to Buy</a>
            <?php endif; ?>
        </div>
<?php
    }
    echo '</div></div>'; // Close last section
} else {
    echo "<p class='no-books'>No books found.</p>";
}
?>

</section>

<footer>
    <div class="footer-content">
        <p>&copy; 2025 EduVault. All rights reserved.</p>
    </div>
</footer>
<script>
function searchBooks() {
    let query = document.getElementById("searchInput").value.toLowerCase();
    let courses = document.querySelectorAll(".course");
    let sections = document.querySelectorAll(".section-title");

    courses.forEach(course => {
        let title = course.querySelector("h3").textContent.toLowerCase();
        let description = course.querySelector("p").textContent.toLowerCase();
        if (title.includes(query) || description.includes(query)) {
            course.style.display = "block";
        } else {
            course.style.display = "none";
        }
    });

    sections.forEach(section => {
        let relatedCourses = section.nextElementSibling.querySelectorAll(".course");
        let anyVisible = Array.from(relatedCourses).some(course => course.style.display === "block");
        section.style.display = anyVisible ? "block" : "none";
    });
}
document.getElementById("menu-btn").addEventListener("click", function() {
    let navbar = document.querySelector("nav");
    navbar.style.display = (navbar.style.display === "block") ? "none" : "block";
});

</script>

</body>
</html>
