<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    // Don't allow deleting yourself
    if ($user_id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id = $user_id");
        $_SESSION['message'] = "User deleted successfully";
    } else {
        $_SESSION['error'] = "You cannot delete your own account";
    }
    header("Location: manage_users.php");
    exit();
}

// Handle admin status toggle
if (isset($_GET['toggle_admin'])) {
    $user_id = intval($_GET['toggle_admin']);
    // Don't allow changing your own admin status
    if ($user_id != $_SESSION['user_id']) {
        $current = $conn->query("SELECT is_admin FROM users WHERE id = $user_id")->fetch_assoc()['is_admin'];
        $new_status = $current ? 0 : 1;
        $conn->query("UPDATE users SET is_admin = $new_status WHERE id = $user_id");
        $_SESSION['message'] = "User admin status updated";
    } else {
        $_SESSION['error'] = "You cannot change your own admin status";
    }
    header("Location: manage_users.php");
    exit();
}

// Get all users
$users = $conn->query("SELECT id, username, email, full_name, phone, created_at, is_admin FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="col-md-10 ms-sm-auto p-4">
        <h2>Manage Users</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Registered</th>
                                <th>Admin</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= $user['username'] ?></td>
                                <td><?= $user['full_name'] ?></td>
                                <td><?= $user['email'] ?></td>
                                <td><?= $user['phone'] ?></td>
                                <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <span class="badge bg-primary">You</span>
                                    <?php else: ?>
                                        <a href="manage_users.php?toggle_admin=<?= $user['id'] ?>" class="btn btn-sm btn-<?= $user['is_admin'] ? 'success' : 'secondary' ?>">
                                            <?= $user['is_admin'] ? 'Yes' : 'No' ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="manage_users.php?delete=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
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