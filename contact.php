<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$loggedIn = isLoggedIn();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_message'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // Basic validation
    $errors = [];
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($subject)) $errors[] = "Subject is required";
    if (empty($message)) $errors[] = "Message is required";
    
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO contact_messages 
                (user_id, name, email, phone, subject, message, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'unread', NOW())
            ");
            
            $user_id = $loggedIn ? $_SESSION['user_id'] : NULL;
            
            $stmt->bind_param(
                "isssss", 
                $user_id,
                $name,
                $email,
                $phone,
                $subject,
                $message
            );
            
            if ($stmt->execute()) {
                $success = "Your message has been sent successfully! We'll get back to you soon.";
            } else {
                $error = "Failed to send message. Please try again.";
            }
            $stmt->close();
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Premium Car Rentals</title>
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
        
        .contact-hero {
            background: linear-gradient(135deg, var(--dark-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }
        
        .contact-hero::before {
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
        
        .contact-hero-content {
            position: relative;
            z-index: 1;
        }
        
        .contact-form-card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            border: none;
        }
        
        .contact-form-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 1.5rem;
        }
        
        .contact-form-body {
            padding: 2rem;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #e9ecef;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .contact-info-card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            height: 100%;
        }
        
        .contact-info-header {
            background: linear-gradient(135deg, #4cc9f0 0%, #4361ee 100%);
            color: white;
            padding: 1.5rem;
        }
        
        .contact-info-body {
            padding: 1.5rem;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        
        .contact-icon {
            background-color: #f1f3f5;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: var(--primary-color);
            font-size: 1rem;
        }
        
        .contact-text h5 {
            margin-bottom: 0.25rem;
            font-weight: 600;
        }
        
        .contact-text p {
            margin-bottom: 0;
            color: #6c757d;
        }
        
        .contact-text a {
            color: #6c757d;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .contact-text a:hover {
            color: var(--primary-color);
        }
        
        .map-container {
            border-radius: 10px;
            overflow: hidden;
            height: 300px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-top: 1.5rem;
        }
        
        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .section-header {
            margin-bottom: 40px;
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
        
        .floating-animation {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 contact-hero-content animate__animated animate__fadeInLeft">
                    <h1 class="display-4 fw-bold mb-4">Get In Touch</h1>
                    <p class="lead mb-5">Have questions or need assistance? Our team is here to help you with all your car rental needs.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="#contact-form" class="btn btn-light btn-lg px-4 py-3">
                            <i class="fas fa-envelope me-2"></i>Send a Message
                        </a>
                        <a href="tel:+919834402736" class="btn btn-outline-light btn-lg px-4 py-3">
                            <i class="fas fa-phone-alt me-2"></i>Call Us Now
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block animate__animated animate__fadeInRight">
                    <img src="assets/images/contactus.png" alt="Contact Us" class="img-fluid floating-animation" style="max-height: 400px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="py-5" id="contact-form">
        <div class="container py-5">
            <div class="section-header mb-5 text-center">
                <h2 class="section-title animate__animated animate__fadeInDown">Send Us a Message</h2>
                <p class="section-subtitle animate__animated animate__fadeInDown animate__delay-1s">We'll get back to you as soon as possible</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-7 animate__animated animate__fadeInLeft">
                    <div class="contact-form-card">
                        <div class="contact-form-header">
                            <h4 class="mb-0"><i class="fas fa-paper-plane me-2"></i> Contact Form</h4>
                        </div>
                        <div class="contact-form-body">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>
                            
                            <?php if (isset($success)): ?>
                                <div class="alert alert-success"><?= $success ?></div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <?php if (!$loggedIn): ?>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Your Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" required
                                               value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="email" name="email" required
                                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                           value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                                </div>
                                <?php else: ?>
                                    <?php
                                    // For logged-in users, get their details from session/database
                                    $user = getUserById($_SESSION['user_id']);
                                    ?>
                                    <input type="hidden" name="name" value="<?= htmlspecialchars($user['username']) ?>">
                                    <input type="hidden" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                                    <input type="hidden" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required
                                           value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '' ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="message" class="form-label">Your Message *</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
                                </div>
                                
                                <div class="text-end">
                                    <button type="submit" name="submit_message" class="btn btn-submit">
                                        <i class="fas fa-paper-plane me-1"></i> Send Message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-5 animate__animated animate__fadeInRight">
                    <div class="contact-info-card">
                        <div class="contact-info-header">
                            <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i> Contact Information</h4>
                        </div>
                        <div class="contact-info-body">
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="contact-text">
                                    <h5>Our Location</h5>
                                    <p>HRP6+4Q Mapusa, Goa, India</p>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <div class="contact-text">
                                    <h5>Call Us</h5>
                                    <p><a href="tel:+919834402736">+91 98340 2736</a></p>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="contact-text">
                                    <h5>Email Us</h5>
                                    <p><a href="mailto:info@carrental.com">info@carrental.com</a></p>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="contact-text">
                                    <h5>Working Hours</h5>
                                    <p>Monday - Saturday: 9:00 AM - 8:00 PM</p>
                                    <p>Sunday: 10:00 AM - 6:00 PM</p>
                                </div>
                            </div>
                            
                            <div class="map-container">
                                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3846.123456789012!2d73.81234567890123!3d15.612345678901234!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTXCsDM2JzQ0LjQiTiA3M8KwNDgnNDQuMyJF!5e0!3m2!1sen!2sin!4v1234567890123!5m2!1sen!2sin" 
                                        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5 bg-light">
        <div class="container py-5">
            <div class="section-header mb-5 text-center">
                <h2 class="section-title animate__animated animate__fadeInDown">Frequently Asked Questions</h2>
                <p class="section-subtitle animate__animated animate__fadeInDown animate__delay-1s">Find answers to common questions about our services</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item mb-3 border-0 rounded-3 overflow-hidden shadow-sm">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    What documents do I need to rent a car?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    You'll need a valid driver's license, proof of identity (Passport or Aadhaar Card), and a valid credit card in your name for the security deposit. International renters will need an International Driving Permit along with their native license.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item mb-3 border-0 rounded-3 overflow-hidden shadow-sm">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    What is your cancellation policy?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    You can cancel your reservation up to 24 hours before your scheduled pickup time for a full refund. Cancellations made less than 24 hours in advance will incur a fee equivalent to one day's rental rate.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item mb-3 border-0 rounded-3 overflow-hidden shadow-sm">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Do you offer long-term rentals?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, we offer special rates for rentals of 30 days or longer. Please contact our customer service team for customized long-term rental packages and discounted rates.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item mb-3 border-0 rounded-3 overflow-hidden shadow-sm">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    Is there a minimum age requirement?
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    The minimum age to rent a car is 21 years. Drivers under 25 may be subject to a young driver surcharge. Some premium vehicles may have higher minimum age requirements.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item mb-3 border-0 rounded-3 overflow-hidden shadow-sm">
                            <h2 class="accordion-header" id="headingFive">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                    What payment methods do you accept?
                                </button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We accept all major credit cards (Visa, MasterCard, American Express), debit cards, and digital payments through UPI, Paytm, and Google Pay. Cash payments are also accepted at our physical locations.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
    <script>
        new WOW().init();
    </script>
    <script src="assets/js/script.js"></script>
</body>
</html>