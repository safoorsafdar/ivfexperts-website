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

// ── Drop FK constraint on medication_id so free-text medicine names are stored without needing a FK match ──
echo "\n=== Fixing medication_id constraint ===\n";

// Find and drop FK constraints referencing medications table
$fkRes = $conn->query("
    SELECT CONSTRAINT_NAME 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'prescription_items' 
      AND COLUMN_NAME = 'medication_id'
      AND REFERENCED_TABLE_NAME = 'medications'
");
if ($fkRes) {
    while ($fkRow = $fkRes->fetch_assoc()) {
        $fkName = $fkRow['CONSTRAINT_NAME'];
        if ($conn->query("ALTER TABLE prescription_items DROP FOREIGN KEY `$fkName`")) {
            echo "  OK  : Dropped FK constraint `$fkName`.\n";
        }
        else {
            echo "  ERR : Could not drop FK `$fkName`: " . $conn->error . "\n";
        }
    }
}
else {
    echo "  INFO: Could not check FK constraints: " . $conn->error . "\n";
}

// Make medication_id nullable
$colCheck = $conn->query("SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'prescription_items' AND COLUMN_NAME = 'medication_id'");
if ($colCheck && ($colRow = $colCheck->fetch_assoc()) && $colRow['IS_NULLABLE'] === 'NO') {
    if ($conn->query("ALTER TABLE prescription_items MODIFY medication_id INT(11) NULL DEFAULT NULL")) {
        echo "  OK  : `medication_id` is now nullable.\n";
    }
    else {
        echo "  ERR : Could not modify medication_id: " . $conn->error . "\n";
    }
}
else {
    echo "  SKIP: `medication_id` is already nullable.\n";
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
