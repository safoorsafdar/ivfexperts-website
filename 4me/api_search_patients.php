<?php
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$patients = [];
try {
    $searchTerm = '%' . $query . '%';
    // Search by MR Number, Phone, Name, CNIC, or Spouse details
    $stmt = $conn->prepare("SELECT id, mr_number, first_name, last_name, phone, cnic, gender, spouse_name, spouse_phone, spouse_cnic FROM patients 
                            WHERE mr_number LIKE ? 
                            OR phone LIKE ? 
                            OR first_name LIKE ? 
                            OR last_name LIKE ? 
                            OR cnic LIKE ? 
                            OR spouse_name LIKE ? 
                            OR spouse_phone LIKE ? 
                            OR spouse_cnic LIKE ? 
                            LIMIT 15");
    if ($stmt) {
        $stmt->bind_param("ssssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $patients[] = $row;
        }
    }
}
catch (Exception $e) {
// Return empty array on error
}

echo json_encode($patients);
