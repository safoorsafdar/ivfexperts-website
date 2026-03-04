<?php
/**
 * Migration Script: Semen Analysis File Upload Support
 */

require_once __DIR__ . '/includes/auth.php';

echo "<h1>IVF Experts - Semen Analysis File Upload Migration</h1>";
echo "<pre>";

$sqls = [
    // Phase 1: File upload support
    "ALTER TABLE semen_analyses
        ADD COLUMN report_type ENUM('manual', 'file') DEFAULT 'manual' AFTER qrcode_hash,
        ADD COLUMN report_file_path VARCHAR(255) NULL AFTER report_type",
    "ALTER TABLE semen_analyses ADD COLUMN lab_name VARCHAR(255) NULL AFTER report_file_path",
    "ALTER TABLE semen_analyses ADD COLUMN lab_report_number VARCHAR(100) NULL AFTER lab_name",

    // Phase 2: WHO 6th Edition macroscopic/microscopic parameters
    "ALTER TABLE semen_analyses ADD COLUMN im_motility DECIMAL(5,2) DEFAULT 0",
    "ALTER TABLE semen_analyses ADD COLUMN abnormal_morphology DECIMAL(5,2) DEFAULT 0",
    "ALTER TABLE semen_analyses ADD COLUMN appearance VARCHAR(50) DEFAULT 'Normal'",
    "ALTER TABLE semen_analyses ADD COLUMN liquefaction VARCHAR(50) DEFAULT 'Complete'",
    "ALTER TABLE semen_analyses ADD COLUMN viscosity VARCHAR(50) DEFAULT 'Normal'",
    "ALTER TABLE semen_analyses ADD COLUMN vitality DECIMAL(5,2) NULL",
    "ALTER TABLE semen_analyses ADD COLUMN round_cells VARCHAR(100) NULL",
    "ALTER TABLE semen_analyses ADD COLUMN debris VARCHAR(100) NULL",
    "ALTER TABLE semen_analyses ADD COLUMN wbc VARCHAR(100) NULL",
    "ALTER TABLE semen_analyses ADD COLUMN agglutination VARCHAR(100) NULL",
    "ALTER TABLE semen_analyses ADD COLUMN auto_diagnosis VARCHAR(255) DEFAULT 'Pending'",
    "ALTER TABLE semen_analyses ADD COLUMN admin_notes TEXT NULL",
];

foreach ($sqls as $sql) {
    echo "Executing: " . substr($sql, 0, 100) . "... ";
    if ($conn->query($sql)) {
        echo "<span style='color:green;'>SUCCESS</span><br>";
    }
    else {
        $err = $conn->error;
        if (strpos($err, 'Duplicate column name') !== false) {
            echo "<span style='color:orange;'>ALREADY EXISTS (skipped)</span><br>";
        }
        else {
            echo "<span style='color:red;'>FAILED: " . $err . "</span><br>";
        }
    }
}

// Ensure Upload Directory Exists
$upload_dir = dirname(__DIR__) . '/assets/uploads/semen_reports/';
if (!is_dir($upload_dir)) {
    if (mkdir($upload_dir, 0755, true)) {
        echo "Created directory: <b>assets/uploads/semen_reports/</b><br>";
    }
    else {
        echo "<span style='color:red;'>Failed to create upload directory.</span><br>";
    }
}
else {
    echo "Upload directory <b>assets/uploads/semen_reports/</b> already exists.<br>";
}

echo "<br><h2 style='color:green;'>Migration Complete!</h2>";
echo "<a href='sync_hospitals.php' style='color:blue;'>Run Hospital Sync (Optional)</a> | <a href='semen_analyses_add.php' style='color:blue;'>Go to New Report</a>";
echo "</pre>";
?>
