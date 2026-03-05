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
// Centralized error logging and exception handling
require_once __DIR__ . '/error_handler.php';
// Load shared utility helpers (formatDate, badge, emptyState, paginate, db_select, etc.)
require_once __DIR__ . '/helpers.php';

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

// CSRF protection helpers
// Usage: echo csrf_token() in forms as a hidden field value
//        Call csrf_check() at the top of every POST handler
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        return;
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;padding:2rem;color:#c00;">⚠️ Invalid security token. Please go back and try again.</div>');
    }
}
?>
