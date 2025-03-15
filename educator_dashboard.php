<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$educator_id= $_SESSION['educator_name'];

// Check if the user is logged in as an educator
if (!isset($_SESSION['educator_id'])) {
    header('location:login.php');
    exit();
}

    if(isset($_POST['logout'])){
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
        
    }
$educator_id = $_SESSION['educator_id'];
$educator_name = $_SESSION['educator_name'];

// Count the number of uploads by this educator
$count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM `products` WHERE educator_id = '$educator_id'");
$count_result = mysqli_fetch_assoc($count_query);
$total_uploads = $count_result['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educator Dashboard</title>
    <link rel="stylesheet" href="dashboard.css"> <!-- Add your dashboard CSS -->
</head>
<body>
<?php 
    include 'educator_header.php';
    ?>
<section class="dashboard-header">
    <h1>Welcome, <?php echo $educator_name; ?></h1>
    <a href="educator_product.php" class="btn">Manage Your Uploads</a>
</section>


<section class="dashboard">
    <div class="box-container">
        <div class="box">
            <h3><?php echo $total_uploads; ?></h3>
            <p>Uploaded Books/Videos</p>
        </div>
    </div>
</section>
<script src="script.js"></script>
</body>
</html>
