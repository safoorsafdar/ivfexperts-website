<?php
session_start();
if (!isset($_SESSION['portal_patient_id'])) {
    header("Location: index.php");
    exit;
}

// Logout — handled at top before any DB queries or HTML output
// Require POST for security (to prevent CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: index.php");
    exit;
}

require_once dirname(__DIR__) . '/4me/config/db.php';
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

// --- P2 Data Logic ---
// 1. Find the nearest upcoming visit
$next_visits = [];
foreach ($histories as $h) {
    if (!empty($h['next_visit']) && $h['next_visit'] >= date('Y-m-d')) {
        $next_visits[] = $h['next_visit'];
    }
}
foreach ($prescriptions as $rx) {
    if (!empty($rx['next_visit']) && $rx['next_visit'] >= date('Y-m-d')) {
        $next_visits[] = $rx['next_visit'];
    }
}
sort($next_visits);
$next_visit_date = $next_visits[0] ?? null;

// 2. Pending labs
$pending_labs = array_filter($lab_results, fn($l) => $l['status'] === 'Pending');
$pending_count = count($pending_labs);

// 3. Billing summary
$total_paid = array_sum(array_column(array_filter($receipts, fn($r) => strtolower($r['status'] ?? '') === 'paid'), 'amount'));
$total_pending = array_sum(array_column(array_filter($receipts, fn($r) => strtolower($r['status'] ?? '') !== 'paid'), 'amount'));

// 4. Update Quick Stats count for UI
$portal_tabs = [
    ['id' => 'timeline', 'icon' => 'fa-notes-medical', 'label' => 'Clinical Timeline', 'count' => count($histories)],
    ['id' => 'procedures', 'icon' => 'fa-syringe', 'label' => 'My Procedures', 'count' => count($advised_procedures)],
    ['id' => 'labs', 'icon' => 'fa-vials', 'label' => 'Lab Results', 'count' => count($lab_results)],
    ['id' => 'diagnostic', 'icon' => 'fa-image', 'label' => 'Scans & Reports', 'count' => count($ultrasounds) + count($semen)],
    ['id' => 'prescriptions', 'icon' => 'fa-prescription', 'label' => 'Prescriptions', 'count' => count($prescriptions)],
    ['id' => 'billing', 'icon' => 'fa-receipt', 'label' => 'Billing', 'count' => count($receipts)],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Records — IVF Experts Portal</title>
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; }
        .prose-portal p { margin-bottom: 0.5em; }
        .prose-portal ul, .prose-portal ol { padding-left: 1.25em; margin-bottom: 0.5em; }
        .prose-portal li { margin-bottom: 0.15em; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-900" x-data="{ activeTab: 'timeline' }">

    <!-- Navigation -->
    <nav class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50 shadow-xl shadow-slate-900/50">
        <div class="max-w-7xl mx-auto px-4 h-16 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-900/50">
                    <i class="fa-solid fa-heart-pulse text-base"></i>
                </div>
                <div>
                    <span class="font-black text-lg tracking-tight text-white">IVF<span class="text-indigo-400">EXPERTS</span></span>
                    <span class="hidden sm:block text-[9px] uppercase font-black tracking-[0.2em] text-white/25 leading-none">Patient Portal</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2.5 bg-slate-800 rounded-xl px-3 py-2 border border-slate-700">
                    <div class="w-7 h-7 bg-indigo-600/30 rounded-lg flex items-center justify-center text-indigo-400 font-black text-xs shrink-0">
                        <?php echo strtoupper(substr($patient['first_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <div class="text-xs font-black text-white leading-tight"><?php echo htmlspecialchars($patient['first_name']); ?></div>
                        <div class="text-[9px] text-white/30 font-mono leading-tight"><?php echo htmlspecialchars($patient['mr_number']); ?></div>
                    </div>
                </div>
                <form method="POST" action="dashboard.php" class="flex items-center">
                    <button type="submit" name="logout" value="1"
                            class="w-9 h-9 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-rose-500/20 hover:text-rose-400 border border-slate-700 transition-all focus:outline-none"
                            title="Sign Out">
                        <i class="fa-solid fa-right-from-bracket text-sm"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Dashboard Header -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-slate-900 to-indigo-950 rounded-3xl p-7 md:p-9 shadow-2xl">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                    <div>
                        <div class="text-[9px] font-black text-indigo-400 uppercase tracking-[0.25em] mb-2">Patient Portal</div>
                        <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight mb-1">
                            <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>
                        </h1>
                        <div class="flex flex-wrap items-center gap-3 text-sm">
                            <span class="font-mono text-indigo-300 text-xs"><?php echo htmlspecialchars($patient['mr_number']); ?></span>
                            <?php if ($patient['spouse_name']): ?>
                            <span class="text-slate-500">·</span>
                            <span class="flex items-center gap-1.5 text-pink-400 text-xs font-bold">
                                <i class="fa-solid fa-heart text-[10px]"></i> <?php echo htmlspecialchars($patient['spouse_name']); ?>
                            </span>
                            <?php
endif; ?>
                        </div>

                        <?php if ($next_visit_date): ?>
                        <div class="mt-4 bg-indigo-500/10 border border-indigo-400/20 rounded-2xl px-4 py-2.5 flex items-center gap-3">
                            <i class="fa-solid fa-calendar-star text-indigo-400 text-lg shrink-0"></i>
                            <div>
                                <div class="text-indigo-300 text-[10px] font-black uppercase tracking-wider leading-none mb-1">Next Appointment</div>
                                <div class="text-white font-black text-sm">
                                    <?php echo date('l, d F Y', strtotime($next_visit_date)); ?>
                                </div>
                            </div>
                        </div>
                        <?php
endif; ?>
                    </div>

                    <!-- Quick Stats -->
                    <div class="flex gap-3">
                        <?php
$quick = [
    ['n' => count($prescriptions), 'l' => 'Rx', 'c' => 'indigo', 'i' => 'fa-prescription'],
    ['n' => count($lab_results), 'l' => 'Tests', 'c' => 'teal', 'i' => 'fa-vials'],
    ['n' => count($ultrasounds), 'l' => 'Scans', 'c' => 'emerald', 'i' => 'fa-image'],
];
foreach ($quick as $q):
?>
                        <div class="bg-white/10 backdrop-blur-sm border border-white/10 rounded-2xl px-4 py-3 text-center min-w-[64px]">
                            <div class="text-xl font-black text-white"><?php echo $q['n']; ?></div>
                            <div class="text-[9px] font-black text-white/40 uppercase tracking-widest"><?php echo $q['l']; ?></div>
                        </div>
                        <?php
endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($pending_count > 0): ?>
        <div class="mb-6 bg-amber-500/10 border border-amber-500/20 rounded-2xl px-5 py-3.5 flex items-center gap-4">
            <div class="w-10 h-10 bg-amber-500/20 rounded-xl flex items-center justify-center text-amber-500 text-xl shrink-0">
                <i class="fa-solid fa-spinner animate-spin"></i>
            </div>
            <div>
                <div class="text-amber-700 font-black text-sm"><?php echo $pending_count; ?> Lab Result<?php echo $pending_count > 1 ? 's' : ''; ?> Processing</div>
                <div class="text-amber-600/70 text-xs font-medium mt-0.5">We'll notify you once they are confirmed. Check back soon.</div>
            </div>
        </div>
        <?php
endif; ?>

        <!-- Mobile Navigation (shown on mobile, hidden on lg) -->
        <div class="lg:hidden flex gap-2 overflow-x-auto pb-4 mb-6 -mx-4 px-4 scrollbar-hide">
            <?php foreach ($portal_tabs as $tab): ?>
            <button @click="activeTab = '<?php echo $tab['id']; ?>'"
                    :class="activeTab === '<?php echo $tab['id']; ?>' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-white text-slate-500 border-slate-200'"
                    class="shrink-0 flex items-center gap-2 px-5 py-3 rounded-2xl text-xs font-black whitespace-nowrap border transition-all">
                <i class="fa-solid <?php echo $tab['icon']; ?>"></i>
                <?php echo $tab['label']; ?>
                <?php if ($tab['count'] > 0): ?>
                    <span class="bg-black/10 px-1.5 py-0.5 rounded-md text-[9px]"><?php echo $tab['count']; ?></span>
                <?php
    endif; ?>
            </button>
            <?php
endforeach; ?>
        </div>

        <!-- Main Content with Tabs -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1">
                <div class="bg-slate-900 rounded-3xl border border-slate-800 p-2 shadow-xl sticky top-24">
                    <nav class="space-y-1">
                        <?php foreach ($portal_tabs as $tab): ?>
                        <button @click="activeTab = '<?php echo $tab['id']; ?>'"
                                :class="activeTab === '<?php echo $tab['id']; ?>' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/40' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition-all font-bold text-sm group">
                            <i class="fa-solid <?php echo $tab['icon']; ?> text-base shrink-0 group-hover:scale-110 transition-transform"></i>
                            <span class="text-left flex-1 whitespace-nowrap"><?php echo $tab['label']; ?></span>
                            <?php if ($tab['count'] > 0): ?>
                            <span :class="activeTab === '<?php echo $tab['id']; ?>' ? 'bg-white/20 text-white' : 'bg-slate-700 text-slate-400 group-hover:bg-slate-600'"
                                  class="text-[9px] font-black px-2 py-0.5 rounded-full shrink-0 transition-colors">
                                <?php echo $tab['count']; ?>
                            </span>
                            <?php
    endif; ?>
                        </button>
                        <?php
endforeach; ?>
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
                                <i class="fa-solid fa-calendar-day text-5xl text-slate-100 mb-4 block"></i>
                                <div class="text-slate-400 font-bold max-w-xs mx-auto">Your clinical visit notes will appear here after your consultation with Dr. Adnan Jabbar.</div>
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
                                                    <div class="text-slate-600 leading-relaxed"><?php
        // clinical_notes may contain HTML from Quill editor — render it safely (admin-generated content)
        $notes_html = $h['clinical_notes'];
        $advice_html = nl2br(htmlspecialchars($h['advice'] ?? ''));
        // Strip potentially dangerous tags but preserve Quill's formatting tags
        $allowed_tags = '<p><br><b><strong><em><i><u><ul><ol><li><span><s>';
        echo strip_tags($notes_html, $allowed_tags);
        if (!empty($h['advice']))
            echo '<div class="mt-2 border-t border-slate-100 pt-2">' . $advice_html . '</div>';
?></div>
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

                <!-- Tab: My Procedures -->
                <div x-show="activeTab === 'procedures'" x-cloak>
                    <div class="space-y-6">
                        <h2 class="text-xl font-black text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-syringe text-indigo-600"></i> Advised & Upcoming Procedures
                        </h2>
                        <?php if (empty($advised_procedures)): ?>
                            <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center text-slate-400 font-bold max-w-xs mx-auto">
                                No procedures have been advised for you at this time.
                            </div>
                        <?php
else: ?>
                            <div class="space-y-4">
                                <?php foreach ($advised_procedures as $ap): ?>
                                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <div class="font-black text-slate-800"><?php echo htmlspecialchars($ap['procedure_name']); ?></div>
                                                <div class="text-xs text-slate-400 mt-1">
                                                    Advised: <?php echo date('d M Y', strtotime($ap['date_advised'])); ?>
                                                    • <?php echo htmlspecialchars($ap['first_name']); ?>
                                                </div>
                                            </div>
                                            <span class="text-[10px] font-black uppercase px-3 py-1 rounded-full
                                                <?php echo $ap['status'] === 'Completed' ? 'bg-emerald-100 text-emerald-700' : ($ap['status'] === 'In Progress' ? 'bg-amber-100 text-amber-700' : 'bg-indigo-100 text-indigo-700'); ?>">
                                                <?php echo htmlspecialchars($ap['status']); ?>
                                            </span>
                                        </div>
                                        <?php if ($ap['total_billed']): ?>
                                            <div class="mt-3 pt-3 border-t border-slate-100 text-sm text-slate-600">
                                                Total billed: <span class="font-black text-emerald-600">Rs. <?php echo number_format($ap['total_billed'], 0); ?></span>
                                            </div>
                                        <?php
        endif; ?>
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

                        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                            <?php if (empty($lab_results)): ?>
                                <div class="p-12 text-center">
                                    <i class="fa-solid fa-box-open text-5xl text-slate-100 mb-4 block"></i>
                                    <div class="text-slate-400 font-bold max-w-xs mx-auto">Your lab results will appear here once your blood tests have been processed and uploaded by the clinic.</div>
                                </div>
                            <?php
else: ?>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left min-w-[700px]">
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
                                                        <a href="https://ivfexperts.pk/<?php echo htmlspecialchars($lr['scanned_report_path']); ?>" target="_blank" class="text-[10px] font-black uppercase text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100 hover:bg-indigo-600 hover:text-white transition-all">
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
        return $i;
    }, $ultrasounds),
    array_map(function ($i) {
        $i['type_label'] = 'Semen Analysis';
        $i['icon'] = 'fa-solid fa-microscope';
        $i['report_title'] = 'Semen Analysis Report';
        return $i;
    }, $semen)
);
usort($all_scans, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>

                            <?php if (empty($all_scans)): ?>
                                <div class="md:col-span-2 bg-white rounded-3xl border border-slate-200 p-12 text-center text-slate-400 font-bold max-w-xs mx-auto">
                                    Ultrasound reports and semen analysis results will appear here after your diagnostic visit.
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
                                <div class="md:col-span-2 bg-white rounded-3xl border border-slate-200 p-12 text-center text-slate-400 font-bold max-w-xs mx-auto">No prescriptions found. Digital medication plans will appear here after your visit.</div>
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
                                        <p class="text-xs text-slate-500 line-clamp-2 mb-4"><?php
        $rx_preview = strip_tags($rx['clinical_notes'] ?? '');
        if (empty($rx_preview))
            $rx_preview = strip_tags($rx['diagnosis'] ?? '');
        $rx_preview = mb_strimwidth(trim($rx_preview), 0, 120, '...');
        echo htmlspecialchars($rx_preview ?: 'Medication plan — tap to view full details.');
?></p>
                                        <div class="flex gap-2">
                                            <a href="view.php?type=rx&hash=<?php echo $rx['qrcode_hash']; ?>" target="_blank" class="flex-1 bg-indigo-600 text-white text-[10px] font-black uppercase text-center py-2 rounded-xl hover:bg-slate-900 transition-all">View Record</a>
                                            <?php if ($rx['scanned_report_path']): ?>
                                                <a href="https://ivfexperts.pk/<?php echo htmlspecialchars($rx['scanned_report_path']); ?>" target="_blank" class="px-3 py-2 bg-slate-100 text-slate-500 rounded-xl hover:bg-slate-200 transition-all"><i class="fa-solid fa-file-pdf"></i></a>
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
                        
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-emerald-50 border border-emerald-100 rounded-3xl p-5 text-center">
                                <div class="text-2xl font-black text-emerald-700">Rs. <?php echo number_format($total_paid, 0); ?></div>
                                <div class="text-[9px] font-black text-emerald-500 uppercase tracking-widest mt-1">Total Paid</div>
                            </div>
                            <?php if ($total_pending > 0): ?>
                            <div class="bg-amber-50 border border-amber-100 rounded-3xl p-5 text-center">
                                <div class="text-2xl font-black text-amber-700">Rs. <?php echo number_format($total_pending, 0); ?></div>
                                <div class="text-[9px] font-black text-amber-500 uppercase tracking-widest mt-1">Pending Balance</div>
                            </div>
                            <?php
else: ?>
                            <div class="bg-slate-50 border border-slate-100 rounded-3xl p-5 text-center opacity-50">
                                <div class="text-2xl font-black text-slate-400">Rs. 0</div>
                                <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Pending Balance</div>
                            </div>
                            <?php
endif; ?>
                        </div>

                        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                            <?php if (empty($receipts)): ?>
                                <div class="p-12 text-center text-slate-400 font-bold">No billing history found.</div>
                            <?php
else: ?>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left min-w-[600px]">
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
