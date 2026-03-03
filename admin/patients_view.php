<?php
/**
 * PATIENT 360 PROFILE - PREMIUM REDESIGN
 * This file implements a high-end, sectional layout for patient data.
 */
$pageTitle = "Patient 360 Profile";
require_once __DIR__ . '/includes/auth.php';

$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($patient_id <= 0) {
    header("Location: patients.php");
    exit;
}

$error = '';
$success = '';

// Handle Add/Edit Clinical History
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_history']) || isset($_POST['edit_history'])) {
        $notes = $_POST['clinical_notes'] ?? '';
        $diagnosis = $_POST['diagnosis'] ?? '';
        $medication = $_POST['medication'] ?? '';
        $advice = $_POST['advice'] ?? '';
        $next_visit = !empty($_POST['next_visit']) ? $_POST['next_visit'] : null;
        $record_for = $_POST['record_for'] ?? 'Patient';

        if (isset($_POST['add_history'])) {
            $stmt = $conn->prepare("INSERT INTO patient_history (patient_id, clinical_notes, diagnosis, medication, advice, next_visit, record_for) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssss", $patient_id, $notes, $diagnosis, $medication, $advice, $next_visit, $record_for);
        }
        else {
            $history_id = intval($_POST['history_id']);
            $stmt = $conn->prepare("UPDATE patient_history SET clinical_notes=?, diagnosis=?, medication=?, advice=?, next_visit=?, record_for=? WHERE id=? AND patient_id=?");
            $stmt->bind_param("ssssssii", $notes, $diagnosis, $medication, $advice, $next_visit, $record_for, $history_id, $patient_id);
        }

        if ($stmt->execute()) {
            header("Location: patients_view.php?id=" . $patient_id . "&msg=" . (isset($_POST['add_history']) ? 'added' : 'updated'));
            exit;
        }
        else {
            $error = "Process failed: " . $stmt->error;
        }
    }
}

// Data Fetching
try {
    // 1. Patient & Hospital
    $stmt = $conn->prepare("SELECT p.*, h.name as hospital_name FROM patients p LEFT JOIN hospitals h ON p.referring_hospital_id = h.id WHERE p.id = ?");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $patient = $stmt->get_result()->fetch_assoc();
    if (!$patient)
        die("Patient not found.");

    // 2. Feed Components
    $histories = $conn->query("SELECT * FROM patient_history WHERE patient_id = $patient_id ORDER BY recorded_at DESC")->fetch_all(MYSQLI_ASSOC);
    $semen_reports = $conn->query("SELECT * FROM semen_analyses WHERE patient_id = $patient_id ORDER BY collection_time DESC")->fetch_all(MYSQLI_ASSOC);
    $prescriptions = $conn->query("SELECT * FROM prescriptions WHERE patient_id = $patient_id ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
    $ultrasounds = $conn->query("SELECT * FROM patient_ultrasounds WHERE patient_id = $patient_id ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
    $lab_results = $conn->query("SELECT plt.*, ltd.test_name, ltd.unit, ltd.reference_range_male, ltd.reference_range_female FROM patient_lab_results plt JOIN lab_tests_directory ltd ON plt.test_id = ltd.id WHERE plt.patient_id = $patient_id ORDER BY plt.test_date DESC")->fetch_all(MYSQLI_ASSOC);
    $advised_procedures = $conn->query("SELECT ap.*, (SELECT COALESCE(SUM(r.amount),0) FROM receipts r WHERE r.advised_procedure_id = ap.id AND r.status = 'Paid') as total_paid FROM advised_procedures ap WHERE ap.patient_id = $patient_id ORDER BY ap.date_advised DESC")->fetch_all(MYSQLI_ASSOC);

}
catch (Exception $e) {
    $error = "Data fetch error: " . $e->getMessage();
}

include __DIR__ . '/includes/header.php';
?>
<div class="max-w-[1400px] mx-auto px-4 py-8">

    <!-- Premium Hero Section -->
    <div class="relative mb-10 group">
        <div class="absolute inset-0 bg-gradient-to-r from-teal-600 to-indigo-700 rounded-[2rem] shadow-2xl transform transition-transform duration-500 group-hover:scale-[1.01]"></div>
        <div class="relative px-8 py-10 flex flex-col lg:flex-row items-center justify-between gap-8 text-white">
            
            <!-- Patient Identity -->
            <div class="flex items-center gap-8 w-full lg:w-auto">
                <div class="relative">
                    <div class="w-24 h-24 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-4xl shadow-xl border border-white/30">
                        <i class="fa-solid fa-user-injured"></i>
                    </div>
                    <div class="absolute -bottom-2 -right-2 bg-teal-400 w-8 h-8 rounded-full border-4 border-teal-600 flex items-center justify-center text-[10px] font-bold">
                        <i class="fa-solid fa-check"></i>
                    </div>
                </div>
                <div>
                    <h1 class="text-3xl font-black tracking-tight mb-1"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></h1>
                    <div class="flex items-center gap-4 text-teal-50">
                        <span class="font-mono bg-white/10 px-3 py-1 rounded-lg text-sm border border-white/10">ID: <?php echo htmlspecialchars($patient['mr_number']); ?></span>
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-cake-candles text-xs"></i> <?php echo $patient['patient_age'] ?? 'N/A'; ?> Yrs</span>
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-droplet text-xs text-red-300"></i> <?php echo $patient['blood_group'] ?? 'N/A'; ?></span>
                    </div>
                </div>
            </div>

            <!-- Spouse Snapshot -->
            <?php if (!empty($patient['spouse_name'])): ?>
            <div class="bg-white/10 backdrop-blur-md border border-white/20 p-5 rounded-2xl flex items-center gap-4 w-full lg:w-auto min-w-[300px]">
                <div class="w-12 h-12 bg-pink-500/30 rounded-xl flex items-center justify-center text-xl text-pink-200">
                    <i class="fa-solid fa-heart"></i>
                </div>
                <div class="flex-1">
                    <div class="text-[10px] font-bold text-pink-200 uppercase tracking-[0.2em]">Spouse / Partner</div>
                    <div class="font-bold text-lg leading-tight"><?php echo htmlspecialchars($patient['spouse_name']); ?></div>
                    <div class="text-xs text-white/60"><?php echo $patient['spouse_age'] ? $patient['spouse_age'] . ' Yrs' : 'Profile Link Ready'; ?></div>
                </div>
                <a href="patients_edit.php?id=<?php echo $patient['id']; ?>#spouse" class="text-white/40 hover:text-white transition-colors"><i class="fa-solid fa-pen-circle"></i></a>
            </div>
            <?php
endif; ?>

            <!-- Global Actions -->
            <div class="flex gap-3 w-full lg:w-auto">
                <a href="prescriptions_add.php?patient_id=<?php echo $patient['id']; ?>" class="bg-white text-teal-700 hover:bg-teal-50 px-6 py-4 rounded-xl font-bold transition-all shadow-lg flex-1 lg:flex-none text-center flex items-center justify-center gap-2">
                    <i class="fa-solid fa-prescription"></i> New Web Rx
                </a>
                <a href="patients_edit.php?id=<?php echo $patient['id']; ?>" class="bg-teal-500/20 hover:bg-teal-500/30 text-white border border-white/20 px-6 py-4 rounded-xl font-bold transition-all flex-1 lg:flex-none text-center flex items-center justify-center gap-2">
                    <i class="fa-solid fa-user-gear"></i> Manage
                </a>
            </div>
        </div>
    </div>

    <!-- Sticky Smart Navigation -->
    <nav class="sticky top-6 z-50 mb-10 transition-all duration-300" x-data="{ scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 200)">
        <div class="bg-white/80 backdrop-blur-xl border border-gray-200 p-2 rounded-2xl shadow-xl flex items-center justify-between max-w-4xl mx-auto overflow-x-auto scrollbar-hide">
            <a href="#history" class="group flex flex-col items-center justify-center w-20 py-2 rounded-xl transition-all hover:bg-teal-600 hover:text-white text-gray-400">
                <i class="fa-solid fa-notes-medical text-xl mb-1 group-hover:scale-110 transition-transform"></i>
                <span class="text-[9px] font-black uppercase tracking-wider">History</span>
            </a>
            <div class="w-px h-8 bg-gray-100"></div>
            <a href="#semen" class="group flex flex-col items-center justify-center w-20 py-2 rounded-xl transition-all hover:bg-teal-600 hover:text-white text-gray-400">
                <i class="fa-solid fa-flask-vial text-xl mb-1 group-hover:scale-110 transition-transform"></i>
                <span class="text-[9px] font-black uppercase tracking-wider">Semen</span>
            </a>
            <div class="w-px h-8 bg-gray-100"></div>
            <a href="#rx" class="group flex flex-col items-center justify-center w-20 py-2 rounded-xl transition-all hover:bg-teal-600 hover:text-white text-gray-400">
                <i class="fa-solid fa-pills text-xl mb-1 group-hover:scale-110 transition-transform"></i>
                <span class="text-[9px] font-black uppercase tracking-wider">Rx List</span>
            </a>
            <div class="w-px h-8 bg-gray-100"></div>
            <a href="#usg" class="group flex flex-col items-center justify-center w-20 py-2 rounded-xl transition-all hover:bg-teal-600 hover:text-white text-gray-400">
                <i class="fa-solid fa-image text-xl mb-1 group-hover:scale-110 transition-transform"></i>
                <span class="text-[9px] font-black uppercase tracking-wider">Scans</span>
            </a>
            <div class="w-px h-8 bg-gray-100"></div>
            <a href="#labs" class="group flex flex-col items-center justify-center w-20 py-2 rounded-xl transition-all hover:bg-teal-600 hover:text-white text-gray-400">
                <i class="fa-solid fa-vials text-xl mb-1 group-hover:scale-110 transition-transform"></i>
                <span class="text-[9px] font-black uppercase tracking-wider">Labs</span>
            </a>
            <div class="w-px h-8 bg-gray-100"></div>
            <a href="#procedures" class="group flex flex-col items-center justify-center w-20 py-2 rounded-xl transition-all hover:bg-teal-600 hover:text-white text-gray-400">
                <i class="fa-solid fa-clipboard-check text-xl mb-1 group-hover:scale-110 transition-transform"></i>
                <span class="text-[9px] font-black uppercase tracking-wider">Advised</span>
            </a>
        </div>
    </nav>
    <!-- Main Command Center Grid -->
    <div class="grid grid-cols-1 gap-10">

        <!-- History & Clinical Progress -->
        <section id="history" class="scroll-mt-32">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-teal-100 text-teal-600 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                        <i class="fa-solid fa-notes-medical"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-gray-800 tracking-tight">Clinical Progress Feed</h2>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">History & Examination Timeline</p>
                    </div>
                </div>
                <button @click="$dispatch('open-history-form')" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-xl shadow-teal-100 transition-all active:scale-95">
                    <i class="fa-solid fa-plus-circle mr-2"></i> Add Visit Record
                </button>
            </div>

            <!-- The Vertical Timeline -->
            <div class="space-y-8 relative before:absolute before:left-[1.45rem] before:top-2 before:bottom-2 before:w-1 before:bg-gray-100 before:rounded-full">
                <?php if (empty($histories)): ?>
                    <div class="ml-16 bg-gray-50 border-2 border-dashed border-gray-200 rounded-[2rem] p-12 text-center">
                        <i class="fa-solid fa-folder-open text-5xl text-gray-200 mb-4 block"></i>
                        <h3 class="font-bold text-gray-400">No clinical recordings found.</h3>
                        <p class="text-sm text-gray-400">Every visit you record will appear here in chronological order.</p>
                    </div>
                <?php
else:
    foreach ($histories as $idx => $h): ?>
                    <div class="relative pl-14 group">
                        <div class="absolute left-0 top-3 w-12 h-12 bg-white border-4 border-teal-50 rounded-2xl flex items-center justify-center z-10 shadow-sm group-hover:border-teal-400 transition-colors">
                            <i class="fa-solid fa-calendar-day text-teal-600"></i>
                        </div>
                        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all duration-500 overflow-hidden">
                            <div class="p-8">
                                <div class="flex flex-wrap justify-between items-center gap-4 mb-6 border-b border-gray-50 pb-6">
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-black px-4 py-1.5 bg-teal-900 text-white rounded-full shadow-lg shadow-teal-100">VISIT #<?php echo count($histories) - $idx; ?></span>
                                        <span class="text-[10px] font-bold uppercase px-3 py-1 rounded-lg <?php echo $h['record_for'] === 'Spouse' ? 'bg-pink-100 text-pink-700' : 'bg-indigo-100 text-indigo-700'; ?>">
                                            <?php echo htmlspecialchars($h['record_for']); ?>
                                        </span>
                                    </div>
                                    <div class="text-sm font-bold text-gray-400 flex items-center gap-2">
                                        <i class="fa-regular fa-clock text-teal-500"></i> <?php echo date('d M Y, h:i A', strtotime($h['recorded_at'])); ?>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                                    <div class="lg:col-span-8 space-y-6">
                                        <?php if (!empty($h['clinical_notes'])): ?>
                                        <div>
                                            <h4 class="text-[10px] font-black text-gray-300 uppercase tracking-[.2em] mb-4 flex items-center gap-2">
                                                <i class="fa-solid fa-align-left text-teal-400"></i> Notes & History
                                            </h4>
                                            <div class="prose prose-sm max-w-none text-gray-600 leading-[1.8]"><?php echo $h['clinical_notes']; ?></div>
                                        </div>
                                        <?php
        endif; ?>
                                        <?php if (!empty($h['diagnosis'])): ?>
                                        <div class="bg-indigo-50/50 rounded-2xl p-6 border border-indigo-100/50">
                                            <h4 class="text-[10px] font-black text-indigo-400 uppercase tracking-[.2em] mb-3">Diagnosis / Impression</h4>
                                            <div class="prose prose-sm max-w-none text-indigo-900 font-bold"><?php echo $h['diagnosis']; ?></div>
                                        </div>
                                        <?php
        endif; ?>
                                    </div>
                                    <div class="lg:col-span-4 space-y-6">
                                        <?php if (!empty($h['medication'])): ?>
                                        <div class="bg-white rounded-2xl border border-pink-100/50 overflow-hidden shadow-inner">
                                            <div class="bg-pink-50 px-4 py-2 border-b border-pink-100 flex items-center justify-between">
                                                <span class="text-[10px] font-black text-pink-600 uppercase">Medications</span>
                                                <i class="fa-solid fa-pills text-pink-300"></i>
                                            </div>
                                            <div class="p-5 prose prose-sm max-w-none text-pink-800 italic"><?php echo $h['medication']; ?></div>
                                        </div>
                                        <?php
        endif; ?>
                                        <?php if (!empty($h['advice'])): ?>
                                        <div class="bg-teal-50/30 rounded-2xl p-6 border border-teal-100/30">
                                            <h4 class="text-[10px] font-black text-teal-500 uppercase tracking-[.2em] mb-2">Advice</h4>
                                            <p class="text-xs text-gray-600 leading-relaxed"><?php echo nl2br(htmlspecialchars($h['advice'])); ?></p>
                                        </div>
                                        <?php
        endif; ?>
                                        <?php if (!empty($h['next_visit'])): ?>
                                        <div class="bg-teal-900 rounded-2xl p-4 text-center shadow-xl shadow-teal-100">
                                            <div class="text-[9px] font-bold text-teal-400 uppercase tracking-widest mb-1">Follow-up Appointment</div>
                                            <div class="text-white font-black text-lg"><?php echo date('d M Y', strtotime($h['next_visit'])); ?></div>
                                        </div>
                                        <?php
        endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
    endforeach;
endif; ?>
            </div>
        </section>

        <!-- Semen Analysis Section -->
        <section id="semen" class="scroll-mt-32">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-sky-100 text-sky-600 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                        <i class="fa-solid fa-flask-vial"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-gray-800 tracking-tight">Semen Reports Gallery</h2>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Andrology Lab Results</p>
                    </div>
                </div>
                <a href="semen_analyses_add.php?patient_id=<?php echo $patient['id']; ?>" class="bg-gray-100 hover:bg-sky-600 hover:text-white text-gray-600 px-6 py-3 rounded-xl font-bold text-sm transition-all flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle"></i> New Analysis
                </a>
            </div>
            <?php if (empty($semen_reports)): ?>
                <div class="bg-white border border-gray-100 rounded-[2rem] p-12 text-center shadow-sm">
                    <i class="fa-solid fa-microscope text-5xl text-gray-100 mb-4 block"></i>
                    <p class="text-gray-400 font-bold">No semen reports recorded in clinical vault.</p>
                </div>
            <?php
else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php foreach ($semen_reports as $sr): ?>
                        <div class="group bg-white rounded-[2rem] border border-gray-100 p-8 shadow-sm hover:shadow-2xl transition-all relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-sky-50 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-150"></div>
                            <div class="relative z-10">
                                <div class="flex justify-between items-start mb-6">
                                    <div class="w-10 h-10 bg-sky-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-sky-100">
                                        <i class="fa-solid fa-droplet"></i>
                                    </div>
                                    <div class="text-[10px] font-black text-gray-300 uppercase"><?php echo date('M Y', strtotime($sr['collection_time'])); ?></div>
                                </div>
                                <h4 class="text-lg font-black text-gray-800 mb-2">Andrology Report</h4>
                                <div class="inline-flex items-center px-3 py-1 rounded-full bg-sky-50 text-sky-700 text-[10px] font-bold border border-sky-100 mb-6">
                                    <i class="fa-solid fa-robot mr-1 text-[8px]"></i> <?php echo htmlspecialchars($sr['auto_diagnosis'] ?: 'Processing Diagnosis...'); ?>
                                </div>
                                <div class="flex items-center justify-between border-t border-gray-50 pt-6">
                                    <div class="text-xs text-gray-400 font-bold italic"><?php echo date('d M Y', strtotime($sr['collection_time'])); ?></div>
                                    <a href="semen_analyses_print.php?id=<?php echo $sr['id']; ?>" target="_blank" class="w-10 h-10 bg-gray-50 hover:bg-sky-100 text-gray-400 hover:text-sky-600 rounded-full flex items-center justify-center transition-all">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php
    endforeach; ?>
                </div>
            <?php
endif; ?>
        </section>

        <!-- Prescription Vault -->
        <section id="rx" class="scroll-mt-32">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                        <i class="fa-solid fa-prescription"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-gray-800 tracking-tight">Prescription Vault</h2>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Digital Healthcare Records</p>
                    </div>
                </div>
                <a href="prescriptions_add.php?patient_id=<?php echo $patient['id']; ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-2xl font-black text-sm shadow-xl shadow-indigo-100 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle"></i> Create New Web Rx
                </a>
            </div>
            <?php if (empty($prescriptions)): ?>
                <div class="bg-indigo-50/30 border border-indigo-100 border-dashed rounded-[2.5rem] p-16 text-center">
                    <i class="fa-solid fa-prescription-bottle-medical text-6xl text-indigo-100 mb-6 block"></i>
                    <h3 class="text-indigo-900/40 text-lg font-black italic">The Prescription wallet is currently empty.</h3>
                </div>
            <?php
else: ?>
                <div class="bg-white rounded-[2.5rem] border border-gray-100 overflow-hidden shadow-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 text-[10px] uppercase font-black tracking-[.2em] text-gray-400">
                                <th class="p-8">Document Type</th>
                                <th class="p-8">Patient / Spouse</th>
                                <th class="p-8">Issued Date</th>
                                <th class="p-8 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach ($prescriptions as $rx): ?>
                                <tr class="hover:bg-indigo-50/10 group transition-colors">
                                    <td class="p-8">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 bg-white border border-gray-100 rounded-2xl flex items-center justify-center shadow-sm group-hover:border-indigo-200 transition-all">
                                                <i class="fa-solid fa-file-medical text-indigo-600 text-xl"></i>
                                            </div>
                                            <span class="font-black text-gray-800 tracking-tight">Digital Prescription</span>
                                        </div>
                                    </td>
                                    <td class="p-8">
                                        <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase <?php echo $rx['record_for'] === 'Spouse' ? 'bg-pink-100 text-pink-700' : 'bg-teal-100 text-teal-700 shadow-sm shadow-teal-50'; ?>">
                                            <?php echo htmlspecialchars($rx['record_for']); ?>
                                        </span>
                                    </td>
                                    <td class="p-8">
                                        <div class="text-sm font-bold text-gray-600"><?php echo date('d M Y', strtotime($rx['created_at'])); ?></div>
                                        <div class="text-[9px] text-gray-400 uppercase tracking-widest"><?php echo date('h:i A', strtotime($rx['created_at'])); ?></div>
                                    </td>
                                    <td class="p-8 text-right">
                                        <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <?php if (!empty($rx['scanned_report_path'])): ?>
                                                <a href="../<?php echo htmlspecialchars($rx['scanned_report_path']); ?>" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 text-gray-400 hover:bg-teal-600 hover:text-white transition-all shadow-sm">
                                                    <i class="fa-solid fa-paperclip"></i>
                                                </a>
                                            <?php
        endif; ?>
                                            <a href="prescriptions_print.php?id=<?php echo $rx['id']; ?>" target="_blank" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-black shadow-lg shadow-indigo-100 hover:-translate-y-0.5 transition-all">
                                                <i class="fa-solid fa-print mr-2"></i> Print Slip
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php
    endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php
endif; ?>
        </section>
        <!-- Ultrasound Section -->
        <section id="usg" class="scroll-mt-32">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                        <i class="fa-solid fa-image"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-gray-800 tracking-tight">Ultrasound Gallery</h2>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Diagnostic Imaging & Follicular Monitoring</p>
                    </div>
                </div>
                <a href="ultrasounds_add.php?patient_id=<?php echo $patient['id']; ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-xl shadow-emerald-100 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle"></i> Add Scan
                </a>
            </div>
            <?php if (empty($ultrasounds)): ?>
                <div class="bg-white border border-gray-100 rounded-[2.5rem] p-16 text-center shadow-sm">
                    <i class="fa-solid fa-camera-retro text-5xl text-gray-100 mb-4 block"></i>
                    <p class="text-gray-400 font-bold italic">No diagnostic scans recorded yet.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($ultrasounds as $u): ?>
                        <div class="bg-white rounded-[2.5rem] border border-gray-100 p-6 shadow-sm hover:shadow-2xl transition-all duration-500 group">
                            <div class="relative rounded-2xl overflow-hidden mb-6 aspect-video bg-gray-100 flex items-center justify-center text-gray-300">
                                <?php if (!empty($u['scanned_report_path'])): ?>
                                    <img src="../<?php echo htmlspecialchars($u['scanned_report_path']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                <?php else: ?>
                                    <i class="fa-solid fa-image-slash text-4xl"></i>
                                <?php endif; ?>
                                <div class="absolute inset-0 bg-gradient-to-t from-gray-900/40 to-transparent"></div>
                                <div class="absolute bottom-4 left-4">
                                    <span class="text-[10px] font-black uppercase text-white tracking-[0.2em] bg-emerald-600/80 px-3 py-1 rounded-lg backdrop-blur-sm">
                                        <?php echo htmlspecialchars($u['record_for']); ?> Scan
                                    </span>
                                </div>
                            </div>
                            <h4 class="font-black text-gray-800 mb-2 truncate"><?php echo htmlspecialchars($u['report_title']); ?></h4>
                            <div class="flex items-center justify-between text-xs font-bold text-gray-400">
                                <span><?php echo date('d M Y', strtotime($u['created_at'])); ?></span>
                                <div class="flex gap-2">
                                    <a href="ultrasounds_print.php?id=<?php echo $u['id']; ?>" target="_blank" class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Lab Hub Section -->
        <section id="labs" class="scroll-mt-32">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                        <i class="fa-solid fa-vials"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-gray-800 tracking-tight">Lab Investigation Hub</h2>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Medical Test Results & Trends</p>
                    </div>
                </div>
                <a href="lab_results_add.php?patient_id=<?php echo $patient['id']; ?>" class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-xl shadow-amber-100 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle"></i> Post Lab Result
                </a>
            </div>
            <?php if (empty($lab_results)): ?>
                <div class="bg-white border border-gray-100 rounded-[3rem] p-16 text-center text-gray-300">
                    <i class="fa-solid fa-vial-circle-check text-5xl mb-4 block"></i>
                    <p class="font-bold">No lab investigations have been posted to this profile.</p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-[2.5rem] border border-gray-100 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50/50 text-[10px] uppercase font-black tracking-widest text-gray-400 border-b border-gray-100">
                                    <th class="px-8 py-6">Test Detail</th>
                                    <th class="px-8 py-6">Subject</th>
                                    <th class="px-8 py-6 text-center">Result</th>
                                    <th class="px-8 py-6">Reference</th>
                                    <th class="px-8 py-6 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php foreach ($lab_results as $lr): ?>
                                    <tr class="hover:bg-amber-50/10 group transition-colors">
                                        <td class="px-8 py-6">
                                            <div class="font-black text-gray-800 tracking-tight mb-1"><?php echo htmlspecialchars($lr['test_name']); ?></div>
                                            <div class="text-[10px] text-gray-400 font-bold flex items-center gap-2 uppercase tracking-tighter">
                                                <i class="fa-solid fa-calendar text-[8px]"></i> <?php echo date('d M Y', strtotime($lr['test_date'])); ?>
                                                <span class="mx-1">•</span>
                                                <i class="fa-solid fa-microscope text-[8px]"></i> <?php echo htmlspecialchars($lr['lab_name'] ?: 'Internal Lab'); ?>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6">
                                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase <?php echo $lr['test_for'] === 'Spouse' ? 'bg-pink-100 text-pink-700' : 'bg-teal-100 text-teal-700'; ?>">
                                                <?php echo htmlspecialchars($lr['test_for']); ?>
                                            </span>
                                        </td>
                                        <td class="px-8 py-6 text-center">
                                            <?php if ($lr['status'] === 'Pending'): ?>
                                                <span class="inline-flex items-center gap-1.5 text-amber-600 italic font-bold">
                                                    <i class="fa-solid fa-clock-rotate-left animate-pulse"></i> Pending
                                                </span>
                                            <?php else: ?>
                                                <div class="flex flex-col items-center">
                                                    <span class="text-lg font-black text-gray-900"><?php echo htmlspecialchars($lr['result_value']); ?></span>
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase"><?php echo htmlspecialchars($lr['unit']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-8 py-6">
                                            <div class="text-[11px] font-mono text-gray-500 leading-relaxed bg-gray-50 p-2 rounded-lg max-w-[150px] truncate">
                                                <?php 
                                                    $targetGender = ($lr['test_for'] === 'Patient') ? ($patient['gender'] ?? 'Male') : (($patient['gender'] === 'Male') ? 'Female' : 'Male');
                                                    echo ($targetGender === 'Male') ? htmlspecialchars($lr['reference_range_male']) : htmlspecialchars($lr['reference_range_female']); 
                                                ?>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 text-right">
                                            <?php if (!empty($lr['scanned_report_path'])): ?>
                                                <a href="../<?php echo htmlspecialchars($lr['scanned_report_path']); ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 text-xs font-black rounded-xl hover:bg-indigo-600 hover:text-white transition-all">
                                                    <i class="fa-solid fa-paperclip"></i> VIEW
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

        <!-- Procedure Section -->
        <section id="procedures" class="scroll-mt-32">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-gray-800 tracking-tight">Procedure Tracker</h2>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Recommended Treatments & Billing</p>
                    </div>
                </div>
                <a href="procedures_add.php?patient_id=<?php echo $patient['id']; ?>" class="bg-rose-600 hover:bg-rose-700 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-xl shadow-rose-100 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle"></i> Log Recommendation
                </a>
            </div>
            <?php if (empty($advised_procedures)): ?>
                <div class="bg-white border border-gray-100 rounded-[3rem] p-16 text-center text-gray-400">
                    <i class="fa-solid fa-notes-medical text-5xl mb-4 block text-gray-100"></i>
                    <p class="font-bold">No clinical procedures have been documented yet.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <?php foreach ($advised_procedures as $ap): 
                        $statusMeta = match ($ap['status']) {
                            'Advised' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-100', 'icon' => 'fa-clock'],
                            'In Progress' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'border' => 'border-sky-100', 'icon' => 'fa-spinner fa-spin'],
                            'Completed' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-100', 'icon' => 'fa-check-double'],
                            default => ['bg' => 'bg-gray-50', 'text' => 'text-gray-600', 'border' => 'border-gray-100', 'icon' => 'fa-folder']
                        };
                    ?>
                        <div class="bg-white rounded-[2rem] border border-gray-100 p-8 shadow-sm hover:shadow-2xl transition-all group flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-black uppercase <?php echo $statusMeta['bg'] . ' ' . $statusMeta['text'] . ' ' . $statusMeta['border']; ?>">
                                        <i class="fa-solid <?php echo $statusMeta['icon']; ?>"></i> <?php echo htmlspecialchars($ap['status']); ?>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-300"><?php echo date('d M Y', strtotime($ap['date_advised'])); ?></span>
                                </div>
                                <h4 class="text-xl font-black text-gray-800 mb-2"><?php echo htmlspecialchars($ap['procedure_name']); ?></h4>
                                <div class="text-[11px] font-bold text-gray-400 uppercase mb-4">Assigned To: <?php echo htmlspecialchars($ap['record_for'] ?? 'Patient'); ?></div>
                                <?php if (!empty($ap['notes'])): ?>
                                    <p class="text-xs text-gray-500 bg-gray-50 p-4 rounded-xl italic mb-6">"<?php echo htmlspecialchars($ap['notes']); ?>"</p>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center justify-between border-t border-gray-50 pt-6 mt-4">
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black text-gray-400 uppercase">Billing Status</span>
                                    <span class="text-lg font-black <?php echo $ap['total_paid'] > 0 ? 'text-emerald-600' : 'text-gray-300'; ?>">
                                        <?php echo $ap['total_paid'] > 0 ? 'Rs. ' . number_format($ap['total_paid']) : 'No Payments'; ?>
                                    </span>
                                </div>
                                <a href="receipts_add.php?patient_id=<?php echo $patient['id']; ?>&procedure_id=<?php echo $ap['id']; ?>" class="bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white px-6 py-2.5 rounded-xl text-xs font-black transition-all border border-emerald-100 flex items-center gap-2">
                                    <i class="fa-solid fa-file-invoice-dollar"></i> GENERATE BILL
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>
<!-- END CHUNK 4 -->




