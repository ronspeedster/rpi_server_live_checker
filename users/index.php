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
$need_password_change = $_SESSION['need_password_change'] ?? 0;

// Get session messages
$success_msg = '';
$error_msg = '';

if (isset($_SESSION['user_added'])) {
    $success_msg = $_SESSION['user_added'];
    unset($_SESSION['user_added']);
}
if (isset($_SESSION['user_updated'])) {
    $success_msg = $_SESSION['user_updated'];
    unset($_SESSION['user_updated']);
}
if (isset($_SESSION['user_deleted'])) {
    $success_msg = $_SESSION['user_deleted'];
    unset($_SESSION['user_deleted']);
}
if (isset($_SESSION['user_error'])) {
    $error_msg = $_SESSION['user_error'];
    unset($_SESSION['user_error']);
}

// Fetch all users
$pdo = db();
$stmt = $pdo->query("SELECT id, username, first_name, last_name, email, phone, role, need_password_change, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Page settings
$page_title = 'Network Monitor - User Management';
$active_page = 'users';
$page_styles = '<link href="' . BASE_PATH . 'sb_admin_theme/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">';
$page_scripts = '
    <!-- Page level plugins -->
    <script src="' . BASE_PATH . 'sb_admin_theme/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="' . BASE_PATH . 'sb_admin_theme/vendor/datatables/dataTables.bootstrap4.min.js"></script>
    
    <!-- Page level custom scripts -->
    <script>
        $(document).ready(function() {
            $(\'#dataTable\').DataTable();
        });
    </script>
';

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

                    <?php require_once __DIR__ . '/../includes/alerts.php'; ?>

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">User Management</h1>
                        <a href="add.php" class="btn btn-primary btn-icon-split">
                            <span class="icon text-white-50">
                                <i class="fas fa-plus"></i>
                            </span>
                            <span class="text">Add New User</span>
                        </a>
                    </div>

                    <!-- DataTales -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Users List</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Username</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                                <td>
                                                    <?php if ($user['email']): ?>
                                                        <i class="fas fa-envelope text-primary"></i>
                                                        <small><?php echo htmlspecialchars($user['email']); ?></small>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($user['phone']): ?>
                                                        <i class="fas fa-phone text-success"></i>
                                                        <small><?php echo htmlspecialchars($user['phone']); ?></small>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary">
                                                        <?php echo htmlspecialchars($user['role']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($user['need_password_change']): ?>
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-exclamation-triangle"></i> Default
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check"></i> Active
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="edit.php?id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-sm btn-info" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <a href="delete.php?id=<?php echo $user['id']; ?>" 
                                                           class="btn btn-sm btn-danger" 
                                                           onclick="return confirm('Are you sure you want to delete this user?');"
                                                           title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-secondary" disabled title="Cannot delete yourself">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
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
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
