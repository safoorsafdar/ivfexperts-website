<?php
// includes/auth.php
// Include this at the TOP of every protected admin page

session_start();

if (!defined('BYPASS_AUTH') || BYPASS_AUTH !== true) {
    if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_username'])) {
        header("Location: login.php");
        exit;
    }
}

// Pass global config
require_once dirname(__DIR__) . '/config/db.php';
// Provide standard function for sanitizing output
function esc($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>