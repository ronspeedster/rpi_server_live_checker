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
$need_password_change = $_SESSION['need_password_change'] ?? 0;

// Get database statistics
$pdo = db();

// Total devices
$stmt = $pdo->query("SELECT COUNT(*) as total FROM devices");
$total_devices = $stmt->fetchColumn();

// Get latest status for each device
$stmt = $pdo->query("
    SELECT 
        d.id,
        d.name,
        d.is_active,
        (SELECT pl.status 
         FROM ping_logs pl 
         WHERE pl.device_id = d.id 
         ORDER BY pl.checked_at DESC 
         LIMIT 1) as last_status,
        (SELECT pl.checked_at 
         FROM ping_logs pl 
         WHERE pl.device_id = d.id 
         ORDER BY pl.checked_at DESC 
         LIMIT 1) as last_checked
    FROM devices d
    WHERE d.is_active = 1
");
$devices_status = $stmt->fetchAll(PDO::FETCH_ASSOC);

$online_count = 0;
$offline_count = 0;
$never_checked = 0;

foreach ($devices_status as $device) {
    if ($device['last_status'] === 'ONLINE') {
        $online_count++;
    } elseif ($device['last_status'] === 'OFFLINE') {
        $offline_count++;
    } else {
        $never_checked++;
    }
}

// Count recent alerts (devices that went offline in last 24 hours)
$stmt = $pdo->query("
    SELECT COUNT(DISTINCT device_id) as alert_count
    FROM ping_logs
    WHERE status = 'OFFLINE'
    AND checked_at >= datetime('now', '-24 hours')
");
$alert_count = $stmt->fetchColumn();

// Page settings
$page_title = 'Network Monitor - Dashboard';
$active_page = 'dashboard';

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

                    <?php require_once __DIR__ . '/includes/alerts.php'; ?>

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Devices Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <a href="devices/" class="text-decoration-none">
                                <div class="card border-left-primary shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Total Devices</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_devices; ?></div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-server fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Online Devices Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <a href="monitor/history.php?status=ONLINE" class="text-decoration-none">
                                <div class="card border-left-success shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                    Online</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $online_count; ?></div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Offline Devices Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <a href="monitor/history.php?status=OFFLINE" class="text-decoration-none">
                                <div class="card border-left-danger shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                    Offline</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $offline_count; ?></div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Alerts Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <a href="monitor/alerts.php" class="text-decoration-none">
                                <div class="card border-left-warning shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                    Alerts (24h)</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $alert_count; ?></div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-bell fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Welcome</h6>
                                </div>
                                <div class="card-body">
                                    <p>Welcome to the Network Monitor Dashboard. This system helps you monitor your network devices and track their online/offline status.</p>
                                    <p class="mb-0"><strong>Next steps:</strong></p>
                                    <ul>
                                        <?php if ($need_password_change): ?>
                                            <li><strong>Change your password</strong> - You're using the default password!</li>
                                        <?php endif; ?>
                                        <li>Add devices to monitor</li>
                                        <li>Configure ping intervals</li>
                                        <li>Set up alert notifications</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
