<?php
require_once __DIR__ . '/includes/auth.php';

$error = '';
$success = '';
$edit_id = intval($_GET['edit'] ?? 0);
$edit_data = null;

if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT plt.*, pt.first_name, pt.last_name, pt.mr_number 
                            FROM patient_lab_results plt 
                            JOIN patients pt ON plt.patient_id = pt.id 
                            WHERE plt.id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
    if (!$edit_data) {
        header("Location: lab_results.php?error=NotFound");
        exit;
    }
}

// Check if directories exist
$upload_dir = dirname(__DIR__) . '/uploads/labs/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Fetch all defined tests, grouped by category
$tests = [];
$res = $conn->query("SELECT id, test_name, unit, reference_range_male, reference_range_female, category FROM lab_tests_directory ORDER BY category ASC, test_name ASC");
if ($res) {
    while ($row = $res->fetch_assoc())
        $tests[] = $row;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_lab_result'])) {

    $patient_id = intval($_POST['patient_id'] ?? 0);
    $test_id = intval($_POST['test_id'] ?? 0);
    $result_value = trim($_POST['result_value'] ?? '');
    $test_date = trim($_POST['test_date'] ?? date('Y-m-d'));

    $lab_name = trim($_POST['lab_name'] ?? '');
    $lab_city = trim($_POST['lab_city'] ?? '');
    $lab_mr_number = trim($_POST['lab_mr_number'] ?? '');
    $current_edit_id = intval($_POST['edit_id'] ?? 0);

    if ($patient_id === 0 || $test_id === 0 || (empty($result_value) && $_POST['status'] === 'Completed')) {
        $error = "Patient, Test, and Result Value are required.";
    }
    else {
        $test_for = $_POST['test_for'] ?? 'Patient';
        $status = $_POST['status'] ?? 'Completed';
        $file_path = $_POST['existing_file'] ?? null;

        // Handle File Upload
        if (isset($_FILES['scanned_report']) && $_FILES['scanned_report']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['scanned_report']['tmp_name'];
            $file_name = $_FILES['scanned_report']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png'];
            if (!in_array($file_ext, $allowed_exts)) {
                $error = "Invalid file format. Only PDF, JPG, and PNG are allowed.";
            }
            else {
                $new_file_name = "lab_pt{$patient_id}_test{$test_id}_" . time() . '.' . $file_ext;
                $dest_path = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp, $dest_path)) {
                    // Delete old file if updating
                    if (!empty($file_path) && file_exists(dirname(__DIR__) . '/' . $file_path)) {
                        unlink(dirname(__DIR__) . '/' . $file_path);
                    }
                    $file_path = 'uploads/labs/' . $new_file_name;
                }
                else {
                    $error = "Failed to save the uploaded file.";
                }
            }
        }

        if (empty($error)) {
            try {
                if ($current_edit_id > 0) {
                    $stmt = $conn->prepare("UPDATE patient_lab_results SET 
                        patient_id = ?, lab_city = ?, lab_name = ?, lab_mr_number = ?, 
                        test_date = ?, test_id = ?, test_for = ?, result_value = ?, 
                        status = ?, scanned_report_path = ? 
                        WHERE id = ?");
                    $stmt->bind_param("issssissssi",
                        $patient_id, $lab_city, $lab_name, $lab_mr_number,
                        $test_date, $test_id, $test_for, $result_value, $status, $file_path, $current_edit_id
                    );
                }
                else {
                    $stmt = $conn->prepare("INSERT INTO patient_lab_results 
                        (patient_id, lab_city, lab_name, lab_mr_number, test_date, test_id, test_for, result_value, status, scanned_report_path) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("isssissss",
                        $patient_id, $lab_city, $lab_name, $lab_mr_number,
                        $test_date, $test_id, $test_for, $result_value, $status, $file_path
                    );
                }

                if ($stmt->execute()) {
                    // Redirect back to patient view if we know the patient_id
                    $redirect_pid = intval($_POST['patient_id'] ?? 0);
                    flash('success', 'Lab result saved successfully.');
                    if ($redirect_pid > 0) {
                        header("Location: patients_view.php?id={$redirect_pid}&tab=labs&msg=lab_saved");
                    }
                    else {
                        header("Location: lab_results.php?success=1");
                    }
                    exit;
                }
                else {
                    $error = "Database operation failed: " . $stmt->error;
                }
            }
            catch (Exception $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Pre-select patient when coming from patients_view.php via ?patient_id=X
$preselected_patient = null;
if (!$edit_data && !empty($_GET['patient_id'])) {
    $pid_pre = intval($_GET['patient_id']);
    $pst = $conn->prepare("SELECT id, first_name, last_name, mr_number, gender FROM patients WHERE id = ?");
    $pst->bind_param("i", $pid_pre);
    $pst->execute();
    $preselected_patient = $pst->get_result()->fetch_assoc() ?: null;
}

$pageTitle = $edit_id ? 'Edit Lab Result' : 'Record New Lab Result';
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <a href="lab_results.php" class="text-sm text-gray-500 hover:text-indigo-600 font-medium flex items-center gap-1">
            <i class="fa-solid fa-arrow-left"></i> Back to Results
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h3 class="font-bold text-gray-800"><?php echo $edit_id ? 'Edit' : 'Add New'; ?> Laboratory Result</h3>
        </div>

        <div class="p-6 md:p-8" x-data="labForm(<?php
if ($edit_data) {
    echo json_encode([
        'patient' => ['id' => $edit_data['patient_id'], 'mr_number' => $edit_data['mr_number'], 'first_name' => $edit_data['first_name'], 'last_name' => $edit_data['last_name'], 'gender' => $edit_data['gender']],
        'test_id' => $edit_data['test_id'],
        'test_for' => $edit_data['test_for'],
        'status' => $edit_data['status']
    ]);
}
elseif ($preselected_patient) {
    echo json_encode([
        'patient' => $preselected_patient,
        'test_id' => '',
        'test_for' => 'Patient',
        'status' => 'Completed'
    ]);
}
else {
    echo 'null';
}
?>, <?php echo json_encode($tests); ?>)">
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-100 flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php
endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <?php if ($edit_id): ?>
                    <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
                    <input type="hidden" name="existing_file" value="<?php echo esc($edit_data['scanned_report_path']); ?>">
                <?php
endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <!-- Patient Selection -->
                    <div class="relative">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Select Patient *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-search text-gray-400"></i>
                            </div>
                            <input type="text" x-model="searchQuery" @input.debounce.300ms="searchPatients()" placeholder="Search MR, Name, Phone..." class="w-full pl-10 px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 bg-gray-50" autocomplete="off">

                            <!-- Search Results Dropdown -->
                            <div x-show="results.length > 0 && showResults" @click.away="showResults = false" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto" x-cloak>
                                <template x-for="pt in results" :key="pt.id">
                                    <div @click="selectPatient(pt)" class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-50 last:border-0">
                                        <div class="font-bold text-gray-800" x-text="pt.first_name + ' ' + (pt.last_name || '')"></div>
                                        <div class="text-xs text-gray-500 mt-1 flex gap-3">
                                            <span class="text-indigo-600 font-mono font-medium" x-text="pt.mr_number"></span>
                                            <span x-text="pt.gender"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Selected Patient Card -->
                        <div x-show="selectedPatient" x-cloak class="mt-4 bg-indigo-50 border border-indigo-100 rounded-xl p-4 flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-indigo-600 shadow-sm font-bold shadow-indigo-200">
                                    <i class="fa-solid fa-user-check"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-gray-900 leading-tight" x-text="selectedPatient?.first_name + ' ' + (selectedPatient?.last_name || '')"></div>
                                    <div class="text-[10px] text-indigo-700 font-bold uppercase" x-text="selectedPatient?.gender + ' | MR: ' + selectedPatient?.mr_number"></div>
                                </div>
                            </div>
                            <button type="button" @click="clearSelection()" class="text-indigo-400 hover:text-indigo-600 text-xs font-bold">Change</button>
                        </div>
                        <input type="hidden" name="patient_id" :value="selectedPatient?.id || ''" required>
                    </div>

                    <!-- Who is this for? -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Who is this lab for? *</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="test_for" value="Patient" x-model="test_for" class="peer sr-only">
                                <div class="p-3 text-center border rounded-xl transition-all peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 border-gray-200 text-gray-600 hover:bg-gray-50">
                                    <div class="font-bold">Main Patient</div>
                                    <div class="text-[10px] opacity-80" x-text="selectedPatient ? '(' + selectedPatient.gender + ')' : ''"></div>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="test_for" value="Spouse" x-model="test_for" class="peer sr-only">
                                <div class="p-3 text-center border rounded-xl transition-all peer-checked:bg-pink-600 peer-checked:text-white peer-checked:border-pink-600 border-gray-200 text-gray-600 hover:bg-gray-50">
                                    <div class="font-bold">Spouse</div>
                                    <div class="text-[10px] opacity-80" x-text="selectedPatient ? '(' + (selectedPatient.gender === 'Male' ? 'Female' : 'Male') + ')' : ''"></div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100 my-8">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- Test Selection -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-1">Select Laboratory Test *</label>
                        <select name="test_id" x-model="test_id" required class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 bg-white">
                            <option value="">-- Choose Test --</option>
                            <?php
$currentCat = null;
foreach ($tests as $t):
    $cat = $t['category'] ?: 'Other';
    if ($cat !== $currentCat):
        if ($currentCat !== null)
            echo '</optgroup>';
        echo '<optgroup label="' . htmlspecialchars($cat) . '">';
        $currentCat = $cat;
    endif;
?>
                                <option value="<?php echo $t['id']; ?>">
                                    <?php echo htmlspecialchars($t['test_name']); ?>
                                </option>
                            <?php
endforeach;
if ($currentCat !== null)
    echo '</optgroup>'; ?>
                        </select>
                        <div class="mt-2 text-xs bg-gray-50 p-2 rounded border border-gray-100 flex justify-between" x-show="test_id > 0">
                            <div>
                                <span class="text-gray-400 font-bold uppercase tracking-wider mr-2">Expected Range:</span>
                                <span class="text-indigo-700 font-bold" x-text="currentRefRange()"></span>
                                <span class="text-gray-500 ml-1 font-mono" x-text="currentUnit()"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Report Status -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Report Status *</label>
                        <select name="status" x-model="status" class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 bg-white text-sm font-bold" :class="status === 'Completed' ? 'text-emerald-600' : 'text-orange-500'">
                            <option value="Completed">Completed / Result Available</option>
                            <option value="Pending">Pending / Awaiting Result</option>
                        </select>
                    </div>

                    <!-- Result Value -->
                    <div x-show="status === 'Completed'">
                        <label class="block text-sm font-bold text-slate-700 mb-1">Result Value *</label>
                        <input type="text" name="result_value" value="<?php echo htmlspecialchars($_POST['result_value'] ?? ($edit_data['result_value'] ?? '')); ?>" :required="status === 'Completed'" class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 bg-gray-50 font-bold text-lg" placeholder="e.g. 2.45">
                    </div>

                    <!-- Test Date -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Test Performance Date *</label>
                        <input type="date" name="test_date" value="<?php echo htmlspecialchars($_POST['test_date'] ?? ($edit_data['test_date'] ?? date('Y-m-d'))); ?>" required class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 bg-white">
                    </div>

                </div>

                <hr class="border-gray-100 my-8">

                <!-- Lab Details -->
                <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2"><i class="fa-solid fa-building text-gray-400"></i> External Laboratory Details</h4>
                <p class="text-xs text-gray-500 mb-4">If the test was performed externally, record the details for correlation.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Lab Name</label>
                        <input type="text" name="lab_name" value="<?php echo htmlspecialchars($_POST['lab_name'] ?? ($edit_data['lab_name'] ?? '')); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="e.g. Chughtai Lab">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Lab City</label>
                        <input type="text" name="lab_city" value="<?php echo htmlspecialchars($_POST['lab_city'] ?? ($edit_data['lab_city'] ?? '')); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="e.g. Lahore">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Lab Patient MR / ID</label>
                        <input type="text" name="lab_mr_number" value="<?php echo htmlspecialchars($_POST['lab_mr_number'] ?? ($edit_data['lab_mr_number'] ?? '')); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="External lab tracking ID">
                    </div>
                </div>

                <hr class="border-gray-100 my-8">

                <!-- File Attachment -->
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 bg-gray-50 text-center hover:border-indigo-300 transition-colors">
                    <?php if (!empty($edit_data['scanned_report_path'])): ?>
                        <div class="mb-4 inline-block bg-white p-3 rounded-lg shadow-sm border border-indigo-100">
                            <div class="flex items-center gap-3 text-left">
                                <div class="w-10 h-10 bg-indigo-50 rounded flex items-center justify-center text-indigo-600">
                                    <i class="fa-solid fa-file-invoice text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-gray-800">Current Attachment</div>
                                    <a href="../<?php echo esc($edit_data['scanned_report_path']); ?>" target="_blank" class="text-xs text-indigo-600 hover:underline">View logic_report.<?php echo pathinfo($edit_data['scanned_report_path'], PATHINFO_EXTENSION); ?></a>
                                </div>
                            </div>
                        </div>
                    <?php
endif; ?>
                    
                    <i class="fa-solid fa-cloud-arrow-up text-4xl text-gray-400 mb-3 block"></i>
                    <h4 class="font-bold text-gray-800 mb-1"><?php echo $edit_id ? 'Replace' : 'Attach'; ?> Scanned Report</h4>
                    <p class="text-xs text-gray-500 mb-4">Upload the PDF, JPG, or PNG of the physical lab report (Optional).</p>
                    <input type="file" name="scanned_report" accept=".pdf, .jpg, .jpeg, .png" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end gap-3">
                    <a href="lab_results.php" class="px-6 py-3 font-medium text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors border border-gray-200 shadow-sm">
                        Cancel
                    </a>
                    <button type="submit" name="save_lab_result" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg transition-colors shadow-lg shadow-indigo-200 flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> <?php echo $edit_id ? 'Update Result' : 'Save Lab Result'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lab Form Logic using Alpine.js -->
<script>
function labForm(initialData = null, allTests = []) {
    return {
        searchQuery: '',
        results: [],
        showResults: false,
        selectedPatient: initialData ? initialData.patient : null,
        test_id: initialData ? initialData.test_id : '',
        test_for: initialData ? initialData.test_for : 'Patient',
        status: initialData ? initialData.status : 'Completed',
        tests: allTests,
        
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
        },

        currentTest() {
            return this.tests.find(t => t.id == this.test_id);
        },

        currentRefRange() {
            const test = this.currentTest();
            if (!test) return '-';
            
            // Determine target gender
            let targetGender = 'Male';
            if (this.test_for === 'Patient') {
                targetGender = this.selectedPatient ? this.selectedPatient.gender : 'Male';
            } else {
                // Spouse is opposite of patient
                targetGender = (this.selectedPatient && this.selectedPatient.gender === 'Male') ? 'Female' : 'Male';
            }

            if (targetGender === 'Male') {
                return test.reference_range_male || 'N/A';
            } else {
                return test.reference_range_female || 'N/A';
            }
        },

        currentUnit() {
            const test = this.currentTest();
            return test ? test.unit : '';
        }
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
