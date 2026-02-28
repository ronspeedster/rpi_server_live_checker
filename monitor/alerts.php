<?php
/**
 * Alert Management Page
 * View and action device alerts
 */

require_once __DIR__ . '/../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$page_title = "Alert Management";
$active_page = "alerts";
$db = db();

// Check if alerts table exists
$tableExists = false;
try {
    $db->query("SELECT 1 FROM alerts LIMIT 1");
    $tableExists = true;
} catch (PDOException $e) {
    // Table doesn't exist yet
}

// Handle alert action
if ($tableExists && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_alert'])) {
    $alert_id = (int)$_POST['alert_id'];
    $notes = $_POST['notes'] ?? '';
    $user_id = $_SESSION['user_id'];
    
    try {
        $stmt = $db->prepare("
            UPDATE alerts 
            SET status = 'resolved', 
                actioned_at = CURRENT_TIMESTAMP,
                actioned_by = ?,
                notes = ?
            WHERE id = ?
        ");
        
        $stmt->execute([$user_id, $notes, $alert_id]);
        
        $_SESSION['success_message'] = 'Alert marked as resolved successfully!';
        header('Location: alerts.php');
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Failed to action alert: ' . $e->getMessage();
    }
}

// Fetch alerts
$alerts = [];
$activeCount = 0;
$filter = $_GET['filter'] ?? 'active';

if ($tableExists) {
    $whereClause = $filter === 'active' ? "WHERE a.status = 'active'" : "";

    $stmt = $db->prepare("
        SELECT 
            a.*,
            d.name as device_name,
            d.ip_address as device_ip,
            u.username as actioned_by_name
        FROM alerts a
        LEFT JOIN devices d ON a.device_id = d.id
        LEFT JOIN users u ON a.actioned_by = u.id
        $whereClause
        ORDER BY 
            CASE WHEN a.status = 'active' THEN 0 ELSE 1 END,
            a.first_detected_at DESC
    ");

    $stmt->execute();
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count active alerts
    $activeCount = $db->query("SELECT COUNT(*) FROM alerts WHERE status = 'active'")->fetchColumn();
}

include __DIR__ . '/../includes/header.php';
?>

<style>
    /* Dark mode styles for alerts page */
    body.dark-mode .table {
        color: #e0e0e0;
    }
    
    body.dark-mode .table thead th {
        background-color: #252525;
        border-color: #404040;
        color: #e0e0e0;
    }
    
    body.dark-mode .table-bordered {
        border-color: #404040;
    }
    
    body.dark-mode .table-bordered td,
    body.dark-mode .table-bordered th {
        border-color: #404040;
    }
    
    body.dark-mode .table-danger {
        background-color: #2d2d2d !important;
    }
    
    body.dark-mode .table-danger td {
        color: #e0e0e0;
    }
    
    body.dark-mode code {
        background-color: #3a3a3a;
        color: #ffc107;
        padding: 2px 6px;
        border-radius: 3px;
    }
    
    body.dark-mode .text-muted {
        color: #888 !important;
    }
    
    body.dark-mode .modal-content {
        background-color: #2d2d2d;
        border-color: #404040;
    }
    
    body.dark-mode .modal-header {
        background-color: #252525;
        border-bottom-color: #404040;
    }
    
    body.dark-mode .modal-title {
        color: #e0e0e0;
    }
    
    body.dark-mode .modal-body {
        color: #e0e0e0;
    }
    
    body.dark-mode .modal-footer {
        border-top-color: #404040;
    }
    
    body.dark-mode .close {
        color: #e0e0e0;
        opacity: 0.8;
    }
    
    body.dark-mode .close:hover {
        color: #fff;
        opacity: 1;
    }
    
    body.dark-mode .form-control {
        background-color: #3a3a3a;
        border-color: #505050;
        color: #e0e0e0;
    }
    
    body.dark-mode .form-control:focus {
        background-color: #3a3a3a;
        border-color: #4e73df;
        color: #e0e0e0;
    }
    
    body.dark-mode label {
        color: #e0e0e0;
    }
</style>

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

<?php if (!$tableExists): ?>
    <!-- Migration Required -->
    <div class="alert alert-warning">
        <h4><i class="fas fa-exclamation-triangle"></i> Alert System Not Initialized</h4>
        <p>The alerts table has not been created yet. Please run the migration script first:</p>
        <a href="../migrate_add_alerts.php" class="btn btn-primary">
            <i class="fas fa-database"></i> Run Migration
        </a>
    </div>
<?php else: ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-exclamation-triangle"></i> Alert Management
        <?php if ($activeCount > 0): ?>
            <span class="badge badge-danger"><?php echo $activeCount; ?> Active</span>
        <?php endif; ?>
    </h1>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        echo htmlspecialchars($_SESSION['success_message']);
        unset($_SESSION['success_message']);
        ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php 
        echo htmlspecialchars($_SESSION['error_message']);
        unset($_SESSION['error_message']);
        ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Filter -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filter Alerts</h6>
    </div>
    <div class="card-body">
        <a href="?filter=active" class="btn btn-<?php echo $filter === 'active' ? 'primary' : 'outline-primary'; ?> btn-sm">
            <i class="fas fa-exclamation-circle"></i> Active Alerts (<?php echo $activeCount; ?>)
        </a>
        <a href="?filter=all" class="btn btn-<?php echo $filter === 'all' ? 'primary' : 'outline-primary'; ?> btn-sm">
            <i class="fas fa-list"></i> All Alerts
        </a>
    </div>
</div>

<!-- Alerts Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <?php echo $filter === 'active' ? 'Active' : 'All'; ?> Alerts
        </h6>
    </div>
    <div class="card-body">
        <?php if (empty($alerts)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <?php echo $filter === 'active' ? 'No active alerts. All devices are running normally!' : 'No alerts found.'; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Device</th>
                            <th>IP Address</th>
                            <th>First Detected</th>
                            <th>Last Notified</th>
                            <th>Duration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alerts as $alert): ?>
                            <?php
                            $isActive = $alert['status'] === 'active';
                            $statusClass = $isActive ? 'danger' : 'success';
                            $statusIcon = $isActive ? 'exclamation-triangle' : 'check-circle';
                            
                            $firstDetected = new DateTime($alert['first_detected_at']);
                            $now = new DateTime();
                            $duration = $firstDetected->diff($now);
                            $durationStr = '';
                            
                            if ($duration->d > 0) {
                                $durationStr .= $duration->d . 'd ';
                            }
                            if ($duration->h > 0) {
                                $durationStr .= $duration->h . 'h ';
                            }
                            $durationStr .= $duration->i . 'm';
                            ?>
                            <tr>
                                <td>
                                    <span class="badge badge-<?php echo $statusClass; ?>">
                                        <i class="fas fa-<?php echo $statusIcon; ?>"></i>
                                        <?php echo strtoupper($alert['status']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($alert['device_name']); ?></td>
                                <td><code><?php echo htmlspecialchars($alert['device_ip']); ?></code></td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($alert['first_detected_at'])); ?></td>
                                <td>
                                    <?php if ($alert['last_notified_at']): ?>
                                        <?php echo date('Y-m-d H:i:s', strtotime($alert['last_notified_at'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Never</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isActive): ?>
                                        <strong><?php echo $durationStr; ?></strong>
                                    <?php else: ?>
                                        <?php echo $durationStr; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isActive): ?>
                                        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#actionModal<?php echo $alert['id']; ?>">
                                            <i class="fas fa-check"></i> Resolve
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            Resolved by <?php echo htmlspecialchars($alert['actioned_by_name'] ?? 'System'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            
                            <!-- Action Modal -->
                            <?php if ($isActive): ?>
                            <div class="modal fade" id="actionModal<?php echo $alert['id']; ?>" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Resolve Alert - <?php echo htmlspecialchars($alert['device_name']); ?></h5>
                                            <button type="button" class="close" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="action_alert" value="1">
                                                <input type="hidden" name="alert_id" value="<?php echo $alert['id']; ?>">
                                                
                                                <div class="alert alert-info">
                                                    <strong>Device:</strong> <?php echo htmlspecialchars($alert['device_name']); ?><br>
                                                    <strong>IP:</strong> <?php echo htmlspecialchars($alert['device_ip']); ?><br>
                                                    <strong>Offline Since:</strong> <?php echo $alert['first_detected_at']; ?><br>
                                                    <strong>Duration:</strong> <?php echo $durationStr; ?>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="notes<?php echo $alert['id']; ?>">Resolution Notes (Optional)</label>
                                                    <textarea class="form-control" id="notes<?php echo $alert['id']; ?>" name="notes" rows="3" placeholder="e.g., Rebooted server, Network cable replaced, etc."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-check"></i> Mark as Resolved
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Info Box -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-info-circle"></i> Alert System Information
        </h6>
    </div>
    <div class="card-body">
        <h6>How the Alert System Works:</h6>
        <ul>
            <li><strong>5-Minute Detection:</strong> If a device is offline for 5+ minutes, an alert is created and notification is sent</li>
            <li><strong>30-Minute Re-alerts:</strong> If the device is still down and hasn't been actioned, another notification is sent every 30 minutes</li>
            <li><strong>Auto-Resolution:</strong> When a device comes back online, the alert is automatically resolved</li>
            <li><strong>Manual Resolution:</strong> You can manually resolve alerts by clicking the "Resolve" button and adding notes</li>
            <li><strong>Notification Settings:</strong> Configure email/SMS recipients per device in <a href="../devices/">Device Management</a></li>
        </ul>
    </div>
</div>

<?php endif; ?>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
