<?php
/**
 * migrate_phase3_db_hardening.php
 * ─────────────────────────────────────────────────────────────
 * Phase 3 Database Hardening Migration
 * Run ONCE via browser (admin only), then this file is
 * automatically blocked from web access by .htaccess.
 *
 * What this does:
 *  1. Fix prescription_items schema (frequency/duration NULLable)
 *  2. Drop orphaned medication_id column from prescription_items
 *  3. Add missing indexes on frequently-queried FK columns
 *  4. Add updated_at timestamps to core clinical tables
 *  5. Add is_active flag to medications table
 *  6. Create schema_migrations tracking table
 * ─────────────────────────────────────────────────────────────
 */

// Auth — admin only
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    die('<p style="font-family:sans-serif;padding:2rem;color:#c00;">⛔ Admin access required.</p>');
}

require_once __DIR__ . '/config/db.php';

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><title>Phase 3 DB Migration</title>';
echo '<style>body{font-family:monospace;padding:24px;background:#f8fafc;} .ok{color:#065f46;} .err{color:#9f1239;} .skip{color:#92400e;} h2{color:#0f172a;border-bottom:1px solid #e2e8f0;padding-bottom:8px;}</style></head><body>';
echo '<h2>🛠 Phase 3 — Database Hardening Migration</h2>';
echo '<pre>';

$results = [];

// Helper: run a query, log result
function run(mysqli $conn, string $label, string $sql): bool
{
    $ok = $conn->query($sql);
    if ($ok) {
        echo "<span class='ok'>✅ $label</span>\n";
        return true;
    }
    else {
        // If the error is "already exists" or "Duplicate", treat as skip not error
        $err = $conn->error;
        if (
        str_contains($err, 'Duplicate key name') ||
        str_contains($err, 'already exists') ||
        str_contains($err, "Can't DROP") // column already removed
        ) {
            echo "<span class='skip'>⏭  $label — already done (skipping)\n";
        }
        else {
            echo "<span class='err'>❌ $label — ERROR: $err</span>\n";
        }
        return false;
    }
}

// ── Step 1: Schema migrations tracking table ──────────────────────────────────
echo "\n<b>STEP 1: Schema tracking table</b>\n";
run($conn, 'Create schema_migrations table',
    "CREATE TABLE IF NOT EXISTS schema_migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(100) NOT NULL UNIQUE,
        ran_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ran_by_admin INT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── Step 2: Fix prescription_items ────────────────────────────────────────────
echo "\n<b>STEP 2: Fix prescription_items schema</b>\n";

run($conn, 'Allow NULL for frequency column',
    "ALTER TABLE prescription_items MODIFY COLUMN frequency VARCHAR(100) NULL DEFAULT NULL");

run($conn, 'Allow NULL for duration column',
    "ALTER TABLE prescription_items MODIFY COLUMN duration VARCHAR(100) NULL DEFAULT NULL");

run($conn, 'Allow NULL for med_type column',
    "ALTER TABLE prescription_items MODIFY COLUMN med_type VARCHAR(100) NULL DEFAULT NULL");

// Drop medication_id only if it exists
$col_check = $conn->query("SHOW COLUMNS FROM prescription_items LIKE 'medication_id'");
if ($col_check && $col_check->num_rows > 0) {
    run($conn, 'Drop orphaned medication_id column',
        "ALTER TABLE prescription_items DROP COLUMN medication_id");
}
else {
    echo "<span class='skip'>⏭  Drop medication_id — column not found (already removed)</span>\n";
}

// ── Step 3: Add indexes ───────────────────────────────────────────────────────
echo "\n<b>STEP 3: Add performance indexes</b>\n";

$indexes = [
    ['prescription_items', 'idx_rx_items_pid', 'prescription_id'],
    ['prescriptions', 'idx_prescriptions_pid', 'patient_id'],
    ['prescriptions', 'idx_prescriptions_dt', 'created_at'],
    ['semen_analyses', 'idx_sa_pid', 'patient_id'],
    ['lab_results', 'idx_lr_pid', 'patient_id'],
    ['patients', 'idx_patients_mrn', 'mr_number'],
    ['leads', 'idx_leads_status', 'status'],
];

foreach ($indexes as [$table, $idxName, $col]) {
    // Check if table exists first
    $tbl = $conn->query("SHOW TABLES LIKE '$table'");
    if (!$tbl || $tbl->num_rows === 0) {
        echo "<span class='skip'>⏭  Index on $table — table doesn't exist yet</span>\n";
        continue;
    }
    run($conn, "Add index $idxName on $table($col)",
        "ALTER TABLE `$table` ADD INDEX `$idxName` (`$col`)");
}

// Compound name index on patients
$pTbl = $conn->query("SHOW TABLES LIKE 'patients'");
if ($pTbl && $pTbl->num_rows > 0) {
    run($conn, 'Add compound name index on patients',
        "ALTER TABLE `patients` ADD INDEX `idx_patients_name` (`first_name`(50), `last_name`(50))");
}

// ── Step 4: Add updated_at timestamps ─────────────────────────────────────────
echo "\n<b>STEP 4: Add updated_at timestamps to clinical tables</b>\n";

$timestampTables = ['prescriptions', 'semen_analyses', 'lab_results', 'patients', 'medications'];
foreach ($timestampTables as $tbl) {
    $exists = $conn->query("SHOW TABLES LIKE '$tbl'");
    if (!$exists || $exists->num_rows === 0) {
        echo "<span class='skip'>⏭  updated_at on $tbl — table not found</span>\n";
        continue;
    }
    $colCheck = $conn->query("SHOW COLUMNS FROM `$tbl` LIKE 'updated_at'");
    if ($colCheck && $colCheck->num_rows > 0) {
        echo "<span class='skip'>⏭  updated_at on $tbl — column already exists</span>\n";
        continue;
    }
    run($conn, "Add updated_at to $tbl",
        "ALTER TABLE `$tbl` ADD COLUMN `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP");
}

// ── Step 5: Add is_active to medications ──────────────────────────────────────
echo "\n<b>STEP 5: Add is_active soft-delete to medications</b>\n";

$medTbl = $conn->query("SHOW TABLES LIKE 'medications'");
if ($medTbl && $medTbl->num_rows > 0) {
    $colCheck = $conn->query("SHOW COLUMNS FROM medications LIKE 'is_active'");
    if ($colCheck && $colCheck->num_rows === 0) {
        run($conn, 'Add is_active to medications',
            "ALTER TABLE medications ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER id");
    }
    else {
        echo "<span class='skip'>⏭  is_active on medications — already exists</span>\n";
    }
}

// ── Record migration ──────────────────────────────────────────────────────────
$aid = (int)$_SESSION['admin_id'];
$conn->query("INSERT IGNORE INTO schema_migrations (migration, ran_by_admin) VALUES ('phase3_db_hardening_2026', $aid)");

echo "\n\n<b>✅ Migration complete!</b>\n";
echo "<b>⚠️  IMPORTANT: This file is now blocked by .htaccess but please remove it from the repo when done.</b>\n";
echo '</pre>';
echo '<hr><p style="font-family:sans-serif;font-size:12px;color:#94a3b8">Run by admin_id=' . $aid . ' at ' . date('Y-m-d H:i:s') . '</p>';
echo '</body></html>';
