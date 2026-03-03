<?php
// portal/includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$IDLE_TIMEOUT = 3600; // 1 hour in seconds
$ABSOLUTE_EXPIRY = 43200; // 12 hours in seconds
$now = time();

if (isset($_SESSION['portal_patient_id'])) {
    // 1. Check Idle Timeout
    if (isset($_SESSION['last_activity']) && ($now - $_SESSION['last_activity']) > $IDLE_TIMEOUT) {
        unset($_SESSION['portal_patient_id']);
        header("Location: index.php?expired=1");
        exit;
    }
    $_SESSION['last_activity'] = $now;

    // 2. Check Absolute Expiry
    if (isset($_SESSION['session_start']) && ($now - $_SESSION['session_start']) > $ABSOLUTE_EXPIRY) {
        unset($_SESSION['portal_patient_id']);
        header("Location: index.php?expired=1");
        exit;
    }
}

if (!isset($_SESSION['portal_patient_id'])) {
    header("Location: index.php");
    exit;
}

require_once dirname(__DIR__, 2) . '/4me/config/db.php';
$patient_id = intval($_SESSION['portal_patient_id']);
?>
