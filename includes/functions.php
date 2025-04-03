<?php
require_once 'config.php';

function redirect($url) {
    header("Location: $url");
    exit();
}

function getAvailableCars() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM cars WHERE available = TRUE");
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getCarById($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

function createBooking($user_id, $car_id, $start_date, $end_date, $total_price) {
    global $conn;
    
    // Begin transaction for atomic operation
    $conn->begin_transaction();
    
    try {
        // Insert booking
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, car_id, start_date, end_date, total_price) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissd", $user_id, $car_id, $start_date, $end_date, $total_price);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create booking");
        }
        
        // Mark car as unavailable
        $stmt = $conn->prepare("UPDATE cars SET available = FALSE WHERE id = ?");
        $stmt->bind_param("i", $car_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update car availability");
        }
        
        // Commit transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        error_log("Booking error: " . $e->getMessage());
        return false;
    }
}

function getUserBookings($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT b.*, c.make, c.model, c.image_path 
                           FROM bookings b 
                           JOIN cars c ON b.car_id = c.id 
                           WHERE b.user_id = ? 
                           ORDER BY b.created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function cancelBooking($booking_id, $user_id) {
    global $conn;
    
    // Verify user owns the booking
    $stmt = $conn->prepare("SELECT car_id FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $booking = $result->fetch_assoc();
        
        // Update booking status
        $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        
        if ($stmt->execute()) {
            // Make car available again
            $stmt = $conn->prepare("UPDATE cars SET available = TRUE WHERE id = ?");
            $stmt->bind_param("i", $booking['car_id']);
            $stmt->execute();
            
            return true;
        }
    }
    return false;
}
function getCarPrice($car_id) {
    $car = getCarById($car_id);
    return $car ? $car['price_per_day'] : 0;
}

function bookCar($user_id, $car_id, $start_date, $end_date, $payment_method) {
    global $conn;
    
    // Get car data
    $car = getCarById($car_id);
    if (!$car) return false;
    
    // Calculate total price
    $days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;
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
        return false; // Car is already booked for the selected dates
    }
    
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, car_id, start_date, end_date, total_price, payment_method, status)
        VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iissds", $user_id, $car_id, $start_date, $end_date, $total_price, $payment_method);
    
    return $stmt->execute();
}

function uploadProfilePicture($file, $user_id) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = pathinfo($file["name"], PATHINFO_EXTENSION);
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array(strtolower($file_extension), $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.'];
    }
    
    $file_name = "profile_" . $user_id . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $file_name;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'file_path' => $file_name];
    } else {
        return ['success' => false, 'message' => 'Error uploading file.'];
    }
}

function updateUserProfile($user_id, $full_name, $email, $phone) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);
    
    return $stmt->execute();
}

?>
