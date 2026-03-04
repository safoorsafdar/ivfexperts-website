<?php
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

// Fetch Prescription Data
$rx = null;
try {
    // Note: prescriptions table does NOT have hospital_id — use patient's referring hospital
    $stmt = $conn->prepare("
        SELECT rx.*,
               p.first_name, p.last_name, p.mr_number, p.gender, p.phone, p.cnic,
               p.patient_age, p.blood_group,
               COALESCE(h.name, 'IVF Experts Clinic') AS hospital_name,
               COALESCE(h.margin_top, 20) AS margin_top,
               COALESCE(h.margin_bottom, 20) AS margin_bottom,
               COALESCE(h.margin_left, 20) AS margin_left,
               COALESCE(h.margin_right, 20) AS margin_right,
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


// Log download and get tracking code
$tracking_code = log_document_download($conn, 'rx', $id);

// Fetch Items (prescription_items uses medicine_name text, no medications JOIN)
$items = [];
try {
    $stmt = $conn->prepare("SELECT * FROM prescription_items WHERE prescription_id = ? ORDER BY id ASC");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc())
            $items[] = $row;
    }
}
catch (Exception $e) {
}

// Fetch Diagnoses — try prescription_diagnoses table first, fall back to icd10_codes JSON
$diagnoses = [];
try {
    $chk = $conn->query("SHOW TABLES LIKE 'prescription_diagnoses'");
    if ($chk && $chk->num_rows > 0) {
        $stmt = $conn->prepare("SELECT * FROM prescription_diagnoses WHERE prescription_id = ? ORDER BY type ASC");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc())
                $diagnoses[] = $row;
        }
    }
    // Fallback: parse icd10_codes JSON column
    if (empty($diagnoses) && !empty($rx['icd10_codes'])) {
        $icd_arr = json_decode($rx['icd10_codes'], true);
        if (is_array($icd_arr)) {
            foreach ($icd_arr as $icd) {
                $diagnoses[] = [
                    'type' => 'ICD',
                    'code' => $icd['icd10_code'] ?? '',
                    'description' => $icd['description'] ?? ''
                ];
            }
        }
    }
}
catch (Exception $e) {
}

// Fetch Advised Lab Tests
$lab_tests = [];
try {
    // Try advised_lab_tests (new schema) with test name from directory
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
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc())
                $lab_tests[] = $row;
        }
    }
}
catch (Exception $e) {
}



$patient_tests = array_filter($lab_tests, fn($t) => $t['advised_for'] == 'Patient');
$spouse_tests = array_filter($lab_tests, fn($t) => $t['advised_for'] == 'Spouse');

$icds = array_filter($diagnoses, fn($d) => $d['type'] == 'ICD');
$cpts = array_filter($diagnoses, fn($d) => $d['type'] == 'CPT' || $d['type'] == 'SNOMED');

// Setup Margins explicitly to handle pre-printed hospital letterheads
$mt = $rx['margin_top'] ?? '40mm';
$mb = $rx['margin_bottom'] ?? '30mm';
$ml = $rx['margin_left'] ?? '20mm';
$mr = $rx['margin_right'] ?? '20mm';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription #<?php echo $id; ?></title>
    <!-- Tailwind via CDN for quick styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @page {
            size: A4;
            margin: <?php echo $mt; ?> <?php echo $mr; ?> <?php echo $mb; ?> <?php echo $ml; ?>;
        }
        body {
            background-color: #f3f4f6; /* Gray background on screen */
            -webkit-print-color-adjust: exact;
            color: #000;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        .a4-container {
            width: 210mm;
            min-height: 297mm;
            background: #fff;
            margin: 0 auto;
            position: relative;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: <?php echo $mt; ?> <?php echo $mr; ?> <?php echo $mb; ?> <?php echo $ml; ?>;
            box-sizing: border-box;
        }
        @media print {
            body { background: #fff; }
            .a4-container {
                width: 100%;
                min-height: 297mm;
                box-shadow: none;
                margin: 0;
            }
            .no-print { display: none !important; }
            .no-print-bg { background: transparent !important; }
            .print-footer {
                position: fixed !important;
                bottom: 25mm !important;
                left: 10mm !important;
                right: 45mm !important;
            }
        }
        
        /* Digital Backdrop Classes */
        .digital-mode .a4-container {
            padding: <?php echo $mt; ?> <?php echo $mr; ?> <?php echo $mb; ?> <?php echo $ml; ?> !important;
            background: transparent !important;
        }
        .digital-mode @page {
            margin: 0;
        }
        
        .letterhead-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            min-height: 297mm;
            z-index: -10;
            object-fit: fill;
        }
        .traceability-code {
            position: absolute; bottom: 8mm; right: 15mm;
            font-size: 8px; color: #94a3b8; font-family: monospace;
            pointer-events: none; text-transform: uppercase;
        }
    </style>
</head>
<body class="py-10 print:py-0 <?php echo(!isset($_SESSION['admin_id']) && !empty($rx['letterhead_image_path'])) ? 'digital-mode' : ''; ?>">

    <!-- Screen-only controls -->
    <div class="flex flex-wrap justify-center gap-4 py-4 mb-6 bg-slate-50 border-b border-slate-200 no-print w-full shadow-sm">
        <?php if (isset($_SESSION['admin_id'])): ?>
            <!-- Admin Controls -->
            <button onclick="printDigital()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg shadow-lg font-bold" <?php if (empty($rx['letterhead_image_path']))
        echo 'disabled title="No Letterhead uploaded in settings" style="opacity: 0.5; cursor: not-allowed;"'; ?>>
                <i class="fa-solid fa-file-pdf"></i> Print Digital PDF
            </button>
            <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg shadow-lg font-bold">
                <i class="fa-solid fa-print"></i> Print on Physical Letterhead
            </button>
            <button onclick="sendWhatsApp()" class="bg-[#25D366] hover:bg-[#128C7E] text-white px-6 py-2 rounded-lg shadow-lg font-bold shadow-green-500/30" <?php if (empty($rx['letterhead_image_path']))
        echo 'disabled title="No Letterhead uploaded in settings" style="opacity: 0.5; cursor: not-allowed;"'; ?>>
                <i class="fa-brands fa-whatsapp text-lg mr-1"></i> Send via WhatsApp
            </button>
        <?php
else: ?>
            <!-- Patient Portal Controls -->
            <div class="flex flex-col items-center">
                <button onclick="printDigital()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg shadow-lg font-bold">
                    <i class="fa-solid fa-download"></i> Save as PDF / Print
                </button>
                <span class="text-[9px] text-slate-400 mt-1 uppercase font-black tracking-widest">(Choose "Save as PDF" in print dialog)</span>
            </div>
            <a href="https://patient.ivfexperts.pk/dashboard.php" class="bg-indigo-50 border border-indigo-200 text-indigo-700 px-4 py-2 rounded-lg shadow-sm font-bold flex items-center gap-2">
                <i class="fa-solid fa-house-user"></i> My Records
            </a>
        <?php
endif; ?>
        <a href="javascript:history.back()" class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded-lg shadow-lg font-bold">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back
        </a>
    </div>

    <!-- The Actual Document -->
    <div class="a4-container flex flex-col relative pb-[35mm]" id="document-container">
        
        <!-- Permanent Letterhead Background for Patients -->
        <?php if (!isset($_SESSION['admin_id']) && !empty($rx['letterhead_image_path'])): ?>
            <img src="https://ivfexperts.pk/<?php echo htmlspecialchars($rx['letterhead_image_path']); ?>" alt="Letterhead" class="letterhead-bg" />
        <?php
endif; ?>

        <!-- Patient Demographics Block -->
        <div class="border-b-2 border-gray-300 pb-2 mb-3 flex items-center justify-between px-2 gap-4">
            <!-- Details -->
            <div class="flex-grow">
                <table class="text-[11px] leading-tight w-full max-w-md">
                    <tr><td class="font-bold pr-2 text-gray-500 py-0.5 w-24">Patient Name:</td><td class="font-bold text-gray-800 text-[12px] uppercase py-0.5"><?php echo esc($rx['first_name'] . ' ' . $rx['last_name']); ?></td></tr>
                    <tr><td class="font-bold pr-2 text-gray-500 py-0.5">MR Number:</td><td class="font-mono font-bold text-indigo-800 py-0.5"><?php echo esc($rx['mr_number']); ?></td></tr>
                    <tr><td class="font-bold pr-2 text-gray-500 py-0.5">Gender / Phone:</td><td class="py-0.5"><?php echo esc($rx['gender']); ?> / <?php echo esc($rx['phone'] ?: 'N/A'); ?></td></tr>
                </table>
            </div>
            
            <!-- QR Code & Date Block Container -->
            <div class="flex items-center gap-6 shrink-0">
                <!-- QR Code (Right Side) -->
                <div class="shrink-0 flex items-center">
                    <div class="border border-gray-200 p-0.5 rounded shadow-sm bg-white">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=64x64&data=<?php echo urlencode('https://patient.ivfexperts.pk/verify.php?hash=' . $rx['qrcode_hash']); ?>" alt="QR Code" class="w-14 h-14" />
                    </div>
                    <!-- Vertical Divider -->
                    <div class="h-10 w-px bg-gray-300 mx-3"></div>
                    <!-- Text on Right -->
                    <div class="text-[7px] text-gray-500 leading-tight w-28">
                        Scan this verification<br>
                        code with phone camera<br>
                        to verify & download.
                    </div>
                </div>

                <!-- Date Block -->
                <div class="text-right text-[11px] shrink-0 border-l border-gray-300 pl-6">
                    <div class="font-bold text-gray-500 uppercase tracking-tight text-[9px] mb-0.5">
                        Printed By: <?php echo isset($_SESSION['admin_id']) ? 'Clinic Staff' : 'Patient Portal'; ?>
                    </div>
                    <div class="font-bold text-gray-800 mb-0.5" title="Date Printed"><?php echo date('d M Y, h:i A'); ?></div>
                    <div class="text-[9px] text-gray-400 uppercase tracking-widest mt-1">RX-<?php echo str_pad($id, 6, '0', STR_PAD_LEFT); ?></div>
                </div>
            </div>
        </div>

        <!-- Clinical Assessment -->
        <div class="mb-4 text-[11px] px-2 bg-gray-50 border border-gray-100 p-2 rounded">
            <div class="grid grid-cols-1 gap-1">
                <?php if (!empty($rx['presenting_complaint'])): ?>
                    <div class="flex border-b border-gray-200 pb-1 mb-1">
                        <span class="font-bold text-gray-500 uppercase text-[10px] w-28 shrink-0">History/Complaint:</span>
                        <span class="font-medium text-gray-800 flex-grow"><?php echo esc($rx['presenting_complaint']); ?></span>
                    </div>
                <?php
endif; ?>
                
                <?php if (!empty($icds) || !empty($rx['icd_disease'])): ?>
                    <div class="flex border-b border-gray-200 pb-1 mb-1">
                        <span class="font-bold text-gray-500 uppercase text-[10px] w-28 shrink-0">Diagnosis:</span>
                        <span class="font-medium text-emerald-800 flex-grow">
                            <?php
    // Backward compatibility
    if (!empty($rx['icd_disease'])) {
        echo(!empty($rx['icd_code']) ? '<strong class="mr-1">[' . esc($rx['icd_code']) . ']</strong>' : '') . esc($rx['icd_disease']);
    }
    else {
        $icd_strings = [];
        foreach ($icds as $icd) {
            $str = '';
            if (!empty($icd['code']))
                $str .= '<strong class="mr-1">[' . esc($icd['code']) . ']</strong>';
            $str .= esc($icd['description']);
            $icd_strings[] = $str;
        }
        echo implode(' <span class="text-gray-300 mx-1">|</span> ', $icd_strings);
    }
?>
                        </span>
                    </div>
                <?php
endif; ?>

                <?php if (!empty($cpts) || !empty($rx['cpt_procedure'])): ?>
                    <div class="flex">
                        <span class="font-bold text-gray-500 uppercase text-[10px] w-28 shrink-0">Advised Procedure:</span>
                        <span class="font-medium text-indigo-800 flex-grow">
                            <?php
    // Backward compatibility
    if (!empty($rx['cpt_procedure'])) {
        echo(!empty($rx['cpt_code']) ? '<strong class="mr-1">[' . esc($rx['cpt_code']) . ']</strong>' : '') . esc($rx['cpt_procedure']);
    }
    else {
        $cpt_strings = [];
        foreach ($cpts as $cpt) {
            $str = '';
            if (!empty($cpt['code']))
                $str .= '<strong class="mr-1">[' . esc($cpt['code']) . ']</strong>';
            $str .= esc($cpt['description']);
            $cpt_strings[] = $str;
        }
        echo implode(' <span class="text-gray-300 mx-1">|</span> ', $cpt_strings);
    }
?>
                        </span>
                    </div>
                <?php
endif; ?>
            </div>
        </div>

        <!-- Removed Big Rx Symbol per user request -->

        <!-- Medication List Table -->
        <div class="flex-grow mb-6 px-2">
            <?php if (empty($items)): ?>
                <p class="text-[11px] text-gray-500 mx-2 italic">No medications prescribed.</p>
            <?php
else: ?>
                <table class="w-full text-left border-collapse border border-gray-200 shadow-sm">
                    <thead>
                        <tr class="bg-gray-100 border border-gray-300 text-gray-800 text-sm">
                            <th colspan="6" class="p-2 border-b border-gray-300 font-bold uppercase tracking-widest"><i class="fa-solid fa-prescription mr-1 text-indigo-700"></i> Prescribed Medications</th>
                        </tr>
                        <tr class="bg-gray-200 text-gray-700 text-[10px] uppercase tracking-wider">
                            <th class="p-1.5 border border-gray-300 w-8 text-center">Sr.</th>
                            <th class="p-1.5 border border-gray-300 w-1/3">Medicine Name</th>
                            <th class="p-1.5 border border-gray-300">Dosage</th>
                            <th class="p-1.5 border border-gray-300">Frequency</th>
                            <th class="p-1.5 border border-gray-300">Duration</th>
                            <th class="p-1.5 border border-gray-300 w-1/4">Instructions</th>
                        </tr>
                    </thead>
                    <tbody class="text-[11px]">
                        <?php foreach ($items as $idx => $item): ?>
                            <tr class="<?php echo $idx % 2 == 0 ? 'bg-white' : 'bg-gray-50'; ?>">
                                <td class="p-1.5 border border-gray-200 text-center font-bold text-gray-500"><?php echo $idx + 1; ?>.</td>
                                <td class="p-1.5 border border-gray-200 font-bold text-gray-900 text-[12px] uppercase">
                                    <?php echo esc($item['name']); ?>
                                    <?php if (!empty($item['med_type'])): ?>
                                        <span class="text-[9px] font-normal text-gray-500 ml-1 italic capitalize">(<?php echo esc($item['med_type']); ?>)</span>
                                    <?php
        endif; ?>
                                </td>
                                <td class="p-1.5 border border-gray-200 font-medium text-gray-800"><?php echo esc($item['dosage'] ?: '-'); ?></td>
                                <td class="p-1.5 border border-gray-200 font-bold text-indigo-700"><?php echo esc($item['usage_frequency'] ?: '-'); ?></td>
                                <td class="p-1.5 border border-gray-200 font-medium whitespace-nowrap text-gray-800"><?php echo esc($item['duration'] ?: '-'); ?></td>
                                <td class="p-1.5 border border-gray-200 text-[10px] text-gray-700 font-medium italic"><?php echo esc($item['instructions'] ?: '-'); ?></td>
                            </tr>
                        <?php
    endforeach; ?>
                    </tbody>
                </table>
            <?php
endif; ?>
        </div>

        <!-- Advised Lab Tests Section -->
        <?php if (!empty($lab_tests)): ?>
            <div class="mb-6 px-2">
                <div class="bg-gray-100 border border-gray-300 text-gray-800 text-sm font-bold uppercase tracking-widest p-2 mb-2">
                    <i class="fa-solid fa-vials mr-1 text-indigo-700"></i> Advised Laboratory Investigations
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Patient Tests -->
                    <?php if (!empty($patient_tests)): ?>
                        <div class="border-l-4 border-indigo-500 pl-3">
                            <div class="text-[10px] font-bold text-gray-500 uppercase mb-1 flex items-center gap-1">
                                <i class="fa-solid fa-user text-[8px]"></i> Tests for Patient
                            </div>
                            <ul class="list-disc list-inside text-[11px] text-gray-800 space-y-0.5 ml-2">
                                <?php foreach ($patient_tests as $pt): ?>
                                    <li><?php echo esc($pt['test_name']); ?></li>
                                <?php
        endforeach; ?>
                            </ul>
                        </div>
                    <?php
    endif; ?>

                    <!-- Spouse Tests -->
                    <?php if (!empty($spouse_tests)): ?>
                        <div class="border-l-4 border-pink-500 pl-3">
                            <div class="text-[10px] font-bold text-gray-500 uppercase mb-1 flex items-center gap-1">
                                <i class="fa-solid fa-person-half-dress text-[8px]"></i> Tests for Spouse
                            </div>
                            <ul class="list-disc list-inside text-[11px] text-gray-800 space-y-0.5 ml-2">
                                <?php foreach ($spouse_tests as $st): ?>
                                    <li><?php echo esc($st['test_name']); ?></li>
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

        <!-- Footer Notes & Follow-up -->
        <div class="mt-2 flex justify-between items-start gap-4 px-2">
            <!-- General Notes -->
            <?php if (!empty($rx['general_advice'])): ?>
                <div class="flex-grow pt-2 border-t border-gray-300">
                    <h3 class="text-[10px] font-bold uppercase tracking-wider text-gray-600 mb-1">Advice / General Notes</h3>
                    <p class="text-[11px] whitespace-pre-wrap leading-snug"><?php echo esc($rx['general_advice']); ?></p>
                </div>
            <?php
else: ?>
                <div class="flex-grow pt-2 border-t border-gray-300 border-none"></div>
            <?php
endif; ?>

            <!-- Revisit Date -->
            <?php if (!empty($rx['next_visit'])): ?>
                <div class="pt-2 shrink-0 text-right">
                    <div class="inline-block border border-gray-300 rounded-full px-4 py-1.5 bg-gray-50 text-[11px] shadow-sm">
                        <span class="font-bold text-gray-500 uppercase">Next Follow-up Visit:</span>
                        <span class="font-bold text-indigo-800 ml-2"><?php echo date('l, d M Y', strtotime($rx['next_visit'])); ?></span>
                    </div>
                </div>
            <?php
endif; ?>
        </div>

        <!-- Footer: Digital Signature text and image -->
        <div class="print-footer no-print-bg w-full">
            <div class="pt-1 w-full flex justify-between items-end gap-4 relative z-10">
                <div class="text-[9px] text-gray-700 italic pb-1 border-t border-gray-400 pt-3 min-w-[250px] inline-block mt-[-5px]">
                    <strong>Digitally Signed :</strong> Dr. Adnan Jabbar | MBBS, DFM, MH, Fertility & Family Medicine Specialist, Clinical Embryologist
                </div>
                <div class="shrink-0 text-right">
                    <?php if (!empty($rx['digital_signature_path'])): ?>
                        <img src="https://ivfexperts.pk/<?php echo esc($rx['digital_signature_path']); ?>" alt="Signature" class="h-10 object-contain ml-auto" />
                    <?php
endif; ?>
                </div>
            </div>
            <!-- Traceability Code -->
            <?php if (!empty($tracking_code)): ?>
                <div class="traceability-code">TRK-<?php echo $tracking_code; ?></div>
            <?php
endif; ?>
        </div>
    </div>

    <!-- Inject printer script automatically for immediate preview -->
    <script>
        function printDigital() {
            <?php if (!empty($rx['letterhead_image_path'])): ?>
            
            <?php if (isset($_SESSION['admin_id'])): ?>
                // Admin temporary digital print toggle
                document.body.classList.add('digital-mode');
                
                const img = document.createElement('img');
                img.src = 'https://ivfexperts.pk/<?php echo addslashes($rx['letterhead_image_path']); ?>';
                img.setAttribute('class', 'letterhead-bg');
                img.id = 'temp-letterhead';
                document.getElementById('document-container').appendChild(img);

                img.onload = () => {
                    window.print();
                    document.body.classList.remove('digital-mode');
                    img.remove();
                };
                img.onerror = () => {
                    alert("Letterhead Image failed to load. Please ensure it is a valid JPG/PNG.");
                    document.body.classList.remove('digital-mode');
                    img.remove();
                };
            <?php
    else: ?>
                // Patient is already in digital mode permanently
                window.print();
            <?php
    endif; ?>

            <?php
else: ?>
            // No letterhead fallback
            window.print();
            <?php
endif; ?>
        }

        function sendWhatsApp() {
            let phone = "<?php echo esc($rx['phone']); ?>";
            phone = phone.replace(/\D/g, ''); // strip to numbers only
            
            if (!phone || phone.length < 10) {
                let manualPhone = prompt("Patient phone number is missing or invalid. Please enter a valid number (e.g. 923111101483):", "92");
                if (!manualPhone) return;
                phone = manualPhone.replace(/\D/g, '');
            } else if (phone.startsWith('03')) {
                phone = '92' + phone.substring(1);
            }
            
            const hash = "<?php echo $rx['qrcode_hash']; ?>";
            const patientName = "<?php echo esc($rx['first_name'] . ' ' . $rx['last_name']); ?>";
            const link = "https://patient.ivfexperts.pk/verify.php?hash=" + hash;
            
            const text = `Dear ${patientName},\n\nWe hope this message finds you well. Here is your recent Prescription from IVF Experts. You can view and download your secure digital record by clicking the link below:\n\nView & Download Record: ${link}\n\nPlease feel free to reach out if you have any questions. Your health and family are our priority.\n\nRegards,\nDr. Adnan Jabbar\nMBBS, DFM, MH, MPH, CGP\nFertility, Family & Emergency Medicine\n+92 3 111 101 483 (IVF)\nhello@ivfexperts.pk\nwww.ivfexperts.pk`;
            
            const url = `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
            window.open(url, '_blank');
        }

        // Auto-trigger digital print mode for patients on portal
        <?php if (!isset($_SESSION['admin_id'])): ?>
        window.onload = function() {
            setTimeout(() => {
                printDigital();
            }, 500);
        };
        <?php
endif; ?>
    </script>
</body>
</html>
