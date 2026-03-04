<?php
/**
 * cleanup_ghost_rows.php
 * Removes all prescription_items rows where medicine_name is NULL or empty.
 * Safe to run multiple times. DELETE this file after running.
 */
require_once __DIR__ . '/config/db.php';
header('Content-Type: text/html; charset=utf-8');
echo '<pre style="font-family:monospace;padding:2rem;">';
echo "=== Cleanup Ghost Prescription Item Rows ===\n\n";

// Show what we will delete
$preview = $conn->query("SELECT prescription_id, COUNT(*) c FROM prescription_items WHERE TRIM(COALESCE(medicine_name,'')) = '' GROUP BY prescription_id");
echo "Ghost rows per prescription (medicine_name is null/empty):\n";
$total = 0;
if ($preview) {
    while ($r = $preview->fetch_assoc()) {
        echo "  rx_id={$r['prescription_id']}: {$r['c']} ghost rows\n";
        $total += $r['c'];
    }
}
echo "Total ghost rows to delete: $total\n\n";

if ($total > 0) {
    $del = $conn->query("DELETE FROM prescription_items WHERE TRIM(COALESCE(medicine_name,'')) = ''");
    if ($del) {
        echo "✅ Deleted $total ghost rows. Affected: " . $conn->affected_rows . "\n";
    }
    else {
        echo "❌ Delete failed: " . $conn->error . "\n";
    }
}
else {
    echo "Nothing to clean up.\n";
}

echo "\n=== Remaining rows per prescription ===\n";
$rem = $conn->query("SELECT prescription_id, COUNT(*) c FROM prescription_items GROUP BY prescription_id");
if ($rem) {
    while ($r = $rem->fetch_assoc())
        echo "  rx_id={$r['prescription_id']}: {$r['c']} rows\n";
}

echo "\n=== All remaining rows ===\n";
$all = $conn->query("SELECT id, prescription_id, medicine_name, dosage, frequency, duration FROM prescription_items ORDER BY prescription_id, id");
if ($all) {
    while ($r = $all->fetch_assoc())
        echo "  " . json_encode($r) . "\n";
}

echo "\n✅ Done. You can now DELETE this file from the server.\n";
echo '</pre>';
