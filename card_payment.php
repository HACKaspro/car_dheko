<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['booking_id'])) {
    header("Location: dashboard.php");
    exit();
}

$booking_id = $_SESSION['booking_id'];
$booking = getBookingById($booking_id);

// Initialize Razorpay payment
$razorpay = new Razorpay\Api\Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
$order = $razorpay->order->create([
    'amount' => $booking['total_price'] * 100,
    'currency' => 'INR',
    'receipt' => $booking_id,
]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Card Payment</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <h1>Card Payment</h1>
    <p>Total Amount: ₹<?= number_format($booking['total_price'], 2) ?></p>
    <button id="pay-button">Pay Now</button>

    <script>
        var options = {
            "key": "<?= RAZORPAY_KEY_ID ?>",
            "amount": "<?= $order->amount ?>",
            "currency": "INR",
            "name": "Car Rental",
            "description": "Booking ID: <?= $booking_id ?>",
            "order_id": "<?= $order->id ?>",
            "handler": function (response) {
                // Handle successful payment
                window.location.href = "payment_success.php?payment_id=" + response.razorpay_payment_id;
            },
            "prefill": {
                "name": "<?= $_SESSION['user_name'] ?>",
                "email": "<?= $_SESSION['user_email'] ?>",
                "contact": "<?= $_SESSION['user_phone'] ?>"
            },
            "theme": {
                "color": "#3399cc"
            }
        };
        var rzp = new Razorpay(options);
        document.getElementById('pay-button').onclick = function (e) {
            rzp.open();
            e.preventDefault();
        }
    </script>
</body>
</html>
