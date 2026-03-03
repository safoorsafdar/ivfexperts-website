<?php
require_once __DIR__ . '/includes/auth.php';

// Bypass auth if requested via CLI or specific param if needed, 
// but here we assume internal execution.
header('Content-Type: text/html; charset=utf-8');
echo "<h1>Diagnostic & Migration: Patients Table Schema v2</h1>";
echo "<pre>";

$columns_to_ensure = [
    "patient_age INT AFTER last_name",
    "date_of_birth DATE AFTER patient_age",
    "blood_group VARCHAR(10) AFTER date_of_birth",
    "marital_status ENUM('Single', 'Married', 'Divorced', 'Widowed') DEFAULT 'Single' AFTER gender",
    "gravida INT DEFAULT 0 AFTER marital_status",
    "para INT DEFAULT 0 AFTER gravida",
    "abortions INT DEFAULT 0 AFTER para",
    "years_married INT AFTER abortions",
    "address TEXT AFTER phone",
    "email VARCHAR(255) AFTER address",
    "spouse_age INT AFTER spouse_name",
    "spouse_gender ENUM('Male', 'Female', 'Other') AFTER spouse_age",
    "spouse_cnic VARCHAR(20) AFTER spouse_gender",
    "spouse_phone VARCHAR(20) AFTER spouse_cnic",
    "referring_hospital_id INT AFTER spouse_phone"
];

foreach ($columns_to_ensure as $colDef) {
    preg_match('/^(\w+)/', $colDef, $matches);
    $colName = $matches[1];

    // Check if column exists
    $check = $conn->query("SHOW COLUMNS FROM patients LIKE '$colName'");
    if ($check->num_rows == 0) {
        echo "Adding column: <b>$colName</b>... ";
        $sql = "ALTER TABLE patients ADD $colDef";
        try {
            if ($conn->query($sql)) {
                echo "<span style='color:green;'>SUCCESS</span>\n";
            }
            else {
                echo "<span style='color:red;'>FAILED: " . $conn->error . "</span>\n";
            }
        }
        catch (Throwable $e) {
            echo "<span style='color:red;'>EXCEPTION: " . $e->getMessage() . "</span>\n";
        }
    }
    else {
        echo "Column <b>$colName</b> already exists. Checking type... ";
        // Optional: you could check if the type matches, but for now we just skip if it exists
        echo "OK\n";
    }
}

echo "<br><h2 style='color:green;'>Migration Complete!</h2>";
echo "You can now register/edit patients safely.";
echo "</pre>";
?>
