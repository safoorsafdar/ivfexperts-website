<?php
require_once __DIR__ . '/../4me/config/db.php';

$sql = "CREATE TABLE IF NOT EXISTS document_download_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tracking_code VARCHAR(32) UNIQUE,
    document_type ENUM('rx', 'sa', 'usg', 'receipt') NOT NULL,
    document_id INT NOT NULL,
    patient_id INT DEFAULT NULL,
    admin_id INT DEFAULT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "<h1>Migration Successful</h1>";
    echo "<p>Table document_download_logs created successfully.</p>";
}
else {
    echo "<h1>Migration Failed</h1>";
    echo "<p>Error creating table: " . $conn->error . "</p>";
}
?>
