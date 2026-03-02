<?php
require_once __DIR__ . '/includes/auth.php';

echo "<h1>Updating Patients Table Schema</h1>";
echo "<pre>";

$columns_to_add = [
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
    "spouse_phone VARCHAR(20) AFTER spouse_cnic"
];

foreach ($columns_to_add as $col) {
    preg_match('/^(\w+)/', $col, $matches);
    $colName = $matches[1];

    // Check if column exists
    $check = $conn->query("SHOW COLUMNS FROM patients LIKE '$colName'");
    if ($check->num_rows == 0) {
        $sql = "ALTER TABLE patients ADD $col";
        if ($conn->query($sql)) {
            echo "Added column: <b>$colName</b><br>";
        }
        else {
            echo "<span style='color:red;'>Error adding $colName: " . $conn->error . "</span><br>";
        }
    }
    else {
        echo "Column <b>$colName</b> already exists.<br>";
    }
}

echo "<br><h2 style='color:green;'>Migration Complete!</h2>";
echo "You can now Edit/Save patients without errors.";
echo "</pre>";
?>
