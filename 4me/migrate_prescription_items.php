<?php
/**
 * migrate_prescription_items.php
 * Adds the missing columns to prescription_items table.
 * Safe to run multiple times (ALTER TABLE ... IF NOT EXISTS equivalent via INFORMATION_SCHEMA checks).
 *
 * Run once from your browser: https://4me.ivfexperts.pk/migrate_prescription_items.php
 * DELETE this file after running.
 */
require_once __DIR__ . '/config/db.php';

header('Content-Type: text/html; charset=utf-8');
echo '<pre style="font-family:monospace;padding:2rem;">';
echo "=== Prescription Items Migration ===\n\n";

$migrations = [
    'medicine_name' => "ALTER TABLE prescription_items ADD COLUMN medicine_name VARCHAR(255) NOT NULL DEFAULT '' AFTER prescription_id",
    'frequency' => "ALTER TABLE prescription_items ADD COLUMN frequency VARCHAR(100) NOT NULL DEFAULT '' AFTER dosage",
    'duration' => "ALTER TABLE prescription_items ADD COLUMN duration VARCHAR(100) NOT NULL DEFAULT '' AFTER frequency",
    'med_type' => "ALTER TABLE prescription_items ADD COLUMN med_type VARCHAR(100) NOT NULL DEFAULT '' AFTER medicine_name",
];

foreach ($migrations as $colName => $sql) {
    // Check if column already exists
    $check = $conn->query("SELECT COUNT(*) c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'prescription_items' AND COLUMN_NAME = '$colName'");
    $exists = $check && $check->fetch_assoc()['c'] > 0;

    if ($exists) {
        echo "  SKIP: Column `$colName` already exists.\n";
        continue;
    }

    if ($conn->query($sql)) {
        echo "  OK  : Column `$colName` added successfully.\n";
    }
    else {
        echo "  ERR : Failed to add `$colName`: " . $conn->error . "\n";
    }
}

// Also ensure the frequency column exists in medications table (may differ on live)
echo "\n=== Checking medications table ===\n";
$medCols = [
    'formula' => "ALTER TABLE medications ADD COLUMN formula VARCHAR(255) NOT NULL DEFAULT '' AFTER name",
    'med_type' => "ALTER TABLE medications ADD COLUMN med_type VARCHAR(100) NOT NULL DEFAULT 'General' AFTER formula",
    'default_dosage' => "ALTER TABLE medications ADD COLUMN default_dosage VARCHAR(100) NOT NULL DEFAULT '' AFTER med_type",
    'default_frequency' => "ALTER TABLE medications ADD COLUMN default_frequency VARCHAR(100) NOT NULL DEFAULT '' AFTER default_dosage",
    'default_duration' => "ALTER TABLE medications ADD COLUMN default_duration VARCHAR(100) NOT NULL DEFAULT '' AFTER default_frequency",
    'default_instructions' => "ALTER TABLE medications ADD COLUMN default_instructions TEXT AFTER default_duration",
];

foreach ($medCols as $colName => $sql) {
    $check = $conn->query("SELECT COUNT(*) c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'medications' AND COLUMN_NAME = '$colName'");
    $exists = $check && $check->fetch_assoc()['c'] > 0;
    if ($exists) {
        echo "  SKIP: medications.`$colName` already exists.\n";
        continue;
    }
    if ($conn->query($sql)) {
        echo "  OK  : medications.`$colName` added.\n";
    }
    else {
        echo "  ERR : " . $conn->error . "\n";
    }
}

// Final verification — show current prescription_items columns
echo "\n=== Current prescription_items structure ===\n";
$res = $conn->query("DESCRIBE prescription_items");
while ($r = $res->fetch_assoc()) {
    echo "  " . str_pad($r['Field'], 22) . str_pad($r['Type'], 24) . "NULL:" . $r['Null'] . "\n";
}

echo "\n✅ Migration complete. You can now DELETE this file from the server.\n";
echo '</pre>';
