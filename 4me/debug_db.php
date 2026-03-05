<?php
define('BYPASS_AUTH', true);
require 'includes/auth.php';

$res = $conn->query("SELECT prescription_id, count(*) as blank_meds FROM prescription_items WHERE TRIM(COALESCE(medicine_name,'')) = '' GROUP BY prescription_id ORDER BY prescription_id DESC LIMIT 5");
$blanks = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

$res2 = $conn->query("SELECT prescription_id, count(*) as count_items FROM prescription_items GROUP BY prescription_id ORDER BY prescription_id DESC LIMIT 5");
$totals = $res2 ? $res2->fetch_all(MYSQLI_ASSOC) : [];

header('Content-Type: application/json');
echo json_encode([
    'prescriptions_with_blanks' => $blanks,
    'recent_prescriptions' => $totals
]);
