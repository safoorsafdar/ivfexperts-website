<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Ghost Recovery - Re-link Uploads';
include __DIR__ . '/includes/header.php';

$dirs = [
    'Labs' => '../uploads/labs/',
    'Prescriptions' => '../uploads/prescriptions/',
    'Ultrasounds' => '../uploads/ultrasounds/'
];

$success = '';
$error = '';

// Handle Re-linking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['relink_file'])) {
    $mr_number = trim($_POST['mr_number']);
    $file_path = $_POST['file_path'];
    $type = $_POST['type'];

    // Find patient by MR
    $stmt = $conn->prepare("SELECT id FROM patients WHERE mr_number = ?");
    $stmt->bind_param("s", $mr_number);
    $stmt->execute();
    $p_res = $stmt->get_result()->fetch_assoc();

    if (!$p_res) {
        $error = "Patient with MR Number '$mr_number' not found. Please create the patient first.";
    }
    else {
        $patient_id = $p_res['id'];
        $hash = bin2hex(random_bytes(16));

        try {
            if ($type === 'Labs') {
                $test_id = intval($_POST['test_id'] ?? 0);
                if ($test_id === 0)
                    throw new Exception("Please select a Lab Test type.");

                $stmt = $conn->prepare("INSERT INTO patient_lab_results (patient_id, test_id, test_date, status, scanned_report_path) VALUES (?, ?, CURDATE(), 'Completed', ?)");
                $stmt->bind_param("iis", $patient_id, $test_id, $file_path);
            }
            elseif ($type === 'Prescriptions') {
                $stmt = $conn->prepare("INSERT INTO prescriptions (patient_id, qrcode_hash, scanned_report_path) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $patient_id, $hash, $file_path);
            }
            elseif ($type === 'Ultrasounds') {
                $title = trim($_POST['title'] ?: 'Restored Ultrasound');
                $stmt = $conn->prepare("INSERT INTO patient_ultrasounds (patient_id, qrcode_hash, report_title, content, scanned_report_path) VALUES (?, ?, ?, 'Restored from history.', ?)");
                $stmt->bind_param("isss", $patient_id, $hash, $title, $file_path);
            }

            if ($stmt->execute()) {
                $success = "File successfully re-linked to patient MR: $mr_number";
            }
        }
        catch (Exception $e) {
            $error = "Recovery failed: " . $e->getMessage();
        }
    }
}

// Fetch Tests for dropdown
$tests = $conn->query("SELECT id, test_name FROM lab_tests_directory ORDER BY test_name ASC")->fetch_all(MYSQLI_ASSOC);

// Scan for orphaned files
$orphans = [];
foreach ($dirs as $label => $path) {
    if (is_dir($path)) {
        $files = scaffold_scan_dir($path);
        foreach ($files as $f) {
            $rel_path = str_replace('../', '', $path . $f);

            // Check if this file is ALREADY in the database
            $exists = false;
            try {
                if ($label === 'Labs') {
                    $chk = $conn->prepare("SELECT id FROM patient_lab_results WHERE scanned_report_path = ?");
                }
                elseif ($label === 'Prescriptions') {
                    $chk = $conn->prepare("SELECT id FROM prescriptions WHERE scanned_report_path = ?");
                }
                else {
                    $chk = $conn->prepare("SELECT id FROM patient_ultrasounds WHERE scanned_report_path = ?");
                }

                $chk->bind_param("s", $rel_path);
                $chk->execute();
                if ($chk->get_result()->num_rows > 0)
                    $exists = true;
            }
            catch (Exception $e) {
                // Table might not exist yet if migration not run
                $exists = false;
            }

            if (!$exists) {
                $orphans[] = ['name' => $f, 'path' => $rel_path, 'type' => $label];
            }
        }
    }
}

function scaffold_scan_dir($dir)
{
    $result = [];
    if (!is_dir($dir))
        return $result;
    $cdir = scandir($dir);
    foreach ($cdir as $value) {
        if (!in_array($value, array(".", ".."))) {
            if (!is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                $result[] = $value;
            }
        }
    }
    return $result;
}
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
            <span class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                <i class="fa-solid fa-ghost"></i>
            </span>
            Ghost Recovery Tool
        </h1>
        <p class="text-slate-500 mt-2 font-medium">Link orphaned files from your <b>uploads/</b> folder back to new patient records.</p>
    </div>

    <?php if ($success): ?>
        <div class="bg-emerald-50 text-emerald-600 p-6 rounded-3xl mb-8 border border-emerald-100 flex items-center gap-3 font-bold shadow-sm shadow-emerald-50">
            <i class="fa-solid fa-check-circle text-2xl"></i> <?php echo $success; ?>
        </div>
    <?php
endif; ?>

    <?php if ($error): ?>
        <div class="bg-rose-50 text-rose-600 p-6 rounded-3xl mb-8 border border-rose-100 flex items-center gap-3 font-bold shadow-sm shadow-rose-50">
            <i class="fa-solid fa-triangle-exclamation text-2xl"></i> <?php echo $error; ?>
        </div>
    <?php
endif; ?>

    <div class="bg-white rounded-[2.5rem] border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/80 border-b border-slate-100">
                    <tr class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                        <th class="px-8 py-5">Orphaned File Info</th>
                        <th class="px-8 py-5">Classification</th>
                        <th class="px-8 py-5">Target & Assignment</th>
                        <th class="px-8 py-5 text-right">Relink Engine</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($orphans)): ?>
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center">
                                <i class="fa-solid fa-shield-check text-5xl text-slate-100 mb-4 block"></i>
                                <div class="text-slate-400 font-black uppercase tracking-widest text-xs">No Orphans Detected</div>
                                <div class="text-slate-300 text-sm mt-1">All files in your upload directories are mapped to database records.</div>
                            </td>
                        </tr>
                    <?php
else:
    foreach ($orphans as $o): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </div>
                                    <div>
                                        <div class="font-black text-slate-800 text-sm break-all max-w-xs"><?php echo $o['name']; ?></div>
                                        <a href="../<?php echo $o['path']; ?>" target="_blank" class="text-[10px] font-bold text-indigo-500 hover:text-indigo-700 uppercase tracking-wider flex items-center gap-1 mt-1 transition-colors">
                                            <i class="fa-solid fa-external-link text-[8px]"></i> Visual Preview
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="px-3 py-1 rounded-lg bg-slate-100 text-[10px] font-black uppercase text-slate-500 tracking-wider">
                                    <?php echo $o['type']; ?>
                                </span>
                            </td>
                            <form method="POST">
                                <input type="hidden" name="file_path" value="<?php echo $o['path']; ?>">
                                <input type="hidden" name="type" value="<?php echo $o['type']; ?>">
                                <td class="px-8 py-6">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">New MR Number</label>
                                            <input type="text" name="mr_number" required placeholder="MR-001" class="px-4 py-2 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none w-full bg-slate-50/50">
                                        </div>
                                        
                                        <?php if ($o['type'] === 'Labs'): ?>
                                            <div>
                                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Test Classification</label>
                                                <select name="test_id" required class="w-full px-4 py-2 border border-slate-200 rounded-xl text-xs font-bold outline-none bg-slate-50/50">
                                                    <option value="">-- Select Test --</option>
                                                    <?php foreach ($tests as $t): ?>
                                                        <option value="<?php echo $t['id']; ?>"><?php echo $t['test_name']; ?></option>
                                                    <?php
            endforeach; ?>
                                                </select>
                                            </div>
                                        <?php
        elseif ($o['type'] === 'Ultrasounds'): ?>
                                            <div>
                                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Scan Title</label>
                                                <input type="text" name="title" placeholder="e.g. Follicular Study" class="w-full px-4 py-2 border border-slate-200 rounded-xl text-xs font-bold outline-none bg-slate-50/50">
                                            </div>
                                        <?php
        endif; ?>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <button type="submit" name="relink_file" class="bg-indigo-600 text-white text-[10px] font-black uppercase px-6 py-3 rounded-2xl hover:bg-slate-900 transition-all shadow-lg shadow-indigo-100 hover:shadow-indigo-200">
                                        Re-Link
                                    </button>
                                </td>
                            </form>
                        </tr>
                    <?php
    endforeach;
endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
