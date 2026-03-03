<?php
$pageTitle = "Modern Prescription Builder";
require_once __DIR__ . '/includes/auth.php';

$error = '';
$success = '';
$pre_patient_id = $_GET['patient_id'] ?? '';
$edit_id = intval($_GET['edit'] ?? 0);
$edit_data = null;
$edit_items = [];
$edit_diagnoses = ['ICD' => [], 'CPT' => []];
$edit_lab_tests = [];

// Handle Edit Fetching
if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT r.*, p.first_name, p.last_name, p.mr_number, p.gender, p.patient_age, p.blood_group FROM prescriptions r JOIN patients p ON r.patient_id = p.id WHERE r.id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
    if ($edit_data) {
        $pre_patient_id = $edit_data['patient_id'];
        $res = $conn->query("SELECT * FROM prescription_items WHERE prescription_id = $edit_id");
        while ($row = $res->fetch_assoc())
            $edit_items[] = $row;
        $res = $conn->query("SELECT * FROM prescription_diagnoses WHERE prescription_id = $edit_id");
        while ($row = $res->fetch_assoc())
            $edit_diagnoses[$row['type']][] = $row;
        $res = $conn->query("SELECT * FROM prescription_lab_tests WHERE prescription_id = $edit_id");
        while ($row = $res->fetch_assoc())
            $edit_lab_tests[] = ['id' => $row['test_id'], 'name' => $row['test_name'], 'advised_for' => $row['advised_for']];
    }
    else {
        $edit_id = 0;
    }
}
else if ($pre_patient_id) {
    // Basic patient info for header
    $stmt = $conn->prepare("SELECT id, first_name, last_name, mr_number, gender, patient_age, blood_group FROM patients WHERE id = ?");
    $stmt->bind_param("i", $pre_patient_id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
}

// Fetch Master Data
$hospitals = $conn->query("SELECT id, name FROM hospitals ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$medications = $conn->query("SELECT id, name, med_type FROM medications ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="max-w-[1400px] mx-auto px-4 py-8" x-data="rxWizard()">

    <!-- Header / Context Bar -->
    <div class="bg-white rounded-[2rem] shadow-xl border border-gray-100 overflow-hidden mb-8">
        <div class="p-8 flex flex-col lg:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <div class="w-16 h-16 bg-teal-600 rounded-2xl flex items-center justify-center text-white text-2xl shadow-lg shadow-teal-100">
                    <i class="fa-solid fa-file-signature"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-800 tracking-tight"><?php echo $edit_id ? 'Update Digital Slip' : 'Create Digital Prescription'; ?></h1>
                    <div class="flex items-center gap-3 text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                        <span class="text-teal-600">IVF Experts EMR</span>
                        <i class="fa-solid fa-chevron-right text-[8px] opacity-30"></i>
                        <span>Rx Module v3.0</span>
                    </div>
                </div>
            </div>

            <!-- Mini Patient Badge -->
            <?php if ($edit_data): ?>
            <div class="bg-gray-50 border border-gray-100 px-6 py-4 rounded-2xl flex items-center gap-4">
                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-teal-600 border border-gray-100"><i class="fa-solid fa-user"></i></div>
                <div>
                    <div class="font-black text-gray-800 leading-none mb-1"><?php echo esc($edit_data['first_name'] . ' ' . $edit_data['last_name']); ?></div>
                    <div class="text-[10px] font-bold text-gray-400 uppercase">MR# <?php echo esc($edit_data['mr_number']); ?> • <?php echo $edit_data['patient_age']; ?> Y/O • <?php echo $edit_data['gender']; ?></div>
                </div>
            </div>
            <?php
endif; ?>

            <div class="flex items-center gap-4">
                <a href="prescriptions.php" class="text-gray-400 hover:text-gray-600 font-bold text-sm px-4">Cancel</a>
                <button type="button" @click="submitForm" class="bg-teal-600 hover:bg-teal-900 text-white px-10 py-4 rounded-xl font-black shadow-xl shadow-teal-100 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-save"></i> Finalize & Publish
                </button>
            </div>
        </div>
    </div>

    <!-- Main Wizard Content -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        
        <!-- Sidebar Navigation (Wizard Steps) -->
        <aside class="lg:col-span-3 space-y-4">
            <template x-for="(step, idx) in steps" :key="idx">
                <button @click="currentStep = idx" class="w-full flex items-center gap-4 p-5 rounded-2xl border transition-all duration-300 text-left group"
                    :class="currentStep === idx ? 'bg-teal-600 border-teal-600 text-white shadow-xl shadow-teal-100 -translate-y-1' : 'bg-white border-gray-100 text-gray-400 hover:border-teal-200 hover:bg-teal-50/50'">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg" :class="currentStep === idx ? 'bg-white/20' : 'bg-gray-50 text-gray-300 group-hover:text-teal-400'">
                        <i :class="step.icon"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-[0.2em] opacity-60" x-text="'STEP ' + (idx + 1)"></div>
                        <div class="font-black" x-text="step.title"></div>
                    </div>
                </button>
            </template>

            <!-- Tip Card -->
            <div class="bg-indigo-900 rounded-[2rem] p-8 text-white relative overflow-hidden mt-10">
                <i class="fa-solid fa-lightbulb absolute -right-6 -bottom-6 text-8xl opacity-10 rotate-12"></i>
                <h4 class="font-black text-indigo-200 mb-2 uppercase text-[10px] tracking-widest">PRO TIP</h4>
                <p class="text-sm leading-relaxed text-indigo-100/80">Use the <b>Live ICD-10 Search</b> to automatically pull validated medical codes directly from the NIH database.</p>
            </div>
        </aside>

        <!-- Dynamic Form Body -->
        <main class="lg:col-span-9">
            <form method="POST" id="mainRxForm" enctype="multipart/form-data">
                <input type="hidden" name="save_rx" value="1">
                <input type="hidden" name="patient_id" value="<?php echo $pre_patient_id; ?>">
                <?php if ($edit_id): ?><input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>"><?php
endif; ?>

                <!-- Step 0: Patient & Basic Setup -->
                <div x-show="currentStep === 0" x-transition:enter="duration-500 ease-out" x-transition:enter-start="opacity-0 translate-y-4" class="space-y-10">
                    <div class="bg-white rounded-[2.5rem] border border-gray-100 p-10 shadow-sm">
                        <h2 class="text-2xl font-black text-gray-800 mb-8 flex items-center gap-3">
                            <i class="fa-solid fa-hospital-user text-teal-600"></i> Setup & Complaints
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                            <div class="space-y-4">
                                <label class="block text-[10px] font-black uppercase text-gray-400 tracking-widest">Hospital / Facility *</label>
                                <select name="hospital_id" required class="w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-teal-500 font-bold text-gray-700 appearance-none">
                                    <?php foreach ($hospitals as $h): ?>
                                        <option value="<?php echo $h['id']; ?>" <?php echo($edit_data && $edit_data['hospital_id'] == $h['id']) ? 'selected' : ''; ?>><?php echo esc($h['name']); ?></option>
                                    <?php
endforeach; ?>
                                </select>
                            </div>
                            <div class="space-y-4">
                                <label class="block text-[10px] font-black uppercase text-gray-400 tracking-widest">Presenting Complaint Summary</label>
                                <textarea name="presenting_complaint" rows="1" class="w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-teal-500 font-medium text-gray-700" placeholder="Patient reports..."><?php echo esc($edit_data['presenting_complaint'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- ICD-10 Live Search -->
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <label class="block text-[10px] font-black uppercase text-gray-400 tracking-widest">Medical Diagnoses (ICD-10)</label>
                                <span class="bg-teal-50 text-teal-600 text-[9px] font-black px-3 py-1 rounded-full border border-teal-100 italic">LIVE NIH SEARCH ENABLED</span>
                            </div>
                            
                            <!-- Search Field -->
                            <div class="relative">
                                <i class="fa-solid fa-search absolute left-6 top-5 text-gray-300"></i>
                                <input type="text" x-model="icdQuery" @input.debounce.400ms="searchIcd" placeholder="Type symptoms or conditions (e.g. Endometriosis, PCOS)..." 
                                    class="w-full pl-14 pr-6 py-5 bg-white border border-gray-100 rounded-2xl focus:ring-2 focus:ring-teal-500 font-bold text-gray-700 shadow-inner">
                                <div x-show="icdLoading" class="absolute right-6 top-5 transition-opacity"><i class="fa-solid fa-spinner fa-spin text-teal-600"></i></div>
                            </div>

                            <!-- Search Results Dropdown -->
                            <div x-show="icdResults.length > 0" class="bg-white border border-gray-100 rounded-2xl shadow-2xl overflow-hidden max-h-[300px] overflow-y-auto mb-6">
                                <template x-for="item in icdResults" :key="item[0]">
                                    <button type="button" @click="addIcd(item)" class="w-full px-8 py-4 flex items-center gap-4 hover:bg-teal-50 transition-colors border-b border-gray-50 last:border-0 text-left group">
                                        <span class="w-16 font-mono text-teal-600 font-black" x-text="item[0]"></span>
                                        <span class="flex-1 font-bold text-gray-700 group-hover:text-teal-900" x-text="item[1]"></span>
                                        <i class="fa-solid fa-plus-circle opacity-0 group-hover:opacity-100 text-teal-600 transition-opacity"></i>
                                    </button>
                                </template>
                            </div>

                            <!-- Selected Tags -->
                            <div class="flex flex-wrap gap-3">
                                <template x-for="(tag, idx) in selectedIcds" :key="idx">
                                    <div class="inline-flex items-center gap-3 bg-teal-900 text-white pl-4 pr-2 py-2 rounded-xl text-xs font-bold shadow-lg shadow-teal-100">
                                        <span x-text="tag.code + ': ' + tag.name"></span>
                                        <input type="hidden" name="icd_codes[]" :value="tag.code">
                                        <input type="hidden" name="icd_names[]" :value="tag.name">
                                        <button type="button" @click="removeIcd(idx)" class="w-6 h-6 bg-white/20 hover:bg-white/40 rounded-lg flex items-center justify-center transition-colors"><i class="fa-solid fa-times"></i></button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="button" @click="currentStep = 1" class="bg-teal-600 text-white px-12 py-5 rounded-2xl font-black shadow-xl shadow-teal-100 flex items-center gap-2">Next: Build Medicine Grid <i class="fa-solid fa-arrow-right"></i></button>
                    </div>
                </div>

                <!-- Step 1: Medication Builder Grid -->
                <div x-show="currentStep === 1" x-transition:enter="duration-500 ease-out" x-transition:enter-start="opacity-0 translate-y-4" class="space-y-8">
                    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
                        <div class="p-8 border-b border-gray-50 flex items-center justify-between bg-gray-50/30">
                            <h2 class="text-2xl font-black text-gray-800 flex items-center gap-3">
                                <i class="fa-solid fa-pills text-teal-600"></i> Medication Protocol
                            </h2>
                            <button type="button" @click="addRow" class="bg-teal-900 hover:bg-black text-white px-6 py-3 rounded-xl text-sm font-black transition-all flex items-center gap-2">
                                <i class="fa-solid fa-plus-circle"></i> Add Custom Row
                            </button>
                        </div>
                        
                        <div class="p-8 space-y-4">
                            <!-- Headers for Desktop -->
                            <div class="hidden lg:grid grid-cols-12 gap-4 px-6 mb-2">
                                <div class="col-span-4 text-[9px] font-black uppercase text-gray-400 tracking-widest">Molecule / Drug</div>
                                <div class="col-span-2 text-[9px] font-black uppercase text-gray-400 tracking-widest text-center">Dosage</div>
                                <div class="col-span-2 text-[9px] font-black uppercase text-gray-400 tracking-widest text-center">Frequency</div>
                                <div class="col-span-1 text-[9px] font-black uppercase text-gray-400 tracking-widest text-center">Days</div>
                                <div class="col-span-2 text-[9px] font-black uppercase text-gray-400 tracking-widest">Note</div>
                                <div class="col-span-1"></div>
                            </div>

                            <template x-for="(row, idx) in rows" :key="row.id">
                                <div class="group relative bg-white border border-gray-100 rounded-3xl p-6 lg:p-4 grid grid-cols-1 lg:grid-cols-12 gap-6 items-center hover:border-teal-400 hover:shadow-2xl hover:shadow-teal-50 transition-all duration-300">
                                    <div class="col-span-4">
                                        <select :name="'med_id['+idx+']'" x-model="row.med_id" class="w-full bg-gray-50 px-4 py-3 rounded-xl border-none focus:ring-2 focus:ring-teal-500 font-bold text-gray-700 text-sm appearance-none" required>
                                            <option value="">Select Drug...</option>
                                            <?php foreach ($medications as $m): ?>
                                                <option value="<?php echo $m['id']; ?>"><?php echo esc($m['name'] . ' (' . $m['med_type'] . ')'); ?></option>
                                            <?php
endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-span-2">
                                        <input type="text" :name="'dosage['+idx+']'" x-model="row.dosage" placeholder="e.g. 1 Tab" class="w-full bg-gray-50 px-4 py-3 rounded-xl border-none focus:ring-2 focus:ring-teal-500 font-black text-gray-700 text-sm text-center">
                                    </div>
                                    <div class="col-span-2">
                                        <select :name="'usage_frequency['+idx+']'" x-model="row.usage_frequency" class="w-full bg-teal-50 text-teal-700 px-4 py-3 rounded-xl border-none focus:ring-2 focus:ring-teal-500 font-black text-xs text-center appearance-none">
                                            <option value="OD">OD (1x Daily)</option>
                                            <option value="BD">BD (2x Daily)</option>
                                            <option value="TDS">TDS (3x Daily)</option>
                                            <option value="QID">QID (4x Daily)</option>
                                            <option value="SOS">SOS (AS NEEDED)</option>
                                            <option value="STAT">STAT (ONCE)</option>
                                        </select>
                                    </div>
                                    <div class="col-span-1">
                                        <input type="text" :name="'duration['+idx+']'" x-model="row.duration" placeholder="10" class="w-full bg-gray-50 px-4 py-3 rounded-xl border-none focus:ring-2 focus:ring-teal-500 font-black text-gray-700 text-sm text-center uppercase">
                                    </div>
                                    <div class="col-span-2">
                                        <input type="text" :name="'instructions['+idx+']'" x-model="row.instructions" placeholder="PC / AC etc" class="w-full bg-gray-50 px-4 py-3 rounded-xl border-none focus:ring-2 focus:ring-teal-500 font-bold text-gray-700 text-sm">
                                    </div>
                                    <div class="col-span-1 flex justify-center">
                                        <button type="button" @click="removeRow(idx)" class="w-10 h-10 rounded-xl bg-rose-50 text-rose-300 hover:bg-rose-500 hover:text-white transition-all"><i class="fa-solid fa-trash-can"></i></button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <button type="button" @click="currentStep = 0" class="text-gray-400 hover:bg-gray-100 px-8 py-5 rounded-2xl font-black flex items-center gap-2"><i class="fa-solid fa-arrow-left"></i> Previous</button>
                        <button type="button" @click="currentStep = 2" class="bg-teal-600 text-white px-12 py-5 rounded-2xl font-black shadow-xl shadow-teal-100 flex items-center gap-2">Next: Future Advice & Labs <i class="fa-solid fa-arrow-right"></i></button>
                    </div>
                </div>

                <!-- Step 2: Labs & Follow-up -->
                <div x-show="currentStep === 2" x-transition:enter="duration-500 ease-out" x-transition:enter-start="opacity-0 translate-y-4" class="space-y-10">
                    
                    <!-- Lab Investigations -->
                    <div class="bg-white rounded-[2.5rem] border border-gray-100 p-10 shadow-sm">
                        <h2 class="text-2xl font-black text-gray-800 mb-8 flex items-center gap-3">
                            <i class="fa-solid fa-vials text-teal-600"></i> Advised Lab Tests
                        </h2>

                        <!-- Search Box -->
                        <div class="relative mb-8">
                            <i class="fa-solid fa-microscope absolute left-6 top-5 text-gray-300"></i>
                            <input type="text" x-model="testQuery" @input.debounce.300ms="searchTest" placeholder="Search test names (e.g. Beta HCG, FSH, LH)..." 
                                class="w-full pl-14 pr-6 py-5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-teal-500 font-bold text-gray-700 outline-none">
                            <div x-show="testLoading" class="absolute right-6 top-5"><i class="fa-solid fa-spinner fa-spin text-teal-600"></i></div>
                        </div>

                        <!-- Results -->
                        <div x-show="testResults.length > 0" class="bg-white border border-gray-100 rounded-2xl shadow-2xl overflow-hidden mb-8 max-h-[250px] overflow-y-auto">
                            <template x-for="t in testResults" :key="t.id">
                                <button type="button" @click="addTest(t)" class="w-full px-8 py-4 flex items-center justify-between hover:bg-teal-50 transition-colors border-b border-gray-50 last:border-0 text-left">
                                    <span class="font-bold text-gray-700" x-text="t.test_name"></span>
                                    <i class="fa-solid fa-plus-circle text-teal-600"></i>
                                </button>
                            </template>
                        </div>

                        <!-- Selected Tests Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <template x-for="(test, idx) in selectedTests" :key="idx">
                                <div class="bg-white border border-gray-100 rounded-[1.5rem] p-5 shadow-sm flex items-center justify-between group">
                                    <input type="hidden" name="adv_test_id[]" :value="test.id">
                                    <input type="hidden" name="adv_test_name[]" :value="test.name">
                                    <input type="hidden" name="adv_test_for[]" :value="test.advised_for">
                                    
                                    <div class="flex-1">
                                        <div class="font-black text-gray-800 text-sm leading-tight mb-1" x-text="test.name"></div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="test.advised_for = 'Patient'" :class="test.advised_for==='Patient'?'bg-teal-900 text-white':'bg-gray-50 text-gray-400'" class="px-3 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all">Patient</button>
                                            <button type="button" @click="test.advised_for = 'Spouse'" :class="test.advised_for==='Spouse'?'bg-pink-600 text-white':'bg-gray-50 text-gray-400'" class="px-3 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all">Spouse</button>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeTest(idx)" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-300 hover:bg-rose-50 hover:text-rose-500 transition-all opacity-0 group-hover:opacity-100"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Final Notes & Revisit -->
                    <div class="bg-indigo-900 rounded-[2.5rem] p-10 text-white shadow-2xl relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-800/50 to-transparent"></div>
                        <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-10">
                            <div class="space-y-6">
                                <h4 class="font-black text-indigo-200 uppercase text-[10px] tracking-[0.2em] mb-4">A. Clinical Advice & Summary</h4>
                                <textarea name="notes" rows="3" class="w-full bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-6 text-white text-sm focus:outline-none focus:ring-2 focus:ring-white placeholder:text-white/30" placeholder="Type general clinical instructions..."><?php echo esc($edit_data['notes'] ?? ''); ?></textarea>
                            </div>
                            <div class="space-y-6">
                                <h4 class="font-black text-indigo-200 uppercase text-[10px] tracking-[0.2em] mb-4">B. Next Appointment Date</h4>
                                <input type="date" name="revisit_date" value="<?php echo $edit_data['revisit_date'] ?? ''; ?>" class="w-full bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl px-6 py-5 text-white font-black text-lg focus:outline-none focus:ring-2 focus:ring-white cursor-pointer">
                                <p class="text-[10px] text-white/40 italic">This will appear as "Revisit Date" on the footer of the printed slip.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center bg-teal-50 rounded-[2rem] p-10 border-2 border-dashed border-teal-100">
                        <button type="button" @click="currentStep = 1" class="text-teal-900/50 font-black flex items-center gap-2 px-6 py-3 rounded-xl hover:bg-teal-100/50 transition-all"><i class="fa-solid fa-arrow-left"></i> Oops, back to Medicines</button>
                        <div class="flex gap-4">
                            <div class="text-right flex flex-col justify-center">
                                <span class="text-[10px] font-black text-teal-600 uppercase">Ready for Checkout?</span>
                                <span class="text-xs text-gray-400 font-bold">This will finalize medical slip #<?php echo $edit_id ?: 'NEW'; ?></span>
                            </div>
                            <button type="button" @click="submitForm" class="bg-teal-600 hover:bg-teal-900 text-white px-12 py-5 rounded-2xl font-black shadow-2xl shadow-teal-100 transition-all transform hover:scale-105 active:scale-95 flex items-center gap-3">
                                <i class="fa-solid fa-check-double"></i> PUSH TO PATIENT PORTAL
                            </button>
                        </div>
                    </div>

                </div>

            </form>
        </main>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@500;600;700;800;900&display=swap');
    body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
    h1, h2, h3, h4, .font-black { font-family: 'Outfit', sans-serif; }
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('rxWizard', () => ({
        currentStep: 0,
        steps: [
            { title: 'Setup & Complaints', icon: 'fa-solid fa-hospital-user' },
            { title: 'Medicine Protocol', icon: 'fa-solid fa-pills' },
            { title: 'Future Care Plan', icon: 'fa-solid fa-vials' }
        ],

        // --- ICD-10 Search ---
        icdQuery: '',
        icdResults: [],
        icdLoading: false,
        selectedIcds: <?php echo json_encode(array_map(function ($d) {
    return ['code' => $d['code'], 'name' => $d['description']]; }, $edit_diagnoses['ICD'])); ?>,
        async searchIcd() {
            if (this.icdQuery.length < 2) { this.icdResults = []; return; }
            this.icdLoading = true;
            try {
                let url = `https://clinicaltables.nlm.nih.gov/api/icd10cm/v3/search?sf=code,name&terms=${encodeURIComponent(this.icdQuery)}&maxList=10`;
                let res = await fetch(url);
                let data = await res.json();
                this.icdResults = data[3] || [];
            } catch (e) { console.error(e); }
            this.icdLoading = false;
        },
        addIcd(item) {
            this.selectedIcds.push({ code: item[0], name: item[1] });
            this.icdQuery = ''; this.icdResults = [];
        },
        removeIcd(idx) { this.selectedIcds.splice(idx, 1); },

        // --- Rx Grid ---
        rows: <?php
if (!empty($edit_items)) {
    $mapped = [];
    $nid = 1;
    foreach ($edit_items as $item) {
        $mapped[] = ['id' => $nid++, 'med_id' => $item['medication_id'], 'dosage' => $item['dosage'], 'usage_frequency' => $item['usage_frequency'], 'duration' => $item['duration'], 'instructions' => $item['instructions']];
    }
    echo json_encode($mapped);
}
else {
    echo '[{"id": 1, "med_id": "", "dosage": "", "usage_frequency": "OD", "duration": "", "instructions": ""}]';
}
?>,
        nextId: <?php echo count($edit_items) + 2; ?>,
        addRow() { this.rows.push({ id: this.nextId++, med_id: '', dosage: '', usage_frequency: 'OD', duration: '', instructions: '' }); },
        removeRow(idx) { this.rows.splice(idx, 1); },

        // --- Lab Search ---
        testQuery: '',
        testResults: [],
        testLoading: false,
        selectedTests: <?php echo json_encode($edit_lab_tests); ?>,
        async searchTest() {
            if (this.testQuery.length < 2) { this.testResults = []; return; }
            this.testLoading = true;
            try {
                let res = await fetch(`api_search_lab_tests.php?q=${encodeURIComponent(this.testQuery)}`);
                this.testResults = await res.json();
            } catch (e) { console.error(e); }
            this.testLoading = false;
        },
        addTest(t) {
            this.selectedTests.push({ id: t.id, name: t.test_name, advised_for: 'Patient' });
            this.testQuery = ''; this.testResults = [];
        },
        removeTest(idx) { this.selectedTests.splice(idx, 1); },

        submitForm() {
            // Basic validation
            if (this.rows.length === 0) { alert('Please add at least one medication.'); return; }
            document.getElementById('mainRxForm').submit();
        }
    }));
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
