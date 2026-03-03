<?php
/**
 * PATIENT 360 PROFILE — COMPLETE OVERHAUL
 * Premium sectional layout for full patient clinical data.
 */
$pageTitle = "Patient 360 Profile";
require_once __DIR__ . '/includes/auth.php';

$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($patient_id <= 0) {
    header("Location: patients.php");
    exit;
}

$error   = '';
$success = '';

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
            $stmt = $conn->prepare(
                "INSERT INTO patient_history (patient_id, clinical_notes, diagnosis, medication, advice, next_visit, record_for)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('issssss', $patient_id, $notes, $diagnosis, $medication, $advice, $next_visit, $record_for);
        } else {
            $history_id = intval($_POST['history_id']);
            $stmt = $conn->prepare(
                "UPDATE patient_history
                 SET clinical_notes=?, diagnosis=?, medication=?, advice=?, next_visit=?, record_for=?
                 WHERE id=? AND patient_id=?"
            );
            $stmt->bind_param('ssssssii', $notes, $diagnosis, $medication, $advice, $next_visit, $record_for, $history_id, $patient_id);
        }

        if ($stmt->execute()) {
            $action = isset($_POST['add_history']) ? 'added' : 'updated';
            header("Location: patients_view.php?id={$patient_id}&msg={$action}");
            exit;
        } else {
            $error = "Operation failed: " . $stmt->error;
        }
    }
}

// ── Data Fetching ──────────────────────────────────────────────────────────────
try {
    // Patient & Hospital
    $stmt = $conn->prepare(
        "SELECT p.*, h.name AS hospital_name
         FROM patients p
         LEFT JOIN hospitals h ON p.referring_hospital_id = h.id
         WHERE p.id = ?"
    );
    $stmt->bind_param('i', $patient_id);
    $stmt->execute();
    $patient = $stmt->get_result()->fetch_assoc();
    if (!$patient) die("Patient not found.");

    // All clinical data — intval-guarded IDs used directly (safe)
    $pid = intval($patient_id);
    $histories          = $conn->query("SELECT * FROM patient_history WHERE patient_id = $pid ORDER BY recorded_at DESC")->fetch_all(MYSQLI_ASSOC);
    $semen_reports      = $conn->query("SELECT * FROM semen_analyses WHERE patient_id = $pid ORDER BY collection_time DESC")->fetch_all(MYSQLI_ASSOC);
    $prescriptions      = $conn->query("SELECT * FROM prescriptions WHERE patient_id = $pid ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
    $ultrasounds        = $conn->query("SELECT * FROM patient_ultrasounds WHERE patient_id = $pid ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
    $lab_results        = $conn->query(
        "SELECT plt.*, ltd.test_name, ltd.unit, ltd.reference_range_male, ltd.reference_range_female
         FROM patient_lab_results plt
         JOIN lab_tests_directory ltd ON plt.test_id = ltd.id
         WHERE plt.patient_id = $pid
         ORDER BY plt.test_date DESC"
    )->fetch_all(MYSQLI_ASSOC);
    $advised_procedures = $conn->query(
        "SELECT ap.*,
                (SELECT COALESCE(SUM(r.amount),0) FROM receipts r WHERE r.advised_procedure_id = ap.id AND r.status = 'Paid') AS total_paid
         FROM advised_procedures ap
         WHERE ap.patient_id = $pid
         ORDER BY ap.date_advised DESC"
    )->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    $error = "Data fetch error: " . $e->getMessage();
}

include __DIR__ . '/includes/header.php';
?>

<?php if (!empty($_GET['msg'])): ?>
<div class="max-w-[1400px] mx-auto px-4 pt-4">
    <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3 rounded-xl text-sm font-bold">
        <i class="fa-solid fa-circle-check text-emerald-500"></i>
        <?php
        $msgs = ['added' => 'Clinical visit record added successfully.', 'updated' => 'Visit record updated.', 'rx_saved' => 'Prescription saved to vault.'];
        echo $msgs[$_GET['msg']] ?? 'Action completed.';
        ?>
    </div>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="max-w-[1400px] mx-auto px-4 pt-4">
    <div class="flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 px-5 py-3 rounded-xl text-sm font-bold">
        <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
        <?php echo esc($error); ?>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════ MAIN WRAPPER ═══ -->
<div class="max-w-[1400px] mx-auto px-4 py-6"
     x-data="{
        showHistoryModal: false,
        editHistory: null,
        openAddHistory() { this.editHistory = null; this.showHistoryModal = true; },
        openEditHistory(data) { this.editHistory = data; this.showHistoryModal = true; }
     }"
     @open-history-form.window="openAddHistory()">

    <!-- ── HERO BANNER ──────────────────────────────────────────────────── -->
    <div class="relative mb-8 group">
        <div class="absolute inset-0 bg-gradient-to-r from-teal-600 via-teal-700 to-indigo-800 rounded-3xl shadow-2xl"></div>
        <div class="relative px-8 py-9 flex flex-col lg:flex-row items-center justify-between gap-6 text-white">

            <!-- Identity -->
            <div class="flex items-center gap-6 w-full lg:w-auto">
                <div class="relative shrink-0">
                    <div class="w-20 h-20 bg-white/15 backdrop-blur-sm rounded-2xl flex items-center justify-center text-4xl shadow-xl border border-white/20">
                        <i class="fa-solid <?php echo $patient['gender'] === 'Female' ? 'fa-person-dress' : 'fa-person'; ?>"></i>
                    </div>
                    <div class="absolute -bottom-1.5 -right-1.5 bg-teal-400 w-7 h-7 rounded-full border-4 border-teal-700 flex items-center justify-center">
                        <i class="fa-solid fa-check text-[9px]"></i>
                    </div>
                </div>
                <div>
                    <p class="text-[10px] font-black text-teal-300 uppercase tracking-[0.25em] mb-1">Patient 360 Profile</p>
                    <h1 class="text-2xl lg:text-3xl font-black tracking-tight mb-2"><?php echo esc($patient['first_name'] . ' ' . $patient['last_name']); ?></h1>
                    <div class="flex flex-wrap items-center gap-3 text-sm">
                        <span class="font-mono bg-white/10 px-3 py-1 rounded-lg text-xs border border-white/10"><?php echo esc($patient['mr_number']); ?></span>
                        <span class="flex items-center gap-1.5 text-teal-100"><i class="fa-solid fa-venus-mars text-xs"></i> <?php echo esc($patient['gender'] ?? 'N/A'); ?></span>
                        <span class="flex items-center gap-1.5 text-teal-100"><i class="fa-solid fa-cake-candles text-xs"></i> <?php echo esc($patient['patient_age'] ? $patient['patient_age'] . ' yrs' : 'Age N/A'); ?></span>
                        <?php if ($patient['blood_group']): ?>
                        <span class="flex items-center gap-1.5 text-red-200"><i class="fa-solid fa-droplet text-xs"></i> <?php echo esc($patient['blood_group']); ?></span>
                        <?php endif; ?>
                        <?php if ($patient['phone']): ?>
                        <span class="flex items-center gap-1.5 text-teal-100"><i class="fa-solid fa-phone text-xs"></i> <?php echo esc($patient['phone']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Spouse Snapshot -->
            <?php if (!empty($patient['spouse_name'])): ?>
            <div class="bg-white/10 backdrop-blur-sm border border-white/20 px-5 py-4 rounded-2xl flex items-center gap-4 min-w-[260px]">
                <div class="w-11 h-11 bg-pink-500/30 rounded-xl flex items-center justify-center text-lg text-pink-200 shrink-0">
                    <i class="fa-solid fa-heart"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-[9px] font-black text-pink-300 uppercase tracking-[0.2em]">Spouse / Partner</div>
                    <div class="font-bold text-base leading-tight truncate"><?php echo esc($patient['spouse_name']); ?></div>
                    <div class="text-xs text-white/50"><?php echo $patient['spouse_age'] ? $patient['spouse_age'] . ' yrs' : 'Linked Profile'; ?></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="flex gap-3 w-full lg:w-auto shrink-0">
                <a href="prescriptions_add.php?patient_id=<?php echo $patient_id; ?>"
                   class="bg-white text-teal-700 hover:bg-teal-50 px-5 py-3.5 rounded-xl font-black text-sm transition-all shadow-lg flex-1 lg:flex-none text-center flex items-center justify-center gap-2">
                    <i class="fa-solid fa-prescription"></i> New Rx
                </a>
                <a href="patients_edit.php?id=<?php echo $patient_id; ?>"
                   class="bg-white/10 hover:bg-white/20 text-white border border-white/20 px-5 py-3.5 rounded-xl font-black text-sm transition-all flex-1 lg:flex-none text-center flex items-center justify-center gap-2">
                    <i class="fa-solid fa-user-gear"></i> Edit Profile
                </a>
                <a href="patients.php"
                   class="bg-white/10 hover:bg-white/20 text-white border border-white/20 w-12 h-12 rounded-xl font-black text-sm transition-all flex items-center justify-center">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- ── TWO-COLUMN LAYOUT ────────────────────────────────────────────── -->
    <div class="flex gap-8 items-start">

        <!-- ── LEFT: Demographics Sidebar ── -->
        <div class="hidden xl:block w-72 shrink-0">
            <div class="sticky top-6 space-y-4">

                <!-- Patient Info Card -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="bg-gray-50 px-5 py-3 border-b border-gray-100">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Patient Details</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        <?php
                        $info_rows = [
                            ['icon' => 'fa-id-card',        'label' => 'CNIC',        'value' => $patient['cnic']            ?? '—'],
                            ['icon' => 'fa-ring',            'label' => 'Marital',     'value' => $patient['marital_status']  ?? '—'],
                            ['icon' => 'fa-calendar',        'label' => 'Date of Birth','value'=> $patient['date_of_birth'] ? date('d M Y', strtotime($patient['date_of_birth'])) : '—'],
                            ['icon' => 'fa-envelope',        'label' => 'Email',       'value' => $patient['email']           ?? '—'],
                            ['icon' => 'fa-location-dot',    'label' => 'Address',     'value' => $patient['address']         ?? '—'],
                            ['icon' => 'fa-hospital',        'label' => 'Referred By', 'value' => $patient['hospital_name']   ?? 'Direct / Walk-in'],
                        ];
                        foreach ($info_rows as $row):
                            if (empty($row['value']) || $row['value'] === '—') continue;
                        ?>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 bg-gray-50 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fa-solid <?php echo $row['icon']; ?> text-[10px] text-gray-400"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest"><?php echo $row['label']; ?></div>
                                <div class="text-xs font-bold text-gray-700 break-words"><?php echo esc($row['value']); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php if ($patient['gender'] === 'Female' && ($patient['gravida'] || $patient['para'])): ?>
                        <div class="mt-2 pt-3 border-t border-gray-50">
                            <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Obstetric History</div>
                            <div class="grid grid-cols-3 gap-2">
                                <div class="bg-purple-50 rounded-xl p-2 text-center">
                                    <div class="text-base font-black text-purple-700"><?php echo $patient['gravida'] ?? 0; ?></div>
                                    <div class="text-[8px] font-bold text-purple-400 uppercase">G</div>
                                </div>
                                <div class="bg-indigo-50 rounded-xl p-2 text-center">
                                    <div class="text-base font-black text-indigo-700"><?php echo $patient['para'] ?? 0; ?></div>
                                    <div class="text-[8px] font-bold text-indigo-400 uppercase">P</div>
                                </div>
                                <div class="bg-rose-50 rounded-xl p-2 text-center">
                                    <div class="text-base font-black text-rose-700"><?php echo $patient['abortions'] ?? 0; ?></div>
                                    <div class="text-[8px] font-bold text-rose-400 uppercase">A</div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Spouse Info Card -->
                <?php if (!empty($patient['spouse_name'])): ?>
                <div class="bg-white rounded-2xl border border-pink-100 shadow-sm overflow-hidden">
                    <div class="bg-pink-50 px-5 py-3 border-b border-pink-100">
                        <h3 class="text-[10px] font-black text-pink-400 uppercase tracking-widest">Spouse / Partner</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        <?php
                        $spouse_rows = [
                            ['icon' => 'fa-user',      'label' => 'Name',   'value' => $patient['spouse_name']  ?? ''],
                            ['icon' => 'fa-venus-mars','label' => 'Gender', 'value' => $patient['spouse_gender'] ?? ''],
                            ['icon' => 'fa-cake-candles','label'=>'Age',    'value' => $patient['spouse_age'] ? $patient['spouse_age'] . ' yrs' : ''],
                            ['icon' => 'fa-phone',     'label' => 'Phone',  'value' => $patient['spouse_phone']  ?? ''],
                            ['icon' => 'fa-id-card',   'label' => 'CNIC',   'value' => $patient['spouse_cnic']   ?? ''],
                        ];
                        foreach ($spouse_rows as $row):
                            if (empty($row['value'])) continue;
                        ?>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 bg-pink-50 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fa-solid <?php echo $row['icon']; ?> text-[10px] text-pink-400"></i>
                            </div>
                            <div>
                                <div class="text-[9px] font-black text-pink-300 uppercase tracking-widest"><?php echo $row['label']; ?></div>
                                <div class="text-xs font-bold text-gray-700"><?php echo esc($row['value']); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Record Summary -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="bg-gray-50 px-5 py-3 border-b border-gray-100">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Record Summary</h3>
                    </div>
                    <div class="p-4 grid grid-cols-2 gap-3">
                        <?php
                        $summary = [
                            ['count' => count($histories),          'label' => 'Visits',       'color' => 'teal'],
                            ['count' => count($prescriptions),      'label' => 'Rx',           'color' => 'indigo'],
                            ['count' => count($semen_reports),      'label' => 'SA Reports',   'color' => 'sky'],
                            ['count' => count($ultrasounds),        'label' => 'Scans',        'color' => 'emerald'],
                            ['count' => count($lab_results),        'label' => 'Lab Results',  'color' => 'amber'],
                            ['count' => count($advised_procedures), 'label' => 'Procedures',   'color' => 'rose'],
                        ];
                        foreach ($summary as $s):
                        ?>
                        <div class="bg-<?php echo $s['color']; ?>-50 rounded-xl p-3 text-center">
                            <div class="text-xl font-black text-<?php echo $s['color']; ?>-700"><?php echo $s['count']; ?></div>
                            <div class="text-[9px] font-black text-<?php echo $s['color']; ?>-400 uppercase tracking-wider leading-tight"><?php echo $s['label']; ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>

        <!-- ── RIGHT: Main Clinical Sections ── -->
        <div class="flex-1 min-w-0 space-y-8">

            <!-- Sticky Section Navigation -->
            <nav class="sticky top-0 z-40 bg-white/90 backdrop-blur-xl border border-gray-100 rounded-2xl shadow-sm px-2 py-1.5 flex items-center gap-1 overflow-x-auto scrollbar-hide">
                <?php
                $nav_items = [
                    ['href' => '#history',    'icon' => 'fa-notes-medical',   'label' => 'History',    'color' => 'teal'],
                    ['href' => '#semen',      'icon' => 'fa-flask-vial',      'label' => 'Semen',      'color' => 'sky'],
                    ['href' => '#rx',         'icon' => 'fa-prescription',    'label' => 'Rx',         'color' => 'indigo'],
                    ['href' => '#usg',        'icon' => 'fa-image',           'label' => 'Scans',      'color' => 'emerald'],
                    ['href' => '#labs',       'icon' => 'fa-vials',           'label' => 'Labs',       'color' => 'amber'],
                    ['href' => '#procedures', 'icon' => 'fa-clipboard-check', 'label' => 'Procedures', 'color' => 'rose'],
                ];
                foreach ($nav_items as $n):
                ?>
                <a href="<?php echo $n['href']; ?>"
                   class="group flex flex-col items-center justify-center flex-1 min-w-[60px] py-2 px-1 rounded-xl transition-all hover:bg-<?php echo $n['color']; ?>-50 text-gray-400 hover:text-<?php echo $n['color']; ?>-600">
                    <i class="fa-solid <?php echo $n['icon']; ?> text-base mb-0.5 transition-transform group-hover:scale-110"></i>
                    <span class="text-[9px] font-black uppercase tracking-wider"><?php echo $n['label']; ?></span>
                </a>
                <?php endforeach; ?>
            </nav>

            <!-- ═══════════════════ SECTION 1: Clinical History ═════════════════ -->
            <section id="history" class="scroll-mt-24">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center text-lg">
                            <i class="fa-solid fa-notes-medical"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-gray-800 tracking-tight">Clinical Progress Feed</h2>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Visit History & Examination Notes</p>
                        </div>
                    </div>
                    <button @click="openAddHistory()"
                            class="bg-teal-600 hover:bg-teal-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-teal-100 transition-all active:scale-95 flex items-center gap-2">
                        <i class="fa-solid fa-plus-circle"></i> Add Visit
                    </button>
                </div>

                <!-- Timeline -->
                <div class="space-y-6 relative before:absolute before:left-[1.35rem] before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-100 before:rounded-full">
                    <?php if (empty($histories)): ?>
                    <div class="ml-14 bg-gray-50 border-2 border-dashed border-gray-200 rounded-3xl p-12 text-center">
                        <i class="fa-solid fa-folder-open text-4xl text-gray-200 mb-3 block"></i>
                        <h3 class="font-bold text-gray-400 mb-1">No clinical recordings yet.</h3>
                        <p class="text-sm text-gray-400">Every visit you record will appear here in chronological order.</p>
                        <button @click="openAddHistory()"
                                class="mt-4 bg-teal-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-teal-700 transition-all">
                            Record First Visit
                        </button>
                    </div>
                    <?php else: foreach ($histories as $idx => $h): ?>
                    <div class="relative pl-12 group">
                        <div class="absolute left-0 top-3 w-11 h-11 bg-white border-4 border-gray-100 rounded-2xl flex items-center justify-center z-10 shadow-sm group-hover:border-teal-200 transition-colors">
                            <i class="fa-solid fa-calendar-day text-teal-500 text-sm"></i>
                        </div>
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
                            <div class="p-6">
                                <!-- Card Header -->
                                <div class="flex flex-wrap justify-between items-center gap-3 mb-5 pb-4 border-b border-gray-50">
                                    <div class="flex items-center gap-2.5">
                                        <span class="text-[10px] font-black px-3 py-1 bg-teal-900 text-white rounded-full">VISIT #<?php echo count($histories) - $idx; ?></span>
                                        <span class="text-[9px] font-black uppercase px-2.5 py-1 rounded-lg <?php echo $h['record_for'] === 'Spouse' ? 'bg-pink-100 text-pink-700' : 'bg-indigo-100 text-indigo-700'; ?>">
                                            <?php echo esc($h['record_for']); ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-bold text-gray-400 flex items-center gap-1.5">
                                            <i class="fa-regular fa-clock text-teal-400 text-[10px]"></i>
                                            <?php echo date('d M Y, h:i A', strtotime($h['recorded_at'])); ?>
                                        </span>
                                        <!-- Edit Button -->
                                        <button @click="openEditHistory({
                                                    history_id: <?php echo $h['id']; ?>,
                                                    clinical_notes: <?php echo json_encode($h['clinical_notes'] ?? ''); ?>,
                                                    diagnosis: <?php echo json_encode($h['diagnosis'] ?? ''); ?>,
                                                    medication: <?php echo json_encode($h['medication'] ?? ''); ?>,
                                                    advice: <?php echo json_encode($h['advice'] ?? ''); ?>,
                                                    next_visit: <?php echo json_encode($h['next_visit'] ?? ''); ?>,
                                                    record_for: <?php echo json_encode($h['record_for'] ?? 'Patient'); ?>
                                                })"
                                                class="w-8 h-8 rounded-lg bg-gray-50 hover:bg-teal-600 hover:text-white text-gray-400 flex items-center justify-center transition-all text-xs opacity-0 group-hover:opacity-100">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    <div class="lg:col-span-2 space-y-4">
                                        <?php if (!empty($h['clinical_notes'])): ?>
                                        <div>
                                            <h4 class="text-[9px] font-black text-gray-300 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                                <i class="fa-solid fa-align-left text-teal-400"></i> Notes & Complaints
                                            </h4>
                                            <div class="prose prose-sm max-w-none text-gray-600 leading-relaxed"><?php echo $h['clinical_notes']; ?></div>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($h['diagnosis'])): ?>
                                        <div class="bg-indigo-50/60 rounded-xl p-4 border border-indigo-100/50">
                                            <h4 class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-2">Diagnosis / Impression</h4>
                                            <div class="text-sm font-bold text-indigo-900"><?php echo nl2br(esc($h['diagnosis'])); ?></div>
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="space-y-3">
                                        <?php if (!empty($h['medication'])): ?>
                                        <div class="bg-white rounded-xl border border-pink-100 overflow-hidden">
                                            <div class="bg-pink-50 px-4 py-2 border-b border-pink-100 flex items-center gap-2">
                                                <i class="fa-solid fa-pills text-pink-400 text-xs"></i>
                                                <span class="text-[9px] font-black text-pink-600 uppercase">Medications</span>
                                            </div>
                                            <div class="p-4 text-sm text-pink-800 italic leading-relaxed"><?php echo nl2br(esc($h['medication'])); ?></div>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($h['advice'])): ?>
                                        <div class="bg-teal-50/40 rounded-xl p-4 border border-teal-100/40">
                                            <h4 class="text-[9px] font-black text-teal-500 uppercase tracking-widest mb-1.5">Advice</h4>
                                            <p class="text-xs text-gray-600 leading-relaxed"><?php echo nl2br(esc($h['advice'])); ?></p>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($h['next_visit'])): ?>
                                        <div class="bg-teal-900 rounded-xl p-3 text-center shadow-lg shadow-teal-100">
                                            <div class="text-[9px] font-bold text-teal-400 uppercase tracking-widest mb-0.5">Follow-up</div>
                                            <div class="text-white font-black"><?php echo date('d M Y', strtotime($h['next_visit'])); ?></div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </section>

            <!-- ═══════════════════ SECTION 2: Semen Analyses ═══════════════════ -->
            <section id="semen" class="scroll-mt-24">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-sky-100 text-sky-600 rounded-xl flex items-center justify-center text-lg">
                            <i class="fa-solid fa-flask-vial"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-gray-800 tracking-tight">Semen Analysis Reports</h2>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Andrology Lab Results</p>
                        </div>
                    </div>
                    <a href="semen_analyses_add.php?patient_id=<?php echo $patient_id; ?>"
                       class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-sky-100 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-plus-circle"></i> New Analysis
                    </a>
                </div>

                <?php if (empty($semen_reports)): ?>
                <div class="bg-white border border-gray-100 rounded-2xl p-12 text-center shadow-sm">
                    <i class="fa-solid fa-microscope text-4xl text-gray-100 mb-3 block"></i>
                    <p class="text-gray-400 font-bold">No semen analysis reports on record.</p>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    <?php foreach ($semen_reports as $sr): ?>
                    <div class="group bg-white rounded-2xl border border-gray-100 p-6 shadow-sm hover:shadow-xl transition-all duration-300 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-sky-50 rounded-full -mr-12 -mt-12 group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative z-10">
                            <div class="flex justify-between items-start mb-4">
                                <div class="w-10 h-10 bg-sky-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-sky-100">
                                    <i class="fa-solid fa-droplet text-sm"></i>
                                </div>
                                <span class="text-[9px] font-black text-gray-300 uppercase"><?php echo date('M Y', strtotime($sr['collection_time'])); ?></span>
                            </div>
                            <h4 class="text-base font-black text-gray-800 mb-2">Andrology Report</h4>
                            <div class="inline-flex items-center px-3 py-1 rounded-full bg-sky-50 text-sky-700 text-[9px] font-black border border-sky-100 mb-4">
                                <i class="fa-solid fa-robot mr-1 text-[8px]"></i>
                                <?php echo esc($sr['auto_diagnosis'] ?: 'Processing...'); ?>
                            </div>
                            <div class="flex items-center justify-between border-t border-gray-50 pt-4">
                                <span class="text-xs text-gray-400 font-bold"><?php echo date('d M Y', strtotime($sr['collection_time'])); ?></span>
                                <a href="semen_analyses_print.php?id=<?php echo $sr['id']; ?>" target="_blank"
                                   class="w-9 h-9 bg-gray-50 hover:bg-sky-100 text-gray-400 hover:text-sky-600 rounded-full flex items-center justify-center transition-all">
                                    <i class="fa-solid fa-print text-sm"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>

            <!-- ═══════════════════ SECTION 3: Prescriptions ════════════════════ -->
            <section id="rx" class="scroll-mt-24">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center text-lg">
                            <i class="fa-solid fa-prescription"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-gray-800 tracking-tight">Prescription Vault</h2>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Digital Healthcare Records</p>
                        </div>
                    </div>
                    <a href="prescriptions_add.php?patient_id=<?php echo $patient_id; ?>"
                       class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-indigo-100 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-plus-circle"></i> Create Rx
                    </a>
                </div>

                <?php if (empty($prescriptions)): ?>
                <div class="bg-indigo-50/30 border-2 border-dashed border-indigo-100 rounded-3xl p-14 text-center">
                    <i class="fa-solid fa-prescription-bottle-medical text-5xl text-indigo-100 mb-4 block"></i>
                    <p class="text-indigo-900/40 font-bold">No prescriptions issued yet.</p>
                </div>
                <?php else: ?>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 text-[9px] uppercase font-black tracking-widest text-gray-400">
                                <th class="px-6 py-4">Document</th>
                                <th class="px-6 py-4">For</th>
                                <th class="px-6 py-4">Date Issued</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach ($prescriptions as $rx): ?>
                            <tr class="hover:bg-indigo-50/20 group transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-white border border-gray-100 rounded-xl flex items-center justify-center shadow-sm group-hover:border-indigo-200 transition-all">
                                            <i class="fa-solid fa-file-medical text-indigo-500 text-base"></i>
                                        </div>
                                        <div>
                                            <div class="font-black text-gray-800 text-sm">Digital Prescription</div>
                                            <?php if (!empty($rx['diagnosis'])): ?>
                                            <div class="text-[10px] text-gray-400 truncate max-w-[200px]"><?php echo esc(substr(strip_tags($rx['diagnosis']), 0, 40)); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase <?php echo $rx['record_for'] === 'Spouse' ? 'bg-pink-100 text-pink-700' : 'bg-teal-100 text-teal-700'; ?>">
                                        <?php echo esc($rx['record_for']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-700"><?php echo date('d M Y', strtotime($rx['created_at'])); ?></div>
                                    <div class="text-[9px] text-gray-400 uppercase"><?php echo date('h:i A', strtotime($rx['created_at'])); ?></div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if (!empty($rx['scanned_report_path'])): ?>
                                        <a href="../<?php echo esc($rx['scanned_report_path']); ?>" target="_blank"
                                           class="w-8 h-8 flex items-center justify-center rounded-xl bg-gray-100 text-gray-400 hover:bg-teal-600 hover:text-white transition-all text-xs">
                                            <i class="fa-solid fa-paperclip"></i>
                                        </a>
                                        <?php endif; ?>
                                        <a href="prescriptions_print.php?id=<?php echo $rx['id']; ?>" target="_blank"
                                           class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-[10px] font-black shadow-sm hover:bg-indigo-700 transition-all flex items-center gap-1.5">
                                            <i class="fa-solid fa-print"></i> Print
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </section>

            <!-- ═══════════════════ SECTION 4: Ultrasounds ══════════════════════ -->
            <section id="usg" class="scroll-mt-24">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center text-lg">
                            <i class="fa-solid fa-image"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-gray-800 tracking-tight">Ultrasound Gallery</h2>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Diagnostic Imaging & Follicular Monitoring</p>
                        </div>
                    </div>
                    <a href="ultrasounds_add.php?patient_id=<?php echo $patient_id; ?>"
                       class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-emerald-100 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-plus-circle"></i> Add Scan
                    </a>
                </div>

                <?php if (empty($ultrasounds)): ?>
                <div class="bg-white border border-gray-100 rounded-2xl p-12 text-center shadow-sm">
                    <i class="fa-solid fa-camera-retro text-4xl text-gray-100 mb-3 block"></i>
                    <p class="text-gray-400 font-bold italic">No diagnostic scans recorded yet.</p>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <?php foreach ($ultrasounds as $u): ?>
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-xl transition-all duration-300 group">
                        <div class="relative rounded-xl overflow-hidden mb-4 aspect-video bg-gray-100 flex items-center justify-center text-gray-300">
                            <?php if (!empty($u['scanned_report_path'])): ?>
                                <img src="../<?php echo esc($u['scanned_report_path']); ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <?php else: ?>
                                <i class="fa-solid fa-image-slash text-3xl"></i>
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-900/40 to-transparent"></div>
                            <div class="absolute bottom-3 left-3">
                                <span class="text-[9px] font-black uppercase text-white bg-emerald-600/80 px-2.5 py-1 rounded-lg backdrop-blur-sm">
                                    <?php echo esc($u['record_for'] ?? 'Patient'); ?> Scan
                                </span>
                            </div>
                        </div>
                        <h4 class="font-black text-gray-800 text-sm mb-1 truncate"><?php echo esc($u['report_title'] ?? 'Ultrasound Report'); ?></h4>
                        <div class="flex items-center justify-between text-xs font-bold text-gray-400">
                            <span><?php echo date('d M Y', strtotime($u['created_at'])); ?></span>
                            <a href="ultrasounds_print.php?id=<?php echo $u['id']; ?>" target="_blank"
                               class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">
                                <i class="fa-solid fa-print text-xs"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>

            <!-- ═══════════════════ SECTION 5: Lab Results ══════════════════════ -->
            <section id="labs" class="scroll-mt-24">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center text-lg">
                            <i class="fa-solid fa-vials"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-gray-800 tracking-tight">Lab Investigation Hub</h2>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Medical Test Results</p>
                        </div>
                    </div>
                    <a href="lab_results_add.php?patient_id=<?php echo $patient_id; ?>"
                       class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-amber-100 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-plus-circle"></i> Post Result
                    </a>
                </div>

                <?php if (empty($lab_results)): ?>
                <div class="bg-white border border-gray-100 rounded-2xl p-12 text-center text-gray-300 shadow-sm">
                    <i class="fa-solid fa-vial-circle-check text-4xl mb-3 block"></i>
                    <p class="font-bold text-gray-400">No lab results posted yet.</p>
                </div>
                <?php else: ?>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50/80 text-[9px] uppercase font-black tracking-widest text-gray-400 border-b border-gray-100">
                                    <th class="px-6 py-4">Test</th>
                                    <th class="px-6 py-4">For</th>
                                    <th class="px-6 py-4 text-center">Result</th>
                                    <th class="px-6 py-4">Reference</th>
                                    <th class="px-6 py-4 text-right">Report</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php foreach ($lab_results as $lr): ?>
                                <tr class="hover:bg-amber-50/20 group transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-black text-gray-800 text-sm"><?php echo esc($lr['test_name']); ?></div>
                                        <div class="text-[9px] text-gray-400 font-bold flex items-center gap-1.5 mt-0.5">
                                            <i class="fa-solid fa-calendar text-[8px]"></i> <?php echo date('d M Y', strtotime($lr['test_date'])); ?>
                                            <?php if (!empty($lr['lab_name'])): ?>
                                            <span class="mx-1">•</span>
                                            <i class="fa-solid fa-flask text-[8px]"></i> <?php echo esc($lr['lab_name']); ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase <?php echo $lr['test_for'] === 'Spouse' ? 'bg-pink-100 text-pink-700' : 'bg-teal-100 text-teal-700'; ?>">
                                            <?php echo esc($lr['test_for']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if ($lr['status'] === 'Pending'): ?>
                                        <span class="inline-flex items-center gap-1.5 text-amber-600 italic font-bold text-xs">
                                            <i class="fa-solid fa-clock-rotate-left animate-pulse text-[10px]"></i> Pending
                                        </span>
                                        <?php else: ?>
                                        <div class="flex flex-col items-center">
                                            <span class="text-lg font-black text-gray-900"><?php echo esc($lr['result_value']); ?></span>
                                            <span class="text-[9px] font-bold text-gray-400 uppercase"><?php echo esc($lr['unit']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-[10px] font-mono text-gray-500 bg-gray-50 p-2 rounded-lg max-w-[140px] truncate">
                                            <?php
                                            $targetGender = ($lr['test_for'] === 'Patient')
                                                ? ($patient['gender'] ?? 'Male')
                                                : ($patient['gender'] === 'Male' ? 'Female' : 'Male');
                                            echo esc($targetGender === 'Male' ? $lr['reference_range_male'] : $lr['reference_range_female']);
                                            ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <?php if (!empty($lr['scanned_report_path'])): ?>
                                        <a href="../<?php echo esc($lr['scanned_report_path']); ?>" target="_blank"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-700 text-[9px] font-black rounded-xl hover:bg-indigo-600 hover:text-white transition-all">
                                            <i class="fa-solid fa-file-pdf"></i> VIEW
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </section>

            <!-- ═══════════════════ SECTION 6: Procedures ═══════════════════════ -->
            <section id="procedures" class="scroll-mt-24 pb-10">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center text-lg">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-gray-800 tracking-tight">Procedure Tracker</h2>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Recommended Treatments & Billing</p>
                        </div>
                    </div>
                    <a href="procedures_add.php?patient_id=<?php echo $patient_id; ?>"
                       class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-rose-100 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-plus-circle"></i> Log Procedure
                    </a>
                </div>

                <?php if (empty($advised_procedures)): ?>
                <div class="bg-white border border-gray-100 rounded-2xl p-12 text-center text-gray-400 shadow-sm">
                    <i class="fa-solid fa-notes-medical text-4xl mb-3 block text-gray-100"></i>
                    <p class="font-bold">No procedures documented yet.</p>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <?php foreach ($advised_procedures as $ap):
                        $statusMeta = match($ap['status']) {
                            'Advised'     => ['bg' => 'amber',   'icon' => 'fa-clock'],
                            'In Progress' => ['bg' => 'sky',     'icon' => 'fa-spinner fa-spin'],
                            'Completed'   => ['bg' => 'emerald', 'icon' => 'fa-check-double'],
                            default       => ['bg' => 'gray',    'icon' => 'fa-folder'],
                        };
                    ?>
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm hover:shadow-xl transition-all group flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[9px] font-black uppercase bg-<?php echo $statusMeta['bg']; ?>-50 text-<?php echo $statusMeta['bg']; ?>-700 border border-<?php echo $statusMeta['bg']; ?>-100">
                                    <i class="fa-solid <?php echo $statusMeta['icon']; ?> text-[8px]"></i> <?php echo esc($ap['status']); ?>
                                </span>
                                <span class="text-[9px] font-black uppercase text-gray-300"><?php echo date('d M Y', strtotime($ap['date_advised'])); ?></span>
                            </div>
                            <h4 class="text-lg font-black text-gray-800 mb-1"><?php echo esc($ap['procedure_name']); ?></h4>
                            <div class="text-[10px] font-bold text-gray-400 uppercase mb-3">For: <?php echo esc($ap['record_for'] ?? 'Patient'); ?></div>
                            <?php if (!empty($ap['notes'])): ?>
                            <p class="text-xs text-gray-500 bg-gray-50 p-3 rounded-xl italic">"<?php echo esc($ap['notes']); ?>"</p>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-50 pt-4 mt-4">
                            <div>
                                <span class="text-[9px] font-black text-gray-400 uppercase block">Payments Received</span>
                                <span class="text-lg font-black <?php echo $ap['total_paid'] > 0 ? 'text-emerald-600' : 'text-gray-300'; ?>">
                                    <?php echo $ap['total_paid'] > 0 ? 'Rs. ' . number_format($ap['total_paid']) : 'Rs. 0'; ?>
                                </span>
                            </div>
                            <a href="receipts_add.php?patient_id=<?php echo $patient_id; ?>&procedure_id=<?php echo $ap['id']; ?>"
                               class="bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white px-5 py-2 rounded-xl text-xs font-black transition-all border border-emerald-100 flex items-center gap-1.5">
                                <i class="fa-solid fa-file-invoice-dollar"></i> Bill
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>

        </div><!-- end right column -->
    </div><!-- end two-column layout -->

    <!-- ═══════════════════════════════ HISTORY MODAL ══════════════════════════ -->
    <div x-show="showHistoryModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="showHistoryModal = false"
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 sm:p-6"
         style="display: none;">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showHistoryModal = false"></div>

        <!-- Panel -->
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto z-10"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-8 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100">

            <!-- Modal Header -->
            <div class="sticky top-0 bg-white border-b border-gray-100 px-8 py-5 flex items-center justify-between rounded-t-3xl z-10">
                <div>
                    <h3 class="text-xl font-black text-gray-800" x-text="editHistory ? 'Edit Visit Record' : 'Add New Visit Record'"></h3>
                    <p class="text-xs text-gray-400 font-bold mt-0.5"><?php echo esc($patient['first_name'] . ' ' . $patient['last_name']); ?> — <?php echo esc($patient['mr_number']); ?></p>
                </div>
                <button @click="showHistoryModal = false"
                        class="w-9 h-9 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-times text-sm"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form method="POST" class="p-8 space-y-6">
                <input type="hidden" name="history_id" :value="editHistory ? editHistory.history_id : ''">

                <!-- For -->
                <div>
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-3">This record is for</label>
                    <div class="flex gap-4">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="record_for" value="Patient" class="peer sr-only"
                                   :checked="!editHistory || editHistory.record_for === 'Patient'">
                            <div class="bg-gray-50 border-2 border-transparent p-4 rounded-2xl text-center transition-all peer-checked:bg-teal-50 peer-checked:border-teal-500">
                                <i class="fa-solid fa-user-injured text-lg text-gray-300 mb-1 block transition-colors peer-checked:text-teal-600"></i>
                                <span class="text-xs font-black text-gray-400 peer-checked:text-teal-700 uppercase">Patient</span>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="record_for" value="Spouse" class="peer sr-only"
                                   :checked="editHistory && editHistory.record_for === 'Spouse'">
                            <div class="bg-gray-50 border-2 border-transparent p-4 rounded-2xl text-center transition-all peer-checked:bg-pink-50 peer-checked:border-pink-500">
                                <i class="fa-solid fa-heart text-lg text-gray-300 mb-1 block"></i>
                                <span class="text-xs font-black text-gray-400 peer-checked:text-pink-700 uppercase">Spouse</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Clinical Notes -->
                <div>
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">
                        <i class="fa-solid fa-comment-medical text-teal-400 mr-1"></i> Presenting Complaints & Notes
                    </label>
                    <textarea name="clinical_notes" rows="4"
                              :value="editHistory ? editHistory.clinical_notes : ''"
                              class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-teal-500 text-sm font-medium resize-none transition-all"
                              placeholder="History of presenting illness, clinical findings..."></textarea>
                </div>

                <!-- Diagnosis -->
                <div>
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">
                        <i class="fa-solid fa-stethoscope text-indigo-400 mr-1"></i> Diagnosis / Clinical Impression
                    </label>
                    <textarea name="diagnosis" rows="3"
                              :value="editHistory ? editHistory.diagnosis : ''"
                              class="w-full px-5 py-4 bg-indigo-50/40 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 text-sm font-medium resize-none"
                              placeholder="ICD-10 code or clinical impression..."></textarea>
                </div>

                <!-- Medication -->
                <div>
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">
                        <i class="fa-solid fa-pills text-pink-400 mr-1"></i> Medications Prescribed (brief note)
                    </label>
                    <textarea name="medication" rows="2"
                              :value="editHistory ? editHistory.medication : ''"
                              class="w-full px-5 py-4 bg-pink-50/30 border-none rounded-2xl focus:ring-2 focus:ring-pink-500 text-sm font-medium resize-none"
                              placeholder="e.g. Tablet Folic Acid 5mg OD × 3 months..."></textarea>
                </div>

                <!-- Advice + Next Visit -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">
                            <i class="fa-solid fa-lightbulb text-teal-400 mr-1"></i> Lifestyle Advice
                        </label>
                        <textarea name="advice" rows="3"
                                  :value="editHistory ? editHistory.advice : ''"
                                  class="w-full px-5 py-4 bg-teal-50/30 border-none rounded-2xl focus:ring-2 focus:ring-teal-500 text-sm font-medium resize-none"
                                  placeholder="Dietary, lifestyle, activity guidance..."></textarea>
                    </div>
                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">
                            <i class="fa-solid fa-calendar-check text-teal-400 mr-1"></i> Next Follow-up Visit
                        </label>
                        <input type="date" name="next_visit"
                               :value="editHistory ? editHistory.next_visit : ''"
                               class="w-full px-5 py-4 bg-teal-50/40 border-none rounded-2xl focus:ring-2 focus:ring-teal-500 font-bold text-teal-800">
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex gap-3 pt-2 border-t border-gray-50">
                    <button type="button" @click="showHistoryModal = false"
                            class="flex-1 py-3.5 rounded-2xl font-black text-gray-500 bg-gray-100 hover:bg-gray-200 transition-all text-sm">
                        Cancel
                    </button>
                    <button type="submit"
                            :name="editHistory ? 'edit_history' : 'add_history'"
                            :value="editHistory ? '1' : '1'"
                            class="flex-2 flex-grow-[2] py-3.5 rounded-2xl font-black text-white bg-teal-600 hover:bg-teal-700 shadow-xl shadow-teal-100 transition-all text-sm flex items-center justify-center gap-2">
                        <i class="fa-solid" :class="editHistory ? 'fa-floppy-disk' : 'fa-plus-circle'"></i>
                        <span x-text="editHistory ? 'Update Visit Record' : 'Save Visit Record'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div><!-- end main wrapper -->

<style>
    [x-cloak] { display: none !important; }
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    html { scroll-behavior: smooth; }
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
