<?php
require_once __DIR__ . '/includes/auth.php';

echo "<h1>Migration: Prescription Lab Tests</h1>";
echo "<pre>";

$sql = "CREATE TABLE IF NOT EXISTS prescription_lab_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prescription_id INT NOT NULL,
    test_id INT NULL,
    test_name VARCHAR(255) NOT NULL,
    advised_for ENUM('Patient', 'Spouse') DEFAULT 'Patient',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (prescription_id),
    INDEX (test_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql)) {
    echo "Table `prescription_lab_tests` created or already exists.\n";
}
else {
    echo "Error creating table: " . $conn->error . "\n";
}

echo "\nMigration Complete!";
echo "</pre>";
?>
