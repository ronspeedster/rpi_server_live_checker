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

// Get list of users for notification dropdown
$pdo = db();
$users_stmt = $pdo->query("SELECT id, username, first_name, last_name, email, phone FROM users ORDER BY first_name, last_name");
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $ip_address = trim($_POST['ip_address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Notification settings
    $notify_email = isset($_POST['notify_email']) ? 1 : 0;
    $notify_sms = isset($_POST['notify_sms']) ? 1 : 0;
    $notify_email_user_id = ($_POST['notify_email_user'] ?? 'all') === 'all' ? null : intval($_POST['notify_email_user']);
    $notify_sms_user_id = ($_POST['notify_sms_user'] ?? 'all') === 'all' ? null : intval($_POST['notify_sms_user']);

    if (!$name || !$ip_address) {
        $error = 'Device name and IP address are required';
    } elseif (!filter_var($ip_address, FILTER_VALIDATE_IP)) {
        $error = 'Invalid IP address format';
    } else {
        try {
            // Check if IP address already exists
            $stmt = $pdo->prepare("SELECT id FROM devices WHERE ip_address = :ip");
            $stmt->execute([':ip' => $ip_address]);
            if ($stmt->fetch()) {
                $error = 'A device with this IP address already exists';
            } else {
                // Insert new device
                $stmt = $pdo->prepare("INSERT INTO devices (name, ip_address, notes, is_active, notify_email, notify_sms, notify_email_user_id, notify_sms_user_id) VALUES (:name, :ip, :notes, :active, :notify_email, :notify_sms, :notify_email_user, :notify_sms_user)");
                $stmt->execute([
                    ':name' => $name,
                    ':ip' => $ip_address,
                    ':notes' => $notes ?: null,
                    ':active' => $is_active,
                    ':notify_email' => $notify_email,
                    ':notify_sms' => $notify_sms,
                    ':notify_email_user' => $notify_email_user_id,
                    ':notify_sms_user' => $notify_sms_user_id
                ]);

                $_SESSION['device_added'] = 'Device "' . $name . '" has been added successfully';
                header('Location: index.php');
                exit;
            }
        } catch (Exception $e) {
            $error = 'Error adding device: ' . $e->getMessage();
        }
    }
}

// Page settings
$page_title = 'Network Monitor - Add Device';
$active_page = 'devices';

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
                        <h1 class="h3 mb-0 text-gray-800">Add Device</h1>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Devices
                        </a>
                    </div>

                    <!-- Add Device Form -->
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
                                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                                   placeholder="e.g., Raspberry Pi Server">
                                        </div>

                                        <div class="form-group">
                                            <label for="ip_address">IP Address <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="ip_address" name="ip_address" required 
                                                   value="<?php echo htmlspecialchars($_POST['ip_address'] ?? ''); ?>"
                                                   placeholder="e.g., 192.168.1.100">
                                            <small class="form-text text-muted">Enter a valid IPv4 or IPv6 address</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="notes">Notes</label>
                                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                                      placeholder="Optional notes about this device"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                                        </div>

                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
                                                       <?php echo (!isset($_POST['name']) || isset($_POST['is_active'])) ? 'checked' : ''; ?>>
                                                <label class="custom-control-label" for="is_active">
                                                    Active (monitor this device)
                                                </label>
                                            </div>
                                        </div>

                        <hr>
                        <h6 class="font-weight-bold text-primary mb-3">Notification Settings</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="notify_email" name="notify_email" 
                                               <?php echo (!isset($_POST['name']) || isset($_POST['notify_email'])) ? 'checked' : ''; ?>
                                               onchange="toggleEmailUser(this.checked)">
                                        <label class="custom-control-label" for="notify_email">
                                            <i class="fas fa-envelope"></i> Send Email Notifications
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group" id="email_user_group">
                                    <label for="notify_email_user">Send To</label>
                                    <select class="form-control" id="notify_email_user" name="notify_email_user">
                                        <option value="all" selected>All Users with Email</option>
                                        <?php foreach ($users as $user): ?>
                                            <?php if ($user['email']): ?>
                                                <option value="<?php echo $user['id']; ?>">
                                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ')'); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="notify_sms" name="notify_sms" 
                                               <?php echo (!isset($_POST['name']) || isset($_POST['notify_sms'])) ? 'checked' : ''; ?>
                                               onchange="toggleSmsUser(this.checked)">
                                        <label class="custom-control-label" for="notify_sms">
                                            <i class="fas fa-sms"></i> Send SMS Notifications
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group" id="sms_user_group">
                                    <label for="notify_sms_user">Send To</label>
                                    <select class="form-control" id="notify_sms_user" name="notify_sms_user">
                                        <option value="all" selected>All Users with Phone</option>
                                        <?php foreach ($users as $user): ?>
                                            <?php if ($user['phone']): ?>
                                                <option value="<?php echo $user['id']; ?>">
                                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['phone'] . ')'); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <script>
                        function toggleEmailUser(checked) {
                            document.getElementById('email_user_group').style.display = checked ? 'block' : 'none';
                        }
                        function toggleSmsUser(checked) {
                            document.getElementById('sms_user_group').style.display = checked ? 'block' : 'none';
                        }
                        // Initialize on page load
                        toggleEmailUser(document.getElementById('notify_email').checked);
                        toggleSmsUser(document.getElementById('notify_sms').checked);
                        </script>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
