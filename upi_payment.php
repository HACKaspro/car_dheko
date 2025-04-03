<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['booking_id'])) {
    header("Location: dashboard.php");
    exit();
}

$booking_id = $_SESSION['booking_id'];
$booking = getBookingById($booking_id);

// Generate UPI payment link or QR code
$upi_payment_link = generateUPIPaymentLink($booking['total_price'], $booking_id);

// Display UPI payment options to the user
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPI Payment</title>
</head>
<body>
    <h1>UPI Payment</h1>
    <p>Total Amount: ₹<?= number_format($booking['total_price'], 2) ?></p>
    <p>Scan the QR code or click the button below to pay:</p>
    <img src="<?= $upi_payment_link['qr_code'] ?>" alt="UPI QR Code">
    <a href="<?= $upi_payment_link['payment_link'] ?>" target="_blank">Pay Now</a>
</body>
</html>
