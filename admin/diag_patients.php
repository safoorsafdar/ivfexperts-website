<?php
define('BYPASS_AUTH', true);
require_once __DIR__ . '/includes/auth.php';
header('Content-Type: text/html; charset=utf-8');
echo "<h1>Diagnostic: Patients Table Schema</h1>";
echo "<pre>";
try {
    $res = $conn->query("DESCRIBE patients");
    if ($res) {
        echo "Successfully fetched schema for 'patients':\n";
        while ($row = $res->fetch_assoc()) {
            print_r($row);
        }
    }
    else {
        echo "Error: Could not DESCRIBE patients. " . $conn->error . "\n";
    }
}
catch (Throwable $e) {
    echo "Caught Exception: " . $e->getMessage() . "\n";
}
echo "</pre>";
?>
