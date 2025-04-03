
<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user = getCurrentUser();
$bookings = getUserBookings($_SESSION['user_id']);
$cars = getAvailableCars();

// Handle booking cancellation
if (isset($_GET['cancel_booking'])) {
    $booking_id = intval($_GET['cancel_booking']);
    if (cancelBooking($booking_id, $_SESSION['user_id'])) {
        $_SESSION['success'] = "Booking cancelled successfully";
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to cancel booking";
    }
}

// Handle new booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_car'])) {
    $car_id = intval($_POST['car_id']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $payment_method = $_POST['payment_method'];
    
    // Validate dates
    if (strtotime($start_date) >= strtotime($end_date)) {
        $_SESSION['error'] = "End date must be after start date";
    } else {
        try {
            $result = bookCar($_SESSION['user_id'], $car_id, $start_date, $end_date, $payment_method);
            if ($result === true) {
                $_SESSION['success'] = "Car booked successfully!";
                header("Location: dashboard.php");
                exit();
            } else {
                // Log the specific error
                error_log("Booking failed: " . $result);
                $_SESSION['error'] = $result ?: "Booking failed due to an unknown error";
            }
        } catch (Exception $e) {
            error_log("Booking exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            $_SESSION['error'] = "Booking error: " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Premium Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --dark-color: #1a1a2e;
            --light-color: #f8f9fa;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }
        
        body {
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            color: #2d3748;
        }
        
        /* Profile Card */
        .profile-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
        }
        
        .profile-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-bottom: 25px;
            background: white;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 16px 24px;
            font-weight: 600;
        }
        
        /* Car Cards */
        .car-card {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            height: 100%;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .price-label {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            z-index: 1;
        }
        
        .car-img-container {
            position: relative;
            overflow: hidden;
            height: 200px;
        }
        
        .car-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .car-card:hover .car-img {
            transform: scale(1.05);
        }
        
        /* Buttons */
        .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
            padding: 8px 20px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        /* Status colors */
        .bg-success {
            background-color: var(--success-color) !important;
        }
        
        .bg-warning {
            background-color: var(--warning-color) !important;
        }
        
        .bg-danger {
            background-color: var(--danger-color) !important;
        }
        
        /* Delivery Status Colors */
        .bg-delivered {
            background-color: var(--success-color) !important;
        }
        
        .bg-returned {
            background-color: var(--primary-color) !important;
        }
        
        .bg-not-delivered {
            background-color: var(--secondary-color) !important;
        }
        
        /* Modal Improvements */
        .modal {
            backdrop-filter: blur(2px);
        }
        
        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-bottom: none;
        }
        
        .modal-body img {
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
            max-width: 100%;
            height: auto;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .car-img-container {
                height: 160px;
            }
            
            .profile-img {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-4">
        <!-- Welcome Banner -->
        <div class="alert alert-primary d-flex align-items-center mb-4 animate__animated animate__fadeIn">
            <i class="fas fa-car-side fa-2x me-3"></i>
            <div>
                <h4 class="alert-heading mb-1">Welcome back, <?= htmlspecialchars($user['full_name']) ?>!</h4>
                <p class="mb-0">Ready for your next adventure? Browse our premium collection below.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-4">
                <!-- User Profile Card -->
                <div class="profile-card mb-4 animate__animated animate__fadeInLeft">
                    <div class="card-body text-center py-4">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['full_name']) ?>&background=<?= substr(md5($user['email']), 0, 6) ?>&color=fff" 
                             class="profile-img rounded-circle mb-3">
                        <h4 class="mb-1"><?= htmlspecialchars($user['full_name']) ?></h4>
                        <p class="text-muted mb-3">@<?= htmlspecialchars($user['username']) ?></p>
                        
                        <div class="d-flex justify-content-center mb-3">
                            <div class="px-3 text-center border-end">
                                <p class="mb-0 fw-bold fs-5"><?= count($bookings) ?></p>
                                <p class="mb-0 text-muted small">Bookings</p>
                            </div>
                            <div class="px-3 text-center">
                                <p class="mb-0 fw-bold fs-5"><?= date('M Y') ?></p>
                                <p class="mb-0 text-muted small">Member Since</p>
                            </div>
                        </div>
                        
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="feature-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <span>Email</span>
                                </div>
                                <span class="text-muted"><?= htmlspecialchars($user['email']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="feature-icon">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <span>Phone</span>
                                </div>
                                <span class="text-muted"><?= htmlspecialchars($user['phone']) ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card mb-4 animate__animated animate__fadeInLeft animate__delay-1s">
                    <div class="card-header">
                        <h5 class="mb-0 text-white"><i class="fas fa-bolt me-2"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="#available-cars" class="btn btn-primary">
                                <i class="fas fa-car me-2"></i> Book a Car
                            </a>
                            <a href="profile.php" class="btn btn-outline-primary">
                                <i class="fas fa-user-edit me-2"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <!-- Bookings Section -->
                <div class="card mb-4 animate__animated animate__fadeInRight">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-white"><i class="fas fa-calendar-alt me-2"></i> My Bookings</h5>
                            <span class="badge bg-light text-dark"><?= count($bookings) ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center">
                                <i class="fas fa-check-circle me-2"></i>
                                <div><?= $_SESSION['success'] ?></div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <div><?= $_SESSION['error'] ?></div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        
                        <?php if (empty($bookings)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                                <h5>No Bookings Yet</h5>
                                <p class="text-muted">You haven't made any bookings yet. Start by exploring our available cars.</p>
                                <a href="#available-cars" class="btn btn-primary">
                                    <i class="fas fa-car me-2"></i> View Available Cars
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
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
                                        <tr class="animate-fade-in">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="assets/images/<?= htmlspecialchars($booking['image_path']) ?>" 
                                                         class="rounded me-3" width="60" height="40" 
                                                         style="object-fit: cover;">
                                                    <div>
                                                        <strong><?= htmlspecialchars($booking['make']) ?> <?= htmlspecialchars($booking['model']) ?></strong>
                                                        <div class="text-muted small">ID: <?= $booking['id'] ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span><?= date('M j, Y', strtotime($booking['start_date'])) ?></span>
                                                    <span class="text-muted small">to</span>
                                                    <span><?= date('M j, Y', strtotime($booking['end_date'])) ?></span>
                                                </div>
                                            </td>
                                            <td class="fw-bold">₹<?= number_format($booking['total_price'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $booking['status'] === 'confirmed' ? 'success' : 
                                                    ($booking['status'] === 'pending' ? 'warning' : 'danger') 
                                                ?>">
                                                    <?= ucfirst($booking['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($booking['status'] === 'confirmed'): ?>
                                                    <span class="badge bg-<?= 
                                                        $booking['delivery_status'] === 'delivered' ? 'success' : 
                                                        ($booking['delivery_status'] === 'returned' ? 'primary' : 'secondary')
                                                    ?>">
                                                        <?= ucfirst($booking['delivery_status'] ?? 'not delivered') ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-outline-primary rounded-circle" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#bookingModal<?= $booking['id'] ?>"
                                                            title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                                                        <a href="dashboard.php?cancel_booking=<?= $booking['id'] ?>" 
                                                           class="btn btn-sm btn-outline-danger rounded-circle"
                                                           title="Cancel Booking"
                                                           onclick="return confirm('Are you sure you want to cancel this booking?')">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Available Cars Section -->
                <div class="card animate__animated animate__fadeInRight animate__delay-1s" id="available-cars">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-white"><i class="fas fa-car me-2"></i> Available Cars</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">All Cars</a></li>
                                    <li><a class="dropdown-item" href="#">Economy</a></li>
                                    <li><a class="dropdown-item" href="#">Luxury</a></li>
                                    <li><a class="dropdown-item" href="#">SUV</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($cars as $car): ?>
                            <div class="col-md-6 mb-4">
                                <div class="car-card h-100">
                                    <div class="price-label">₹<?= number_format($car['price_per_day'], 2) ?>/day</div>
                                    <div class="car-img-container">
                                        <img src="assets/images/<?= htmlspecialchars($car['image_path']) ?>" class="car-img" alt="<?= htmlspecialchars($car['make']) ?>">
                                    </div>
                                    <div class="card-body">
                                        <h5><?= htmlspecialchars($car['make']) ?> <?= htmlspecialchars($car['model']) ?></h5>
                                        <div class="d-flex justify-content-between align-items-center text-muted mb-3">
                                            <span><i class="fas fa-gas-pump me-1"></i> <?= htmlspecialchars($car['fuel_type']) ?></span>
                                            <span><i class="fas fa-users me-1"></i> <?= $car['seats'] ?> seats</span>
                                            <span><i class="fas fa-tachometer-alt me-1"></i> <?= $car['mileage'] ?> kmpl</span>
                                        </div>
                                        <button class="btn btn-primary w-100" data-bs-toggle="modal" 
                                            data-bs-target="#bookModal" 
                                            data-car-id="<?= $car['id'] ?>"
                                            data-car-name="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>"
                                            data-car-price="<?= $car['price_per_day'] ?>"
                                            data-car-image="<?= htmlspecialchars($car['image_path']) ?>"
                                            data-car-fuel="<?= htmlspecialchars($car['fuel_type']) ?>"
                                            data-car-seats="<?= $car['seats'] ?>">
                                            <i class="fas fa-calendar-alt me-2"></i>Book Now
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Modals -->
    <?php foreach ($bookings as $booking): ?>
    <div class="modal fade" id="bookingModal<?= $booking['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Booking Details #<?= $booking['id'] ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <img src="assets/images/<?= htmlspecialchars($booking['image_path']) ?>" 
                                 class="img-fluid rounded shadow" 
                                 alt="<?= htmlspecialchars($booking['make'] . ' ' . $booking['model']) ?>"
                                 style="max-height: 200px; width: auto; object-fit: contain;">
                        </div>
                        <div class="col-md-8">
                            <h4><?= htmlspecialchars($booking['make']) ?> <?= htmlspecialchars($booking['model']) ?></h4>
                            <div class="d-flex align-items-center text-muted mb-2">
                                <i class="fas fa-tag me-2"></i>
                                <span>Booking ID: <?= $booking['id'] ?></span>
                            </div>
                            <div class="d-flex align-items-center text-muted mb-2">
                                <i class="fas fa-clock me-2"></i>
                                <span>Booked on: <?= date('M j, Y H:i', strtotime($booking['created_at'])) ?></span>
                            </div>
                                <div class="d-flex align-items-center text-muted mb-2">
                                    <i class="fas fa-truck me-2"></i>
                                    <span>Delivery Status: 
                                        <span class="badge bg-<?= 
                                            $booking['delivery_status'] === 'delivered' ? 'success' : 
                                            ($booking['delivery_status'] === 'returned' ? 'primary' : 'secondary')
                                        ?>">
                                            <?= ucfirst($booking['delivery_status'] ?? 'not delivered') ?>
                                        </span>
                                    </span>
                                </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3 border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3">
                                        <i class="fas fa-calendar-day me-2"></i>Booking Dates
                                    </h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>From:</span>
                                        <strong><?= date('M j, Y', strtotime($booking['start_date'])) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>To:</span>
                                        <strong><?= date('M j, Y', strtotime($booking['end_date'])) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Duration:</span>
                                        <strong><?= 
                                            (strtotime($booking['end_date']) - strtotime($booking['start_date'])) / (60 * 60 * 24) + 1 
                                        ?> days</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3">
                                        <i class="fas fa-receipt me-2"></i>Payment Details
                                    </h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total Price:</span>
                                        <strong>₹<?= number_format($booking['total_price'], 2) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Daily Rate:</span>
                                        <strong>₹<?= number_format($booking['total_price'] / 
                                            ((strtotime($booking['end_date']) - strtotime($booking['start_date'])) / (60 * 60 * 24) + 1), 2) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Status:</span>
                                        <span class="badge bg-<?= 
                                            $booking['status'] === 'confirmed' ? 'success' : 
                                            ($booking['status'] === 'pending' ? 'warning' : 'danger') 
                                        ?>">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                        <a href="dashboard.php?cancel_booking=<?= $booking['id'] ?>" 
                           class="btn btn-danger"
                           onclick="return confirm('Are you sure you want to cancel this booking?')">
                            <i class="fas fa-times me-2"></i>Cancel Booking
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Book Car Modal -->
    <div class="modal fade" id="bookModal" tabindex="-1" aria-labelledby="bookModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookModalLabel">Book Car</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="bookingForm" action="dashboard.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="car_id" id="modalCarId">
                        <input type="hidden" name="book_car" value="1">
                        <div class="row mb-4 align-items-center">
                            <div class="col-md-3">
                                <img id="modalCarImage" src="" class="img-fluid rounded shadow" alt="Car Image">
                            </div>
                            <div class="col-md-9">
                                <h4 id="modalCarName" class="mb-1"></h4>
                                <p class="text-muted mb-2" id="modalCarDetails"></p>
                                <span class="badge bg-success fs-6" id="modalCarPrice"></span>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Start Date *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                                </div>
                                <div class="invalid-feedback">Please select a valid start date</div>
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">End Date *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                                </div>
                                <div class="invalid-feedback">Please select a valid end date</div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Booking Summary</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tr>
                                                <td>Price per day:</td>
                                                <td class="text-end" id="summaryPricePerDay"></td>
                                            </tr>
                                            <tr>
                                                <td>Duration:</td>
                                                <td class="text-end" id="summaryDuration"></td>
                                            </tr>
                                            <tr class="table-active">
                                                <th>Total Price:</th>
                                                <th class="text-end" id="summaryTotalPrice"></th>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Method</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="radio" name="payment_method" id="creditCard" value="credit_card" checked>
                                            <label class="form-check-label d-flex align-items-center" for="creditCard">
                                                <i class="fas fa-credit-card me-3 text-primary"></i>
                                                <div>
                                                    <div>Credit/Debit Card</div>
                                                    <small class="text-muted">Visa, Mastercard, etc.</small>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="radio" name="payment_method" id="upi" value="upi">
                                            <label class="form-check-label d-flex align-items-center" for="upi">
                                                <i class="fas fa-mobile-alt me-3 text-primary"></i>
                                                <div>
                                                    <div>UPI Payment</div>
                                                    <small class="text-muted">Instant bank transfers</small>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash">
                                            <label class="form-check-label d-flex align-items-center" for="cash">
                                                <i class="fas fa-money-bill-wave me-3 text-primary"></i>
                                                <div>
                                                    <div>Cash on Delivery</div>
                                                    <small class="text-muted">Pay when you receive the car</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="confirmBookingBtn">
                            <i class="fas fa-calendar-check me-2"></i>Confirm Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize popovers
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });

        // Book Modal functionality
        const bookModal = document.getElementById('bookModal');
        if (bookModal) {
            bookModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const modal = this;
                
                // Extract car details from button data attributes
                const carId = button.getAttribute('data-car-id');
                const carName = button.getAttribute('data-car-name');
                const carPrice = parseFloat(button.getAttribute('data-car-price'));
                const carImage = button.getAttribute('data-car-image');
                const carFuel = button.getAttribute('data-car-fuel');
                const carSeats = button.getAttribute('data-car-seats');
                
                // Update modal content
                modal.querySelector('#modalCarId').value = carId;
                modal.querySelector('#modalCarName').textContent = carName;
                modal.querySelector('#modalCarDetails').textContent = `${carFuel} • ${carSeats} seats`;
                modal.querySelector('#modalCarPrice').textContent = `₹${carPrice.toFixed(2)}/day`;
                modal.querySelector('#modalCarImage').src = `assets/images/${carImage}`;
                modal.querySelector('#summaryPricePerDay').textContent = `₹${carPrice.toFixed(2)}`;
                
                // Set default dates (today and tomorrow)
                const today = new Date().toISOString().split('T')[0];
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                const tomorrowStr = tomorrow.toISOString().split('T')[0];
                
                modal.querySelector('#start_date').value = today;
                modal.querySelector('#end_date').value = tomorrowStr;
                modal.querySelector('#start_date').min = today;
                modal.querySelector('#end_date').min = today;
                
                // Calculate initial summary
                updateBookingSummary(modal);
            });
        }

        // Update booking summary when dates change
        document.addEventListener('change', function(e) {
            if (e.target.matches('#start_date, #end_date')) {
                const modal = e.target.closest('.modal');
                updateBookingSummary(modal);
            }
        });

        // Function to update booking summary
        function updateBookingSummary(modal) {
            if (!modal) return;
            
            const startDate = modal.querySelector('#start_date').value;
            const endDate = modal.querySelector('#end_date').value;
            const pricePerDay = parseFloat(modal.querySelector('#summaryPricePerDay').textContent.replace('₹', ''));
            
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                if (start <= end) {
                    const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
                    const totalPrice = days * pricePerDay;
                    
                    modal.querySelector('#summaryDuration').textContent = `${days} day${days > 1 ? 's' : ''}`;
                    modal.querySelector('#summaryTotalPrice').textContent = `₹${totalPrice.toFixed(2)}`;
                    
                    // Enable submit button
                    modal.querySelector('#confirmBookingBtn').disabled = false;
                } else {
                    modal.querySelector('#summaryDuration').textContent = 'Invalid date range';
                    modal.querySelector('#summaryTotalPrice').textContent = '₹0.00';
                    modal.querySelector('#confirmBookingBtn').disabled = true;
                }
            }
        }

        // Form validation
        const bookingForm = document.getElementById('bookingForm');
        if (bookingForm) {
            bookingForm.addEventListener('submit', function(event) {
                if (!this.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                this.classList.add('was-validated');
            }, false);
        }

        // Fix for Windows/Chrome blurry modals
        if (navigator.userAgent.indexOf('Windows') !== -1 && 
            navigator.userAgent.indexOf('Chrome') !== -1) {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('show.bs.modal', function() {
                    document.body.style.overflow = 'hidden';
                    document.body.style.paddingRight = '0px';
                });
                
                modal.addEventListener('hidden.bs.modal', function() {
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                });
            });
        }
    });
    </script>
</body>
</html>
