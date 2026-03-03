<?php
/**
 * IVF Experts - Schema Diagnostic Tool
 * Access this page to verify all required database tables and columns exist.
 * IMPORTANT: Delete or password-protect this file in production!
 */
require_once __DIR__ . '/includes/auth.php';
$pageTitle = "Schema Diagnostic";

$required = [
    'patients' => ['id', 'mr_number', 'first_name', 'last_name', 'gender', 'phone', 'cnic', 'spouse_name', 'spouse_age', 'spouse_gender', 'spouse_cnic', 'spouse_phone', 'referring_hospital_id', 'patient_age', 'date_of_birth', 'blood_group', 'marital_status', 'gravida', 'para', 'abortions', 'years_married', 'address', 'email', 'created_at'],
    'prescriptions' => ['id', 'patient_id', 'record_for', 'clinical_notes', 'diagnosis', 'icd10_codes', 'general_advice', 'next_visit', 'qrcode_hash', 'created_at'],
    'prescription_items' => ['id', 'prescription_id', 'medicine_name', 'dosage', 'frequency', 'duration', 'instructions'],
    'advised_lab_tests' => ['id', 'prescription_id', 'patient_id', 'test_id', 'record_for'],
    'patient_lab_results' => ['id', 'patient_id', 'test_id', 'test_for', 'result_value', 'unit', 'status', 'test_date', 'lab_name', 'lab_city', 'lab_mr_number', 'scanned_report_path', 'reference_range_male', 'reference_range_female'],
    'lab_tests_directory' => ['id', 'test_name', 'unit', 'reference_range_male', 'reference_range_female', 'category', 'cpt_code'],
    'patient_ultrasounds' => ['id', 'patient_id', 'hospital_id', 'qrcode_hash', 'report_title', 'content', 'scanned_report_path', 'record_for', 'created_at'],
    'semen_analyses' => ['id', 'patient_id', 'collection_time', 'volume', 'concentration', 'pr_motility', 'np_motility', 'im_motility', 'normal_morphology', 'auto_diagnosis'],
    'advised_procedures' => ['id', 'patient_id', 'procedure_name', 'date_advised', 'notes', 'record_for', 'status', 'created_at'],
    'hospitals' => ['id', 'name'],
    'ultrasound_templates' => ['id', 'title', 'body'],
];

$results = [];
foreach ($required as $table => $columns) {
    $table_exists = $conn->query("SHOW TABLES LIKE '$table'") && $conn->query("SHOW TABLES LIKE '$table'")->num_rows > 0;
    $col_status = [];
    if ($table_exists) {
        $col_res = $conn->query("SHOW COLUMNS FROM `$table`");
        $existing_cols = [];
        while ($crow = $col_res->fetch_assoc())
            $existing_cols[] = $crow['Field'];
        foreach ($columns as $col) {
            $col_status[$col] = in_array($col, $existing_cols) ? 'ok' : 'MISSING';
        }
    }
    $results[$table] = ['exists' => $table_exists, 'columns' => $col_status];
}

include __DIR__ . '/includes/header.php';
?>
<div class="max-w-5xl mx-auto py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-black text-slate-900">🔬 Schema Diagnostic</h1>
        <p class="text-gray-400 mt-1 text-sm">This page checks if all required database tables and columns exist.</p>
        <div class="mt-2 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-2 rounded-xl text-xs font-bold">⚠️ Delete or restrict access to this file in production.</div>
    </div>

    <div class="space-y-6">
        <?php foreach ($results as $table => $info): ?>
        <div class="bg-white rounded-2xl border <?php echo $info['exists'] ? 'border-gray-100' : 'border-rose-300 ring-2 ring-rose-100'; ?> overflow-hidden">
            <div class="px-6 py-4 flex items-center justify-between <?php echo $info['exists'] ? 'bg-gray-50' : 'bg-rose-50'; ?>">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-black font-mono text-slate-800"><?php echo htmlspecialchars($table); ?></span>
                </div>
                <?php if ($info['exists']): ?>
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-black rounded-lg">✓ EXISTS</span>
                <?php
    else: ?>
                <span class="px-2 py-0.5 bg-rose-100 text-rose-700 text-xs font-black rounded-lg">✗ TABLE MISSING</span>
                <?php
    endif; ?>
            </div>
            <?php if ($info['exists'] && !empty($info['columns'])): ?>
            <div class="p-4 flex flex-wrap gap-2">
                <?php foreach ($info['columns'] as $col => $status): ?>
                <span class="px-2 py-1 rounded-lg text-[10px] font-black <?php echo $status === 'ok' ? 'bg-slate-100 text-slate-600' : 'bg-rose-100 text-rose-700 border border-rose-200'; ?>">
                    <?php echo $status === 'ok' ? '✓' : '✗'; ?> <?php echo htmlspecialchars($col); ?>
                    <?php if ($status !== 'ok'): ?> <span class="text-[8px]">(MISSING)</span><?php
            endif; ?>
                </span>
                <?php
        endforeach; ?>
            </div>
            <?php
    endif; ?>
        </div>
        <?php
endforeach; ?>
    </div>

    <div class="mt-10 bg-slate-50 rounded-2xl p-6 border border-slate-100">
        <h3 class="font-black text-slate-800 mb-4">🔧 Auto-Fix: Run Missing Migrations</h3>
        <div class="flex flex-wrap gap-3 text-xs font-bold">
            <a href="migrate_spouse_enhancements.php" class="px-4 py-2 bg-brand-600 text-white rounded-xl hover:bg-brand-700 transition-all">Run: Spouse Enhancements</a>
            <a href="migrate_prescription_tests.php" class="px-4 py-2 bg-violet-600 text-white rounded-xl hover:bg-violet-700 transition-all">Run: Prescription Items</a>
            <a href="migrate_lab_tests_library.php" class="px-4 py-2 bg-amber-600 text-white rounded-xl hover:bg-amber-700 transition-all">Run: Lab Tests Library</a>
            <a href="migrate_patients_v2.php" class="px-4 py-2 bg-cyan-600 text-white rounded-xl hover:bg-cyan-700 transition-all">Run: Patients V2</a>
            <a href="migrate_procedures_financials.php" class="px-4 py-2 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-all">Run: Procedures & Financials</a>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
