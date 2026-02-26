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

$success_msg = '';
$error_msg = '';

// Paths
$script_path = __DIR__ . '/scripts/ping_devices.py';
$pid_file = __DIR__ . '/data/monitor.pid';
$log_file = __DIR__ . '/data/monitor.log';

// Check if monitoring is running
function is_monitoring_running() {
    global $pid_file;
    
    if (!file_exists($pid_file)) {
        return false;
    }
    
    // Read PID file
    $pid_content = trim(file_get_contents($pid_file));
    
    if (empty($pid_content)) {
        return false;
    }
    
    // Check if process is actually running (macOS/Linux)
    if (PHP_OS_FAMILY === 'Windows') {
        // Windows: check with tasklist
        exec("tasklist /FI \"IMAGENAME eq python*\" 2>NUL", $output);
        foreach ($output as $line) {
            if (stripos($line, 'python') !== false) {
                return true;
            }
        }
    } else {
        // Unix-like: check with ps
        exec("ps aux | grep 'ping_devices.py' | grep -v grep", $output);
        return !empty($output);
    }
    
    return false;
}

// Get monitoring status
$is_running = is_monitoring_running();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'start') {
        $interval = intval($_POST['interval'] ?? 60);
        
        if ($interval < 5) {
            $error_msg = 'Interval must be at least 5 seconds';
        } elseif ($is_running) {
            $error_msg = 'Monitoring is already running';
        } else {
            // Start monitoring in background
            $python = 'python3'; // or 'python' on Windows
            
            if (PHP_OS_FAMILY === 'Windows') {
                $command = "start /B $python \"$script_path\" --continuous --interval $interval --log";
            } else {
                $command = "$python \"$script_path\" --continuous --interval $interval --log > /dev/null 2>&1 &";
            }
            
            exec($command);
            
            // Wait a moment to check if it started
            sleep(1);
            
            if (is_monitoring_running()) {
                $success_msg = "Monitoring service started successfully with {$interval}s interval";
                $is_running = true;
            } else {
                $error_msg = 'Failed to start monitoring service';
            }
        }
    } elseif ($action === 'stop') {
        if (!$is_running) {
            $error_msg = 'Monitoring is not running';
        } else {
            // Stop monitoring by killing the process
            if (PHP_OS_FAMILY === 'Windows') {
                exec('taskkill /F /IM python.exe 2>NUL');
            } else {
                exec("pkill -f 'ping_devices.py'");
            }
            
            // Remove PID file
            if (file_exists($pid_file)) {
                unlink($pid_file);
            }
            
            // Wait a moment
            sleep(1);
            
            $success_msg = 'Monitoring service stopped successfully';
            $is_running = false;
        }
    }
}

// Get log file info
$log_size = 0;
$log_exists = false;
if (file_exists($log_file)) {
    $log_exists = true;
    $log_size = filesize($log_file);
}

// Page settings
$page_title = 'Network Monitor - Monitor Control';
$active_page = 'monitor_control';

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
                        <h1 class="h3 mb-0 text-gray-800">Monitor Control</h1>
                    </div>

                    <div class="row">
                        <!-- Status Card -->
                        <div class="col-xl-6 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Monitoring Status</h6>
                                </div>
                                <div class="card-body">
                                    <?php if ($is_running): ?>
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle"></i> <strong>Service Running</strong>
                                            <p class="mb-0 mt-2">The monitoring service is currently active and pinging devices at regular intervals.</p>
                                        </div>
                                        
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to stop the monitoring service?');">
                                            <input type="hidden" name="action" value="stop">
                                            <button type="submit" class="btn btn-danger btn-lg">
                                                <i class="fas fa-stop-circle"></i> Stop Monitoring
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> <strong>Service Stopped</strong>
                                            <p class="mb-0 mt-2">The monitoring service is not running. Devices are not being pinged.</p>
                                        </div>
                                        
                                        <form method="POST">
                                            <input type="hidden" name="action" value="start">
                                            
                                            <div class="form-group">
                                                <label for="interval">Ping Interval (seconds)</label>
                                                <input type="number" class="form-control" id="interval" name="interval" 
                                                       value="60" min="5" max="3600" required>
                                                <small class="form-text text-muted">
                                                    How often to ping all devices (minimum: 5 seconds)
                                                </small>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="fas fa-play-circle"></i> Start Monitoring
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Info Card -->
                        <div class="col-xl-6 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <a href="monitor_logs.php" class="btn btn-info btn-block mb-3">
                                        <i class="fas fa-stream"></i> View Live Logs
                                    </a>
                                    
                                    <a href="logs.php" class="btn btn-secondary btn-block mb-3">
                                        <i class="fas fa-list"></i> View Historical Logs
                                    </a>
                                    
                                    <a href="devices.php" class="btn btn-secondary btn-block">
                                        <i class="fas fa-server"></i> Manage Devices
                                    </a>
                                    
                                    <hr>
                                    
                                    <h6 class="font-weight-bold">Log File Info</h6>
                                    <?php if ($log_exists): ?>
                                        <p class="mb-2">
                                            <strong>Status:</strong> <span class="badge badge-success">Exists</span><br>
                                            <strong>Size:</strong> <?php echo number_format($log_size / 1024, 2); ?> KB
                                        </p>
                                        <a href="<?php echo 'data/monitor.log'; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="fas fa-download"></i> Download Log
                                        </a>
                                    <?php else: ?>
                                        <p class="text-muted">No log file available</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">How It Works</h6>
                                </div>
                                <div class="card-body">
                                    <ol>
                                        <li><strong>Add Devices:</strong> Go to the Devices page and add network devices to monitor.</li>
                                        <li><strong>Start Monitoring:</strong> Click "Start Monitoring" above and set your desired ping interval.</li>
                                        <li><strong>View Live Logs:</strong> Click "View Live Logs" to see real-time ping activity as it happens.</li>
                                        <li><strong>Check History:</strong> Go to "View Historical Logs" to see all past ping results with filters.</li>
                                        <li><strong>Stop When Done:</strong> Click "Stop Monitoring" to stop the service.</li>
                                    </ol>
                                    
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle"></i> 
                                        <strong>Note:</strong> The monitoring service runs in the background and will continue even if you close this page. 
                                        It will stop if you restart your web server or manually stop it from this page.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
