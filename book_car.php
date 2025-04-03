<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method";
    header("Location: dashboard.php");
    exit();
}

// Validate and sanitize input
$car_id = filter_input(INPUT_POST, 'car_id', FILTER_VALIDATE_INT);
$start_date = filter_input(INPUT_POST, 'start_date');
$end_date = filter_input(INPUT_POST, 'end_date');

if (!$car_id || !$start_date || !$end_date) {
    $_SESSION['error'] = "Invalid booking data provided";
    header("Location: dashboard.php");
    exit();
}

// Convert dates to DateTime objects for comparison
try {
    $start_date_obj = new DateTime($start_date);
    $end_date_obj = new DateTime($end_date);
    $today = new DateTime();
    
    // Validate date range
    if ($start_date_obj < $today) {
        $_SESSION['error'] = "Start date cannot be in the past";
        header("Location: dashboard.php");
        exit();
    }
    
    if ($end_date_obj <= $start_date_obj) {
        $_SESSION['error'] = "End date must be after start date";
        header("Location: dashboard.php");
        exit();
    }
    
    // Calculate booking duration (max 30 days)
    $interval = $start_date_obj->diff($end_date_obj);
    if ($interval->days > 30) {
        $_SESSION['error'] = "Maximum booking duration is 30 days";
        header("Location: dashboard.php");
        exit();
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Invalid date format provided";
    header("Location: dashboard.php");
    exit();
}

// Check car availability
$car = getCarById($car_id);
if (!$car || !$car['available']) {
    $_SESSION['error'] = "Selected car is no longer available";
    header("Location: dashboard.php");
    exit();
}

// Calculate total price
$days = $interval->days + 1; // Include both start and end dates
$total_price = $days * $car['price_per_day'];

// Check for overlapping bookings
$stmt = $conn->prepare("SELECT id FROM bookings WHERE car_id = ? AND 
                        ((start_date BETWEEN ? AND ?) OR 
                        (end_date BETWEEN ? AND ?) OR 
                        (start_date <= ? AND end_date >= ?)) AND 
                        status != 'cancelled'");
$stmt->bind_param("issssss", $car_id, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['error'] = "This car is already booked for the selected dates";
    header("Location: dashboard.php");
    exit();
}

// Create booking
if (bookCar($_SESSION['user_id'], $car_id, $start_date, $end_date, $payment_method)) {
    $_SESSION['success'] = "Booking created successfully! Your booking ID is #" . $conn->insert_id;
    
    // Send confirmation email (optional)
    sendBookingConfirmation($_SESSION['user_id'], $conn->insert_id);
    
    header("Location: dashboard.php");
    exit();
} else {
    $_SESSION['error'] = "Failed to create booking. Please try again.";
    header("Location: dashboard.php");
    exit();
}

// Function to send booking confirmation
function sendBookingConfirmation($user_id, $booking_id) {
    // In a real application, implement email sending logic here
    // This is just a placeholder for the example
    error_log("Booking confirmation email would be sent for booking ID: $booking_id");
}
<select name="payment_method" required>
    <option value="upi">UPI</option>
    <option value="card">Credit/Debit Card</option>
</select>
if (isset($_POST['book_car'])) {
    $car_id = $_POST['car_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $payment_method = $_POST['payment_method'];

    if (bookCar($_SESSION['user_id'], $car_id, $start_date, $end_date, $payment_method)) {
        $booking_id = $conn->insert_id;
        $_SESSION['booking_id'] = $booking_id;
        
        if ($payment_method == 'upi') {
            header("Location: upi_payment.php");
        } elseif ($payment_method == 'card') {
            header("Location: card_payment.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "Failed to book car. Please try again.";
    }
}

?>