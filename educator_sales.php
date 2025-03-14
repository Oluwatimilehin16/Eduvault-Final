<?php
include 'connection.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure only educators can access this page
if (!isset($_SESSION['educator_id'])) {
    header('location:login.php');
    exit();
}
// Get logged-in educator ID
$educator_id = $_SESSION['educator_id'];

$result = mysqli_query($conn, "SELECT SUM(amount * 0.8) AS total_earnings FROM purchases 
    JOIN products ON purchases.product_id = products.id 
    WHERE products.educator_id = '$educator_id'");
$row = mysqli_fetch_assoc($result);
$total_earnings = $row['total_earnings'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Page</title>
    <link rel="stylesheet" href="sales.css">
    <link rel="stylesheet" href="educator.css">
</head>
<body>
<?php 
ob_start(); // Start output buffering
include 'educator_header.php'; 
$header_content = ob_get_clean(); // Store header output in a variable

// Remove the original banner
$header_content = preg_replace('/<div class="banner">.*?<\/div>/s', '', $header_content);

// Output the modified header without the original banner
echo $header_content;
?>

<!-- New Banner -->
<div class="banner">
    <div class="detail">
        <h1>Educator Sales And Revenue Dashboard</h1>
    </div>
</div>

<section class="info">
    <p>At EduVault, we ensure your intellectual property is <strong>protected</strong> and your revenue is <strong>maximized</strong>. 
       Unlike other platforms, we only take a <strong>small 20% commission</strong>, leaving you with <strong>80% of every sale</strong>.</p>
    
    <h3>📌 How It Works</h3>
    <ul>
        <li>🚀 Upload your books & courses – We protect your content with DRM.</li>
        <li>💰 Students purchase – Payments are tracked & securely processed.</li>
        <li>📊 You earn 80% – Revenue is credited directly to your account.</li>
    </ul>
</section>

<section class="dashboard">
    <h2>Your Sales Summary</h2>
    <h3>Total Revenue: <span>₦<?php echo number_format($total_earnings, 2); ?></span></h3>

    <table>
        <tr>
            <th>Transaction ID</th>
            <th>Book Title</th>
            <th>Student</th>
            <th>Amount Earned</th>
            <th>Date</th>
        </tr>
        <?php
        $transactions = mysqli_query($conn, "SELECT purchases.*, products.title, user.firstname 
            FROM purchases 
            JOIN products ON purchases.product_id = products.id 
            JOIN user ON purchases.student_id = user.id 
            WHERE products.educator_id = '$educator_id'
            ORDER BY purchases.purchase_date DESC");

        if (mysqli_num_rows($transactions) > 0) {
            while ($transaction = mysqli_fetch_assoc($transactions)) {
                echo "<tr>
                    <td>{$transaction['transaction_ref']}</td>
                    <td>{$transaction['title']}</td>
                    <td>{$transaction['firstname']}</td>
                    <td>₦" . number_format($transaction['amount'] * 0.8, 2) . "</td>
                    <td>{$transaction['purchase_date']}</td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No sales yet.</td></tr>";
        }
        ?>
    </table>
</section>
<script src="script.js"></script>
</body>
</html>