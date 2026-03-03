<?php
/**
 * Migration: Leads (CRM) & Staff Tables
 * Run once to create tables and add any missing columns.
 */
require_once __DIR__ . '/includes/auth.php';

echo "<h1>IVF Experts — Leads & Staff Migration</h1><pre>";

$sqls = [
    // Leads / CRM table
    "CREATE TABLE IF NOT EXISTS leads (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        patient_name  VARCHAR(255) NOT NULL,
        phone         VARCHAR(50),
        email         VARCHAR(255),
        inquiry_type  VARCHAR(100),
        source        VARCHAR(100),
        status        ENUM('new','contacted','consultation_booked','closed') DEFAULT 'new',
        notes         TEXT,
        created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // Staff table
    "CREATE TABLE IF NOT EXISTS staff (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        name        VARCHAR(255) NOT NULL,
        role        VARCHAR(100) NOT NULL,
        phone       VARCHAR(50),
        email       VARCHAR(255),
        cnic        VARCHAR(20),
        salary      DECIMAL(12,2) DEFAULT 0,
        join_date   DATE,
        status      ENUM('Active','Inactive') DEFAULT 'Active',
        notes       TEXT,
        created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
];

foreach ($sqls as $sql) {
    echo "Executing: " . substr($sql, 0, 80) . "...\n";
    if ($conn->query($sql)) {
        echo "<span style='color:green;'>SUCCESS</span>\n\n";
    } else {
        echo "<span style='color:red;'>FAILED: " . $conn->error . "</span>\n\n";
    }
}

// Add missing columns to leads if table pre-existed
$lead_checks = [
    "phone"        => "VARCHAR(50) AFTER patient_name",
    "email"        => "VARCHAR(255) AFTER phone",
    "inquiry_type" => "VARCHAR(100) AFTER email",
    "source"       => "VARCHAR(100) AFTER inquiry_type",
    "notes"        => "TEXT AFTER status",
    "updated_at"   => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",
];

echo "--- Checking <b>leads</b> columns ---\n";
foreach ($lead_checks as $col => $def) {
    $res = $conn->query("SHOW COLUMNS FROM leads LIKE '$col'");
    if ($res && $res->num_rows === 0) {
        echo "Adding <b>$col</b>... ";
        echo $conn->query("ALTER TABLE leads ADD COLUMN $col $def")
            ? "<span style='color:green;'>OK</span>\n"
            : "<span style='color:red;'>FAILED: " . $conn->error . "</span>\n";
    } else {
        echo "Column <b>$col</b> already exists.\n";
    }
}

echo "\n<h2 style='color:green;'>Migration Complete!</h2>";
echo "<a href='leads.php' style='color:blue;'>Go to Leads</a> | ";
echo "<a href='staff.php' style='color:blue;'>Go to Staff</a>";
echo "</pre>";
?>
