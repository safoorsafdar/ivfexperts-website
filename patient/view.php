<?php
session_start();
if (!isset($_SESSION['portal_patient_id'])) {
    // Preserve hash and type for post-login redirect
    $h = preg_replace('/[^a-f0-9]/i', '', $_GET['hash'] ?? '');
    $t = in_array($_GET['type'] ?? '', ['rx','sa','usg','receipt']) ? $_GET['type'] : 'rx';
    header("Location: verify.php?hash={$h}&type={$t}");
    exit;
}

$type = $_GET['type'] ?? '';
$hash = $_GET['hash'] ?? '';
$patient_id = intval($_SESSION['portal_patient_id']);

require_once dirname(__DIR__) . '/4me/config/db.php';

// Route and Verify
$doc_id = 0;
$script = '';

if ($type === 'sa') {
    $stmt = $conn->prepare("SELECT id FROM semen_analyses WHERE qrcode_hash = ? AND patient_id = ?");
    $stmt->bind_param("si", $hash, $patient_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $doc_id = $res->fetch_assoc()['id'];
        $script = '../4me/semen_analyses_print.php';
    }
}
elseif ($type === 'usg') {
    $stmt = $conn->prepare("SELECT id FROM patient_ultrasounds WHERE qrcode_hash = ? AND patient_id = ?");
    $stmt->bind_param("si", $hash, $patient_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $doc_id = $res->fetch_assoc()['id'];
        $script = '../4me/ultrasounds_print.php';
    }
}
elseif ($type === 'rx') {
    $stmt = $conn->prepare("SELECT id FROM prescriptions WHERE qrcode_hash = ? AND patient_id = ?");
    $stmt->bind_param("si", $hash, $patient_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $doc_id = $res->fetch_assoc()['id'];
        $script = '../4me/prescriptions_print.php';
    }
}
elseif ($type === 'receipt') {
    $stmt = $conn->prepare("SELECT id FROM receipts WHERE qrcode_hash = ? AND patient_id = ?");
    $stmt->bind_param("si", $hash, $patient_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $doc_id = $res->fetch_assoc()['id'];
        $script = '../4me/receipts_print.php';
    }
}

if ($doc_id > 0 && !empty($script)) {
    // Setup environment for the admin script
    define('BYPASS_AUTH', true);
    $_GET['id'] = $doc_id; // Fake the ID parameter

    // Execute the layout script
    include $script;
}
else {
    // Clean error page
    ?>
    <!DOCTYPE html>
    <html lang="en"><head><meta charset="UTF-8"><title>Not Found — IVF Experts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>body{background:linear-gradient(135deg,#0f172a,#1e1b4b,#1e3a5f);font-family:system-ui,sans-serif;}</style>
    </head>
    <body class="min-h-screen flex items-center justify-center p-6">
    <div class="bg-white/10 backdrop-blur border border-white/10 rounded-3xl p-10 max-w-sm w-full text-center">
        <div class="w-16 h-16 bg-rose-500/20 rounded-2xl flex items-center justify-center mx-auto mb-5">
            <i class="fa-solid fa-file-circle-xmark text-rose-400 text-2xl"></i>
        </div>
        <h2 class="text-xl font-black text-white mb-2">Document Not Found</h2>
        <p class="text-white/50 text-sm mb-6">This document was not found or you do not have permission to view it.</p>
        <a href="dashboard.php" class="block bg-indigo-600 text-white py-3.5 rounded-2xl font-black text-sm hover:bg-indigo-500 transition-all">
            ← Back to Dashboard
        </a>
    </div>
    </body></html>
    <?php
}
