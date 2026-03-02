<?php
$pageTitle = "Patient 360 Profile";
require_once __DIR__ . '/includes/auth.php';

$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($patient_id <= 0) {
    header("Location: patients.php");
    exit;
}

$error = '';
$success = '';

// Handle Add Clinical History form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_history'])) {
    $notes = $_POST['clinical_notes'] ?? '';
    $diagnosis = $_POST['diagnosis'] ?? '';
    $medication = $_POST['medication'] ?? '';
    $advice = $_POST['advice'] ?? '';
    $next_visit = !empty($_POST['next_visit']) ? $_POST['next_visit'] : null;

    $stmt = $conn->prepare("INSERT INTO patient_history (patient_id, clinical_notes, diagnosis, medication, advice, next_visit) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("isssss", $patient_id, $notes, $diagnosis, $medication, $advice, $next_visit);
        if ($stmt->execute()) {
            header("Location: patients_view.php?id=" . $patient_id . "&msg=history_added");
            exit;
        }
        else {
            $error = "Failed to add history: " . $stmt->error;
        }
    }
}

// Handle Edit Clinical History
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_history'])) {
    $history_id = intval($_POST['history_id']);
    $notes = $_POST['clinical_notes'] ?? '';
    $diagnosis = $_POST['diagnosis'] ?? '';
    $medication = $_POST['medication'] ?? '';
    $advice = $_POST['advice'] ?? '';
    $next_visit = !empty($_POST['next_visit']) ? $_POST['next_visit'] : null;

    $stmt = $conn->prepare("UPDATE patient_history SET clinical_notes=?, diagnosis=?, medication=?, advice=?, next_visit=? WHERE id=? AND patient_id=?");
    if ($stmt) {
        $stmt->bind_param("sssssii", $notes, $diagnosis, $medication, $advice, $next_visit, $history_id, $patient_id);
        if ($stmt->execute()) {
            header("Location: patients_view.php?id=" . $patient_id . "&msg=history_updated");
            exit;
        }
    }
}

// Fetch Patient Details
$patient = null;
try {
    $stmt = $conn->prepare("SELECT p.*, h.name as hospital_name FROM patients p LEFT JOIN hospitals h ON p.referring_hospital_id = h.id WHERE p.id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $patient = $res->fetch_assoc();
    }
}
catch (Exception $e) {
}

if (!$patient) {
    die("Patient not found.");
}

// Fetch History
$histories = [];
try {
    $stmt = $conn->prepare("SELECT * FROM patient_history WHERE patient_id = ? ORDER BY recorded_at DESC");
    if ($stmt) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc())
            $histories[] = $row;
    }
}
catch (Exception $e) {
}

// Fetch Semen Analyses
$semen_reports = [];
try {
    $stmt = $conn->prepare("SELECT id, collection_time, auto_diagnosis, qrcode_hash FROM semen_analyses WHERE patient_id = ? ORDER BY collection_time DESC");
    if ($stmt) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc())
            $semen_reports[] = $row;
    }
}
catch (Exception $e) {
}

// Fetch Prescriptions
$prescriptions = [];
try {
    $stmt = $conn->prepare("SELECT id, created_at, scanned_report_path FROM prescriptions WHERE patient_id = ? ORDER BY created_at DESC");
    if ($stmt) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc())
            $prescriptions[] = $row;
    }
}
catch (Exception $e) {
}

// Fetch Ultrasounds
$ultrasounds = [];
try {
    $stmt = $conn->prepare("SELECT id, created_at, report_title, scanned_report_path FROM patient_ultrasounds WHERE patient_id = ? ORDER BY created_at DESC");
    if ($stmt) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc())
            $ultrasounds[] = $row;
    }
}
catch (Exception $e) {
}

// Fetch Lab Results
$lab_results = [];
try {
    $stmt = $conn->prepare("SELECT plt.*, ltd.test_name, ltd.reference_range, ltd.unit FROM patient_lab_results plt JOIN lab_tests_directory ltd ON plt.test_id = ltd.id WHERE plt.patient_id = ? ORDER BY plt.test_date DESC, plt.id DESC");
    if ($stmt) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc())
            $lab_results[] = $row;
    }
}
catch (Exception $e) {
}

// Fetch Advised Procedures
$advised_procedures = [];
try {
    $stmt = $conn->prepare("SELECT ap.*, 
        (SELECT COALESCE(SUM(r.amount),0) FROM receipts r WHERE r.advised_procedure_id = ap.id AND r.status = 'Paid') as total_paid
        FROM advised_procedures ap WHERE ap.patient_id = ? ORDER BY ap.date_advised DESC");
    if ($stmt) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc())
            $advised_procedures[] = $row;
    }
}
catch (Exception $e) {
}

include __DIR__ . '/includes/header.php';
?>

<!-- Alpine component for Tabs -->
<div x-data="{ currentTab: 'history' }" class="flex flex-col lg:flex-row gap-6">

    <!-- Left Column: Patient Demographics -->
    <div class="w-full lg:w-1/3 shrink-0">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-6">
            <div class="bg-teal-900 p-6 text-center relative">
                <div class="w-20 h-20 mx-auto bg-white/20 rounded-full flex items-center justify-center text-3xl text-white mb-3 shadow-inner">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="relative flex flex-col items-center">
                    <h2 class="text-xl font-bold text-white"><?php echo esc($patient['first_name'] . ' ' . $patient['last_name']); ?></h2>
                    <a href="patients_edit.php?id=<?php echo $patient['id']; ?>" class="absolute right-0 top-0 mt-1 mr-1 text-teal-300 hover:text-white transition-colors tooltip" title="Edit Patient"><i class="fa-solid fa-pen"></i></a>
                </div>
                <p class="text-teal-200 text-sm font-mono mt-1">MR: <?php echo esc($patient['mr_number']); ?></p>
            </div>
            
            <div class="p-6">
                <ul class="space-y-3 text-sm">
                    <li class="flex justify-between items-center border-b border-gray-50 pb-2">
                        <span class="text-gray-500 font-medium">Gender</span>
                        <span class="text-gray-800 font-semibold"><?php echo esc($patient['gender']); ?></span>
                    </li>
                    <?php if (!empty($patient['patient_age'])): ?>
                    <li class="flex justify-between items-center border-b border-gray-50 pb-2">
                        <span class="text-gray-500 font-medium">Age</span>
                        <span class="text-gray-800 font-semibold"><?php echo $patient['patient_age']; ?> yrs</span>
                    </li>
                    <?php
endif; ?>
                    <?php if (!empty($patient['blood_group'])): ?>
                    <li class="flex justify-between items-center border-b border-gray-50 pb-2">
                        <span class="text-gray-500 font-medium">Blood</span>
                        <span class="bg-red-50 text-red-700 px-2 py-0.5 rounded font-bold text-xs border border-red-100"><?php echo esc($patient['blood_group']); ?></span>
                    </li>
                    <?php
endif; ?>
                    <li class="flex justify-between items-center border-b border-gray-50 pb-2">
                        <span class="text-gray-500 font-medium">Phone</span>
                        <span class="text-gray-800 font-semibold"><?php echo esc($patient['phone'] ?: 'N/A'); ?></span>
                    </li>
                    <li class="flex justify-between items-center border-b border-gray-50 pb-2">
                        <span class="text-gray-500 font-medium">CNIC</span>
                        <span class="text-gray-800 font-mono text-xs"><?php echo esc($patient['cnic'] ?: 'N/A'); ?></span>
                    </li>
                    <?php if (!empty($patient['email'])): ?>
                    <li class="flex justify-between items-center border-b border-gray-50 pb-2">
                        <span class="text-gray-500 font-medium">Email</span>
                        <span class="text-gray-800 text-xs truncate max-w-[140px]"><?php echo esc($patient['email']); ?></span>
                    </li>
                    <?php
endif; ?>
                    <li class="flex justify-between items-center pb-1">
                        <span class="text-gray-500 font-medium">Hospital</span>
                        <span class="text-gray-800 text-right text-xs"><?php echo esc($patient['hospital_name'] ?: 'Main Clinic'); ?></span>
                    </li>
                </ul>

                <!-- Spouse Card -->
                <?php if (!empty($patient['spouse_name'])): ?>
                <div class="mt-5 pt-4 border-t border-gray-100">
                    <h4 class="text-xs font-bold text-pink-700 uppercase tracking-wider mb-3"><i class="fa-solid fa-heart mr-1"></i> Spouse / Partner</h4>
                    <ul class="space-y-2 text-sm">
                        <li class="flex justify-between items-center">
                            <span class="text-gray-500 font-medium">Name</span>
                            <span class="text-gray-800 font-semibold"><?php echo esc($patient['spouse_name']); ?></span>
                        </li>
                        <?php if (!empty($patient['spouse_gender'])): ?>
                        <li class="flex justify-between items-center">
                            <span class="text-gray-500 font-medium">Gender</span>
                            <span class="text-gray-800"><?php echo esc($patient['spouse_gender']); ?></span>
                        </li>
                        <?php
    endif; ?>
                        <?php if (!empty($patient['spouse_age'])): ?>
                        <li class="flex justify-between items-center">
                            <span class="text-gray-500 font-medium">Age</span>
                            <span class="text-gray-800"><?php echo $patient['spouse_age']; ?> yrs</span>
                        </li>
                        <?php
    endif; ?>
                        <?php if (!empty($patient['spouse_phone'])): ?>
                        <li class="flex justify-between items-center">
                            <span class="text-gray-500 font-medium">Phone</span>
                            <span class="text-gray-800"><?php echo esc($patient['spouse_phone']); ?></span>
                        </li>
                        <?php
    endif; ?>
                        <?php if (!empty($patient['spouse_cnic'])): ?>
                        <li class="flex justify-between items-center">
                            <span class="text-gray-500 font-medium">CNIC</span>
                            <span class="text-gray-800 font-mono text-xs"><?php echo esc($patient['spouse_cnic']); ?></span>
                        </li>
                        <?php
    endif; ?>
                    </ul>
                </div>
                <?php
endif; ?>
                
                <div class="mt-6 pt-6 border-t border-gray-100 grid grid-cols-2 gap-3">
                    <a href="semen_analyses_add.php?patient_id=<?php echo $patient['id']; ?>" class="bg-sky-50 hover:bg-sky-100 text-sky-700 font-medium py-2 rounded-lg text-center text-sm transition-colors cursor-pointer border border-sky-100">
                        <i class="fa-solid fa-flask mb-1 block"></i> Semen Analysis
                    </a>
                    <a href="prescriptions_add.php?patient_id=<?php echo $patient['id']; ?>" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-medium py-2 rounded-lg text-center text-sm transition-colors cursor-pointer border border-indigo-100">
                        <i class="fa-solid fa-prescription mb-1 block"></i> Web Rx
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Tabs & Content -->
    <div class="w-full lg:w-2/3">
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-100 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo esc($error); ?>
            </div>
        <?php
endif; ?>
        <?php if (!empty($success)): ?>
            <div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl mb-6 border border-emerald-100 flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i> <?php echo esc($success); ?>
            </div>
        <?php
endif; ?>

        <!-- Tab Navigation -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 flex overflow-hidden">
            <button @click="currentTab = 'history'" :class="{'bg-teal-50 text-teal-700 font-bold border-b-2 border-teal-600': currentTab === 'history', 'text-gray-500 hover:bg-gray-50': currentTab !== 'history'}" class="flex-1 py-4 text-sm font-medium transition-colors">
                <i class="fa-solid fa-notes-medical mr-1"></i> Clinical History
            </button>
            <button @click="currentTab = 'semen'" :class="{'bg-teal-50 text-teal-700 font-bold border-b-2 border-teal-600': currentTab === 'semen', 'text-gray-500 hover:bg-gray-50': currentTab !== 'semen'}" class="flex-1 py-4 text-sm font-medium transition-colors">
                <i class="fa-solid fa-microscope mr-1"></i> Semen Reports
            </button>
            <button @click="currentTab = 'rx'" :class="{'bg-teal-50 text-teal-700 font-bold border-b-2 border-teal-600': currentTab === 'rx', 'text-gray-500 hover:bg-gray-50': currentTab !== 'rx'}" class="flex-1 py-4 text-sm font-medium transition-colors">
                <i class="fa-solid fa-pills mr-1"></i> Prescriptions
            </button>
            <button @click="currentTab = 'usg'" :class="{'bg-teal-50 text-teal-700 font-bold border-b-2 border-teal-600': currentTab === 'usg', 'text-gray-500 hover:bg-gray-50': currentTab !== 'usg'}" class="flex-1 py-4 text-sm font-medium transition-colors">
                <i class="fa-solid fa-image mr-1"></i> Ultrasounds
            </button>
            <button @click="currentTab = 'labs'" :class="{'bg-teal-50 text-teal-700 font-bold border-b-2 border-teal-600': currentTab === 'labs', 'text-gray-500 hover:bg-gray-50': currentTab !== 'labs'}" class="flex-1 py-4 text-sm font-medium transition-colors border-l border-gray-100">
                <i class="fa-solid fa-vials mr-1"></i> Lab Reports
            </button>
            <button @click="currentTab = 'procedures'" :class="{'bg-teal-50 text-teal-700 font-bold border-b-2 border-teal-600': currentTab === 'procedures', 'text-gray-500 hover:bg-gray-50': currentTab !== 'procedures'}" class="flex-1 py-4 text-sm font-medium transition-colors border-l border-gray-100">
                <i class="fa-solid fa-clipboard-check mr-1"></i> Procedures
            </button>
        </div>

        <!-- Tab 1: History -->
        <div x-show="currentTab === 'history'" x-cloak>
            
            <!-- Quill.js CDN -->
            <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
            <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
            
            <!-- Add History Form -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6" x-data="{ expanded: false }">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center cursor-pointer" @click="expanded = !expanded">
                    <h3 class="font-bold text-gray-800"><i class="fa-solid fa-plus-circle text-teal-600 mr-2"></i> Add Clinical Visit Record</h3>
                    <i class="fa-solid fa-chevron-down text-gray-400 transition-transform" :class="expanded ? 'rotate-180' : ''"></i>
                </div>
                <div x-show="expanded" x-collapse>
                    <div class="p-6">
                        <form method="POST" onsubmit="document.getElementById('hidden_notes').value = quillAdd.root.innerHTML; document.getElementById('hidden_diagnosis').value = quillDiagnosis.root.innerHTML; document.getElementById('hidden_medication').value = quillMedication.root.innerHTML;">
                            
                            <div class="mb-5">
                                <label class="block text-sm font-bold text-gray-700 mb-2"><i class="fa-solid fa-stethoscope text-teal-600 mr-1"></i> Clinical History & Findings</label>
                                <div id="editor-notes" style="height:180px;"></div>
                                <input type="hidden" name="clinical_notes" id="hidden_notes">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2"><i class="fa-solid fa-clipboard-list text-indigo-600 mr-1"></i> Diagnosis</label>
                                    <div id="editor-diagnosis" style="height:120px;"></div>
                                    <input type="hidden" name="diagnosis" id="hidden_diagnosis">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2"><i class="fa-solid fa-pills text-pink-600 mr-1"></i> Medication Prescribed</label>
                                    <div id="editor-medication" style="height:120px;"></div>
                                    <input type="hidden" name="medication" id="hidden_medication">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2"><i class="fa-solid fa-lightbulb text-amber-600 mr-1"></i> Advice Given</label>
                                    <textarea name="advice" rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-500 text-sm" placeholder="Lifestyle advice, dietary recommendations, follow-up instructions..."></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2"><i class="fa-solid fa-calendar-check text-emerald-600 mr-1"></i> Next Visit Date</label>
                                    <input type="date" name="next_visit" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-500 text-sm">
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" name="add_history" class="bg-teal-700 hover:bg-teal-800 text-white px-8 py-3 rounded-lg text-sm font-bold transition-colors shadow-lg shadow-teal-200 flex items-center gap-2">
                                    <i class="fa-solid fa-save"></i> Save Clinical Record
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- History Timeline -->
            <div class="space-y-4">
                <?php if (empty($histories)): ?>
                    <div class="text-center py-8 text-gray-400 bg-white rounded-2xl border border-gray-100 border-dashed">
                        <i class="fa-solid fa-notes-medical text-3xl mb-2 block text-gray-300"></i>
                        No clinical history records yet. Click "Add Clinical Visit Record" above.
                    </div>
                <?php
else:
    foreach ($histories as $idx => $h): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ editing: false }">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-teal-500 rounded-l-xl"></div>
                        
                        <!-- Header -->
                        <div class="flex justify-between items-center px-5 py-3 bg-gray-50/50 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-teal-700 bg-teal-50 px-3 py-1 rounded-full">
                                    Visit #<?php echo count($histories) - $idx; ?>
                                </span>
                                <span class="text-xs text-gray-500">
                                    <i class="fa-regular fa-clock mr-1"></i><?php echo date('d M Y, h:i A', strtotime($h['recorded_at'])); ?>
                                </span>
                            </div>
                            <button @click="editing = !editing" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1 transition-colors">
                                <i class="fa-solid fa-pen-to-square"></i> <span x-text="editing ? 'Cancel' : 'Edit'"></span>
                            </button>
                        </div>

                        <!-- Read-Only View -->
                        <div x-show="!editing" class="p-5 space-y-4">
                            <?php if (!empty($h['clinical_notes'])): ?>
                            <div>
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Clinical History</div>
                                <div class="text-sm text-gray-700 leading-relaxed prose prose-sm max-w-none"><?php echo $h['clinical_notes']; ?></div>
                            </div>
                            <?php
        endif; ?>
                            
                            <?php if (!empty($h['diagnosis'])): ?>
                            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-3">
                                <div class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider mb-1"><i class="fa-solid fa-clipboard-list mr-1"></i> Diagnosis</div>
                                <div class="text-sm text-indigo-900 prose prose-sm max-w-none"><?php echo $h['diagnosis']; ?></div>
                            </div>
                            <?php
        endif; ?>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <?php if (!empty($h['medication'])): ?>
                                <div class="bg-pink-50 border border-pink-100 rounded-lg p-3">
                                    <div class="text-[10px] font-bold text-pink-600 uppercase tracking-wider mb-1"><i class="fa-solid fa-pills mr-1"></i> Medication</div>
                                    <div class="text-sm text-pink-900 prose prose-sm max-w-none"><?php echo $h['medication']; ?></div>
                                </div>
                                <?php
        endif; ?>
                                
                                <?php if (!empty($h['advice'])): ?>
                                <div class="bg-amber-50 border border-amber-100 rounded-lg p-3">
                                    <div class="text-[10px] font-bold text-amber-600 uppercase tracking-wider mb-1"><i class="fa-solid fa-lightbulb mr-1"></i> Advice</div>
                                    <div class="text-sm text-amber-900 whitespace-pre-wrap"><?php echo esc($h['advice']); ?></div>
                                </div>
                                <?php
        endif; ?>
                            </div>

                            <?php if (!empty($h['next_visit'])): ?>
                            <div class="flex items-center gap-2 text-xs text-emerald-700 bg-emerald-50 border border-emerald-100 px-3 py-2 rounded-lg w-fit">
                                <i class="fa-solid fa-calendar-check"></i>
                                <span class="font-bold">Next Visit:</span> <?php echo date('d M Y', strtotime($h['next_visit'])); ?>
                            </div>
                            <?php
        endif; ?>
                        </div>

                        <!-- Edit Form -->
                        <div x-show="editing" x-cloak class="p-5">
                            <form method="POST">
                                <input type="hidden" name="history_id" value="<?php echo $h['id']; ?>">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 mb-1">Clinical History</label>
                                        <textarea name="clinical_notes" rows="4" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-teal-500"><?php echo esc(strip_tags($h['clinical_notes'])); ?></textarea>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 mb-1">Diagnosis</label>
                                            <textarea name="diagnosis" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"><?php echo esc(strip_tags($h['diagnosis'])); ?></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 mb-1">Medication</label>
                                            <textarea name="medication" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-pink-500"><?php echo esc(strip_tags($h['medication'])); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 mb-1">Advice</label>
                                            <textarea name="advice" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-amber-500"><?php echo esc($h['advice'] ?? ''); ?></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 mb-1">Next Visit</label>
                                            <input type="date" name="next_visit" value="<?php echo $h['next_visit'] ?? ''; ?>" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                        </div>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" name="edit_history" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg text-sm font-bold transition-colors">Update Record</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php
    endforeach;
endif; ?>
            </div>

            <!-- Quill Init Script -->
            <script>
            var quillAdd, quillDiagnosis, quillMedication;
            document.addEventListener('DOMContentLoaded', function() {
                var toolbarOptions = [['bold', 'italic', 'underline'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['clean']];
                if (document.getElementById('editor-notes')) {
                    quillAdd = new Quill('#editor-notes', { theme: 'snow', modules: { toolbar: toolbarOptions }, placeholder: 'Type or dictate clinical history, presenting complaints, examination findings...' });
                }
                if (document.getElementById('editor-diagnosis')) {
                    quillDiagnosis = new Quill('#editor-diagnosis', { theme: 'snow', modules: { toolbar: toolbarOptions }, placeholder: 'Primary and secondary diagnoses...' });
                }
                if (document.getElementById('editor-medication')) {
                    quillMedication = new Quill('#editor-medication', { theme: 'snow', modules: { toolbar: toolbarOptions }, placeholder: 'Drug name, dosage, frequency, duration...' });
                }
            });
            </script>

        </div>

        <!-- Tab 2: Semen Reports -->
        <div x-show="currentTab === 'semen'" x-cloak>
            <div class="space-y-4">
                <?php if (empty($semen_reports)): ?>
                    <div class="text-center py-8 text-gray-400 bg-white rounded-2xl border border-gray-100 border-dashed">No semen analyses recorded yet.</div>
                <?php
else:
    foreach ($semen_reports as $sr): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex justify-between items-center hover:border-sky-200 transition-colors">
                        <div>
                            <div class="font-bold text-gray-800 mb-1">Analysis Report</div>
                            <div class="text-xs text-gray-500 mb-2">Collected: <?php echo date('d M Y', strtotime($sr['collection_time'])); ?></div>
                            <span class="text-xs font-mono bg-sky-50 text-sky-700 px-2 py-1 rounded border border-sky-100">
                                <?php echo esc($sr['auto_diagnosis'] ?: 'Diagnosis Pending'); ?>
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <a href="semen_analyses_print.php?id=<?php echo $sr['id']; ?>" target="_blank" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm transition-colors">
                                <i class="fa-solid fa-print"></i>
                            </a>
                        </div>
                    </div>
                <?php
    endforeach;
endif; ?>
            </div>
        </div>

        <!-- Tab 3: Prescriptions -->
        <div x-show="currentTab === 'rx'" x-cloak>
            <div class="space-y-4">
                <?php if (empty($prescriptions)): ?>
                    <div class="text-center py-8 text-gray-400 bg-white rounded-2xl border border-gray-100 border-dashed">No prescriptions written yet.</div>
                <?php
else:
    foreach ($prescriptions as $rx): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex justify-between items-center hover:border-indigo-200 transition-colors">
                        <div>
                            <div class="font-bold text-gray-800 mb-1">Digital Prescription</div>
                            <div class="text-xs text-gray-500">Issued: <?php echo date('d M Y, h:i A', strtotime($rx['created_at'])); ?></div>
                        </div>
                        <div class="flex gap-2">
                            <?php if (!empty($rx['scanned_report_path'])): ?>
                                <a href="../<?php echo htmlspecialchars($rx['scanned_report_path']); ?>" target="_blank" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm transition-colors border border-gray-200" title="Original Scan">
                                    <i class="fa-solid fa-paperclip"></i>
                                </a>
                            <?php
        endif; ?>
                            <a href="prescriptions_print.php?id=<?php echo $rx['id']; ?>" target="_blank" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3 py-2 rounded-lg text-sm transition-colors">
                                <i class="fa-solid fa-print"></i>
                            </a>
                        </div>
                    </div>
                <?php
    endforeach;
endif; ?>
            </div>
        </div>

        <!-- Tab 4: Ultrasounds -->
        <div x-show="currentTab === 'usg'" x-cloak>
            <div class="space-y-4">
                <?php if (empty($ultrasounds)): ?>
                    <div class="text-center py-8 text-gray-400 bg-white rounded-2xl border border-gray-100 border-dashed">No ultrasound reports recorded yet.</div>
                <?php
else:
    foreach ($ultrasounds as $u): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex justify-between items-center hover:border-sky-200 transition-colors">
                        <div>
                            <div class="font-bold text-gray-800 mb-1"><?php echo esc($u['report_title']); ?></div>
                            <div class="text-xs text-gray-500">Recorded: <?php echo date('d M Y, h:i A', strtotime($u['created_at'])); ?></div>
                        </div>
                        <div class="flex gap-2">
                            <?php if (!empty($u['scanned_report_path'])): ?>
                                <a href="../<?php echo htmlspecialchars($u['scanned_report_path']); ?>" target="_blank" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm transition-colors border border-gray-200" title="Original Scan">
                                    <i class="fa-solid fa-paperclip"></i>
                                </a>
                            <?php
        endif; ?>
                            <a href="ultrasounds_print.php?id=<?php echo $u['id']; ?>" target="_blank" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm transition-colors">
                                <i class="fa-solid fa-print"></i>
                            </a>
                        </div>
                        </div>
                    <?php
    endforeach;
endif; ?>
                </div>
            </div>

        <!-- Tab 5: Lab Reports -->
        <div x-show="currentTab === 'labs'" x-cloak>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800"><i class="fa-solid fa-vials text-teal-600 mr-2"></i> External / Internal Lab Results</h3>
                    <a href="lab_results_add.php?patient_id=<?php echo $patient['id']; ?>" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fa-solid fa-plus mr-1"></i> Add Lab Result
                    </a>
                </div>
                
                <div class="p-0">
                    <?php if (empty($lab_results)): ?>
                        <div class="p-8 text-center text-gray-400">
                            <i class="fa-solid fa-flask mb-2 text-3xl block"></i>
                            No lab test results have been recorded for this patient yet.
                        </div>
                    <?php
else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                        <th class="p-4 font-medium border-b border-gray-100">Test name</th>
                                        <th class="p-4 font-medium border-b border-gray-100">Result value</th>
                                        <th class="p-4 font-medium border-b border-gray-100">Reference / Normal</th>
                                        <th class="p-4 font-medium border-b border-gray-100">Date & Source</th>
                                        <th class="p-4 font-medium border-b border-gray-100 text-right"><i class="fa-solid fa-paperclip"></i></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 text-sm">
                                    <?php foreach ($lab_results as $lr): ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="p-4 font-semibold text-gray-900"><?php echo htmlspecialchars($lr['test_name']); ?></td>
                                        <td class="p-4">
                                            <span class="font-bold text-lg text-gray-900"><?php echo htmlspecialchars($lr['result_value']); ?></span>
                                            <span class="text-xs text-gray-500 font-mono ml-1"><?php echo htmlspecialchars($lr['unit']); ?></span>
                                        </td>
                                        <td class="p-4 text-gray-600 text-xs leading-relaxed"><?php echo $lr['reference_range'] ? nl2br(htmlspecialchars($lr['reference_range'])) : '-'; ?></td>
                                        <td class="p-4">
                                            <div class="font-medium text-gray-800"><?php echo date('d M Y', strtotime($lr['test_date'])); ?></div>
                                            <div class="text-[10px] text-gray-500 uppercase"><?php echo htmlspecialchars($lr['lab_name'] ?: 'In-House'); ?> <?php echo $lr['lab_city'] ? '- ' . htmlspecialchars($lr['lab_city']) : ''; ?></div>
                                        </td>
                                        <td class="p-4 text-right">
                                            <?php if (!empty($lr['scanned_report_path'])): ?>
                                                <a href="../<?php echo htmlspecialchars($lr['scanned_report_path']); ?>" target="_blank" class="bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-3 py-1.5 rounded-md text-xs font-bold transition-colors border border-indigo-100">
                                                    View PDF
                                                </a>
                                            <?php
        else: ?>
                                                <span class="text-gray-300">-</span>
                                            <?php
        endif; ?>
                                        </td>
                                    </tr>
                                    <?php
    endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php
endif; ?>
                </div>
            </div>
        </div>

        <!-- Tab 6: Advised Procedures -->
        <div x-show="currentTab === 'procedures'" x-cloak>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800"><i class="fa-solid fa-clipboard-check text-indigo-600 mr-2"></i> Advised Treatments</h3>
                    <a href="procedures_add.php?patient_id=<?php echo $patient['id']; ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fa-solid fa-plus mr-1"></i> Advise Treatment
                    </a>
                </div>
                
                <div class="p-0">
                    <?php if (empty($advised_procedures)): ?>
                        <div class="p-8 text-center text-gray-400">
                            <i class="fa-solid fa-clipboard mb-2 text-3xl block"></i>
                            No treatments have been advised for this patient yet.
                        </div>
                    <?php
else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                        <th class="p-4 font-medium border-b border-gray-100">Procedure</th>
                                        <th class="p-4 font-medium border-b border-gray-100">Status</th>
                                        <th class="p-4 font-medium border-b border-gray-100">Date</th>
                                        <th class="p-4 font-medium border-b border-gray-100">Paid</th>
                                        <th class="p-4 font-medium border-b border-gray-100 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 text-sm">
                                    <?php foreach ($advised_procedures as $ap):
        $stColor = match ($ap['status']) {
                'Advised' => 'bg-amber-50 text-amber-700 border-amber-200',
                'In Progress' => 'bg-sky-50 text-sky-700 border-sky-200',
                'Completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'Cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
                default => 'bg-gray-50 text-gray-700 border-gray-200',
            };
?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="p-4">
                                            <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($ap['procedure_name']); ?></div>
                                            <?php if (!empty($ap['notes'])): ?>
                                                <div class="text-xs text-gray-500 mt-1 truncate max-w-xs"><?php echo htmlspecialchars($ap['notes']); ?></div>
                                            <?php
        endif; ?>
                                        </td>
                                        <td class="p-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border <?php echo $stColor; ?>">
                                                <?php echo htmlspecialchars($ap['status']); ?>
                                            </span>
                                        </td>
                                        <td class="p-4 text-gray-500 text-xs"><?php echo date('d M Y', strtotime($ap['date_advised'])); ?></td>
                                        <td class="p-4">
                                            <?php if ($ap['total_paid'] > 0): ?>
                                                <span class="font-bold text-emerald-600">Rs. <?php echo number_format($ap['total_paid'], 0); ?></span>
                                            <?php
        else: ?>
                                                <span class="text-gray-300">-</span>
                                            <?php
        endif; ?>
                                        </td>
                                        <td class="p-4 text-right">
                                            <a href="receipts_add.php?patient_id=<?php echo $patient['id']; ?>&procedure_id=<?php echo $ap['id']; ?>" class="text-emerald-600 hover:text-emerald-800 text-xs font-bold bg-emerald-50 px-3 py-1.5 rounded border border-emerald-100">
                                                <i class="fa-solid fa-file-invoice-dollar mr-1"></i> Bill
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
    endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php
endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
