<?php
include 'connection.php';
session_start();

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
        <div  class="logo">
        <a href="homepage.html"><img src="./assets/logo.png" alt="EduVault"></a>
        </div>
        <div>
        <nav>
            <ul>
                <li><a href="homepage.html">Home</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Courses</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </nav>
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
                    <a href="#" class="btn btn-primary btn-lg">Get Started</a>
                </div>
            </div>
            <div class="carousel-item">
                <img src="./assets/Slide2.jpg" class="d-block w-100" alt="Slide 2">
                <div class="overlay"></div>
                <div class="hero-content">
                    <h1 class="hero-title">Learn at Your Own Pace</h1>
                    <p class="sub-hero">Access eBooks, videos, and interactive lessons anytime, anywhere.</p>
                    <a href="#" class="btn btn-primary btn-lg">Explore Courses</a>
                </div>
            </div>
            <div class="carousel-item">
                <img src="./assets/Slide 3.png" class="d-block w-100" alt="Slide 3">
                <div class="overlay"></div>
                <div class="hero-content">
                    <h1 class="hero-title">Join Our Learning Community</h1>
                    <p class="sub-hero">Connect with experts and expand your knowledge effortlessly.</p>
                    <a href="#" class="btn btn-primary btn-lg">Join Now</a>
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
    
    <h2 class="title">Explore Our Collection</h2>

<section class="show-products">
    <div class="box-container1">
        <?php
        $select_products = mysqli_query($conn, "SELECT * FROM products") or die('Query failed');

        if (mysqli_num_rows($select_products) > 0) {
            while ($product = mysqli_fetch_assoc($select_products)) {
                // Check if the student has already purchased this product
                $purchase_check = $student_id ? mysqli_query($conn, "SELECT * FROM purchases WHERE student_id = '$student_id' AND product_id = '{$product['id']}' AND status = 'paid'") : false;
                $has_purchased = $purchase_check && mysqli_num_rows($purchase_check) > 0;
        ?>
                <div class="box1">
                    <img src="uploads/covers/<?php echo $product['cover_img']; ?>" alt="Cover" class="cover-image">
                    <h3><?php echo $product['title']; ?></h3>
                    <p class="price">₦<?php echo number_format($product['price'], 2); ?></p>

                    <?php if ($student_id): ?>
                        <?php if ($has_purchased): ?>
                            <a href="<?php echo $product['file_path']; ?>" download class="download-btn">Download</a>
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
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>