<?php
define('BYPASS_AUTH', true);
require 'includes/auth.php';

$rx_id = isset($_GET['rx']) ? intval($_GET['rx']) : 1;

$res1 = $conn->query("SELECT * FROM prescription_items WHERE prescription_id = $rx_id ORDER BY id ASC");
$items1 = $res1 ? $res1->fetch_all(MYSQLI_ASSOC) : [];

$res2 = $conn->query("SELECT * FROM prescription_items WHERE prescription_id = $rx_id AND TRIM(COALESCE(medicine_name,'')) != '' ORDER BY id ASC");
$items2 = $res2 ? $res2->fetch_all(MYSQLI_ASSOC) : [];

header('Content-Type: application/json');
echo json_encode([
    'all_items' => $items1,
    'filtered_items' => $items2
]);
