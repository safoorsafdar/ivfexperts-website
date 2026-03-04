<?php
/**
 * Temporary debug script — DELETE after use
 * Checks prescription_items schema and sample data
 */
require_once __DIR__ . '/config/db.php';

header('Content-Type: text/plain');

// Show table columns
$res = $conn->query("DESCRIBE prescription_items");
echo "=== prescription_items COLUMNS ===\n";
while ($r = $res->fetch_assoc()) {
    echo $r['Field'] . ' | ' . $r['Type'] . ' | NULL:' . $r['Null'] . ' | Default:' . $r['Default'] . "\n";
}

echo "\n=== SAMPLE ROWS (prescription_id = 1) ===\n";
$res2 = $conn->query("SELECT * FROM prescription_items WHERE prescription_id = 1 LIMIT 10");
if ($res2) {
    while ($r = $res2->fetch_assoc()) {
        echo json_encode($r) . "\n";
    }
}
else {
    echo "Query error: " . $conn->error . "\n";
}

echo "\n=== COUNT with current filter ===\n";
$res3 = $conn->query("SELECT COUNT(*) c FROM prescription_items WHERE prescription_id = 1 AND TRIM(COALESCE(medicine_name,'')) != ''");
if ($res3)
    echo "Rows with non-empty medicine_name: " . $res3->fetch_assoc()['c'] . "\n";

echo "\n=== COUNT without filter ===\n";
$res4 = $conn->query("SELECT COUNT(*) c FROM prescription_items WHERE prescription_id = 1");
if ($res4)
    echo "Total rows: " . $res4->fetch_assoc()['c'] . "\n";
