<?php
/**
 * prescriptions_edit.php — Edit an existing prescription
 * All-in-one clean rewrite fixing: empty rows, ICD save, autocomplete, frequency select, View button.
 */
$pageTitle = "Edit Prescription";
require_once __DIR__ . '/includes/auth.php';

$rx_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($rx_id <= 0) {
    header("Location: patients.php");
    exit;
}

// Fetch prescription
$stmt = $conn->prepare("SELECT * FROM prescriptions WHERE id = ?");
$stmt->bind_param("i", $rx_id);
$stmt->execute();
$rx = $stmt->get_result()->fetch_assoc();
if (!$rx) {
    header("Location: patients.php");
    exit;
}

$patient_id = intval($rx['patient_id']);

// Fetch patient
$stmt2 = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt2->bind_param("i", $patient_id);
$stmt2->execute();
$patient = $stmt2->get_result()->fetch_assoc();

// Fetch medication items — strictly filter empty rows
$items_res = $conn->query("SELECT * FROM prescription_items WHERE prescription_id = $rx_id AND TRIM(COALESCE(medicine_name,'')) != '' ORDER BY id ASC");
$rx_items = $items_res ? $items_res->fetch_all(MYSQLI_ASSOC) : [];

// Fetch advised lab tests
$advised_labs = [];
try {
    $chk = $conn->query("SHOW TABLES LIKE 'advised_lab_tests'");
    if ($chk && $chk->num_rows > 0) {
        $ls = $conn->prepare("SELECT alt.test_id as id, alt.record_for as `for`, COALESCE(ltd.test_name, '') as test_name FROM advised_lab_tests alt LEFT JOIN lab_tests_directory ltd ON alt.test_id = ltd.id WHERE alt.prescription_id = ?");
        if ($ls) {
            $ls->bind_param("i", $rx_id);
            $ls->execute();
            $advised_labs = $ls->get_result()->fetch_all(MYSQLI_ASSOC);
        }
    }
}
catch (Exception $e) {
}

// Parse existing ICD-10 codes
$existing_icd = [];
if (!empty($rx['icd10_codes'])) {
    $decoded = json_decode($rx['icd10_codes'], true);
    if (is_array($decoded))
        $existing_icd = $decoded;
}

// Strip HTML tags for textarea display (clinical notes may be stored as HTML from rich editor)
$clinical_notes_plain = strip_tags($rx['clinical_notes'] ?? '');
$diagnosis_plain = strip_tags($rx['diagnosis'] ?? '');

// Handle update
$save_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_prescription'])) {
    $diagnosis = trim($_POST['diagnosis'] ?? '');
    $clinical_notes = trim($_POST['clinical_notes'] ?? '');
    $general_advice = trim($_POST['general_advice'] ?? '');
    $next_visit = !empty($_POST['next_visit']) ? $_POST['next_visit'] : null;
    $record_for = in_array($_POST['record_for'] ?? '', ['Patient', 'Spouse']) ? $_POST['record_for'] : 'Patient';
    $icd10_codes = $_POST['icd10_data'] ?? '[]';

    // Validate ICD JSON
    if (json_decode($icd10_codes) === null)
        $icd10_codes = '[]';

    $upd = $conn->prepare(
        "UPDATE prescriptions SET diagnosis=?, clinical_notes=?, general_advice=?, next_visit=?, record_for=?, icd10_codes=? WHERE id=?"
    );
    if ($upd) {
        $upd->bind_param("ssssssi", $diagnosis, $clinical_notes, $general_advice, $next_visit, $record_for, $icd10_codes, $rx_id);
        if ($upd->execute()) {
            // Replace medication items
            $del_meds = $conn->prepare("DELETE FROM prescription_items WHERE prescription_id = ?");
            if ($del_meds) {
                $del_meds->bind_param("i", $rx_id);
                $del_meds->execute();
            }
            $meds_post = $_POST['meds'] ?? [];
            if (is_array($meds_post) && count($meds_post) > 0) {
                $m_stmt = $conn->prepare("INSERT INTO prescription_items (prescription_id, medicine_name, dosage, frequency, duration, instructions) VALUES (?,?,?,?,?,?)");
                $auto_med = $conn->prepare(
                    "INSERT IGNORE INTO medications (name, default_dosage, default_frequency, default_duration, default_instructions) VALUES (?, ?, ?, ?, ?)"
                );
                if ($m_stmt) {
                    foreach ($meds_post as $m) {
                        $name = trim($m['medicine_name'] ?? '');
                        if (empty($name))
                            continue;
                        $dose = trim($m['dosage'] ?? '');
                        $freq = trim($m['frequency'] ?? '');
                        $dur = trim($m['duration'] ?? '');
                        $instr = trim($m['instructions'] ?? '');
                        $m_stmt->bind_param("isssss", $rx_id, $name, $dose, $freq, $dur, $instr);
                        $m_stmt->execute();
                        if ($auto_med) {
                            $auto_med->bind_param("sssss", $name, $dose, $freq, $dur, $instr);
                            $auto_med->execute();
                        }
                    }
                }
            }

            // Replace advised lab tests
            $del_labs = $conn->prepare("DELETE FROM advised_lab_tests WHERE prescription_id = ?");
            if ($del_labs) {
                $del_labs->bind_param("i", $rx_id);
                $del_labs->execute();
            }
            $labs_post = $_POST['labs'] ?? [];
            if (is_array($labs_post) && !empty($labs_post)) {
                $l_stmt = $conn->prepare("INSERT INTO advised_lab_tests (prescription_id, patient_id, test_id, record_for) VALUES (?, ?, ?, ?)");
                if ($l_stmt) {
                    foreach ($labs_post as $l) {
                        $tid = intval($l['id'] ?? 0);
                        if ($tid <= 0)
                            continue;
                        $lab_for = in_array($l['for'] ?? '', ['Patient', 'Spouse']) ? $l['for'] : 'Patient';
                        $l_stmt->bind_param("iiis", $rx_id, $patient_id, $tid, $lab_for);
                        $l_stmt->execute();
                    }
                }
            }

            header("Location: patients_view.php?id=$patient_id&tab=rx&msg=rx_saved");
            exit;
        }
        else {
            $save_error = "Update failed: " . $upd->error;
        }
    }
    else {
        $save_error = "Prepare failed: " . $conn->error;
    }
}

include __DIR__ . '/includes/header.php';

// Prepare medication data as JSON for JS initialization
$items_for_js = array_values(array_map(fn($i) => [
'medicine_name' => $i['medicine_name'],
'dosage' => $i['dosage'] ?? '',
'frequency' => $i['frequency'] ?? '',
'duration' => $i['duration'] ?? '',
'instructions' => $i['instructions'] ?? '',
], $rx_items));

// Prepare lab data as JSON for JS initialization
$labs_for_js = array_values(array_map(fn($l) => [
'id' => (int)$l['id'],
'test_name' => $l['test_name'],
'for' => $l['for'] ?? 'Patient',
], $advised_labs));
?>

<style>
.icd-chip { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; background:#f5f3ff; border:1px solid #ddd6fe; border-radius:8px; font-size:11px; color:#6d28d9; font-weight:600; }
.icd-chip button { background:none; border:none; cursor:pointer; color:#a78bfa; font-size:12px; line-height:1; padding:0; }
.icd-chip button:hover { color:#7c3aed; }
#icd-dropdown { position:absolute; z-index:50; left:0; right:0; margin-top:4px; background:white; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 40px rgba(0,0,0,.08); max-height:240px; overflow-y:auto; }
.icd-option { padding:10px 14px; cursor:pointer; font-size:13px; color:#374151; border-bottom:1px solid #f9fafb; }
.icd-option:hover { background:#f5f3ff; color:#6d28d9; }
.icd-option .code { font-weight:700; color:#7c3aed; font-family:monospace; margin-right:8px; }
</style>

<script>
// ─── Shared constants ────────────────────────────────────────────────────────
const _FREQ_OPTS = [
    ['', '— Select frequency —'],
    ['1-0-1', 'Twice daily (BDS / 1-0-1)'],
    ['1-1-1', 'Three times daily (TDS / 1-1-1)'],
    ['1-0-0', 'Morning only (OD)'],
    ['0-0-1', 'Night only (OD)'],
    ['0-1-0', 'Noon only'],
    ['SOS',   'As needed (SOS)'],
    ['Weekly','Weekly'],
    ['Stat',  'Single dose (Stat)'],
];
const _IC = 'px-3 py-2 bg-white border border-gray-100 rounded-lg text-sm outline-none w-full';

// Pre-loaded med data from PHP
var _medsData = <?php echo json_encode($items_for_js); ?>;
var _medCount = 0;

// ─── Med row rendering ────────────────────────────────────────────────────────
function _buildFreqOpts(selected) {
    return _FREQ_OPTS.map(function(o) {
        var sel = (o[0] === selected) ? ' selected' : '';
        return '<option value="' + o[0] + '"' + sel + '>' + o[1] + '</option>';
    }).join('');
}

function renderMedRow(idx, data) {
    data = data || {};
    var name  = data.medicine_name || '';
    var dose  = data.dosage        || '';
    var freq  = data.frequency     || '';
    var dur   = data.duration      || '';
    var instr = data.instructions  || '';
    return [
        '<div class="med-row bg-gray-50 rounded-xl p-3 border border-gray-100 grid grid-cols-2 md:grid-cols-3 gap-2 relative mb-2">',
            '<button type="button" onclick="removeMedRow(this)" class="absolute top-2 right-2 w-6 h-6 rounded-lg bg-white text-gray-300 hover:text-rose-500 flex items-center justify-center text-xs border border-gray-100"><i class="fa-solid fa-times"></i></button>',
            '<div class="col-span-2 md:col-span-3 relative">',
                '<input type="text" name="meds[' + idx + '][medicine_name]" id="editmed-name-' + idx + '"',
                ' value="' + _escHtml(name) + '"',
                ' placeholder="Type medicine name or search..." autocomplete="off"',
                ' class="' + _IC + '" oninput="editMedSearch(this,' + idx + ')">',
                '<div id="editmed-drop-' + idx + '" class="absolute z-30 w-full bg-white mt-1 rounded-xl shadow-2xl border border-gray-100 overflow-hidden max-h-48 overflow-y-auto hidden"></div>',
            '</div>',
            '<input type="text"   name="meds[' + idx + '][dosage]"       id="editmed-dosage-' + idx + '" value="' + _escHtml(dose)  + '" placeholder="Dosage (e.g. 500mg)"   class="' + _IC + '">',
            '<select              name="meds[' + idx + '][frequency]"     id="editmed-freq-'  + idx + '" class="' + _IC + '">' + _buildFreqOpts(freq) + '</select>',
            '<input type="text"   name="meds[' + idx + '][duration]"      id="editmed-dur-'   + idx + '" value="' + _escHtml(dur)   + '" placeholder="Duration (e.g. 7 days)"  class="' + _IC + '">',
            '<input type="text"   name="meds[' + idx + '][instructions]"  id="editmed-instr-' + idx + '" value="' + _escHtml(instr) + '" placeholder="Instructions (optional)" class="col-span-2 md:col-span-3 ' + _IC + '">',
        '</div>'
    ].join('');
}

function _escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// Render all pre-loaded medications on page load
function initMedRows() {
    var container = document.getElementById('med-rows');
    if (!container) return;
    var html = '';
    _medsData.forEach(function(m) {
        html += renderMedRow(_medCount++, m);
    });
    container.innerHTML = html;
    toggleNoMedsMsg();
}

function addMedRow() {
    var container = document.getElementById('med-rows');
    if (!container) return;
    container.insertAdjacentHTML('beforeend', renderMedRow(_medCount++, {}));
    toggleNoMedsMsg();
}

function removeMedRow(btn) {
    btn.closest('.med-row').remove();
    toggleNoMedsMsg();
}

function toggleNoMedsMsg() {
    var msg = document.getElementById('no-meds-msg');
    var hasRows = document.querySelector('#med-rows .med-row');
    if (msg) msg.style.display = hasRows ? 'none' : '';
}

// ─── Medicine autocomplete ────────────────────────────────────────────────────
var _editMedTimer = {};
function editMedSearch(input, idx) {
    clearTimeout(_editMedTimer[idx]);
    _editMedTimer[idx] = setTimeout(async function() {
        var q = input.value.trim();
        var drop = document.getElementById('editmed-drop-' + idx);
        if (!drop) return;
        if (q.length < 2) { drop.classList.add('hidden'); return; }
        try {
            var res = await fetch('api_search_medications.php?q=' + encodeURIComponent(q));
            var data = await res.json();
            if (data.length === 0) { drop.classList.add('hidden'); return; }
            drop.innerHTML = data.map(function(m) {
                var label = '<span class="font-bold text-gray-800">' + _escHtml(m.name) + '</span>';
                if (m.formula) label += '<span class="text-xs text-indigo-400 ml-1">(' + _escHtml(m.formula) + ')</span>';
                if (m.default_dosage) label += '<span class="text-xs text-gray-400 ml-2">' + _escHtml(m.default_dosage) + '</span>';
                var safe = encodeURIComponent(JSON.stringify(m));
                return '<button type="button" class="w-full text-left px-4 py-2 hover:bg-teal-50 border-b border-gray-50 last:border-0 text-sm"' +
                       ' onmousedown="editMedSelect(decodeURIComponent(\'' + safe + '\'),' + idx + ')">' + label + '</button>';
            }).join('');
            drop.classList.remove('hidden');
        } catch(e) { drop.classList.add('hidden'); }
    }, 280);
}

function editMedSelect(jsonStr, idx) {
    var m = typeof jsonStr === 'string' ? JSON.parse(jsonStr) : jsonStr;
    var set = function(id, val) { var el = document.getElementById(id); if (el) el.value = val || ''; };
    set('editmed-name-'  + idx, m.name);
    set('editmed-dosage-'+ idx, m.default_dosage);
    set('editmed-dur-'   + idx, m.default_duration);
    set('editmed-instr-' + idx, m.default_instructions);
    // Frequency is a select
    var freq = document.getElementById('editmed-freq-' + idx);
    if (freq && m.default_frequency) freq.value = m.default_frequency;
    var drop = document.getElementById('editmed-drop-' + idx);
    if (drop) drop.classList.add('hidden');
}

// Run init on DOM ready
document.addEventListener('DOMContentLoaded', initMedRows);
</script>

<script>
// ─── Alpine.js data for ICD-10 + Lab Tests ───────────────────────────────────
function rxEditData() {
    return {
        icdCodes:     <?php echo json_encode($existing_icd); ?>,
        selectedLabs: <?php echo json_encode($labs_for_js); ?>,
        icdQuery: '',
        icdResults: [],
        icdLoading: false,
        icdOpen: false,
        labSearch: '',
        labResults: [],

        async searchIcd() {
            if (this.icdQuery.length < 2) { this.icdOpen = false; return; }
            this.icdLoading = true; this.icdOpen = true;
            try {
                const r = await fetch('api_search_icd10.php?q=' + encodeURIComponent(this.icdQuery));
                this.icdResults = await r.json();
            } catch(e) { this.icdResults = []; }
            this.icdLoading = false;
        },
        addIcd(item) {
            if (!this.icdCodes.find(c => c.icd10_code === item.icd10_code)) {
                this.icdCodes.push(item);
            }
            this.icdQuery = ''; this.icdOpen = false; this.icdResults = [];
        },
        removeIcd(code) { this.icdCodes = this.icdCodes.filter(c => c.icd10_code !== code); },

        async searchLabs() {
            if (this.labSearch.length < 2) { this.labResults = []; return; }
            try {
                const r = await fetch('api_search_lab_tests.php?q=' + encodeURIComponent(this.labSearch));
                this.labResults = await r.json();
            } catch(e) { this.labResults = []; }
        },
        addLab(lab) {
            if (!this.selectedLabs.find(l => l.id === lab.id)) {
                this.selectedLabs.push({id: lab.id, test_name: lab.test_name, for: 'Patient'});
            }
            this.labSearch = ''; this.labResults = [];
        },
        removeLab(i)    { this.selectedLabs.splice(i, 1); },
        toggleLabFor(i) { this.selectedLabs[i].for = this.selectedLabs[i].for === 'Patient' ? 'Spouse' : 'Patient'; },

        // Populate hidden ICD field and submit the form — called from submit button onclick
        submitForm() {
            var el = document.getElementById('edit_icd10_data');
            if (el) el.value = JSON.stringify(this.icdCodes);
            // Use a small delay so Alpine can flush reactive bindings before native submit
            setTimeout(function() {
                document.getElementById('rxEditForm').submit();
            }, 10);
        }
    };
}
</script>

<div class="max-w-2xl mx-auto px-4 py-8">
    <nav class="flex items-center gap-2 text-[10px] font-semibold uppercase tracking-widest text-gray-400 mb-5">
        <a href="patients_view.php?id=<?php echo $patient_id; ?>&tab=rx" class="hover:text-teal-600">← Back to Prescriptions</a>
    </nav>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-teal-500 to-teal-600 px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-white font-semibold text-lg">Edit Prescription #RX-<?php echo str_pad($rx_id, 5, '0', STR_PAD_LEFT); ?></h1>
                <p class="text-teal-100 text-xs mt-0.5"><?php echo htmlspecialchars(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')); ?> · <?php echo htmlspecialchars($patient['mr_number'] ?? ''); ?></p>
            </div>
            <div class="flex items-center gap-2">
                <a href="prescriptions_print.php?id=<?php echo $rx_id; ?>" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-white/20 text-white rounded-xl text-xs font-semibold hover:bg-white/30 transition-all">
                    <i class="fa-solid fa-eye"></i> View
                </a>
                <a href="prescriptions_print.php?id=<?php echo $rx_id; ?>" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-white text-teal-700 rounded-xl text-xs font-semibold hover:bg-teal-50 transition-all">
                    <i class="fa-solid fa-print"></i> Print / PDF
                </a>
            </div>
        </div>

        <?php if ($save_error): ?>
        <div class="mx-6 mt-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm"><?php echo htmlspecialchars($save_error); ?></div>
        <?php
endif; ?>

        <form method="POST" class="p-6 space-y-5" id="rxEditForm"
              x-data="rxEditData()">

            <!-- Record For -->
            <div class="flex items-center gap-3">
                <span class="text-xs font-medium text-slate-500">Record for:</span>
                <label class="cursor-pointer">
                    <input type="radio" name="record_for" value="Patient" class="peer sr-only"
                           <?php echo($rx['record_for'] ?? 'Patient') !== 'Spouse' ? 'checked' : ''; ?>>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border-2 transition-all border-gray-200 text-gray-500 bg-white peer-checked:border-teal-500 peer-checked:text-teal-700 peer-checked:bg-teal-50">
                        <i class="fa-solid fa-user text-[10px]"></i> Patient
                    </span>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="record_for" value="Spouse" class="peer sr-only"
                           <?php echo($rx['record_for'] ?? '') === 'Spouse' ? 'checked' : ''; ?>>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border-2 transition-all border-gray-200 text-gray-500 bg-white peer-checked:border-rose-400 peer-checked:text-rose-700 peer-checked:bg-rose-50">
                        <i class="fa-solid fa-heart text-[10px]"></i> Spouse
                    </span>
                </label>
            </div>

            <!-- Presenting Complaint -->
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Presenting Complaint</label>
                <textarea name="clinical_notes" rows="3"
                          class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-300 outline-none transition-all"><?php echo htmlspecialchars($clinical_notes_plain); ?></textarea>
            </div>

            <!-- Diagnosis -->
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Diagnosis</label>
                <textarea name="diagnosis" rows="2"
                          class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-300 outline-none transition-all"><?php echo htmlspecialchars($diagnosis_plain); ?></textarea>
            </div>

            <!-- ICD-10 Codes -->
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-2">ICD-10 Diagnosis Codes</label>
                <!-- Selected chips -->
                <div class="flex flex-wrap gap-2 mb-2" x-show="icdCodes.length > 0">
                    <template x-for="icd in icdCodes" :key="icd.icd10_code">
                        <span class="icd-chip">
                            <span class="font-mono font-bold" x-text="icd.icd10_code"></span>
                            <span x-text="icd.description && icd.description.length > 35 ? icd.description.substring(0,35)+'...' : icd.description"></span>
                            <button type="button" @click="removeIcd(icd.icd10_code)"><i class="fa-solid fa-xmark"></i></button>
                        </span>
                    </template>
                </div>
                <!-- Search -->
                <div class="relative">
                    <div class="relative">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 text-xs"></i>
                        <input type="text" x-model="icdQuery" @input.debounce.300ms="searchIcd()"
                               @focus="icdQuery.length >= 2 && searchIcd()"
                               @keydown.escape="icdOpen = false"
                               placeholder="Search ICD-10 codes (e.g. Male infertility)"
                               class="w-full pl-8 pr-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 outline-none transition-all">
                    </div>
                    <!-- Dropdown -->
                    <div id="icd-dropdown" x-show="icdOpen && icdResults.length > 0" @click.outside="icdOpen = false">
                        <template x-for="item in icdResults" :key="item.icd10_code">
                            <div class="icd-option" @click="addIcd(item)">
                                <span class="code" x-text="item.icd10_code"></span>
                                <span x-text="item.description"></span>
                            </div>
                        </template>
                    </div>
                    <div x-show="icdLoading" class="absolute right-3 top-1/2 -translate-y-1/2">
                        <i class="fa-solid fa-spinner fa-spin text-gray-300 text-xs"></i>
                    </div>
                </div>
                <!-- Hidden field populated by handleSubmit() -->
                <input type="hidden" name="icd10_data" id="edit_icd10_data">
            </div>

            <!-- Medications (Vanilla JS rendered — autocomplete enabled on all rows) -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <label class="text-xs font-medium text-slate-500">Medications</label>
                    <button type="button" onclick="addMedRow()" class="text-xs text-teal-600 hover:text-teal-800 font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-plus text-[10px]"></i> Add Medicine
                    </button>
                </div>
                <div id="med-rows" class="space-y-1"></div>
                <div id="no-meds-msg" class="text-center py-6 text-xs text-gray-400" style="display:none">
                    No medications added. Click "+ Add Medicine" to start.
                </div>
            </div>

            <!-- Lab Tests -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-medium text-slate-500">Advised Lab Investigations</label>
                </div>
                <div class="relative mb-3">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 text-xs"></i>
                    <input type="text" x-model="labSearch" @input.debounce.300ms="searchLabs()"
                           @click.away="labResults = []"
                           placeholder="Search lab tests to add (e.g. FSH, Semen)..."
                           class="w-full pl-8 pr-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-amber-400/30 focus:border-amber-300 outline-none transition-all">
                    <div x-show="labResults.length > 0" class="absolute z-30 left-0 right-0 top-full mt-1 bg-white border border-gray-100 rounded-xl shadow-2xl max-h-48 overflow-y-auto">
                        <template x-for="lab in labResults" :key="lab.id">
                            <button type="button" @click="addLab(lab)"
                                    class="w-full text-left px-4 py-2.5 hover:bg-amber-50 text-sm font-medium text-gray-800 border-b border-gray-50 last:border-0 flex items-center justify-between">
                                <span x-text="lab.test_name"></span>
                                <i class="fa-solid fa-plus-circle text-amber-400 text-xs"></i>
                            </button>
                        </template>
                    </div>
                </div>
                <div class="space-y-2" x-show="selectedLabs.length > 0">
                    <template x-for="(l, idx) in selectedLabs" :key="idx">
                        <div class="flex items-center justify-between px-3 py-2 bg-gray-50 rounded-xl border border-gray-100">
                            <input type="hidden" :name="'labs[' + idx + '][id]'" :value="l.id">
                            <input type="hidden" :name="'labs[' + idx + '][for]'" :value="l.for">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-vial text-amber-400 text-xs"></i>
                                <span class="text-sm font-semibold text-gray-800" x-text="l.test_name"></span>
                                <button type="button" @click="toggleLabFor(idx)"
                                        :class="l.for === 'Patient' ? 'bg-teal-100 text-teal-700' : 'bg-rose-100 text-rose-700'"
                                        class="text-[10px] font-bold px-2 py-0.5 rounded-full transition-colors" x-text="l.for"></button>
                            </div>
                            <button type="button" @click="removeLab(idx)" class="w-6 h-6 flex items-center justify-center rounded-lg text-gray-300 hover:text-rose-500 hover:bg-rose-50 transition-all text-xs">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                    </template>
                </div>
                <div x-show="selectedLabs.length === 0" class="text-center py-3 text-xs text-gray-400">No lab tests advised. Search above to add.</div>
            </div>

            <!-- General Advice + Next Visit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1.5">General Advice</label>
                    <textarea name="general_advice" rows="3"
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-300 outline-none transition-all"><?php echo htmlspecialchars($rx['general_advice'] ?? ''); ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1.5">Next Visit</label>
                    <input type="date" name="next_visit" value="<?php echo htmlspecialchars($rx['next_visit'] ?? ''); ?>"
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-300 outline-none transition-all">
                </div>
            </div>

            <!-- Actions -->
            <input type="hidden" name="update_prescription" value="1">
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="patients_view.php?id=<?php echo $patient_id; ?>&tab=rx"
                   class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-500 bg-gray-100 hover:bg-gray-200 transition-all">Cancel</a>
                <button type="button" @click="submitForm()"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-teal-600 hover:bg-teal-700 shadow-sm transition-all active:scale-95">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
