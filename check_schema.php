<?php
define('BYPASS_AUTH', true);
try {
    require_once __DIR__ . '/admin/includes/auth.php';
    $res = $conn->query("DESCRIBE hospitals");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            print_r($row);
        }
    }
    else {
        echo "Query failed but no exception thrown.\n";
    }
}
catch (Exception $e) {
    echo "Caught exception: " . $e->getMessage() . "\n";
}
?>
