<?php
require_once __DIR__ . '/includes/auth.php';

$asset_id = intval($_GET['id'] ?? 0);
if ($asset_id <= 0) {
    die("Invalid asset ID.");
}

$stmt = $conn->prepare("SELECT * FROM assets WHERE id = ?");
$stmt->bind_param("i", $asset_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    die("Asset not found.");
}

$barcode = $item['barcode_string'] ?: 'ASSET-' . $item['id'];
$qr_api = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($barcode);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Label: <?php echo htmlspecialchars($item['name']); ?></title>
    <style>
        @page {
            size: 2in 1.5in;
            margin: 0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Arial', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #f3f4f6;
        }
        .label-card {
            width: 2in;
            height: 1.5in;
            background: #fff;
            border: 1px solid #e5e7eb;
            padding: 4px 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
            page-break-after: always;
        }
        .label-card .qr {
            width: 70px;
            height: 70px;
        }
        .label-card .name {
            font-size: 7pt;
            font-weight: bold;
            text-align: center;
            line-height: 1.2;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .label-card .barcode-text {
            font-size: 6pt;
            font-family: 'Courier New', monospace;
            color: #6b7280;
            letter-spacing: 0.5px;
        }
        .label-card .meta {
            font-size: 5pt;
            color: #9ca3af;
        }
        .no-print {
            margin-top: 20px;
            text-align: center;
        }
        .no-print button {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
        }
        .no-print button:hover {
            background: #4338ca;
        }

        @media print {
            body { background: white; min-height: auto; }
            .no-print { display: none !important; }
            .label-card { border: none; }
        }
    </style>
</head>
<body>

<div>
    <div class="label-card">
        <img class="qr" src="<?php echo $qr_api; ?>" alt="QR Code">
        <div class="name"><?php echo htmlspecialchars($item['name']); ?></div>
        <div class="barcode-text"><?php echo htmlspecialchars($barcode); ?></div>
        <div class="meta">IVF Experts • <?php echo htmlspecialchars($item['type']); ?> <?php echo !empty($item['location']) ? '• ' . htmlspecialchars($item['location']) : ''; ?></div>
    </div>

    <div class="no-print">
        <button onclick="window.print()"><i class="fa-solid fa-print"></i> Print Label (2" × 1.5")</button>
        <p style="margin-top:10px; font-size:12px; color:#9ca3af;">Formatted for thermal label printers. Use borderless printing.</p>
    </div>
</div>

</body>
</html>
