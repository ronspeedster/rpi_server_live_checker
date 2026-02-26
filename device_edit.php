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
$success = '';
$error = '';
$device_id = intval($_GET['id'] ?? 0);

if (!$device_id) {
    header('Location: devices.php');
    exit;
}

$pdo = db();

// Fetch device data
$stmt = $pdo->prepare("SELECT * FROM devices WHERE id = :id");
$stmt->execute([':id' => $device_id]);
$device = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$device) {
    $_SESSION['device_error'] = 'Device not found';
    header('Location: devices.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $ip_address = trim($_POST['ip_address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!$name || !$ip_address) {
        $error = 'Device name and IP address are required';
    } elseif (!filter_var($ip_address, FILTER_VALIDATE_IP)) {
        $error = 'Invalid IP address format';
    } else {
        try {
            // Check if IP address already exists (except for current device)
            $stmt = $pdo->prepare("SELECT id FROM devices WHERE ip_address = :ip AND id != :id");
            $stmt->execute([':ip' => $ip_address, ':id' => $device_id]);
            if ($stmt->fetch()) {
                $error = 'A device with this IP address already exists';
            } else {
                // Update device
                $stmt = $pdo->prepare("UPDATE devices SET name = :name, ip_address = :ip, notes = :notes, is_active = :active WHERE id = :id");
                $stmt->execute([
                    ':name' => $name,
                    ':ip' => $ip_address,
                    ':notes' => $notes ?: null,
                    ':active' => $is_active,
                    ':id' => $device_id
                ]);

                // Update device array for re-display
                $device['name'] = $name;
                $device['ip_address'] = $ip_address;
                $device['notes'] = $notes;
                $device['is_active'] = $is_active;

                $_SESSION['device_updated'] = 'Device "' . $name . '" has been updated successfully';
                header('Location: devices.php');
                exit;
            }
        } catch (Exception $e) {
            $error = 'Error updating device: ' . $e->getMessage();
        }
    }
}

// Page settings
$page_title = 'Network Monitor - Edit Device';
$active_page = 'devices';

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
                        <h1 class="h3 mb-0 text-gray-800">Edit Device</h1>
                        <a href="devices.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Devices
                        </a>
                    </div>

                    <!-- Edit Device Form -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Device Information</h6>
                                </div>
                                <div class="card-body">
                                    <?php if ($success): ?>
                                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                                    <?php endif; ?>
                                    <?php if ($error): ?>
                                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                    <?php endif; ?>

                                    <form method="POST">
                                        <div class="form-group">
                                            <label for="name">Device Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" required 
                                                   value="<?php echo htmlspecialchars($device['name']); ?>"
                                                   placeholder="e.g., Raspberry Pi Server">
                                        </div>

                                        <div class="form-group">
                                            <label for="ip_address">IP Address <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="ip_address" name="ip_address" required 
                                                   value="<?php echo htmlspecialchars($device['ip_address']); ?>"
                                                   placeholder="e.g., 192.168.1.100">
                                            <small class="form-text text-muted">Enter a valid IPv4 or IPv6 address</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="notes">Notes</label>
                                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                                      placeholder="Optional notes about this device"><?php echo htmlspecialchars($device['notes'] ?? ''); ?></textarea>
                                        </div>

                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
                                                       <?php echo $device['is_active'] ? 'checked' : ''; ?>>
                                                <label class="custom-control-label" for="is_active">
                                                    Active (monitor this device)
                                                </label>
                                            </div>
                                        </div>

                                        <hr>

                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Device
                                        </button>
                                        <a href="devices.php" class="btn btn-secondary">
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
