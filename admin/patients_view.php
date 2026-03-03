<?php
/**
 * PATIENT 360 — FULL APP-SHELL REDESIGN
 * True tab-switching UX with dark patient panel.
 */
$pageTitle = "Patient 360";
require_once __DIR__ . '/includes/auth.php';

$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($patient_id <= 0) { header("Location: patients.php"); exit; }

$error = $success = '';

// ── Handle Add / Edit Clinical History ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_history']) || isset($_POST['edit_history'])) {
        $notes      = $_POST['clinical_notes'] ?? '';
        $diagnosis  = $_POST['diagnosis']      ?? '';
        $medication = $_POST['medication']     ?? '';
        $advice     = $_POST['advice']         ?? '';
        $next_visit = !empty($_POST['next_visit']) ? $_POST['next_visit'] : null;
        $record_for = $_POST['record_for']     ?? 'Patient';

        if (isset($_POST['add_history'])) {
            $stmt = $conn->prepare("INSERT INTO patient_history (patient_id, clinical_notes, diagnosis, medication, advice, next_visit, record_for) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('issssss', $patient_id, $notes, $diagnosis, $medication, $advice, $next_visit, $record_for);
        } else {
            $history_id = intval($_POST['history_id']);
            $stmt = $conn->prepare("UPDATE patient_history SET clinical_notes=?, diagnosis=?, medication=?, advice=?, next_visit=?, record_for=? WHERE id=? AND patient_id=?");
            $stmt->bind_param('ssssssii', $notes, $diagnosis, $medication, $advice, $next_visit, $record_for, $history_id, $patient_id);
        }
        if ($stmt->execute()) {
            $action = isset($_POST['add_history']) ? 'added' : 'updated';
            header("Location: patients_view.php?id={$patient_id}&tab=history&msg={$action}");
            exit;
        } else { $error = "Operation failed: " . $stmt->error; }
    }
    if (isset($_POST['delete_history'])) {
        $id = intval($_POST['record_id']);
        $conn->query("DELETE FROM patient_history WHERE id = $id AND patient_id = $patient_id");
        header("Location: patients_view.php?id={$patient_id}&tab=history&msg=deleted"); exit;
    }
    if (isset($_POST['delete_rx'])) {
        $id = intval($_POST['record_id']);
        $conn->query("DELETE FROM prescription_medications WHERE prescription_id = $id");
        $conn->query("DELETE FROM prescription_lab_tests WHERE prescription_id = $id");
        $conn->query("DELETE FROM prescriptions WHERE id = $id AND patient_id = $patient_id");
        header("Location: patients_view.php?id={$patient_id}&tab=rx&msg=deleted"); exit;
    }
    if (isset($_POST['delete_semen'])) {
        $id = intval($_POST['record_id']);
        $conn->query("DELETE FROM semen_analyses WHERE id = $id AND patient_id = $patient_id");
        header("Location: patients_view.php?id={$patient_id}&tab=semen&msg=deleted"); exit;
    }
    if (isset($_POST['delete_usg'])) {
        $id = intval($_POST['record_id']);
        $conn->query("DELETE FROM patient_ultrasounds WHERE id = $id AND patient_id = $patient_id");
        header("Location: patients_view.php?id={$patient_id}&tab=usg&msg=deleted"); exit;
    }
    if (isset($_POST['delete_lab'])) {
        $id = intval($_POST['record_id']);
        $conn->query("DELETE FROM patient_lab_results WHERE id = $id AND patient_id = $patient_id");
        header("Location: patients_view.php?id={$patient_id}&tab=labs&msg=deleted"); exit;
    }
    if (isset($_POST['delete_procedure'])) {
        $id = intval($_POST['record_id']);
        $conn->query("DELETE FROM advised_procedures WHERE id = $id AND patient_id = $patient_id");
        header("Location: patients_view.php?id={$patient_id}&tab=procedures&msg=deleted"); exit;
    }
}

// ── Data Fetching ──────────────────────────────────────────────────────────────
try {
    $stmt = $conn->prepare("SELECT p.*, h.name AS hospital_name FROM patients p LEFT JOIN hospitals h ON p.referring_hospital_id = h.id WHERE p.id = ?");
    $stmt->bind_param('i', $patient_id);
    $stmt->execute();
    $patient = $stmt->get_result()->fetch_assoc();
    if (!$patient) die("Patient not found.");

    $pid = intval($patient_id);
    $histories          = $conn->query("SELECT * FROM patient_history WHERE patient_id = $pid ORDER BY recorded_at DESC")->fetch_all(MYSQLI_ASSOC);
    $semen_reports      = $conn->query("SELECT * FROM semen_analyses WHERE patient_id = $pid ORDER BY collection_time DESC")->fetch_all(MYSQLI_ASSOC);
    $prescriptions      = $conn->query("SELECT * FROM prescriptions WHERE patient_id = $pid ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
    $ultrasounds        = $conn->query("SELECT * FROM patient_ultrasounds WHERE patient_id = $pid ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
    $lab_results        = $conn->query("SELECT plt.*, ltd.test_name, ltd.unit, ltd.reference_range_male, ltd.reference_range_female FROM patient_lab_results plt JOIN lab_tests_directory ltd ON plt.test_id = ltd.id WHERE plt.patient_id = $pid ORDER BY plt.test_date DESC")->fetch_all(MYSQLI_ASSOC);
    $advised_procedures = $conn->query("SELECT ap.*, (SELECT COALESCE(SUM(r.amount),0) FROM receipts r WHERE r.advised_procedure_id = ap.id AND r.status = 'Paid') AS total_paid FROM advised_procedures ap WHERE ap.patient_id = $pid ORDER BY ap.date_advised DESC")->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) { $error = "Data fetch error: " . $e->getMessage(); }

// Initial tab (from URL or default)
$initial_tab = in_array($_GET['tab'] ?? '', ['history','rx','semen','usg','labs','procedures']) ? $_GET['tab'] : 'history';

// Patient avatar initials
$initials = strtoupper(substr($patient['first_name'] ?? 'P', 0, 1) . substr($patient['last_name'] ?? 'X', 0, 1));

// Tab definitions
$tabs = [
    ['id' => 'history',    'icon' => 'fa-notes-medical',   'label' => 'Clinical History', 'subtitle' => 'Visit notes & records',          'count' => count($histories),          'color_class' => 'emerald', 'btn_label' => 'Add Visit',      'btn_action' => '@click="openAddHistory()"', 'btn_href' => ''],
    ['id' => 'rx',         'icon' => 'fa-prescription',    'label' => 'Prescriptions',    'subtitle' => 'Medication & Rx vault',          'count' => count($prescriptions),      'color_class' => 'violet',  'btn_label' => 'New Prescription','btn_action' => '',               'btn_href' => "prescriptions_add.php?patient_id=$patient_id"],
    ['id' => 'semen',      'icon' => 'fa-flask-vial',      'label' => 'Semen Analysis',   'subtitle' => 'Andrology reports',              'count' => count($semen_reports),      'color_class' => 'cyan',    'btn_label' => 'New Analysis',   'btn_action' => '',               'btn_href' => "semen_analyses_add.php?patient_id=$patient_id"],
    ['id' => 'usg',        'icon' => 'fa-image',           'label' => 'Ultrasounds',      'subtitle' => 'Diagnostic imaging',             'count' => count($ultrasounds),        'color_class' => 'orange',  'btn_label' => 'Add Scan',       'btn_action' => '',               'btn_href' => "ultrasounds_add.php?patient_id=$patient_id"],
    ['id' => 'labs',       'icon' => 'fa-vials',           'label' => 'Lab Results',      'subtitle' => 'Test results & investigations',  'count' => count($lab_results),        'color_class' => 'amber',   'btn_label' => 'Post Result',    'btn_action' => '',               'btn_href' => "lab_results_add.php?patient_id=$patient_id"],
    ['id' => 'procedures', 'icon' => 'fa-clipboard-check', 'label' => 'Procedures',       'subtitle' => 'Treatments & billing',           'count' => count($advised_procedures), 'color_class' => 'rose',    'btn_label' => 'Log Procedure',  'btn_action' => '',               'btn_href' => "procedures_add.php?patient_id=$patient_id"],
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
<?php endif; ?>

<?php if (!empty($_GET['msg'])): ?>
<div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3 rounded-xl text-sm font-bold">
    <i class="fa-solid fa-circle-check text-emerald-500"></i>
    <?php $msgs = ['added'=>'Visit record added.','updated'=>'Visit record updated.','deleted'=>'Record deleted.','rx_saved'=>'Prescription saved.']; echo $msgs[$_GET['msg']] ?? 'Done.'; ?>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════
     MAIN APP SHELL — dark left panel + tabbed right content
═══════════════════════════════════════════════════════════════ -->
<div class="-mx-6 -my-6 flex min-h-[calc(100vh-4rem)]"
     x-data="{
        tab: '<?php echo $initial_tab; ?>',
        showHistoryModal: false,
        editHistory: null,
        openAddHistory()  { this.editHistory = null; this.showHistoryModal = true; },
        openEditHistory(d){ this.editHistory = d;    this.showHistoryModal = true; }
     }">

    <!-- ══════════════ LEFT: PATIENT PANEL ══════════════ -->
    <div class="w-72 shrink-0 bg-slate-950 flex flex-col overflow-y-auto scrollbar-hide border-r border-slate-800">

        <!-- Patient Card -->
        <div class="p-6 pb-5">
            <!-- Avatar -->
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-700 flex items-center justify-center text-white text-2xl font-black shadow-xl shadow-indigo-900/50 mb-4">
                <?php echo $initials; ?>
            </div>
            <h2 class="text-white text-lg font-black leading-snug"><?php echo esc($patient['first_name'] . ' ' . $patient['last_name']); ?></h2>
            <div class="font-mono text-slate-500 text-xs mt-0.5 tracking-wider"><?php echo esc($patient['mr_number']); ?></div>

            <!-- Chips -->
            <div class="flex flex-wrap gap-1.5 mt-3">
                <?php if ($patient['patient_age']): ?>
                <span class="bg-slate-800 text-slate-300 text-[10px] font-bold px-2.5 py-0.5 rounded-full"><?php echo esc($patient['patient_age']); ?> yrs</span>
                <?php endif; ?>
                <span class="bg-slate-800 text-slate-300 text-[10px] font-bold px-2.5 py-0.5 rounded-full"><?php echo esc($patient['gender'] ?? 'N/A'); ?></span>
                <?php if (!empty($patient['blood_group'])): ?>
                <span class="bg-red-900/50 text-red-300 text-[10px] font-bold px-2.5 py-0.5 rounded-full"><i class="fa-solid fa-droplet text-[8px] mr-0.5"></i><?php echo esc($patient['blood_group']); ?></span>
                <?php endif; ?>
                <?php if (!empty($patient['marital_status'])): ?>
                <span class="bg-slate-800 text-slate-400 text-[10px] font-bold px-2.5 py-0.5 rounded-full"><?php echo esc($patient['marital_status']); ?></span>
                <?php endif; ?>
            </div>

            <?php if (!empty($patient['phone'])): ?>
            <a href="tel:<?php echo esc($patient['phone']); ?>" class="flex items-center gap-2 mt-3 text-slate-500 hover:text-teal-400 text-xs font-medium transition-colors">
                <i class="fa-solid fa-phone text-[9px]"></i><?php echo esc($patient['phone']); ?>
            </a>
            <?php endif; ?>
            <?php if (!empty($patient['cnic'])): ?>
            <div class="flex items-center gap-2 mt-1 text-slate-600 text-xs font-mono">
                <i class="fa-solid fa-id-card text-[9px]"></i><?php echo esc($patient['cnic']); ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Obstetric: G/P/A (Female only) -->
        <?php if ($patient['gender'] === 'Female' && ($patient['gravida'] || $patient['para'])): ?>
        <div class="mx-4 mb-4 bg-slate-900 rounded-xl p-3 border border-slate-800">
            <div class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-2">Obstetric History</div>
            <div class="grid grid-cols-3 gap-2 text-center">
                <div><div class="text-xl font-black text-violet-400"><?php echo $patient['gravida'] ?? 0; ?></div><div class="text-[9px] text-slate-500 font-bold uppercase">Gravida</div></div>
                <div><div class="text-xl font-black text-indigo-400"><?php echo $patient['para'] ?? 0; ?></div><div class="text-[9px] text-slate-500 font-bold uppercase">Para</div></div>
                <div><div class="text-xl font-black text-rose-400"><?php echo $patient['abortions'] ?? 0; ?></div><div class="text-[9px] text-slate-500 font-bold uppercase">Abortus</div></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Spouse mini -->
        <?php if (!empty($patient['spouse_name'])): ?>
        <div class="mx-4 mb-4 bg-slate-900 rounded-xl p-3 border border-pink-900/40">
            <div class="text-[9px] font-black text-pink-600 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                <i class="fa-solid fa-heart text-[8px]"></i> Spouse / Partner
            </div>
            <div class="text-slate-200 text-sm font-bold"><?php echo esc($patient['spouse_name']); ?></div>
            <?php if (!empty($patient['spouse_age'])): ?>
            <div class="text-slate-500 text-xs mt-0.5"><?php echo esc($patient['spouse_age']); ?> yrs<?php echo !empty($patient['spouse_phone']) ? ' · ' . esc($patient['spouse_phone']) : ''; ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Referral -->
        <?php if (!empty($patient['hospital_name'])): ?>
        <div class="mx-4 mb-4">
            <div class="text-[9px] font-black text-slate-600 uppercase tracking-widest mb-1">Referred By</div>
            <div class="text-slate-400 text-xs font-bold flex items-center gap-1.5">
                <i class="fa-solid fa-hospital text-[9px] text-slate-600"></i>
                <?php echo esc($patient['hospital_name']); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Divider -->
        <div class="mx-4 border-t border-slate-800 mb-3"></div>

        <!-- Tab Navigation -->
        <div class="px-3 flex-1">
            <div class="text-[9px] font-black text-slate-600 uppercase tracking-widest px-3 mb-2">Patient Records</div>
            <?php
            $tab_colors = [
                'emerald' => ['active_bg' => 'bg-emerald-500/15 border-emerald-500/30', 'active_text' => 'text-emerald-300', 'active_icon' => 'text-emerald-400', 'active_badge' => 'bg-emerald-500 text-white'],
                'violet'  => ['active_bg' => 'bg-violet-500/15 border-violet-500/30',   'active_text' => 'text-violet-300',  'active_icon' => 'text-violet-400',  'active_badge' => 'bg-violet-500 text-white'],
                'cyan'    => ['active_bg' => 'bg-cyan-500/15 border-cyan-500/30',       'active_text' => 'text-cyan-300',    'active_icon' => 'text-cyan-400',    'active_badge' => 'bg-cyan-500 text-white'],
                'orange'  => ['active_bg' => 'bg-orange-500/15 border-orange-500/30',   'active_text' => 'text-orange-300',  'active_icon' => 'text-orange-400',  'active_badge' => 'bg-orange-500 text-white'],
                'amber'   => ['active_bg' => 'bg-amber-500/15 border-amber-500/30',     'active_text' => 'text-amber-300',   'active_icon' => 'text-amber-400',   'active_badge' => 'bg-amber-500 text-white'],
                'rose'    => ['active_bg' => 'bg-rose-500/15 border-rose-500/30',       'active_text' => 'text-rose-300',    'active_icon' => 'text-rose-400',    'active_badge' => 'bg-rose-500 text-white'],
            ];
            foreach ($tabs as $t):
                $tc = $tab_colors[$t['color_class']];
            ?>
            <button @click="tab = '<?php echo $t['id']; ?>'"
                    class="w-full text-left px-3 py-2.5 rounded-xl mb-1 flex items-center gap-3 transition-all border group"
                    :class="tab === '<?php echo $t['id']; ?>'
                        ? '<?php echo $tc['active_bg']; ?>'
                        : 'border-transparent hover:bg-slate-800'">
                <i class="fa-solid <?php echo $t['icon']; ?> text-sm w-4 text-center flex-shrink-0 transition-colors"
                   :class="tab === '<?php echo $t['id']; ?>' ? '<?php echo $tc['active_icon']; ?>' : 'text-slate-600 group-hover:text-slate-400'"></i>
                <span class="flex-1 text-sm font-bold transition-colors"
                      :class="tab === '<?php echo $t['id']; ?>' ? '<?php echo $tc['active_text']; ?>' : 'text-slate-500 group-hover:text-slate-300'"><?php echo $t['label']; ?></span>
                <span class="text-[9px] font-black w-5 h-5 rounded-full flex items-center justify-center transition-colors"
                      :class="tab === '<?php echo $t['id']; ?>' ? '<?php echo $tc['active_badge']; ?>' : 'bg-slate-800 text-slate-500'"><?php echo $t['count']; ?></span>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- Bottom Actions -->
        <div class="p-4 mt-4 border-t border-slate-800 space-y-2">
            <a href="patients_edit.php?id=<?php echo $patient_id; ?>"
               class="flex items-center justify-center gap-2 w-full bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold py-2.5 rounded-xl transition-all">
                <i class="fa-solid fa-user-gear"></i> Edit Profile
            </a>
            <a href="patients.php"
               class="flex items-center justify-center gap-2 w-full text-slate-600 hover:text-slate-400 text-xs font-medium py-2 rounded-xl transition-all">
                <i class="fa-solid fa-arrow-left text-[10px]"></i> All Patients
            </a>
        </div>

    </div><!-- end left panel -->

    <!-- ══════════════ RIGHT: CONTENT AREA ══════════════ -->
    <div class="flex-1 flex flex-col bg-slate-50 min-w-0 overflow-y-auto">

        <!-- Sticky Tab Header -->
        <div class="sticky top-0 z-20 bg-white border-b border-gray-100 shadow-sm">
            <?php foreach ($tabs as $t):
                $tc = $tab_colors[$t['color_class']];
                $icon_bg_map = ['emerald'=>'bg-emerald-100 text-emerald-600','violet'=>'bg-violet-100 text-violet-600','cyan'=>'bg-cyan-100 text-cyan-600','orange'=>'bg-orange-100 text-orange-600','amber'=>'bg-amber-100 text-amber-600','rose'=>'bg-rose-100 text-rose-600'];
                $btn_bg_map  = ['emerald'=>'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-100','violet'=>'bg-violet-600 hover:bg-violet-700 shadow-violet-100','cyan'=>'bg-cyan-600 hover:bg-cyan-700 shadow-cyan-100','orange'=>'bg-orange-500 hover:bg-orange-600 shadow-orange-100','amber'=>'bg-amber-500 hover:bg-amber-600 shadow-amber-100','rose'=>'bg-rose-600 hover:bg-rose-700 shadow-rose-100'];
            ?>
            <div x-show="tab === '<?php echo $t['id']; ?>'" x-cloak
                 class="flex items-center justify-between px-8 py-4 tab-panel">
                <!-- Tab Title -->
                <div class="flex items-center gap-4">
                    <div class="w-11 h-11 <?php echo $icon_bg_map[$t['color_class']]; ?> rounded-2xl flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-solid <?php echo $t['icon']; ?>"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-gray-800 leading-none"><?php echo $t['label']; ?></h2>
                        <p class="text-xs text-gray-400 font-medium mt-0.5"><?php echo $t['subtitle']; ?> &nbsp;·&nbsp; <span class="font-bold text-gray-500"><?php echo $t['count']; ?> record<?php echo $t['count'] !== 1 ? 's' : ''; ?></span></p>
                    </div>
                </div>
                <!-- Action Button -->
                <?php if ($t['btn_action']): ?>
                <button <?php echo $t['btn_action']; ?>
                        class="inline-flex items-center gap-2 <?php echo $btn_bg_map[$t['color_class']]; ?> text-white font-black text-sm px-5 py-3 rounded-xl shadow-lg transition-all active:scale-95">
                    <i class="fa-solid fa-plus text-xs"></i> <?php echo $t['btn_label']; ?>
                </button>
                <?php else: ?>
                <a href="<?php echo $t['btn_href']; ?>"
                   class="inline-flex items-center gap-2 <?php echo $btn_bg_map[$t['color_class']]; ?> text-white font-black text-sm px-5 py-3 rounded-xl shadow-lg transition-all active:scale-95">
                    <i class="fa-solid fa-plus text-xs"></i> <?php echo $t['btn_label']; ?>
                </a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ══════════════ TAB PANELS ══════════════ -->
        <div class="p-6 md:p-8 flex-1">

            <!-- ─── TAB: Clinical History ─── -->
            <div x-show="tab === 'history'" x-cloak class="tab-panel">
                <?php if (empty($histories)): ?>
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-20 h-20 bg-emerald-50 rounded-3xl flex items-center justify-center mb-5">
                        <i class="fa-solid fa-folder-open text-3xl text-emerald-200"></i>
                    </div>
                    <h3 class="font-black text-gray-400 text-lg mb-1">No visits recorded yet</h3>
                    <p class="text-gray-400 text-sm mb-5">Every visit note will appear here as a timeline.</p>
                    <button @click="openAddHistory()" class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100">
                        Record First Visit
                    </button>
                </div>
                <?php else: ?>
                <div class="space-y-5 relative before:absolute before:left-5 before:top-0 before:bottom-0 before:w-0.5 before:bg-gray-100">
                    <?php foreach ($histories as $idx => $h): ?>
                    <div class="relative pl-14 group">
                        <!-- Timeline dot -->
                        <div class="absolute left-0 top-4 w-10 h-10 bg-white border-4 border-gray-100 group-hover:border-emerald-200 rounded-2xl flex items-center justify-center z-10 shadow-sm transition-colors">
                            <i class="fa-solid fa-stethoscope text-emerald-500 text-xs"></i>
                        </div>
                        <div class="bg-white rounded-2xl border border-gray-100 hover:border-emerald-100 hover:shadow-lg transition-all duration-200 overflow-hidden">
                            <!-- Card header -->
                            <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-gray-50 bg-gray-50/40">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-[10px] font-black px-2.5 py-1 bg-slate-800 text-white rounded-full">VISIT #<?php echo count($histories) - $idx; ?></span>
                                    <span class="text-[9px] font-black px-2.5 py-1 rounded-lg uppercase <?php echo $h['record_for'] === 'Spouse' ? 'bg-pink-100 text-pink-700' : 'bg-emerald-100 text-emerald-700'; ?>">
                                        <?php echo esc($h['record_for']); ?>
                                    </span>
                                    <span class="text-xs font-bold text-gray-400">
                                        <i class="fa-regular fa-clock text-emerald-300 text-[10px] mr-1"></i>
                                        <?php echo date('d M Y, h:i A', strtotime($h['recorded_at'])); ?>
                                    </span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                <button @click="openEditHistory({
                                            history_id: <?php echo $h['id']; ?>,
                                            clinical_notes: <?php echo json_encode($h['clinical_notes'] ?? ''); ?>,
                                            diagnosis: <?php echo json_encode($h['diagnosis'] ?? ''); ?>,
                                            medication: <?php echo json_encode($h['medication'] ?? ''); ?>,
                                            advice: <?php echo json_encode($h['advice'] ?? ''); ?>,
                                            next_visit: <?php echo json_encode($h['next_visit'] ?? ''); ?>,
                                            record_for: <?php echo json_encode($h['record_for'] ?? 'Patient'); ?>
                                        })"
                                        class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-emerald-600 hover:text-white text-gray-400 flex items-center justify-center transition-all text-xs" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <form method="POST" class="inline" onsubmit="return confirm('Delete this visit record? This cannot be undone.')">
                                    <input type="hidden" name="record_id" value="<?php echo $h['id']; ?>">
                                    <button type="submit" name="delete_history" value="1"
                                            class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-rose-600 hover:text-white text-gray-400 flex items-center justify-center transition-all text-xs" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                                </div>
                            </div>
                            <!-- Card body -->
                            <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <div class="lg:col-span-2 space-y-4">
                                    <?php if (!empty($h['clinical_notes'])): ?>
                                    <div>
                                        <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center gap-1.5"><i class="fa-solid fa-align-left text-emerald-400"></i> Notes & Complaints</div>
                                        <div class="text-sm text-gray-700 leading-relaxed"><?php echo nl2br(esc(strip_tags($h['clinical_notes']))); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($h['diagnosis'])): ?>
                                    <div class="bg-violet-50/60 rounded-xl p-4 border border-violet-100/50">
                                        <div class="text-[9px] font-black text-violet-500 uppercase tracking-widest mb-1.5">Diagnosis / Impression</div>
                                        <div class="text-sm font-bold text-violet-900"><?php echo nl2br(esc($h['diagnosis'])); ?></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="space-y-3">
                                    <?php if (!empty($h['medication'])): ?>
                                    <div class="bg-rose-50/50 rounded-xl p-4 border border-rose-100/50">
                                        <div class="text-[9px] font-black text-rose-500 uppercase tracking-widest mb-1.5 flex items-center gap-1"><i class="fa-solid fa-pills text-[8px]"></i> Medications</div>
                                        <div class="text-xs text-rose-800 italic leading-relaxed"><?php echo nl2br(esc($h['medication'])); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($h['advice'])): ?>
                                    <div class="bg-sky-50/50 rounded-xl p-4 border border-sky-100/40">
                                        <div class="text-[9px] font-black text-sky-500 uppercase tracking-widest mb-1.5">Advice</div>
                                        <div class="text-xs text-gray-600 leading-relaxed"><?php echo nl2br(esc($h['advice'])); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($h['next_visit'])): ?>
                                    <div class="bg-slate-900 rounded-xl p-3 text-center shadow-lg">
                                        <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Follow-up</div>
                                        <div class="text-white font-black text-sm"><?php echo date('d M Y', strtotime($h['next_visit'])); ?></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- ─── TAB: Prescriptions ─── -->
            <div x-show="tab === 'rx'" x-cloak class="tab-panel">
                <?php if (empty($prescriptions)): ?>
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-20 h-20 bg-violet-50 rounded-3xl flex items-center justify-center mb-5">
                        <i class="fa-solid fa-prescription-bottle-medical text-3xl text-violet-200"></i>
                    </div>
                    <h3 class="font-black text-gray-400 text-lg mb-1">No prescriptions yet</h3>
                    <a href="prescriptions_add.php?patient_id=<?php echo $patient_id; ?>" class="mt-3 bg-violet-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-violet-700 transition-all">
                        Create First Prescription
                    </a>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <?php foreach ($prescriptions as $rx): ?>
                    <div class="bg-white rounded-2xl border border-gray-100 hover:border-violet-200 hover:shadow-lg transition-all duration-200 overflow-hidden group">
                        <div class="flex items-start gap-4 p-5">
                            <div class="w-12 h-12 bg-violet-100 text-violet-600 rounded-2xl flex items-center justify-center text-lg shrink-0 group-hover:bg-violet-600 group-hover:text-white transition-colors">
                                <i class="fa-solid fa-file-medical"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[10px] font-black text-gray-400 font-mono">RX-<?php echo str_pad($rx['id'], 5, '0', STR_PAD_LEFT); ?></span>
                                    <span class="text-[9px] font-black px-2 py-0.5 rounded-full uppercase <?php echo ($rx['record_for'] ?? '') === 'Spouse' ? 'bg-pink-100 text-pink-700' : 'bg-violet-100 text-violet-700'; ?>">
                                        <?php echo esc($rx['record_for'] ?? 'Patient'); ?>
                                    </span>
                                </div>
                                <?php if (!empty($rx['diagnosis'])): ?>
                                <div class="text-sm font-bold text-gray-800 truncate"><?php echo esc(substr(strip_tags($rx['diagnosis']), 0, 50)); ?></div>
                                <?php else: ?>
                                <div class="text-sm font-bold text-gray-800">Digital Prescription</div>
                                <?php endif; ?>
                                <div class="text-xs text-gray-400 mt-1 font-medium"><?php echo date('d M Y', strtotime($rx['created_at'])); ?></div>
                            </div>
                        </div>
                        <div class="border-t border-gray-50 px-5 py-3 flex items-center justify-between bg-gray-50/50">
                            <?php if (!empty($rx['next_visit'])): ?>
                            <span class="text-[10px] text-gray-400 font-bold flex items-center gap-1">
                                <i class="fa-solid fa-calendar-check text-violet-400 text-[9px]"></i>
                                Follow-up: <?php echo date('d M Y', strtotime($rx['next_visit'])); ?>
                            </span>
                            <?php else: ?>
                            <span></span>
                            <?php endif; ?>
                            <div class="flex items-center gap-2">
                                <?php if (!empty($rx['scanned_report_path'])): ?>
                                <a href="../<?php echo esc($rx['scanned_report_path']); ?>" target="_blank"
                                   class="w-8 h-8 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-400 hover:bg-gray-200 transition-all text-xs" title="Attachment">
                                    <i class="fa-solid fa-paperclip"></i>
                                </a>
                                <?php endif; ?>
                                <a href="prescriptions_print.php?id=<?php echo $rx['id']; ?>" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-violet-600 text-white rounded-xl text-[10px] font-black hover:bg-violet-700 transition-all shadow-sm">
                                    <i class="fa-solid fa-print"></i> Print / View
                                </a>
                                <form method="POST" class="inline" onsubmit="return confirm('Delete this prescription? This cannot be undone.')">
                                    <input type="hidden" name="record_id" value="<?php echo $rx['id']; ?>">
                                    <button type="submit" name="delete_rx" value="1"
                                            class="w-8 h-8 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-rose-400 hover:bg-rose-600 hover:text-white hover:border-rose-600 transition-all text-xs" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- ─── TAB: Semen Analysis ─── -->
            <div x-show="tab === 'semen'" x-cloak class="tab-panel">
                <?php if (empty($semen_reports)): ?>
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-20 h-20 bg-cyan-50 rounded-3xl flex items-center justify-center mb-5">
                        <i class="fa-solid fa-microscope text-3xl text-cyan-200"></i>
                    </div>
                    <h3 class="font-black text-gray-400 text-lg mb-1">No semen analysis reports</h3>
                    <a href="semen_analyses_add.php?patient_id=<?php echo $patient_id; ?>" class="mt-3 bg-cyan-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-cyan-700 transition-all">
                        Record First Analysis
                    </a>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    <?php foreach ($semen_reports as $sr):
                        // Flag abnormals
                        $conc_ok  = !$sr['concentration'] || $sr['concentration'] >= 16;
                        $mot_ok   = !$sr['pr_motility']   || ($sr['pr_motility'] + $sr['np_motility']) >= 42;
                        $morph_ok = !$sr['normal_morphology'] || $sr['normal_morphology'] >= 4;
                        $status   = ($conc_ok && $mot_ok && $morph_ok) ? 'normal' : 'abnormal';
                    ?>
                    <div class="bg-white rounded-2xl border border-gray-100 hover:shadow-xl transition-all duration-300 group overflow-hidden">
                        <div class="bg-gradient-to-br from-slate-800 to-slate-900 p-5">
                            <div class="flex justify-between items-start mb-3">
                                <div class="w-10 h-10 bg-cyan-500/20 rounded-xl flex items-center justify-center border border-cyan-500/30">
                                    <i class="fa-solid fa-flask-vial text-cyan-400 text-sm"></i>
                                </div>
                                <span class="text-[9px] font-black text-slate-500 uppercase"><?php echo date('M Y', strtotime($sr['collection_time'])); ?></span>
                            </div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[9px] font-black border <?php echo $status === 'normal' ? 'bg-emerald-500/20 border-emerald-500/30 text-emerald-400' : 'bg-rose-500/20 border-rose-500/30 text-rose-400'; ?>">
                                <i class="fa-solid <?php echo $status === 'normal' ? 'fa-check-circle' : 'fa-triangle-exclamation'; ?> text-[8px]"></i>
                                <?php echo esc($sr['auto_diagnosis'] ?: ($status === 'normal' ? 'Normozoospermia' : 'Abnormal')); ?>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-3 gap-3 mb-4 text-center">
                                <div class="bg-slate-50 rounded-xl p-2">
                                    <div class="text-base font-black text-gray-800 <?php echo !$conc_ok ? 'text-rose-600' : ''; ?>"><?php echo $sr['concentration'] ?: '—'; ?></div>
                                    <div class="text-[9px] text-gray-400 font-bold uppercase">M/ml</div>
                                </div>
                                <div class="bg-slate-50 rounded-xl p-2">
                                    <div class="text-base font-black text-gray-800 <?php echo !$mot_ok ? 'text-rose-600' : ''; ?>"><?php echo ($sr['pr_motility'] + $sr['np_motility']) ?: '—'; ?>%</div>
                                    <div class="text-[9px] text-gray-400 font-bold uppercase">Motility</div>
                                </div>
                                <div class="bg-slate-50 rounded-xl p-2">
                                    <div class="text-base font-black text-gray-800 <?php echo !$morph_ok ? 'text-rose-600' : ''; ?>"><?php echo $sr['normal_morphology'] ?: '—'; ?>%</div>
                                    <div class="text-[9px] text-gray-400 font-bold uppercase">Normal</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-gray-400">
                                <span class="font-bold"><?php echo date('d M Y', strtotime($sr['collection_time'])); ?></span>
                                <div class="flex items-center gap-1.5">
                                    <a href="semen_analyses_add.php?edit=<?php echo $sr['id']; ?>"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black hover:bg-slate-200 transition-all" title="Edit">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <a href="semen_analyses_print.php?id=<?php echo $sr['id']; ?>" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-cyan-600 text-white rounded-xl text-[10px] font-black hover:bg-cyan-700 transition-all">
                                        <i class="fa-solid fa-print"></i> Report
                                    </a>
                                    <form method="POST" class="inline" onsubmit="return confirm('Delete this semen analysis report? This cannot be undone.')">
                                        <input type="hidden" name="record_id" value="<?php echo $sr['id']; ?>">
                                        <button type="submit" name="delete_semen" value="1"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-rose-50 text-rose-500 rounded-xl text-[10px] font-black hover:bg-rose-600 hover:text-white transition-all" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- ─── TAB: Ultrasounds ─── -->
            <div x-show="tab === 'usg'" x-cloak class="tab-panel">
                <?php if (empty($ultrasounds)): ?>
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-20 h-20 bg-orange-50 rounded-3xl flex items-center justify-center mb-5">
                        <i class="fa-solid fa-camera-retro text-3xl text-orange-200"></i>
                    </div>
                    <h3 class="font-black text-gray-400 text-lg mb-1">No ultrasound scans recorded</h3>
                    <a href="ultrasounds_add.php?patient_id=<?php echo $patient_id; ?>" class="mt-3 bg-orange-500 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-orange-600 transition-all">
                        Add First Scan
                    </a>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    <?php foreach ($ultrasounds as $u): ?>
                    <div class="bg-white rounded-2xl border border-gray-100 hover:shadow-xl transition-all duration-200 group overflow-hidden">
                        <!-- Image/placeholder -->
                        <div class="relative aspect-video bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center overflow-hidden">
                            <?php if (!empty($u['scanned_report_path'])): ?>
                            <img src="../<?php echo esc($u['scanned_report_path']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <?php else: ?>
                            <i class="fa-solid fa-image text-4xl text-slate-300"></i>
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                            <div class="absolute bottom-3 left-3 flex items-center gap-2">
                                <span class="text-[9px] font-black uppercase text-white bg-orange-500/90 px-2.5 py-1 rounded-lg">
                                    <?php echo esc($u['record_for'] ?? 'Patient'); ?>
                                </span>
                            </div>
                        </div>
                        <div class="p-4">
                            <h4 class="font-black text-gray-800 text-sm mb-1 truncate"><?php echo esc($u['report_title'] ?? 'Ultrasound Report'); ?></h4>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs text-gray-400 font-medium"><?php echo date('d M Y', strtotime($u['created_at'])); ?></span>
                                <div class="flex items-center gap-1.5">
                                    <a href="ultrasounds_add.php?edit=<?php echo $u['id']; ?>"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black hover:bg-slate-200 transition-all" title="Edit">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <a href="ultrasounds_print.php?id=<?php echo $u['id']; ?>" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-500 text-white rounded-xl text-[10px] font-black hover:bg-orange-600 transition-all">
                                        <i class="fa-solid fa-print"></i> View
                                    </a>
                                    <form method="POST" class="inline" onsubmit="return confirm('Delete this ultrasound report? This cannot be undone.')">
                                        <input type="hidden" name="record_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" name="delete_usg" value="1"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-rose-50 text-rose-500 rounded-xl text-[10px] font-black hover:bg-rose-600 hover:text-white transition-all" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- ─── TAB: Lab Results ─── -->
            <div x-show="tab === 'labs'" x-cloak class="tab-panel">
                <?php if (empty($lab_results)): ?>
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-20 h-20 bg-amber-50 rounded-3xl flex items-center justify-center mb-5">
                        <i class="fa-solid fa-vial-circle-check text-3xl text-amber-200"></i>
                    </div>
                    <h3 class="font-black text-gray-400 text-lg mb-1">No lab results posted</h3>
                    <a href="lab_results_add.php?patient_id=<?php echo $patient_id; ?>" class="mt-3 bg-amber-500 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-amber-600 transition-all">
                        Post First Result
                    </a>
                </div>
                <?php else: ?>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <!-- Pending count badge -->
                    <?php $pending_count = count(array_filter($lab_results, fn($lr) => $lr['status'] === 'Pending')); ?>
                    <?php if ($pending_count > 0): ?>
                    <div class="bg-amber-50 border-b border-amber-100 px-6 py-3 flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-amber-500 text-sm animate-pulse"></i>
                        <span class="text-xs font-black text-amber-700"><?php echo $pending_count; ?> result<?php echo $pending_count !== 1 ? 's' : ''; ?> pending</span>
                    </div>
                    <?php endif; ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50/80 text-[9px] uppercase font-black tracking-widest text-gray-400 border-b border-gray-100">
                                    <th class="px-6 py-4">Test Name</th>
                                    <th class="px-6 py-4">For</th>
                                    <th class="px-6 py-4 text-center">Result</th>
                                    <th class="px-6 py-4">Reference</th>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4 text-right">Report</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php foreach ($lab_results as $lr): ?>
                                <tr class="hover:bg-amber-50/20 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="font-black text-gray-800 text-sm"><?php echo esc($lr['test_name']); ?></div>
                                        <?php if (!empty($lr['lab_name'])): ?>
                                        <div class="text-[9px] text-gray-400 font-bold mt-0.5"><i class="fa-solid fa-flask text-[8px] mr-1"></i><?php echo esc($lr['lab_name']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase <?php echo $lr['test_for'] === 'Spouse' ? 'bg-pink-100 text-pink-700' : 'bg-amber-100 text-amber-700'; ?>">
                                            <?php echo esc($lr['test_for'] ?? 'Patient'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if ($lr['status'] === 'Pending'): ?>
                                        <span class="inline-flex items-center gap-1.5 text-amber-500 font-bold text-xs">
                                            <i class="fa-solid fa-clock animate-pulse text-[10px]"></i> Pending
                                        </span>
                                        <?php else: ?>
                                        <div class="text-lg font-black text-gray-800 leading-none"><?php echo esc($lr['result_value']); ?></div>
                                        <div class="text-[9px] text-gray-400 uppercase font-bold"><?php echo esc($lr['unit']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $targetGender = ($lr['test_for'] === 'Patient') ? ($patient['gender'] ?? 'Male') : ($patient['gender'] === 'Male' ? 'Female' : 'Male');
                                        $ref = $targetGender === 'Male' ? $lr['reference_range_male'] : $lr['reference_range_female'];
                                        ?>
                                        <div class="text-[10px] font-mono text-gray-500 bg-gray-50 px-2 py-1.5 rounded-lg max-w-[120px] truncate"><?php echo esc($ref ?: '—'); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs font-bold text-gray-600"><?php echo date('d M Y', strtotime($lr['test_date'])); ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                        <?php if (!empty($lr['scanned_report_path'])): ?>
                                        <a href="../<?php echo esc($lr['scanned_report_path']); ?>" target="_blank"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 text-white rounded-xl text-[10px] font-black hover:bg-amber-600 transition-all shadow-sm">
                                            <i class="fa-solid fa-file-pdf"></i> View
                                        </a>
                                        <?php endif; ?>
                                        <a href="lab_results_add.php?edit=<?php echo $lr['id']; ?>"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black hover:bg-slate-200 transition-all" title="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form method="POST" class="inline" onsubmit="return confirm('Delete this lab result? This cannot be undone.')">
                                            <input type="hidden" name="record_id" value="<?php echo $lr['id']; ?>">
                                            <button type="submit" name="delete_lab" value="1"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-rose-50 text-rose-500 rounded-xl text-[10px] font-black hover:bg-rose-600 hover:text-white transition-all" title="Delete">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ─── TAB: Procedures ─── -->
            <div x-show="tab === 'procedures'" x-cloak class="tab-panel pb-8">
                <?php if (empty($advised_procedures)): ?>
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-20 h-20 bg-rose-50 rounded-3xl flex items-center justify-center mb-5">
                        <i class="fa-solid fa-notes-medical text-3xl text-rose-200"></i>
                    </div>
                    <h3 class="font-black text-gray-400 text-lg mb-1">No procedures documented</h3>
                    <a href="procedures_add.php?patient_id=<?php echo $patient_id; ?>" class="mt-3 bg-rose-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-rose-700 transition-all">
                        Log First Procedure
                    </a>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <?php foreach ($advised_procedures as $ap):
                        $statusCfg = match($ap['status']) {
                            'Advised'     => ['dot' => 'bg-amber-400',   'badge' => 'bg-amber-100 text-amber-800 border-amber-200',   'icon' => 'fa-clock'],
                            'In Progress' => ['dot' => 'bg-sky-400',     'badge' => 'bg-sky-100 text-sky-800 border-sky-200',         'icon' => 'fa-spinner'],
                            'Completed'   => ['dot' => 'bg-emerald-400', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200','icon' => 'fa-check-circle'],
                            default       => ['dot' => 'bg-gray-300',    'badge' => 'bg-gray-100 text-gray-600 border-gray-200',       'icon' => 'fa-folder'],
                        };
                    ?>
                    <div class="bg-white rounded-2xl border border-gray-100 hover:shadow-xl transition-all group overflow-hidden">
                        <!-- Header stripe -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50 bg-gray-50/40">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[9px] font-black uppercase border <?php echo $statusCfg['badge']; ?>">
                                <i class="fa-solid <?php echo $statusCfg['icon']; ?> text-[8px]"></i>
                                <?php echo esc($ap['status']); ?>
                            </span>
                            <span class="text-[9px] font-black text-gray-300"><?php echo date('d M Y', strtotime($ap['date_advised'])); ?></span>
                        </div>
                        <div class="p-6">
                            <h4 class="text-lg font-black text-gray-800 mb-0.5"><?php echo esc($ap['procedure_name']); ?></h4>
                            <div class="text-[10px] text-gray-400 font-bold uppercase mb-3">For: <?php echo esc($ap['record_for'] ?? 'Patient'); ?></div>
                            <?php if (!empty($ap['notes'])): ?>
                            <p class="text-xs text-gray-500 italic bg-gray-50 px-4 py-3 rounded-xl leading-relaxed"><?php echo esc($ap['notes']); ?></p>
                            <?php endif; ?>
                            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-50">
                                <div>
                                    <div class="text-[9px] font-black text-gray-400 uppercase tracking-wider">Payments Received</div>
                                    <div class="text-xl font-black <?php echo $ap['total_paid'] > 0 ? 'text-emerald-600' : 'text-gray-200'; ?>">
                                        Rs. <?php echo number_format($ap['total_paid']); ?>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="procedures_add.php?edit=<?php echo $ap['id']; ?>"
                                       class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-600 hover:bg-slate-200 px-3 py-2 rounded-xl text-xs font-black transition-all" title="Edit">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <a href="receipts_add.php?patient_id=<?php echo $patient_id; ?>&procedure_id=<?php echo $ap['id']; ?>"
                                       class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white px-4 py-2 rounded-xl text-xs font-black transition-all border border-emerald-100">
                                        <i class="fa-solid fa-file-invoice-dollar"></i> Bill
                                    </a>
                                    <form method="POST" class="inline" onsubmit="return confirm('Delete this procedure? This cannot be undone.')">
                                        <input type="hidden" name="record_id" value="<?php echo $ap['id']; ?>">
                                        <button type="submit" name="delete_procedure" value="1"
                                                class="inline-flex items-center gap-1 px-2.5 py-2 bg-rose-50 text-rose-500 rounded-xl text-xs font-black hover:bg-rose-600 hover:text-white transition-all" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

        </div><!-- end tab content wrapper -->
    </div><!-- end right panel -->

    <!-- ══════════════ HISTORY MODAL (overlay on both panels) ══════════════ -->
    <div x-show="showHistoryModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="showHistoryModal = false"
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4"
         style="display:none;">
        <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" @click="showHistoryModal = false"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[92vh] overflow-y-auto z-10"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="translate-y-8 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100">
            <!-- Modal header -->
            <div class="sticky top-0 bg-white border-b border-gray-100 px-8 py-5 flex items-center justify-between rounded-t-3xl z-10">
                <div>
                    <h3 class="text-xl font-black text-gray-800" x-text="editHistory ? 'Edit Visit Record' : 'Record New Visit'"></h3>
                    <p class="text-xs text-gray-400 mt-0.5"><?php echo esc($patient['first_name'] . ' ' . $patient['last_name']); ?> — <?php echo esc($patient['mr_number']); ?></p>
                </div>
                <button @click="showHistoryModal = false" class="w-9 h-9 rounded-xl bg-gray-100 hover:bg-red-100 hover:text-red-600 text-gray-500 flex items-center justify-center transition-all">
                    <i class="fa-solid fa-times text-sm"></i>
                </button>
            </div>
            <!-- Modal form -->
            <form method="POST" class="p-8 space-y-6">
                <input type="hidden" name="history_id" :value="editHistory ? editHistory.history_id : ''">
                <!-- For: Patient or Spouse -->
                <div>
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-3">Record is for</label>
                    <div class="flex gap-3">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="record_for" value="Patient" class="peer sr-only" :checked="!editHistory || editHistory.record_for === 'Patient'">
                            <div class="bg-gray-50 border-2 border-transparent p-4 rounded-2xl text-center transition-all peer-checked:bg-emerald-50 peer-checked:border-emerald-400">
                                <i class="fa-solid fa-user-injured text-2xl text-gray-300 mb-1 block transition-colors peer-checked:text-emerald-600"></i>
                                <span class="text-xs font-black text-gray-400 uppercase">Patient</span>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="record_for" value="Spouse" class="peer sr-only" :checked="editHistory && editHistory.record_for === 'Spouse'">
                            <div class="bg-gray-50 border-2 border-transparent p-4 rounded-2xl text-center transition-all peer-checked:bg-pink-50 peer-checked:border-pink-400">
                                <i class="fa-solid fa-heart text-2xl text-gray-300 mb-1 block"></i>
                                <span class="text-xs font-black text-gray-400 uppercase">Spouse</span>
                            </div>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2"><i class="fa-solid fa-comment-medical text-emerald-400 mr-1"></i> Presenting Complaints & Notes</label>
                    <textarea name="clinical_notes" rows="4" :value="editHistory ? editHistory.clinical_notes : ''"
                              class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-emerald-500 text-sm font-medium resize-none"
                              placeholder="History of presenting illness, clinical findings..."></textarea>
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2"><i class="fa-solid fa-stethoscope text-violet-400 mr-1"></i> Diagnosis / Clinical Impression</label>
                    <textarea name="diagnosis" rows="3" :value="editHistory ? editHistory.diagnosis : ''"
                              class="w-full px-5 py-4 bg-violet-50/40 border-none rounded-2xl focus:ring-2 focus:ring-violet-500 text-sm font-medium resize-none"
                              placeholder="ICD-10 code or clinical impression..."></textarea>
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2"><i class="fa-solid fa-pills text-rose-400 mr-1"></i> Medications (brief note)</label>
                    <textarea name="medication" rows="2" :value="editHistory ? editHistory.medication : ''"
                              class="w-full px-5 py-4 bg-rose-50/30 border-none rounded-2xl focus:ring-2 focus:ring-rose-500 text-sm font-medium resize-none"
                              placeholder="e.g. Tab Folic Acid 5mg OD × 3 months..."></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2"><i class="fa-solid fa-lightbulb text-amber-400 mr-1"></i> Lifestyle Advice</label>
                        <textarea name="advice" rows="3" :value="editHistory ? editHistory.advice : ''"
                                  class="w-full px-5 py-4 bg-amber-50/20 border-none rounded-2xl focus:ring-2 focus:ring-amber-500 text-sm font-medium resize-none"
                                  placeholder="Diet, lifestyle, activity guidance..."></textarea>
                    </div>
                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2"><i class="fa-solid fa-calendar-check text-emerald-400 mr-1"></i> Next Follow-up</label>
                        <input type="date" name="next_visit" :value="editHistory ? editHistory.next_visit : ''"
                               class="w-full px-5 py-4 bg-emerald-50/30 border-none rounded-2xl focus:ring-2 focus:ring-emerald-500 font-bold text-emerald-800">
                    </div>
                </div>
                <div class="flex gap-3 pt-2 border-t border-gray-50">
                    <button type="button" @click="showHistoryModal = false"
                            class="flex-1 py-3.5 rounded-2xl font-black text-gray-500 bg-gray-100 hover:bg-gray-200 transition-all text-sm">Cancel</button>
                    <button type="submit" :name="editHistory ? 'edit_history' : 'add_history'" value="1"
                            class="flex-[2] py-3.5 rounded-2xl font-black text-white bg-emerald-600 hover:bg-emerald-700 shadow-xl shadow-emerald-100 transition-all text-sm flex items-center justify-center gap-2">
                        <i class="fa-solid" :class="editHistory ? 'fa-floppy-disk' : 'fa-plus-circle'"></i>
                        <span x-text="editHistory ? 'Update Record' : 'Save Visit Record'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div><!-- end app shell -->

<?php include __DIR__ . '/includes/footer.php'; ?>
