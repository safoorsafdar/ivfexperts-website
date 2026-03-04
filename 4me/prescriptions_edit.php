<?php
/**
 * prescriptions_edit.php
 * Loads existing prescription data and redirects to the add wizard pre-populated.
 * Since prescriptions_add.php is a full wizard, we display a simple edit form here.
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

// Fetch medications
$items = $conn->query("SELECT * FROM prescription_items WHERE prescription_id = $rx_id ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);

// Handle update
$save_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_prescription'])) {
    $diagnosis = trim($_POST['diagnosis'] ?? '');
    $clinical_notes = trim($_POST['clinical_notes'] ?? '');
    $general_advice = trim($_POST['general_advice'] ?? '');
    $next_visit = !empty($_POST['next_visit']) ? $_POST['next_visit'] : null;
    $record_for = in_array($_POST['record_for'] ?? '', ['Patient', 'Spouse']) ? $_POST['record_for'] : 'Patient';

    $upd = $conn->prepare(
        "UPDATE prescriptions SET diagnosis=?, clinical_notes=?, general_advice=?, next_visit=?, record_for=? WHERE id=?"
    );
    if ($upd) {
        $upd->bind_param("sssssi", $diagnosis, $clinical_notes, $general_advice, $next_visit, $record_for, $rx_id);
        if ($upd->execute()) {
            // Replace medication items
            $conn->query("DELETE FROM prescription_items WHERE prescription_id = $rx_id");
            $medications_json = $_POST['medications_data'] ?? '[]';
            $meds = json_decode($medications_json, true);
            if (is_array($meds)) {
                $m_stmt = $conn->prepare("INSERT INTO prescription_items (prescription_id, medicine_name, dosage, frequency, duration, instructions) VALUES (?,?,?,?,?,?)");
                if ($m_stmt) {
                    foreach ($meds as $m) {
                        if (empty($m['medicine_name']))
                            continue;
                        $name = $m['medicine_name'] ?? '';
                        $dose = $m['dosage'] ?? '';
                        $freq = $m['frequency'] ?? '';
                        $dur = $m['duration'] ?? '';
                        $instr = $m['instructions'] ?? '';
                        $m_stmt->bind_param("isssss", $rx_id, $name, $dose, $freq, $dur, $instr);
                        $m_stmt->execute();
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
?>
<div class="max-w-3xl mx-auto px-4 py-8">
    <nav class="flex items-center gap-2 text-[10px] font-semibold uppercase tracking-widest text-gray-400 mb-6">
        <a href="patients_view.php?id=<?php echo $patient_id; ?>&tab=rx" class="hover:text-teal-600">← Back to Prescriptions</a>
    </nav>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-teal-500 to-teal-600 px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-white font-semibold text-lg">Edit Prescription #RX-<?php echo str_pad($rx_id, 5, '0', STR_PAD_LEFT); ?></h1>
                <p class="text-teal-100 text-xs mt-0.5"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?> · <?php echo htmlspecialchars($patient['mr_number'] ?? ''); ?></p>
            </div>
            <a href="prescriptions_print.php?id=<?php echo $rx_id; ?>" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-white text-teal-700 rounded-xl text-xs font-semibold hover:bg-teal-50 transition-all">
                <i class="fa-solid fa-print"></i> Print / PDF
            </a>
        </div>

        <?php if ($save_error): ?>
        <div class="mx-6 mt-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm"><?php echo htmlspecialchars($save_error); ?></div>
        <?php
endif; ?>

        <form method="POST" class="p-6 space-y-5" x-data="{
            medsRaw: <?php echo htmlspecialchars(json_encode(array_map(fn($i) => [
'medicine_name' => $i['medicine_name'],
'dosage' => $i['dosage'] ?? '',
'frequency' => $i['frequency'] ?? '',
'duration' => $i['duration'] ?? '',
'instructions' => $i['instructions'] ?? '',
], $items))); ?>,
            addMed() { this.medsRaw.push({medicine_name:'',dosage:'',frequency:'',duration:'',instructions:''}); },
            removeMed(i) { this.medsRaw.splice(i,1); }
        }">
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

            <!-- Diagnosis -->
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Diagnosis / ICD Notes</label>
                <textarea name="diagnosis" rows="3" class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-300 outline-none transition-all"><?php echo htmlspecialchars($rx['diagnosis'] ?? ''); ?></textarea>
            </div>

            <!-- Clinical Notes -->
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Clinical Notes</label>
                <textarea name="clinical_notes" rows="3" class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-300 outline-none transition-all"><?php echo htmlspecialchars($rx['clinical_notes'] ?? ''); ?></textarea>
            </div>

            <!-- Medications -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <label class="text-xs font-medium text-slate-500">Medications</label>
                    <button type="button" @click="addMed()" class="text-xs text-teal-600 hover:text-teal-800 font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-plus text-[10px]"></i> Add Medicine
                    </button>
                </div>
                <div class="space-y-3">
                    <template x-for="(med, idx) in medsRaw" :key="idx">
                        <div class="bg-gray-50 rounded-xl p-3 border border-gray-100 grid grid-cols-2 md:grid-cols-3 gap-2 relative">
                            <button type="button" @click="removeMed(idx)" class="absolute top-2 right-2 w-6 h-6 rounded-lg text-gray-300 hover:text-rose-500 hover:bg-rose-50 flex items-center justify-center text-xs transition-all">
                                <i class="fa-solid fa-times"></i>
                            </button>
                            <div class="col-span-2 md:col-span-3">
                                <input type="text" x-model="med.medicine_name" placeholder="Medicine name *"
                                       class="w-full px-3 py-2 bg-white border border-gray-100 rounded-lg text-sm focus:ring-2 focus:ring-teal-500/20 outline-none">
                            </div>
                            <input type="text" x-model="med.dosage" placeholder="Dosage (e.g. 500mg)"
                                   class="px-3 py-2 bg-white border border-gray-100 rounded-lg text-sm focus:ring-2 focus:ring-teal-500/20 outline-none">
                            <input type="text" x-model="med.frequency" placeholder="Frequency (e.g. BD)"
                                   class="px-3 py-2 bg-white border border-gray-100 rounded-lg text-sm focus:ring-2 focus:ring-teal-500/20 outline-none">
                            <input type="text" x-model="med.duration" placeholder="Duration (e.g. 7 days)"
                                   class="px-3 py-2 bg-white border border-gray-100 rounded-lg text-sm focus:ring-2 focus:ring-teal-500/20 outline-none">
                            <input type="text" x-model="med.instructions" placeholder="Instructions (optional)"
                                   class="col-span-2 md:col-span-3 px-3 py-2 bg-white border border-gray-100 rounded-lg text-sm focus:ring-2 focus:ring-teal-500/20 outline-none">
                        </div>
                    </template>
                </div>
                <input type="hidden" name="medications_data" :value="JSON.stringify(medsRaw)">
            </div>

            <!-- General Advice + Next Visit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1.5">General Advice</label>
                    <textarea name="general_advice" rows="3" class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-300 outline-none transition-all"><?php echo htmlspecialchars($rx['general_advice'] ?? ''); ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1.5">Next Visit</label>
                    <input type="date" name="next_visit" value="<?php echo htmlspecialchars($rx['next_visit'] ?? ''); ?>"
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-300 outline-none transition-all">
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="patients_view.php?id=<?php echo $patient_id; ?>&tab=rx"
                   class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-500 bg-gray-100 hover:bg-gray-200 transition-all">Cancel</a>
                <button type="submit" name="update_prescription" value="1"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-teal-600 hover:bg-teal-700 shadow-sm transition-all active:scale-95">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
