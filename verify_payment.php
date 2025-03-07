<?php
include 'connection.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the required parameters are received
if (!isset($_GET['reference']) || !isset($_GET['student_id']) || !isset($_GET['product_id'])) {
    die("Invalid request.");
}

$status= $_GET['paid'];
$reference = $_GET['reference'];
$student_id = $_GET['student_id'];
$product_id = $_GET['product_id'];

// Paystack Secret Key (Use your secret key, NOT public key)
$paystack_secret_key = "sk_test_70aef57813cc596e01503cca38901a36fd28a63d"; // Replace with your Paystack secret key

// Verify payment with Paystack API
$url = "https://api.paystack.co/transaction/verify/" . $reference;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $paystack_secret_key",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
curl_close($ch);

$payment_data = json_decode($response, true);

// Check if payment was successful
if ($payment_data['status'] && $payment_data['data']['status'] == 'success') {
    $amount_paid = $payment_data['data']['amount'] / 100; // Convert kobo to Naira

    // Check if student already owns the product
    $check_purchase = mysqli_query($conn, "SELECT * FROM purchases WHERE student_id = '$student_id' AND product_id = '$product_id'");
    
    if (mysqli_num_rows($check_purchase) > 0) {
        // If student already purchased, redirect to library
        echo "<script>alert('You have already purchased this item!'); window.location='library.php';</script>";
        exit();
    }

    // Insert the purchase into the database
    $insert_purchase = mysqli_query($conn, "INSERT INTO purchases (student_id, product_id, amount, transaction_ref, status) 
        VALUES ('$student_id', '$product_id', '$amount_paid', '$reference', 'pending')") or die('Purchase failed');

$update_purchase = mysqli_query($conn, "UPDATE purchases 
SET status='paid', transaction_ref='$reference' 
WHERE student_id='$student_id' AND product_id='$product_id' AND status='pending'");

if ($update_purchase) {
echo "<script>alert('Payment successful!'); window.location='library.php';</script>";
exit();
} else {
echo "<script>alert('Failed to update purchase status! Contact support.'); window.location='homepage.php';</script>";
exit();
}
}
?>
