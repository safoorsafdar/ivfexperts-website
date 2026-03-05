<?php
define('BYPASS_AUTH', true);
require 'includes/auth.php';
$_GET['id'] = 1;

$rx_id = 1;
$trace = [];

$items_res = $conn->query("SELECT * FROM prescription_items WHERE prescription_id = $rx_id AND TRIM(COALESCE(medicine_name,'')) != '' ORDER BY id ASC");
$items = $items_res ? $items_res->fetch_all(MYSQLI_ASSOC) : [];
$trace[] = "After query: count=" . count($items);

// include header? Let's exactly copy
require_once __DIR__ . '/includes/auth.php';

// Check if a POST was accidentally triggered?
$trace[] = "Is POST? " . ($_SERVER['REQUEST_METHOD'] === 'POST' ? 'Yes' : 'No');

// Let's include header
ob_start();
include __DIR__ . '/includes/header.php';
ob_get_clean();

$trace[] = "After header.php: count=" . count($items);

header('Content-Type: application/json');
echo json_encode([
    'trace' => $trace,
    'final_items' => $items
]);
