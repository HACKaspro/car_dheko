<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Premium Car Rental Services</title>
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
        
        .about-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/images/about-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            margin-bottom: 50px;
            text-align: center;
        }
        
        .about-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .about-header {
            position: relative;
            padding: 30px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            text-align: center;
        }
        
        .about-header h2 {
            font-weight: 700;
            position: relative;
            display: inline-block;
        }
        
        .about-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: white;
        }
        
        .about-img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .about-img:hover {
            transform: scale(1.02);
        }
        
        .feature-box {
            padding: 30px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
            border-top: 4px solid var(--primary-color);
        }
        
        .feature-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .section-title {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--primary-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
        }
        
        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
        }
        
        .mission-section {
            background-color: #f9f9f9;
            padding: 50px;
            border-radius: 15px;
            margin: 50px 0;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="about-hero animate__animated animate__fadeIn">
        <div class="container">
            <h1 class="display-3 fw-bold mb-4">Our Story</h1>
            <p class="lead">Driving your journeys with passion and reliability</p>
        </div>
    </section>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="about-card animate__animated animate__fadeInUp">
                    <div class="about-header">
                        <h2 class="text-white">About CarRental</h2>
                    </div>
                    
                    <div class="card-body p-5">
                        <div class="row align-items-center mb-5">
                            <div class="col-lg-6">
                                <img src="assets/images/car_key.png" class="about-img" alt="Car Rental Service">
                            </div>
                            <div class="col-lg-6">
                                <h3 class="section-title">Our Journey</h3>
                                <p class="lead">
                                    Founded in 2023, CarRental has quickly become a trusted name in the car rental industry. 
                                    We started with a small fleet of just 5 cars and a big dream to revolutionize the way 
                                    people rent vehicles for their travel needs.
                                </p>
                                <p>
                                    Today, we serve thousands of satisfied customers across the country with our growing 
                                    fleet of premium vehicles and exceptional service.
                                </p>
                            </div>
                        </div>
                        
                        <div class="mission-section">
                            <h3 class="section-title text-center">Our Mission</h3>
                            <p class="text-center lead">
                                To provide reliable, affordable, and convenient car rental services with exceptional 
                                customer service. We believe everyone deserves a hassle-free rental experience, whether 
                                you're traveling for business or pleasure.
                            </p>
                        </div>
                        
                        <div class="mb-5">
                            <h3 class="section-title text-center mb-5">Why Choose Us?</h3>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="feature-box text-center">
                                        <div class="feature-icon">
                                            <i class="fas fa-car"></i>
                                        </div>
                                        <h4>Diverse Fleet</h4>
                                        <p>From compact cars to luxury SUVs, we have the perfect vehicle for every need and budget.</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="feature-box text-center">
                                        <div class="feature-icon">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                        <h4>Best Value</h4>
                                        <p>Competitive rates with transparent pricing and no hidden fees.</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="feature-box text-center">
                                        <div class="feature-icon">
                                            <i class="fas fa-headset"></i>
                                        </div>
                                        <h4>Always Available</h4>
                                        <p>24/7 customer support ready to assist you whenever you need.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-5">
                            <a href="index.php" class="btn btn-primary btn-lg me-3">Explore Our Fleet</a>
                            <?php if (!isLoggedIn()): ?>
                                <a href="register.php" class="btn btn-outline-primary btn-lg">Join Now</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animation trigger for feature boxes
        document.addEventListener('DOMContentLoaded', function() {
            const features = document.querySelectorAll('.feature-box');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                        }, index * 100);
                    }
                });
            }, { threshold: 0.1 });
            
            features.forEach(feature => {
                observer.observe(feature);
            });
        });
    </script>
</body>
</html>