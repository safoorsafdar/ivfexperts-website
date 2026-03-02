<?php
define('BYPASS_AUTH', true);
try {
    require_once __DIR__ . '/admin/includes/auth.php';
    echo "<h1>Table: advised_procedures</h1><pre>";
    $res = $conn->query("DESCRIBE advised_procedures");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            print_r($row);
        }
    }
}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage();
}
?>
