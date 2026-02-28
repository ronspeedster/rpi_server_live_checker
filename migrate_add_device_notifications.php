<?php
/**
 * Database Migration: Add notification columns to devices table
 * Run this script once to update existing databases
 */

require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    
    // Check which columns already exist
    $stmt = $pdo->query("PRAGMA table_info(devices)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $existingColumns = [];
    foreach ($columns as $column) {
        $existingColumns[] = $column['name'];
    }
    
    $columnsToAdd = [
        'notify_email' => 'ALTER TABLE devices ADD COLUMN notify_email INTEGER NOT NULL DEFAULT 1',
        'notify_sms' => 'ALTER TABLE devices ADD COLUMN notify_sms INTEGER NOT NULL DEFAULT 1',
        'notify_email_user_id' => 'ALTER TABLE devices ADD COLUMN notify_email_user_id INTEGER',
        'notify_sms_user_id' => 'ALTER TABLE devices ADD COLUMN notify_sms_user_id INTEGER'
    ];
    
    $added = [];
    $skipped = [];
    
    foreach ($columnsToAdd as $columnName => $sql) {
        if (!in_array($columnName, $existingColumns)) {
            $pdo->exec($sql);
            $added[] = $columnName;
        } else {
            $skipped[] = $columnName;
        }
    }
    
    echo "Migration completed successfully!\n\n";
    
    if (!empty($added)) {
        echo "✓ Added columns: " . implode(', ', $added) . "\n";
    }
    
    if (!empty($skipped)) {
        echo "→ Already exists (skipped): " . implode(', ', $skipped) . "\n";
    }
    
    if (empty($added) && empty($skipped)) {
        echo "No changes needed.\n";
    }
    
} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
