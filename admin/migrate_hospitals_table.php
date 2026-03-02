<?php
require_once __DIR__ . '/includes/auth.php';

echo "<h1>Updating Hospitals Table Schema</h1>";
echo "<pre>";

$columns_to_add = [
    "address TEXT AFTER name",
    "phone VARCHAR(50) AFTER address"
];

foreach ($columns_to_add as $col) {
    preg_match('/^(\w+)/', $col, $matches);
    $colName = $matches[1];

    // Check if column exists
    $check = $conn->query("SHOW COLUMNS FROM hospitals LIKE '$colName'");
    if ($check->num_rows == 0) {
        $sql = "ALTER TABLE hospitals ADD $col";
        if ($conn->query($sql)) {
            echo "Added column: <b>$colName</b><br>";
        }
        else {
            echo "<span style='color:red;'>Error adding $colName: " . $conn->error . "</span><br>";
        }
    }
    else {
        echo "Column <b>$colName</b> already exists.<br>";
    }
}

echo "<br><h2 style='color:green;'>Migration Complete!</h2>";
echo "You can now Edit/Save hospital contact details.";
echo "</pre>";
?>
