<?php
if (!defined('BYPASS_AUTH')) {
    require_once __DIR__ . '/includes/auth.php';
}
else {
    // Called from portal — load DB + esc() helper without auth redirect
    require_once __DIR__ . '/config/db.php';
    if (!function_exists('esc')) {
        function esc($string)
        {
            return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
        }
    }
}
require_once __DIR__ . '/includes/traceability.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0)
    die("Invalid Receipt ID");

// Fetch Receipt Data
$rx = null;
try {
    $stmt = $conn->prepare("
        SELECT r.*, p.first_name, p.last_name, p.mr_number, p.phone, p.cnic,
               h.name as hospital_name, h.logo_path, h.digital_signature_path, h.address, h.phone as hospital_phone
        FROM receipts r
        JOIN patients p ON r.patient_id = p.id
        JOIN hospitals h ON r.hospital_id = h.id
        WHERE r.id = ?
    ");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $rx = $stmt->get_result()->fetch_assoc();
    }
}
catch (Exception $e) {
    die("DB Error");
}

if (!$rx)
    die("Receipt not found.");

// Log download and get tracking code
$tracking_code = log_document_download($conn, 'receipt', $id);

$is_paid = strtolower($rx['status'] ?? '') === 'paid';
$status_label = $is_paid ? 'PAID' : strtoupper($rx['status'] ?? 'PENDING');
$amount_words = ''; // Could add number-to-words conversion here if needed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt RCPT-<?php echo str_pad($id, 6, '0', STR_PAD_LEFT); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        @page { size: A4; margin: 15mm 15mm 20mm 15mm; }
        body {
            background-color: #f1f5f9;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color: #000;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        .a4-container {
            width: 210mm;
            min-height: 297mm;
            background: #fff;
            margin: 0 auto;
            position: relative;
            box-shadow: 0 4px 32px rgba(0,0,0,0.12);
            padding: 15mm 15mm 20mm 15mm;
            box-sizing: border-box;
        }
        @media print {
            body { background: #fff; }
            .a4-container { width: auto; min-height: auto; box-shadow: none; padding: 0; margin: 0; }
            .no-print { display: none !important; }
        }
        .status-paid    { background: #dcfce7; color: #15803d; border: 1.5px solid #86efac; }
        .status-pending { background: #fef9c3; color: #b45309; border: 1.5px solid #fde047; }
        .status-other   { background: #f1f5f9; color: #475569; border: 1.5px solid #cbd5e1; }
        .receipt-divider { border-top: 2px dashed #d1d5db; }
        .watermark-paid {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%) rotate(-30deg);
            font-size: 80px; font-weight: 900; color: rgba(16,185,129,0.07);
            white-space: nowrap; pointer-events: none; user-select: none;
            letter-spacing: 0.2em;
        }
        .traceability-code {
            position: absolute; bottom: 8mm; right: 15mm;
            font-size: 8px; color: #94a3b8; font-family: monospace;
            pointer-events: none; text-transform: uppercase;
        }
    </style>
</head>
<body class="py-10 print:py-0">

    <!-- Toolbar -->
    <div class="flex flex-wrap justify-center gap-3 py-4 mb-6 bg-white border-b border-slate-200 no-print w-full shadow-sm">
        <?php if (isset($_SESSION['admin_id'])): ?>
            <button onclick="window.print()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg shadow font-bold text-sm">
                <i class="fa-solid fa-print mr-1"></i> Print Receipt
            </button>
            <button onclick="sendWhatsApp()" class="bg-[#25D366] hover:bg-[#128C7E] text-white px-6 py-2 rounded-lg shadow font-bold text-sm shadow-green-500/20">
                <i class="fa-brands fa-whatsapp text-base mr-1"></i> Send via WhatsApp
            </button>
            <a href="receipts_add.php?edit=<?php echo $id; ?>" class="bg-slate-700 hover:bg-slate-900 text-white px-5 py-2 rounded-lg shadow font-bold text-sm">
                <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
            </a>
        <?php
else: ?>
            <!-- Patient Portal Controls -->
            <button onclick="window.print()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg shadow font-bold text-sm">
                <i class="fa-solid fa-download mr-1"></i> Download / Print
            </button>
        <?php
endif; ?>
        <button onclick="window.close()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-bold text-sm">Close</button>
    </div>

    <!-- The Actual Document -->
    <div class="a4-container" id="document-container">

        <!-- Paid Watermark -->
        <?php if ($is_paid): ?>
            <div class="watermark-paid">PAID</div>
        <?php
endif; ?>

        <!-- Header -->
        <div class="flex justify-between items-start pb-6 mb-6 border-b-2 border-emerald-800">
            <!-- Logo -->
            <div class="w-2/5">
                <img src="https://ivfexperts.pk/assets/images/logo.png" alt="IVF Experts" class="h-16 object-contain"
                     onerror="this.style.display='none'; document.getElementById('logo-fallback').style.display='block';">
                <div id="logo-fallback" style="display:none;" class="font-extrabold text-xl text-emerald-900 tracking-tight">IVF EXPERTS</div>
                <?php if (!empty($rx['hospital_name'])): ?>
                    <div class="text-xs text-gray-500 mt-1 font-medium"><?php echo esc($rx['hospital_name']); ?></div>
                <?php
endif; ?>
                <?php if (!empty($rx['address'])): ?>
                    <div class="text-[10px] text-gray-400 leading-snug mt-0.5"><?php echo esc($rx['address']); ?></div>
                <?php
endif; ?>
                <?php if (!empty($rx['hospital_phone'])): ?>
                    <div class="text-[10px] text-gray-400 mt-0.5">Tel: <?php echo esc($rx['hospital_phone']); ?></div>
                <?php
endif; ?>
            </div>

            <!-- Title + Status -->
            <div class="text-right">
                <h1 class="font-extrabold text-4xl uppercase tracking-widest text-emerald-900 leading-none">Receipt</h1>
                <div class="mt-2 inline-block">
                    <span class="text-xs font-black uppercase tracking-widest px-3 py-1 rounded-full <?php echo $is_paid ? 'status-paid' : (strtolower($rx['status'] ?? '') === 'pending' ? 'status-pending' : 'status-other'); ?>">
                        <?php echo $status_label; ?>
                    </span>
                </div>
                <div class="text-[11px] text-gray-500 mt-2 font-mono font-bold">RCPT-<?php echo str_pad($id, 6, '0', STR_PAD_LEFT); ?></div>
                <div class="text-[11px] text-gray-700 mt-0.5">Date: <strong><?php echo date('d M Y', strtotime($rx['receipt_date'])); ?></strong></div>
            </div>
        </div>

        <!-- Billed To -->
        <div class="flex justify-between mb-8 gap-6">
            <div class="flex-1">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Billed To</p>
                <p class="text-xl font-black text-gray-900 leading-tight"><?php echo esc($rx['first_name'] . ' ' . $rx['last_name']); ?></p>
                <p class="text-xs text-gray-500 font-mono mt-1">MR #: <span class="font-bold text-indigo-700"><?php echo esc($rx['mr_number']); ?></span></p>
                <?php if (!empty($rx['phone'])): ?>
                    <p class="text-xs text-gray-500 mt-0.5"><i class="fa-solid fa-phone text-[9px] mr-1"></i><?php echo esc($rx['phone']); ?></p>
                <?php
endif; ?>
            </div>
            <div class="shrink-0 text-right">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Receipt Details</p>
                <p class="text-sm text-gray-700">Payment Date: <strong><?php echo date('d M Y', strtotime($rx['receipt_date'])); ?></strong></p>
                <?php if (!empty($rx['payment_method'])): ?>
                    <p class="text-sm text-gray-700">Method: <strong><?php echo esc($rx['payment_method']); ?></strong></p>
                <?php
endif; ?>
            </div>
        </div>

        <!-- Line Items -->
        <table class="w-full mb-0 text-sm">
            <thead>
                <tr style="background: #064e3b; color: white;">
                    <th class="text-left py-3 px-4 font-bold uppercase text-xs tracking-wider w-3/4">Description</th>
                    <th class="text-right py-3 px-4 font-bold uppercase text-xs tracking-wider w-1/4">Amount (Rs)</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-gray-100">
                    <td class="py-5 px-4">
                        <div class="font-bold text-base text-gray-900"><?php echo esc($rx['procedure_name']); ?></div>
                        <?php if (!empty($rx['notes'])): ?>
                            <div class="text-xs text-gray-500 mt-1 italic"><?php echo esc($rx['notes']); ?></div>
                        <?php
endif; ?>
                    </td>
                    <td class="py-5 px-4 text-right font-mono text-gray-900 font-bold text-base">
                        <?php echo number_format($rx['amount'], 2); ?>
                    </td>
                </tr>
                <!-- Empty spacer rows for authenticity -->
                <tr class="border-b border-dashed border-gray-100"><td class="py-2 px-4 text-xs text-gray-200">—</td><td></td></tr>
                <tr class="border-b border-dashed border-gray-100"><td class="py-2 px-4 text-xs text-gray-200">—</td><td></td></tr>
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-emerald-800">
                    <td class="pt-4 px-4 font-black text-emerald-900 uppercase tracking-wider text-base">Total Amount</td>
                    <td class="pt-4 px-4 text-right font-mono font-black text-emerald-900 text-2xl">
                        Rs.&nbsp;<?php echo number_format($rx['amount'], 2); ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="px-4 pb-4 pt-1 text-right text-[10px] text-gray-400 uppercase tracking-widest font-bold">
                        <?php echo $is_paid ? 'Payment Received — Thank you for trusting IVF Experts.' : 'Amount Due — Please settle at earliest convenience.'; ?>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Dashed divider -->
        <div class="receipt-divider my-6"></div>

        <!-- Footer: QR + Signature -->
        <div class="flex justify-between items-end">

            <!-- QR Code -->
            <div class="flex items-center gap-3">
                <?php if (!empty($rx['qrcode_hash'])): ?>
                    <div class="border border-gray-200 p-1 rounded bg-white shadow-sm">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?php echo urlencode('https://patient.ivfexperts.pk/verify.php?hash=' . $rx['qrcode_hash'] . '&type=receipt'); ?>"
                             alt="QR Code" class="w-16 h-16" />
                    </div>
                    <div class="text-[9px] text-gray-500 leading-snug w-36">
                        <span class="font-bold block text-gray-700 text-[10px] mb-0.5">Verify Online</span>
                        Scan with phone camera to view &amp; download your digital receipt at ivfexperts.pk.
                    </div>
                <?php
endif; ?>
            </div>

            <!-- Signature block -->
            <div class="text-right">
                <?php if (!empty($rx['digital_signature_path'])): ?>
                    <img src="https://ivfexperts.pk/<?php echo esc($rx['digital_signature_path']); ?>" alt="Signature" class="h-12 ml-auto object-contain mb-1" />
                <?php
else: ?>
                    <div class="h-12"></div>
                <?php
endif; ?>
                <div class="border-t-2 border-gray-700 pt-2 text-[10px] text-gray-700 font-bold leading-snug text-right">
                    Authorised Signatory<br>
                    <span class="text-[9px] font-normal text-gray-500">IVF Experts Clinic</span>
                </div>
            </div>

        </div>

        <!-- Bottom note -->
        <div class="mt-6 pt-4 border-t border-gray-100 text-center text-[9px] text-gray-400 tracking-widest uppercase">
            This is a computer-generated receipt and is valid without a physical signature.
            &nbsp;|&nbsp; hello@ivfexperts.pk &nbsp;|&nbsp; www.ivfexperts.pk
            <!-- Traceability Code -->
            <?php if (!empty($tracking_code)): ?>
                <div class="traceability-code">TRK-<?php echo $tracking_code; ?></div>
            <?php
endif; ?>
        </div>

    </div>

    <script>
        function sendWhatsApp() {
            let phone = "<?php echo esc($rx['phone']); ?>";
            phone = phone.replace(/\D/g, '');

            if (!phone || phone.length < 10) {
                let manual = prompt("Patient phone missing. Enter a valid number (e.g. 923111101483):", "92");
                if (!manual) return;
                phone = manual.replace(/\D/g, '');
            } else if (phone.startsWith('03')) {
                phone = '92' + phone.substring(1);
            }

            const hash = "<?php echo $rx['qrcode_hash']; ?>";
            const name = "<?php echo esc($rx['first_name'] . ' ' . $rx['last_name']); ?>";
            const amount = "Rs. <?php echo number_format($rx['amount'], 2); ?>";
            const link = "https://patient.ivfexperts.pk/verify.php?hash=" + hash + "&type=receipt";
            const rcpt = "RCPT-<?php echo str_pad($id, 6, '0', STR_PAD_LEFT); ?>";

            const text = `Dear ${name},\n\nThank you for your payment at IVF Experts.\n\nReceipt No.: ${rcpt}\nProcedure: <?php echo esc($rx['procedure_name']); ?>\nAmount: ${amount}\nStatus: <?php echo $status_label; ?>\n\nView & download your digital receipt here:\n${link}\n\nFor any queries, contact us at hello@ivfexperts.pk or +92 3 111 101 483.\n\nBest regards,\nDr. Adnan Jabbar — IVF Experts`;

            window.open(`https://wa.me/${phone}?text=${encodeURIComponent(text)}`, '_blank');
        }
    </script>
</body>
</html>
