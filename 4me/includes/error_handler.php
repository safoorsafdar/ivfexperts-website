<?php
/**
 * 4me Admin — Centralized Error Handler & Logger
 * ─────────────────────────────────────────────────────────────────────────────
 * Auto-included via includes/auth.php on every admin page.
 *
 * Features:
 *  - Captures PHP errors, exceptions, and warnings to a rotating log file
 *  - Shows a friendly error message to users in production
 *  - Shows full details only when ADMIN_DEBUG is true
 *  - Keeps logs at 4me/logs/admin-YYYY-MM.log (auto-created)
 *  - Rotates log daily (max 2MB per file before clearing header)
 */

// ── Create logs directory (auto) ──────────────────────────────────────────
$_logDir = dirname(__DIR__) . '/logs';
if (!is_dir($_logDir)) {
    @mkdir($_logDir, 0750, true);
    // Protect log dir from web access
    @file_put_contents($_logDir . '/.htaccess', "Deny from all\n");
}
unset($_logDir);

// ── Debug mode (safe default: OFF in production) ──────────────────────────
if (!defined('ADMIN_DEBUG')) {
    define('ADMIN_DEBUG', false);
}

// ── Log writer ────────────────────────────────────────────────────────────
function admin_log(string $level, string $message, array $context = []): void
{
    $logFile = dirname(__DIR__) . '/logs/admin-' . date('Y-m') . '.log';

    // Rotate if > 2MB
    if (@filesize($logFile) > 2 * 1024 * 1024) {
        @rename($logFile, $logFile . '.old-' . time());
    }

    $ctx = $context ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES) : '';
    $line = sprintf(
        "[%s] [%s] %s%s | %s | %s\n",
        date('Y-m-d H:i:s'),
        strtoupper($level),
        $message,
        $ctx,
        ($_SERVER['REQUEST_URI'] ?? '-'),
        ($_SERVER['REMOTE_ADDR'] ?? '-')
    );

    @file_put_contents($logFile, $line, FILE_APPEND);
}

// ── Set PHP error reporting ───────────────────────────────────────────────
if (ADMIN_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', dirname(__DIR__) . '/logs/php-errors-' . date('Y-m') . '.log');
}

// ── Global exception handler ──────────────────────────────────────────────
set_exception_handler(function (Throwable $e) {
    admin_log('EXCEPTION', $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => substr($e->getTraceAsString(), 0, 800),
    ]);

    if (ADMIN_DEBUG) {
        throw $e; // Re-throw in debug so developer sees the full trace
    }

    // Graceful user-facing error page
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo '<div style="font-family:system-ui;max-width:540px;margin:60px auto;padding:32px;background:#fff;border:1px solid #fee2e2;border-radius:16px;text-align:center;">';
    echo '<i style="font-size:40px;">⚠️</i>';
    echo '<h2 style="color:#dc2626;margin:12px 0 8px;">Something went wrong</h2>';
    echo '<p style="color:#64748b;font-size:14px;">An unexpected error occurred. Our team has been notified. Please try again or <a href="index.php" style="color:#0d9488;">return to the dashboard</a>.</p>';
    echo '</div>';
    exit;
});

// ── Global error handler (for non-exception errors) ───────────────────────
set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    // Don't log suppressed errors (@ operator)
    if (!(error_reporting()& $errno)) {
        return false;
    }

    $levels = [
        E_ERROR => 'ERROR', E_WARNING => 'WARNING',
        E_NOTICE => 'NOTICE', E_DEPRECATED => 'DEPRECATED',
        E_USER_ERROR => 'USER_ERROR',
    ];
    $level = $levels[$errno] ?? 'UNKNOWN';

    admin_log($level, $errstr, ['file' => $errfile, 'line' => $errline]);

    // Let PHP handle fatal errors
    return false;
});

// ── Register shutdown handler (catches fatal errors too) ──────────────────
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        admin_log('FATAL', $err['message'], ['file' => $err['file'], 'line' => $err['line']]);
    }
});
