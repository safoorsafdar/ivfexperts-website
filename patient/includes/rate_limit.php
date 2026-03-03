<?php
/**
 * Simple session-based rate limiter for login/verify forms.
 * Limits to $max_attempts attempts per $window_seconds.
 */
function check_rate_limit(string $key, int $max_attempts = 5, int $window_seconds = 300): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $session_key = 'rl_' . $key;
    $now = time();

    if (!isset($_SESSION[$session_key])) {
        $_SESSION[$session_key] = ['count' => 0, 'window_start' => $now];
    }

    $rl = & $_SESSION[$session_key];

    // Reset window if expired
    if ($now - $rl['window_start'] > $window_seconds) {
        $rl = ['count' => 0, 'window_start' => $now];
    }

    $rl['count']++;

    if ($rl['count'] > $max_attempts) {
        return false; // Blocked
    }
    return true; // Allowed
}

function get_rate_limit_remaining(string $key, int $window_seconds = 300): int
{
    $session_key = 'rl_' . $key;
    if (!isset($_SESSION[$session_key]))
        return $window_seconds;
    return max(0, $window_seconds - (time() - $_SESSION[$session_key]['window_start']));
}
