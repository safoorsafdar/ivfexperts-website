<?php
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) { echo json_encode([]); exit; }

$results = [];
try {
    $term = '%' . $q . '%';
    $stmt = $conn->prepare(
        "SELECT icd10_code, description, category, snomed_code
         FROM icd10_codes
         WHERE icd10_code LIKE ? OR description LIKE ?
         ORDER BY
             CASE WHEN icd10_code LIKE ? THEN 0 ELSE 1 END,
             category ASC, description ASC
         LIMIT 20"
    );
    $stmt->bind_param("sss", $term, $term, $term);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $results[] = $row;
    }
} catch (Exception $e) {
    // return empty on error
}

echo json_encode($results);
