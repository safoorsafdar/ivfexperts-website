<?php
header('Content-Type: application/json');
echo json_encode([
    'patients_view_md5_local' => md5_file('e:\Github\ivfexperts\4me\patients_view.php'),
    'patients_view_md5_server' => md5_file('patients_view.php'),
    'header_md5_local' => md5_file('e:\Github\ivfexperts\4me\includes\header.php'),
    'header_md5_server' => md5_file('includes/header.php'),
]);
