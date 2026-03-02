<?php
$pageTitle = "Write Prescription";
require_once __DIR__ . '/includes/auth.php';

$error = '';
$success = '';
$pre_patient_id = $_GET['patient_id'] ?? '';
$edit_id = intval($_GET['edit'] ?? 0);
$edit_data = null;
$edit_items = [];
$edit_diagnoses = ['ICD' => [], 'CPT' => []];

// Generate hash for public portal verifying
$qrcode_hash = bin2hex(random_bytes(16));

// If editing, fetch existing record
if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT r.*, p.first_name, p.last_name, p.mr_number FROM prescriptions r JOIN patients p ON r.patient_id = p.id WHERE r.id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
    if ($edit_data) {
        $pre_patient_id = $edit_data['patient_id'];
        $pageTitle = "Edit Prescription";

        // Fetch Items
        $res = $conn->query("SELECT * FROM prescription_items WHERE prescription_id = $edit_id");
        while ($row = $res->fetch_assoc())
            $edit_items[] = $row;

        // Fetch Diagnoses
        $res = $conn->query("SELECT * FROM prescription_diagnoses WHERE prescription_id = $edit_id");
        while ($row = $res->fetch_assoc()) {
            $edit_diagnoses[$row['type']][] = $row;
        }

        // Fetch Advised Lab Tests
        $edit_lab_tests = [];
        $res = $conn->query("SELECT * FROM prescription_lab_tests WHERE prescription_id = $edit_id");
        while ($row = $res->fetch_assoc()) {
            $edit_lab_tests[] = [
                'id' => $row['test_id'],
                'name' => $row['test_name'],
                'advised_for' => $row['advised_for']
            ];
        }
    }
    else {
        $edit_id = 0;
        $edit_lab_tests = [];
    }
}

// Removed patient loop memory overhead, using AJAX search API.

// Fetch Hospitals
$hospitals = [];
try {
    $res = $conn->query("SELECT id, name FROM hospitals ORDER BY name ASC");
    if ($res) {
        while ($row = $res->fetch_assoc())
            $hospitals[] = $row;
    }
}
catch (Exception $e) {
}

// Fetch Medications DB for Vue/Alpine data
$medications = [];
try {
    $res = $conn->query("SELECT id, name, med_type FROM medications ORDER BY name ASC");
    if ($res) {
        while ($row = $res->fetch_assoc())
            $medications[] = $row;
    }
}
catch (Exception $e) {
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_rx'])) {
    $patient_id = intval($_POST['patient_id'] ?? 0);
    $hospital_id = intval($_POST['hospital_id'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    $complaint = trim($_POST['presenting_complaint'] ?? '');
    $editing = intval($_POST['edit_id'] ?? 0);

    // Diagnoses Arrays
    $icd_codes = $_POST['icd_codes'] ?? [];
    $icd_names = $_POST['icd_names'] ?? [];
    $cpt_codes = $_POST['cpt_codes'] ?? [];
    $cpt_names = $_POST['cpt_names'] ?? [];
    $revisit_date = !empty($_POST['revisit_date']) ? $_POST['revisit_date'] : null;

    $meds_array = $_POST['med_id'] ?? [];
    $dosages = $_POST['dosage'] ?? [];
    $usage_freqs = $_POST['usage_frequency'] ?? [];
    $durations = $_POST['duration'] ?? [];
    $instructions = $_POST['instructions'] ?? [];

    $adv_test_ids = $_POST['adv_test_id'] ?? [];
    $adv_test_names = $_POST['adv_test_name'] ?? [];
    $adv_test_for = $_POST['adv_test_for'] ?? [];

    if (empty($patient_id) || empty($hospital_id)) {
        $error = "Patient and Hospital fields are required.";
    }
    else {
        // Handle file upload
        $scanned_path = null;
        if ($editing > 0)
            $scanned_path = $edit_data['scanned_report_path'];

        $scan_location = $_POST['scan_location'] ?? null;
        $scan_timestamp = null;

        if (isset($_FILES['scanned_report']) && $_FILES['scanned_report']['error'] == UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
            if (in_array($_FILES['scanned_report']['type'], $allowed_types)) {
                $upload_dir = __DIR__ . '/../uploads/scans/rx/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $ext = pathinfo($_FILES['scanned_report']['name'], PATHINFO_EXTENSION);
                $filename = 'rx_' . date('Ymd_His') . '_' . uniqid() . '.' . $ext;
                $dest = $upload_dir . $filename;

                if (move_uploaded_file($_FILES['scanned_report']['tmp_name'], $dest)) {
                    $scanned_path = 'uploads/scans/rx/' . $filename;
                    $scan_timestamp = date('Y-m-d H:i:s');
                }
            }
            else {
                $error = "Only JPG, PNG, and PDF files are allowed for scanned reports.";
            }
        }

        if (empty($error)) {
            $conn->begin_transaction();
            try {
                if ($editing > 0) {
                    // Update Prescript Master
                    $stmt = $conn->prepare("UPDATE prescriptions SET patient_id=?, hospital_id=?, presenting_complaint=?, revisit_date=?, notes=?, scanned_report_path=?, scan_timestamp=?, scan_location_data=? WHERE id=?");
                    $stmt->bind_param("iissssssi", $patient_id, $hospital_id, $complaint, $revisit_date, $notes, $scanned_path, $scan_timestamp, $scan_location, $editing);
                    $stmt->execute();
                    $rx_id = $editing;

                    // Clear existing items and diagnoses for re-insert (Cleanest way)
                    $conn->query("DELETE FROM prescription_items WHERE prescription_id = $rx_id");
                    $conn->query("DELETE FROM prescription_diagnoses WHERE prescription_id = $rx_id");
                    $conn->query("DELETE FROM prescription_lab_tests WHERE prescription_id = $rx_id");
                }
                else {
                    // Insert Prescript Master
                    $stmt = $conn->prepare("INSERT INTO prescriptions (patient_id, hospital_id, qrcode_hash, presenting_complaint, revisit_date, notes, scanned_report_path, scan_timestamp, scan_location_data) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("iisssssss", $patient_id, $hospital_id, $qrcode_hash, $complaint, $revisit_date, $notes, $scanned_path, $scan_timestamp, $scan_location);
                    $stmt->execute();
                    $rx_id = $conn->insert_id;
                }

                // Insert Items
                if (!empty($meds_array)) {
                    $stmt_items = $conn->prepare("INSERT INTO prescription_items (prescription_id, medication_id, dosage, usage_frequency, duration, instructions) VALUES (?, ?, ?, ?, ?, ?)");
                    foreach ($meds_array as $k => $m_id) {
                        if (!empty($m_id)) {
                            $dos = trim($dosages[$k] ?? '');
                            $ufreq = trim($usage_freqs[$k] ?? '');
                            $dur = trim($durations[$k] ?? '');
                            $ins = trim($instructions[$k] ?? '');
                            $stmt_items->bind_param("iissss", $rx_id, $m_id, $dos, $ufreq, $dur, $ins);
                            $stmt_items->execute();
                        }
                    }
                }

                // Insert Diagnoses (Multiple)
                $stmt_diag = $conn->prepare("INSERT INTO prescription_diagnoses (prescription_id, type, code, description) VALUES (?, ?, ?, ?)");

                // Loop ICD
                $type_icd = 'ICD';
                for ($i = 0; $i < count($icd_codes); $i++) {
                    $c = trim($icd_codes[$i] ?? '');
                    $n = trim($icd_names[$i] ?? '');
                    if (!empty($n)) {
                        $stmt_diag->bind_param("isss", $rx_id, $type_icd, $c, $n);
                        $stmt_diag->execute();
                    }
                }

                // Loop CPT
                $type_cpt = 'CPT';
                for ($i = 0; $i < count($cpt_codes); $i++) {
                    $c = trim($cpt_codes[$i] ?? '');
                    $n = trim($cpt_names[$i] ?? '');
                    if (!empty($n)) {
                        $stmt_diag->bind_param("isss", $rx_id, $type_cpt, $c, $n);
                        $stmt_diag->execute();
                    }
                }

                // Insert Advised Lab Tests
                if (!empty($adv_test_names)) {
                    $stmt_lab = $conn->prepare("INSERT INTO prescription_lab_tests (prescription_id, test_id, test_name, advised_for) VALUES (?, ?, ?, ?)");
                    foreach ($adv_test_names as $k => $t_name) {
                        if (!empty($t_name)) {
                            $t_id = !empty($adv_test_ids[$k]) ? intval($adv_test_ids[$k]) : null;
                            $t_for = $adv_test_for[$k] ?? 'Patient';
                            $stmt_lab->bind_param("iiss", $rx_id, $t_id, $t_name, $t_for);
                            $stmt_lab->execute();
                        }
                    }
                }

                $conn->commit();
                header("Location: prescriptions.php?msg=rx_saved");
                exit;
            }
            catch (Exception $e) {
                $conn->rollback();
                $error = "Failed to save prescription: " . $e->getMessage();
            }
        } // Close if (empty($error))
    } // Close else
} // Close if POST

include __DIR__ . '/includes/header.php';
?>

<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <h3 class="font-bold text-gray-800"><i class="fa-solid fa-prescription text-indigo-600 mr-2"></i> <?php echo $edit_id ? 'Edit' : 'New'; ?> E-Prescription</h3>
        </div>
        
        <div class="p-6">
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-100 flex gap-2">
                    <i class="fa-solid fa-circle-exclamation mt-1"></i> <?php echo esc($error); ?>
                </div>
            <?php
endif; ?>

            <form method="POST" enctype="multipart/form-data" x-data="prescriptionBuilder()">
                <?php if ($edit_id): ?><input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>"><?php
endif; ?>
                
                <!-- Setup Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- AJAX Patient Search -->
                    <div class="relative" x-data="{ init() { if(<?php echo $edit_data ? 'true' : 'false'; ?>) { this.selectPatient(<?php echo json_encode(['id' => $edit_data['patient_id'], 'mr_number' => $edit_data['mr_number'], 'first_name' => $edit_data['first_name'], 'last_name' => $edit_data['last_name']]); ?>); } } }">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search Patient (MR / Phone / Name) *</label>
                        <input type="hidden" name="patient_id" :value="selectedPatientId" required>
                        <div class="relative">
                            <input type="text" x-model="patQuery" @input.debounce.300ms="searchPatient" placeholder="Type to search..." 
                                class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors" 
                                :class="selectedPatientId ? 'bg-indigo-50 border-indigo-300 font-bold text-indigo-800 shadow-inner' : ''" autocomplete="off">
                            <div x-show="patLoading" class="absolute right-3 top-3.5 text-indigo-500">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        
                        <!-- Dropdown -->
                        <div x-show="patResults.length > 0 && !selectedPatientId" @click.away="patResults = []" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-2xl overflow-hidden max-h-64 overflow-y-auto">
                            <template x-for="p in patResults" :key="p.id">
                                <div @click="selectPatient(p)" class="px-4 py-3 border-b border-gray-100 hover:bg-indigo-50 cursor-pointer transition-colors">
                                    <div class="font-bold text-gray-800 flex justify-between">
                                        <span x-text="p.first_name + ' ' + (p.last_name || '')"></span>
                                        <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded border border-gray-200" x-text="p.gender"></span>
                                    </div>
                                    <div class="text-xs text-gray-500 flex gap-3 mt-1.5">
                                        <span class="font-mono text-indigo-700 font-bold bg-indigo-100 px-1.5 py-0.5 rounded" x-text="'MR: ' + p.mr_number"></span>
                                        <span x-html="'<i class=\'fa-solid fa-phone text-[9px]\'></i> ' + (p.phone || 'N/A')" class="flex items-center gap-1"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div x-show="selectedPatientId" class="text-xs text-red-500 mt-2 font-medium cursor-pointer hover:underline flex items-center gap-1 w-max" @click="clearPatient()">
                            <i class="fa-solid fa-times-circle"></i> Clear Selection
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hospital / Letterhead *</label>
                        <select name="hospital_id" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                            <?php foreach ($hospitals as $h): ?>
                                <option value="<?php echo $h['id']; ?>" <?php echo($edit_data && $edit_data['hospital_id'] == $h['id']) ? 'selected' : ''; ?>><?php echo esc($h['name']); ?></option>
                            <?php
endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Select the hospital to accurately print on its specific letterhead margins.</p>
                    </div>
                </div>

                <!-- Clinical Details (Complaint & ICD Search) -->
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 mb-6">
                    <h4 class="font-bold text-gray-800 border-b border-gray-200 pb-2 mb-4">Clinical Assesment & Diagnosis</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Presenting Complaint / History</label>
                            <textarea name="presenting_complaint" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:-ring-indigo-500 text-sm" placeholder="Patient presented with..."><?php echo esc($edit_data['presenting_complaint'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- ICD-10 Search via NIH API -->
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1 flex justify-between">
                                <span>Diagnosis (ICD-10)</span>
                                <span class="text-[10px] text-gray-400 font-bold bg-white px-1 border rounded">LIVE NIH DB</span>
                            </label>

                            <!-- Selected ICDs List -->
                            <div class="mb-2 space-y-2">
                                <template x-for="(icd, idx) in selectedIcds" :key="idx">
                                    <div class="flex items-center bg-emerald-50 border border-emerald-200 px-3 py-2 rounded shadow-sm gap-3 text-sm">
                                        <input type="hidden" name="icd_codes[]" :value="icd.code">
                                        <input type="hidden" name="icd_names[]" :value="icd.name">
                                        <span class="font-mono text-emerald-700 font-bold w-12 shrink-0" x-text="icd.code"></span>
                                        <span class="text-gray-800 flex-grow" x-text="icd.name"></span>
                                        <button type="button" @click="removeIcd(idx)" class="text-red-400 hover:text-red-700"><i class="fa-solid fa-times-circle"></i></button>
                                    </div>
                                </template>
                            </div>

                            <div class="relative">
                                <input type="text" x-model="icdQuery" @input.debounce.400ms="searchIcd" placeholder="Search and add diagnosis..." 
                                    class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-emerald-500 transition-colors text-sm" autocomplete="off">
                                <div x-show="icdLoading" class="absolute right-3 top-2.5 text-emerald-500">
                                    <i class="fa-solid fa-spinner fa-spin text-sm"></i>
                                </div>
                            </div>
                            
                            <!-- Dropdown -->
                            <div x-show="icdResults.length > 0" @click.away="icdResults = []" class="absolute z-40 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-xl overflow-hidden max-h-48 overflow-y-auto text-sm">
                                <template x-for="icd in icdResults" :key="icd[0]">
                                    <div @click="addIcd(icd)" class="px-3 py-2 border-b border-gray-100 hover:bg-emerald-50 cursor-pointer flex gap-3">
                                        <span class="font-mono text-emerald-700 font-bold w-12 shrink-0" x-text="icd[0]"></span>
                                        <span class="text-gray-800" x-text="icd[1]"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- CPT / Procedure -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 flex justify-between">
                                <span>Advised Procedures (CPT/SNOMED)</span>
                                <span class="text-[10px] text-gray-400 font-bold bg-white px-1 border rounded">MANUAL ENTRY</span>
                            </label>

                            <!-- Selected Procs List -->
                            <div class="mb-2 space-y-2">
                                <template x-for="(proc, idx) in selectedProcs" :key="idx">
                                    <div class="flex items-center bg-indigo-50 border border-indigo-200 px-3 py-2 rounded shadow-sm gap-3 text-sm">
                                        <input type="hidden" name="cpt_codes[]" :value="proc.code">
                                        <input type="hidden" name="cpt_names[]" :value="proc.name">
                                        <span class="font-mono text-indigo-700 font-bold w-16 shrink-0" x-text="proc.code || 'N/A'"></span>
                                        <span class="text-gray-800 flex-grow" x-text="proc.name"></span>
                                        <button type="button" @click="removeProc(idx)" class="text-red-400 hover:text-red-700"><i class="fa-solid fa-times-circle"></i></button>
                                    </div>
                                </template>
                            </div>

                            <div class="flex gap-2">
                                <input type="text" x-model="procCodeInput" placeholder="Code (opt)" class="w-1/4 px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-indigo-500 text-sm font-mono" autocomplete="off" @keydown.enter.prevent="addProc">
                                <input type="text" x-model="procNameInput" placeholder="e.g. Scrotal Ultrasound" class="w-3/4 px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-indigo-500 text-sm" autocomplete="off" @keydown.enter.prevent="addProc">
                                <button type="button" @click="addProc" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 shadow-sm transition-colors text-sm"><i class="fa-solid fa-plus"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Medication Builder -->
                <div class="border border-gray-200 rounded-xl overflow-hidden mb-6 mt-4">
                    <div class="bg-indigo-900 text-white px-4 py-3 flex justify-between items-center">
                        <span class="font-bold">Rx. Medications</span>
                        <button type="button" @click="addRow" class="bg-indigo-700 hover:bg-indigo-600 px-3 py-1 rounded text-sm transition-colors text-white border border-indigo-500 shadow-sm">
                            <i class="fa-solid fa-plus mr-1"></i> Add Medication
                        </button>
                    </div>
                    
                    <div class="p-4 bg-gray-50 space-y-3">
                        <template x-for="(row, index) in rows" :key="row.id">
                            <div class="flex flex-col md:flex-row gap-2 items-end bg-white p-3 rounded-lg border border-gray-200 shadow-sm relative pt-6 md:pt-3">
                                
                                <div class="absolute top-1 left-2 md:hidden text-[10px] font-bold text-gray-400" x-text="'Item #' + (index+1)"></div>
                                
                                <div class="w-full md:w-[35%]">
                                    <label class="block text-xs text-gray-500 mb-1">Medication *</label>
                                    <select :name="'med_id['+index+']'" x-model="row.med_id" class="w-full px-3 py-2 border border-gray-300 rounded text-sm outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required>
                                        <option value="">Choose...</option>
                                        <?php foreach ($medications as $m): ?>
                                            <option value="<?php echo $m['id']; ?>"><?php echo esc($m['name'] . ' (' . $m['med_type'] . ')'); ?></option>
                                        <?php
endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="w-full md:w-[15%]">
                                    <label class="block text-xs text-gray-500 mb-1">Dosage</label>
                                    <input type="text" :name="'dosage['+index+']'" x-model="row.dosage" placeholder="500mg, 1 tab" class="w-full px-3 py-2 border border-gray-300 rounded text-sm outline-none focus:ring-1 focus:ring-indigo-500">
                                </div>

                                <div class="w-full md:w-[20%]">
                                    <label class="block text-xs text-gray-500 mb-1">Usage Freq</label>
                                    <select :name="'usage_frequency['+index+']'" x-model="row.usage_frequency" class="w-full px-3 py-2 border border-gray-300 rounded text-sm outline-none focus:ring-1 focus:ring-indigo-500">
                                        <option value="">--</option>
                                        <option value="OD">OD (1x a day)</option>
                                        <option value="BD">BD (2x a day)</option>
                                        <option value="TDS">TDS (3x a day)</option>
                                        <option value="QID">QID (4x a day)</option>
                                        <option value="SOS">SOS (When needed)</option>
                                        <option value="Stat">Stat (Immediately)</option>
                                    </select>
                                </div>

                                <div class="w-full md:w-[15%]">
                                    <label class="block text-xs text-gray-500 mb-1">Duration</label>
                                    <input type="text" :name="'duration['+index+']'" x-model="row.duration" placeholder="5 Days" class="w-full px-3 py-2 border border-gray-300 rounded text-sm outline-none focus:ring-1 focus:ring-indigo-500">
                                </div>
                                
                                <div class="w-full md:w-[30%]">
                                    <label class="block text-xs text-gray-500 mb-1">Instructions</label>
                                    <input type="text" :name="'instructions['+index+']'" x-model="row.instructions" placeholder="e.g. After meals" class="w-full px-3 py-2 border border-gray-300 rounded text-sm outline-none focus:ring-1 focus:ring-indigo-500">
                                </div>
                                
                                <div class="w-full md:w-10 flex justify-end">
                                    <button type="button" @click="removeRow(index)" class="text-gray-400 hover:text-red-600 hover:bg-red-50 w-full md:w-10 h-9 rounded-md transition-colors border border-transparent md:border-gray-200 mt-2 md:mt-0" title="Remove row">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                                
                            </div>
                        </template>
                        <div x-show="rows.length === 0" class="text-center py-6 text-gray-400 text-sm">
                            Click <strong class="text-indigo-600">"Add Medication"</strong> to assign drugs to this prescription.
                        </div>
                    </div>
                </div>

                <!-- Advised Laboratory Tests -->
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-6 mb-6">
                    <h4 class="font-bold text-slate-800 border-b border-slate-200 pb-2 mb-4 flex justify-between items-center">
                        <span><i class="fa-solid fa-vial-virus text-indigo-600 mr-2"></i> Advised Laboratory Investigations</span>
                        <span class="text-[10px] text-gray-400 font-bold bg-white px-2 py-0.5 border rounded">SEARCH DIRECTORY</span>
                    </h4>

                    <!-- Selected Tests List -->
                    <div class="mb-4 space-y-2">
                        <template x-for="(test, idx) in selectedTests" :key="idx">
                            <div class="flex items-center bg-white border border-slate-200 px-4 py-3 rounded-lg shadow-sm gap-4 transition-all hover:border-indigo-300">
                                <input type="hidden" name="adv_test_id[]" :value="test.id">
                                <input type="hidden" name="adv_test_name[]" :value="test.name">
                                <input type="hidden" name="adv_test_for[]" :value="test.advised_for">
                                
                                <div class="flex-grow">
                                    <div class="font-bold text-slate-800" x-text="test.name"></div>
                                    <div class="text-[10px] text-slate-500 font-mono mt-0.5" x-text="'ID: ' + (test.id || 'Custom')"></div>
                                </div>

                                <!-- Attribution Toggle -->
                                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-lg border border-slate-200">
                                    <button type="button" @click="test.advised_for = 'Patient'" 
                                        :class="test.advised_for === 'Patient' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                                        class="px-3 py-1 rounded text-[10px] font-bold transition-all uppercase">Patient</button>
                                    <button type="button" @click="test.advised_for = 'Spouse'" 
                                        :class="test.advised_for === 'Spouse' ? 'bg-pink-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                                        class="px-3 py-1 rounded text-[10px] font-bold transition-all uppercase">Spouse</button>
                                </div>

                                <button type="button" @click="removeTest(idx)" class="text-slate-300 hover:text-red-500 transition-colors">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </template>
                        <div x-show="selectedTests.length === 0" class="text-center py-4 text-slate-400 text-xs italic">
                            Search for tests below to add them to this prescription.
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="relative">
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-search text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                            </div>
                            <input type="text" x-model="testQuery" @input.debounce.300ms="searchTest" placeholder="Search lab tests (e.g. HIV, HbA1c, Beta HCG)..." 
                                class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all text-sm" autocomplete="off" @keydown.enter.prevent="addCustomTest">
                            <div x-show="testLoading" class="absolute right-3 top-3.5 text-indigo-500">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        
                        <!-- Search Results Dropdown -->
                        <div x-show="testResults.length > 0" @click.away="testResults = []" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-2xl overflow-hidden max-h-60 overflow-y-auto">
                            <template x-for="t in testResults" :key="t.id">
                                <div @click="addTest(t)" class="px-4 py-3 border-b border-gray-100 hover:bg-slate-50 cursor-pointer flex justify-between items-center transition-colors">
                                    <span class="font-medium text-slate-700" x-text="t.test_name"></span>
                                    <i class="fa-solid fa-plus text-slate-300"></i>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Follow-up, Notes & Manual Upload -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">General Advice / Notes</label>
                        <textarea name="notes" rows="3" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Follow-up exactly after 14 days for beta-hCG..."><?php echo esc($edit_data['notes'] ?? ''); ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Revisit / Follow-up Date</label>
                        <input type="date" name="revisit_date" value="<?php echo $edit_data['revisit_date'] ?? ''; ?>" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-800">
                        <p class="text-[10px] text-gray-400 mt-2">Set this to automatically print the next visit date on the bottom of the prescription.</p>
                    </div>
                </div>

                <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-6 mb-6">
                    <h4 class="font-bold text-indigo-900 border-b border-indigo-200 pb-2 mb-4">
                        <i class="fa-solid fa-camera mr-2"></i> Attach Manual / Outside Document (Optional)
                    </h4>
                    <p class="text-xs text-indigo-700 mb-4">If the patient brought a handwritten slip or you wish to bypass the digital entry, capture a picture or upload the PDF here. The patient will be able to download the raw document securely.</p>
                    
                    <div class="w-full">
                        <input type="file" name="scanned_report" accept="image/jpeg,image/png,application/pdf" class="block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-indigo-600 file:text-white
                            hover:file:bg-indigo-700 cursor-pointer
                        "/>
                    </div>
                    
                    <input type="hidden" name="scan_location" x-model="scanLocationData">
                    <button type="button" @click="captureLocation" class="mt-4 text-xs font-semibold px-3 py-1.5 rounded-lg border border-indigo-300 bg-white text-indigo-700 hover:bg-indigo-100 transition-colors shadow-sm">
                        <i class="fa-solid fa-location-crosshairs"></i> Tag GPS Location
                    </button>
                    <span x-show="locStatus" class="ml-2 text-[10px] text-gray-600 font-mono" x-text="locStatus"></span>
                </div>
                
                <div class="flex justify-end gap-3 mt-8">
                    <a href="prescriptions.php" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-6 py-3 rounded-lg font-medium transition-colors">Cancel</a>
                    <button type="submit" name="save_rx" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg shadow-md hover:shadow-lg transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center gap-2">
                        <i class="fa-solid fa-file-signature"></i> <?php echo $edit_id ? 'Update Prescription' : 'Save & Generate Prescription'; ?>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('prescriptionBuilder', () => ({
        
        // --- Patient Search ---
        patQuery: '<?php echo $edit_data ? esc($edit_data['first_name'] . ' ' . $edit_data['last_name'] . ' (' . $edit_data['mr_number'] . ')') : ''; ?>',
        patResults: [],
        patLoading: false,
        selectedPatientId: '<?php echo $edit_data['patient_id'] ?? ''; ?>',
        
        async searchPatient() {
            if (this.selectedPatientId) return;
            if (this.patQuery.length < 2) {
                this.patResults = []; return;
            }
            this.patLoading = true;
            try {
                let res = await fetch(`api_search_patients.php?q=${encodeURIComponent(this.patQuery)}`);
                this.patResults = await res.json();
            } catch (e) {
                console.error(e);
            }
            this.patLoading = false;
        },
        
        selectPatient(p) {
            this.selectedPatientId = p.id;
            this.patQuery = p.first_name + ' ' + (p.last_name || '') + ' (' + p.mr_number + ')';
            this.patResults = [];
        },
        
        clearPatient() {
            this.selectedPatientId = '';
            this.patQuery = '';
            this.patResults = [];
        },

        // --- ICD-10 Handling ---
        icdQuery: '',
        icdResults: [],
        icdLoading: false,
        selectedIcds: <?php echo json_encode(array_map(function ($d) {
    return ['code' => $d['code'], 'name' => $d['description']];
}, $edit_diagnoses['ICD'])); ?>,

        async searchIcd() {
            if (this.icdQuery.length < 2) {
                this.icdResults = []; return;
            }
            this.icdLoading = true;
            try {
                // NIH Clinical Tables API for ICD-10
                let url = `https://clinicaltables.nlm.nih.gov/api/icd10cm/v3/search?sf=code,name&terms=${encodeURIComponent(this.icdQuery)}&maxList=10`;
                let res = await fetch(url);
                let data = await res.json();
                this.icdResults = data[3] || [];
            } catch (e) {
                console.error('ICD API Error:', e);
            }
            this.icdLoading = false;
        },

        addIcd(icdArr) {
            this.selectedIcds.push({ code: icdArr[0], name: icdArr[1] });
            this.icdQuery = '';
            this.icdResults = [];
        },

        removeIcd(idx) {
            this.selectedIcds.splice(idx, 1);
        },

        // --- CPT / SNOMED Handling ---
        selectedProcs: <?php echo json_encode(array_map(function ($d) {
    return ['code' => $d['code'], 'name' => $d['description']];
}, $edit_diagnoses['CPT'])); ?>,
        procCodeInput: '',
        procNameInput: '',

        addProc() {
            const name = this.procNameInput.trim();
            if (!name) return;
            this.selectedProcs.push({ code: this.procCodeInput.trim(), name: name });
            this.procCodeInput = '';
            this.procNameInput = '';
        },

        removeProc(idx) {
            this.selectedProcs.splice(idx, 1);
        },

        // --- Rx Rows ---
        rows: <?php
if (!empty($edit_items)) {
    $mapped = [];
    $nid = 1;
    foreach ($edit_items as $item) {
        $mapped[] = [
            'id' => $nid++,
            'med_id' => $item['medication_id'],
            'dosage' => $item['dosage'],
            'usage_frequency' => $item['usage_frequency'],
            'duration' => $item['duration'],
            'instructions' => $item['instructions']
        ];
    }
    echo json_encode($mapped);
}
else {
    echo '[{id: 1}]';
}
?>,
        nextId: <?php echo count($edit_items) + 1; ?>,
        addRow() {
            this.rows.push({ id: this.nextId++ });
        },
        removeRow(index) {
            this.rows.splice(index, 1);
        },

        // --- Lab Tests ---
        selectedTests: <?php echo json_encode($edit_lab_tests); ?>,
        testQuery: '',
        testResults: [],
        testLoading: false,

        async searchTest() {
            if (this.testQuery.length < 2) {
                this.testResults = []; return;
            }
            this.testLoading = true;
            try {
                let res = await fetch(`api_search_lab_tests.php?q=${encodeURIComponent(this.testQuery)}`);
                this.testResults = await res.json();
            } catch (e) {
                console.error(e);
            }
            this.testLoading = false;
        },

        addTest(t) {
            this.selectedTests.push({ id: t.id, name: t.test_name, advised_for: 'Patient' });
            this.testQuery = '';
            this.testResults = [];
        },

        addCustomTest() {
            if (this.testQuery.trim().length > 0) {
                this.selectedTests.push({ id: null, name: this.testQuery.trim(), advised_for: 'Patient' });
                this.testQuery = '';
                this.testResults = [];
            }
        },

        removeTest(idx) {
            this.selectedTests.splice(idx, 1);
        },

        // --- Geolocation ---
        scanLocationData: '',
        locStatus: '',
        captureLocation() {
            if (!navigator.geolocation) {
                this.locStatus = "Geolocation not supported.";
                return;
            }
            this.locStatus = "Requesting location...";
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const coords = { lat: pos.coords.latitude, lng: pos.coords.longitude, acc: pos.coords.accuracy };
                    this.scanLocationData = JSON.stringify(coords);
                    this.locStatus = `Captured: ${coords.lat.toFixed(4)}, ${coords.lng.toFixed(4)}`;
                },
                (err) => {
                    this.locStatus = "Denied / Unavailable.";
                },
                { enableHighAccuracy: true, timeout: 5000 }
            );
        }
    }));
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
