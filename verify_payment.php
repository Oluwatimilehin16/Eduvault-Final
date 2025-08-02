<?php
// Start output buffering to prevent header issues
ob_start();

include 'config.php';

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the required parameters are received
if (!isset($_GET['reference']) || !isset($_GET['student_id']) || !isset($_GET['product_id'])) {
    die("Invalid request. Missing required parameters.");
}

// Get parameters and check if 'paid' exists
$reference = mysqli_real_escape_string($conn, $_GET['reference']);
$student_id = mysqli_real_escape_string($conn, $_GET['student_id']);
$product_id = mysqli_real_escape_string($conn, $_GET['product_id']);

// Check if 'paid' parameter exists before using it
$status = isset($_GET['paid']) ? $_GET['paid'] : null;

// Paystack Secret Key (Use your secret key, NOT public key)
$paystack_secret_key = "sk_test_70aef57813cc596e01503cca38901a36fd28a63d"; // Replace with your actual secret key

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
$curl_error = curl_error($ch);
curl_close($ch);

// Check for cURL errors
if ($curl_error) {
    die("Payment verification failed: " . $curl_error);
}

$payment_data = json_decode($response, true);

// Check if the API response is valid
if (!$payment_data || !isset($payment_data['status'])) {
    die("Invalid response from payment gateway.");
}

// Check if payment was successful
if ($payment_data['status'] && $payment_data['data']['status'] == 'success') {
    $amount_paid = $payment_data['data']['amount'] / 100; // Convert kobo to Naira
    
    // Check if student already owns the product
    $check_purchase = mysqli_query($conn, "SELECT * FROM purchases WHERE student_id = '$student_id' AND product_id = '$product_id' AND status = 'paid'");
    
    if (!$check_purchase) {
        die("Database query failed: " . mysqli_error($conn));
    }
    
    if (mysqli_num_rows($check_purchase) > 0) {
        // If student already purchased, redirect to library
        echo "<script>alert('You have already purchased this item!'); window.location='library.php';</script>";
        exit();
    }
    
    // Check if there's a pending purchase record
    $check_pending = mysqli_query($conn, "SELECT * FROM purchases WHERE student_id = '$student_id' AND product_id = '$product_id' AND status = 'pending'");
    
    if (mysqli_num_rows($check_pending) > 0) {
        // Update existing pending record
        $update_purchase = mysqli_query($conn, "UPDATE purchases SET status='paid', transaction_ref='$reference', amount='$amount_paid' WHERE student_id='$student_id' AND product_id='$product_id' AND status='pending'");
        
        if (!$update_purchase) {
            die("Failed to update purchase: " . mysqli_error($conn));
        }
    } else {
        // Insert new purchase record directly as paid
        $insert_purchase = mysqli_query($conn, "INSERT INTO purchases (student_id, product_id, amount, transaction_ref, status) VALUES ('$student_id', '$product_id', '$amount_paid', '$reference', 'paid')");
        
        if (!$insert_purchase) {
            die("Failed to insert purchase: " . mysqli_error($conn));
        }
    }
    
    // Success - redirect to library
    echo "<script>alert('Payment successful!'); window.location='library.php';</script>";
    exit();
    
} else {
    // Payment failed or was not successful
    $error_message = isset($payment_data['message']) ? $payment_data['message'] : "Payment verification failed";
    echo "<script>alert('Payment failed: $error_message'); window.location='index.php';</script>";
    exit();
}

// End output buffering
ob_end_flush();
?>