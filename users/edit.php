<?php
require_once __DIR__ . '/../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$username = $_SESSION['username'];
$first_name = $_SESSION['first_name'] ?? 'User';
$last_name = $_SESSION['last_name'] ?? '';
$success = '';
$error = '';
$user_id = intval($_GET['id'] ?? 0);

if (!$user_id) {
    header('Location: index.php');
    exit;
}

$pdo = db();

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['user_error'] = 'User not found';
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'admin';
    $need_password_change = isset($_POST['need_password_change']) ? 1 : 0;

    if (!$new_username || !$first_name || !$last_name) {
        $error = 'Username, first name, and last name are required';
    } elseif ($new_password && $new_password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif ($new_password && strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } elseif ($phone && !preg_match('/^\+?[1-9]\d{1,14}$/', $phone)) {
        $error = 'Invalid phone number. Please use international format with area code (e.g., +1234567890)';
    } else {
        try {
            // Check if username already exists (except for current user)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
            $stmt->execute([':username' => $new_username, ':id' => $user_id]);
            if ($stmt->fetch()) {
                $error = 'Username already exists';
            } else {
                // Update user
                if ($new_password) {
                    // Update with new password
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username = :username, password_hash = :password_hash, first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, role = :role, need_password_change = :need_password_change WHERE id = :id");
                    $stmt->execute([
                        ':username' => $new_username,
                        ':password_hash' => $password_hash,
                        ':first_name' => $first_name,
                        ':last_name' => $last_name,
                        ':email' => $email ?: null,
                        ':phone' => $phone ?: null,
                        ':role' => $role,
                        ':need_password_change' => $need_password_change,
                        ':id' => $user_id
                    ]);
                } else {
                    // Update without changing password
                    $stmt = $pdo->prepare("UPDATE users SET username = :username, first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, role = :role, need_password_change = :need_password_change WHERE id = :id");
                    $stmt->execute([
                        ':username' => $new_username,
                        ':first_name' => $first_name,
                        ':last_name' => $last_name,
                        ':email' => $email ?: null,
                        ':phone' => $phone ?: null,
                        ':role' => $role,
                        ':need_password_change' => $need_password_change,
                        ':id' => $user_id
                    ]);
                }

                // If editing current user, update session
                if ($user_id == $_SESSION['user_id']) {
                    $_SESSION['username'] = $new_username;
                    $_SESSION['first_name'] = $first_name;
                    $_SESSION['last_name'] = $last_name;
                    $_SESSION['need_password_change'] = $need_password_change;
                }

                $_SESSION['user_updated'] = 'User "' . $new_username . '" has been updated successfully';
                header('Location: index.php');
                exit;
            }
        } catch (Exception $e) {
            $error = 'Failed to update user: ' . $e->getMessage();
        }
    }
    
    // Update user array for display
    $user['username'] = $new_username;
    $user['first_name'] = $first_name;
    $user['last_name'] = $last_name;
    $user['email'] = $email;
    $user['role'] = $role;
    $user['need_password_change'] = $need_password_change;
}

// Page settings
$page_title = 'Network Monitor - Edit User';
$active_page = 'users';

require_once __DIR__ . '/../includes/header.php';
?>

    <!-- Page Wrapper -->
    <div id="wrapper">

        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Edit User</h1>
                        <a href="index.php" class="btn btn-secondary btn-icon-split">
                            <span class="icon text-white-50">
                                <i class="fas fa-arrow-left"></i>
                            </span>
                            <span class="text">Back to Users</span>
                        </a>
                    </div>

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
                                    <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="edit.php?id=<?php echo $user_id; ?>">
                                        <div class="form-group">
                                            <label for="username">Username <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="username" 
                                                   name="username" required 
                                                   value="<?php echo htmlspecialchars($user['username']); ?>">
                                        </div>

                                        <div class="form-group">
                                            <label for="first_name">First Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="first_name" 
                                                   name="first_name" required 
                                                   value="<?php echo htmlspecialchars($user['first_name']); ?>">
                                        </div>

                                        <div class="form-group">
                                            <label for="last_name">Last Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="last_name" 
                                                   name="last_name" required 
                                                   value="<?php echo htmlspecialchars($user['last_name']); ?>">
                                        </div>

                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" id="email" 
                                                   name="email" 
                                                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                            <small class="form-text text-muted">Optional - Used for notifications and password recovery</small>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number (SMS)</label>
                            <input type="tel" class="form-control" id="phone" 
                                   name="phone" 
                                   placeholder="+1234567890"
                                   pattern="\+?[1-9]\d{1,14}"
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            <small class="form-text text-muted">Optional - Must include country/area code (e.g., +1234567890) for SMS notifications</small>
                                                   name="password" minlength="6">
                                            <small class="form-text text-muted">Leave blank to keep current password. Must be at least 6 characters if changing.</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="confirm_password">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirm_password" 
                                                   name="confirm_password" minlength="6">
                                        </div>

                                        <div class="form-group">
                                            <label for="role">Role <span class="text-danger">*</span></label>
                                            <select class="form-control" id="role" name="role" required>
                                                <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                                <option value="user" <?php echo ($user['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="need_password_change" 
                                                       name="need_password_change" <?php echo $user['need_password_change'] ? 'checked' : ''; ?>>
                                                <label class="custom-control-label" for="need_password_change">
                                                    Require password change on next login
                                                </label>
                                            </div>
                                        </div>

                                        <hr>

                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update User
                                        </button>
                                        <a href="index.php" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    </form>
                                </div>
                            </div>

                        </div>

                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-info">User Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th>User ID:</th>
                                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Created At:</th>
                                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Password Status:</th>
                                            <td>
                                                <?php if ($user['need_password_change']): ?>
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-exclamation-triangle"></i> Needs Change
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Changed
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
