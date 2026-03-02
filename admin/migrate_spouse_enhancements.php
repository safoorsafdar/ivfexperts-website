<?php
/**
 * Migration Script: Spouse Enhancements
 * Adds sex-specific reference ranges and record attribution (Patient vs Spouse)
 */

require_once dirname(__DIR__) . '/config/db.php';

echo "<h1>IVF Experts - Spouse Enhancements Migration</h1>";
echo "<pre>";

$sqls = [
    // 1. Update Lab Tests Directory for sex-specific ranges
    "ALTER TABLE lab_tests_directory 
        ADD COLUMN reference_range_male TEXT AFTER reference_range,
        ADD COLUMN reference_range_female TEXT AFTER reference_range_male",

    // 2. Update Lab Results for Spouse attribution and Status
    "ALTER TABLE patient_lab_results 
        ADD COLUMN test_for ENUM('Patient', 'Spouse') DEFAULT 'Patient' AFTER test_id,
        ADD COLUMN status ENUM('Pending', 'Completed') DEFAULT 'Completed' AFTER result_value",

    // 3. Update Ultrasounds for Spouse attribution
    "ALTER TABLE patient_ultrasounds 
        ADD COLUMN record_for ENUM('Patient', 'Spouse') DEFAULT 'Patient' AFTER patient_id",

    // 4. Update Prescriptions for Spouse attribution
    "ALTER TABLE prescriptions 
        ADD COLUMN record_for ENUM('Patient', 'Spouse') DEFAULT 'Patient' AFTER patient_id",

    // 5. Update Patient History (Clinical Visit Record) for Spouse attribution
    "ALTER TABLE patient_history 
        ADD COLUMN record_for ENUM('Patient', 'Spouse') DEFAULT 'Patient' AFTER patient_id",

    // 6. Update Advised Procedures for Spouse attribution
    "ALTER TABLE advised_procedures 
        ADD COLUMN record_for ENUM('Patient', 'Spouse') DEFAULT 'Patient' AFTER patient_id",

    // 7. Create Semen Diagnosis Definitions table
    "CREATE TABLE IF NOT EXISTS semen_diagnosis_definitions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        condition_name VARCHAR(255) UNIQUE NOT NULL,
        definition TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // 8. Populate Semen Diagnosis Definitions
    "INSERT IGNORE INTO semen_diagnosis_definitions (condition_name, definition) VALUES 
        ('Normozoospermia', 'Normal semen parameters as defined by WHO criteria (Volume, Count, Motility, and Morphology).'),
        ('Oligozoospermia', 'Sperm concentration less than 15 million/mL or total sperm count less than 39 million per ejaculate.'),
        ('Asthenozoospermia', 'Reduced sperm motility, specifically less than 32% progressive motility or less than 40% total motility.'),
        ('Teratozoospermia', 'Reduced percentage of morphologically normal sperm (less than 4% as per WHO 5th/6th edition).'),
        ('Azoospermia', 'Complete absence of sperm in the ejaculate after centrifugation.'),
        ('Leukocytospermia', 'Presence of white blood cells (leukocytes) greater than 1 million/mL in the semen, suggesting infection.'),
        ('Necrozoospermia', 'A high percentage of dead (non-viable) sperm in the ejaculate.'),
        ('Globozoospermia', 'A rare condition where sperm heads are round and lack an acrosome, making them unable to fertilize an egg naturally.'),
        ('Oligoteratozoospermia', 'Combination of low sperm count and abnormal morphology.'),
        ('Oligoasthenoteratozoospermia', 'Combination of low count, low motility, and abnormal morphology (OAT syndrome).')"
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

echo "<br><h2 style='color:green;'>Migration Complete!</h2>";
echo "You can now use the new sex-specific features.";
echo "</pre>";
?>
