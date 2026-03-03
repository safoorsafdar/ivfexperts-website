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

// Flash message system — call flash('Type', 'Message') before a redirect
// Types: success | error | warning | info
function flash(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function get_flash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}
?>
