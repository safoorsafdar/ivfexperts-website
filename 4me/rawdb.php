<?php
/**
 * rawdb.php — Show raw DB content for prescription_items
 * DELETE after use.
 */
require_once __DIR__ . '/config/db.php';
header('Content-Type: text/plain');

echo "=== ALL prescription_items raw ===\n";
$res = $conn->query("SELECT id, prescription_id, HEX(medicine_name) as hex_name, medicine_name, LENGTH(medicine_name) as len, dosage FROM prescription_items ORDER BY prescription_id, id LIMIT 20");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        printf("  id=%-4s rx=%-4s len=%-4s hex=%s name=[%s]\n",
            $r['id'], $r['prescription_id'], $r['len'] ?? 'NULL', $r['hex_name'] ?? 'NULL', $r['medicine_name'] ?? 'NULL');
    }
}

echo "\n=== TRIM filter results ===\n";
$r2 = $conn->query("SELECT COUNT(*) c FROM prescription_items WHERE TRIM(COALESCE(medicine_name,'')) != ''");
echo "Rows passing TRIM filter: " . ($r2 ? $r2->fetch_assoc()['c'] : 'error') . "\n";

$r3 = $conn->query("SELECT COUNT(*) c FROM prescription_items WHERE medicine_name IS NULL");
echo "Rows where medicine_name IS NULL: " . ($r3 ? $r3->fetch_assoc()['c'] : 'error') . "\n";

$r4 = $conn->query("SELECT COUNT(*) c FROM prescription_items WHERE medicine_name = ''");
echo "Rows where medicine_name = '': " . ($r4 ? $r4->fetch_assoc()['c'] : 'error') . "\n";

$r5 = $conn->query("SELECT COUNT(*) c FROM prescription_items");
echo "Total rows: " . ($r5 ? $r5->fetch_assoc()['c'] : 'error') . "\n";
