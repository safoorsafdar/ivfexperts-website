<?php
// portal/includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['portal_patient_id'])) {
    header("Location: index.php");
    exit;
}

require_once dirname(__DIR__, 2) . '/4me/config/db.php';
$patient_id = intval($_SESSION['portal_patient_id']);
?>
