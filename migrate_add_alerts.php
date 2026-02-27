<?php
/**
 * Database Migration: Add Alerts Table
 * 
 * Run this file once via browser: http://localhost/rpi_server_live_checker/migrate_add_alerts.php
 */

require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    
    // Drop existing alerts table if it exists (to recreate with new structure)
    $pdo->exec("DROP TABLE IF EXISTS alerts");
    
    // Create alerts table with new structure
    $pdo->exec("CREATE TABLE IF NOT EXISTS alerts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        device_id INTEGER NOT NULL,
        status TEXT NOT NULL DEFAULT 'active',
        first_detected_at TEXT NOT NULL,
        last_notified_at TEXT,
        actioned_at TEXT,
        actioned_by INTEGER,
        notes TEXT,
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
        FOREIGN KEY (actioned_by) REFERENCES users(id) ON DELETE SET NULL
    )");
    
    echo "✅ Migration successful!<br><br>";
    echo "Alerts table created with columns:<br>";
    echo "- id: Primary key<br>";
    echo "- device_id: Foreign key to devices table<br>";
    echo "- status: 'active' or 'resolved'<br>";
    echo "- first_detected_at: When device first went offline<br>";
    echo "- last_notified_at: Last time notification was sent<br>";
    echo "- actioned_at: When alert was resolved<br>";
    echo "- actioned_by: User who resolved the alert<br>";
    echo "- notes: Resolution notes<br>";
    echo "- created_at: Record creation timestamp<br><br>";
    
    echo "<a href='dashboard.php'>Go to Dashboard</a>";
    
} catch (PDOException $e) {
    echo "❌ Migration failed: " . htmlspecialchars($e->getMessage());
}
?>
