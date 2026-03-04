<?php
/**
 * debug_rx_post.php — Temporary script to debug prescription_items INSERT
 * DELETE after use.
 */
require_once __DIR__ . '/config/db.php';
header('Content-Type: text/plain');

echo "=== POST DATA ===\n";
echo json_encode($_POST, JSON_PRETTY_PRINT) . "\n\n";

echo "=== TEST INSERT ===\n";
// Simulate what the edit page does
$rx_id = 2; // test with prescription 2
$meds_post = $_POST['meds'] ?? [['medicine_name' => 'TestMed', 'dosage' => '10mg', 'frequency' => 'OD', 'duration' => '7d', 'instructions' => '']];

$m_stmt = $conn->prepare("INSERT INTO prescription_items (prescription_id, medicine_name, dosage, frequency, duration, instructions) VALUES (?,?,?,?,?,?)");
echo "Prepare result: " . ($m_stmt ? "SUCCESS" : "FAILED: " . $conn->error) . "\n";

if ($m_stmt) {
    foreach ($meds_post as $m) {
        $name = trim($m['medicine_name'] ?? '');
        if (empty($name)) {
            echo "  SKIP: empty name\n";
            continue;
        }
        $dose = trim($m['dosage'] ?? '');
        $freq = trim($m['frequency'] ?? '');
        $dur = trim($m['duration'] ?? '');
        $instr = trim($m['instructions'] ?? '');
        $m_stmt->bind_param("isssss", $rx_id, $name, $dose, $freq, $dur, $instr);
        $ok = $m_stmt->execute();
        echo "  INSERT '$name': " . ($ok ? "OK (insert_id=" . $m_stmt->insert_id . ")" : "FAIL: " . $m_stmt->error) . "\n";
    }
}

echo "\n=== CURRENT prescription_items ROWS (rx_id=2) ===\n";
$res = $conn->query("SELECT * FROM prescription_items WHERE prescription_id = 2");
if ($res) {
    while ($r = $res->fetch_assoc())
        echo json_encode($r) . "\n";
}
else {
    echo "Query failed: " . $conn->error . "\n";
}

echo "\n=== prescription_items COLUMN CHECK ===\n";
$res2 = $conn->query("DESCRIBE prescription_items");
while ($r = $res2->fetch_assoc()) {
    echo "  " . str_pad($r['Field'], 24) . $r['Type'] . "\n";
}
