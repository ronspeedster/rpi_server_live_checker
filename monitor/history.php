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

// Get filter parameters
$device_filter = $_GET['device'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Fetch ping logs with device information
$pdo = db();
$query = "
    SELECT 
        pl.id,
        pl.device_id,
        d.name as device_name,
        d.ip_address,
        pl.status,
        pl.rtt_ms,
        pl.message,
        pl.checked_at
    FROM ping_logs pl
    INNER JOIN devices d ON pl.device_id = d.id
    WHERE 1=1
";

$params = [];

if ($device_filter) {
    $query .= " AND pl.device_id = :device_id";
    $params[':device_id'] = $device_filter;
}

if ($status_filter) {
    $query .= " AND pl.status = :status";
    $params[':status'] = $status_filter;
}

$query .= " ORDER BY pl.checked_at DESC LIMIT 1000";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch devices for filter dropdown
$stmt = $pdo->query("SELECT id, name FROM devices ORDER BY name ASC");
$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Page settings
$page_title = 'Network Monitor - Ping Logs';
$active_page = 'logs';
$page_styles = '
<link href="' . BASE_PATH . 'sb_admin_theme/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
';
$page_scripts = '
<script src="' . BASE_PATH . 'sb_admin_theme/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="' . BASE_PATH . 'sb_admin_theme/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    $("#logsTable").DataTable({
        "order": [[6, "desc"]], // Sort by checked_at (timestamp) descending
        "pageLength": 50
    });
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
                        <h1 class="h3 mb-0 text-gray-800">Ping Logs</h1>
                    </div>

                    <!-- Filters -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="form-inline">
                                <div class="form-group mr-3 mb-2">
                                    <label for="device" class="mr-2">Device:</label>
                                    <select name="device" id="device" class="form-control">
                                        <option value="">All Devices</option>
                                        <?php foreach ($devices as $device): ?>
                                            <option value="<?php echo $device['id']; ?>" <?php echo ($device_filter == $device['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($device['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group mr-3 mb-2">
                                    <label for="status" class="mr-2">Status:</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="ONLINE" <?php echo ($status_filter === 'ONLINE') ? 'selected' : ''; ?>>Online</option>
                                        <option value="OFFLINE" <?php echo ($status_filter === 'OFFLINE') ? 'selected' : ''; ?>>Offline</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary mb-2">
                                    <i class="fas fa-filter"></i> Apply Filter
                                </button>

                                <?php if ($device_filter || $status_filter): ?>
                                    <a href="history.php" class="btn btn-secondary ml-2 mb-2">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <!-- Logs Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                Ping History (Last 1000 records)
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($logs)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No ping logs found. Run the ping script to start monitoring devices.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="logsTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Device</th>
                                                <th>IP Address</th>
                                                <th>Status</th>
                                                <th>RTT (ms)</th>
                                                <th>Message</th>
                                                <th>Checked At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($log['id']); ?></td>
                                                <td><?php echo htmlspecialchars($log['device_name']); ?></td>
                                                <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                                <td>
                                                    <?php if ($log['status'] === 'ONLINE'): ?>
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check-circle"></i> ONLINE
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-times-circle"></i> OFFLINE
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if ($log['rtt_ms'] !== null) {
                                                        echo htmlspecialchars($log['rtt_ms']);
                                                    } else {
                                                        echo '<span class="text-muted">—</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($log['message'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($log['checked_at']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
