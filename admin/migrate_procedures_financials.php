<?php
/**
 * Super Migration: Procedures & Financials
 */

require_once __DIR__ . '/includes/auth.php';

echo "<h1>IVF Experts - Procedures & Financials Sync</h1>";
echo "<pre>";

$sqls = [
    // 1. Advised Procedures Table
    "CREATE TABLE IF NOT EXISTS advised_procedures (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        procedure_name VARCHAR(255) NOT NULL,
        date_advised DATE NOT NULL,
        status ENUM('Advised', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Advised',
        notes TEXT,
        record_for ENUM('Patient', 'Spouse') DEFAULT 'Patient',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 2. Receipts Table
    "CREATE TABLE IF NOT EXISTS receipts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        hospital_id INT NOT NULL,
        procedure_name VARCHAR(255) NOT NULL,
        amount DECIMAL(15, 2) NOT NULL,
        status ENUM('Paid', 'Unpaid', 'Pending', 'Past Due') DEFAULT 'Paid',
        advised_procedure_id INT NULL,
        receipt_date DATE NOT NULL,
        qrcode_hash VARCHAR(255) UNIQUE,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 3. Expenses Table
    "CREATE TABLE IF NOT EXISTS expenses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        amount DECIMAL(15, 2) NOT NULL,
        expense_date DATE NOT NULL,
        category VARCHAR(100),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

foreach ($sqls as $sql) {
    echo "Executing: " . substr($sql, 0, 80) . "... ";
    if ($conn->query($sql)) {
        echo "<span style='color:green;'>SUCCESS</span><br>";
    }
    else {
        echo "<span style='color:red;'>FAILED: " . $conn->error . "</span><br>";
    }
}

// Check for missing columns in case tables already existed
$checks = [
    "advised_procedures" => [
        "status" => "ENUM('Advised', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Advised' AFTER procedure_name",
        "record_for" => "ENUM('Patient', 'Spouse') DEFAULT 'Patient' AFTER patient_id"
    ],
    "receipts" => [
        "status" => "ENUM('Paid', 'Unpaid', 'Pending', 'Past Due') DEFAULT 'Paid' AFTER amount",
        "payment_method" => "VARCHAR(50) DEFAULT 'Cash' AFTER status",
        "advised_procedure_id" => "INT NULL AFTER payment_method",
        "qrcode_hash" => "VARCHAR(255) AFTER receipt_date",
        "notes" => "TEXT AFTER qrcode_hash"
    ]
];

foreach ($checks as $table => $cols) {
    echo "--- Checking table: <b>$table</b> ---<br>";
    foreach ($cols as $col => $def) {
        try {
            $res = $conn->query("SHOW COLUMNS FROM $table LIKE '$col'");
            if ($res && $res->num_rows == 0) {
                echo "Adding column <b>$col</b> to <b>$table</b>... ";
                if ($conn->query("ALTER TABLE $table ADD $col $def")) {
                    echo "<span style='color:green;'>SUCCESS</span><br>";
                }
                else {
                    echo "<span style='color:red;'>FAILED: " . $conn->error . "</span><br>";
                }
            }
            else {
                echo "Column <b>$col</b> already exists in <b>$table</b>.<br>";
            }
        }
        catch (Throwable $e) {
            echo "<span style='color:red;'>Exception on $col: " . $e->getMessage() . "</span><br>";
        }
    }
}

echo "<br><h2 style='color:green;'>Migration Complete!</h2>";
echo "<a href='procedures.php' style='color:blue;'>Back to Procedures</a> | <a href='financials.php' style='color:blue;'>Back to Financials</a>";
echo "</pre>";
?>
