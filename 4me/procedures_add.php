<?php
require_once __DIR__ . '/includes/auth.php';

$error = '';
$success = '';
$edit_id = intval($_GET['edit'] ?? 0);
$edit_data = null;

if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT ap.*, p.first_name, p.last_name, p.mr_number, p.phone 
                            FROM advised_procedures ap 
                            JOIN patients p ON ap.patient_id = p.id 
                            WHERE ap.id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
    if (!$edit_data) {
        header("Location: procedures.php?error=NotFound");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_procedure'])) {
    $patient_id = intval($_POST['patient_id'] ?? 0);
    $procedure_name = trim($_POST['procedure_name'] ?? '');
    $date_advised = trim($_POST['date_advised'] ?? date('Y-m-d'));
    $notes = trim($_POST['notes'] ?? '');
    $record_for = $_POST['record_for'] ?? 'Patient';
    $status = $_POST['status'] ?? 'Advised';
    $current_edit_id = intval($_POST['edit_id'] ?? 0);

    // Check if new procedure name was entered manually
    if ($procedure_name === 'OTHER') {
        $procedure_name = trim($_POST['other_procedure_name'] ?? '');
    }

    if ($patient_id === 0 || empty($procedure_name)) {
        $error = "Patient and Procedure Name are required.";
    }
    else {
        try {
            if ($current_edit_id > 0) {
                $stmt = $conn->prepare("UPDATE advised_procedures SET patient_id=?, procedure_name=?, date_advised=?, notes=?, record_for=?, status=? WHERE id=?");
                $stmt->bind_param("isssss i", $patient_id, $procedure_name, $date_advised, $notes, $record_for, $status, $current_edit_id);
            }
            else {
                $stmt = $conn->prepare("INSERT INTO advised_procedures (patient_id, procedure_name, date_advised, notes, record_for, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssss", $patient_id, $procedure_name, $date_advised, $notes, $record_for, $status);
            }

            if ($stmt && $stmt->execute()) {
                // Redirect back to patient view
                flash('success', 'Procedure record saved successfully.');
                header("Location: patients_view.php?id={$patient_id}&tab=procedures&msg=proc_saved");
                exit;
            }
            else {
                $error = "Database operation failed: " . ($stmt ? $stmt->error : $conn->error);
            }
        }
        catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Predefined IVF / Fertility Procedures
$common_procedures = [
    "In Vitro Fertilization (IVF)",
    "Intracytoplasmic Sperm Injection (ICSI)",
    "Intrauterine Insemination (IUI)",
    "Frozen Embryo Transfer (FET)",
    "Preimplantation Genetic Testing (PGT-A / PGT-M)",
    "Next-Generation Sequencing (NGS)",
    "Testicular Sperm Extraction (TESE)",
    "Percutaneous Epididymal Sperm Aspiration (PESA)",
    "Egg Freezing (Oocyte Cryopreservation)",
    "Sperm Freezing",
    "Embryo Freezing",
    "Diagnostic Hysteroscopy",
    "Diagnostic Laparoscopy"
];

// Pre-select patient when coming from patients_view.php or any ?patient_id= link
$preselected_patient = null;
if ($edit_data) {
    $preselected_patient = [
        'id' => $edit_data['patient_id'],
        'first_name' => $edit_data['first_name'],
        'last_name' => $edit_data['last_name'],
        'mr_number' => $edit_data['mr_number'],
        'phone' => $edit_data['phone'] ?? '',
    ];
}
elseif (!empty($_GET['patient_id'])) {
    $pid_pre = intval($_GET['patient_id']);
    $pst = $conn->prepare("SELECT id, first_name, last_name, mr_number, phone FROM patients WHERE id = ?");
    $pst->bind_param("i", $pid_pre);
    $pst->execute();
    $preselected_patient = $pst->get_result()->fetch_assoc() ?: null;
}

$pageTitle = $edit_id ? 'Edit Advised Treatment' : 'Advise New Treatment';
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <a href="procedures.php" class="text-sm text-gray-500 hover:text-indigo-600 font-medium flex items-center gap-1">
            <i class="fa-solid fa-arrow-left"></i> Back to Tracker
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-8 py-5 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
            <div class="w-10 h-10 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-clipboard-check text-base"></i>
            </div>
            <div>
                <h3 class="font-black text-gray-800 text-lg"><?php echo $edit_id ? 'Edit' : 'Advise'; ?> Medical Procedure</h3>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Treatment & Procedure Planning</p>
            </div>
        </div>
        
        <div class="p-6 md:p-8">
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-100 flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php
endif; ?>

            <form method="POST">
                <?php if ($edit_id): ?>
                    <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
                <?php
endif; ?>
                
                <!-- Patient Selection (AJAX component via Alpine) -->
                <div class="mb-8" x-data="patientSearch(<?php echo json_encode($preselected_patient); ?>)">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Select Patient *</label>
                    
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-search text-gray-400"></i>
                        </div>
                        <input type="text" x-model="searchQuery" @input.debounce.300ms="searchPatients()" placeholder="Search by name, MR number, or phone..." class="w-full pl-10 px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 bg-gray-50" autocomplete="off">
                        
                        <!-- Search Results Dropdown -->
                        <div x-show="results.length > 0 && showResults" @click.away="showResults = false" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto" x-cloak>
                            <template x-for="pt in results" :key="pt.id">
                                <div @click="selectPatient(pt)" class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-50 last:border-0">
                                    <div class="font-bold text-gray-800" x-text="pt.first_name + ' ' + (pt.last_name || '')"></div>
                                    <div class="text-xs text-gray-500 mt-1 flex gap-3">
                                        <span class="text-indigo-600 font-mono font-medium" x-text="pt.mr_number"></span>
                                        <span x-text="pt.phone || 'No phone'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Selected Patient Card -->
                    <div x-show="selectedPatient" x-cloak class="mt-4 bg-indigo-50 border border-indigo-100 rounded-xl p-4 flex justify-between items-center transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-indigo-600 shadow-sm font-bold shadow-indigo-200">
                                <i class="fa-solid fa-user-check"></i>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900 leading-tight" x-text="selectedPatient?.first_name + ' ' + (selectedPatient?.last_name || '')"></div>
                                <div class="text-xs text-indigo-700 font-mono mt-0.5" x-text="'MR: ' + selectedPatient?.mr_number"></div>
                            </div>
                        </div>
                        <button type="button" @click="clearSelection()" class="text-indigo-400 hover:text-indigo-600 text-sm font-medium px-2 py-1 hover:bg-indigo-100 rounded transition-colors">
                            Change
                        </button>
                    </div>


                    <input type="hidden" name="patient_id" :value="selectedPatient?.id || ''" required>
                </div>

                <hr class="border-gray-100 my-8">

                <div x-data="{ procType: '' }">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        
                        <!-- Procedure Selection -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Recommended Procedure *</label>
                            <?php
$curr_proc = $_POST['procedure_name'] ?? ($edit_data['procedure_name'] ?? '');
$is_other = !empty($curr_proc) && !in_array($curr_proc, $common_procedures);
?>
                            <select name="procedure_name" x-model="procType" required class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 bg-white">
                                <option value="">-- Select Standard Protocol --</option>
                                <?php foreach ($common_procedures as $cp): ?>
                                    <option value="<?php echo htmlspecialchars($cp); ?>" <?php echo($curr_proc === $cp) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cp); ?>
                                    </option>
                                <?php
endforeach; ?>
                                <option value="OTHER" <?php echo $is_other ? 'selected' : ''; ?>>Other (Specify Custom Procedure)</option>
                            </select>
                        </div>
                        
                        <!-- Date -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Date Advised *</label>
                            <input type="date" name="date_advised" value="<?php echo htmlspecialchars($_POST['date_advised'] ?? ($edit_data['date_advised'] ?? date('Y-m-d'))); ?>" required class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 bg-white">
                        </div>

                    </div>

                    <!-- Custom Procedure Input -->
                    <div x-show="procType === 'OTHER' || (procType === '' && <?php echo $is_other ? 'true' : 'false'; ?>)" x-cloak class="mt-4 mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-1">Custom Procedure Name *</label>
                        <input type="text" name="other_procedure_name" value="<?php echo $is_other ? htmlspecialchars($curr_proc) : ''; ?>" class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 bg-gray-50" placeholder="Type custom treatment plan name here...">
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 mb-1">Clinical Justification & Notes</label>
                    <textarea name="notes" rows="4" class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 bg-gray-50" placeholder="Any specific requirements, expected timeline, or pre-procedure preparations..."><?php echo htmlspecialchars($_POST['notes'] ?? ($edit_data['notes'] ?? '')); ?></textarea>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100 flex items-center justify-between gap-3">
                    <a href="procedures.php" class="text-sm text-gray-500 hover:text-gray-700 font-bold flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-arrow-left text-xs"></i> Back
                    </a>
                    <button type="submit" name="save_procedure"
                            class="bg-rose-600 hover:bg-rose-700 text-white font-black py-3.5 px-8 rounded-2xl transition-all shadow-xl shadow-rose-100 active:scale-95 flex items-center gap-2.5">
                        <i class="fa-solid fa-clipboard-check"></i>
                        <?php echo $edit_id ? 'Update Record' : 'Advise Procedure'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Patient Search Script using Alpine.js -->
<script>
function patientSearch(initialPatient = null) {
    return {
        searchQuery: '',
        results: [],
        showResults: false,
        selectedPatient: initialPatient,
        
        async searchPatients() {
            if (this.searchQuery.length < 2) {
                this.results = [];
                this.showResults = false;
                return;
            }
            
            try {
                const response = await fetch(`api_search_patients.php?q=${encodeURIComponent(this.searchQuery)}`);
                const data = await response.json();
                this.results = data;
                this.showResults = true;
            } catch (error) {
                console.error('Error searching patients:', error);
            }
        },
        
        selectPatient(pt) {
            this.selectedPatient = pt;
            this.searchQuery = '';
            this.showResults = false;
        },
        
        clearSelection() {
            this.selectedPatient = null;
            setTimeout(() => { document.querySelector('input[x-model="searchQuery"]').focus(); }, 100);
        }
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
