<?php
require_once __DIR__ . '/includes/auth.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) { echo '[]'; exit; }

$like    = '%' . $conn->escape_string($q) . '%';
$results = [];

$res = $conn->query(
    "SELECT id, name, formula, default_dosage, default_frequency, default_duration, default_instructions
     FROM medications
     WHERE name LIKE '$like' OR formula LIKE '$like'
     ORDER BY name ASC
     LIMIT 12"
);
if ($res) {
    while ($row = $res->fetch_assoc()) $results[] = $row;
}

echo json_encode($results);
