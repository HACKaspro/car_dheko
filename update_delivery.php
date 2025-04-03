
<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['booking_id']) && isset($_GET['status'])) {
    $booking_id = intval($_GET['booking_id']);
    $status = $_GET['status'];
    
    // Validate status
    $allowed_statuses = ['not_delivered', 'delivered', 'returned'];
    if (!in_array($status, $allowed_statuses)) {
        $_SESSION['error'] = "Invalid delivery status";
        header("Location: dashboard.php");
        exit();
    }
    
    // Update the delivery status
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET delivery_status = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$status, $booking_id, $_SESSION['user_id']]);
        
        $_SESSION['success'] = "Delivery status updated successfully";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Failed to update delivery status: " . $e->getMessage();
    }
}

header("Location: dashboard.php");
exit();
