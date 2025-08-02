<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Cross-Origin-Resource-Policy: cross-origin");

// Ensure the student is logged in
if (!isset($_SESSION['student_id'])) {
    header('location:login.php');
    exit();
}

$student_id = $_SESSION['student_id'];
$student_email = $_SESSION['student_email'];
$student_name = $_SESSION['student_name'];

// Check if a product ID is provided
if (!isset($_GET['id'])) {
    header('location:homepage.php');
    exit();
}

if(isset($_POST['logout'])){
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$product_id = $_GET['id'];

// Fetch product details
$product_query = mysqli_query($conn, "SELECT p.*, u.firstname AS educator_name 
    FROM products p 
    JOIN user u ON p.educator_id = u.id
    WHERE p.id = '$product_id'") or die('Query failed: ' . mysqli_error($conn));

if (mysqli_num_rows($product_query) > 0) {
    $product = mysqli_fetch_assoc($product_query);
} else {
    die("Product not found.");
}

// Paystack Public Key
$paystack_public_key = "pk_test_4729b3310d94eb3bfce9c491f7f685b9ebe12d22";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="checkout.css"> <!-- Link to checkout styling -->
</head>
<body>
<?php include 'profile.php'; ?>
<?php include 'sidebar.php'; ?>  
<div class="content">
    <h1 id="checkout-title">Checkout</h1>
<section class="checkout-container">
    <h2>Confirm Your Purchase</h2>

    <div class="product-details">
        <?php 
        // Fixed: Use $product instead of $book
        $cover_image = !empty($product['cover_img_data']) ? $product['cover_img_data'] : $product['cover_img']; 
        ?>
        <img src="<?php echo $cover_image; ?>" alt="Cover" style="max-width: 200px; height: auto;">
        <h3><?php echo $product['title']; ?></h3>
        <p>Educator: <?php echo $product['educator_name']; ?></p>
        <p>Price: ₦<?php echo number_format($product['price'], 2); ?></p>
    </div>

    <!-- Hidden Inputs for Paystack -->
    <input type="hidden" id="email" value="<?php echo $student_email; ?>">
    <input type="hidden" id="amount" value="<?php echo $product['price'] * 100; ?>"> <!-- Convert to kobo -->
    <input type="hidden" id="student_id" value="<?php echo $student_id; ?>">
    <input type="hidden" id="product_id" value="<?php echo $product_id; ?>">

    <!-- Button to trigger Paystack payment -->
    <button type="button" class="btn" onclick="payWithPaystack()">Confirm Purchase</button>
    <a href="homepage.php" class="cancel-btn">Cancel</a>
</section>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
    function payWithPaystack() {
        let handler = PaystackPop.setup({
            key: "<?php echo $paystack_public_key; ?>", // Your Paystack Public Key
            email: document.getElementById("email").value,
            amount: document.getElementById("amount").value,
            currency: "NGN",
            ref: "EDU_" + Math.floor((Math.random() * 1000000000) + 1), // Generate unique ref
            callback: function(response) {
                let reference = response.reference;
                window.location.href = "verify_payment.php?reference=" + reference + "&student_id=" + document.getElementById("student_id").value + "&product_id=" + document.getElementById("product_id").value;
            },
            onClose: function() {
                alert('Payment was not completed.');
            }
        });
        handler.openIframe();
    }
</script>
<script src="script.js"></script>

</body>
</html>