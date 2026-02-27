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

$success_msg = '';
$error_msg = '';

// Paths (relative to project root since we're in monitor/ subfolder)
$script_path = __DIR__ . '/../scripts/ping_devices.py';
$pid_file = __DIR__ . '/../data/monitor.pid';
$log_file = __DIR__ . '/../data/monitor.log';

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
    
    // Check if process is actually running
    if (PHP_OS_FAMILY === 'Windows') {
        // Windows: check with tasklist - use simpler, faster command
        $output = shell_exec('tasklist /FI "IMAGENAME eq python.exe" /FO CSV /NH 2>NUL');
        return $output !== null && stripos($output, 'python.exe') !== false;
    } else {
        // Unix-like: check with ps
        exec("ps aux | grep 'ping_devices.py' | grep -v grep", $output);
        return !empty($output);
    }
    
    return false;
}

// Get monitoring status (check after any potential redirects)
$is_running = is_monitoring_running();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // IMPORTANT: Close session immediately to prevent blocking other tabs
    session_write_close();
    
    if ($action === 'start') {
        $interval = intval($_POST['interval'] ?? 60);
        $result_msg = '';
        $result_type = 'error';
        
        if ($interval < 5) {
            $result_msg = 'Interval must be at least 5 seconds';
        } elseif ($is_running) {
            $result_msg = 'Monitoring is already running';
        } else {
            // Pre-flight checks
            $checks_passed = true;
            $error_details = [];
            
            // Check if script exists
            if (!file_exists($script_path)) {
                $checks_passed = false;
                $error_details[] = "Python script not found at: " . basename($script_path);
            }
            
            // Check if data directory exists and is writable
            $data_dir = __DIR__ . '/../data';
            if (!is_dir($data_dir)) {
                $checks_passed = false;
                $error_details[] = "Data directory does not exist";
            } elseif (!is_writable($data_dir)) {
                $checks_passed = false;
                $error_details[] = "Data directory is not writable";
            }
            
            // Check if Python is available
            $python = PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
            $python_check = PHP_OS_FAMILY === 'Windows' 
                ? shell_exec("where $python 2>NUL") 
                : shell_exec("which $python 2>/dev/null");
            
            if (empty($python_check)) {
                $checks_passed = false;
                $error_details[] = "Python is not installed or not in PATH (looking for '$python')";
            }
            
            // Check if database exists
            $db_path = __DIR__ . '/../data/network_monitor.sqlite';
            if (!file_exists($db_path)) {
                $checks_passed = false;
                $error_details[] = "Database not found. Please run database initialization first";
            }
            
            if (!$checks_passed) {
                $result_msg = 'Failed to start monitoring service: ' . implode('; ', $error_details);
            } else {
                // Start monitoring in background
                if (PHP_OS_FAMILY === 'Windows') {
                    // Use WScript to launch completely detached from PHP process
                    $vbs_content = "Set WshShell = CreateObject(\"WScript.Shell\")\n";
                    $vbs_content .= "WshShell.Run \"$python \"\"$script_path\"\" --continuous --interval $interval --log\", 0, False\n";
                    $vbs_file = $data_dir . '/start_monitor.vbs';
                    file_put_contents($vbs_file, $vbs_content);
                    exec("wscript //nologo \"$vbs_file\"");
                    // Clean up VBS file after a moment
                    @unlink($vbs_file);
                } else {
                    $command = "$python \"$script_path\" --continuous --interval $interval --log > /dev/null 2>&1 &";
                    exec($command);
                }
                
                // Give the process a brief moment to start
                usleep(300000); // 0.3 seconds
                
                if (is_monitoring_running()) {
                    $result_msg = "Monitoring service started successfully with {$interval}s interval";
                    $result_type = 'success';
                } else {
                    $result_msg = "The process may have started but stopped immediately. Check the log file for details.";
                }
            }
        }
        
        // Re-open session only to write the result
        session_start();
        if ($result_type === 'success') {
            $_SESSION['success_msg'] = $result_msg;
        } else {
            $_SESSION['error_msg'] = $result_msg;
        }
        
        // Redirect to prevent form resubmission
        header('Location: control.php');
        exit;
        
    } elseif ($action === 'stop') {
        $result_msg = '';
        $result_type = 'error';
        
        if (!$is_running) {
            $result_msg = 'Monitoring is not running';
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
            
            $result_msg = 'Monitoring service stopped successfully';
            $result_type = 'success';
        }
        
        // Re-open session only to write the result
        session_start();
        if ($result_type === 'success') {
            $_SESSION['success_msg'] = $result_msg;
        } else {
            $_SESSION['error_msg'] = $result_msg;
        }
        
        // Redirect to prevent form resubmission
        header('Location: control.php');
        exit;
    }
}

// Get messages from session (from redirect)
if (isset($_SESSION['success_msg'])) {
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}
if (isset($_SESSION['error_msg'])) {
    $error_msg = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}

// Refresh monitoring status (in case we just redirected after start/stop)
$is_running = is_monitoring_running();

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
                                    <a href="logs.php" class="btn btn-info btn-block mb-3">
                                        <i class="fas fa-stream"></i> View Live Logs
                                    </a>
                                    
                                    <a href="logs.php" class="btn btn-secondary btn-block mb-3">
                                        <i class="fas fa-list"></i> View Historical Logs
                                    </a>
                                    
                                    <a href="../devices/" class="btn btn-secondary btn-block">
                                        <i class="fas fa-server"></i> Manage Devices
                                    </a>
                                    
                                    <hr>
                                    
                                    <h6 class="font-weight-bold">Log File Info</h6>
                                    <?php if ($log_exists): ?>
                                        <p class="mb-2">
                                            <strong>Status:</strong> <span class="badge badge-success">Exists</span><br>
                                            <strong>Size:</strong> <?php echo number_format($log_size / 1024, 2); ?> KB
                                        </p>
                                        <a href="<?php echo BASE_PATH . 'data/monitor.log'; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
