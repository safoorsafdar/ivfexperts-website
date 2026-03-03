<?php
/**
 * CLINICAL PRESCRIPTION WIZARD - PREMIUM REDESIGN
 * This file implements a modern, step-by-step prescription builder.
 */
$pageTitle = "New Prescription Wizard";
require_once __DIR__ . '/includes/auth.php';

$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;
if ($patient_id <= 0) {
    header("Location: patients.php");
    exit;
}

// Fetch Patient Data
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
if (!$patient)
    die("Patient not found.");

// Handling POST Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_prescription'])) {
    $record_for     = $_POST['record_for']     ?? 'Patient';
    $clinical_notes = $_POST['clinical_notes'] ?? '';
    $diagnosis      = $_POST['diagnosis']      ?? '';
    $icd10_codes    = $_POST['icd10_data']     ?? '[]';
    $general_advice = $_POST['general_advice'] ?? '';
    $next_visit     = !empty($_POST['next_visit']) ? $_POST['next_visit'] : null;
    $medications_json = $_POST['medications_data'] ?? '[]';
    $lab_tests_json   = $_POST['lab_tests_data']   ?? '[]';
    $qrcode_hash    = bin2hex(random_bytes(16));

    // Rebuild diagnosis text from ICD-10 selections if present
    $icd_arr = json_decode($icd10_codes, true);
    if (is_array($icd_arr) && count($icd_arr) > 0 && empty(trim($diagnosis))) {
        $diagnosis = implode("\n", array_map(fn($d) => $d['description'] . ' (' . $d['icd10_code'] . ')', $icd_arr));
    }

    try {
        // Insert Prescription (icd10_codes stored in dedicated JSON column if it exists)
        $hasIcd10Col = $conn->query("SHOW COLUMNS FROM prescriptions LIKE 'icd10_codes'")->num_rows > 0;
        if ($hasIcd10Col) {
            $stmt = $conn->prepare("INSERT INTO prescriptions (patient_id, record_for, clinical_notes, diagnosis, icd10_codes, general_advice, next_visit, qrcode_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssss", $patient_id, $record_for, $clinical_notes, $diagnosis, $icd10_codes, $general_advice, $next_visit, $qrcode_hash);
        } else {
            $stmt = $conn->prepare("INSERT INTO prescriptions (patient_id, record_for, clinical_notes, diagnosis, general_advice, next_visit, qrcode_hash) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssss", $patient_id, $record_for, $clinical_notes, $diagnosis, $general_advice, $next_visit, $qrcode_hash);
        }

        if ($stmt->execute()) {
            $rx_id = $stmt->insert_id;

            // Save Medications
            $meds = json_decode($medications_json, true);
            if (is_array($meds)) {
                $m_stmt = $conn->prepare("INSERT INTO prescription_items (prescription_id, medicine_name, dosage, frequency, duration, instructions) VALUES (?, ?, ?, ?, ?, ?)");
                foreach ($meds as $m) {
                    if (empty($m['medicine_name'])) continue;
                    $m_stmt->bind_param("isssss", $rx_id, $m['medicine_name'], $m['dosage'], $m['frequency'], $m['duration'], $m['instructions']);
                    $m_stmt->execute();
                }
            }

            // Save Lab Advising
            $labs = json_decode($lab_tests_json, true);
            if (is_array($labs)) {
                $l_stmt = $conn->prepare("INSERT INTO advised_lab_tests (prescription_id, patient_id, test_id, record_for) VALUES (?, ?, ?, ?)");
                foreach ($labs as $l) {
                    if (empty($l['id'])) continue;
                    $l_stmt->bind_param("iiis", $rx_id, $patient_id, $l['id'], $l['for']);
                    $l_stmt->execute();
                }
            }

            header("Location: patients_view.php?id=$patient_id&tab=rx&msg=rx_saved");
            exit;
        }
    } catch (Exception $e) {
        $save_error = "Could not save prescription: " . $e->getMessage();
    }
}

include __DIR__ . '/includes/header.php';
?>

<?php if (!empty($save_error)): ?>
<div class="max-w-7xl mx-auto px-4 mb-4">
    <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-3 rounded-xl text-sm font-bold flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation text-red-500"></i> <?php echo htmlspecialchars($save_error); ?>
    </div>
</div>
<?php endif; ?>

<div class="max-w-7xl mx-auto px-4 py-8" x-data="prescriptionWizard()">
    
    <!-- Header Area -->
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <nav class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4">
                <a href="patients_view.php?id=<?php echo $patient_id; ?>" class="hover:text-teal-600 transition-colors">Patient Record</a>
                <i class="fa-solid fa-chevron-right text-[8px]"></i>
                <span class="text-teal-600">Prescription Wizard</span>
            </nav>
            <h1 class="text-4xl font-black text-gray-900 tracking-tight">Generate Clinical Rx</h1>
            <p class="text-gray-400 font-bold mt-1 uppercase text-xs tracking-widest">Digital Healthcare Module • Version 2.0</p>
        </div>
        
        <div class="flex items-center gap-4 bg-white p-4 rounded-3xl shadow-sm border border-gray-100">
            <div class="w-12 h-12 bg-teal-50 rounded-2xl flex items-center justify-center text-teal-600 text-xl">
                <i class="fa-solid fa-user-doctor"></i>
            </div>
            <div>
                <div class="text-[10px] font-black uppercase text-gray-300">Active Patient</div>
                <div class="font-bold text-gray-800"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></div>
            </div>
        </div>
    </div>

    <!-- Wizard Progress Bar -->
    <div class="mb-12 relative flex justify-between">
        <div class="absolute top-1/2 left-0 w-full h-1 bg-gray-100 -translate-y-1/2 z-0"></div>
        <div class="absolute top-1/2 left-0 h-1 bg-teal-600 -translate-y-1/2 z-0 transition-all duration-700" :style="`width: ${(step-1)*50}%`"></div >

        <!-- Step 1 Trigger -->
        <button @click="step = 1" class="relative z-10 flex flex-col items-center gap-3">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-500 font-black text-lg" :class="step >= 1 ? 'bg-teal-600 text-white shadow-xl shadow-teal-100' : 'bg-white border-4 border-gray-50 text-gray-300'">1</div>
            <span class="text-[10px] font-black uppercase tracking-widest" :class="step >= 1 ? 'text-teal-700' : 'text-gray-300'">Assessment</span>
        </button>

        <!-- Step 2 Trigger -->
        <button @click="step = 2" class="relative z-10 flex flex-col items-center gap-3">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-500 font-black text-lg" :class="step >= 2 ? 'bg-teal-600 text-white shadow-xl shadow-teal-100' : 'bg-white border-4 border-gray-50 text-gray-300'">2</div>
            <span class="text-[10px] font-black uppercase tracking-widest" :class="step >= 2 ? 'text-teal-700' : 'text-gray-300'">Medication</span>
        </button>

        <!-- Step 3 Trigger -->
        <button @click="step = 3" class="relative z-10 flex flex-col items-center gap-3">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-500 font-black text-lg" :class="step >= 3 ? 'bg-teal-600 text-white shadow-xl shadow-teal-100' : 'bg-white border-4 border-gray-50 text-gray-300'">3</div>
            <span class="text-[10px] font-black uppercase tracking-widest" :class="step >= 3 ? 'text-teal-700' : 'text-gray-300'">Finalize</span>
        </button>
    </div>

    <form method="POST" id="rxForm" class="space-y-10 pb-20">
        
        <!-- STEP 1: Assessment -->
        <div x-show="step === 1" x-cloak x-transition:enter="duration-500 delay-200" x-transition:enter-start="translate-y-10 opacity-0" class="max-w-4xl mx-auto">
            <div class="bg-white rounded-[3rem] p-10 shadow-2xl shadow-gray-100 border border-gray-50">
                <div class="mb-10 text-center">
                    <h2 class="text-2xl font-black text-gray-800">Step 1: Clinical Assessment</h2>
                    <p class="text-gray-400 text-sm mt-1">Select the person being assessed and document their status.</p>
                </div>

                <div class="mb-12">
                    <label class="block text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-6 text-center">Who is this assessment for?</label>
                    <div class="flex gap-6 max-w-sm mx-auto">
                        <label class="flex-1 cursor-pointer group">
                            <input type="radio" name="record_for" value="Patient" checked class="peer sr-only">
                            <div class="bg-gray-50 border-4 border-transparent p-6 rounded-[2rem] text-center transition-all peer-checked:bg-teal-50 peer-checked:border-teal-600 group-hover:bg-gray-100">
                                <i class="fa-solid fa-user-injured text-2xl text-gray-300 peer-checked:text-teal-600 mb-2 transition-colors"></i>
                                <div class="font-black text-xs text-gray-400 peer-checked:text-teal-900 uppercase">Patient</div>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer group">
                            <input type="radio" name="record_for" value="Spouse" class="peer sr-only">
                            <div class="bg-gray-50 border-4 border-transparent p-6 rounded-[2rem] text-center transition-all peer-checked:bg-pink-50 peer-checked:border-pink-600 group-hover:bg-gray-100">
                                <i class="fa-solid fa-heart text-2xl text-gray-300 peer-checked:text-pink-600 mb-2 transition-colors"></i>
                                <div class="font-black text-xs text-gray-400 peer-checked:text-pink-900 uppercase">Spouse</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="space-y-10">
                    <div class="space-y-4">
                        <label class="flex items-center gap-2 text-[10px] font-black uppercase text-gray-400 tracking-widest">
                            <i class="fa-solid fa-comment-medical text-teal-500"></i> Presenting Complaints & History
                        </label>
                        <div id="notes-editor" class="bg-gray-50 rounded-[2rem] border-none min-h-[150px]"></div>
                        <input type="hidden" name="clinical_notes" id="clinical_notes">
                    </div>

                    <div class="flex flex-col gap-4">
                        <label class="flex items-center gap-2 text-[10px] font-black uppercase text-gray-400 tracking-widest">
                            <i class="fa-solid fa-stethoscope text-indigo-500"></i> Diagnosis — ICD-10 / SNOMED Search
                        </label>

                        <!-- ICD-10 Search Box -->
                        <div class="relative">
                            <div class="flex items-center gap-3 px-6 py-4 bg-indigo-50/60 rounded-2xl">
                                <i class="fa-solid fa-magnifying-glass text-indigo-300"></i>
                                <input
                                    type="text"
                                    x-model="icdSearch"
                                    @input.debounce.300ms="searchICD"
                                    @keydown.escape="icdResults = []"
                                    placeholder="Search by diagnosis name or ICD-10 code (e.g. PCOS, N97, endometriosis)..."
                                    class="flex-1 bg-transparent font-bold text-indigo-900 placeholder-indigo-300 focus:outline-none text-sm"
                                    autocomplete="off"
                                >
                                <span x-show="icdLoading" class="text-indigo-300 text-xs animate-pulse">Searching…</span>
                            </div>

                            <!-- Search Results Dropdown -->
                            <div x-show="icdResults.length > 0" @click.away="icdResults = []"
                                 class="absolute z-30 top-full left-0 w-full bg-white mt-2 rounded-2xl shadow-2xl border border-gray-100 overflow-hidden max-h-72 overflow-y-auto">
                                <template x-for="res in icdResults" :key="res.icd10_code">
                                    <button @click="selectICD(res)" type="button"
                                            class="w-full text-left px-5 py-3.5 hover:bg-indigo-50 flex items-start justify-between gap-4 border-b border-gray-50 last:border-0 transition-colors group">
                                        <div class="flex-1 min-w-0">
                                            <div class="font-bold text-gray-800 text-sm leading-tight" x-text="res.description"></div>
                                            <div class="text-[10px] text-indigo-400 font-bold mt-0.5" x-text="res.category"></div>
                                            <div x-show="res.snomed_code" class="text-[9px] text-gray-400 font-mono mt-0.5">SNOMED: <span x-text="res.snomed_code"></span></div>
                                        </div>
                                        <div class="flex-shrink-0 text-right">
                                            <span class="inline-block px-2.5 py-1 bg-indigo-600 text-white text-[11px] font-black rounded-lg" x-text="res.icd10_code"></span>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Selected Diagnosis Chips -->
                        <div x-show="selectedDiagnoses.length > 0" class="flex flex-wrap gap-2">
                            <template x-for="(dx, idx) in selectedDiagnoses" :key="dx.icd10_code">
                                <div class="inline-flex items-center gap-2 pl-3 pr-2 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow-sm shadow-indigo-200 group">
                                    <div>
                                        <span class="opacity-70 text-[9px] font-black tracking-widest" x-text="dx.icd10_code"></span>
                                        <span class="mx-1">·</span>
                                        <span x-text="dx.description"></span>
                                        <span x-show="dx.snomed_code" class="ml-1.5 opacity-50 text-[9px] font-mono">SNOMED <span x-text="dx.snomed_code"></span></span>
                                    </div>
                                    <button type="button" @click="removeICD(idx)"
                                            class="w-5 h-5 rounded-full bg-white/20 hover:bg-white/40 flex items-center justify-center transition-colors ml-1">
                                        <i class="fa-solid fa-times text-[9px]"></i>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- Free-text diagnosis (always visible for additional notes) -->
                        <textarea
                            name="diagnosis"
                            x-model="diagnosisText"
                            rows="3"
                            class="w-full px-6 py-4 bg-gray-50 border-none rounded-[1.5rem] focus:ring-2 focus:ring-teal-500 text-sm font-medium"
                            placeholder="Additional clinical notes or free-text impression (optional — ICD-10 selections above are auto-included)..."
                        ></textarea>
                        <input type="hidden" name="icd10_data" :value="JSON.stringify(selectedDiagnoses)">
                    </div>
                </div>

                <div class="mt-12 flex justify-center">
                    <button type="button" @click="step = 2" class="bg-teal-600 hover:bg-teal-700 text-white px-12 py-5 rounded-[2rem] font-black shadow-xl shadow-teal-100 transition-all flex items-center gap-3">
                        Proceed to Medications <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- STEP 2: Medication Builder -->
        <div x-show="step === 2" x-cloak x-transition:enter="duration-500" x-transition:enter-start="translate-x-full opacity-0" class="space-y-10">
            <div class="bg-white rounded-[3rem] p-12 shadow-2xl shadow-gray-100 border border-gray-50">
                <div class="flex items-center justify-between mb-12">
                    <div>
                        <h2 class="text-2xl font-black text-gray-800">Step 2: Digital Prescription</h2>
                        <p class="text-gray-400 text-sm mt-1 uppercase font-bold tracking-widest">Medication Administration Plan</p>
                    </div>
                    <button type="button" @click="addMedRow" class="bg-gray-100 hover:bg-teal-600 hover:text-white px-8 py-4 rounded-2xl font-black text-xs transition-all flex items-center gap-2">
                        <i class="fa-solid fa-plus-circle"></i> ADD MEDICINE
                    </button>
                </div>

                <div class="space-y-6">
                    <template x-for="(row, index) in rows" :key="index">
                        <div class="group relative bg-gray-50/50 hover:bg-white rounded-[2.5rem] p-8 transition-all border-2 border-transparent hover:border-teal-100 hover:shadow-xl">
                            <button type="button" @click="removeMedRow(index)" class="absolute -top-3 -right-3 w-10 h-10 bg-rose-500 text-white rounded-full flex items-center justify-center shadow-lg transition-transform hover:scale-110 opacity-0 group-hover:opacity-100 z-10">
                                <i class="fa-solid fa-times"></i>
                            </button>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                                <div class="md:col-span-5 space-y-4 relative">
                                    <label class="block text-[9px] font-black uppercase text-gray-400 tracking-[0.25em]">Medication Name *</label>
                                    <input type="text" x-model="row.medicine_name"
                                           @input.debounce.250ms="searchMeds(index, row.medicine_name)"
                                           @blur.debounce.200ms="clearMedResults(index)"
                                           placeholder="Type medicine name..."
                                           class="w-full px-6 py-4 bg-white border border-gray-100 rounded-2xl focus:ring-2 focus:ring-teal-500 font-bold text-gray-800">
                                    <div x-show="medResults[index] && medResults[index].length > 0"
                                         class="absolute z-30 top-full left-0 w-full bg-white mt-1 rounded-2xl shadow-2xl border border-gray-100 overflow-hidden max-h-48 overflow-y-auto">
                                        <template x-for="med in (medResults[index] || [])">
                                            <button type="button" @mousedown.prevent="selectMed(index, med.name)"
                                                    class="w-full text-left px-5 py-3 hover:bg-teal-50 text-sm font-bold text-gray-800 border-b border-gray-50 last:border-0 transition-colors">
                                                <i class="fa-solid fa-pills text-teal-400 mr-2 text-xs"></i>
                                                <span x-text="med.name"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                <div class="md:col-span-2 space-y-4">
                                    <label class="block text-[9px] font-black uppercase text-gray-400 tracking-[0.25em]">Dosage</label>
                                    <input type="text" x-model="row.dosage" placeholder="e.g. 500mg" class="w-full px-6 py-4 bg-white border border-gray-100 rounded-2xl focus:ring-2 focus:ring-teal-500 font-medium">
                                </div>
                                <div class="md:col-span-3 space-y-4">
                                    <label class="block text-[9px] font-black uppercase text-gray-400 tracking-[0.25em]">Frequency</label>
                                    <select x-model="row.frequency" class="w-full px-6 py-4 bg-white border border-gray-100 rounded-2xl focus:ring-2 focus:ring-teal-500 font-medium">
                                        <option value="1-0-1">Daily (BDS - 1-0-1)</option>
                                        <option value="1-1-1">Daily (TDS - 1-1-1)</option>
                                        <option value="1-0-0">Morning (OD - 1-0-0)</option>
                                        <option value="0-0-1">Night (OD - 0-0-1)</option>
                                        <option value="SOS">On Need (SOS)</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2 space-y-4">
                                    <label class="block text-[9px] font-black uppercase text-gray-400 tracking-[0.25em]">Duration</label>
                                    <input type="text" x-model="row.duration" placeholder="7 Days" class="w-full px-6 py-4 bg-white border border-gray-100 rounded-2xl focus:ring-2 focus:ring-teal-500 font-medium">
                                </div>
                                <div class="md:col-span-12 space-y-4 bg-teal-50/30 p-4 rounded-2xl">
                                    <label class="block text-[9px] font-black uppercase text-teal-600 tracking-[0.25em]">Special Instructions</label>
                                    <input type="text" x-model="row.instructions" placeholder="e.g. Empty stomach, after meals..." class="w-full px-6 py-3 bg-white border border-gray-100 rounded-xl focus:ring-2 focus:ring-teal-500 text-sm font-medium">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="rows.length === 0" class="text-center py-20 border-4 border-dashed border-gray-50 rounded-[3rem]">
                    <i class="fa-solid fa-pills text-6xl text-gray-100 mb-6 block"></i>
                    <p class="text-gray-300 font-bold uppercase tracking-widest text-sm">No medication added to this script.</p>
                    <button type="button" @click="addMedRow" class="mt-6 bg-teal-600 text-white px-8 py-3 rounded-2xl font-black text-sm hover:bg-teal-700 transition-all">
                        + Add First Medication
                    </button>
                </div>

                <div class="mt-12 flex items-center justify-between">
                    <button type="button" @click="step = 1" class="px-8 py-5 rounded-2xl font-black text-gray-400 hover:bg-gray-100 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-arrow-left"></i> BACK
                    </button>
                    <button type="button" @click="step = 3" class="bg-teal-600 hover:bg-teal-700 text-white px-12 py-5 rounded-[2rem] font-black shadow-xl shadow-teal-100 transition-all flex items-center gap-3">
                        Proceed to Lab Advising <i class="fa-solid fa-vials"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- STEP 3: Finalize & Labs -->
        <div x-show="step === 3" x-cloak x-transition:enter="duration-500" x-transition:enter-start="scale-90 opacity-0" class="max-w-5xl mx-auto space-y-10">
            <div class="bg-white rounded-[3rem] p-10 shadow-2xl shadow-gray-100 border border-gray-50">
                <div class="flex items-center gap-4 mb-10 pb-10 border-b border-gray-50">
                    <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-[1.5rem] flex items-center justify-center text-2xl shadow-inner">
                        <i class="fa-solid fa-vials"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-gray-800">Step 3: Lab Advising & Closure</h2>
                        <p class="text-gray-400 text-sm font-bold uppercase tracking-widest">Investigations & Return Instructions</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <!-- Lab Search -->
                    <div class="space-y-6">
                        <label class="block text-[10px] font-black uppercase text-gray-400 tracking-widest">Recommend Clinical Tests</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                x-model="labSearch" 
                                @input.debounce.300ms="searchLabs"
                                placeholder="Search by test name (e.g. FSH, Semen Atlas...)"
                                class="w-full px-8 py-5 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-amber-400 font-bold text-gray-800"
                            >
                            <div x-show="labResults.length > 0" class="absolute z-30 top-full left-0 w-full bg-white mt-2 rounded-[1.5rem] shadow-2xl border border-gray-100 overflow-hidden max-h-60 overflow-y-auto">
                                <template x-for="lab in labResults">
                                    <button @click="addLab(lab)" type="button" class="w-full text-left px-6 py-4 hover:bg-amber-50 flex items-center justify-between border-b border-gray-50 last:border-0">
                                        <div class="flex-1 min-w-0">
                                            <div class="font-bold text-gray-800 text-sm" x-text="lab.test_name"></div>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[9px] font-black text-gray-400 uppercase" x-text="lab.category || 'Lab'"></span>
                                                <span x-show="lab.cpt_code" class="text-[9px] font-black font-mono px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded">CPT <span x-text="lab.cpt_code"></span></span>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-plus-circle text-amber-300 flex-shrink-0"></i>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Selected Labs List -->
                        <div class="space-y-3">
                            <template x-for="(l, idx) in selectedLabs" :key="idx">
                                <div class="flex items-center justify-between p-4 bg-white border border-gray-100 rounded-2xl shadow-sm">
                                    <div class="flex items-center gap-4">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center" :class="l.for === 'Patient' ? 'bg-teal-100 text-teal-600' : 'bg-pink-100 text-pink-600'">
                                            <i class="fa-solid" :class="l.for === 'Patient' ? 'fa-user' : 'fa-heart'"></i>
                                        </div>
                                        <div>
                                            <div class="font-black text-xs text-gray-800" x-text="l.test_name"></div>
                                            <div class="text-[8px] font-bold uppercase" :class="l.for === 'Patient' ? 'text-teal-400' : 'text-pink-400'" x-text="`For ${l.for}`"></div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="toggleLabFor(idx)" class="w-8 h-8 rounded-lg hover:bg-gray-100 text-gray-400 transition-colors"><i class="fa-solid fa-arrows-rotate"></i></button>
                                        <button type="button" @click="removeLab(idx)" class="w-8 h-8 rounded-lg hover:bg-rose-50 text-rose-300 hover:text-rose-500 transition-all"><i class="fa-solid fa-trash-can text-xs"></i></button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Closing Details -->
                    <div class="space-y-10">
                        <div class="space-y-4">
                            <label class="block text-[10px] font-black uppercase text-gray-400 tracking-widest">Advice & Lifestyle Guidance</label>
                            <textarea name="general_advice" rows="4" class="w-full px-8 py-5 bg-gray-50 border-none rounded-[2rem] focus:ring-2 focus:ring-teal-500 text-sm font-medium" placeholder="Additional instructions to patient..."></textarea>
                        </div>
                        <div class="space-y-4">
                            <label class="block text-[10px] font-black uppercase text-gray-400 tracking-widest">Recommended Revisit</label>
                            <div class="relative">
                                <input type="date" name="next_visit" class="w-full px-8 py-5 bg-teal-50/50 border-none rounded-2xl focus:ring-2 focus:ring-teal-500 font-black text-teal-800">
                                <div class="absolute right-6 top-1/2 -translate-y-1/2 text-teal-600 pointer-events-none">
                                    <i class="fa-solid fa-calendar-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-16 flex items-center justify-between gap-6">
                    <button type="button" @click="step = 2" class="px-10 py-5 rounded-2xl font-black text-gray-400 hover:bg-gray-100 transition-all">
                        <i class="fa-solid fa-arrow-left mr-2"></i> PREVIOUS STEP
                    </button>
                    
                    <input type="hidden" name="medications_data" :value="JSON.stringify(rows)">
                    <input type="hidden" name="lab_tests_data" :value="JSON.stringify(selectedLabs)">
                    <input type="hidden" name="save_prescription" value="1">
                    
                    <button type="submit" @click="prepareSubmit" class="flex-1 bg-gradient-to-r from-teal-600 to-indigo-700 hover:from-teal-700 hover:to-indigo-800 text-white px-12 py-6 rounded-[2.5rem] font-black text-lg shadow-2xl shadow-indigo-100 transition-all active:scale-95 flex items-center justify-center gap-4">
                        <i class="fa-solid fa-cloud-upload"></i> LEGALIZE & SAVE Rx
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>

<!-- Dependencies -->
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
    let quill;
    function prescriptionWizard() {
        return {
            step: 1,
            rows: [{ medicine_name: '', dosage: '', frequency: '1-0-1', duration: '', instructions: '' }],
            medResults: {},
            icdSearch: '',
            icdResults: [],
            icdLoading: false,
            selectedDiagnoses: [],   // [{icd10_code, description, category, snomed_code}]
            diagnosisText: '',
            labSearch: '',
            labResults: [],
            selectedLabs: [],

            init() {
                quill = new Quill('#notes-editor', {
                    theme: 'snow',
                    placeholder: 'Enter clinical history, complaints, and findings...',
                    modules: { toolbar: [['bold', 'italic', 'underline'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['clean']] }
                });
            },

            addMedRow() { this.rows.push({ medicine_name: '', dosage: '', frequency: '1-0-1', duration: '', instructions: '' }); },
            removeMedRow(idx) { this.rows.splice(idx, 1); delete this.medResults[idx]; },

            async searchMeds(idx, query) {
                if (query.length < 2) { this.medResults[idx] = []; return; }
                try {
                    const res = await fetch(`api_search_medications.php?q=${encodeURIComponent(query)}`);
                    this.medResults[idx] = await res.json();
                } catch(e) { this.medResults[idx] = []; }
            },
            selectMed(idx, name) {
                this.rows[idx].medicine_name = name;
                this.medResults[idx] = [];
            },
            clearMedResults(idx) {
                setTimeout(() => { this.medResults[idx] = []; }, 250);
            },

            async searchICD() {
                if (this.icdSearch.length < 2) { this.icdResults = []; return; }
                this.icdLoading = true;
                try {
                    // 1) Local database first (fast, offline-capable, fertility-focused)
                    const local = await fetch(`api_search_icd10.php?q=${encodeURIComponent(this.icdSearch)}`);
                    const localData = await local.json();
                    if (localData.length > 0) {
                        this.icdResults = localData;
                    } else {
                        // 2) NIH API fallback (full ICD-10-CM, requires internet)
                        const nih = await fetch(`https://clinicaltables.nlm.nih.gov/api/icd10cm/v3/search?terms=${encodeURIComponent(this.icdSearch)}&maxList=15`);
                        const nihData = await nih.json();
                        this.icdResults = (nihData[3] || []).map(r => ({
                            icd10_code: r[0], description: r[1], category: 'General (NIH)', snomed_code: ''
                        }));
                    }
                } catch(e) { this.icdResults = []; }
                this.icdLoading = false;
            },
            selectICD(item) {
                if (!this.selectedDiagnoses.find(d => d.icd10_code === item.icd10_code)) {
                    this.selectedDiagnoses.push(item);
                }
                this.icdSearch = '';
                this.icdResults = [];
            },
            removeICD(idx) {
                this.selectedDiagnoses.splice(idx, 1);
            },

            async searchLabs() {
                if (this.labSearch.length < 2) { this.labResults = []; return; }
                try {
                    const res = await fetch(`api_search_lab_tests.php?q=${encodeURIComponent(this.labSearch)}`);
                    this.labResults = await res.json();
                } catch(e) { this.labResults = []; }
            },
            addLab(lab) {
                if (!this.selectedLabs.find(l => l.id === lab.id)) {
                    this.selectedLabs.push({ id: lab.id, test_name: lab.test_name, for: 'Patient' });
                }
                this.labSearch = '';
                this.labResults = [];
            },
            removeLab(idx) { this.selectedLabs.splice(idx, 1); },
            toggleLabFor(idx) {
                this.selectedLabs[idx].for = (this.selectedLabs[idx].for === 'Patient') ? 'Spouse' : 'Patient';
            },

            prepareSubmit() {
                document.getElementById('clinical_notes').value = quill ? quill.root.innerHTML : '';
            }
        }
    }
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@500;600;700;800;900&display=swap');
    body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
    h1, h2, h3, .font-black { font-family: 'Outfit', sans-serif; }
    .ql-container { font-family: 'Inter', sans-serif !important; font-size: 15px !important; }
    .ql-editor { min-height: 150px; padding: 2rem !important; }
    .ql-toolbar.ql-snow { border: none !important; background: rgba(0,0,0,0.03); border-radius: 2rem 2rem 0 0; padding: 1rem !important; }
    [x-cloak] { display: none !important; }
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
