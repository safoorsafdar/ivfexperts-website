<?php
header('Content-Type: application/json');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$source = isset($_POST['source']) ? trim($_POST['source']) : 'footer';

// Validate email
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

// Sanitize source (only allow alphanumeric + underscores)
$source = preg_replace('/[^a-z0-9_]/', '', strtolower($source));
if (!$source) {
    $source = 'footer';
}

require_once __DIR__ . '/config/db.php';

// Insert with duplicate handling — if already subscribed, silently succeed
$stmt = $conn->prepare(
    'INSERT INTO newsletter_subscribers (email, source) VALUES (?, ?)
     ON DUPLICATE KEY UPDATE is_active = 1, source = VALUES(source)'
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again.']);
    exit;
}

$stmt->bind_param('ss', $email, $source);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true, 'message' => 'You\'re subscribed! Thank you.']);
