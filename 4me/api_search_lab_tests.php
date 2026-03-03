<?php
require_once __DIR__ . '/includes/auth.php';

$q = trim($_GET['q'] ?? '');
$results = [];

if (strlen($q) >= 2) {
    $searchTerm = "%$q%";
    $stmt = $conn->prepare("SELECT id, test_name, category, cpt_code FROM lab_tests_directory WHERE test_name LIKE ? ORDER BY category ASC, test_name ASC LIMIT 15");
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $results[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($results);
?>
