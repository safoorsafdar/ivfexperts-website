<?php
/**
 * prescriptions_print.php
 * Smart paginated prescription print with:
 *  - Repeating header (patient demographics) on every page via <thead>
 *  - Repeating footer (doctor signature + traceability) on every page via <tfoot>
 *  - CSS @page overrides browser/printer margin settings
 *  - JavaScript content paginator for very long prescriptions
 *  - Digital letterhead injected per-page in digital print mode
 */

if (!defined('BYPASS_AUTH')) {
    require_once __DIR__ . '/includes/auth.php';
}
else {
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
    die("Invalid Prescription ID");

// ── Fetch Prescription ────────────────────────────────────────────────────────
$rx = null;
try {
    $stmt = $conn->prepare("
        SELECT rx.*,
               p.first_name, p.last_name, p.mr_number, p.gender, p.phone, p.cnic,
               p.patient_age, p.blood_group,
               COALESCE(h.name, 'IVF Experts Clinic') AS hospital_name,
               COALESCE(h.margin_top,    '40mm') AS margin_top,
               COALESCE(h.margin_bottom, '30mm') AS margin_bottom,
               COALESCE(h.margin_left,   '20mm') AS margin_left,
               COALESCE(h.margin_right,  '20mm') AS margin_right,
               h.digital_signature_path,
               h.letterhead_image_path
        FROM prescriptions rx
        JOIN patients p ON rx.patient_id = p.id
        LEFT JOIN hospitals h ON p.referring_hospital_id = h.id
        WHERE rx.id = ?
    ");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $rx = $stmt->get_result()->fetch_assoc();
    }
}
catch (Exception $e) {
    die("DB Error: " . $e->getMessage());
}
if (!$rx)
    die("Prescription not found. ID=$id");

$tracking_code = log_document_download($conn, 'rx', $id);

// ── Fetch Items ───────────────────────────────────────────────────────────────
$items = [];
try {
    $stmt = $conn->prepare(
        "SELECT pi.*, COALESCE(m.formula,'') AS formula, COALESCE(m.med_type,'') AS med_type
         FROM prescription_items pi
         LEFT JOIN medications m ON m.name = pi.medicine_name
         WHERE pi.prescription_id = ? AND TRIM(COALESCE(pi.medicine_name,'')) != ''
         ORDER BY pi.id ASC"
    );
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
catch (Exception $e) {
}

// ── Fetch Diagnoses ───────────────────────────────────────────────────────────
$diagnoses = [];
try {
    $chk = $conn->query("SHOW TABLES LIKE 'prescription_diagnoses'");
    if ($chk && $chk->num_rows > 0) {
        $stmt = $conn->prepare("SELECT * FROM prescription_diagnoses WHERE prescription_id = ? ORDER BY type ASC");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $diagnoses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
    }
    if (empty($diagnoses) && !empty($rx['icd10_codes'])) {
        $icd_arr = json_decode($rx['icd10_codes'], true);
        if (is_array($icd_arr)) {
            foreach ($icd_arr as $icd) {
                $diagnoses[] = ['type' => 'ICD', 'code' => $icd['icd10_code'] ?? '', 'description' => $icd['description'] ?? ''];
            }
        }
    }
}
catch (Exception $e) {
}

// ── Fetch Lab Tests ───────────────────────────────────────────────────────────
$lab_tests = [];
try {
    $chk = $conn->query("SHOW TABLES LIKE 'advised_lab_tests'");
    if ($chk && $chk->num_rows > 0) {
        $stmt = $conn->prepare(
            "SELECT alt.*, ltd.test_name, ltd.category
             FROM advised_lab_tests alt
             LEFT JOIN lab_tests_directory ltd ON alt.test_id = ltd.id
             WHERE alt.prescription_id = ? ORDER BY alt.id ASC"
        );
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $lab_tests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
    }
}
catch (Exception $e) {
}

$patient_tests = array_filter($lab_tests, fn($t) => ($t['record_for'] ?? $t['advised_for'] ?? '') !== 'Spouse');
$spouse_tests = array_filter($lab_tests, fn($t) => ($t['record_for'] ?? $t['advised_for'] ?? '') === 'Spouse');
$icds = array_filter($diagnoses, fn($d) => $d['type'] === 'ICD');
$cpts = array_filter($diagnoses, fn($d) => in_array($d['type'], ['CPT', 'SNOMED']));

// ── Margins from DB ───────────────────────────────────────────────────────────
$mt = $rx['margin_top'] ?: '40mm';
$mb = $rx['margin_bottom'] ?: '30mm';
$ml = $rx['margin_left'] ?: '20mm';
$mr = $rx['margin_right'] ?: '20mm';

// Make sure margin values end in mm
if (is_numeric($mt))
    $mt .= 'mm';
if (is_numeric($mb))
    $mb .= 'mm';
if (is_numeric($ml))
    $ml .= 'mm';
if (is_numeric($mr))
    $mr .= 'mm';

$has_letterhead = !empty($rx['letterhead_image_path']);
$letterhead_url = $has_letterhead ? 'https://ivfexperts.pk/' . addslashes($rx['letterhead_image_path']) : '';
$is_admin = isset($_SESSION['admin_id']);
$digital_auto = !$is_admin && $has_letterhead; // patient portal gets letterhead always

// QR + tracking
$qr_hash = $rx['qrcode_hash'] ?? '';
$qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=56x56&data=' . urlencode('https://patient.ivfexperts.pk/verify.php?hash=' . $qr_hash);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription #<?php echo $id; ?> — <?php echo esc($rx['first_name'] . ' ' . $rx['last_name']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!--
    ══════════════════════════════════════════════════════════
    PRINT SETTINGS — these override browser & printer defaults
    ══════════════════════════════════════════════════════════
    -->
    <style>
        /* ── Reset & document base ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        @font-face {
            font-family: 'PrintSans';
            src: local('Helvetica Neue'), local('Helvetica'), local('Arial');
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #000;
            background: #e5e7eb;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* ══════════════════════════════════════════════
           @PAGE RULES — override EVERYTHING the printer
           thinks it knows about margins & headers
           ══════════════════════════════════════════════ */
        @page {
            size: A4 portrait;
            margin: 0 !important; /* Forces edge-to-edge printing for letterhead background */
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            /* Suppress browser default "URL / date" headers and footers */
            @top-center    { content: none; }
            @top-left      { content: none; }
            @top-right     { content: none; }
            @bottom-center { content: none; }
            @bottom-left   { content: none; }
            @bottom-right  { content: none; }
        }

        /* ── Screen: show as A4 card ── */
        .rx-page {
            width: 210mm;
            min-height: 297mm;
            background: #fff;
            margin: 0 auto 20px auto;
            box-shadow: 0 4px 32px rgba(0,0,0,0.15);
            position: relative;
            box-sizing: border-box;
            /* Apply hospital margins as padding to strictly contain text.
               This applies to BOTH screen view and print mode! */
            padding: <?php echo $mt; ?> <?php echo $mr; ?> <?php echo $mb; ?> <?php echo $ml; ?>;
        }

        /* ── Print: each rx-page = one physical page ── */
        @media print {
            html, body { background: transparent !important; margin: 0 !important; padding: 0 !important; }
            .rx-page {
                width: 210mm;
                min-height: 297mm;
                margin: 0;
                box-shadow: none;
                page-break-after: always;
                page-break-inside: avoid;
            }
            .rx-page:last-child { page-break-after: avoid; }
            .no-print { display: none !important; }
        }

        /* ── Layout table inside each page ── */
        .rx-layout-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            position: relative;
            z-index: 10; /* Keep text above the letterhead background */
        }

        /* ── Repeating HEADER on every page ── */
        .rx-layout-table thead tr td { padding: 0; }
        .rx-header-cell {
            padding: 12px 16px 8px 16px;
            border-bottom: 2px solid #d1d5db;
        }

        /* ── Repeating FOOTER on every page ── */
        .rx-layout-table tfoot tr td { padding: 0; }
        .rx-footer-cell {
            padding: 8px 16px 12px 16px;
            border-top: 1px solid #d1d5db;
        }

        /* ── Body content area ── */
        .rx-body-cell { padding: 8px 16px; vertical-align: top; }

        /* ── Content sections ── */
        .section { margin-bottom: 10px; }
        .section-heading {
            display: flex; align-items: center; gap: 6px;
            font-size: 9px; font-weight: 800; text-transform: uppercase;
            letter-spacing: 1.5px; color: #6b7280;
            background: #f3f4f6; border: 1px solid #e5e7eb;
            padding: 4px 8px; margin-bottom: 4px;
        }

        /* ── Medication table ── */
        .med-table { width: 100%; border-collapse: collapse; font-size: 11px; }
        .med-table th {
            background: #f3f4f6; font-size: 9px; text-transform: uppercase;
            letter-spacing: 1px; color: #374151; font-weight: 700;
            padding: 4px 6px; border: 1px solid #d1d5db; text-align: left;
        }
        .med-table td { padding: 5px 6px; border: 1px solid #e5e7eb; vertical-align: top; }
        .med-table tr:nth-child(even) td { background: #f9fafb; }
        .med-name { font-weight: 700; color: #111827; font-size: 11px; text-transform: uppercase; }
        .med-formula { font-size: 9px; color: #6366f1; margin-top: 1px; }
        .med-freq { font-weight: 700; color: #4f46e5; }

        /* ── Avoid breaking inside a med row ── */
        .med-table tr { break-inside: avoid; page-break-inside: avoid; }

        /* ── Lab tests ── */
        .lab-col { display: inline-block; vertical-align: top; width: 48%; }
        .lab-col:first-child { margin-right: 4%; }
        .lab-list { list-style: disc inside; padding: 0; margin: 0; }
        .lab-list li { font-size: 11px; color: #1f2937; line-height: 1.6; }

        /* ── Clinical notes ── */
        .clinical-note { font-size: 11px; color: #1f2937; line-height: 1.5; }

        /* ── Page break helpers ── */
        .page-break-before { page-break-before: always; break-before: page; }
        .avoid-break { page-break-inside: avoid; break-inside: avoid; }

        /* ── Letterhead background ── */
        .letterhead-bg {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            z-index: 0; object-fit: fill; pointer-events: none;
        }
        
        /* If printing Digital PDF, force background graphics */
        @media print {
            .letterhead-bg {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        /* ── QR code ── */
        .qr-wrap { border: 1px solid #e5e7eb; padding: 2px; background: #fff; display: inline-block; }

        /* ── Signature line ── */
        .sig-line { border-top: 1px solid #9ca3af; padding-top: 6px; margin-top: 4px; }
        .sig-text { font-size: 9px; color: #374151; font-style: italic; }
        .sig-img  { max-height: 36px; object-fit: contain; display: block; margin-left: auto; }

        /* ── Traceability ── */
        .traceability { font-size: 8px; color: #9ca3af; font-family: monospace; text-transform: uppercase; }

        /* ── ICD chips ── */
        .icd-chip {
            display: inline-flex; align-items: center; gap: 4px;
            background: #ede9fe; border: 1px solid #c4b5fd;
            color: #5b21b6; font-size: 9px; font-weight: 700;
            padding: 2px 6px; border-radius: 6px; margin: 1px 2px;
        }
        .icd-chip .code { font-family: monospace; }

        /* ── Next visit pill ── */
        .next-visit-pill {
            display: inline-block; border: 1px solid #c7d2fe;
            background: #eef2ff; color: #3730a3;
            padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700;
        }

        /* ── Toolbar (screen only) ── */
        .toolbar {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: center;
            gap: 12px; padding: 14px; background: #1e293b;
            border-bottom: 3px solid #0f172a;
            position: sticky; top: 0; z-index: 100;
        }
        .toolbar button, .toolbar a {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 18px; border-radius: 8px; font-size: 12px;
            font-weight: 700; cursor: pointer; border: none; text-decoration: none;
            transition: all 0.15s; letter-spacing: 0.3px;
        }
        .btn-green  { background: #059669; color: #fff; }
        .btn-green:hover  { background: #047857; }
        .btn-indigo { background: #4f46e5; color: #fff; }
        .btn-indigo:hover { background: #4338ca; }
        .btn-green2 { background: #16a34a; color: #fff; }  /* WhatsApp */
        .btn-green2:hover { background: #15803d; }
        .btn-gray   { background: #334155; color: #cbd5e1; }
        .btn-gray:hover   { background: #475569; }
        .toolbar-label { color: #94a3b8; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }

        @media print {
            .toolbar, .no-print { display: none !important; }
        }
    </style>

    <!-- Tailwind for toolbar & screen mode only (not used in print body) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- ══════════════════════════════════════════════════════
     TOOLBAR (screen only — hidden on print)
     ══════════════════════════════════════════════════════ -->
<div class="toolbar no-print">
    <?php if ($is_admin): ?>
        <button onclick="printDigital()" class="btn-green"
                <?php echo !$has_letterhead ? 'disabled style="opacity:0.4;cursor:not-allowed;" title="Upload a letterhead in Hospital Settings first"' : ''; ?>>
            <i class="fa-solid fa-file-pdf"></i> Print Digital PDF
        </button>
        <button onclick="window.print()" class="btn-indigo">
            <i class="fa-solid fa-print"></i> Print on Physical Letterhead
        </button>
        <button onclick="sendWhatsApp()" class="btn-green2"
                <?php echo !$has_letterhead ? 'disabled style="opacity:0.4;cursor:not-allowed;"' : ''; ?>>
            <i class="fa-brands fa-whatsapp"></i> Send WhatsApp
        </button>
        <a href="prescriptions_edit.php?id=<?php echo $id; ?>" class="btn-gray">
            <i class="fa-solid fa-pen-to-square"></i> Edit
        </a>
    <?php
else: ?>
        <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
            <button onclick="printDigital()" class="btn-indigo">
                <i class="fa-solid fa-download"></i> Save as PDF / Print
            </button>
            <span class="toolbar-label">Choose "Save as PDF" in the print dialog</span>
        </div>
        <a href="https://patient.ivfexperts.pk/dashboard.php" class="btn-gray">
            <i class="fa-solid fa-house-user"></i> My Records
        </a>
    <?php
endif; ?>
    <a href="javascript:history.back()" class="btn-gray">
        <i class="fa-solid fa-arrow-left"></i> Back
    </a>
</div>

<!-- ══════════════════════════════════════════════════════
     PAGE CONTENT WRAPPER — one .rx-page per printed sheet
     ══════════════════════════════════════════════════════ -->
<div id="all-pages">

<?php
// ── Prepare content sections ──────────────────────────────────────────────────
// We'll render them inside a page-aware wrapper.
// The JS paginator will inject page-break divs between sections if needed.
?>

<!-- PAGE 1 (and possibly only page) -->
<div class="rx-page" id="rx-page-1">

    <!-- Letterhead for patient portal (permanent) -->
    <?php if ($digital_auto && $has_letterhead): ?>
    <img src="<?php echo esc($letterhead_url); ?>" class="letterhead-bg" alt="Letterhead" />
    <?php
endif; ?>

    <table class="rx-layout-table">

        <!-- ══ REPEATING HEADER ══════════════════════════════════ -->
        <thead>
            <tr><td class="rx-header-cell">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">

                    <!-- Patient Details -->
                    <div style="flex:1;">
                        <table style="font-size:11px;line-height:1.6;width:100%;max-width:320px;">
                            <tr>
                                <td style="font-weight:700;color:#6b7280;width:100px;padding-right:8px;white-space:nowrap;">Patient Name:</td>
                                <td style="font-weight:800;color:#111827;font-size:12px;text-transform:uppercase;"><?php echo esc($rx['first_name'] . ' ' . $rx['last_name']); ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight:700;color:#6b7280;">MR Number:</td>
                                <td style="font-weight:700;color:#3730a3;font-family:monospace;"><?php echo esc($rx['mr_number']); ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight:700;color:#6b7280;">Gender / Age:</td>
                                <td><?php echo esc($rx['gender']) . ($rx['patient_age'] ? ' / ' . esc($rx['patient_age']) : ''); ?></td>
                            </tr>
                            <?php if (!empty($rx['phone'])): ?>
                            <tr>
                                <td style="font-weight:700;color:#6b7280;">Phone:</td>
                                <td><?php echo esc($rx['phone']); ?></td>
                            </tr>
                            <?php
endif; ?>
                        </table>
                    </div>

                    <!-- QR Code -->
                    <?php if (!empty($qr_hash)): ?>
                    <div style="text-align:center;flex-shrink:0;">
                        <div class="qr-wrap">
                            <img src="<?php echo $qr_url; ?>" width="56" height="56" alt="QR" />
                        </div>
                        <div style="font-size:7px;color:#9ca3af;margin-top:2px;text-align:center;">Scan to verify</div>
                    </div>
                    <?php
endif; ?>

                    <!-- Date + RX Number -->
                    <div style="text-align:right;flex-shrink:0;border-left:1px solid #e5e7eb;padding-left:12px;">
                        <?php if (!empty($tracking_code)): ?>
                        <div class="traceability" style="margin-bottom:3px;">TRK-<?php echo $tracking_code; ?></div>
                        <?php
endif; ?>
                        <div style="font-weight:700;color:#374151;font-size:12px;"><?php echo date('d M Y'); ?></div>
                        <div style="font-size:9px;color:#6b7280;margin-top:2px;"><?php echo date('h:i A'); ?></div>
                        <div style="font-size:11px;font-weight:800;color:#4f46e5;margin-top:4px;letter-spacing:1px;">
                            RX-<?php echo str_pad($id, 6, '0', STR_PAD_LEFT); ?>
                        </div>
                        <div style="font-size:9px;color:#9ca3af;margin-top:2px;">
                            <?php echo $is_admin ? 'Clinic Staff' : 'Patient Portal'; ?>
                        </div>
                    </div>
                </div>
            </td></tr>
        </thead>

        <!-- ══ REPEATING FOOTER ══════════════════════════════════ -->
        <tfoot>
            <tr><td class="rx-footer-cell">
                <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:12px;">
                    <!-- Sig line -->
                    <div class="sig-line" style="flex:1;">
                        <div class="sig-text">
                            <strong>Digitally Signed:</strong>
                            Dr. Adnan Jabbar | MBBS, DFM, MH, Fertility &amp; Family Medicine Specialist, Clinical Embryologist
                        </div>
                    </div>
                    <!-- Signature image -->
                    <?php if (!empty($rx['digital_signature_path'])): ?>
                    <div style="flex-shrink:0;">
                        <img src="https://ivfexperts.pk/<?php echo esc($rx['digital_signature_path']); ?>"
                             class="sig-img" alt="Signature" />
                    </div>
                    <?php
endif; ?>
                    <!-- Traceability -->
                    <div style="text-align:right;flex-shrink:0;">
                        <div class="traceability">
                            <?php echo $rx['hospital_name'] ?? 'IVF Experts'; ?>
                        </div>
                        <?php if (!empty($tracking_code)): ?>
                        <div class="traceability">DOC-<?php echo $tracking_code; ?></div>
                        <?php
endif; ?>
                        <div class="traceability page-num-ref" style="margin-top:2px;">Page 1</div>
                    </div>
                </div>
            </td></tr>
        </tfoot>

        <!-- ══ BODY CONTENT ══════════════════════════════════════ -->
        <tbody>
            <tr><td class="rx-body-cell" id="rx-body-content">

                <!-- ── Clinical Notes ──────────────────────────────── -->
                <?php
$complaint_text = trim(strip_tags($rx['clinical_notes'] ?? $rx['presenting_complaint'] ?? ''));
$diagnosis_text = trim(strip_tags($rx['diagnosis'] ?? ''));
if (!empty($complaint_text) || !empty($diagnosis_text) || !empty($icds) || !empty($cpts)):
?>
                <div class="section avoid-break" id="section-clinical">
                    <div class="section-heading">
                        <i class="fa-solid fa-notes-medical" style="color:#6d28d9;font-size:8px;"></i>
                        Clinical Assessment
                    </div>
                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:4px;padding:8px 10px;">

                        <?php if (!empty($complaint_text)): ?>
                        <div style="display:flex;gap:8px;border-bottom:1px solid #e5e7eb;padding-bottom:5px;margin-bottom:5px;">
                            <span style="font-weight:700;color:#6b7280;font-size:9px;text-transform:uppercase;letter-spacing:0.5px;width:100px;flex-shrink:0;">History / Complaint:</span>
                            <span class="clinical-note"><?php echo nl2br(esc($complaint_text)); ?></span>
                        </div>
                        <?php
    endif; ?>

                        <?php if (!empty($diagnosis_text)): ?>
                        <div style="display:flex;gap:8px;border-bottom:1px solid #e5e7eb;padding-bottom:5px;margin-bottom:5px;">
                            <span style="font-weight:700;color:#6b7280;font-size:9px;text-transform:uppercase;letter-spacing:0.5px;width:100px;flex-shrink:0;">Diagnosis:</span>
                            <span class="clinical-note" style="color:#065f46;"><?php echo nl2br(esc($diagnosis_text)); ?></span>
                        </div>
                        <?php
    endif; ?>

                        <?php if (!empty($icds)): ?>
                        <div style="display:flex;gap:8px;align-items:flex-start;<?php echo !empty($cpts) ? 'border-bottom:1px solid #e5e7eb;padding-bottom:5px;margin-bottom:5px;' : ''; ?>">
                            <span style="font-weight:700;color:#6b7280;font-size:9px;text-transform:uppercase;letter-spacing:0.5px;width:100px;flex-shrink:0;padding-top:2px;">ICD-10 Codes:</span>
                            <div><?php
        foreach ($icds as $icd) {
            echo '<span class="icd-chip"><span class="code">' . esc($icd['code']) . '</span>' . esc($icd['description']) . '</span>';
        }
?></div>
                        </div>
                        <?php
    endif; ?>

                        <?php if (!empty($cpts)): ?>
                        <div style="display:flex;gap:8px;">
                            <span style="font-weight:700;color:#6b7280;font-size:9px;text-transform:uppercase;width:100px;flex-shrink:0;">Procedures:</span>
                            <div class="clinical-note" style="color:#1e40af;"><?php
        echo implode(' <span style="color:#d1d5db;">|</span> ', array_map(
        fn($c) => (!empty($c['code']) ? '<strong>[' . esc($c['code']) . ']</strong> ' : '') . esc($c['description']),
            $cpts
        ));
?></div>
                        </div>
                        <?php
    endif; ?>
                    </div>
                </div>
                <?php
endif; ?>

                <!-- ── Medications ─────────────────────────────────── -->
                <div class="section" id="section-medications">
                    <div class="section-heading">
                        <i class="fa-solid fa-prescription" style="color:#4f46e5;font-size:8px;"></i>
                        Prescribed Medications
                    </div>
                    <?php if (empty($items)): ?>
                    <p style="font-size:11px;color:#9ca3af;font-style:italic;padding:8px 0;">No medications prescribed.</p>
                    <?php
else: ?>
                    <table class="med-table">
                        <thead>
                            <tr>
                                <th style="width:28px;text-align:center;">Sr.</th>
                                <th style="width:40%;">Medicine</th>
                                <th style="width:20%;">Dosage</th>
                                <th style="width:20%;">Frequency</th>
                                <th style="width:20%;">Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $idx => $item):
        $bg = $idx % 2 === 0 ? '#fff' : '#f9fafb';
?>
                            <tr style="background:<?php echo $bg; ?>; border-bottom: none;">
                                <td style="text-align:center;font-weight:700;color:#9ca3af;border-bottom:none;padding-bottom:1px;"><?php echo $idx + 1; ?>.</td>
                                <td style="border-bottom:none;padding-bottom:1px;">
                                    <div class="med-name"><?php echo esc($item['medicine_name']); ?></div>
                                    <?php if (!empty($item['formula'])): ?>
                                    <div class="med-formula"><?php echo esc($item['formula']); ?></div>
                                    <?php
        elseif (!empty($item['med_type'])): ?>
                                    <div style="font-size:9px;color:#9ca3af;font-style:italic;"><?php echo esc($item['med_type']); ?></div>
                                    <?php
        endif; ?>
                                </td>
                                <td style="font-weight:600;color:#1f2937;border-bottom:none;padding-bottom:1px;"><?php echo esc($item['dosage'] ?: '—'); ?></td>
                                <td class="med-freq" style="border-bottom:none;padding-bottom:1px;"><?php echo esc($item['frequency'] ?: '—'); ?></td>
                                <td style="font-weight:600;color:#1f2937;white-space:nowrap;border-bottom:none;padding-bottom:1px;"><?php echo esc($item['duration'] ?: '—'); ?></td>
                            </tr>
                            <tr style="background:<?php echo $bg; ?>; border-top: none;">
                                <td style="border-top:none;padding-top:1px;"></td>
                                <td colspan="4" style="border-top:none;padding-top:1px;font-size:10px;color:#4b5563;font-style:italic;">
                                    <?php echo esc($item['instructions'] ?: '—'); ?>
                                </td>
                            </tr>
                            <?php
    endforeach; ?>
                        </tbody>
                    </table>
                    <?php
endif; ?>
                </div>

                <!-- ── Advised Lab Tests ───────────────────────────── -->
                <?php if (!empty($lab_tests)): ?>
                <div class="section avoid-break" id="section-labs">
                    <div class="section-heading">
                        <i class="fa-solid fa-vials" style="color:#d97706;font-size:8px;"></i>
                        Advised Laboratory Investigations
                    </div>
                    <div style="display:flex;gap:16px;flex-wrap:wrap;">
                        <?php if (!empty($patient_tests)): ?>
                        <div style="flex:1;min-width:40%;border-left:3px solid #6366f1;padding-left:10px;">
                            <div style="font-size:9px;font-weight:700;color:#6b7280;text-transform:uppercase;margin-bottom:4px;">
                                <i class="fa-solid fa-user" style="font-size:8px;"></i> For Patient
                            </div>
                            <ul class="lab-list">
                                <?php foreach ($patient_tests as $t): ?>
                                <li><?php echo esc($t['test_name']); ?></li>
                                <?php
        endforeach; ?>
                            </ul>
                        </div>
                        <?php
    endif; ?>
                        <?php if (!empty($spouse_tests)): ?>
                        <div style="flex:1;min-width:40%;border-left:3px solid #ec4899;padding-left:10px;">
                            <div style="font-size:9px;font-weight:700;color:#6b7280;text-transform:uppercase;margin-bottom:4px;">
                                <i class="fa-solid fa-person-half-dress" style="font-size:8px;"></i> For Spouse
                            </div>
                            <ul class="lab-list">
                                <?php foreach ($spouse_tests as $t): ?>
                                <li><?php echo esc($t['test_name']); ?></li>
                                <?php
        endforeach; ?>
                            </ul>
                        </div>
                        <?php
    endif; ?>
                    </div>
                </div>
                <?php
endif; ?>

                <!-- ── General Advice + Next Visit ────────────────── -->
                <div class="section avoid-break" id="section-advice" style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-top:8px;border-top:1px solid #e5e7eb;padding-top:8px;">
                    <?php if (!empty($rx['general_advice'])): ?>
                    <div style="flex:1;">
                        <div style="font-size:9px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">
                            Advice / General Notes
                        </div>
                        <p style="font-size:11px;color:#1f2937;line-height:1.6;white-space:pre-wrap;"><?php echo esc($rx['general_advice']); ?></p>
                    </div>
                    <?php
endif; ?>
                    <?php if (!empty($rx['next_visit'])): ?>
                    <div style="flex-shrink:0;text-align:right;">
                        <div style="font-size:9px;color:#6b7280;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Next Follow-up</div>
                        <span class="next-visit-pill"><?php echo date('D, d M Y', strtotime($rx['next_visit'])); ?></span>
                    </div>
                    <?php
endif; ?>
                </div>

            </td></tr>
        </tbody>

    </table><!-- end rx-layout-table -->
</div><!-- end rx-page-1 -->

</div><!-- end all-pages -->

<!-- ══════════════════════════════════════════════════════
     SCRIPTS
     ══════════════════════════════════════════════════════ -->
<script>
// ── Configuration from PHP ───────────────────────────────────────────────────
var RX_CONFIG = {
    letterheadUrl:  '<?php echo addslashes($letterhead_url); ?>',
    hasLetterhead:  <?php echo $has_letterhead ? 'true' : 'false'; ?>,
    isAdmin:        <?php echo $is_admin ? 'true' : 'false'; ?>,
    margins: {
        top:    '<?php echo $mt; ?>',
        bottom: '<?php echo $mb; ?>',
        left:   '<?php echo $ml; ?>',
        right:  '<?php echo $mr; ?>'
    }
};

// ── Helper: parse mm value ────────────────────────────────────────────────────
function parseMM(v) {
    return parseFloat(String(v).replace('mm','').replace('cm','')) || 0;
}

// ── Inject letterhead into every .rx-page ────────────────────────────────────
function injectLetterheads(callback) {
    if (!RX_CONFIG.hasLetterhead || !RX_CONFIG.letterheadUrl) {
        if (callback) callback();
        return;
    }
    var pages = document.querySelectorAll('.rx-page');
    var pending = 0;
    pages.forEach(function(page) {
        // Remove any existing letterhead (avoid duplicates)
        var old = page.querySelector('.letterhead-bg');
        if (old) old.remove();

        var img = document.createElement('img');
        img.className = 'letterhead-bg';
        img.src = RX_CONFIG.letterheadUrl;
        img.alt = 'Letterhead';
        pending++;

        var done = false;
        var doneFn = function() {
            if (done) return;
            done = true;
            pending--;
            if (pending <= 0 && callback) callback();
        };
        img.onload = doneFn;
        img.onerror = doneFn;
        setTimeout(doneFn, 4000); // 4s fallback per image (was 3s)

        page.insertBefore(img, page.firstChild);
    });
    if (pending === 0 && callback) callback();
}

// ── Print Digital ─────────────────────────────────────────────────────────────
var _printAttempted = false;
function printDigital() {
    if (_printAttempted) return;
    _printAttempted = true;

    if (RX_CONFIG.hasLetterhead) {
        document.body.style.background = '#fff';
        injectLetterheads(function() {
            window.print();
            setTimeout(function() {
                _printAttempted = false;
                // Remove injected letterheads to restore screen view ONLY for admin.
                // Patient portal keeps them permanently.
                if (RX_CONFIG.isAdmin) {
                    document.querySelectorAll('.rx-page .letterhead-bg').forEach(function(img) {
                        if (!img.classList.contains('perm-lh')) img.remove();
                    });
                }
            }, 1500);
        });
    } else {
        window.print();
        setTimeout(function() { _printAttempted = false; }, 1500);
    }
}

// ── Page number updater ───────────────────────────────────────────────────────
function updatePageNumbers() {
    var pages = document.querySelectorAll('.rx-page');
    var total = pages.length;
    pages.forEach(function(page, i) {
        var el = page.querySelector('.page-num-ref');
        if (el) el.textContent = 'Page ' + (i+1) + ' of ' + total;
    });
}

// ── WhatsApp sender ───────────────────────────────────────────────────────────
function sendWhatsApp() {
    var phone = '<?php echo esc($rx['phone']); ?>'.replace(/\D/g, '');
    if (!phone || phone.length < 10) {
        phone = prompt('Enter patient WhatsApp number (e.g. 923001234567):','92');
        if (!phone) return;
        phone = phone.replace(/\D/g,'');
    } else if (phone.startsWith('03')) {
        phone = '92' + phone.substring(1);
    }
    var hash   = '<?php echo $qr_hash; ?>';
    var name   = '<?php echo esc($rx['first_name'] . ' ' . $rx['last_name']); ?>';
    var link   = 'https://patient.ivfexperts.pk/verify.php?hash=' + hash;
    var msg    = `Dear ${name},\n\nHere is your Prescription from IVF Experts Clinic. View and download:\n\n${link}\n\nRegards,\nDr. Adnan Jabbar\n+92 3 111 101 483`;
    window.open('https://wa.me/' + phone + '?text=' + encodeURIComponent(msg), '_blank');
}

// ── Auto-trigger for patient portal ──────────────────────────────────────────
<?php if (!$is_admin): ?>
window.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        printDigital();
    }, 600);
});
<?php
endif; ?>

// ── Paginate after all resources are loaded (images, fonts) ─────────────────
// Using window.load instead of DOMContentLoaded so that images are
// measured at their real height before we split pages.
var _paginateDone = false;
function _safePaginate() {
    if (_paginateDone) return;
    _paginateDone = true;
    paginateContent();
    updatePageNumbers();
}
// Primary trigger: after everything is loaded
window.addEventListener('load', _safePaginate);
// Absolute fallback: run after 5 seconds regardless
setTimeout(_safePaginate, 5000);

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 4 — JS Content Paginator
// Splits rx-body-content sections into separate .rx-page divs when they
// overflow a single A4 page height, so each page gets its own letterhead.
// ══════════════════════════════════════════════════════════════════════════════
function paginateContent() {
    // A4 page in mm: 297mm tall. Subtract margins (top + bottom).
    var marginTopMM    = parseMM(RX_CONFIG.margins.top);
    var marginBottomMM = parseMM(RX_CONFIG.margins.bottom);
    var pageHeightMM   = 297 - marginTopMM - marginBottomMM;

    // Convert mm to pixels on this screen (1mm ≈ 3.7795px at 96dpi)
    // We use getBoundingClientRect on the actual rx-page to get a real pixel measurement.
    var firstPage = document.getElementById('rx-page-1');
    if (!firstPage) return;

    // Measure the actual available body height. We use the table's tbody td.
    var bodyCell = document.getElementById('rx-body-content');
    if (!bodyCell) return;

    // Get DPR-aware px height of one page's content area
    var pageRect     = firstPage.getBoundingClientRect();
    var pageWidthPx  = pageRect.width;
    // Calculate usable content height in pixels (A4 area minus margins)
    var mmToPx       = pageWidthPx / (210 - parseMM(RX_CONFIG.margins.left) - parseMM(RX_CONFIG.margins.right));
    var maxBodyPx    = pageHeightMM * mmToPx;

    // Measure header + footer heights to subtract from usable area
    var header = firstPage.querySelector('thead');
    var footer = firstPage.querySelector('tfoot');
    var headerH = header ? header.getBoundingClientRect().height : 0;
    var footerH = footer ? footer.getBoundingClientRect().height : 0;
    var usableBodyPx = maxBodyPx - headerH - footerH - 32; // 32px padding buffer

    // Get all top-level sections inside the body cell
    var sections = Array.from(bodyCell.children);
    if (sections.length === 0) return;

    // Measure each section's height
    var sectionHeights = sections.map(function(s) {
        return s.getBoundingClientRect().height;
    });

    // Check if total fits in one page
    var totalH = sectionHeights.reduce(function(a, b) { return a + b; }, 0);
    if (totalH <= usableBodyPx) return; // All fits — nothing to do

    // Build page groups: assign sections to pages
    var pages   = [[]];
    var running = 0;
    sections.forEach(function(section, i) {
        var h = sectionHeights[i];
        // If a single section is taller than the page, don't break it — just let it overflow
        if (running + h > usableBodyPx && running > 0) {
            pages.push([]);
            running = 0;
        }
        pages[pages.length - 1].push(section);
        running += h;
    });

    if (pages.length <= 1) return; // Still only one page needed

    // Page 1 already exists — keep its sections as-is (pages[0])
    // We need to remove sections assigned to pages 2+ from the body cell
    // and create new .rx-page divs for them.

    // Clone the page template (header + footer) to use for each new page
    var templateTable = firstPage.querySelector('.rx-layout-table');
    var templateThead = templateTable.querySelector('thead').cloneNode(true);
    var templateTfoot = templateTable.querySelector('tfoot').cloneNode(true);

    // Remove sections from firstPage body that belong to pages 2+
    var sectionsToKeep = pages[0];
    var sectionsToMove = sections.filter(function(s) { return !sectionsToKeep.includes(s); });
    sectionsToMove.forEach(function(s) { s.remove(); });

    // Build overflow pages
    var allPagesDiv = document.getElementById('all-pages');
    var pageCounter = 1;

    for (var pi = 1; pi < pages.length; pi++) {
        pageCounter++;
        var newPage = document.createElement('div');
        newPage.className = 'rx-page';
        newPage.id = 'rx-page-' + pageCounter;

        var newTable = document.createElement('table');
        newTable.className = 'rx-layout-table';

        // Add cloned header and footer
        newTable.appendChild(templateThead.cloneNode(true));
        newTable.appendChild(templateTfoot.cloneNode(true));

        // Add body with this page's sections
        var tbody = document.createElement('tbody');
        var tr    = document.createElement('tr');
        var td    = document.createElement('td');
        td.className = 'rx-body-cell';
        pages[pi].forEach(function(s) { td.appendChild(s); });
        tr.appendChild(td);
        tbody.appendChild(tr);

        // Insert tbody BEFORE tfoot (correct table order: thead > tbody > tfoot)
        var tfoot = newTable.querySelector('tfoot');
        newTable.insertBefore(tbody, tfoot);

        newPage.appendChild(newTable);
        allPagesDiv.appendChild(newPage);
    }

    // If patient portal — letterheads are injected statically by PHP, re-inject for new pages
    if (!RX_CONFIG.isAdmin && RX_CONFIG.hasLetterhead) {
        var newPages = allPagesDiv.querySelectorAll('.rx-page:not(#rx-page-1)');
        newPages.forEach(function(page) {
            var img = document.createElement('img');
            img.className = 'letterhead-bg';
            img.src = RX_CONFIG.letterheadUrl;
            img.alt = 'Letterhead';
            page.insertBefore(img, page.firstChild);
        });
    }
}
</script>
</body>
</html>
