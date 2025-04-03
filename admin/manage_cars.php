<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Handle car deletion
if (isset($_GET['delete'])) {
    $car_id = intval($_GET['delete']);
    $conn->query("DELETE FROM cars WHERE id = $car_id");
    $_SESSION['message'] = "Car deleted successfully";
    header("Location: manage_cars.php");
    exit();
}

// Get all cars
$cars = $conn->query("SELECT * FROM cars ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cars - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="col-md-10 ms-sm-auto p-4">
        <div class="d-flex justify-content-between mb-4">
            <h2>Manage Cars</h2>
            <a href="add_car.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Car
            </a>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Make & Model</th>
                                <th>Year</th>
                                <th>Price/Day</th>
                                <th>Fuel Type</th>
                                <th>Available</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cars as $car): ?>
                            <tr>
                                <td><?= $car['id'] ?></td>
                                <td>
                                    <img src="../assets/images/<?= $car['image_path'] ?>" width="80" class="img-thumbnail">
                                </td>
                                <td>
                                    <strong><?= $car['make'] ?></strong><br>
                                    <?= $car['model'] ?>
                                </td>
                                <td><?= $car['year'] ?></td>
                                <td>₹<?= number_format($car['price_per_day'], 2) ?></td>
                                <td><?= ucfirst($car['fuel_type']) ?></td>
                                <td>
                                    <?php if ($car['available']): ?>
                                        <span class="badge bg-success">Yes</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_car.php?id=<?= $car['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="manage_cars.php?delete=<?= $car['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
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