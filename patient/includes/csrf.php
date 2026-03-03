<?php
/**
 * CSRF Protection Helper
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate a CSRF token and store it in the session
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify a CSRF token from POST request
 */
function csrf_verify(): bool
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        return true;
    $token = $_POST['_csrf'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
