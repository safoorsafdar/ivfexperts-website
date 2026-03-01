<?php
require_once __DIR__ . '/includes/auth.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0)
    die("Invalid Report ID");

// Fetch SA Data
$sa = null;
try {
    $stmt = $conn->prepare("
        SELECT sa.*, p.first_name, p.last_name, p.mr_number, p.gender, p.phone, p.cnic, p.spouse_name,
               h.name as hospital_name, h.logo_path, h.digital_signature_path 
        FROM semen_analyses sa 
        JOIN patients p ON sa.patient_id = p.id 
        JOIN hospitals h ON sa.hospital_id = h.id 
        WHERE sa.id = ?
    ");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $sa = $stmt->get_result()->fetch_assoc();
    }
}
catch (Exception $e) {
    die("DB Error");
}

if (!$sa)
    die("Report not found.");

// Since this is a custom plain A4 layout (not using hospital letterhead margins, we supply logos ourselves)
$mt = 5;
$mb = 5;
$ml = 12;
$mr = 12;

// Calculated Totals
$total_motility = $sa['pr_motility'] + $sa['np_motility'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Semen Analysis #<?php echo $id; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @page { size: A4; margin: <?php echo $mt; ?>mm <?php echo $mr; ?>mm <?php echo $mb; ?>mm <?php echo $ml; ?>mm; }
        body { background: #f3f4f6; -webkit-print-color-adjust: exact; color: #000; }
        .a4-container {
            width: 210mm; min-height: 297mm; background: #fff; margin: 0 auto;
            padding: <?php echo $mt; ?>mm <?php echo $mr; ?>mm <?php echo $mb; ?>mm <?php echo $ml; ?>mm;
            box-sizing: border-box; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        @media print {
            body { background: #fff; }
            .a4-container { width: 100%; min-height: 297mm; box-shadow: none; margin: 0; }
            .no-print { display: none !important; }
        }
        
        /* Digital Backdrop Classes */
        .digital-mode .a4-container {
            padding: <?php echo $mt; ?>mm <?php echo $mr; ?>mm <?php echo $mb; ?>mm <?php echo $ml; ?>mm !important;
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

        .sa-table th { padding: 2px 6px; text-align: left; background: #f9fafb; font-size: 10px; text-transform: uppercase; border: 1px solid #e5e7eb; }
        .sa-table td { padding: 2px 6px; font-size: 12px; border: 1px solid #e5e7eb; }
        .red-flag { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body class="py-10 print:py-0 <?php echo(!isset($_SESSION['admin_id']) && !empty($sa['letterhead_image_path'])) ? 'digital-mode' : ''; ?>">

    <div class="flex flex-wrap justify-center gap-4 py-4 mb-6 bg-slate-50 border-b border-slate-200 no-print w-full shadow-sm">
        <?php if (isset($_SESSION['admin_id'])): ?>
            <!-- Admin Controls -->
            <button onclick="printDigital()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg shadow-lg font-bold" <?php if (empty($sa['letterhead_image_path']))
        echo 'disabled title="No Letterhead uploaded in settings" style="opacity: 0.5; cursor: not-allowed;"'; ?>>
                <i class="fa-solid fa-file-pdf"></i> Print Digital PDF
            </button>
            <button onclick="window.print()" class="bg-sky-600 hover:bg-sky-700 text-white px-6 py-2 rounded-lg shadow-lg font-bold">
                <i class="fa-solid fa-print"></i> Print on Physical Letterhead
            </button>
            <button onclick="sendWhatsApp()" class="bg-[#25D366] hover:bg-[#128C7E] text-white px-6 py-2 rounded-lg shadow-lg font-bold shadow-green-500/30">
                <i class="fa-brands fa-whatsapp text-lg mr-1"></i> Send via WhatsApp
            </button>
        <?php
else: ?>
            <!-- Patient Portal Controls -->
            <button onclick="printDigital()" class="bg-sky-600 hover:bg-sky-700 text-white px-6 py-2 rounded-lg shadow-lg font-bold">
                <i class="fa-solid fa-download"></i> Download / Print
            </button>
        <?php
endif; ?>
        <button onclick="window.close()" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg shadow-lg">Close</button>
    </div>

    <div class="a4-container flex flex-col relative font-sans" id="document-container" style="padding-bottom: 40px;">
        
        <!-- Permanent Letterhead Background for Patients -->
        <?php if (!isset($_SESSION['admin_id']) && !empty($sa['letterhead_image_path'])): ?>
            <img src="../<?php echo htmlspecialchars($sa['letterhead_image_path']); ?>" alt="Letterhead" class="letterhead-bg" />
        <?php
endif; ?>

        <!-- Header -->
        <div class="flex justify-between items-center mb-1 border-b border-slate-800 pb-0.5">
            <!-- Left Side: IVF Experts Standard Logo (we pull from web root assets if exists, or text fallback) -->
            <div class="w-1/3">
                <img src="../assets/images/logo.png" alt="IVF Experts" class="h-12 object-contain" onerror="this.style.display='none'; document.getElementById('fb1').style.display='block';">
                <div id="fb1" style="display:none;" class="font-extrabold text-xl text-blue-900 tracking-tight">IVF EXPERTS</div>
            </div>
            
            <div class="w-1/3 text-center">
                <h1 class="font-bold text-lg uppercase tracking-widest text-slate-800 m-0">Semen Analysis</h1>
                <p class="text-[8px] text-slate-500 uppercase tracking-widest mt-0">WHO 6th Edition Standard</p>
            </div>
            
            <div class="w-1/3 flex justify-end">
                <?php if (!empty($sa['logo_path']) && $sa['logo_path'] !== 'assets/images/logo.png'): ?>
                    <img src="../<?php echo esc($sa['logo_path']); ?>" alt="<?php echo esc($sa['hospital_name']); ?>" class="h-12 object-contain">
                <?php
endif; ?>
            </div>
        </div>

        <!-- Patient Demographics Box -->
        <div class="border border-slate-300 rounded px-2 py-1 mb-1 bg-slate-50">
            <div class="grid grid-cols-2 gap-y-1 text-[12px]">
                <div><span class="font-semibold w-24 inline-block text-slate-600">Patient Name:</span> <span class="font-bold text-[12px] text-slate-900"><?php echo esc($sa['first_name'] . ' ' . $sa['last_name']); ?></span></div>
                <div><span class="font-semibold w-28 inline-block text-slate-600">Spouse Name:</span> <span class="font-medium text-slate-800"><?php echo esc($sa['spouse_name'] ?: '-'); ?></span></div>
                <div><span class="font-semibold w-24 inline-block text-slate-600">MR Number:</span> <span class="font-mono font-bold text-indigo-800 tracking-wider text-[12px]"><?php echo esc($sa['mr_number']); ?></span></div>
                <div><span class="font-semibold w-28 inline-block text-slate-600">Referred By:</span> <span class="font-medium text-slate-800">Dr. Adnan Jabbar</span></div>
            </div>
            <div class="flex justify-between border-t border-slate-200 mt-1 pt-1 text-[11px]">
                <div><span class="font-semibold text-slate-600">Collection:</span> <?php echo date('d M Y, h:i A', strtotime($sa['collection_time'])); ?></div>
                <div><span class="font-semibold text-slate-600">Examination:</span> <?php echo $sa['examination_time'] ? date('d M Y, h:i A', strtotime($sa['examination_time'])) : '-'; ?></div>
                <div><span class="font-semibold text-slate-600">Abstinence:</span> <span class="font-bold text-slate-800"><?php echo esc($sa['abstinence_days']); ?> Days</span></div>
            </div>
        </div>

        <!-- Macroscopic -->
        <h3 class="font-bold uppercase tracking-widest text-[10px] mb-0.5 bg-slate-800 text-white px-3 py-0.5 rounded-sm shadow-sm">Macroscopic Examination</h3>
        <table class="w-full sa-table mb-1.5">
            <thead>
                <tr>
                    <th class="w-1/3">Parameter</th>
                    <th class="w-1/3 text-center">Result</th>
                    <th class="w-1/3 text-right">WHO 6th Standard Reference</th>
                </tr>
            </thead>
            <tr>
                <td class="w-1/3">Volume</td>
                <td class="w-1/3 text-center font-bold text-slate-900 <?php echo($sa['volume'] > 0 && $sa['volume'] < 1.4) ? 'red-flag' : ''; ?>"><?php echo $sa['volume']; ?> ml</td>
                <td class="w-1/3 text-right text-xs text-slate-500 italic">≥ 1.4 ml</td>
            </tr>
            <tr>
                <td>pH</td>
                <td class="text-center font-bold text-slate-900 <?php echo($sa['ph'] > 0 && $sa['ph'] < 7.2) ? 'red-flag' : ''; ?>"><?php echo $sa['ph']; ?></td>
                <td class="text-right text-xs text-slate-500 italic">≥ 7.2</td>
            </tr>
            <tr>
                <td>Appearance / Color</td>
                <td class="text-center font-bold text-slate-900"><?php echo esc($sa['appearance']); ?></td>
                <td class="text-right text-xs text-slate-500 italic">Normal</td>
            </tr>
            <tr>
                <td>Liquefaction</td>
                <td class="text-center font-bold text-slate-900"><?php echo esc($sa['liquefaction']); ?></td>
                <td class="text-right text-xs text-slate-500 italic">Complete</td>
            </tr>
            <tr>
                <td>Viscosity</td>
                <td class="text-center font-bold text-slate-900"><?php echo esc($sa['viscosity']); ?></td>
                <td class="text-right text-xs text-slate-500 italic">Normal</td>
            </tr>
        </table>

        <!-- Microscopic -->
        <h3 class="font-bold uppercase tracking-widest text-[10px] mb-0.5 bg-slate-800 text-white px-3 py-0.5 rounded-sm shadow-sm">Microscopic Examination</h3>
        <table class="w-full sa-table mb-1.5">
            <thead>
                <tr>
                    <th class="w-1/3">Parameter</th>
                    <th class="w-1/3 text-center">Result</th>
                    <th class="w-1/3 text-right">WHO 6th Standard Reference</th>
                </tr>
            </thead>
            <tr>
                <td class="w-1/3 font-bold bg-slate-100">Sperm Concentration</td>
                <td class="w-1/3 text-center font-bold text-slate-900 <?php echo($sa['concentration'] > 0 && $sa['concentration'] < 16) ? 'red-flag' : ''; ?>"><?php echo $sa['concentration']; ?> <span class="text-[10px] font-normal">M/ml</span></td>
                <td class="w-1/3 text-right text-xs text-slate-500 italic">≥ 16 M/ml</td>
            </tr>
            <tr>
                <td>Progressive Motility (PR)</td>
                <td class="text-center font-bold text-slate-900 <?php echo($sa['pr_motility'] > 0 && $sa['pr_motility'] < 30) ? 'red-flag' : ''; ?>"><?php echo $sa['pr_motility']; ?> %</td>
                <td class="text-right text-xs text-slate-500 italic">≥ 30 %</td>
            </tr>
            <tr>
                <td>Non-Progressive Motility (NP)</td>
                <td class="text-center font-bold text-slate-900"><?php echo $sa['np_motility']; ?> %</td>
                <td></td>
            </tr>
            <tr>
                <td>Immotility (IM)</td>
                <td class="text-center font-bold text-slate-900"><?php echo $sa['im_motility']; ?> %</td>
                <td></td>
            </tr>
            <tr>
                <td class="font-bold bg-slate-100 text-blue-900 border-t-2 border-slate-300">Total Motility (PR + NP)</td>
                <td class="text-center font-bold text-slate-900 border-t-2 border-slate-300 <?php echo($total_motility > 0 && $total_motility < 42) ? 'red-flag' : 'text-blue-700'; ?>"><?php echo $total_motility; ?> %</td>
                <td class="text-right text-xs text-slate-500 border-t-2 border-slate-300 italic">≥ 42 %</td>
            </tr>
            <tr>
                <td>Vitality (Live Sperm)</td>
                <td class="text-center font-bold text-slate-900 <?php echo($sa['vitality'] > 0 && $sa['vitality'] < 54) ? 'red-flag' : ''; ?>"><?php echo $sa['vitality'] ? $sa['vitality'] . ' %' : 'N/A'; ?></td>
                <td class="text-right text-xs text-slate-500 italic">≥ 54 %</td>
            </tr>
        </table>

        <!-- Morphological Examination -->
        <h3 class="font-bold uppercase tracking-widest text-[10px] mb-0.5 bg-slate-800 text-white px-3 py-0.5 rounded-sm shadow-sm">Morphological Examination</h3>
        <table class="w-full sa-table mb-1.5">
            <thead>
                <tr>
                    <th class="w-1/3">Parameter</th>
                    <th class="w-1/3 text-center">Result</th>
                    <th class="w-1/3 text-right">WHO 6th Standard Reference</th>
                </tr>
            </thead>
            <tr>
                <td class="w-1/3">Normal Morphology</td>
                <td class="w-1/3 text-center font-bold text-slate-900 <?php echo($sa['normal_morphology'] > 0 && $sa['normal_morphology'] < 4) ? 'red-flag' : ''; ?>"><?php echo $sa['normal_morphology']; ?> %</td>
                <td class="w-1/3 text-right text-xs text-slate-500 italic">≥ 4 %</td>
            </tr>
            <tr>
                <td>Abnormal Forms</td>
                <td class="text-center font-bold text-slate-900"><?php echo $sa['abnormal_morphology']; ?> %</td>
                <td></td>
            </tr>
            <tr>
                <td>Round Cells</td>
                <td class="text-center font-bold text-slate-900 text-sm" colspan="2"><?php echo esc($sa['round_cells'] ?: '< 1M/ml'); ?></td>
            </tr>
            <tr>
                <td>Pus Cells (WBC)</td>
                <td class="text-center font-bold text-slate-900 text-sm" colspan="2"><?php echo esc($sa['wbc'] ?: 'Nil'); ?></td>
            </tr>
            <tr>
                <td>Agglutination</td>
                <td class="text-center font-bold text-slate-900 text-sm" colspan="2"><?php echo esc($sa['agglutination'] ?: 'Nil'); ?></td>
            </tr>
            <tr>
                <td>Debris</td>
                <td class="text-center font-bold text-slate-900 text-sm" colspan="2"><?php echo esc($sa['debris'] ?: 'Occasional'); ?></td>
            </tr>
        </table>

        <!-- Diagnosis Box -->
        <?php
$diagnosis = $sa['auto_diagnosis'] ?? '';
$display_diagnosis = [];

if (!empty($diagnosis)) {
    $parts = explode(', ', $diagnosis);
    $definitions = [];

    // Safer fetch for compatibility
    $quoted_parts = [];
    foreach ($parts as $p) {
        $quoted_parts[] = "'" . $conn->real_escape_string(trim($p)) . "'";
    }
    $in_list = implode(',', $quoted_parts);

    if (!empty($in_list)) {
        $res_def = $conn->query("SELECT condition_name, definition FROM semen_diagnosis_definitions WHERE condition_name IN ($in_list)");
        if ($res_def) {
            while ($row = $res_def->fetch_assoc()) {
                $definitions[$row['condition_name']] = $row['definition'];
            }
        }
    }

    foreach ($parts as $p) {
        $trimmed_p = trim($p);
        if (isset($definitions[$trimmed_p])) {
            $display_diagnosis[] = "<div class='pb-0.5 border-b border-white/20 last:border-0 last:pb-0'><span class='font-extrabold block text-xs underline decoration-sky-300 underline-offset-4 tracking-wider'>$trimmed_p</span><p class='text-[8.5px] mt-0 text-sky-100 font-normal normal-case leading-tight'>" . $definitions[$trimmed_p] . "</p></div>";
        }
        else {
            $display_diagnosis[] = "<div class='pb-0.5 border-b border-white/20 last:border-0 last:pb-0'><span class='font-extrabold block text-xs'>$trimmed_p</span></div>";
        }
    }
}
?>
        <?php if (!empty($display_diagnosis)): ?>
        <div class="mt-1 bg-gray-100 text-slate-900 rounded-[3px] p-2 border border-gray-300 mx-4">
            <h4 class="uppercase tracking-widest text-[9px] font-bold text-slate-500 mb-1 border-b border-gray-200 pb-0.5">Conclusion / Clinical Diagnosis</h4>
            <div class="space-y-1">
                <?php
    foreach ($display_diagnosis as $d) {
        echo str_replace(['text-sky-100', 'underline decoration-sky-300', 'border-white/20'], ['text-slate-600', 'underline decoration-slate-300', 'border-gray-200'], $d);
    }
?>
            </div>
        </div>
        <?php
endif; ?>

        <?php if (!empty($sa['admin_notes'])): ?>
            <div class="mt-1 p-2 bg-slate-50 text-[10px] border border-slate-200 text-slate-800 rounded-[3px] shadow-sm mx-4">
                <span class="font-bold uppercase text-[9px] block mb-1 text-slate-500 border-b border-slate-100 pb-0.5">Clinical Embryologist Remarks</span>
                <div class="leading-relaxed whitespace-pre-wrap italic text-slate-700"><?php echo esc($sa['admin_notes']); ?></div>
            </div>
        <?php
endif; ?>

        <!-- Push footer to bottom -->
        <div class="flex-grow"></div>

        <!-- Footer -->
        <div class="flex justify-between items-end pb-2 px-6 border-t border-slate-100 mt-2 mx-4 bg-white">
            <div class="flex items-center gap-3 pt-2">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?php echo urlencode('https://ivfexperts.pk/portal/verify.php?hash=' . $sa['qrcode_hash']); ?>" alt="QR Code" class="w-12 h-12 border border-slate-200 p-0.5 bg-white shadow-sm" />
                <div class="text-[8px] text-slate-500 w-48 leading-tight">
                    <span class="font-bold block text-slate-700 text-[9px] mb-0.5">Secure Digitally Verified Record</span>
                    Scan to verify authenticity at ivfexperts.pk.
                </div>
            </div>

            <div class="text-right pt-2">
                <?php if (!empty($sa['digital_signature_path'])): ?>
                    <img src="../<?php echo esc($sa['digital_signature_path']); ?>" alt="Signature" class="h-10 ml-auto object-contain mb-0.5" />
                <?php
endif; ?>
                <div class="font-bold text-[12px] text-slate-900 leading-tight">Dr. Adnan Jabbar</div>
                <div class="text-[8.5px] text-slate-600 leading-tight mt-0">
                    MBBS, DFM, MH, GCP, Family, Fertility & ER Medicine<br>
                    <span class="font-bold text-slate-700 uppercase tracking-widest text-[7.5px]">Clinical Embryologist</span><br>
                    <span class="text-emerald-700 font-bold italic text-[8px]"><i class="fa-solid fa-circle-check"></i> Digitally Verified Report.</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Digital PDF Print & WhatsApp Logic -->
    <script>
        function printDigital() {
            <?php if (!empty($sa['letterhead_image_path'])): ?>
            
            <?php if (isset($_SESSION['admin_id'])): ?>
                // Admin temporary digital print toggle
                document.body.classList.add('digital-mode');
                
                const img = document.createElement('img');
                img.src = '../<?php echo addslashes($sa['letterhead_image_path']); ?>';
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
            let phone = "<?php echo esc($sa['phone']); ?>";
            phone = phone.replace(/\D/g, ''); // strip to numbers only
            
            if (!phone || phone.length < 10) {
                let manualPhone = prompt("Patient phone number is missing or invalid. Please enter a valid number (e.g. 923111101483):", "92");
                if (!manualPhone) return;
                phone = manualPhone.replace(/\D/g, '');
            } else if (phone.startsWith('03')) {
                phone = '92' + phone.substring(1);
            }
            
            const hash = "<?php echo $sa['qrcode_hash']; ?>";
            const patientName = "<?php echo esc($sa['first_name'] . ' ' . $sa['last_name']); ?>";
            const link = "https://ivfexperts.pk/portal/verify.php?hash=" + hash;
            
            const text = `Dear ${patientName},\n\nWe hope this message finds you well. Here is your recent Semen Analysis from IVF Experts. You can view and download your secure digital record by clicking the link below:\n\nView & Download Record: ${link}\n\nPlease feel free to reach out if you have any questions. Your health and family are our priority.\n\nRegards,\nDr. Adnan Jabbar\nMBBS, DFM, MH, MPH, CGP\nFertility, Family & Emergency Medicine\n+92 3 111 101 483 (IVF)\nhello@ivfexperts.pk\nwww.ivfexperts.pk`;
            
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
