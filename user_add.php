<?php
require_once __DIR__ . '/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username'] ?? '');
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'admin';
    $need_password_change = isset($_POST['need_password_change']) ? 1 : 0;

    if (!$new_username || !$new_password || !$confirm_password) {
        $error = 'All fields are required';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        try {
            $pdo = db();
            
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->execute([':username' => $new_username]);
            if ($stmt->fetch()) {
                $error = 'Username already exists';
            } else {
                // Insert new user
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, need_password_change) VALUES (:username, :password_hash, :role, :need_password_change)");
                $stmt->execute([
                    ':username' => $new_username,
                    ':password_hash' => $password_hash,
                    ':role' => $role,
                    ':need_password_change' => $need_password_change
                ]);

                $_SESSION['user_added'] = 'User "' . $new_username . '" has been created successfully';
                header('Location: users.php');
                exit;
            }
        } catch (Exception $e) {
            $error = 'Failed to create user: ' . $e->getMessage();
        }
    }
}

// Page settings
$page_title = 'Network Monitor - Add User';
$active_page = 'users';

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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Add New User</h1>
                        <a href="users.php" class="btn btn-secondary btn-icon-split">
                            <span class="icon text-white-50">
                                <i class="fas fa-arrow-left"></i>
                            </span>
                            <span class="text">Back to Users</span>
                        </a>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">

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
                                    <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="user_add.php">
                                        <div class="form-group">
                                            <label for="username">Username <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="username" 
                                                   name="username" required 
                                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                                        </div>

                                        <div class="form-group">
                                            <label for="password">Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="password" 
                                                   name="password" required minlength="6">
                                            <small class="form-text text-muted">Must be at least 6 characters long</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="confirm_password">Confirm Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="confirm_password" 
                                                   name="confirm_password" required minlength="6">
                                        </div>

                                        <div class="form-group">
                                            <label for="role">Role <span class="text-danger">*</span></label>
                                            <select class="form-control" id="role" name="role" required>
                                                <option value="admin" <?php echo (($_POST['role'] ?? 'admin') === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                                <option value="user" <?php echo (($_POST['role'] ?? '') === 'user') ? 'selected' : ''; ?>>User</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="need_password_change" 
                                                       name="need_password_change" <?php echo isset($_POST['need_password_change']) ? 'checked' : ''; ?>>
                                                <label class="custom-control-label" for="need_password_change">
                                                    Require password change on first login
                                                </label>
                                            </div>
                                        </div>

                                        <hr>

                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Create User
                                        </button>
                                        <a href="users.php" class="btn btn-secondary">
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
