<?php
/**
 * Migration Script: Semen Analysis File Upload Support
 */

require_once __DIR__ . '/includes/auth.php';

echo "<h1>IVF Experts - Semen Analysis File Upload Migration</h1>";
echo "<pre>";

$sqls = [
    "ALTER TABLE semen_analyses 
        ADD COLUMN report_type ENUM('manual', 'file') DEFAULT 'manual' AFTER qrcode_hash,
        ADD COLUMN report_file_path VARCHAR(255) NULL AFTER report_type"
];

foreach ($sqls as $sql) {
    echo "Executing: " . substr($sql, 0, 100) . "... ";
    try {
        if ($conn->query($sql)) {
            echo "<span style='color:green;'>SUCCESS</span><br>";
        }
    }
    catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<span style='color:orange;'>ALREADY EXISTS</span><br>";
        }
        else {
            echo "<span style='color:red;'>FAILED: " . $e->getMessage() . "</span><br>";
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
