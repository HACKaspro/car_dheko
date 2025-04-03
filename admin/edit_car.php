<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_cars.php");
    exit();
}

$car_id = intval($_GET['id']);
$car = $conn->query("SELECT * FROM cars WHERE id = $car_id")->fetch_assoc();

if (!$car) {
    header("Location: manage_cars.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $make = $conn->real_escape_string($_POST['make']);
    $model = $conn->real_escape_string($_POST['model']);
    $year = intval($_POST['year']);
    $price_per_day = floatval($_POST['price_per_day']);
    $fuel_type = $conn->real_escape_string($_POST['fuel_type']);
    $seats = intval($_POST['seats']);
    $mileage = $conn->real_escape_string($_POST['mileage']);
    $description = $conn->real_escape_string($_POST['description']);
    $available = isset($_POST['available']) ? 1 : 0;
    $image_path = $car['image_path'];

    // Handle image upload if new image is provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../assets/images/";
        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_image_path = uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $new_image_path;
        
        // Check if image file is actual image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            $_SESSION['error'] = "File is not an image.";
            header("Location: edit_car.php?id=$car_id");
            exit();
        }
        
        // Check file size (5MB max)
        if ($_FILES["image"]["size"] > 5000000) {
            $_SESSION['error'] = "Sorry, your file is too large.";
            header("Location: edit_car.php?id=$car_id");
            exit();
        }
        
        // Allow certain file formats
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $_SESSION['error'] = "Only JPG, JPEG, PNG & GIF files are allowed.";
            header("Location: edit_car.php?id=$car_id");
            exit();
        }
        
        // Upload file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Delete old image if it's not the default
            if ($image_path !== 'default-car.jpg') {
                @unlink($target_dir . $image_path);
            }
            $image_path = $new_image_path;
        } else {
            $_SESSION['error'] = "Sorry, there was an error uploading your file.";
            header("Location: edit_car.php?id=$car_id");
            exit();
        }
    }

    $sql = "UPDATE cars SET 
            make = '$make', 
            model = '$model', 
            year = $year, 
            price_per_day = $price_per_day, 
            fuel_type = '$fuel_type', 
            seats = $seats, 
            mileage = '$mileage', 
            description = '$description', 
            available = $available, 
            image_path = '$image_path' 
            WHERE id = $car_id";

    if ($conn->query($sql)) {
        $_SESSION['message'] = "Car updated successfully";
        header("Location: manage_cars.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating car: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="col-md-10 ms-sm-auto p-4">
        <h2>Edit Car</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Make</label>
                            <input type="text" name="make" class="form-control" value="<?= $car['make'] ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control" value="<?= $car['model'] ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Year</label>
                            <input type="number" name="year" class="form-control" min="2000" max="<?= date('Y') + 1 ?>" value="<?= $car['year'] ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Price/Day (₹)</label>
                            <input type="number" name="price_per_day" class="form-control" min="0" step="0.01" value="<?= $car['price_per_day'] ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fuel Type</label>
                            <select name="fuel_type" class="form-select" required>
                                <option value="petrol" <?= $car['fuel_type'] === 'petrol' ? 'selected' : '' ?>>Petrol</option>
                                <option value="diesel" <?= $car['fuel_type'] === 'diesel' ? 'selected' : '' ?>>Diesel</option>
                                <option value="electric" <?= $car['fuel_type'] === 'electric' ? 'selected' : '' ?>>Electric</option>
                                <option value="hybrid" <?= $car['fuel_type'] === 'hybrid' ? 'selected' : '' ?>>Hybrid</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Seats</label>
                            <input type="number" name="seats" class="form-control" min="2" max="10" value="<?= $car['seats'] ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Mileage</label>
                            <input type="text" name="mileage" class="form-control" value="<?= $car['mileage'] ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Availability</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="available" id="available" <?= $car['available'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="available">Available for booking</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?= $car['description'] ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Image</label><br>
                        <img src="../assets/images/<?= $car['image_path'] ?>" width="200" class="img-thumbnail mb-2">
                        <label class="form-label">Change Image</label>
                        <input type="file" name="image" class="form-control">
                        <small class="text-muted">Leave blank to keep current image</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Car</button>
                    <a href="manage_cars.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>