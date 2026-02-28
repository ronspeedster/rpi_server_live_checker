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

if (isset($_SESSION['device_added'])) {
    $success_msg = $_SESSION['device_added'];
    unset($_SESSION['device_added']);
}
if (isset($_SESSION['device_updated'])) {
    $success_msg = $_SESSION['device_updated'];
    unset($_SESSION['device_updated']);
}
if (isset($_SESSION['device_deleted'])) {
    $success_msg = $_SESSION['device_deleted'];
    unset($_SESSION['device_deleted']);
}
if (isset($_SESSION['device_error'])) {
    $error_msg = $_SESSION['device_error'];
    unset($_SESSION['device_error']);
}

// Fetch all devices
$pdo = db();
$stmt = $pdo->query("SELECT d.*, 
    u_email.first_name as email_user_first, u_email.last_name as email_user_last,
    u_sms.first_name as sms_user_first, u_sms.last_name as sms_user_last
    FROM devices d
    LEFT JOIN users u_email ON d.notify_email_user_id = u_email.id
    LEFT JOIN users u_sms ON d.notify_sms_user_id = u_sms.id
    ORDER BY name ASC");
$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Page settings
$page_title = 'Network Monitor - Devices';
$active_page = 'devices';
$page_styles = '
<link href="' . BASE_PATH . 'sb_admin_theme/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
';
$page_scripts = '
<script src="' . BASE_PATH . 'sb_admin_theme/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="' . BASE_PATH . 'sb_admin_theme/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    $("#devicesTable").DataTable();
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
                        <h1 class="h3 mb-0 text-gray-800">Devices</h1>
                        <a href="add.php" class="btn btn-primary btn-icon-split">
                            <span class="icon text-white-50">
                                <i class="fas fa-plus"></i>
                            </span>
                            <span class="text">Add Device</span>
                        </a>
                    </div>

                    <!-- DataTales -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Device List</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="devicesTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>IP Address</th>
                                            <th>Notifications</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($devices as $device): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($device['id']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($device['name']); ?></strong>
                                                <?php if ($device['notes']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($device['notes']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><code><?php echo htmlspecialchars($device['ip_address']); ?></code></td>
                                            <td>
                                                <?php if (($device['notify_email'] ?? 1)): ?>
                                                    <span class="badge badge-info" title="Email notifications enabled">
                                                        <i class="fas fa-envelope"></i>
                                                        <?php 
                                                        if ($device['notify_email_user_id']) {
                                                            echo htmlspecialchars($device['email_user_first'] . ' ' . $device['email_user_last']);
                                                        } else {
                                                            echo 'All';
                                                        }
                                                        ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (($device['notify_sms'] ?? 1)): ?>
                                                    <span class="badge badge-success" title="SMS notifications enabled">
                                                        <i class="fas fa-sms"></i>
                                                        <?php 
                                                        if ($device['notify_sms_user_id']) {
                                                            echo htmlspecialchars($device['sms_user_first'] . ' ' . $device['sms_user_last']);
                                                        } else {
                                                            echo 'All';
                                                        }
                                                        ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!($device['notify_email'] ?? 1) && !($device['notify_sms'] ?? 1)): ?>
                                                    <span class="text-muted">None</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($device['is_active']): ?>
                                                    <span class="badge badge-success"><i class="fas fa-check"></i> Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary"><i class="fas fa-pause"></i> Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="edit.php?id=<?php echo $device['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete.php?id=<?php echo $device['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this device?');">
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
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
