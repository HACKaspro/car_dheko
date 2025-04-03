<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Get all contact messages
$messages = [];
try {
    $query = "
        SELECT cm.*, u.username 
        FROM contact_messages cm
        LEFT JOIN users u ON cm.user_id = u.id
        ORDER BY cm.created_at DESC
    ";
    $result = $conn->query($query);
    
    if ($result) {
        $messages = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
    }
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Update status if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_id']) && isset($_POST['status'])) {
    try {
        $stmt = $conn->prepare("
            UPDATE contact_messages 
            SET status = ? 
            WHERE id = ?
        ");
        $stmt->bind_param("si", $_POST['status'], $_POST['message_id']);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['message'] = "Message status updated successfully";
        header('Location: admin_contact.php');
        exit();
    } catch (Exception $e) {
        die("Database error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../css/admin-style.css" rel="stylesheet">
    <style>
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            min-width: 70px;
            text-align: center;
        }
        .status-unread {
            background-color: #d1e7ff;
            color: #084298;
        }
        .status-read {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .status-replied {
            background-color: #d1fae5;
            color: #065f46;
        }
        .message-preview {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            color: #4361ee;
        }
        .message-preview:hover {
            text-decoration: underline;
        }
        .action-btn {
            padding: 5px 10px;
            font-size: 13px;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <div class="col-md-10 ms-sm-auto p-4 bg-light">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0"><i class="fas fa-envelope me-2"></i> Contact Messages</h2>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $_SESSION['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>From</th>
                                        <th>Subject</th>
                                        <th>Message Preview</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($messages as $message): ?>
                                    <tr>
                                        <td><?= $message['id'] ?></td>
                                        <td><?= date('M j, Y h:i A', strtotime($message['created_at'])) ?></td>
                                        <td>
                                            <?php if ($message['username']): ?>
                                                <a href="mailto:<?= htmlspecialchars($message['email']) ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($message['username']) ?>
                                                </a>
                                            <?php else: ?>
                                                <a href="mailto:<?= htmlspecialchars($message['email']) ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($message['name']) ?>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($message['subject']) ?></td>
                                        <td class="message-preview" onclick="showMessageModal(
                                            '<?= addslashes($message['name']) ?>',
                                            '<?= addslashes($message['email']) ?>',
                                            '<?= addslashes($message['phone'] ?? 'N/A') ?>',
                                            '<?= addslashes($message['subject']) ?>',
                                            '<?= addslashes($message['message']) ?>',
                                            '<?= date('M j, Y h:i A', strtotime($message['created_at'])) ?>'
                                        )">
                                            <?= htmlspecialchars(substr($message['message'], 0, 50)) ?>
                                            <?php if (strlen($message['message']) > 50): ?>[...]<?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?= $message['status'] ?>">
                                                <?= ucfirst($message['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="post" class="d-inline me-2">
                                                <input type="hidden" name="message_id" value="<?= $message['id'] ?>">
                                                <select name="status" class="form-select form-select-sm d-inline-block" style="width: 120px;" onchange="this.form.submit()">
                                                    <option value="unread" <?= $message['status'] === 'unread' ? 'selected' : '' ?>>Unread</option>
                                                    <option value="read" <?= $message['status'] === 'read' ? 'selected' : '' ?>>Read</option>
                                                    <option value="replied" <?= $message['status'] === 'replied' ? 'selected' : '' ?>>Replied</option>
                                                </select>
                                            </form>
                                            <a href="mailto:<?= htmlspecialchars($message['email']) ?>?subject=Re: <?= rawurlencode($message['subject']) ?>" 
                                               class="btn btn-sm btn-outline-primary action-btn" title="Reply">
                                                <i class="fas fa-reply"></i>
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
        </div>
    </div>

    <!-- Message Details Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Message Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="bg-light p-3 rounded mb-3">
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <strong>From:</strong> <span id="modalFrom"></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Email:</strong> <span id="modalEmail"></span>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <strong>Phone:</strong> <span id="modalPhone"></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Date:</strong> <span id="modalDate"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <strong>Subject:</strong> <span id="modalSubject"></span>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Message Content:</h6>
                        </div>
                        <div class="card-body">
                            <div class="p-3 bg-white rounded" id="modalMessage"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" class="btn btn-primary" id="modalReplyBtn">
                        <i class="fas fa-reply me-1"></i> Reply
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showMessageModal(name, email, phone, subject, message, date) {
            document.getElementById('modalFrom').textContent = name;
            document.getElementById('modalEmail').textContent = email;
            document.getElementById('modalPhone').textContent = phone;
            document.getElementById('modalSubject').textContent = subject;
            document.getElementById('modalMessage').textContent = message;
            document.getElementById('modalDate').textContent = date;
            document.getElementById('modalReplyBtn').setAttribute('href', 
                `mailto:${email}?subject=Re: ${encodeURIComponent(subject)}`);
            
            // Show the modal
            var modal = new bootstrap.Modal(document.getElementById('messageModal'));
            modal.show();
        }
    </script>
</body>
</html>