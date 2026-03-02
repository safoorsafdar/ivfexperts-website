<?php
define('BYPASS_AUTH', true);
try {
    require_once __DIR__ . '/admin/includes/auth.php';
    $res = $conn->query("DESCRIBE semen_analyses");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            print_r($row);
        }
    }
}
catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
