<?php
// config.php
session_start();

define('DB_DIR', __DIR__ . '/data');
define('DB_PATH', DB_DIR . '/network_monitor.sqlite');

// Calculate base path for assets (relative to current script location)
// Count how many directories deep from project root
$project_root = __DIR__;
$script_path = realpath($_SERVER['SCRIPT_FILENAME']);
$relative = str_replace($project_root, '', dirname($script_path));
$depth = $relative ? substr_count($relative, DIRECTORY_SEPARATOR) : 0;
define('BASE_PATH', str_repeat('../', $depth));

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    if (!is_dir(DB_DIR)) {
        // Create /data folder if missing
        mkdir(DB_DIR, 0775, true);
    }

    // Create PDO connection
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Enable foreign keys in SQLite
    $pdo->exec('PRAGMA foreign_keys = ON;');

    // Ensure schema exists
    require_once __DIR__ . '/init_db.php';
    init_db($pdo);

    return $pdo;
}