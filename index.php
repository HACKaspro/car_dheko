<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$loggedIn = isLoggedIn();
$cars = getAvailableCars();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Car Rentals | Luxury & Economy Vehicles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --dark-color: #1a1a2e;
            --light-color: #f8f9fa;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--dark-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 120px 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('assets/images/hero-pattern.png') center/cover no-repeat;
            opacity: 0.1;
            z-index: 0;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .car-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            background: white;
            position: relative;
        }
        
        .car-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }
        
        .car-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--primary-color);
            color: white;
            padding: 5px 15px;
            border-radius: 50px;
            font-weight: 600;
            z-index: 2;
        }
        
        .car-img-container {
            height: 180px;
            overflow: hidden;
            position: relative;
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
        
        .car-body {
            padding: 20px;
        }
        
        .car-title {
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--dark-color);
        }
        
        .car-features {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            color: #6c757d;
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            color: var(--primary-color);
        }
        
        .step-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border-top: 3px solid var(--primary-color);
        }
        
        .step-number {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .testimonial-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            position: relative;
        }
        
        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: 10px;
            left: 20px;
            font-size: 60px;
            color: rgba(67, 97, 238, 0.1);
            font-family: serif;
            line-height: 1;
        }
        
        .rating {
            color: #ffc107;
        }
        
        .testimonial-author {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .testimonial-author strong {
            display: block;
            color: var(--dark-color);
        }
        
        .testimonial-author span {
            font-size: 14px;
            color: #6c757d;
        }
        
        .section-header {
            margin-bottom: 60px;
        }
        
        .section-title {
            font-weight: 700;
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
            color: var(--dark-color);
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: var(--primary-color);
        }
        
        .section-subtitle {
            color: #6c757d;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .btn-outline-light:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .cta-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('assets/images/cta-pattern.png') center/cover no-repeat;
            opacity: 0.1;
            z-index: 0;
        }
        
        .cta-content {
            position: relative;
            z-index: 1;
        }
        
        .floating-animation {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }

        /* Modal car details styles */
        .car-detail-img {
            height: 250px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .car-detail-feature {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .car-detail-feature i {
            width: 30px;
            color: var(--primary-color);
            margin-right: 10px;
        }
        
        .car-detail-description {
            margin: 20px 0;
            line-height: 1.8;
        }
        
        .specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        
        .spec-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        
        .spec-icon {
            font-size: 24px;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .spec-value {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .spec-label {
            font-size: 14px;
            color: #6c757d;
        }
        
        .modal-footer .btn {
            min-width: 120px;
        }
        
        /* Car collection section */
        .car-collection-section {
            padding: 80px 0;
        }
        
        .car-collection-header {
            margin-bottom: 40px;
            text-align: center;
        }
        
        .car-collection-title {
            font-weight: 700;
            color: var(--dark-color);
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .car-collection-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: var(--primary-color);
        }
        
        .car-collection-subtitle {
            color: #6c757d;
            max-width: 700px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content animate__animated animate__fadeInLeft">
                    <h1 class="display-4 fw-bold mb-4">Experience Premium Car Rentals</h1>
                    <p class="lead mb-5">Discover the perfect vehicle for your journey with our curated collection of luxury and economy cars. Unmatched quality and service.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="#car-collection" class="btn btn-primary btn-lg px-4 py-3">
                            <i class="fas fa-car me-2"></i>View Our Collection
                        </a>
                        <a href="<?= $loggedIn ? 'dashboard.php' : 'register.php' ?>" class="btn btn-outline-light btn-lg px-4 py-3">
                            <i class="fas fa-calendar-alt me-2"></i><?= $loggedIn ? 'My Bookings' : 'Reserve Now' ?>
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block animate__animated animate__fadeInRight">
                    <img src="assets/images/ford_mustang.png" alt="Luxury Car" class="img-fluid floating-animation" style="max-height: 400px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card text-center p-4 h-100 animate__animated animate__fadeInUp" data-wow-delay="0.1s">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-tag fa-3x"></i>
                        </div>
                        <h3 class="mb-3">Best Prices</h3>
                        <p class="mb-0">Competitive rates with transparent pricing. No hidden fees or surprises.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center p-4 h-100 animate__animated animate__fadeInUp" data-wow-delay="0.2s">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-car fa-3x"></i>
                        </div>
                        <h3 class="mb-3">Diverse Fleet</h3>
                        <p class="mb-0">From compact cars to luxury SUVs, we have options for every need.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center p-4 h-100 animate__animated animate__fadeInUp" data-wow-delay="0.3s">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-headset fa-3x"></i>
                        </div>
                        <h3 class="mb-3">24/7 Support</h3>
                        <p class="mb-0">Our dedicated team is always ready to assist you anytime, anywhere.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Car Collection Section -->
    <section class="car-collection-section" id="car-collection">
        <div class="container">
            <div class="car-collection-header">
                <h2 class="car-collection-title animate__animated animate__fadeInDown">Our Car Collection</h2>
                <p class="car-collection-subtitle animate__animated animate__fadeInDown animate__delay-1s">Browse our complete fleet of premium vehicles</p>
            </div>
            
            <div class="row g-4">
                <?php foreach ($cars as $car): ?>
                <div class="col-md-6 col-lg-4 col-xl-3 animate__animated animate__fadeInUp">
                    <div class="car-card">
                        <div class="car-badge">₹<?= number_format($car['price_per_day'], 2) ?>/day</div>
                        <div class="car-img-container">
                            <img src="assets/images/<?= htmlspecialchars($car['image_path']) ?>" class="car-img" alt="<?= htmlspecialchars($car['make']) ?>">
                        </div>
                        <div class="car-body">
                            <h5 class="car-title"><?= htmlspecialchars($car['make']) ?> <?= htmlspecialchars($car['model']) ?></h5>
                            <div class="car-features">
                                <span><i class="fas fa-gas-pump"></i> <?= htmlspecialchars($car['fuel_type']) ?></span>
                                <span><i class="fas fa-users"></i> <?= $car['seats'] ?></span>
                                <span><i class="fas fa-tachometer-alt"></i> <?= htmlspecialchars($car['mileage']) ?></span>
                            </div>
                            <button class="btn btn-primary w-100 mt-3 view-details-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#carDetailsModal"
                                    data-car-id="<?= $car['id'] ?>"
                                    data-car-make="<?= htmlspecialchars($car['make']) ?>"
                                    data-car-model="<?= htmlspecialchars($car['model']) ?>"
                                    data-car-year="<?= htmlspecialchars($car['year']) ?>"
                                    data-car-price="<?= number_format($car['price_per_day'], 2) ?>"
                                    data-car-fuel="<?= htmlspecialchars($car['fuel_type']) ?>"
                                    data-car-seats="<?= $car['seats'] ?>"
                                    data-car-mileage="<?= htmlspecialchars($car['mileage']) ?>"
                                    data-car-image="<?= htmlspecialchars($car['image_path']) ?>"
                                    data-car-description="<?= htmlspecialchars($car['description'] ?? 'Premium quality vehicle with excellent performance and comfort features.') ?>">
                                <i class="fas fa-info-circle me-2"></i>View Details
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (!$loggedIn): ?>
            <div class="text-center mt-5 animate__animated animate__fadeIn">
                <a href="register.php" class="btn btn-primary btn-lg px-5 py-3">
                    <i class="fas fa-user-plus me-2"></i>Register to Book
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-5 bg-light">
        <div class="container py-5">
            <div class="section-header mb-5 text-center">
                <h2 class="section-title animate__animated animate__fadeInDown">Simple Rental Process</h2>
                <p class="section-subtitle animate__animated animate__fadeInDown animate__delay-1s">Get your dream car in just 3 easy steps</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4 animate__animated animate__fadeInLeft">
                    <div class="step-card text-center p-4 h-100">
                        <div class="step-number">1</div>
                        <h3 class="mb-3">Select Your Car</h3>
                        <p class="mb-0">Browse our collection and choose your perfect vehicle</p>
                    </div>
                </div>
                <div class="col-md-4 animate__animated animate__fadeInUp">
                    <div class="step-card text-center p-4 h-100">
                        <div class="step-number">2</div>
                        <h3 class="mb-3">Customize Booking</h3>
                        <p class="mb-0">Pick your dates and any additional options</p>
                    </div>
                </div>
                <div class="col-md-4 animate__animated animate__fadeInRight">
                    <div class="step-card text-center p-4 h-100">
                        <div class="step-number">3</div>
                        <h3 class="mb-3">Hit the Road</h3>
                        <p class="mb-0">Pick up your car and enjoy your journey</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-5">
        <div class="container py-5">
            <div class="section-header mb-5 text-center">
                <h2 class="section-title animate__animated animate__fadeInDown">Customer Experiences</h2>
                <p class="section-subtitle animate__animated animate__fadeInDown animate__delay-1s">What our clients say about us</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4 animate__animated animate__fadeInLeft">
                    <div class="testimonial-card p-4 h-100">
                        <div class="rating mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"The entire experience was seamless. The car was clean, well-maintained, and exactly as described. Will definitely rent again!"</p>
                        <div class="testimonial-author">
                            <strong>Rahul Sharma</strong>
                            <span>Mumbai</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 animate__animated animate__fadeInUp">
                    <div class="testimonial-card p-4 h-100">
                        <div class="rating mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"Excellent customer service! They went above and beyond to accommodate my last-minute booking request. Highly recommended!"</p>
                        <div class="testimonial-author">
                            <strong>Priya Patel</strong>
                            <span>Delhi</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 animate__animated animate__fadeInRight">
                    <div class="testimonial-card p-4 h-100">
                        <div class="rating mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <p class="testimonial-text">"Great prices and the car was in perfect condition. The pickup and drop-off process was smooth and hassle-free."</p>
                        <div class="testimonial-author">
                            <strong>Amit Singh</strong>
                            <span>Bangalore</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container text-center py-5">
            <div class="cta-content animate__animated animate__zoomIn">
                <h2 class="mb-4 display-5 fw-bold">Ready for Your Next Adventure?</h2>
                <p class="lead mb-5">Join thousands of satisfied customers who trust us for their mobility needs</p>
                <a href="<?= $loggedIn ? 'dashboard.php' : 'register.php' ?>" class="btn btn-light btn-lg px-5 py-3">
                    <?= $loggedIn ? 'Book Another Car' : 'Get Started Now' ?> <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Car Details Modal -->
    <div class="modal fade" id="carDetailsModal" tabindex="-1" aria-labelledby="carDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="carDetailsModalLabel">Car Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <img id="modalCarImage" src="" alt="Car Image" class="img-fluid car-detail-img">
                        </div>
                        <div class="col-md-6">
                            <h3 id="modalCarTitle" class="mb-3"></h3>
                            <div class="car-detail-feature">
                                <i class="fas fa-tag"></i>
                                <span id="modalCarPrice"></span>
                            </div>
                            <div class="car-detail-feature">
                                <i class="fas fa-gas-pump"></i>
                                <span id="modalCarFuel"></span>
                            </div>
                            <div class="car-detail-feature">
                                <i class="fas fa-users"></i>
                                <span id="modalCarSeats"></span>
                            </div>
                            <div class="car-detail-feature">
                                <i class="fas fa-tachometer-alt"></i>
                                <span id="modalCarMileage"></span>
                            </div>
                            <div class="car-detail-feature">
                                <i class="fas fa-calendar-alt"></i>
                                <span id="modalCarYear"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h4>Description</h4>
                            <p id="modalCarDescription" class="car-detail-description"></p>
                            
                            <h4>Key Specifications</h4>
                            <div class="specs-grid">
                                <div class="spec-item">
                                    <div class="spec-icon"><i class="fas fa-gas-pump"></i></div>
                                    <div class="spec-value" id="specFuelType"></div>
                                    <div class="spec-label">Fuel Type</div>
                                </div>
                                <div class="spec-item">
                                    <div class="spec-icon"><i class="fas fa-users"></i></div>
                                    <div class="spec-value" id="specSeats"></div>
                                    <div class="spec-label">Seating Capacity</div>
                                </div>
                                <div class="spec-item">
                                    <div class="spec-icon"><i class="fas fa-tachometer-alt"></i></div>
                                    <div class="spec-value" id="specMileage"></div>
                                    <div class="spec-label">Mileage</div>
                                </div>
                                <div class="spec-item">
                                    <div class="spec-icon"><i class="fas fa-calendar-alt"></i></div>
                                    <div class="spec-value" id="specYear"></div>
                                    <div class="spec-label">Model Year</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <?php if ($loggedIn): ?>
                        <a href="dashboard.php" class="btn btn-primary">
                            <i class="fas fa-calendar-check me-2"></i>Book Now
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Register to Book
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
    <script>
        new WOW().init();
        
        // Car Details Modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const carDetailsModal = document.getElementById('carDetailsModal');
            if (carDetailsModal) {
                carDetailsModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    
                    // Extract car details from button data attributes
                    const carMake = button.getAttribute('data-car-make');
                    const carModel = button.getAttribute('data-car-model');
                    const carYear = button.getAttribute('data-car-year');
                    const carPrice = button.getAttribute('data-car-price');
                    const carFuel = button.getAttribute('data-car-fuel');
                    const carSeats = button.getAttribute('data-car-seats');
                    const carMileage = button.getAttribute('data-car-mileage');
                    const carImage = button.getAttribute('data-car-image');
                    const carDescription = button.getAttribute('data-car-description');
                    
                    // Update modal content
                    document.getElementById('modalCarTitle').textContent = `${carMake} ${carModel}`;
                    document.getElementById('modalCarImage').src = `assets/images/${carImage}`;
                    document.getElementById('modalCarImage').alt = `${carMake} ${carModel}`;
                    document.getElementById('modalCarPrice').textContent = `₹${carPrice}/day`;
                    document.getElementById('modalCarFuel').textContent = carFuel;
                    document.getElementById('modalCarSeats').textContent = `${carSeats} seats`;
                    document.getElementById('modalCarMileage').textContent = `${carMileage} kmpl`;
                    document.getElementById('modalCarYear').textContent = `Year: ${carYear}`;
                    document.getElementById('modalCarDescription').textContent = carDescription;
                    
                    // Update specifications
                    document.getElementById('specFuelType').textContent = carFuel;
                    document.getElementById('specSeats').textContent = carSeats;
                    document.getElementById('specMileage').textContent = `${carMileage} kmpl`;
                    document.getElementById('specYear').textContent = carYear;
                });
            }
        });
    </script>
    <script src="assets/js/script.js"></script>
</body>
</html>