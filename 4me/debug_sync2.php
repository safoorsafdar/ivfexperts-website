<?php
header('Content-Type: text/plain');
$content = file_get_contents('prescriptions_edit.php');

if (strpos($content, 'for($i=$med_count; $i<8; $i++)') !== false) {
    echo "BUG FOUND: Server is running the OLD version of prescriptions_edit.php with 8 hardcoded rows!\n\n";
}
else {
    echo "Server is running a version WITHOUT the 8 hardcoded rows.\n\n";
}

if (strpos($content, '$items_for_js = array_values(') !== false) {
    echo "Found \$items_for_js (from Phase 4 rewrite).\n";
}
else {
    echo "Did NOT find \$items_for_js.\n";
}

echo "\n--- First 500 characters ---\n";
echo substr($content, 0, 500);
