
<?php
// Check if user is logged in (optional security measure)
if (!isset($_SESSION)) {
    session_start();
}
?>

<!-- Sidebar Structure -->
<div class="sidebar">
    <!-- Sidebar Brand/Header -->
    <div class="sidebar-brand bg-gradient-primary">
        <div class="d-flex align-items-center justify-content-center py-3">
            <i class="fas fa-car fa-2x text-white me-3"></i>
            <h4 class="text-white mb-0">Car Rental Admin</h4>
        </div>
    </div>

    <!-- Sidebar Navigation -->
    <ul class="sidebar-nav">
        <!-- Dashboard -->
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="index.php">
                <i class="fas fa-tachometer-alt me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Manage Cars -->
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manage_cars.php' ? 'active' : '' ?>" href="manage_cars.php">
                <i class="fas fa-car me-2"></i>
                <span>Manage Cars</span>
            </a>
        </li>

        <!-- Manage Bookings -->
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manage_bookings.php' ? 'active' : '' ?>" href="manage_bookings.php">
                <i class="fas fa-calendar-check me-2"></i>
                <span>Manage Bookings</span>
            </a>
        </li>

        <!-- Manage Users -->
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : '' ?>" href="manage_users.php">
                <i class="fas fa-users me-2"></i>
                <span>Manage Users</span>
            </a>
        </li>

        <!-- Contact Messages -->
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_contact.php' ? 'active' : '' ?>" href="admin_contact.php">
                <i class="fas fa-envelope me-2"></i>
                <span>Contact Messages</span>
            </a>
        </li>

        <!-- Divider -->
        <li class="nav-divider my-3"></li>

        <!-- Bottom-aligned items -->
        <div class="sidebar-bottom">
            <!-- Go to User Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="../dashboard.php">
                    <i class="fas fa-exchange-alt me-2"></i>
                    <span>Go to User Dashboard</span>
                </a>
            </li>

            <!-- Logout -->
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    <span>Logout</span>
                </a>
            </li>
        </div>
    </ul>
</div>

<!-- Sidebar CSS -->
<style>
    :root {
        --sidebar-width: 250px;
        --sidebar-dark: #1a1a2e;
        --sidebar-accent: #16213e;
        --primary-blue: #0f3460;
        --light-blue: #e94560;
    }
    
    /* Sidebar Base Styles */
    .sidebar {
        width: var(--sidebar-width);
        min-height: 100vh;
        background: var(--sidebar-dark);
        position: fixed;
        left: 0;
        top: 0;
        z-index: 1000;
        box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    
    /* Sidebar Brand/Header */
    .sidebar-brand {
        padding: 1.2rem;
        background: var(--sidebar-accent);
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    /* Navigation List */
    .sidebar-nav {
        padding: 0;
        list-style: none;
        margin-top: 1rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    /* Navigation Items */
    .nav-item {
        position: relative;
    }
    
    /* Navigation Links */
    .nav-link {
        color: rgba(255,255,255,0.8);
        padding: 0.75rem 1.5rem;
        display: flex;
        align-items: center;
        text-decoration: none;
        transition: all 0.3s;
        border-left: 3px solid transparent;
    }
    
    /* Active/Hover States */
    .nav-link:hover,
    .nav-link.active {
        color: white;
        background: rgba(255,255,255,0.05);
        border-left: 3px solid var(--light-blue);
    }
    
    /* Icons */
    .nav-link i {
        width: 24px;
        text-align: center;
        font-size: 1.1rem;
    }
    
    /* Divider */
    .nav-divider {
        border-top: 1px solid rgba(255,255,255,0.1);
        margin: 0 1.5rem;
    }
    
    /* Bottom-aligned items */
    .sidebar-bottom {
        margin-top: auto;
        padding-bottom: 1rem;
    }
    
    /* Main Content Adjustment */
    .main-content {
        margin-left: var(--sidebar-width);
        width: calc(100% - var(--sidebar-width));
        transition: all 0.3s;
    }
</style>
