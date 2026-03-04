<?php
/**
 * debug_edit_meds.php — Check what prescription_items exist on the server
 * Shows raw DB state + what PHP TRIM filter returns.
 * DELETE after diagnosing.
 */
require_once __DIR__ . '/config/db.php';
header('Content-Type: text/plain');

$ids = [1, 2, 3];
foreach ($ids as $rx_id) {
    echo "=== prescription_id = $rx_id ===\n";

    // Raw count 
    $raw = $conn->query("SELECT id, medicine_name, dosage, frequency FROM prescription_items WHERE prescription_id = $rx_id ORDER BY id ASC");
    echo "All rows (no filter): " . ($raw ? $raw->num_rows . "\n" : "QUERY FAILED: " . $conn->error . "\n");
    if ($raw) {
        while ($r = $raw->fetch_assoc()) {
            echo "  id=" . $r['id'] . " name=[" . $r['medicine_name'] . "] dosage=[" . $r['dosage'] . "] freq=[" . $r['frequency'] . "]\n";
        }
    }

    // With TRIM filter (same as prescriptions_edit.php line 34)
    $filtered = $conn->query("SELECT * FROM prescription_items WHERE prescription_id = $rx_id AND TRIM(COALESCE(medicine_name,'')) != '' ORDER BY id ASC");
    echo "With TRIM filter: " . ($filtered ? $filtered->num_rows . " rows\n" : "QUERY FAILED: " . $conn->error . "\n");
    echo "\n";
}

// Also show the table structure
echo "=== prescription_items COLUMNS ===\n";
$cols = $conn->query("SHOW COLUMNS FROM prescription_items");
while ($c = $cols->fetch_assoc()) {
    echo "  " . $c['Field'] . " | " . $c['Type'] . " | Null=" . $c['Null'] . " | Default=" . $c['Default'] . "\n";
}
