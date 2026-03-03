<?php
require_once __DIR__ . '/includes/auth.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) { echo '[]'; exit; }

$like = '%' . $conn->escape_string($q) . '%';
$results = [];

try {
    $res = $conn->query("SELECT id, name FROM medications WHERE name LIKE '$like' ORDER BY name ASC LIMIT 12");
    if ($res) while ($row = $res->fetch_assoc()) $results[] = $row;
} catch (Exception $e) {}

echo json_encode($results);
