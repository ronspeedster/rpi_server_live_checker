<?php
require_once __DIR__ . '/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$first_name = $_SESSION['first_name'] ?? 'User';
$last_name = $_SESSION['last_name'] ?? '';
$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($current_password && $new_password && $confirm_password) {
        if ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters long';
        } elseif ($new_password === 'admin') {
            $error = 'Please choose a more secure password than "admin"';
        } else {
            try {
                $pdo = db();
                
                // Verify current password
                $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
                $stmt->execute([':id' => $user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($current_password, $user['password_hash'])) {
                    // Update password
                    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = :hash, need_password_change = 0 WHERE id = :id");
                    $stmt->execute([':hash' => $new_hash, ':id' => $user_id]);

                    // Update session
                    $_SESSION['need_password_change'] = 0;

                    $success = 'Password changed successfully!';
                } else {
                    $error = 'Current password is incorrect';
                }
            } catch (Exception $e) {
                $error = 'Failed to change password: ' . $e->getMessage();
            }
        }
    } else {
        $error = 'Please fill in all fields';
    }
}

// Page settings
$page_title = 'Network Monitor - Change Password';
$active_page = 'change_password';

require_once __DIR__ . '/includes/header.php';
?>

    <!-- Page Wrapper -->
    <div id="wrapper">

        <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <?php require_once __DIR__ . '/includes/topbar.php'; ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-4 text-gray-800">Change Password</h1>

                    <div class="row">
                        <div class="col-lg-6">

                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong><i class="fas fa-check-circle"></i> Success!</strong> <?php echo htmlspecialchars($success); ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>

                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong><i class="fas fa-exclamation-triangle"></i> Error!</strong> <?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>

                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Change Your Password</h6>
                                </div>
                                <div class="card-body">
                                    <?php if ($_SESSION['need_password_change']): ?>
                                        <div class="alert alert-warning mb-4">
                                            <i class="fas fa-exclamation-triangle"></i> You are using the default password. Please change it for security reasons.
                                        </div>
                                    <?php endif; ?>

                                    <form method="POST" action="change_password.php">
                                        <div class="form-group">
                                            <label for="current_password">Current Password</label>
                                            <input type="password" class="form-control" id="current_password" 
                                                   name="current_password" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="new_password">New Password</label>
                                            <input type="password" class="form-control" id="new_password" 
                                                   name="new_password" required minlength="6">
                                            <small class="form-text text-muted">Must be at least 6 characters long</small>
                                        </div>
                                        <div class="form-group">
                                            <label for="confirm_password">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirm_password" 
                                                   name="confirm_password" required minlength="6">
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Change Password
                                        </button>
                                        <a href="dashboard.php" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
