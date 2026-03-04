<?php
require_once __DIR__ . '/config/db.php';
header('Content-Type: text/plain');

echo "=== All prescription_items rows ===\n";
$res = $conn->query("SELECT * FROM prescription_items ORDER BY prescription_id, id");
if ($res) {
    while ($r = $res->fetch_assoc())
        echo json_encode($r) . "\n";
}
else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n=== With TRIM filter (what edit page sees for id=2) ===\n";
$res2 = $conn->query("SELECT * FROM prescription_items WHERE prescription_id = 2 AND TRIM(COALESCE(medicine_name,'')) != '' ORDER BY id");
if ($res2) {
    $count = 0;
    while ($r = $res2->fetch_assoc()) {
        echo json_encode($r) . "\n";
        $count++;
    }
    echo "(total: $count rows)\n";
}

echo "\n=== Without filter (raw count per prescription) ===\n";
$res3 = $conn->query("SELECT prescription_id, COUNT(*) c, SUM(CASE WHEN TRIM(COALESCE(medicine_name,'')) != '' THEN 1 ELSE 0 END) as has_name FROM prescription_items GROUP BY prescription_id");
if ($res3) {
    while ($r = $res3->fetch_assoc())
        echo "rx_id={$r['prescription_id']}: total={$r['c']}, with_name={$r['has_name']}\n";
}

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
