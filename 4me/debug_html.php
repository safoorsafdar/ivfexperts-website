<?php
header('Content-Type: application/json');
echo json_encode([
    'local_md5' => 'F63AB01D450964DC2F9B8CC382FE9DE6', // from my local machine
    'server_md5' => md5_file('prescriptions_edit.php')
]);
