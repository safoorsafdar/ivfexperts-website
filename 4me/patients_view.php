<?php
/**
 * PATIENT 360 — FULL APP-SHELL REDESIGN
 * True tab-switching UX with dark patient panel.
 */
$pageTitle = "Patient 360";
require_once __DIR__ . '/includes/auth.php';

$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($patient_id <= 0) {
    header("Location: patients.php");
    exit;
}

$error = $success = '';

// ── Handle Add / Edit Clinical History ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_history']) || isset($_POST['edit_history'])) {
        // ── Auto-ensure patient_history columns exist (safe to run every time) ──
        $colChecks = [
            'record_for' => "ALTER TABLE patient_history ADD COLUMN record_for ENUM('Patient','Spouse') DEFAULT 'Patient'",
            'next_visit' => "ALTER TABLE patient_history ADD COLUMN next_visit DATE",
            'clinical_notes' => "ALTER TABLE patient_history ADD COLUMN clinical_notes TEXT",
            'diagnosis' => "ALTER TABLE patient_history ADD COLUMN diagnosis TEXT",
            'medication' => "ALTER TABLE patient_history ADD COLUMN medication TEXT",
            'advice' => "ALTER TABLE patient_history ADD COLUMN advice TEXT",
        ];
        foreach ($colChecks as $col => $sql) {
            $chk = $conn->query("SHOW COLUMNS FROM patient_history LIKE '$col'");
            if ($chk && $chk->num_rows === 0) {
                $conn->query($sql);
            }
        }

        $notes = trim($_POST['clinical_notes'] ?? '');
        $diagnosis = trim($_POST['diagnosis'] ?? '');
        $medication = trim($_POST['medication'] ?? '');
        $advice = trim($_POST['advice'] ?? '');
        $next_visit = !empty($_POST['next_visit']) ? $_POST['next_visit'] : null;
        $record_for = in_array($_POST['record_for'] ?? '', ['Patient', 'Spouse']) ? $_POST['record_for'] : 'Patient';

        if (isset($_POST['add_history'])) {
            $stmt = $conn->prepare("INSERT INTO patient_history (patient_id, clinical_notes, diagnosis, medication, advice, next_visit, record_for) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                $error = "DB prepare() failed: " . $conn->error;
                error_log("[patients_view INSERT history] " . $conn->error);
            }
            else {
                $stmt->bind_param('issssss', $patient_id, $notes, $diagnosis, $medication, $advice, $next_visit, $record_for);
            }
        }
        else {
            $history_id = intval($_POST['history_id'] ?? 0);
            $stmt = $conn->prepare("UPDATE patient_history SET clinical_notes=?, diagnosis=?, medication=?, advice=?, next_visit=?, record_for=? WHERE id=? AND patient_id=?");
            if (!$stmt) {
                $error = "DB prepare() failed: " . $conn->error;
                error_log("[patients_view UPDATE history] " . $conn->error);
            }
            else {
                $stmt->bind_param('ssssssii', $notes, $diagnosis, $medication, $advice, $next_visit, $record_for, $history_id, $patient_id);
            }
        }

        if (empty($error) && isset($stmt) && $stmt) {
            if ($stmt->execute()) {
                $action = isset($_POST['add_history']) ? 'added' : 'updated';
                header("Location: patients_view.php?id={$patient_id}&tab=history&msg={$action}");
                exit;
            }
            else {
                $error = "Save failed: " . $stmt->error;
                error_log("[patients_view history execute] " . $stmt->error);
            }
        }
    }


    if (isset($_POST['delete_history'])) {
        $id = intval($_POST['record_id']);
        $conn->query("DELETE FROM patient_history WHERE id = $id AND patient_id = $patient_id");
        header("Location: patients_view.php?id={$patient_id}&tab=history&msg=deleted");
        exit;
    }
    if (isset($_POST['delete_rx'])) {
        $id = intval($_POST['record_id']);
        $conn->query("DELETE FROM prescription_medications WHERE prescription_id = $id");
        $conn->query("DELETE FROM prescription_lab_tests WHERE prescription_id = $id");
        $conn->query("DELETE FROM prescriptions WHERE id = $id AND patient_id = $patient_id");
        header("Location: patients_view.php?id={$patient_id}&tab=rx&msg=deleted");
        exit;
    }
    if (isset($_POST['delete_semen'])) {
        $id = intval($_POST['record_id']);
        $conn->query("DELETE FROM semen_analyses WHERE id = $id AND patient_id = $patient_id");
        header("Location: patients_view.php?id={$patient_id}&tab=semen&msg=deleted");
        exit;
    }
    if (isset($_POST['delete_usg'])) {
        $id = intval($_POST['record_id']);
        $conn->query("DELETE FROM patient_ultrasounds WHERE id = $id AND patient_id = $patient_id");
        header("Location: patients_view.php?id={$patient_id}&tab=usg&msg=deleted");
        exit;
    }
    if (isset($_POST['delete_lab'])) {
        $id = intval($_POST['record_id']);
        $conn->query("DELETE FROM patient_lab_results WHERE id = $id AND patient_id = $patient_id");
        header("Location: patients_view.php?id={$patient_id}&tab=labs&msg=deleted");
        exit;
    }
    if (isset($_POST['delete_procedure'])) {
        $id = intval($_POST['record_id']);
        $conn->query("DELETE FROM advised_procedures WHERE id = $id AND patient_id = $patient_id");
        header("Location: patients_view.php?id={$patient_id}&tab=procedures&msg=deleted");
        exit;
    }
}

// ── Data Fetching ──────────────────────────────────────────────────────────────
try {
    $stmt = $conn->prepare("SELECT p.*, h.name AS hospital_name FROM patients p LEFT JOIN hospitals h ON p.referring_hospital_id = h.id WHERE p.id = ?");
    $stmt->bind_param('i', $patient_id);
    $stmt->execute();
    $patient = $stmt->get_result()->fetch_assoc();
    if (!$patient)
        die("Patient not found.");

    $pid = intval($patient_id);
    $histories = $conn->query("SELECT * FROM patient_history WHERE patient_id = $pid ORDER BY COALESCE(created_at, id) DESC")->fetch_all(MYSQLI_ASSOC);
    $semen_reports = $conn->query("SELECT * FROM semen_analyses WHERE patient_id = $pid ORDER BY collection_time DESC")->fetch_all(MYSQLI_ASSOC);
    $prescriptions_raw = $conn->query("SELECT * FROM prescriptions WHERE patient_id = $pid ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
    // Enrich each prescription with medication items + lab count
    $prescriptions = [];
    foreach ($prescriptions_raw as $prx) {
        $rid = intval($prx['id']);
        $prx['_items'] = $conn->query("SELECT medicine_name, dosage, frequency, duration FROM prescription_items WHERE prescription_id = $rid ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
        $prx['_lab_count'] = (int)($conn->query("SELECT COUNT(*) AS c FROM advised_lab_tests WHERE prescription_id = $rid")->fetch_assoc()['c'] ?? 0);
        $prescriptions[] = $prx;
    }

    $ultrasounds = $conn->query("SELECT * FROM patient_ultrasounds WHERE patient_id = $pid ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
    $lab_results = $conn->query("SELECT plt.*, ltd.test_name, ltd.unit, ltd.reference_range_male, ltd.reference_range_female FROM patient_lab_results plt JOIN lab_tests_directory ltd ON plt.test_id = ltd.id WHERE plt.patient_id = $pid ORDER BY plt.test_date DESC")->fetch_all(MYSQLI_ASSOC);
    $advised_procedures = $conn->query("SELECT ap.*, (SELECT COALESCE(SUM(r.amount),0) FROM receipts r WHERE r.advised_procedure_id = ap.id AND r.status = 'Paid') AS total_paid FROM advised_procedures ap WHERE ap.patient_id = $pid ORDER BY ap.date_advised DESC")->fetch_all(MYSQLI_ASSOC);
}
catch (Exception $e) {
    $error = "Data fetch error: " . $e->getMessage();
}

// Initial tab (from URL or default)
$initial_tab = in_array($_GET['tab'] ?? '', ['history', 'rx', 'semen', 'usg', 'labs', 'procedures']) ? $_GET['tab'] : 'history';

// Patient avatar initials
$initials = strtoupper(substr($patient['first_name'] ?? 'P', 0, 1) . substr($patient['last_name'] ?? 'X', 0, 1));

// Tab definitions
$tabs = [
    ['id' => 'history', 'icon' => 'fa-notes-medical', 'label' => 'Clinical History', 'subtitle' => 'Visit notes & records', 'count' => count($histories), 'color_class' => 'emerald', 'btn_label' => 'Add Visit', 'btn_action' => '@click="openAddHistory()"', 'btn_href' => ''],
    ['id' => 'rx', 'icon' => 'fa-prescription', 'label' => 'Prescriptions', 'subtitle' => 'Medication & Rx vault', 'count' => count($prescriptions), 'color_class' => 'violet', 'btn_label' => 'New Prescription', 'btn_action' => '', 'btn_href' => "prescriptions_add.php?patient_id=$patient_id"],
    ['id' => 'semen', 'icon' => 'fa-flask-vial', 'label' => 'Semen Analysis', 'subtitle' => 'Andrology reports', 'count' => count($semen_reports), 'color_class' => 'cyan', 'btn_label' => 'New Analysis', 'btn_action' => '', 'btn_href' => "semen_analyses_add.php?patient_id=$patient_id"],
    ['id' => 'usg', 'icon' => 'fa-image', 'label' => 'Ultrasounds', 'subtitle' => 'Diagnostic imaging', 'count' => count($ultrasounds), 'color_class' => 'orange', 'btn_label' => 'Add Scan', 'btn_action' => '', 'btn_href' => "ultrasounds_add.php?patient_id=$patient_id"],
    ['id' => 'labs', 'icon' => 'fa-vials', 'label' => 'Lab Results', 'subtitle' => 'Test results & investigations', 'count' => count($lab_results), 'color_class' => 'amber', 'btn_label' => 'Post Result', 'btn_action' => '', 'btn_href' => "lab_results_add.php?patient_id=$patient_id"],
    ['id' => 'procedures', 'icon' => 'fa-clipboard-check', 'label' => 'Procedures', 'subtitle' => 'Treatments & billing', 'count' => count($advised_procedures), 'color_class' => 'rose', 'btn_label' => 'Log Procedure', 'btn_action' => '', 'btn_href' => "procedures_add.php?patient_id=$patient_id"],
];

include __DIR__ . '/includes/header.php';
?>

<style>
[x-cloak] { display: none !important; }
.tab-panel { animation: fadeIn .15s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style:none; scrollbar-width:none; }
</style>

<?php if ($error): ?>
<div class="mb-4 flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 px-5 py-3 rounded-xl text-sm font-bold">
    <i class="fa-solid fa-circle-exclamation text-rose-500"></i> <?php echo esc($error); ?>
</div>
<?php
endif; ?>

<?php if (!empty($_GET['msg'])): ?>
<?php $msgs = ['added' => 'Visit record added.', 'updated' => 'Visit record updated.', 'deleted' => 'Record deleted.', 'rx_saved' => 'Prescription saved.'];
    $msgText = $msgs[$_GET['msg']] ?? 'Done.';
    $isDelete = $_GET['msg'] === 'deleted';
?>
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-2"
     class="fixed bottom-6 right-6 z-[9999] flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl text-sm font-bold <?php echo $isDelete ? 'bg-red-600 text-white' : 'bg-emerald-600 text-white'; ?>">
    <i class="fa-solid <?php echo $isDelete ? 'fa-trash-can' : 'fa-circle-check'; ?> text-base"></i>
    <?php echo htmlspecialchars($msgText); ?>
    <button @click="show = false" class="ml-2 opacity-70 hover:opacity-100 transition-opacity">
        <i class="fa-solid fa-xmark text-xs"></i>
    </button>
</div>
<?php
endif; ?>

<!-- ══════════════════════════════════════════════════════════════
     MAIN APP SHELL — dark left panel + tabbed right content
═══════════════════════════════════════════════════════════════ -->
<!-- ══════════════════════════════════════════════════════════════
     MAIN APP SHELL — Bio-Dossier (Dark) + Command Center (Light)
     ═══════════════════════════════════════════════════════════════ -->
<div class="-mx-6 -my-6 flex min-h-[calc(100vh-4rem)] bg-slate-50"
     x-data="{
        tab: '<?php echo $initial_tab; ?>',
        showHistoryModal: false,
        editHistory: null,
        openAddHistory()  { this.editHistory = null; this.showHistoryModal = true; },
        openEditHistory(d){ this.editHistory = d;    this.showHistoryModal = true; }
     }">

    <!-- ─── LEFT: BIO-DOSSIER PANEL ─── -->
    <div class="w-80 shrink-0 bg-white flex flex-col overflow-y-auto scrollbar-hide border-r border-gray-100 relative z-30">
        <!-- Glossy Accent -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-brand-500/10 blur-[80px] rounded-full pointer-events-none"></div>
        
        <div class="relative z-10 px-6 pt-10 pb-8 flex flex-col items-center text-center">
            <!-- Avatar Ring -->
            <div class="relative group cursor-default">
                <div class="absolute inset-0 bg-brand-500/20 rounded-[2.5rem] blur-xl group-hover:bg-brand-500/40 transition-all duration-700"></div>
                <div class="relative w-24 h-24 rounded-[2.5rem] bg-teal-50 border border-teal-100 flex items-center justify-center shadow-sm">
                    <span class="text-4xl font-black bg-gradient-to-tr from-brand-300 to-brand-600 bg-clip-text text-transparent">
                        <?php echo $initials; ?>
                    </span>
                </div>
                <!-- Gender Icon Badge -->
                <div class="absolute -bottom-1 -right-1 w-8 h-8 rounded-xl border-4 border-white flex items-center justify-center text-white shadow-lg <?php echo($patient['gender'] === 'Female') ? 'bg-rose-500' : 'bg-indigo-500'; ?>">
                    <i class="fa-solid <?php echo($patient['gender'] === 'Female') ? 'fa-venus' : 'fa-mars'; ?> text-xs"></i>
                </div>
            </div>

            <h2 class="text-slate-800 text-xl font-semibold mt-6 leading-tight tracking-tight"><?php echo esc($patient['first_name'] . ' ' . $patient['last_name']); ?></h2>
            <div class="inline-flex items-center gap-2 mt-2 px-3 py-1 bg-teal-50 rounded-full border border-teal-100">
                <span class="text-[10px] font-mono text-teal-600 font-semibold tracking-widest"><?php echo esc($patient['mr_number']); ?></span>
            </div>
            
            <div class="flex items-center gap-3 mt-6">
                <div class="flex flex-col items-center">
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-tighter">Age</span>
                    <span class="text-sm font-semibold text-slate-800"><?php echo esc($patient['patient_age'] ?: '—'); ?>y</span>
                </div>
                <div class="w-px h-6 bg-gray-200"></div>
                <div class="flex flex-col items-center">
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-tighter">Blood</span>
                    <span class="text-sm font-semibold text-teal-600"><?php echo esc($patient['blood_group'] ?: 'N/A'); ?></span>
                </div>
                <div class="w-px h-6 bg-gray-200"></div>
                <div class="flex flex-col items-center">
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-tighter">Status</span>
                    <span class="text-sm font-semibold text-slate-800"><?php echo esc($patient['marital_status'] ? substr($patient['marital_status'], 0, 1) : '—'); ?></span>
                </div>
            </div>
        </div>

        <!-- Bio-Metrics Section -->
        <div class="px-4 space-y-4 mb-8">
            <!-- Obstetric Pulse (Female only) -->
            <?php if ($patient['gender'] === 'Female' && ($patient['gravida'] || $patient['para'])): ?>
            <div class="bg-rose-50 border border-rose-100 rounded-2xl p-4">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-[10px] font-semibold text-rose-500 uppercase tracking-widest">Obstetrics</span>
                    <i class="fa-solid fa-heart-pulse text-rose-500/50 text-[10px]"></i>
                </div>
                <div class="grid grid-cols-3 gap-1">
                    <div class="text-center">
                        <div class="text-lg font-semibold text-rose-700"><?php echo $patient['gravida'] ?? 0; ?></div>
                        <div class="text-[9px] text-rose-400 font-semibold uppercase">G</div>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-semibold text-rose-700"><?php echo $patient['para'] ?? 0; ?></div>
                        <div class="text-[9px] text-rose-400 font-semibold uppercase">P</div>
                    </div>
                    <div class="text-center border-l border-rose-200">
                        <div class="text-lg font-semibold text-rose-700"><?php echo $patient['abortions'] ?? 0; ?></div>
                        <div class="text-[9px] text-rose-400 font-semibold uppercase">A</div>
                    </div>
                </div>
            </div>
            <?php
endif; ?>

            <!-- Spouse Intelligence -->
            <?php if (!empty($patient['spouse_name'])): ?>
            <div class="bg-brand-600/10 border border-brand-500/20 rounded-3xl p-5 group hover:bg-brand-600/20 transition-all cursor-pointer">
                <div class="text-[10px] font-black text-brand-400 uppercase tracking-[0.2em] mb-3 flex items-center justify-between">
                    <span>Partner Dossier</span>
                    <i class="fa-solid fa-link text-[10px] opacity-20"></i>
                </div>
                <h4 class="text-teal-800 text-sm font-semibold"><?php echo esc($patient['spouse_name']); ?></h4>
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-[10px] font-medium text-teal-600"><?php echo esc($patient['spouse_age'] ?: '—'); ?> years</span>
                    <span class="w-1 h-1 rounded-full bg-teal-300"></span>
                    <span class="text-[10px] font-medium text-teal-600"><?php echo esc($patient['spouse_phone'] ?: 'No Phone'); ?></span>
                </div>
            </div>
            <?php
endif; ?>

            <!-- Contact/Referral Chips -->
            <div class="grid grid-cols-2 gap-2">
                <a href="tel:<?php echo esc($patient['phone']); ?>" class="flex flex-col items-center justify-center p-3 bg-teal-50 rounded-xl border border-teal-100 hover:bg-teal-100 hover:border-teal-200 transition-all">
                    <i class="fa-solid fa-phone text-brand-400 text-xs mb-1.5"></i>
                    <span class="text-[9px] font-semibold text-teal-700 uppercase">Call</span>
                </a>
                <a href="patients_edit.php?id=<?php echo $patient_id; ?>" class="flex flex-col items-center justify-center p-3 bg-gray-50 rounded-xl border border-gray-100 hover:bg-gray-100 hover:border-gray-200 transition-all text-slate-500 hover:text-slate-700">
                    <i class="fa-solid fa-user-pen text-xs mb-1.5 transition-transform group-hover:scale-110"></i>
                    <span class="text-[9px] font-semibold text-slate-500 uppercase">Edit</span>
                </a>
            </div>
        </div>

        <!-- Record Categories (Tabs) -->
        <div class="flex-1 px-3 space-y-1">
            <div class="px-4 mb-4 flex items-center justify-between">
                <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-widest">Medical Log</span>
                <span class="text-[9px] font-semibold text-teal-500 uppercase tracking-widest">Clinical Data</span>
            </div>
            
            <?php foreach ($tabs as $t):
    $tc = $tab_colors[$t['color_class']]; ?>
            <button @click="tab = '<?php echo $t['id']; ?>'"
                    class="w-full text-left px-4 py-3 rounded-2xl flex items-center gap-3 transition-all group overflow-hidden relative"
                    :class="tab === '<?php echo $t['id']; ?>' ? 'bg-teal-50' : 'hover:bg-gray-50'">
                <!-- Glow Indicator -->
                <div x-show="tab === '<?php echo $t['id']; ?>'" x-cloak
                     class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-brand-500 rounded-r-full shadow-[0_0_15px_rgba(20,184,166,0.6)]"></div>
                
                <i class="fa-solid <?php echo $t['icon']; ?> text-sm w-5 text-center transition-all duration-300"
                   :class="tab === '<?php echo $t['id']; ?>' ? 'text-teal-600 scale-110' : 'text-slate-400 group-hover:text-slate-600'"></i>
                
                <span class="flex-1 text-[13px] font-black tracking-tight transition-colors"
                      :class="tab === '<?php echo $t['id']; ?>' ? 'text-teal-700' : 'text-slate-500 group-hover:text-slate-700'"><?php echo $t['label']; ?></span>
                
                <span class="text-[10px] font-black px-2 py-0.5 rounded-lg border transition-all"
                      :class="tab === '<?php echo $t['id']; ?>' ? 'bg-brand-500/20 border-brand-500/30 text-brand-300' : 'bg-gray-100 border-gray-200 text-slate-500 group-hover:text-slate-600'"><?php echo $t['count']; ?></span>
            </button>
            <?php
endforeach; ?>
        </div>

        <!-- Global Action -->
        <div class="p-6">
            <a href="patients.php" class="flex items-center justify-center gap-2 py-3 rounded-xl border border-gray-100 text-slate-400 hover:text-teal-700 hover:bg-teal-50 transition-all text-[11px] font-medium uppercase tracking-widest group">
                <i class="fa-solid fa-arrow-left text-[9px] group-hover:-translate-x-1 transition-transform"></i> Exit Dossier
            </a>
        </div>
    </div><!-- end bio-dossier panel -->

    <!-- ─── RIGHT: COMMAND CENTER CONTENT ─── -->
    <div class="flex-1 flex flex-col bg-slate-50 min-w-0 overflow-y-auto">

        <!-- Sticky COMMAND BAR -->
        <div class="sticky top-0 z-40 bg-white/80 backdrop-blur-xl border-b border-gray-100/80">
            <?php foreach ($tabs as $t):
    $icon_bg_map = ['emerald' => 'bg-emerald-50 text-emerald-600', 'violet' => 'bg-violet-50 text-violet-600', 'cyan' => 'bg-cyan-50 text-cyan-600', 'orange' => 'bg-orange-50 text-orange-600', 'amber' => 'bg-amber-50 text-amber-600', 'rose' => 'bg-rose-50 text-rose-600'];
    $btn_brand = ['emerald' => 'brand', 'violet' => 'brand', 'cyan' => 'brand', 'orange' => 'brand', 'amber' => 'brand', 'rose' => 'brand']; // Using brand for consistency
?>
            <div x-show="tab === '<?php echo $t['id']; ?>'" x-cloak
                 class="flex items-center justify-between px-10 py-5 tab-panel">
                <div class="flex items-center gap-5">
                    <div class="w-14 h-14 <?php echo $icon_bg_map[$t['color_class']]; ?> rounded-2xl flex items-center justify-center text-2xl shadow-inner border border-white/40">
                        <i class="fa-solid <?php echo $t['icon']; ?>"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1"><?php echo $t['id']; ?> control</div>
                        <h2 class="text-2xl font-black text-gray-900 leading-none tracking-tight"><?php echo $t['label']; ?></h2>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Protocol Indicator badge would go here if active -->
                    <?php if ($t['btn_action']): ?>
                    <button <?php echo $t['btn_action']; ?>
                            class="inline-flex items-center gap-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs px-6 py-3.5 rounded-2xl shadow-sm transition-all active:scale-95 group">
                        <i class="fa-solid fa-plus text-[10px] group-hover:rotate-90 transition-transform"></i> <?php echo $t['btn_label']; ?>
                    </button>
                    <?php
    else: ?>
                    <a href="<?php echo $t['btn_href']; ?>"
                       class="inline-flex items-center gap-2.5 bg-brand-600 hover:bg-brand-700 text-white font-black text-xs px-6 py-3.5 rounded-2xl shadow-xl shadow-brand-100 transition-all active:scale-95 group">
                        <i class="fa-solid fa-plus text-[10px] group-hover:rotate-90 transition-transform"></i> <?php echo $t['btn_label']; ?>
                    </a>
                    <?php
    endif; ?>
                </div>
            </div>
            <?php
endforeach; ?>
        </div>

        <!-- ══════════════ TAB PANELS ══════════════ -->
        <div class="p-8 md:p-10 flex-1">

            <!-- ─── PROTOCOL COMMAND CENTER (Dynamic) ─── -->
            <?php
$active_protocol = null;
foreach ($advised_procedures as $ap) {
    if ($ap['status'] === 'In Progress' && (stripos($ap['procedure_name'], 'IVF') !== false || stripos($ap['procedure_name'], 'IUI') !== false || stripos($ap['procedure_name'], 'ICSI') !== false || stripos($ap['procedure_name'], 'FET') !== false)) {
        $active_protocol = $ap;
        break;
    }
}
if ($active_protocol):
    $p_name = $active_protocol['procedure_name'];
    $is_ivf = stripos($p_name, 'IVF') !== false || stripos($p_name, 'ICSI') !== false;
    $is_iui = stripos($p_name, 'IUI') !== false;
?>
            <div class="mb-10 bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group">
                <!-- Decorative background elements -->
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-brand-500/20 rounded-full blur-[100px] group-hover:bg-brand-500/30 transition-all duration-1000"></div>
                <div class="absolute left-10 bottom-0 w-40 h-1 bg-gradient-to-r from-transparent via-brand-500/50 to-transparent"></div>
                
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                    <div class="flex items-center gap-6">
                        <div class="w-20 h-20 bg-brand-500/10 border border-brand-500/20 rounded-3xl flex items-center justify-center shadow-inner">
                            <i class="fa-solid fa-microscope text-brand-400 text-3xl animate-pulse"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2.5 py-0.5 bg-brand-500 text-white text-[10px] font-black uppercase tracking-widest rounded-lg transition-transform group-hover:scale-105">Active Protocol</span>
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Day <?php echo floor((time() - strtotime($active_protocol['date_advised'])) / 86400) + 1; ?> of Cycle</span>
                            </div>
                            <h3 class="text-3xl font-black text-white tracking-tight"><?php echo esc($p_name); ?></h3>
                            <p class="text-slate-400 text-sm mt-1 max-w-md"><?php echo esc($active_protocol['notes'] ?: 'Treatment plan in progress. Monitoring clinical indicators.'); ?></p>
                        </div>
                    </div>
                    
                    <!-- Protocol-Specific Micro-Stats -->
                    <div class="flex flex-wrap gap-4">
                        <div class="px-6 py-4 bg-white/5 border border-white/5 rounded-3xl text-center min-w-[120px] hover:bg-white/10 transition-colors">
                            <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Last Scan</div>
                            <div class="text-lg font-black text-white"><?php echo !empty($ultrasounds) ? date('d M', strtotime($ultrasounds[0]['created_at'])) : '—'; ?></div>
                        </div>
                        <?php if ($is_ivf): ?>
                        <div class="px-6 py-4 bg-white/5 border border-white/5 rounded-3xl text-center min-w-[120px] hover:bg-white/10 transition-colors">
                            <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">HCG Trigger</div>
                            <div class="text-lg font-black text-brand-400">PENDING</div>
                        </div>
                        <?php
    endif; ?>
                        <a href="procedures_add.php?edit=<?php echo $active_protocol['id']; ?>" class="flex flex-col items-center justify-center px-6 py-4 bg-brand-500 hover:bg-brand-600 text-white rounded-3xl shadow-xl shadow-brand-500/20 transition-all hover:-translate-y-1 active:scale-95">
                            <i class="fa-solid fa-gear text-sm mb-1 text-white/50 group-hover:rotate-180 transition-transform duration-700"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest">Protocol Mgmt</span>
                        </a>
                    </div>
                </div>
            </div>
            <?php
endif; ?>

            <!-- ─── TAB: Clinical History ─── -->
            <div x-show="tab === 'history'" x-cloak class="tab-panel">
                <?php if (empty($histories)): ?>
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-24 h-24 bg-brand-50 rounded-[2.5rem] flex items-center justify-center mb-6 shadow-inner border border-brand-100">
                        <i class="fa-solid fa-notes-medical text-4xl text-brand-200"></i>
                    </div>
                    <h3 class="font-black text-gray-900 text-xl mb-2">No Clinical Narrative</h3>
                    <p class="text-gray-400 text-sm mb-8 max-w-sm">Every consultation and clinical visit note will be recorded here in a chronological timeline.</p>
                    <button @click="openAddHistory()" class="bg-brand-600 hover:bg-brand-700 text-white px-8 py-3.5 rounded-2xl font-black text-xs transition-all shadow-xl shadow-brand-100 active:scale-95 uppercase tracking-widest">
                        Initiate First Visit
                    </button>
                </div>
                <?php
else: ?>
                <div class="space-y-8 relative before:absolute before:left-5 before:top-0 before:bottom-0 before:w-1 before:bg-gradient-to-b before:from-brand-500/20 before:via-brand-500/5 before:to-transparent">
                    <?php foreach ($histories as $idx => $h): ?>
                    <div class="relative pl-16 group">
                        <!-- Timeline Pin -->
                        <div class="absolute left-1.5 top-6 w-8 h-8 bg-white border-2 border-brand-500 rounded-xl flex items-center justify-center z-10 shadow-lg group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-stethoscope text-brand-600 text-[10px]"></i>
                        </div>
                        <div class="bg-white rounded-[2rem] border border-gray-100 hover:border-brand-200 hover:shadow-2xl transition-all duration-500 overflow-hidden">
                            <!-- Entry Logic Header -->
                            <div class="flex flex-wrap items-center justify-between gap-4 px-8 py-5 border-b border-gray-50 bg-gray-50/30">
                                <div class="flex items-center gap-3">
                                    <div class="text-[10px] font-black px-3 py-1 bg-teal-50 text-teal-700 rounded-lg tracking-widest uppercase border border-teal-100">Visit #<?php echo count($histories) - $idx; ?></div>
                                    <span class="text-[10px] font-black px-3 py-1 rounded-lg uppercase tracking-widest border <?php echo $h['record_for'] === 'Spouse' ? 'bg-rose-50 border-rose-100 text-rose-600 shadow-sm shadow-rose-100/50' : 'bg-brand-50 border-brand-100 text-brand-600'; ?>">
                                        <i class="fa-solid <?php echo $h['record_for'] === 'Spouse' ? 'fa-heart' : 'fa-user-nurse'; ?> mr-1"></i>
                                        <?php echo esc($h['record_for']); ?>
                                    </span>
                                    <div class="flex items-center gap-1.5 text-xs font-bold text-gray-400">
                                        <i class="fa-regular fa-clock text-brand-400"></i>
                                        <?php echo(!empty($h['recorded_at']) && $h['recorded_at'] !== '0000-00-00 00:00:00') ? date('j M Y', strtotime($h['recorded_at'])) : (!empty($h['created_at']) ? date('j M Y', strtotime($h['created_at'])) : '—'); ?>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="openEditHistory(<?php echo htmlspecialchars(json_encode([
            'history_id' => $h['id'],
            'clinical_notes' => $h['clinical_notes'] ?? '',
            'diagnosis' => $h['diagnosis'] ?? '',
            'medication' => $h['medication'] ?? '',
            'advice' => $h['advice'] ?? '',
            'next_visit' => $h['next_visit'] ?? '',
            'record_for' => $h['record_for'] ?? 'Patient'
        ])); ?>)"
                                            class="w-10 h-10 rounded-xl bg-white border border-gray-100 hover:border-brand-500 hover:bg-brand-500 hover:text-white text-gray-400 flex items-center justify-center transition-all shadow-sm" title="Modify Entry">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Purge this clinical record? Data cannot be recovered.')">
                                        <input type="hidden" name="record_id" value="<?php echo $h['id']; ?>">
                                        <button type="submit" name="delete_history" value="1"
                                                class="w-10 h-10 rounded-xl bg-white border border-gray-100 hover:border-rose-500 hover:bg-rose-500 hover:text-white text-gray-400 flex items-center justify-center transition-all shadow-sm" title="Delete">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <!-- Clinical Content -->
                            <div class="p-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
                                <div class="lg:col-span-2 space-y-6">
                                    <?php if (!empty($h['clinical_notes'])): ?>
                                    <div>
                                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 flex items-center gap-2"><div class="w-1.5 h-1.5 bg-brand-500 rounded-full"></div> S.O.A.P / Clinical Notes</div>
                                        <div class="text-[15px] text-gray-800 leading-relaxed font-medium bg-gray-50/50 p-5 rounded-2xl border border-gray-50"><?php echo nl2br(esc(strip_tags($h['clinical_notes']))); ?></div>
                                    </div>
                                    <?php
        endif; ?>
                                    
                                    <?php if (!empty($h['diagnosis'])): ?>
                                    <div class="bg-brand-950 rounded-2xl p-6 shadow-xl border border-white/5 relative group/diag overflow-hidden">
                                        <div class="absolute -right-4 -top-4 w-20 h-20 bg-brand-500/10 rounded-full blur-2xl group-hover/diag:bg-brand-500/20 transition-all"></div>
                                        <div class="text-[10px] font-black text-brand-500 uppercase tracking-[0.2em] mb-2 flex items-center gap-1.5">
                                            <i class="fa-solid fa-dna text-[9px]"></i> Clinical Impression
                                        </div>
                                        <div class="text-lg font-black text-white"><?php echo nl2br(esc($h['diagnosis'])); ?></div>
                                    </div>
                                    <?php
        endif; ?>
                                </div>
                                
                                <div class="space-y-4">
                                    <?php if (!empty($h['medication'])): ?>
                                    <div class="bg-rose-50/50 rounded-2xl p-5 border border-rose-100/50 relative">
                                        <div class="text-[10px] font-black text-rose-600 uppercase tracking-widest mb-3 flex items-center gap-2">
                                            <i class="fa-solid fa-pills text-xs"></i> Med Schedule
                                        </div>
                                        <div class="text-xs text-rose-900 font-bold leading-relaxed"><?php echo nl2br(esc($h['medication'])); ?></div>
                                    </div>
                                    <?php
        endif; ?>
                                    
                                    <?php if (!empty($h['advice'])): ?>
                                    <div class="bg-sky-50/50 rounded-2xl p-5 border border-sky-100/50">
                                        <div class="text-[10px] font-black text-sky-600 uppercase tracking-widest mb-2">Advice & Lifestyle</div>
                                        <div class="text-xs text-gray-600 font-medium leading-relaxed"><?php echo nl2br(esc($h['advice'])); ?></div>
                                    </div>
                                    <?php
        endif; ?>
                                    
                                    <?php if (!empty($h['next_visit'])): ?>
                                    <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 flex items-center justify-between group/follow">
                                        <div>
                                            <div class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Follow-up</div>
                                            <div class="text-white font-black text-sm"><?php echo($h['next_visit'] && $h['next_visit'] !== '0000-00-00') ? date('d M, Y', strtotime($h['next_visit'])) : '—'; ?></div>
                                        </div>
                                        <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-brand-400 group-hover/follow:bg-brand-500 group-hover/follow:text-white transition-all">
                                            <i class="fa-solid fa-calendar-check opacity-50"></i>
                                        </div>
                                    </div>
                                    <?php
        endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
    endforeach; ?>
                </div>
                <?php
endif; ?>
            </div>

            <!-- ─── TAB: Prescriptions ─── -->
            <div x-show="tab === 'rx'" x-cloak class="tab-panel">
                <?php if (empty($prescriptions)): ?>
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-24 h-24 bg-brand-50 rounded-[2.5rem] flex items-center justify-center mb-6 shadow-inner border border-brand-100">
                        <i class="fa-solid fa-prescription-bottle-medical text-4xl text-brand-200"></i>
                    </div>
                    <h3 class="font-black text-gray-900 text-xl mb-2">No Digital Prescriptions</h3>
                    <p class="text-gray-400 text-sm mb-8 max-w-sm">Every prescribed medication and treatment plan will be managed here.</p>
                    <a href="prescriptions_add.php?patient_id=<?php echo $patient_id; ?>" class="bg-brand-600 hover:bg-brand-700 text-white px-8 py-3.5 rounded-2xl font-black text-xs transition-all shadow-xl shadow-brand-100 active:scale-95 uppercase tracking-widest">
                        Draft First Script
                    </a>
                </div>
                <?php
else: ?>
                <div class="grid grid-cols-1 gap-5">
                    <?php foreach ($prescriptions as $rx): ?>
                    <div class="bg-white rounded-2xl border border-gray-100 hover:border-teal-200 hover:shadow-lg transition-all duration-300 overflow-hidden">
                        <!-- Card Header -->
                        <div class="px-6 py-4 flex items-start justify-between gap-4 border-b border-gray-50">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 bg-violet-50 text-violet-600 rounded-xl flex items-center justify-center text-sm shrink-0">
                                    <i class="fa-solid fa-file-prescription"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs font-mono font-semibold text-slate-400">RX-<?php echo str_pad($rx['id'], 5, '0', STR_PAD_LEFT); ?></span>
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full <?php echo ($rx['record_for'] ?? '') === 'Spouse' ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-teal-50 text-teal-700 border border-teal-100'; ?>">
                                            <?php echo esc($rx['record_for'] ?? 'Patient'); ?>
                                        </span>
                                        <?php if (!empty($rx['next_visit']) && $rx['next_visit'] !== '0000-00-00'): ?>
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-100">
                                            <i class="fa-regular fa-calendar-clock mr-1"></i>Follow-up: <?php echo date('d M Y', strtotime($rx['next_visit'])); ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                        <?php echo ($rx['created_at'] && $rx['created_at'] !== '0000-00-00 00:00:00') ? date('j M Y, g:ia', strtotime($rx['created_at'])) : '—'; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="prescriptions_edit.php?id=<?php echo $rx['id']; ?>"
                                   class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 text-gray-400 hover:bg-teal-50 hover:text-teal-600 transition-all text-xs" title="Edit Prescription">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form method="POST" class="inline" onsubmit="return confirm('Delete this prescription?')">
                                    <input type="hidden" name="record_id" value="<?php echo $rx['id']; ?>">
                                    <button type="submit" name="delete_rx" value="1"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 text-gray-300 hover:text-rose-500 hover:bg-rose-50 transition-all text-xs" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                                <a href="prescriptions_print.php?id=<?php echo $rx['id']; ?>" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 text-white rounded-xl text-[11px] font-semibold hover:bg-slate-900 transition-all shadow-sm active:scale-95">
                                    <i class="fa-solid fa-print text-[10px]"></i> Print / PDF
                                </a>
                            </div>
                        </div>

                        <!-- Diagnosis Summary -->
                        <?php if (!empty($rx['diagnosis']) || !empty($rx['clinical_notes'])): ?>
                        <div class="px-6 py-3 bg-indigo-50/40 border-b border-indigo-50">
                            <p class="text-[11px] font-semibold text-indigo-500 uppercase tracking-wider mb-1">Diagnosis</p>
                            <p class="text-sm text-slate-700 font-medium leading-snug line-clamp-2">
                                <?php echo esc(!empty($rx['diagnosis']) ? $rx['diagnosis'] : $rx['clinical_notes']); ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Medications -->
                        <?php if (!empty($rx['_items'])): ?>
                        <div class="px-6 py-3 border-b border-gray-50">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">
                                <i class="fa-solid fa-pills text-violet-400 mr-1"></i>
                                <?php echo count($rx['_items']); ?> Medication<?php echo count($rx['_items']) !== 1 ? 's' : ''; ?>
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($rx['_items'] as $item): ?>
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-violet-50 border border-violet-100 rounded-lg">
                                    <span class="text-xs font-semibold text-violet-800"><?php echo esc($item['medicine_name']); ?></span>
                                    <?php if (!empty($item['dosage'])): ?>
                                    <span class="text-[10px] text-violet-400">· <?php echo esc($item['dosage']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($item['frequency'])): ?>
                                    <span class="text-[10px] text-violet-400"><?php echo esc($item['frequency']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($item['duration'])): ?>
                                    <span class="text-[10px] text-violet-300">for <?php echo esc($item['duration']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Footer: Labs + Advice -->
                        <div class="px-6 py-3 flex items-center gap-4 flex-wrap">
                            <?php if ($rx['_lab_count'] > 0): ?>
                            <span class="inline-flex items-center gap-1.5 text-xs text-amber-700 bg-amber-50 border border-amber-100 px-3 py-1 rounded-lg font-semibold">
                                <i class="fa-solid fa-vials text-amber-500 text-[10px]"></i>
                                <?php echo $rx['_lab_count']; ?> Lab Test<?php echo $rx['_lab_count'] !== 1 ? 's' : ''; ?> Advised
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($rx['general_advice'])): ?>
                            <span class="inline-flex items-center gap-1.5 text-xs text-sky-700 bg-sky-50 border border-sky-100 px-3 py-1 rounded-lg font-semibold">
                                <i class="fa-solid fa-lightbulb text-sky-400 text-[10px]"></i>
                                Advice Included
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>



            <!-- ─── TAB: Semen Analysis ─── -->
            <div x-show="tab === 'semen'" x-cloak class="tab-panel">
                <?php if (empty($semen_reports)): ?>
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-24 h-24 bg-cyan-50 rounded-[2.5rem] flex items-center justify-center mb-6 shadow-inner border border-cyan-100">
                        <i class="fa-solid fa-flask-vial text-4xl text-cyan-200"></i>
                    </div>
                    <h3 class="font-black text-gray-900 text-xl mb-2">No Spermatogenesis Data</h3>
                    <p class="text-gray-400 text-sm mb-8 max-w-sm">Detailed laboratory analysis of semen parameters will be aggregated here.</p>
                    <a href="semen_analyses_add.php?patient_id=<?php echo $patient_id; ?>" class="bg-cyan-600 hover:bg-cyan-700 text-white px-8 py-3.5 rounded-2xl font-black text-xs transition-all shadow-xl shadow-cyan-100 active:scale-95 uppercase tracking-widest">
                        New Analysis
                    </a>
                </div>
                <?php
else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php foreach ($semen_reports as $sr):
        $conc_ok = !$sr['concentration'] || $sr['concentration'] >= 16;
        $mot_ok = !$sr['pr_motility'] || ($sr['pr_motility'] + $sr['np_motility']) >= 42;
        $morph_ok = !$sr['normal_morphology'] || $sr['normal_morphology'] >= 4;
        $status = ($conc_ok && $mot_ok && $morph_ok) ? 'normal' : 'abnormal';
?>
                    <div class="bg-white rounded-[2rem] border border-gray-100 hover:border-cyan-200 hover:shadow-2xl transition-all duration-500 overflow-hidden group">
                        <div class="bg-white p-6 md:p-8 relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-cyan-600/10 to-transparent"></div>
                            <div class="relative z-10 flex justify-between items-start mb-6">
                                <div class="w-12 h-12 bg-white/5 border border-white/10 rounded-2xl flex items-center justify-center">
                                    <i class="fa-solid fa-vial text-cyan-400 text-xl"></i>
                                </div>
                                <div class="px-3 py-1 bg-white/5 rounded-lg border border-white/5 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                    <?php echo($sr['collection_time'] && $sr['collection_time'] !== '0000-00-00 00:00:00') ? date('M Y', strtotime($sr['collection_time'])) : '—'; ?>
                                </div>
                            </div>
                            <div class="relative z-10">
                                <div class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-1">Clinical Assessment</div>
                                <div class="text-xl font-black <?php echo $status === 'normal' ? 'text-emerald-400' : 'text-rose-400'; ?>">
                                    <?php echo esc($sr['auto_diagnosis'] ?: ($status === 'normal' ? 'Normozoospermia' : 'Oligo/Asthenospermia')); ?>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 md:p-8">
                            <div class="grid grid-cols-3 gap-3 mb-8">
                                <div class="bg-gray-50 rounded-2xl p-4 text-center border border-gray-50">
                                    <div class="text-xs font-black text-slate-400 uppercase tracking-tighter mb-1">Conc</div>
                                    <div class="text-xl font-black text-slate-900 <?php echo !$conc_ok ? 'text-rose-500' : ''; ?>"><?php echo $sr['concentration'] ?: '—'; ?></div>
                                    <div class="text-[9px] text-slate-400 font-bold uppercase">M/ml</div>
                                </div>
                                <div class="bg-gray-50 rounded-2xl p-4 text-center border border-gray-50">
                                    <div class="text-xs font-black text-slate-400 uppercase tracking-tighter mb-1">Motil</div>
                                    <div class="text-xl font-black text-slate-900 <?php echo !$mot_ok ? 'text-rose-500' : ''; ?>"><?php echo($sr['pr_motility'] + $sr['np_motility']) ?: '—'; ?>%</div>
                                    <div class="text-[9px] text-slate-400 font-bold uppercase">PR+NP</div>
                                </div>
                                <div class="bg-gray-50 rounded-2xl p-4 text-center border border-gray-50">
                                    <div class="text-xs font-black text-slate-400 uppercase tracking-tighter mb-1">Morph</div>
                                    <div class="text-xl font-black text-slate-900 <?php echo !$morph_ok ? 'text-rose-500' : ''; ?>"><?php echo $sr['normal_morphology'] ?: '—'; ?>%</div>
                                    <div class="text-[9px] text-slate-400 font-bold uppercase">Normal</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-6 border-t border-gray-50">
                                <a href="semen_analyses_add.php?edit=<?php echo $sr['id']; ?>" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-teal-600 hover:text-white transition-all">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </a>
                                <div class="flex items-center gap-2">
                                     <form method="POST" class="inline" onsubmit="return confirm('Purge data?')">
                                        <input type="hidden" name="record_id" value="<?php echo $sr['id']; ?>">
                                        <button type="submit" name="delete_semen" value="1" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-300 hover:text-rose-500 transition-all">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                    <a href="semen_analyses_print.php?id=<?php echo $sr['id']; ?>" target="_blank" class="inline-flex items-center gap-2 bg-cyan-600 hover:bg-cyan-700 text-white font-black text-[10px] px-5 py-2.5 rounded-xl transition-all shadow-lg active:scale-95 uppercase tracking-widest">
                                        <i class="fa-solid fa-print"></i> Full Report
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
    endforeach; ?>
                </div>
                <?php
endif; ?>
            </div>

            <!-- ─── TAB: Ultrasounds ─── -->
            <div x-show="tab === 'usg'" x-cloak class="tab-panel">
                <?php if (empty($ultrasounds)): ?>
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-24 h-24 bg-orange-50 rounded-[2.5rem] flex items-center justify-center mb-6 shadow-inner border border-orange-100">
                        <i class="fa-solid fa-camera-retro text-4xl text-orange-200"></i>
                    </div>
                    <h3 class="font-black text-gray-900 text-xl mb-2">No Imaging History</h3>
                    <p class="text-gray-400 text-sm mb-8 max-w-sm">Every ultrasound scan and follicular tracking image will be securely stored here.</p>
                    <a href="ultrasounds_add.php?patient_id=<?php echo $patient_id; ?>" class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-3.5 rounded-2xl font-black text-xs transition-all shadow-xl shadow-orange-100 active:scale-95 uppercase tracking-widest">
                        Upload Scan
                    </a>
                </div>
                <?php
else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php foreach ($ultrasounds as $u): ?>
                    <div class="bg-white rounded-[2rem] border border-gray-100 hover:border-orange-200 hover:shadow-2xl transition-all duration-500 overflow-hidden group">
                        <!-- Immersive Preview -->
                        <div class="relative aspect-[4/3] bg-slate-900 overflow-hidden group">
                            <?php if (!empty($u['scanned_report_path'])): ?>
                            <img src="../<?php echo esc($u['scanned_report_path']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000 opacity-90 group-hover:opacity-100">
                            <?php
        else: ?>
                            <div class="w-full h-full flex flex-col items-center justify-center gap-3">
                                <i class="fa-solid fa-image text-4xl text-white/10"></i>
                                <span class="text-[9px] font-black text-white/20 uppercase tracking-widest">No Preview Available</span>
                            </div>
                            <?php
        endif; ?>
                            <!-- Glass Overlays -->
                            <div class="absolute inset-x-0 bottom-0 p-6 bg-gradient-to-t from-black/80 via-black/40 to-transparent">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2 py-0.5 bg-orange-500 text-white text-[9px] font-black uppercase tracking-widest rounded-lg">
                                        <?php echo esc($u['record_for'] ?? 'Patient'); ?>
                                    </span>
                                    <span class="text-[10px] text-white/60 font-medium"><?php echo date('j M Y', strtotime($u['created_at'])); ?></span>
                                </div>
                                <h4 class="text-white font-black text-lg truncate"><?php echo esc($u['report_title'] ?: 'Transvaginal Ultrasound'); ?></h4>
                            </div>
                        </div>
                        <div class="p-6 flex items-center justify-between bg-white border-t border-gray-50">
                            <div class="flex items-center gap-3">
                                <a href="ultrasounds_add.php?edit=<?php echo $u['id']; ?>" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 text-gray-400 hover:bg-teal-600 hover:text-white transition-all shadow-sm">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </a>
                                <form method="POST" class="inline" onsubmit="return confirm('Purge image?')">
                                    <input type="hidden" name="record_id" value="<?php echo $u['id']; ?>">
                                    <button type="submit" name="delete_usg" value="1" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 text-gray-300 hover:text-rose-500 transition-all">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                            </div>
                            <a href="ultrasounds_print.php?id=<?php echo $u['id']; ?>" target="_blank" class="inline-flex items-center gap-2 bg-teal-700 hover:bg-teal-800 text-white px-5 py-2.5 rounded-xl text-[10px] font-black transition-all shadow-lg active:scale-95 uppercase tracking-widest">
                                <i class="fa-solid fa-expand"></i> Inspect
                            </a>
                        </div>
                    </div>
                    <?php
    endforeach; ?>
                </div>
                <?php
endif; ?>
            </div>

            <!-- ─── TAB: Lab Results ─── -->
            <div x-show="tab === 'labs'" x-cloak class="tab-panel">
                <?php if (empty($lab_results)): ?>
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-24 h-24 bg-amber-50 rounded-[2.5rem] flex items-center justify-center mb-6 shadow-inner border border-amber-100">
                        <i class="fa-solid fa-vial-circle-check text-4xl text-amber-200"></i>
                    </div>
                    <h3 class="font-black text-gray-900 text-xl mb-2">No Bio-Analytical Records</h3>
                    <p class="text-gray-400 text-sm mb-8 max-w-sm">All hormone panels, infectious disease screens, and genetic tests will be visualized here.</p>
                    <a href="lab_results_add.php?patient_id=<?php echo $patient_id; ?>" class="bg-amber-500 hover:bg-amber-600 text-white px-8 py-3.5 rounded-2xl font-black text-xs transition-all shadow-xl shadow-amber-100 active:scale-95 uppercase tracking-widest">
                        Post Result
                    </a>
                </div>
                <?php
else: ?>
                <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-2xl overflow-hidden">
                    <?php $pending_count = count(array_filter($lab_results, fn($lr) => $lr['status'] === 'Pending')); ?>
                    <?php if ($pending_count > 0): ?>
                    <div class="bg-gray-50 px-8 py-4 flex items-center gap-3 border-b border-gray-100">
                        <div class="w-2 h-2 bg-amber-400 rounded-full animate-ping"></div>
                        <span class="text-[10px] font-black text-white uppercase tracking-widest"><?php echo $pending_count; ?> Critical Pending Result<?php echo $pending_count !== 1 ? 's' : ''; ?></span>
                    </div>
                    <?php
    endif; ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 text-[10px] uppercase font-black tracking-[0.1em] text-slate-400 border-b border-gray-100">
                                    <th class="px-8 py-5">Diagnostic Test</th>
                                    <th class="px-8 py-5">Sovereignty</th>
                                    <th class="px-8 py-5 text-center">Magnitude</th>
                                    <th class="px-8 py-5">Reference Gap</th>
                                    <th class="px-8 py-5">Timestamp</th>
                                    <th class="px-8 py-5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php foreach ($lab_results as $lr): ?>
                                <tr class="hover:bg-brand-50/20 transition-all duration-300 group">
                                    <td class="px-8 py-5">
                                        <div class="font-black text-slate-900 text-sm group-hover:text-brand-600 transition-colors"><?php echo esc($lr['test_name']); ?></div>
                                        <?php if (!empty($lr['lab_name'])): ?>
                                        <div class="text-[9px] text-slate-400 font-bold mt-1 uppercase tracking-tighter"><i class="fa-solid fa-flask-vial text-[8px] mr-1 opacity-50"></i><?php echo esc($lr['lab_name']); ?></div>
                                        <?php
        endif; ?>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span class="px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border <?php echo $lr['test_for'] === 'Spouse' ? 'bg-rose-50 border-rose-100 text-rose-600' : 'bg-brand-50 border-brand-100 text-brand-600'; ?>">
                                            <?php echo esc($lr['test_for'] ?? 'Patient'); ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <?php if ($lr['status'] === 'Pending'): ?>
                                        <span class="inline-flex items-center gap-2 text-amber-500 font-black text-[10px] uppercase tracking-widest bg-amber-50 px-3 py-1 rounded-lg border border-amber-100">
                                            <i class="fa-solid fa-clock-rotate-left animate-spin-slow"></i> Awaiting
                                        </span>
                                        <?php
        else: ?>
                                        <div class="text-base font-black text-slate-900 leading-none mb-1"><?php echo esc($lr['result_value']); ?></div>
                                        <div class="text-[9px] text-slate-400 uppercase font-black tracking-tighter"><?php echo esc($lr['unit']); ?></div>
                                        <?php
        endif; ?>
                                    </td>
                                    <td class="px-8 py-5">
                                        <?php
        $targetGender = ($lr['test_for'] === 'Patient') ? ($patient['gender'] ?? 'Male') : ($patient['gender'] === 'Male' ? 'Female' : 'Male');
        $ref = $targetGender === 'Male' ? $lr['reference_range_male'] : $lr['reference_range_female'];
?>
                                        <div class="text-[10px] font-mono text-slate-500 bg-slate-50 border border-slate-100 px-3 py-1.5 rounded-xl inline-block"><?php echo esc($ref ?: 'Not Defined'); ?></div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="text-xs font-bold text-slate-600 whitespace-nowrap"><?php echo($lr['test_date'] && $lr['test_date'] !== '0000-00-00') ? date('d M, Y', strtotime($lr['test_date'])) : '—'; ?></div>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="lab_results_add.php?edit=<?php echo $lr['id']; ?>" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-teal-600 hover:text-white transition-all">
                                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                            </a>
                                            <form method="POST" class="inline" onsubmit="return confirm('Purge data?')">
                                                <input type="hidden" name="record_id" value="<?php echo $lr['id']; ?>">
                                                <button type="submit" name="delete_lab" value="1" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-50 text-slate-300 hover:text-rose-500 transition-all">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </button>
                                            </form>
                                            <?php if (!empty($lr['scanned_report_path'])): ?>
                                            <a href="../<?php echo esc($lr['scanned_report_path']); ?>" target="_blank" class="w-9 h-9 flex items-center justify-center rounded-xl bg-brand-500 text-white hover:bg-black transition-all shadow-lg shadow-brand-100">
                                                <i class="fa-solid fa-file-pdf text-xs"></i>
                                            </a>
                                            <?php
        endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php
    endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
endif; ?>
            </div>

            <!-- ─── TAB: Procedures ─── -->
            <div x-show="tab === 'procedures'" x-cloak class="tab-panel pb-12">
                <?php if (empty($advised_procedures)): ?>
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-24 h-24 bg-rose-50 rounded-[2.5rem] flex items-center justify-center mb-6 shadow-inner border border-rose-100">
                        <i class="fa-solid fa-notes-medical text-4xl text-rose-200"></i>
                    </div>
                    <h3 class="font-black text-gray-900 text-xl mb-2">No Procedures Documented</h3>
                    <p class="text-gray-400 text-sm mb-8 max-w-sm">Document all surgical interventions, advised protocols, and specialized treatments here.</p>
                    <a href="procedures_add.php?patient_id=<?php echo $patient_id; ?>" class="bg-rose-600 hover:bg-rose-700 text-white px-8 py-3.5 rounded-2xl font-black text-xs transition-all shadow-xl shadow-rose-100 active:scale-95 uppercase tracking-widest">
                        Initiate Record
                    </a>
                </div>
                <?php
else: ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <?php foreach ($advised_procedures as $ap):
        $statusCfg = match ($ap['status']) {
                'Advised' => ['color' => 'amber', 'icon' => 'fa-clock'],
                'In Progress' => ['color' => 'sky', 'icon' => 'fa-spinner'],
                'Completed' => ['color' => 'emerald', 'icon' => 'fa-check-double'],
                default => ['color' => 'slate', 'icon' => 'fa-folder'],
            };
?>
                    <div class="bg-white rounded-[2.5rem] border border-gray-100 hover:border-brand-200 hover:shadow-2xl transition-all duration-500 group overflow-hidden flex flex-col">
                        <div class="flex items-center justify-between px-8 py-5 border-b border-gray-50 bg-gray-50/30">
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border bg-<?php echo $statusCfg['color']; ?>-50 border-<?php echo $statusCfg['color']; ?>-100 text-<?php echo $statusCfg['color']; ?>-700">
                                <i class="fa-solid <?php echo $statusCfg['icon']; ?> text-[9px]"></i>
                                <?php echo esc($ap['status']); ?>
                            </span>
                            <span class="text-[10px] font-black text-slate-400 bg-white border border-gray-100 px-2 py-0.5 rounded-lg"><?php echo($ap['date_advised'] && $ap['date_advised'] !== '0000-00-00') ? date('j M Y', strtotime($ap['date_advised'])) : '—'; ?></span>
                        </div>
                        <div class="p-8 flex-1">
                            <h4 class="text-xl font-black text-slate-950 mb-1"><?php echo esc($ap['procedure_name']); ?></h4>
                            <div class="text-[10px] text-brand-500 font-black uppercase tracking-[0.2em] mb-4">Patient Sovereignty: <?php echo esc($ap['record_for'] ?? 'Patient'); ?></div>
                            <?php if (!empty($ap['notes'])): ?>
                            <p class="text-xs text-slate-500 font-medium bg-slate-50 p-5 rounded-2xl border border-slate-50 leading-relaxed italic"><?php echo esc($ap['notes']); ?></p>
                            <?php
        endif; ?>
                            
                            <div class="mt-8 pt-8 border-t border-gray-50 flex items-end justify-between">
                                <div>
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Equity Position</div>
                                    <div class="text-2xl font-black <?php echo $ap['total_paid'] > 0 ? 'text-emerald-500' : 'text-slate-200'; ?>">
                                        <span class="text-sm font-black mr-0.5 opacity-50">Rs.</span><?php echo number_format($ap['total_paid']); ?>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <form method="POST" class="inline" onsubmit="return confirm('Purge clinical history?')">
                                        <input type="hidden" name="record_id" value="<?php echo $ap['id']; ?>">
                                        <button type="submit" name="delete_procedure" value="1" class="w-11 h-11 flex items-center justify-center rounded-xl bg-slate-50 text-slate-300 hover:text-rose-500 transition-all">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </button>
                                    </form>
                                    <a href="procedures_add.php?edit=<?php echo $ap['id']; ?>" class="w-11 h-11 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-teal-600 hover:text-white transition-all shadow-sm">
                                        <i class="fa-solid fa-pen-nib text-sm"></i>
                                    </a>
                                    <a href="receipts_add.php?patient_id=<?php echo $patient_id; ?>&procedure_id=<?php echo $ap['id']; ?>" class="bg-brand-500 hover:bg-black text-white px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 shadow-lg shadow-brand-100 transition-all active:scale-95">
                                        <i class="fa-solid fa-file-invoice-dollar"></i> Settle Billing
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
    endforeach; ?>
                </div>
                <?php
endif; ?>
            </div>

        </div><!-- end tab content wrapper -->
    </div><!-- end right panel -->

    <!-- ══════════════ HISTORY MODAL (overlay on both panels) ══════════════ -->
    <div x-show="showHistoryModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="showHistoryModal = false"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-10"
         style="display:none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showHistoryModal = false"></div>
        <div class="relative bg-white rounded-[3rem] shadow-2xl w-full max-w-2xl max-h-full overflow-hidden z-10 flex flex-col"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-12 scale-95 opacity-0"
             x-transition:enter-end="translate-y-0 scale-100 opacity-100">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-white relative">
                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-brand-500 via-brand-600 to-brand-400"></div>
                <div>
                    <h3 class="text-2xl font-black text-slate-950" x-text="editHistory ? 'Edit Visit Record' : 'New Clinical Visit'"></h3>
                    <p class="text-[10px] font-black text-brand-500 uppercase tracking-widest mt-1"><?php echo esc($patient['first_name'] . ' ' . $patient['last_name']); ?> · ID: <?php echo esc($patient['mr_number']); ?></p>
                </div>
                <button @click="showHistoryModal = false" class="w-12 h-12 rounded-2xl bg-gray-50 text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all flex items-center justify-center active:scale-95 group">
                    <i class="fa-solid fa-xmark text-lg group-hover:rotate-90 transition-transform"></i>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <form method="POST" class="flex-1 overflow-y-auto p-6 space-y-5 custom-scrollbar">
                <input type="hidden" name="history_id" :value="editHistory ? editHistory.history_id : ''">
                <!-- Record For — compact inline toggle -->
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-xs font-medium text-slate-500">Record for:</span>
                    <label class="cursor-pointer">
                        <input type="radio" name="record_for" value="Patient" class="peer sr-only"
                               :checked="!editHistory || editHistory.record_for !== 'Spouse'">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border-2 transition-all
                                     border-gray-200 text-gray-500 bg-white
                                     peer-checked:border-teal-500 peer-checked:text-teal-700 peer-checked:bg-teal-50">
                            <i class="fa-solid fa-user text-[10px]"></i> Patient
                        </span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="record_for" value="Spouse" class="peer sr-only"
                               :checked="editHistory && editHistory.record_for === 'Spouse'">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border-2 transition-all
                                     border-gray-200 text-gray-500 bg-white
                                     peer-checked:border-rose-400 peer-checked:text-rose-700 peer-checked:bg-rose-50">
                            <i class="fa-solid fa-heart text-[10px]"></i> Spouse
                        </span>
                    </label>
                </div>


                <!-- Notes & Findings -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-align-left text-brand-500"></i> Presenting Complaints & Clinical Notes
                        </label>
                        <textarea name="clinical_notes" rows="4" :value="editHistory ? editHistory.clinical_notes : ''"
                                  class="w-full px-6 py-5 bg-slate-50 border-none rounded-[1.5rem] focus:bg-white focus:ring-4 focus:ring-brand-500/10 text-sm font-medium transition-all"
                                  placeholder="Document clinical findings and patient subjective notes..."></textarea>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-dna text-violet-500"></i> Diagnosis
                        </label>
                        <textarea name="diagnosis" rows="2" :value="editHistory ? editHistory.diagnosis : ''"
                                  class="w-full px-6 py-5 bg-violet-50/30 border-none rounded-[1.5rem] focus:bg-white focus:ring-4 focus:ring-violet-500/10 text-sm font-black text-violet-950 transition-all placeholder:font-medium placeholder:text-violet-300"
                                  placeholder="Primary clinical diagnosis or ICD-10 code..."></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-pills text-rose-500"></i> Medications / Treatment
                            </label>
                            <textarea name="medication" rows="3" :value="editHistory ? editHistory.medication : ''"
                                      class="w-full px-6 py-5 bg-rose-50/20 border-none rounded-[1.5rem] focus:bg-white focus:ring-4 focus:ring-rose-500/10 text-sm font-bold text-rose-950 transition-all placeholder:font-medium"
                                      placeholder="Brief pharmacy guidance..."></textarea>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-calendar-check text-brand-500"></i> Next Visit / Follow-up Date
                            </label>
                            <div class="relative">
                                <input type="date" name="next_visit" :value="editHistory ? editHistory.next_visit : ''"
                                       class="w-full px-6 py-5 bg-brand-50/30 border-none rounded-[1.5rem] focus:bg-white focus:ring-4 focus:ring-brand-500/10 font-black text-brand-800 text-sm transition-all appearance-none">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-lightbulb text-amber-500"></i> Advice & Instructions
                        </label>
                        <textarea name="advice" rows="3" :value="editHistory ? editHistory.advice : ''"
                                  class="w-full px-6 py-5 bg-amber-50/20 border-none rounded-[1.5rem] focus:bg-white focus:ring-4 focus:ring-amber-500/10 text-sm font-medium transition-all"
                                  placeholder="Lifestyle, nutrition, and strategic clinical advice..."></textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-50">
                    <button type="button" @click="showHistoryModal = false"
                            class="px-5 py-2.5 rounded-xl font-medium text-slate-500 bg-gray-100 hover:bg-gray-200 transition-all text-sm active:scale-95">Discard</button>
                    <button type="submit" :name="editHistory ? 'edit_history' : 'add_history'" value="1"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white text-sm bg-teal-600 hover:bg-teal-700 shadow-sm transition-all active:scale-95">
                        <i class="fa-solid" :class="editHistory ? 'fa-floppy-disk' : 'fa-check'"></i>
                        <span x-text="editHistory ? 'Update Visit' : 'Save Visit'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div><!-- end app shell -->

<?php include __DIR__ . '/includes/footer.php'; ?>
