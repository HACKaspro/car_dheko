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
        if (bookCar($_SESSION['user_id'], $car_id, $start_date, $end_date, $payment_method)) {
            $_SESSION['success'] = "Car booked successfully!";
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to book car. Please try again.";
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
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .profile-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .profile-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 25px;
            font-weight: 600;
        }
        
        .car-card {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .car-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .price-label {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .car-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .car-card:hover .car-img {
            transform: scale(1.05);
        }
        
        .badge {
            font-weight: 500;
            padding: 6px 10px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .table th {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .feature-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(67, 97, 238, 0.1);
            border-radius: 50%;
            color: var(--primary-color);
            margin-right: 10px;
        }
        
        @media (max-width: 768px) {
            .car-img {
                height: 150px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-4">
        <div class="row">
            <div class="col-md-4">
                <!-- User Profile Card -->
                <div class="profile-card mb-4">
                    <div class="card-body text-center py-4">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['full_name']) ?>&background=random" class="profile-img rounded-circle mb-3">
                        <h4 class="mb-1"><?= htmlspecialchars($user['full_name']) ?></h4>
                        <p class="text-muted mb-3">@<?= htmlspecialchars($user['username']) ?></p>
                        
                        <div class="d-flex justify-content-center mb-3">
                            <div class="px-3 text-center">
                                <p class="mb-0 fw-bold fs-5"><?= count($bookings) ?></p>
                                <p class="mb-0 text-muted small">Bookings</p>
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
            </div>
            
            <div class="col-md-8">
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
                            <div class="alert alert-success alert-dismissible fade show">
                                <?= $_SESSION['success'] ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= $_SESSION['error'] ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        
                        <?php if (empty($bookings)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                                <h5>No Bookings Yet</h5>
                                <p class="text-muted">You haven't made any bookings yet. Start by exploring our available cars.</p>
                                <a href="#available-cars" class="btn btn-primary">View Available Cars</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Car</th>
                                            <th>Dates</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="assets/images/<?= htmlspecialchars($booking['image_path']) ?>" class="rounded me-3" width="60" height="40" style="object-fit: cover;">
                                                    <div>
                                                        <strong><?= htmlspecialchars($booking['make']) ?> <?= htmlspecialchars($booking['model']) ?></strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?= date('M j, Y', strtotime($booking['start_date'])) ?><br>
                                                <small class="text-muted">to</small><br>
                                                <?= date('M j, Y', strtotime($booking['end_date'])) ?>
                                            </td>
                                            <td>₹<?= number_format($booking['total_price'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $booking['status'] === 'confirmed' ? 'success' : 
                                                    ($booking['status'] === 'pending' ? 'warning' : 'danger') 
                                                ?>">
                                                    <?= ucfirst($booking['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                                        data-bs-target="#bookingModal<?= $booking['id'] ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                                                        <a href="dashboard.php?cancel_booking=<?= $booking['id'] ?>" 
                                                           class="btn btn-sm btn-outline-danger"
                                                           onclick="return confirm('Are you sure you want to cancel this booking?')">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- Booking Details Modal -->
                                        <div class="modal fade" id="bookingModal<?= $booking['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Booking Details</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mb-4">
                                                            <div class="col-md-4">
                                                                <img src="assets/images/<?= htmlspecialchars($booking['image_path']) ?>" 
                                                                     class="img-fluid rounded shadow">
                                                            </div>
                                                            <div class="col-md-8">
                                                                <h4><?= htmlspecialchars($booking['make']) ?> <?= htmlspecialchars($booking['model']) ?></h4>
                                                                <div class="d-flex align-items-center text-muted mb-2">
                                                                    <i class="fas fa-tag me-2"></i>
                                                                    <span>Booking ID: <?= $booking['id'] ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="card mb-3">
                                                                    <div class="card-body">
                                                                        <h6 class="card-title text-muted">Booking Dates</h6>
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
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                        <h6 class="card-title text-muted">Payment Details</h6>
                                                                        <div class="d-flex justify-content-between mb-2">
                                                                            <span>Total Price:</span>
                                                                            <strong>₹<?= number_format($booking['total_price'], 2) ?></strong>
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
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
                        <h5 class="mb-0 text-white"><i class="fas fa-car me-2"></i> Available Cars</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($cars as $car): ?>
                            <div class="col-md-6 mb-4">
                                <div class="car-card h-100">
                                    <div class="price-label">₹<?= number_format($car['price_per_day'], 2) ?>/day</div>
                                    <img src="assets/images/<?= htmlspecialchars($car['image_path']) ?>" class="car-img" alt="<?= htmlspecialchars($car['make']) ?>">
                                    <div class="card-body">
                                        <h5><?= htmlspecialchars($car['make']) ?> <?= htmlspecialchars($car['model']) ?></h5>
                                        <div class="d-flex justify-content-between align-items-center text-muted mb-3">
                                            <span><i class="fas fa-gas-pump me-1"></i> <?= htmlspecialchars($car['fuel_type']) ?></span>
                                            <span><i class="fas fa-users me-1"></i> <?= $car['seats'] ?> seats</span>
                                        </div>
                                        <button class="btn btn-primary w-100" data-bs-toggle="modal" 
                                            data-bs-target="#bookModal" 
                                            data-car-id="<?= $car['id'] ?>"
                                            data-car-name="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>"
                                            data-car-price="<?= $car['price_per_day'] ?>"
                                            data-car-image="<?= htmlspecialchars($car['image_path']) ?>">
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

    <!-- Booking Modal -->
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
                                <input type="date" name="start_date" id="start_date" class="form-control" required>
                                <div class="invalid-feedback">Please select a valid start date</div>
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">End Date *</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" required>
                                <div class="invalid-feedback">Please select a valid end date</div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Booking Summary</h6>
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
                                        <h6 class="mb-0">Payment Method</h6>
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
                                            <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal">
                                            <label class="form-check-label d-flex align-items-center" for="paypal">
                                                <i class="fab fa-paypal me-3 text-primary"></i>
                                                <div>
                                                    <div>PayPal</div>
                                                    <small class="text-muted">Secure online payments</small>
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
        const bookModal = document.getElementById('bookModal');
        const bookingForm = document.getElementById('bookingForm');
        const today = new Date().toISOString().split('T')[0];
        
        // Set minimum dates
        document.getElementById('start_date').min = today;
        document.getElementById('end_date').min = today;
        
        // Modal show event - enhanced with more car details
        bookModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const carId = button.getAttribute('data-car-id');
            const carName = button.getAttribute('data-car-name');
            const carPrice = parseFloat(button.getAttribute('data-car-price'));
            const carImage = button.getAttribute('data-car-image');
            const carFuel = button.closest('.car-card').querySelector('.fa-gas-pump').nextSibling.textContent.trim();
            const carSeats = button.closest('.car-card').querySelector('.fa-users').nextSibling.textContent.trim();
            
            // Set car details
            document.getElementById('modalCarId').value = carId;
            document.getElementById('modalCarName').textContent = carName;
            document.getElementById('modalCarDetails').textContent = `${carFuel} • ${carSeats} seats`;
            document.getElementById('modalCarPrice').textContent = `₹${carPrice.toFixed(2)}/day`;
            document.getElementById('summaryPricePerDay').textContent = `₹${carPrice.toFixed(2)}`;
            document.getElementById('modalCarImage').src = `assets/images/${carImage}`;
            
            // Reset form
            bookingForm.reset();
            document.getElementById('start_date').value = '';
            document.getElementById('end_date').value = '';
            document.getElementById('summaryDuration').textContent = '0 days';
            document.getElementById('summaryTotalPrice').textContent = '₹0.00';
        });
        
        // Date change handlers
        document.getElementById('start_date').addEventListener('change', updateBookingSummary);
        document.getElementById('end_date').addEventListener('change', updateBookingSummary);
        
        function updateBookingSummary() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const carPrice = parseFloat(document.getElementById('summaryPricePerDay').textContent.replace('₹', ''));
            
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                if (start <= end) {
                    const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
                    const totalPrice = days * carPrice;
                    
                    document.getElementById('summaryDuration').textContent = `${days} day${days > 1 ? 's' : ''}`;
                    document.getElementById('summaryTotalPrice').textContent = `₹${totalPrice.toFixed(2)}`;
                    
                    // Enable submit button
                    document.getElementById('confirmBookingBtn').disabled = false;
                } else {
                    document.getElementById('summaryDuration').textContent = 'Invalid date range';
                    document.getElementById('summaryTotalPrice').textContent = '₹0.00';
                    document.getElementById('confirmBookingBtn').disabled = true;
                }
            }
        }
        
        // Form validation
        bookingForm.addEventListener('submit', function(event) {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (!startDate || !endDate) {
                event.preventDefault();
                event.stopPropagation();
                
                if (!startDate) {
                    document.getElementById('start_date').classList.add('is-invalid');
                }
                if (!endDate) {
                    document.getElementById('end_date').classList.add('is-invalid');
                }
            }
            
            bookingForm.classList.add('was-validated');
        }, false);
    });
    </script>
</body>
</html>