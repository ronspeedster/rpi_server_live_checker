<?php
require_once __DIR__ . '/../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$device_id = intval($_GET['id'] ?? 0);

if (!$device_id) {
    header('Location: index.php');
    exit;
}

try {
    $pdo = db();
    
    // Fetch device name for confirmation message
    $stmt = $pdo->prepare("SELECT name FROM devices WHERE id = :id");
    $stmt->execute([':id' => $device_id]);
    $device = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$device) {
        $_SESSION['device_error'] = 'Device not found';
    } else {
        // Delete the device (CASCADE will delete related ping_logs and alerts)
        $stmt = $pdo->prepare("DELETE FROM devices WHERE id = :id");
        $stmt->execute([':id' => $device_id]);
        
        $_SESSION['device_deleted'] = 'Device "' . $device['name'] . '" has been deleted successfully';
    }
} catch (Exception $e) {
    $_SESSION['device_error'] = 'Error deleting device: ' . $e->getMessage();
}

header('Location: index.php');
exit;
