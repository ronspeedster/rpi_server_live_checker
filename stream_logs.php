<?php
require_once __DIR__ . '/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

header('Content-Type: application/json');

$log_file = __DIR__ . '/data/monitor.log';
$position = intval($_GET['position'] ?? 0);

// Check if log file exists
if (!file_exists($log_file)) {
    echo json_encode([
        'success' => true,
        'content' => '',
        'position' => 0,
        'file_exists' => false
    ]);
    exit;
}

// Get file size
$file_size = filesize($log_file);

// If position is beyond file size, file was likely truncated/recreated
if ($position > $file_size) {
    $position = 0;
}

// Read new content from the position
$handle = fopen($log_file, 'r');
if ($handle === false) {
    echo json_encode([
        'success' => false,
        'error' => 'Could not open log file'
    ]);
    exit;
}

// Seek to the last read position
fseek($handle, $position);

// Read new content
$content = '';
while (!feof($handle)) {
    $content .= fread($handle, 8192);
}

$new_position = ftell($handle);
fclose($handle);

echo json_encode([
    'success' => true,
    'content' => $content,
    'position' => $new_position,
    'file_exists' => true,
    'file_size' => $file_size
]);
