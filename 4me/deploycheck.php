<?php
/**
 * deploycheck.php — Verify DB state of prescription_items for rx 1 and 2.
 * DELETE after use.
 */
require_once __DIR__ . '/config/db.php';
header('Content-Type: text/plain');

$file = __DIR__ . '/prescriptions_edit.php';
echo "=== prescriptions_edit.php server info ===\n";
echo "File path: $file\n";
echo "File size: " . (file_exists($file) ? filesize($file) . " bytes" : "NOT FOUND") . "\n";
echo "Last modified: " . (file_exists($file) ? date('Y-m-d H:i:s', filemtime($file)) : "N/A") . "\n";
echo "MD5: " . (file_exists($file) ? md5_file($file) : "N/A") . "\n";

echo "\n=== Does it have TRIM filter? ===\n";
$content = file_get_contents($file);
echo "Has 'TRIM(COALESCE': " . (strpos($content, 'TRIM(COALESCE') !== false ? "YES ✅" : "NO ❌") . "\n";
echo "Has '_medsData': " . (strpos($content, '_medsData') !== false ? "YES ✅" : "NO ❌") . "\n";
echo "Has 'renderMedRow': " . (strpos($content, 'renderMedRow') !== false ? "YES ✅" : "NO ❌") . "\n";
echo "Has 'initMedRows': " . (strpos($content, 'initMedRows') !== false ? "YES ✅" : "NO ❌") . "\n";
echo "Has 'handleSubmit': " . (strpos($content, 'handleSubmit') !== false ? "YES ✅" : "NO ❌") . "\n";
echo "Has 'update_prescription': " . (strpos($content, 'update_prescription') !== false ? "YES ✅" : "NO ❌") . "\n";

echo "\n=== First 500 chars ===\n";
echo substr($content, 0, 500) . "\n";
