<?php
/**
 * WhatsApp URL helper
 * Returns a wa.me deep-link with a URL-encoded pre-filled message.
 *
 * Usage:
 *   require_once __DIR__ . '/wa.php';         // or dirname(__DIR__) . '/includes/wa.php'
 *   echo waLink('Hi, I want to book a consultation about IVF.');
 *
 * @param string $message Optional pre-filled message. Falls back to default.
 * @return string Full wa.me URL with encoded text parameter.
 */
function waLink(string $message = ''): string {
    $phone = '923111101483';
    if ($message === '') {
        $message = 'Hi Dr. Adnan, I would like to book a fertility consultation.';
    }
    return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
}
