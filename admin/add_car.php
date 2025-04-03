<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $make = $conn->real_escape_string($_POST['make']);
    $model = $conn->real_escape_string($_POST['model']);
    $year = intval($_POST['year']);
    $price_per_day = floatval($_POST['price_per_day']);
    $fuel_type = $conn->real_escape_string($_POST['fuel_type']);
    $seats = intval($_POST['seats']);
    $mileage = $conn->real_escape_string($_POST['mileage']);
    $description = $conn->real_escape_string($_POST['description']);
    $available = isset($_POST['available']) ? 1 : 0;

    // Handle image upload
    $image_path = 'default-car.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../assets/images/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0755, true)) {
                $_SESSION['error'] = "Failed to create upload directory.";
                header("Location: add_car.php");
                exit();
            }
        }

        // Verify directory is writable
        if (!is_writable($target_dir)) {
            $_SESSION['error'] = "Upload directory is not writable.";
            header("Location: add_car.php");
            exit();
        }

        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $image_path = uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $image_path;
        
        // Check if image file is actual image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            $_SESSION['error'] = "File is not an image.";
            header("Location: add_car.php");
            exit();
        }
        
        // Check file size (5MB max)
        if ($_FILES["image"]["size"] > 5000000) {
            $_SESSION['error'] = "File is too large (maximum 5MB allowed).";
            header("Location: add_car.php");
            exit();
        }
        
        // Allow certain file formats
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowedTypes)) {
            $_SESSION['error'] = "Only JPG, JPEG, PNG & GIF files are allowed.";
            header("Location: add_car.php");
            exit();
        }
        
        // Upload file with error handling
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Get specific upload error
            $uploadError = "Unknown error occurred";
            switch ($_FILES['image']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $uploadError = "File exceeds upload_max_filesize in php.ini";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $uploadError = "File exceeds MAX_FILE_SIZE in form";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $uploadError = "File was only partially uploaded";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $uploadError = "No file was uploaded";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $uploadError = "Missing temporary folder";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $uploadError = "Failed to write file to disk";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $uploadError = "File upload stopped by extension";
                    break;
            }
            
            $_SESSION['error'] = "File upload failed: " . $uploadError;
            header("Location: add_car.php");
            exit();
        }
    }

    // Insert car data into database
    $sql = "INSERT INTO cars (make, model, year, price_per_day, fuel_type, seats, mileage, description, available, image_path) 
            VALUES ('$make', '$model', $year, $price_per_day, '$fuel_type', $seats, '$mileage', '$description', $available, '$image_path')";

    if ($conn->query($sql)) {
        $_SESSION['message'] = "Car added successfully";
        header("Location: manage_cars.php");
        exit();
    } else {
        $_SESSION['error'] = "Error adding car: " . $conn->error;
        header("Location: add_car.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Car - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .upload-help {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .file-input-label {
            display: block;
            padding: 0.375rem 0.75rem;
            background: #f8f9fa;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            cursor: pointer;
        }
        .file-input-label:hover {
            background: #e9ecef;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="col-md-10 ms-sm-auto p-4">
        <h2>Add New Car</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Make *</label>
                            <input type="text" name="make" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Model *</label>
                            <input type="text" name="model" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Year *</label>
                            <input type="number" name="year" class="form-control" min="2000" max="<?= date('Y') + 1 ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Price/Day (₹) *</label>
                            <input type="number" name="price_per_day" class="form-control" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fuel Type *</label>
                            <select name="fuel_type" class="form-select" required>
                                <option value="petrol">Petrol</option>
                                <option value="diesel">Diesel</option>
                                <option value="electric">Electric</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Seats *</label>
                            <input type="number" name="seats" class="form-control" min="2" max="10" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Mileage *</label>
                            <input type="text" name="mileage" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Availability</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="available" id="available" checked>
                                <label class="form-check-label" for="available">Available for booking</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Car Image</label>
                        <label for="imageUpload" class="file-input-label mb-2">
                            <i class="fas fa-upload me-2"></i>Choose file...
                        </label>
                        <input type="file" name="image" id="imageUpload" class="form-control d-none">
                        <div class="upload-help">
                            <small>Allowed formats: JPG, JPEG, PNG, GIF (Max 5MB)</small>
                        </div>
                        <div id="fileName" class="mt-2 small"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>Add Car
                    </button>
                    <a href="manage_cars.php" class="btn btn-secondary">
                        <i class="fas fa-times-circle me-2"></i>Cancel
                    </a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show selected file name
        document.getElementById('imageUpload').addEventListener('change', function(e) {
            const fileName = document.getElementById('fileName');
            if (this.files.length > 0) {
                fileName.textContent = 'Selected file: ' + this.files[0].name;
                fileName.style.color = '#198754';
            } else {
                fileName.textContent = '';
            }
        });

        // Better error display
        const alert = document.querySelector('.alert');
        if (alert) {
            setTimeout(() => {
                alert.classList.add('fade');
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        }
    </script>
</body>
</html>