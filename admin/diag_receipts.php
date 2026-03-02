<?php
require_once __DIR__ . '/includes/auth.php';
header('Content-Type: text/html; charset=utf-8');
echo "<h1>Diagnostic: Receipts Table Schema</h1>";
echo "<pre>";
try {
    $res = $conn->query("DESCRIBE receipts");
    if ($res) {
        echo "Successfully fetched schema for 'receipts':\n";
        while ($row = $res->fetch_assoc()) {
            print_r($row);
        }
    }
    else {
        echo "Error: Could not DESCRIBE receipts. " . $conn->error . "\n";
    }
}
catch (Throwable $e) {
    echo "Caught Exception: " . $e->getMessage() . "\n";
}
echo "</pre>";
?>
