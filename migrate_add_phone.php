<?php
/**
 * Database Migration: Add phone column to users table
 * Run this script once to update existing databases
 */

require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    
    // Check if phone column already exists
    $stmt = $pdo->query("PRAGMA table_info(users)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $phoneExists = false;
    foreach ($columns as $column) {
        if ($column['name'] === 'phone') {
            $phoneExists = true;
            break;
        }
    }
    
    if ($phoneExists) {
        echo "✓ Phone column already exists in users table. No migration needed.\n";
    } else {
        // Add phone column
        $pdo->exec("ALTER TABLE users ADD COLUMN phone TEXT");
        echo "✓ Successfully added phone column to users table.\n";
    }
    
    echo "\nMigration completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
