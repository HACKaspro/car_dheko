
<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Handle booking status and delivery status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = intval($_POST['booking_id']);
    
    // Update booking status if changed
    if (isset($_POST['status'])) {
        $status = $conn->real_escape_string($_POST['status']);
        
        if (!$conn->query("UPDATE bookings SET status = '$status' WHERE id = $booking_id")) {
            $_SESSION['error'] = "Error updating booking status: " . $conn->error;
            header("Location: manage_bookings.php");
            exit();
        }
        
        // If status is cancelled, make the car available again
        if ($status === 'cancelled') {
            $car_result = $conn->query("SELECT car_id FROM bookings WHERE id = $booking_id");
            if ($car_result && $car_result->num_rows > 0) {
                $car_id = $car_result->fetch_assoc()['car_id'];
                if (!$conn->query("UPDATE cars SET available = TRUE WHERE id = $car_id")) {
                    $_SESSION['error'] = "Error updating car availability: " . $conn->error;
                    header("Location: manage_bookings.php");
                    exit();
                }
            }
        }
    }
    
    // Update delivery status if changed
    if (isset($_POST['delivery_status'])) {
        $delivery_status = $conn->real_escape_string($_POST['delivery_status']);
        
        if (!$conn->query("UPDATE bookings SET delivery_status = '$delivery_status' WHERE id = $booking_id")) {
            $_SESSION['error'] = "Error updating delivery status: " . $conn->error;
            header("Location: manage_bookings.php");
            exit();
        }
    }
    
    $_SESSION['message'] = "Booking updated successfully";
    header("Location: manage_bookings.php");
    exit();
}

// Get all bookings with user and car info
$bookings = $conn->query("
    SELECT b.*, u.username, u.full_name, u.phone, 
           c.make, c.model, c.image_path, c.fuel_type, c.seats
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN cars c ON b.car_id = c.id
    ORDER BY b.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .badge-delivered {
            background-color: #28a745;
        }
        .badge-not-delivered {
            background-color: #6c757d;
        }
        .badge-returned {
            background-color: #007bff;
        }
        .img-thumbnail {
            max-height: 80px;
            width: auto;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="col-md-10 ms-sm-auto p-4">
        <h2>Manage Bookings</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Car</th>
                                <th>Dates</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Delivery Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?= $booking['id'] ?></td>
                                <td>
                                    <strong><?= $booking['full_name'] ?></strong><br>
                                    <?= $booking['username'] ?><br>
                                    <?= $booking['phone'] ?>
                                </td>
                                <td>
                                    <img src="../assets/images/<?= $booking['image_path'] ?>" class="img-thumbnail me-2">
                                    <?= $booking['make'] ?> <?= $booking['model'] ?>
                                </td>
                                <td>
                                    <?= date('M j, Y', strtotime($booking['start_date'])) ?><br>
                                    to<br>
                                    <?= date('M j, Y', strtotime($booking['end_date'])) ?>
                                    <small class="d-block text-muted">
                                        <?= 
                                            (strtotime($booking['end_date']) - strtotime($booking['start_date'])) / (60 * 60 * 24) + 1 
                                        ?> days
                                    </small>
                                </td>
                                <td>₹<?= number_format($booking['total_price'], 2) ?></td>
                                <td>
                                    <form method="POST" class="d-flex">
                                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                        <select name="status" class="form-select form-select-sm me-2">
                                            <option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="confirmed" <?= $booking['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                            <option value="cancelled" <?= $booking['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" class="d-flex">
                                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                        <select name="delivery_status" class="form-select form-select-sm me-2">
                                            <option value="not_delivered" <?= ($booking['delivery_status'] ?? 'not_delivered') === 'not_delivered' ? 'selected' : '' ?>>Not Delivered</option>
                                            <option value="delivered" <?= ($booking['delivery_status'] ?? 'not_delivered') === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                            <option value="returned" <?= ($booking['delivery_status'] ?? 'not_delivered') === 'returned' ? 'selected' : '' ?>>Returned</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#bookingModal<?= $booking['id'] ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- Booking Details Modal -->
                            <div class="modal fade" id="bookingModal<?= $booking['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Booking Details #<?= $booking['id'] ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>User Information</h6>
                                                    <p>
                                                        <strong>Name:</strong> <?= $booking['full_name'] ?><br>
                                                        <strong>Username:</strong> <?= $booking['username'] ?><br>
                                                        <strong>Phone:</strong> <?= $booking['phone'] ?><br>
                                                        <strong>Booking Date:</strong> <?= date('M j, Y H:i', strtotime($booking['created_at'])) ?>
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>Car Information</h6>
                                                    <div class="d-flex">
                                                        <img src="../assets/images/<?= $booking['image_path'] ?>" class="img-thumbnail me-3">
                                                        <div>
                                                            <strong><?= $booking['make'] ?> <?= $booking['model'] ?></strong><br>
                                                            <small class="text-muted">
                                                                <?= ucfirst($booking['fuel_type']) ?> | 
                                                                <?= $booking['seats'] ?> seats
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>Booking Period</h6>
                                                    <p>
                                                        <strong>Start Date:</strong> <?= date('M j, Y', strtotime($booking['start_date'])) ?><br>
                                                        <strong>End Date:</strong> <?= date('M j, Y', strtotime($booking['end_date'])) ?><br>
                                                        <strong>Duration:</strong> <?= 
                                                            (strtotime($booking['end_date']) - strtotime($booking['start_date'])) / (60 * 60 * 24) + 1 
                                                        ?> days
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>Payment & Status</h6>
                                                    <p>
                                                        <strong>Price per day:</strong> ₹<?= 
                                                            number_format($booking['total_price'] / 
                                                            ((strtotime($booking['end_date']) - strtotime($booking['start_date'])) / (60 * 60 * 24) + 1), 2) 
                                                        ?><br>
                                                        <strong>Total Price:</strong> ₹<?= number_format($booking['total_price'], 2) ?><br>
                                                        <strong>Status:</strong> 
                                                        <span class="badge bg-<?= 
                                                            $booking['status'] === 'confirmed' ? 'success' : 
                                                            ($booking['status'] === 'pending' ? 'warning' : 'danger') 
                                                        ?>">
                                                            <?= ucfirst($booking['status']) ?>
                                                        </span><br>
                                                        <strong>Delivery Status:</strong> 
                                                        <span class="badge badge-<?= 
                                                            ($booking['delivery_status'] ?? 'not_delivered') === 'delivered' ? 'delivered' : 
                                                            (($booking['delivery_status'] ?? 'not_delivered') === 'returned' ? 'returned' : 'not-delivered')
                                                        ?>">
                                                            <?= ucfirst($booking['delivery_status'] ?? 'not delivered') ?>
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
