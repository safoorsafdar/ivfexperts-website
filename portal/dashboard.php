<?php
session_start();
if (!isset($_SESSION['portal_patient_id'])) {
    header("Location: index.php");
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';
$patient_id = intval($_SESSION['portal_patient_id']);

// Fetch Patient Info
$stmt = $conn->prepare("SELECT mr_number, first_name, last_name, gender, cnic, phone, spouse_name FROM patients WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if (!$patient) {
    session_destroy();
    die("Account anomaly detected.");
}

// 1. Gather all linked patient IDs (Spouse linking)
$patient_ids = [$patient_id];
$cnic_clean = preg_replace('/[^0-9]/', '', $patient['cnic'] ?? '');
$phone = $patient['phone'] ?? '';
$mr = $patient['mr_number'] ?? '';

// Find if anyone matches this patient's spouse_name and shares contact info
if (!empty($patient['spouse_name'])) {
    $stmt_spouse = $conn->prepare("SELECT id FROM patients WHERE first_name = ? AND (phone = ? OR mr_number = ? OR REPLACE(cnic, '-', '') = ?)");
    $stmt_spouse->bind_param("ssss", $patient['spouse_name'], $phone, $mr, $cnic_clean);
    $stmt_spouse->execute();
    $res_spouse = $stmt_spouse->get_result();
    while ($row = $res_spouse->fetch_assoc()) {
        $patient_ids[] = $row['id'];
    }
}

// Find if anyone listed THIS patient as their spouse
$stmt_rev = $conn->prepare("SELECT id FROM patients WHERE spouse_name = ? AND (phone = ? OR mr_number = ? OR REPLACE(cnic, '-', '') = ?)");
$stmt_rev->bind_param("ssss", $patient['first_name'], $phone, $mr, $cnic_clean);
$stmt_rev->execute();
$res_rev = $stmt_rev->get_result();
while ($row = $res_rev->fetch_assoc()) {
    $patient_ids[] = $row['id'];
}

$patient_ids = array_unique($patient_ids);
$ids_csv = implode(',', $patient_ids);

// Fetch all 5 document streams for all linked IDs with spouse attribution
$prescriptions = [];
$res = $conn->query("SELECT p.*, pt.first_name, pt.last_name FROM prescriptions p JOIN patients pt ON p.patient_id = pt.id WHERE p.patient_id IN ($ids_csv) ORDER BY p.created_at DESC");
if ($res) {
    while ($row = $res->fetch_assoc())
        $prescriptions[] = $row;
}

$ultrasounds = [];
$res = $conn->query("SELECT u.*, pt.first_name, pt.last_name FROM patient_ultrasounds u JOIN patients pt ON u.patient_id = pt.id WHERE u.patient_id IN ($ids_csv) ORDER BY u.created_at DESC");
if ($res) {
    while ($row = $res->fetch_assoc())
        $ultrasounds[] = $row;
}

$semen = [];
$res = $conn->query("SELECT s.*, pt.first_name, pt.last_name FROM semen_analyses s JOIN patients pt ON s.patient_id = pt.id WHERE s.patient_id IN ($ids_csv) ORDER BY s.collection_time DESC");
if ($res) {
    while ($row = $res->fetch_assoc())
        $semen[] = $row;
}

$receipts = [];
$res = $conn->query("SELECT r.*, pt.first_name, pt.last_name FROM receipts r JOIN patients pt ON r.patient_id = pt.id WHERE r.patient_id IN ($ids_csv) ORDER BY r.receipt_date DESC");
if ($res) {
    while ($row = $res->fetch_assoc())
        $receipts[] = $row;
}

$lab_results = [];
try {
    $res = $conn->query("SELECT plt.*, ltd.test_name, ltd.unit, ltd.reference_range_male, ltd.reference_range_female, pt.first_name, pt.last_name, pt.gender as pt_gender FROM patient_lab_results plt JOIN lab_tests_directory ltd ON plt.test_id = ltd.id JOIN patients pt ON plt.patient_id = pt.id WHERE plt.patient_id IN ($ids_csv) ORDER BY plt.status DESC, plt.test_date DESC, plt.id DESC");
    if ($res) {
        while ($row = $res->fetch_assoc())
            $lab_results[] = $row;
    }
}
catch (Exception $e) {
}

$histories = [];
try {
    $res = $conn->query("SELECT h.*, pt.first_name, pt.last_name FROM patient_history h JOIN patients pt ON h.patient_id = pt.id WHERE h.patient_id IN ($ids_csv) ORDER BY h.recorded_at DESC");
    if ($res) {
        while ($row = $res->fetch_assoc())
            $histories[] = $row;
    }
}
catch (Exception $e) {
}

$advised_procedures = [];
try {
    $res = $conn->query("SELECT ap.*, pt.first_name, pt.last_name, 
            (SELECT GROUP_CONCAT(status SEPARATOR ',') FROM receipts WHERE advised_procedure_id = ap.id) as payment_statuses,
            (SELECT SUM(amount) FROM receipts WHERE advised_procedure_id = ap.id) as total_billed
            FROM advised_procedures ap JOIN patients pt ON ap.patient_id = pt.id WHERE ap.patient_id IN ($ids_csv) ORDER BY ap.date_advised DESC, ap.id DESC");
    if ($res) {
        while ($row = $res->fetch_assoc())
            $advised_procedures[] = $row;
    }
}
catch (Exception $e) {
}

// Logout handler
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Portal - IVF Experts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .tab-active { @apply border-indigo-600 text-indigo-600 bg-indigo-50/50; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen font-sans text-slate-900" x-data="{ activeTab: 'timeline' }">

    <!-- Navigation -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 h-16 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                    <i class="fa-solid fa-heart-pulse text-xl"></i>
                </div>
                <div>
                    <span class="font-black text-xl tracking-tight text-slate-800">IVF<span class="text-indigo-600">EXPERTS</span></span>
                    <span class="block text-[10px] uppercase font-bold tracking-widest text-slate-400 leading-none">Patient Portal</span>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="hidden md:block text-right">
                    <div class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars($patient['first_name']); ?></div>
                    <div class="text-[10px] text-slate-400 font-mono"><?php echo htmlspecialchars($patient['mr_number']); ?></div>
                </div>
                <a href="?logout=1" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-rose-50 hover:text-rose-600 transition-all">
                    <i class="fa-solid fa-power-off"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Dashboard Header -->
        <div class="mb-10">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Partnership Dashboard</h1>
                    <p class="text-slate-500 mt-1">Comprehensive medical journey for 
                        <span class="font-bold text-slate-700"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></span>
                        <?php if ($patient['spouse_name']): ?>
                            & <span class="font-bold text-slate-700"><?php echo htmlspecialchars($patient['spouse_name']); ?></span>
                        <?php
endif; ?>
                    </p>
                </div>
                
                <!-- Quick Info Cards -->
                <div class="flex gap-4">
                    <div class="bg-white p-3 pr-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-vials"></i>
                        </div>
                        <div>
                            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Reports</div>
                            <div class="text-lg font-black text-slate-800"><?php echo count($lab_results) + count($ultrasounds); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content with Tabs -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-3xl border border-slate-200 p-2 shadow-sm sticky top-24">
                    <nav class="space-y-1">
                        <button @click="activeTab = 'timeline'" :class="activeTab === 'timeline' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'" class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition-all font-bold text-sm">
                            <i class="fa-solid fa-timeline text-lg"></i> Clinical Timeline
                        </button>
                        <button @click="activeTab = 'labs'" :class="activeTab === 'labs' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'" class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition-all font-bold text-sm">
                            <i class="fa-solid fa-vials text-lg"></i> Lab Results
                        </button>
                        <button @click="activeTab = 'diagnostic'" :class="activeTab === 'diagnostic' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'" class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition-all font-bold text-sm">
                            <i class="fa-solid fa-microscope text-lg"></i> Scans & Reports
                        </button>
                        <button @click="activeTab = 'prescriptions'" :class="activeTab === 'prescriptions' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'" class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition-all font-bold text-sm">
                            <i class="fa-solid fa-prescription text-lg"></i> Prescriptions
                        </button>
                        <button @click="activeTab = 'billing'" :class="activeTab === 'billing' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'" class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition-all font-bold text-sm">
                            <i class="fa-solid fa-receipt text-lg"></i> Billing History
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Content Area -->
            <div class="lg:col-span-3">
                
                <!-- Tab: Clinical Timeline -->
                <div x-show="activeTab === 'timeline'" x-cloak>
                    <div class="space-y-6">
                        <h2 class="text-xl font-black text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-notes-medical text-indigo-600"></i> Clinical Visit History
                        </h2>
                        
                        <?php if (empty($histories)): ?>
                            <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center">
                                <i class="fa-solid fa-calendar-day text-5xl text-slate-200 mb-4 block"></i>
                                <div class="text-slate-400 font-bold">No clinical visits recorded yet.</div>
                            </div>
                        <?php
else: ?>
                            <div class="relative border-l-2 border-indigo-100 ml-4 pl-8 space-y-12">
                                <?php foreach ($histories as $h): ?>
                                    <div class="relative">
                                        <!-- Timeline Dot -->
                                        <div class="absolute -left-[41px] top-0 w-5 h-5 bg-white border-4 border-indigo-600 rounded-full shadow-sm"></div>
                                        
                                        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
                                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                                                <div>
                                                    <span class="text-[10px] uppercase font-black tracking-widest text-slate-400 bg-slate-50 px-2 py-1 rounded-md">
                                                        <?php echo date('d M Y', strtotime($h['recorded_at'])); ?> at <?php echo date('h:i A', strtotime($h['recorded_at'])); ?>
                                                    </span>
                                                    <h3 class="text-lg font-black text-slate-800 mt-1"><?php echo htmlspecialchars($h['diagnosis'] ?: 'Clinical Assessment'); ?></h3>
                                                </div>
                                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider <?php echo $h['record_for'] === 'Spouse' ? 'bg-pink-100 text-pink-700' : 'bg-indigo-100 text-indigo-700'; ?>">
                                                    <?php echo $h['record_for'] === 'Spouse' ? 'Partner Record' : 'Patient Record'; ?>
                                                </span>
                                            </div>
                                            
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                                                <div class="prose prose-slate prose-sm max-w-none">
                                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Observation / Advice</div>
                                                    <div class="text-slate-600 leading-relaxed"><?php echo nl2br(htmlspecialchars($h['clinical_notes'] . "\n" . $h['advice'])); ?></div>
                                                </div>
                                                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Medication / Plan</div>
                                                    <div class="text-slate-700 font-bold"><?php echo htmlspecialchars($h['medication'] ?: 'As per original prescription'); ?></div>
                                                </div>
                                            </div>

                                            <?php if ($h['next_visit']): ?>
                                                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center gap-2 text-indigo-600 text-xs font-bold">
                                                    <i class="fa-solid fa-calendar-star"></i> Next Visit: <?php echo date('d M Y', strtotime($h['next_visit'])); ?>
                                                </div>
                                            <?php
        endif; ?>
                                        </div>
                                    </div>
                                <?php
    endforeach; ?>
                            </div>
                        <?php
endif; ?>
                    </div>
                </div>

                <!-- Tab: Lab Results -->
                <div x-show="activeTab === 'labs'" x-cloak>
                    <div class="space-y-6">
                        <h2 class="text-xl font-black text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-vials text-indigo-600"></i> Laboratory & Blood Tests
                        </h2>

                        <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
                            <?php if (empty($lab_results)): ?>
                                <div class="p-12 text-center">
                                    <i class="fa-solid fa-box-open text-5xl text-slate-100 mb-4 block"></i>
                                    <div class="text-slate-400 font-bold">No laboratory results found.</div>
                                </div>
                            <?php
else: ?>
                                <table class="w-full text-left">
                                    <thead>
                                        <tr class="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100">
                                            <th class="px-6 py-4">Test Attribution</th>
                                            <th class="px-6 py-4">Status</th>
                                            <th class="px-6 py-4">Biological Markers</th>
                                            <th class="px-6 py-4 text-right">Date / Scan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <?php foreach ($lab_results as $lr): ?>
                                            <tr class="hover:bg-slate-50/50 transition-colors">
                                                <td class="px-6 py-5">
                                                    <div class="font-black text-slate-800"><?php echo htmlspecialchars($lr['test_name']); ?></div>
                                                    <span class="text-[10px] font-bold uppercase <?php echo $lr['test_for'] === 'Spouse' ? 'text-pink-500' : 'text-indigo-500'; ?>">
                                                        For: <?php echo $lr['test_for']; ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-5">
                                                    <?php if ($lr['status'] === 'Pending'): ?>
                                                        <span class="flex items-center gap-1.5 text-orange-500 font-black text-[10px] uppercase">
                                                            <i class="fa-solid fa-spinner animate-spin"></i> Processing
                                                        </span>
                                                    <?php
        else: ?>
                                                        <span class="flex items-center gap-1.5 text-emerald-500 font-black text-[10px] uppercase tracking-wider">
                                                            <i class="fa-solid fa-check-double"></i> Confirmed
                                                        </span>
                                                    <?php
        endif; ?>
                                                </td>
                                                <td class="px-6 py-5">
                                                    <?php if ($lr['status'] === 'Pending'): ?>
                                                        <div class="text-slate-300 italic text-xs">Awaiting lab upload...</div>
                                                    <?php
        else: ?>
                                                        <div class="flex items-baseline gap-2">
                                                            <span class="text-xl font-black text-slate-900"><?php echo htmlspecialchars($lr['result_value']); ?></span>
                                                            <span class="text-xs text-slate-400 font-mono"><?php echo htmlspecialchars($lr['unit']); ?></span>
                                                        </div>
                                                        <div class="text-[9px] text-slate-400 mt-1 max-w-[150px] leading-tight italic">
                                                            Ref: <?php
            $targetGender = ($lr['test_for'] === 'Patient') ? $lr['pt_gender'] : ($lr['pt_gender'] === 'Male' ? 'Female' : 'Male');
            echo($targetGender === 'Male') ? htmlspecialchars($lr['reference_range_male']) : htmlspecialchars($lr['reference_range_female']);
?>
                                                        </div>
                                                    <?php
        endif; ?>
                                                </td>
                                                <td class="px-6 py-5 text-right">
                                                    <div class="text-xs font-bold text-slate-700 mb-2"><?php echo date('d M Y', strtotime($lr['test_date'])); ?></div>
                                                    <?php if (!empty($lr['scanned_report_path'])): ?>
                                                        <a href="../<?php echo htmlspecialchars($lr['scanned_report_path']); ?>" target="_blank" class="text-[10px] font-black uppercase text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100 hover:bg-indigo-600 hover:text-white transition-all">
                                                            <i class="fa-solid fa-file-pdf mr-1"></i> Original PDF
                                                        </a>
                                                    <?php
        else: ?>
                                                        <span class="text-[9px] text-slate-300 uppercase font-black">Internal Record</span>
                                                    <?php
        endif; ?>
                                                </td>
                                            </tr>
                                        <?php
    endforeach; ?>
                                    </tbody>
                                </table>
                            <?php
endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tab: Scans & Reports -->
                <div x-show="activeTab === 'diagnostic'" x-cloak>
                    <div class="space-y-6">
                        <h2 class="text-xl font-black text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-image text-indigo-600"></i> Ultrasounds & Advanced Diagnostics
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php
$all_scans = array_merge(
    array_map(function ($i) {
        $i['type_label'] = 'Ultrasound';
        $i['icon'] = 'fa-solid fa-image';
        return $i; }, $ultrasounds),
    array_map(function ($i) {
        $i['type_label'] = 'Semen Analysis';
        $i['icon'] = 'fa-solid fa-microscope';
        $i['report_title'] = 'Semen Analysis Report';
        return $i; }, $semen)
);
usort($all_scans, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']); });
?>

                            <?php if (empty($all_scans)): ?>
                                <div class="md:col-span-2 bg-white rounded-3xl border border-slate-200 p-12 text-center">
                                    <div class="text-slate-400 font-bold">No diagnostic reports available.</div>
                                </div>
                            <?php
else: ?>
                                <?php foreach ($all_scans as $s): ?>
                                    <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-sm flex justify-between items-center group hover:border-indigo-200 transition-all">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                                <i class="<?php echo $s['icon']; ?>"></i>
                                            </div>
                                            <div>
                                                <div class="text-[10px] font-black text-indigo-500 uppercase tracking-widest"><?php echo $s['type_label']; ?></div>
                                                <div class="font-black text-slate-800 leading-tight"><?php echo htmlspecialchars($s['report_title'] ?? ($s['auto_diagnosis'] ?: 'Diagnostic Report')); ?></div>
                                                <div class="text-[10px] text-slate-400 mt-1 font-bold">
                                                    <?php echo date('d M Y', strtotime($s['created_at'])); ?> • <?php echo htmlspecialchars($s['first_name']); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <?php if ($s['type_label'] === 'Ultrasound'): ?>
                                                <a href="view.php?type=usg&hash=<?php echo $s['qrcode_hash']; ?>" target="_blank" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-indigo-600 hover:text-white transition-all"><i class="fa-solid fa-eye text-xs"></i></a>
                                            <?php
        else: ?>
                                                <a href="view.php?type=sa&hash=<?php echo $s['qrcode_hash']; ?>" target="_blank" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-teal-600 hover:text-white transition-all"><i class="fa-solid fa-eye text-xs"></i></a>
                                            <?php
        endif; ?>
                                        </div>
                                    </div>
                                <?php
    endforeach; ?>
                            <?php
endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tab: Prescriptions -->
                <div x-show="activeTab === 'prescriptions'" x-cloak>
                    <div class="space-y-6">
                        <h2 class="text-xl font-black text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-prescription-bottle-medical text-indigo-600"></i> Digital Prescriptions
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php if (empty($prescriptions)): ?>
                                <div class="md:col-span-2 bg-white rounded-3xl border border-slate-200 p-12 text-center text-slate-400 font-bold">No active prescriptions.</div>
                            <?php
else: ?>
                                <?php foreach ($prescriptions as $rx): ?>
                                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-all">
                                        <div class="flex justify-between items-start mb-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center font-black">Rx</div>
                                                <div>
                                                    <div class="text-sm font-black text-slate-800">E-Prescription #<?php echo $rx['id']; ?></div>
                                                    <div class="text-[10px] text-slate-400 uppercase font-black"><?php echo htmlspecialchars($rx['first_name']); ?> • <?php echo date('d M Y', strtotime($rx['created_at'])); ?></div>
                                                </div>
                                            </div>
                                            <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded <?php echo($rx['record_for'] ?? 'Patient') === 'Spouse' ? 'bg-pink-50 text-pink-600' : 'bg-indigo-50 text-indigo-600'; ?>">
                                                <?php echo $rx['record_for'] ?? 'Patient'; ?>
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500 line-clamp-2 mb-4"><?php echo htmlspecialchars($rx['notes'] ?: 'Medication plan issued during consultation.'); ?></p>
                                        <div class="flex gap-2">
                                            <a href="view.php?type=rx&hash=<?php echo $rx['qrcode_hash']; ?>" target="_blank" class="flex-1 bg-indigo-600 text-white text-[10px] font-black uppercase text-center py-2 rounded-xl hover:bg-slate-900 transition-all">View Record</a>
                                            <?php if ($rx['scanned_report_path']): ?>
                                                <a href="../<?php echo htmlspecialchars($rx['scanned_report_path']); ?>" target="_blank" class="px-3 py-2 bg-slate-100 text-slate-500 rounded-xl hover:bg-slate-200 transition-all"><i class="fa-solid fa-file-pdf"></i></a>
                                            <?php
        endif; ?>
                                        </div>
                                    </div>
                                <?php
    endforeach; ?>
                            <?php
endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tab: Billing -->
                <div x-show="activeTab === 'billing'" x-cloak>
                    <div class="space-y-6">
                        <h2 class="text-xl font-black text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-receipt text-indigo-600"></i> Payment & Billing Records
                        </h2>
                        
                        <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
                            <?php if (empty($receipts)): ?>
                                <div class="p-12 text-center text-slate-400 font-bold">No billing history found.</div>
                            <?php
else: ?>
                                <table class="w-full text-left">
                                    <thead>
                                        <tr class="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100">
                                            <th class="px-6 py-4">Transaction / Service</th>
                                            <th class="px-6 py-4">Financials</th>
                                            <th class="px-6 py-4 text-right">Receipt</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <?php foreach ($receipts as $r): ?>
                                            <tr class="hover:bg-slate-50/50 transition-colors">
                                                <td class="px-6 py-5">
                                                    <div class="font-black text-slate-800"><?php echo htmlspecialchars($r['procedure_name']); ?></div>
                                                    <div class="text-[10px] text-slate-400 mt-1 font-bold">
                                                        <?php echo date('d M Y', strtotime($r['receipt_date'])); ?> • <?php echo htmlspecialchars($r['first_name']); ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-5">
                                                    <div class="text-lg font-black text-emerald-600">Rs. <?php echo number_format($r['amount'], 0); ?></div>
                                                    <div class="text-[9px] uppercase font-black text-slate-400 tracking-wider">Method: Paid Item</div>
                                                </td>
                                                <td class="px-6 py-5 text-right">
                                                    <a href="view.php?type=receipt&hash=<?php echo $r['qrcode_hash']; ?>" target="_blank" class="w-10 h-10 rounded-2xl bg-slate-100 inline-flex items-center justify-center text-slate-500 hover:bg-emerald-600 hover:text-white transition-all">
                                                        <i class="fa-solid fa-cloud-arrow-down text-sm"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php
    endforeach; ?>
                                    </tbody>
                                </table>
                            <?php
endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>
