<?php
$pageTitle = "New Semen Analysis";
require_once __DIR__ . '/includes/auth.php';

$error = '';
$pre_patient_id = $_GET['patient_id'] ?? '';
$edit_id = intval($_GET['edit'] ?? 0);
$edit_data = null;
$qrcode_hash = bin2hex(random_bytes(16));

// If editing, fetch existing record
if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM semen_analyses WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
    if ($edit_data) {
        $pre_patient_id = $edit_data['patient_id'];
        $pageTitle = "Edit Semen Analysis";
    }
    else {
        $edit_id = 0;
    }
}

// Fetch Patients
$patients = [];
try {
    $res = $conn->query("SELECT id, mr_number, first_name, last_name FROM patients ORDER BY id DESC");
    if ($res) {
        while ($row = $res->fetch_assoc())
            $patients[] = $row;
    }
}
catch (Exception $e) {
}

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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_sa'])) {
    $patient_id = intval($_POST['patient_id'] ?? 0);
    $hospital_id = intval($_POST['hospital_id'] ?? 0);
    $coll_time = $_POST['collection_time'] ?? null;
    $exam_time = $_POST['examination_time'] ?? null;
    $abstinence = intval($_POST['abstinence_days'] ?? 0);

    $volume = floatval($_POST['volume'] ?? 0);
    $ph = floatval($_POST['ph'] ?? 0);
    $conc = floatval($_POST['concentration'] ?? 0);

    $pr = floatval($_POST['pr_motility'] ?? 0);
    $np = floatval($_POST['np_motility'] ?? 0);
    $im = floatval($_POST['im_motility'] ?? 0);

    $norm = floatval($_POST['normal_morphology'] ?? 0);
    $abnorm = floatval($_POST['abnormal_morphology'] ?? 0);

    // New WHO 6th Macro/Microscopic Parameters
    $appearance = trim($_POST['appearance'] ?? 'Normal');
    $liquefaction = trim($_POST['liquefaction'] ?? 'Complete');
    $viscosity = trim($_POST['viscosity'] ?? 'Normal');
    $vitality = isset($_POST['vitality']) && $_POST['vitality'] !== '' ? floatval($_POST['vitality']) : null;
    $round_cells = trim($_POST['round_cells'] ?? '');
    $debris = trim($_POST['debris'] ?? '');

    $wbc = trim($_POST['wbc'] ?? '');
    $agglut = trim($_POST['agglutination'] ?? '');
    $auto_diag = trim($_POST['auto_diagnosis'] ?? 'Pending');
    $notes = trim($_POST['notes'] ?? '');
    $editing = intval($_POST['edit_id'] ?? 0);
    $report_type = $_POST['report_type'] ?? 'manual';
    $file_path = $edit_data['report_file_path'] ?? null;

    if ($report_type === 'file') {
        // Handle File Upload — stored inside 4me/uploads/semen_reports/
        if (isset($_FILES['report_file']) && $_FILES['report_file']['error'] == 0) {
            $upload_dir = __DIR__ . '/uploads/semen_reports/';
            if (!is_dir($upload_dir))
                mkdir($upload_dir, 0755, true);

            $ext = strtolower(pathinfo($_FILES['report_file']['name'], PATHINFO_EXTENSION));
            $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $allowed)) {
                $error = "Unsupported file type. Please upload PDF or image files.";
            }
            else {
                $filename = 'sa_' . time() . '_' . $patient_id . '.' . $ext;
                if (move_uploaded_file($_FILES['report_file']['tmp_name'], $upload_dir . $filename)) {
                    $file_path = '4me/uploads/semen_reports/' . $filename;
                }
                else {
                    $error = "File upload failed. Check folder permissions on the server.";
                }
            }
        }
        elseif (!$file_path) {
            $error = "Please select a file to upload.";
        }
        $auto_diag = 'External Report';
    }

    if (empty($patient_id)) {
        $error = "Please select a patient.";
    }
    else {

        if ($editing > 0) {
            // UPDATE mode
            $stmt = $conn->prepare("UPDATE semen_analyses SET patient_id=?, hospital_id=?, report_type=?, report_file_path=?, collection_time=?, examination_time=?, abstinence_days=?, volume=?, ph=?, concentration=?, pr_motility=?, np_motility=?, im_motility=?, normal_morphology=?, abnormal_morphology=?, appearance=?, liquefaction=?, viscosity=?, vitality=?, round_cells=?, debris=?, wbc=?, agglutination=?, auto_diagnosis=?, admin_notes=? WHERE id=?");
            if ($stmt) {
                $stmt->bind_param("iissssiddddddddsssdssssssi", $patient_id, $hospital_id, $report_type, $file_path, $coll_time, $exam_time, $abstinence, $volume, $ph, $conc, $pr, $np, $im, $norm, $abnorm, $appearance, $liquefaction, $viscosity, $vitality, $round_cells, $debris, $wbc, $agglut, $auto_diag, $notes, $editing);
                if ($stmt->execute()) {
                    flash('success', 'Semen analysis report updated successfully.');
                    header("Location: patients_view.php?id={$patient_id}&tab=semen&msg=saved");
                    exit;
                }
                else {
                    $error = "Database Error: " . $stmt->error;
                }
            }
        }
        else {
            // INSERT mode
            $stmt = $conn->prepare("INSERT INTO semen_analyses (patient_id, hospital_id, qrcode_hash, report_type, report_file_path, collection_time, examination_time, abstinence_days, volume, ph, concentration, pr_motility, np_motility, im_motility, normal_morphology, abnormal_morphology, appearance, liquefaction, viscosity, vitality, round_cells, debris, wbc, agglutination, auto_diagnosis, admin_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if ($stmt) {
                $stmt->bind_param("iisssssiddddddddsssdssssss", $patient_id, $hospital_id, $qrcode_hash, $report_type, $file_path, $coll_time, $exam_time, $abstinence, $volume, $ph, $conc, $pr, $np, $im, $norm, $abnorm, $appearance, $liquefaction, $viscosity, $vitality, $round_cells, $debris, $wbc, $agglut, $auto_diag, $notes);
                if ($stmt->execute()) {
                    flash('success', 'Semen analysis report saved successfully.');
                    header("Location: patients_view.php?id={$patient_id}&tab=semen&msg=saved");
                    exit;
                }
                else {
                    $error = "Database Error: " . $stmt->error;
                }
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="max-w-5xl mx-auto" x-data="semenEngine()">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 border-b border-gray-100 text-slate-800 flex justify-between items-center">
            <h3 class="font-bold"><i class="fa-solid fa-microscope text-teal-600 mr-2"></i> Advanced Semen Analysis Form</h3>
            <span class="text-xs bg-sky-800 px-2 py-1 rounded text-slate-500 uppercase font-bold tracking-wider">WHO 6th Edition Ready</span>
        </div>
        
        <div class="p-6 md:p-8">
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-100 flex gap-2">
                    <i class="fa-solid fa-circle-exclamation mt-1"></i> <?php echo esc($error); ?>
                </div>
            <?php
endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <?php if ($edit_id): ?><input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>"><?php
endif; ?>
                
                <!-- Report Type Toggle -->
                <div class="mb-8 p-1 bg-gray-100 rounded-xl inline-flex gap-1 border border-gray-200">
                    <button type="button" @click="reportType = 'manual'" :class="reportType === 'manual' ? 'bg-white text-sky-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="px-6 py-2 rounded-lg font-bold text-sm transition-all focus:outline-none">
                        <i class="fa-solid fa-keyboard mr-2"></i> New Analysis (Manual)
                    </button>
                    <button type="button" @click="reportType = 'file'" :class="reportType === 'file' ? 'bg-white text-sky-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="px-6 py-2 rounded-lg font-bold text-sm transition-all focus:outline-none">
                        <i class="fa-solid fa-file-upload mr-2"></i> Upload Existing Report
                    </button>
                    <input type="hidden" name="report_type" :value="reportType">
                </div>
                
                <!-- Setup Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 border-b border-gray-100 pb-8">
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Patient *</label>
                        <select name="patient_id" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 bg-gray-50" required>
                            <option value="">-- Choose Patient --</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo($pre_patient_id == $p['id']) ? 'selected' : ''; ?>>
                                    <?php echo esc($p['mr_number'] . ' | ' . $p['first_name'] . ' ' . $p['last_name']); ?>
                                </option>
                            <?php
endforeach; ?>
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Print Location (Hospital) *</label>
                        <select name="hospital_id" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 bg-gray-50">
                            <option value="1">-- IVF Experts Clinic (Default) --</option>
                            <?php foreach ($hospitals as $h): ?>
                                <option value="<?php echo $h['id']; ?>" <?php echo($edit_data && $edit_data['hospital_id'] == $h['id']) ? 'selected' : ''; ?>>
                                    <?php echo esc($h['name']); ?>
                                </option>
                            <?php
endforeach; ?>
                        </select>

                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Collection Time</label>
                        <input type="datetime-local" name="collection_time" value="<?php echo $edit_data ? date('Y-m-d\TH:i', strtotime($edit_data['collection_time'])) : date('Y-m-d\TH:i'); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Examination Time</label>
                        <input type="datetime-local" name="examination_time" value="<?php echo($edit_data && $edit_data['examination_time']) ? date('Y-m-d\TH:i', strtotime($edit_data['examination_time'])) : ''; ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Abstinence (Days)</label>
                        <input type="number" name="abstinence_days" value="<?php echo $edit_data['abstinence_days'] ?? ''; ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-sky-500">
                    </div>
                </div>

                <!-- File Selection (Only in File Mode) -->
                <div x-show="reportType === 'file'" x-cloak class="mb-8 p-8 bg-sky-50 rounded-2xl border-2 border-dashed border-sky-200 text-center">
                    <div class="mb-4">
                        <i class="fa-solid fa-cloud-arrow-up text-4xl text-teal-600"></i>
                    </div>
                    <label class="block text-lg font-bold text-sky-900 mb-2">Select Report File (PDF/Image)</label>
                    <p class="text-sm text-sky-600 mb-6 font-medium">Please upload the complete report obtained from an external laboratory.</p>
                    
                    <div class="max-w-xs mx-auto">
                        <input type="file" name="report_file" accept="image/*,application/pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-sky-600 file:text-white hover:file:bg-sky-700 transition-all shadow-sm">
                    </div>
                    
                    <?php if ($edit_data && $edit_data['report_file_path']): ?>
                        <div class="mt-6 pt-6 border-t border-sky-100">
                             <a href="../<?php echo esc($edit_data['report_file_path']); ?>" target="_blank" class="text-sky-700 text-sm font-bold flex items-center justify-center gap-2 hover:underline">
                                <i class="fa-solid fa-eye"></i> View Current Uploaded Report
                             </a>
                        </div>
                    <?php
endif; ?>
                </div>

                <div x-show="reportType === 'manual'">
                    <!-- Live Evaluator Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    
                    <!-- Macroscopic & Basic -->
                    <div>
                        <h4 class="font-bold text-gray-800 border-b border-gray-100 pb-2 mb-4">Core Parameters</h4>
                        
                        <div class="space-y-4">
                            <!-- Appearance -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-gray-700 w-1/3">Appearance</label>
                                <div class="w-1/3">
                                    <select name="appearance" class="w-full px-3 py-2 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-sky-500">
                                        <option value="Normal" <?php echo($edit_data && $edit_data['appearance'] == 'Normal') ? 'selected' : ''; ?>>Normal</option>
                                        <option value="Abnormal" <?php echo($edit_data && $edit_data['appearance'] == 'Abnormal') ? 'selected' : ''; ?>>Abnormal</option>
                                    </select>
                                </div>
                                <div class="w-1/3 text-right text-xs text-gray-400 font-mono">Normal</div>
                            </div>
                            
                            <!-- Liquefaction -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-gray-700 w-1/3">Liquefaction</label>
                                <div class="w-1/3">
                                    <select name="liquefaction" class="w-full px-3 py-2 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-sky-500">
                                        <option value="Complete" <?php echo($edit_data && $edit_data['liquefaction'] == 'Complete') ? 'selected' : ''; ?>>Complete (< 60m)</option>
                                        <option value="Incomplete" <?php echo($edit_data && $edit_data['liquefaction'] == 'Incomplete') ? 'selected' : ''; ?>>Incomplete</option>
                                    </select>
                                </div>
                                <div class="w-1/3 text-right text-xs text-gray-400 font-mono">< 60 min</div>
                            </div>

                            <!-- Viscosity -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-gray-700 w-1/3">Viscosity</label>
                                <div class="w-1/3">
                                    <select name="viscosity" class="w-full px-3 py-2 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-sky-500">
                                        <option value="Normal" <?php echo($edit_data && $edit_data['viscosity'] == 'Normal') ? 'selected' : ''; ?>>Normal</option>
                                        <option value="Abnormal" <?php echo($edit_data && $edit_data['viscosity'] == 'Abnormal') ? 'selected' : ''; ?>>Abnormal (High)</option>
                                    </select>
                                </div>
                                <div class="w-1/3 text-right text-xs text-gray-400 font-mono">Normal</div>
                            </div>

                            <!-- Volume -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-gray-700 w-1/3">Semen Volume</label>
                                <div class="w-1/3 relative">
                                    <input type="number" step="0.1" name="volume" x-model.number="volume" class="w-full px-3 py-2 border border-gray-200 rounded focus:ring-1 focus:ring-sky-500 font-mono text-right" :class="isRed(volume, 1.4) ? 'text-red-600 bg-red-50 border-red-300' : ''">
                                    <span class="absolute right-3 top-2 text-xs text-gray-400">ml</span>
                                </div>
                                <div class="w-1/3 text-right text-xs text-gray-400 font-mono">≥ 1.4 ml</div>
                            </div>
                            
                            <!-- pH -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-gray-700 w-1/3">pH</label>
                                <div class="w-1/3">
                                    <input type="number" step="0.1" name="ph" x-model.number="ph" class="w-full px-3 py-2 border border-gray-200 rounded focus:ring-1 focus:ring-sky-500 font-mono text-right" :class="isRed(ph, 7.2) ? 'text-red-600 bg-red-50 border-red-300' : ''">
                                </div>
                                <div class="w-1/3 text-right text-xs text-gray-400 font-mono">≥ 7.2</div>
                            </div>
                            
                            <!-- Concentration -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-gray-700 w-1/3 border-b-2 border-transparent hover:border-sky-300">Concentration</label>
                                <div class="w-1/3 relative">
                                    <input type="number" step="0.1" name="concentration" x-model.number="conc" class="w-full px-3 py-2 border border-gray-200 rounded focus:ring-1 focus:ring-sky-500 font-mono text-right font-bold text-sky-800" :class="isRed(conc, 16) ? 'text-red-600 bg-red-50 border-red-300' : ''">
                                    <span class="absolute right-2 top-2 text-[10px] text-gray-400 leading-tight">M/ml</span>
                                </div>
                                <div class="w-1/3 text-right text-xs text-gray-400 font-mono">≥ 16 M/ml</div>
                            </div>

                            <div class="pt-4 border-t border-gray-100 grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Pus Cells (WBC)</label>
                                    <input type="text" name="wbc" value="<?php echo esc($edit_data['wbc'] ?? ''); ?>" placeholder="e.g. 1-2 / HPF" class="w-full px-3 py-2 border border-gray-200 rounded text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Round Cells</label>
                                    <input type="text" name="round_cells" value="<?php echo esc($edit_data['round_cells'] ?? ''); ?>" placeholder="e.g. < 1M/ml" class="w-full px-3 py-2 border border-gray-200 rounded text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Agglutination</label>
                                    <input type="text" name="agglutination" value="<?php echo esc($edit_data['agglutination'] ?? ''); ?>" placeholder="e.g. None, ++" class="w-full px-3 py-2 border border-gray-200 rounded text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Debris</label>
                                    <input type="text" name="debris" value="<?php echo esc($edit_data['debris'] ?? ''); ?>" placeholder="e.g. Occasional" class="w-full px-3 py-2 border border-gray-200 rounded text-sm">
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Motility & Morphology -->
                    <div>
                        <h4 class="font-bold text-gray-800 border-b border-gray-100 pb-2 mb-4">Motility & Morphology</h4>
                        
                        <div class="space-y-4">
                            <!-- PR -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-gray-700 w-1/3">Progressive (PR)</label>
                                <div class="w-1/3 relative">
                                    <input type="number" step="1" name="pr_motility" x-model.number="pr" class="w-full px-3 py-2 border border-gray-200 rounded focus:ring-1 focus:ring-sky-500 font-mono text-right" :class="isRed(pr, 30) ? 'text-red-600 bg-red-50 border-red-300' : ''">
                                    <span class="absolute right-3 top-2 text-xs text-gray-400">%</span>
                                </div>
                                <div class="w-1/3 text-right text-xs text-gray-400 font-mono">≥ 30 %</div>
                            </div>
                            
                            <!-- NP -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-gray-700 w-1/3">Non-Progress (NP)</label>
                                <div class="w-1/3 relative">
                                    <input type="number" step="1" name="np_motility" x-model.number="np" class="w-full px-3 py-2 border border-gray-200 rounded font-mono text-right focus:ring-1 focus:ring-sky-500">
                                    <span class="absolute right-3 top-2 text-xs text-gray-400">%</span>
                                </div>
                                <div class="w-1/3 text-right"></div>
                            </div>

                            <!-- IM -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-gray-700 w-1/3">Immotility (IM)</label>
                                <div class="w-1/3 relative">
                                    <input type="number" step="1" name="im_motility" :value="im()" readonly class="w-full px-3 py-2 border border-gray-100 rounded text-gray-500 bg-gray-50 font-mono text-right cursor-not-allowed">
                                    <span class="absolute right-3 top-2 text-xs text-gray-400">%</span>
                                </div>
                                <div class="w-1/3 text-right text-xs text-gray-400 italic">Auto</div>
                            </div>

                            <!-- Total Motility -->
                            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                <label class="text-sm font-bold text-sky-800 w-1/3">Total Motility (PR+NP)</label>
                                <div class="w-1/3 text-right font-mono font-bold text-lg" :class="isRed(pr + np, 42) ? 'text-red-600' : 'text-sky-700'" x-text="(pr + np) + ' %'"></div>
                                <div class="w-1/3 text-right text-xs text-gray-400 font-mono">≥ 42 %</div>
                            </div>

                            <div class="flex items-center justify-between mt-4">
                                <label class="text-sm font-medium text-gray-700 w-1/3">Vitality (Live %)</label>
                                <div class="w-1/3 relative">
                                    <input type="number" step="1" name="vitality" x-model.number="vitality" class="w-full px-3 py-2 border border-gray-200 rounded focus:ring-1 focus:ring-sky-500 font-mono text-right" :class="isRed(vitality, 54) ? 'text-red-600 bg-red-50 border-red-300' : ''">
                                    <span class="absolute right-3 top-2 text-xs text-gray-400">%</span>
                                </div>
                                <div class="w-1/3 text-right text-xs text-gray-400 font-mono">≥ 54 %</div>
                            </div>

                            <h4 class="font-bold text-gray-800 border-b border-gray-100 pb-2 mt-6 mb-4">Morphology</h4>

                            <!-- Normal Form -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-gray-700 w-1/3">Normal Forms</label>
                                <div class="w-1/3 relative">
                                    <input type="number" step="1" name="normal_morphology" x-model.number="norm" class="w-full px-3 py-2 border border-gray-200 rounded focus:ring-1 focus:ring-sky-500 font-mono text-right" :class="isRed(norm, 4) ? 'text-red-600 bg-red-50 border-red-300' : ''">
                                    <span class="absolute right-3 top-2 text-xs text-gray-400">%</span>
                                </div>
                                <div class="w-1/3 text-right text-xs text-gray-400 font-mono">≥ 4 %</div>
                            </div>
                            
                            <!-- Abnormal Form -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-gray-700 w-1/3">Abnormal Forms</label>
                                <div class="w-1/3 relative">
                                    <input type="number" step="1" name="abnormal_morphology" :value="100 - norm" readonly class="w-full px-3 py-2 border border-gray-100 bg-gray-50 rounded text-gray-500 font-mono text-right cursor-not-allowed">
                                    <span class="absolute right-3 top-2 text-xs text-gray-400">%</span>
                                </div>
                                <div class="w-1/3 text-right text-xs text-gray-400 italic">Auto</div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Auto Diagnosis -->
                <div class="bg-sky-50 rounded-xl p-6 border border-sky-100 mb-8 flex flex-col md:flex-row items-center justify-between gap-4 shadow-inner">
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-sky-800 mb-1 uppercase tracking-wider">Automated Diagnosis Output</label>
                        <input type="text" name="auto_diagnosis" x-model="diagnosis" readonly class="w-full bg-transparent text-xl md:text-2xl font-bold text-gray-900 focus:outline-none cursor-default truncate">
                        <p class="text-xs text-sky-600 mt-1">Extrapolated via JS mapping directly against WHO 6th standard reference guidelines.</p>
                    </div>
                    <div class="shrink-0 text-3xl opacity-20 text-sky-900 hidden md:block">
                        <i class="fa-solid fa-brain"></i>
                    </div>
                </div>

                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Clinical Embryologist Remarks</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-sky-500"><?php echo esc($edit_data['admin_notes'] ?? ''); ?></textarea>
                </div>
                
                <div class="flex justify-end gap-3 mt-8">
                    <a href="semen_analyses.php" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-6 py-3 rounded-lg font-medium transition-colors">Cancel</a>
                    <button type="submit" name="save_sa" class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-3 px-8 rounded-lg shadow-md hover:shadow-lg transition-all focus:outline-none flex items-center gap-2">
                        <i class="fa-solid fa-file-signature"></i> <?php echo $edit_id ? 'Update Report' : 'Finalize Report'; ?>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('semenEngine', () => ({
        volume: <?php echo $edit_data['volume'] ?? 0; ?>,
        ph: <?php echo $edit_data['ph'] ?? 0; ?>,
        conc: <?php echo $edit_data['concentration'] ?? 0; ?>,
        pr: <?php echo $edit_data['pr_motility'] ?? 0; ?>,
        np: <?php echo $edit_data['np_motility'] ?? 0; ?>,
        norm: <?php echo $edit_data['normal_morphology'] ?? 0; ?>,
        vitality: <?php echo $edit_data['vitality'] ?? 0; ?>,
        
        // Compute Immotility automatically (100 - PR - NP)
        im() {
            let res = 100 - this.pr - this.np;
            return res < 0 ? 0 : res;
        },

        // Red light engine based on WHO
        isRed(val, ref) {
            // Only trigger red if a number is actually entered and it's below reference
            if (val === 0 || val === "") return false;
            return val < ref;
        },

        // The Smart Diagnosis Array map
        get diagnosis() {
            if (this.conc === 0 && this.volume === 0) return 'Pending Entry...';
            
            let diagList = [];
            
            // Azoospermia rules out everything else
            if (this.conc === 0) {
                return 'Azoospermia';
            }
            
            if (this.conc > 0 && this.conc < 16) diagList.push('Oligozoospermia');
            if (this.pr < 30 || (this.pr + this.np) < 42) diagList.push('Asthenozoospermia');
            if (this.norm < 4) diagList.push('Teratozoospermia');
            
            if (diagList.length === 0) {
                return 'Normozoospermia';
            } else if (diagList.length === 3) {
                return 'Oligoasthenoteratozoospermia (OAT)';
            } else {
                return diagList.join(', ');
            }
        }
    }))
})
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
