<?php
$pageTitle = "Write Ultrasound Report";
require_once __DIR__ . '/includes/auth.php';

$error = '';
$success = '';
$pre_patient_id = $_GET['patient_id'] ?? '';
$edit_id = intval($_GET['edit'] ?? 0);
$edit_data = null;

// Generate QR hash
$qrcode_hash = bin2hex(random_bytes(16));

// If editing, fetch existing record
if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT u.*, p.first_name, p.last_name, p.mr_number FROM patient_ultrasounds u JOIN patients p ON u.patient_id = p.id WHERE u.id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
    if ($edit_data) {
        $pre_patient_id = $edit_data['patient_id'];
        $pageTitle = "Edit Ultrasound Report";
    }
    else {
        $edit_id = 0;
    }
}

// (Patients array removed; doing AJAX search instead to boost speed on large DBs)

$hospitals = [];
$res = $conn->query("SELECT id, name FROM hospitals ORDER BY name ASC");
if ($res) {
    while ($row = $res->fetch_assoc())
        $hospitals[] = $row;
}

$templates = [];
$res = $conn->query("SELECT id, title, body FROM ultrasound_templates ORDER BY title ASC");
if ($res) {
    while ($row = $res->fetch_assoc())
        $templates[] = $row;
}

// Save Ultrasound
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_usg'])) {
    $patient_id = intval($_POST['patient_id'] ?? 0);
    $hospital_id = intval($_POST['hospital_id'] ?? 0);
    $report_title = trim($_POST['report_title'] ?? 'Ultrasound Report');
    $content = trim($_POST['content'] ?? '');
    $editing = intval($_POST['edit_id'] ?? 0);

    if (empty($patient_id) || empty($hospital_id) || empty($content)) {
        $error = "Patient, Hospital, and Report Content are required.";
    }
    else {
        // Handle file upload
        $scanned_path = null;
        $scan_location = $_POST['scan_location'] ?? null;
        $scan_timestamp = null;
        $record_for = $_POST['record_for'] ?? 'Patient';

        if (isset($_FILES['scanned_report']) && $_FILES['scanned_report']['error'] == UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
            if (in_array($_FILES['scanned_report']['type'], $allowed_types)) {
                $upload_dir = __DIR__ . '/../uploads/scans/usg/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $ext = pathinfo($_FILES['scanned_report']['name'], PATHINFO_EXTENSION);
                $filename = 'usg_' . date('Ymd_His') . '_' . uniqid() . '.' . $ext;
                $dest = $upload_dir . $filename;

                if (move_uploaded_file($_FILES['scanned_report']['tmp_name'], $dest)) {
                    $scanned_path = 'uploads/scans/usg/' . $filename;
                    $scan_timestamp = date('Y-m-d H:i:s');
                }
            }
            else {
                $error = "Only JPG, PNG, and PDF files are allowed for scanned reports.";
            }
        }

        if (empty($error)) {
            if ($editing > 0) {
                // UPDATE mode
                if ($scanned_path) {
                    $stmt = $conn->prepare("UPDATE patient_ultrasounds SET patient_id=?, hospital_id=?, report_title=?, content=?, scanned_report_path=?, scan_timestamp=?, scan_location_data=?, record_for=? WHERE id=?");
                    $stmt->bind_param("iissssssi", $patient_id, $hospital_id, $report_title, $content, $scanned_path, $scan_timestamp, $scan_location, $record_for, $editing);
                }
                else {
                    $stmt = $conn->prepare("UPDATE patient_ultrasounds SET patient_id=?, hospital_id=?, report_title=?, content=?, record_for=? WHERE id=?");
                    $stmt->bind_param("iisssi", $patient_id, $hospital_id, $report_title, $content, $record_for, $editing);
                }
                if ($stmt && $stmt->execute()) {
                    flash('success', 'Ultrasound report saved successfully.');
                    header("Location: patients_view.php?id={$patient_id}&tab=usg&msg=usg_saved");
                    exit;
                }
                else {
                    $error = "Database Error: " . ($stmt ? $stmt->error : $conn->error);
                }
            }
            else {
                // INSERT mode
                $stmt = $conn->prepare("INSERT INTO patient_ultrasounds (patient_id, hospital_id, qrcode_hash, report_title, content, scanned_report_path, scan_timestamp, scan_location_data, record_for) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("iissssss s", $patient_id, $hospital_id, $qrcode_hash, $report_title, $content, $scanned_path, $scan_timestamp, $scan_location, $record_for);
                    if ($stmt->execute()) {
                        flash('success', 'Ultrasound report saved successfully.');
                        header("Location: patients_view.php?id={$patient_id}&tab=usg&msg=usg_saved");
                        exit;
                    }
                    else {
                        $error = "Database Error: " . $stmt->error;
                    }
                }
                else {
                    $error = "Statement preparation failed: " . $conn->error;
                }
            }
        }
    }
}

// Map templates to JS
$templates_json = json_encode(array_column($templates, 'body', 'id'));

// Pre-select patient for new forms when coming from patients_view.php via ?patient_id=X
$pre_patient_init = null;
if ($edit_data) {
    $pre_patient_init = ['id' => $edit_data['patient_id'], 'name' => $edit_data['first_name'] . ' ' . $edit_data['last_name'], 'mr' => $edit_data['mr_number']];
}
elseif (!empty($_GET['patient_id'])) {
    $pid_pre = intval($_GET['patient_id']);
    $pst = $conn->prepare("SELECT id, first_name, last_name, mr_number FROM patients WHERE id = ?");
    $pst->bind_param("i", $pid_pre);
    $pst->execute();
    $pre = $pst->get_result()->fetch_assoc();
    if ($pre) {
        $pre_patient_init = ['id' => $pre['id'], 'name' => $pre['first_name'] . ' ' . $pre['last_name'], 'mr' => $pre['mr_number']];
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- Include TinyMCE -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: "#usg_content",
    plugins: "lists link table code preview",
    toolbar: "undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | table | preview",
    menubar: false,
    height: 600
  });
</script>

<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <h3 class="font-bold text-gray-800"><i class="fa-solid fa-image text-sky-600 mr-2"></i> <?php echo $edit_id ? 'Edit' : 'New'; ?> Ultrasound Report</h3>
        </div>
        
        <div class="p-6">
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-100 flex gap-2">
                    <i class="fa-solid fa-circle-exclamation mt-1"></i> <?php echo esc($error); ?>
                </div>
            <?php
endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <?php if ($edit_id): ?><input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>"><?php
endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    <!-- AJAX Patient Search -->
                    <div class="lg:col-span-1 relative" x-data="patientSearch(<?php echo json_encode($pre_patient_init); ?>)">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search Patient (MR / Phone / Name) *</label>
                        <input type="hidden" name="patient_id" :value="selectedPatientId" required>
                        <div class="relative">
                            <input type="text" x-model="query" @input.debounce.300ms="search" placeholder="Type to search..." 
                                class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 transition-colors" 
                                :class="selectedPatientId ? 'bg-sky-50 border-sky-300 font-bold text-sky-800 shadow-inner' : ''" autocomplete="off">
                            <div x-show="isLoading" class="absolute right-3 top-3.5 text-sky-500">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        
                        <!-- Dropdown -->
                        <div x-show="results.length > 0 && !selectedPatientId" @click.away="results = []" class="absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-2xl overflow-hidden max-h-64 overflow-y-auto">
                            <template x-for="p in results" :key="p.id">
                                <div @click="selectPatient(p)" class="px-4 py-3 border-b border-gray-100 hover:bg-sky-50 cursor-pointer transition-colors">
                                    <div class="font-bold text-gray-800 flex justify-between">
                                        <span x-text="p.first_name + ' ' + (p.last_name || '')"></span>
                                        <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded border border-gray-200" x-text="p.gender"></span>
                                    </div>
                                    <div class="text-xs text-gray-500 flex gap-3 mt-1.5">
                                        <span class="font-mono text-sky-700 font-bold bg-sky-100 px-1.5 py-0.5 rounded" x-text="'MR: ' + p.mr_number"></span>
                                        <span x-html="'<i class=\'fa-solid fa-phone text-[9px]\'></i> ' + (p.phone || 'N/A')" class="flex items-center gap-1"></span>
                                        <span x-text="'CNIC: ' + (p.cnic || 'N/A')"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div x-show="selectedPatientId" class="text-xs text-red-500 mt-2 font-medium cursor-pointer hover:underline flex items-center gap-1 w-max" @click="clearSelection()">
                            <i class="fa-solid fa-times-circle"></i> Clear Selection
                        </div>
                    </div>

                    <!-- Hospital -->
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hospital (Print Location) *</label>
                        <select name="hospital_id" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500" required>
                            <?php foreach ($hospitals as $h): ?>
                                <option value="<?php echo $h['id']; ?>" <?php echo($edit_data && $edit_data['hospital_id'] == $h['id']) ? 'selected' : ''; ?>><?php echo esc($h['name']); ?></option>
                            <?php
endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Load Template -->
                    <div class="lg:col-span-1 border-l pl-6 border-gray-100 hidden md:block">
                        <label class="block text-sm font-bold text-sky-700 mb-1">Load Template</label>
                        <select id="template_loader" onchange="loadTemplate(this.value)" class="w-full px-4 py-3 bg-sky-50 border border-sky-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 text-sky-800">
                            <option value="">-- Start from Scratch --</option>
                            <?php foreach ($templates as $t): ?>
                                <option value="<?php echo $t['id']; ?>"><?php echo esc($t['title']); ?></option>
                            <?php
endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-400 mt-2">Loading a template will overwrite current editor contents.</p>
                    </div>
                </div>

                <!-- Report Title -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Report Title *</label>
                    <input type="text" name="report_title" id="report_title" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-sky-500" placeholder="e.g. Scrotal Ultrasound / Penile Doppler" value="<?php echo esc($edit_data['report_title'] ?? ''); ?>" required>
                </div>

                <!-- WYSIWYG Editor -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Clinical Findings & Report *</label>
                    <textarea name="content" id="usg_content"><?php echo $edit_data['content'] ?? ''; ?></textarea>
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
                    <a href="ultrasounds.php" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-6 py-3 rounded-lg font-medium transition-colors">Cancel</a>
                    <button type="submit" name="save_usg" class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-3 px-8 rounded-lg shadow-md hover:shadow-lg transition-all focus:outline-none flex items-center gap-2">
                        <i class="fa-solid fa-file-signature"></i> <?php echo $edit_id ? 'Update Report' : 'Finalize Report'; ?>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
const templatesData = <?php echo $templates_json; ?>;

function loadTemplate(id) {
    if (!id) return;
    if (confirm("Loading this template will replace any text currently in the editor. Proceed?")) {
        const body = templatesData[id];
        if (tinymce.get('usg_content')) {
            tinymce.get('usg_content').setContent(body);
        }
        
        // Auto-fill title
        const select = document.getElementById('template_loader');
        const text = select.options[select.selectedIndex].text;
        document.getElementById('report_title').value = text;
    } else {
        document.getElementById('template_loader').value = "";
    }
}

document.addEventListener('alpine:init', () => {
    Alpine.data('patientSearch', (initialData = null) => ({
        query: initialData ? (initialData.name + ' (' + initialData.mr + ')') : '',
        results: [],
        isLoading: false,
        selectedPatientId: initialData ? initialData.id : '',

        async search() {
            if (this.selectedPatientId) return; // Don't search if already selected
            if (this.query.length < 2) {
                this.results = [];
                return;
            }
            this.isLoading = true;
            try {
                let res = await fetch(`api_search_patients.php?q=${encodeURIComponent(this.query)}`);
                let data = await res.json();
                this.results = data;
            } catch (e) {
                console.error(e);
            }
            this.isLoading = false;
        },
        
        selectPatient(p) {
            this.selectedPatientId = p.id;
            this.query = p.first_name + ' ' + (p.last_name || '') + ' (' + p.mr_number + ')';
            this.results = [];
        },
        
        clearSelection() {
            this.selectedPatientId = '';
            this.query = '';
            this.results = [];
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
