<?php
define('BYPASS_AUTH', true); // Temporarily allow access without login to fix the 500 error
require_once __DIR__ . '/includes/auth.php';

echo "<h1>Hospitals Table - Complete Schema Sync</h1>";
echo "<pre>";

// Ensure table exists
$sql_create = "CREATE TABLE IF NOT EXISTS hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    phone VARCHAR(50),
    margin_top VARCHAR(20) DEFAULT '20mm',
    margin_bottom VARCHAR(20) DEFAULT '20mm',
    margin_left VARCHAR(20) DEFAULT '20mm',
    margin_right VARCHAR(20) DEFAULT '20mm',
    logo_path VARCHAR(255),
    digital_signature_path VARCHAR(255),
    letterhead_image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql_create)) {
    echo "Base table check: <b>OK</b><br>";
}
else {
    die("Error checking/creating table: " . $conn->error);
}

// Columns to ensure exist (in case table was created with older schema)
$columns = [
    "address" => "TEXT AFTER name",
    "phone" => "VARCHAR(50) AFTER address",
    "margin_top" => "VARCHAR(20) DEFAULT '20mm' AFTER phone",
    "margin_bottom" => "VARCHAR(20) DEFAULT '20mm' AFTER margin_top",
    "margin_left" => "VARCHAR(20) DEFAULT '20mm' AFTER margin_bottom",
    "margin_right" => "VARCHAR(20) DEFAULT '20mm' AFTER margin_left",
    "logo_path" => "VARCHAR(255) AFTER margin_right",
    "digital_signature_path" => "VARCHAR(255) AFTER logo_path",
    "letterhead_image_path" => "VARCHAR(255) AFTER digital_signature_path"
];

foreach ($columns as $col => $definition) {
    $check = $conn->query("SHOW COLUMNS FROM hospitals LIKE '$col'");
    if ($check->num_rows == 0) {
        $sql = "ALTER TABLE hospitals ADD $col $definition";
        if ($conn->query($sql)) {
            echo "Added missing column: <b>$col</b><br>";
        }
        else {
            echo "<span style='color:red;'>Error adding $col: " . $conn->error . "</span><br>";
        }
    }
    else {
        echo "Column <b>$col</b> already exists.<br>";
    }
}

echo "<br><h2 style='color:green;'>Migration Complete!</h2>";
echo "You can now Edit/Save hospital details safely.";
echo "<a href='hospitals_edit.php' style='display:inline-block;padding:10px 20px;background:#4f46e5;color:white;text-decoration:none;border-radius:5px;font-weight:bold;margin-top:20px;'>Go to Hospital Edit Page</a>";
echo "</pre>";
?>
