<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$student_id = $_SESSION['student_id'] ?? null;

// if (!isset($_SESSION['student_id'])) {
//     header('location:login.php');
//     exit();
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduVault Home</title>
    <link rel="stylesheet" href="homepage.css">
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
<header>
    <div class="logo">
        <a href="index.php"><img src="./assets/logo.png" alt="EduVault"></a>
    </div>

    <!-- Hamburger Menu Icon -->
    <div class="hamburger" onclick="toggleMenu()">
        <img src="./assets/ham.png" alt="Menu">
    </div>

    <!-- Navigation Menu -->
    <nav id="mobileMenu">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="course.php">Courses</a></li>
            <li><a href="library.php">Library</a></li>
            <li class="mobile-buttons">
           <button class="btn1 btn-reg" onclick="window.location.href='UserRegistration.php'">Register</button>
           <button class="btn1 btn-log" onclick="window.location.href='login.php'">Login</button>
           </li>
        </ul>
    </nav>

    <div class="desktop-buttons">
        <button class="btn1 btn-reg" onclick="window.location.href='UserRegistration.php'">Register</button>
        <button class="btn1 btn-log" onclick="window.location.href='login.php'">Login</button>
    </div>
</header>

    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <!-- Indicators (Dots) -->
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
        </div>
    
        <!-- Slides -->
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="./assets/Slide1.jpg" class="d-block w-100" alt="Slide 1">
                <div class="overlay"></div>
                <div class="hero-content">
                    <h1 class="hero-title">Welcome to EduVault</h1>
                    <p class="sub-hero">Unlock endless learning possibilities with our DRM-protected educational content.</p>
                    <a href="UserRegistration.php" class="btn btn-primary btn-lg">Get Started</a>
                </div>
            </div>
            <div class="carousel-item">
                <img src="./assets/Slide2.jpg" class="d-block w-100" alt="Slide 2">
                <div class="overlay"></div>
                <div class="hero-content">
                    <h1 class="hero-title">Learn at Your Own Pace</h1>
                    <p class="sub-hero">Access eBooks, videos, and interactive lessons anytime, anywhere.</p>
                    <a href="course.php" class="btn btn-primary btn-lg">Explore Courses</a>
                </div>
            </div>
            <div class="carousel-item">
                <img src="./assets/Slide 3.png" class="d-block w-100" alt="Slide 3">
                <div class="overlay"></div>
                <div class="hero-content">
                    <h1 class="hero-title">Become an Educator</h1>
                    <p class="sub-hero">Upload your courses & start earning today.</p>
                    <a href="AuthorRegistration.php" class="btn btn-primary btn-lg">Join Now</a>
                </div>
            </div>
        </div>
    
        <!-- Previous & Next Buttons -->
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>

    <h2 class="title">New Arrivals</h2>
<section class="show-products">
    <div class="new-arrivals-container">
        <?php
        $new_books = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC LIMIT 3");
        while ($book = mysqli_fetch_assoc($new_books)) {
        ?>
            <div class="new-box">
                <img src="<?php echo $book['cover_img']; ?>" alt="Book Cover" class="cover-image">
                <h4><?php echo $book['title']; ?></h4>
                <p class="price">₦<?php echo number_format($book['price'], 2); ?></p>
                <a href="checkout.php?id=<?php echo $book['id']; ?>" class="buy-btn">Buy Now</a>
            </div>
        <?php
        }
        ?>
    </div>
</section>

<h2 class="title">Explore Our Collection</h2>
<section class="show-products">
    <div class="box-container1">
        <?php
        $select_products = mysqli_query($conn, "SELECT * FROM products LIMIT 6") or die('Query failed');

        if (mysqli_num_rows($select_products) > 0) {
            while ($product = mysqli_fetch_assoc($select_products)) {
                $purchase_check = $student_id ? mysqli_query($conn, "SELECT * FROM purchases WHERE student_id = '$student_id' AND product_id = '{$product['id']}' AND status = 'paid'") : false;
                $has_purchased = $purchase_check && mysqli_num_rows($purchase_check) > 0;
        ?>
                <div class="box1">
                    <img src="<?php echo $product['cover_img']; ?>" alt="Cover" class="cover-image">
                    <h4><?php echo $product['title']; ?></h4>
                    <p class="price">₦<?php echo number_format($product['price'], 2); ?></p>

                    <?php if ($student_id): ?>
                     <?php if ($has_purchased): ?>
                     <a href="library.php" class="library-btn">Go to Library</a>
                <?php else: ?>
                 <a href="checkout.php?id=<?php echo $product['id']; ?>" class="buy-btn">Buy Now</a>
                <?php endif; ?>
                <?php else: ?>
                <a href="login.php" class="login-btn">Login to Buy</a>
                <?php endif; ?>

                </div>
        <?php
            }
        } else {
            echo '<p class="empty">No products available.</p>';
        }
        ?>
    </div>
</section>

<h2 class="title">What Our Students Say</h2>
<div class="testimonial-section">
    <div class="testimonial">
        <p>"EduVault has made learning so much easier! The DRM protection ensures high-quality content."</p>
        <h4>-Timothy James</h4>
    </div>
    <div class="testimonial">
        <p>"As an educator, I love how I can upload courses and earn without worrying about piracy."</p>
        <h4>- Mr. Johnson</h4>
    </div>
    <div class="testimonial">
    <p>"EduVault has truly transformed my learning experience. The DRM protection gives me peace of mind."</p>
    <h4>- Peace</h4>
    </div>
    <div class="testimonial">
    <p>"I love how easy it is to access my courses anytime, anywhere, without worrying about content theft."</p>
    <h4>- Nifemi</h4>
    </div>
</div>

<h2 class="title">Frequently Asked Questions</h2>
<div class="faq-section">
    <div class="faq">
        <h3>How do I purchase a book?</h3>
        <p>Simply click the "Buy Now" button and complete payment via Paystack.</p>
    </div>
    <div class="faq">
        <h3>Can I download my books?</h3>
        <p>No, all books are DRM-protected and can only be read within the EduVault platform.</p>
    </div>
</div>

      <footer>
    <div class="footer-container">
        <div class="footer-section about">
            <h2>About EduVault</h2>
            <p>EduVault is your go-to platform for high-quality DRM-protected educational resources. Learn, explore, and grow with confidence.</p>
        </div>

        <div class="footer-section links">
            <h2>Quick Links</h2>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="course.php">Courses</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </div>

        <div class="footer-section contact">
            <h2>Contact Us</h2>
            <p><i class="bi bi-envelope"></i> support@eduvault.com</p>
            <p><i class="bi bi-telephone"></i> +234 803 447 4877</p>
            <p><i class="bi bi-geo-alt"></i> Lagos, Nigeria</p>
        </div>
    </div>
    <div class="footer-teach">
        Want to share knowledge? <a href="AuthorRegistration.php">Sign up as an Educator</a>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2025 EduVault. All Rights Reserved.</p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>