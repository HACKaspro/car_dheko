<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

/* if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}*/

// Get stats
$cars_count = $conn->query("SELECT COUNT(*) as count FROM cars")->fetch_assoc()['count'];
$bookings_count = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$users_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$revenue = $conn->query("SELECT SUM(total_price) as total FROM bookings WHERE status = 'confirmed'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-dark: #1a1a2e;
            --sidebar-accent: #16213e;
            --primary-blue: #0f3460;
            --light-blue: #e94560;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--sidebar-dark);
            position: fixed;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-brand {
            padding: 1.5rem 1rem;
            background: var(--sidebar-accent);
            color: white;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-nav {
            padding: 0;
            list-style: none;
            flex: 1;
        }
        
        .sidebar-nav .nav-item {
            position: relative;
        }
        
        .sidebar-nav .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 0.8rem 1.5rem;
            display: flex;
            align-items: center;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.05);
            border-left: 3px solid var(--light-blue);
        }
        
        .sidebar-nav .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        .sidebar-bottom {
            padding: 1rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 2rem;
            transition: all 0.3s;
        }
        
        /* Stat Cards */
        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .stat-card .card-body {
            padding: 1.5rem;
        }
        
        .stat-card .card-title {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        
        .stat-card h2 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .border-left-primary {
            border-left: 4px solid var(--primary-blue);
        }
        
        .border-left-success {
            border-left: 4px solid var(--success-color);
        }
        
        .border-left-info {
            border-left: 4px solid var(--info-color);
        }
        
        .border-left-warning {
            border-left: 4px solid var(--warning-color);
        }
        
        /* Table Styles */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 600;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background: var(--primary-blue);
            color: white;
            padding: 1rem;
            font-weight: 500;
            border: none;
        }
        
        .table tbody tr {
            transition: all 0.2s;
        }
        
        .table tbody tr:hover {
            background-color: rgba(15, 52, 96, 0.03);
        }
        
        /* Badges */
        .badge {
            font-weight: 500;
            padding: 0.5em 0.8em;
            border-radius: 50px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h4><i class="fas fa-car me-2"></i> Car Rental Admin</h4>
        </div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a class="nav-link active" href="index.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_cars.php">
                    <i class="fas fa-car"></i> Manage Cars
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_bookings.php">
                    <i class="fas fa-calendar-check"></i> Manage Bookings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_users.php">
                    <i class="fas fa-users"></i> Manage Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_contact.php">
                    <i class="fas fa-envelope"></i> Contact Messages
                </a>
            </li>
        </ul>

        <!-- Bottom-aligned items -->
        <div class="sidebar-bottom">
            <a href="../dashboard.php" class="btn btn-outline-light w-100 mb-2">
                <i class="fas fa-exchange-alt me-2"></i> User Dashboard
            </a>
            <a href="../logout.php" class="btn btn-outline-light w-100">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="mb-4 fw-bold">Admin Dashboard</h2>
        
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card stat-card border-left-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total Cars</h5>
                        <h2><?= $cars_count ?></h2>
                        <a href="manage_cars.php" class="btn btn-sm btn-primary">View Cars</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-left-success">
                    <div class="card-body">
                        <h5 class="card-title">Total Bookings</h5>
                        <h2><?= $bookings_count ?></h2>
                        <a href="manage_bookings.php" class="btn btn-sm btn-success">View Bookings</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-left-info">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <h2><?= $users_count ?></h2>
                        <a href="manage_users.php" class="btn btn-sm btn-info">View Users</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-left-warning">
                    <div class="card-body">
                        <h5 class="card-title">Total Revenue</h5>
                        <h2>₹<?= number_format($revenue ?: 0, 2) ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Bookings</h5>
                <a href="manage_bookings.php" class="btn btn-primary btn-sm">View All</a>
            </div>
            <div class="card-body">
                <?php
                $recent_bookings = $conn->query("
                    SELECT b.*, u.username, c.make, c.model 
                    FROM bookings b
                    JOIN users u ON b.user_id = u.id
                    JOIN cars c ON b.car_id = c.id
                    ORDER BY b.created_at DESC LIMIT 5
                ")->fetch_all(MYSQLI_ASSOC);
                ?>
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_bookings as $booking): ?>
                            <tr>
                                <td><?= $booking['id'] ?></td>
                                <td><?= $booking['username'] ?></td>
                                <td><?= $booking['make'] ?> <?= $booking['model'] ?></td>
                                <td>
                                    <?= date('M j', strtotime($booking['start_date'])) ?> - 
                                    <?= date('M j', strtotime($booking['end_date'])) ?>
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