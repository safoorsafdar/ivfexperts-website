<?php
$pageTitle = "Semen Analysis Registry";
require_once __DIR__ . '/includes/auth.php';

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $conn->query("DELETE FROM semen_analyses WHERE id = $id");
    header("Location: semen_analyses.php?msg=deleted");
    exit;
}

$msg = $_GET['msg'] ?? '';

// Fetch SA Records
$records = [];
try {
    $stmt = $conn->query("
        SELECT sa.id, sa.collection_time, sa.auto_diagnosis, sa.concentration, sa.pr_motility, sa.np_motility, sa.report_type, sa.report_file_path, p.first_name, p.last_name, p.mr_number, h.name as hospital_name 
        FROM semen_analyses sa 
        JOIN patients p ON sa.patient_id = p.id 
        LEFT JOIN hospitals h ON sa.hospital_id = h.id 
        ORDER BY sa.collection_time DESC LIMIT 100
    ");
    if ($stmt) {
        while ($row = $stmt->fetch_assoc())
            $records[] = $row;
    }
}
catch (Exception $e) {
}

include __DIR__ . '/includes/header.php';
?>

<?php if ($msg === 'deleted'): ?>
    <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 border border-red-100 flex items-center gap-3 shadow-sm">
        <i class="fa-solid fa-trash text-xl"></i>
        <span class="font-bold">Semen analysis record deleted successfully.</span>
    </div>
<?php
endif; ?>
<?php if ($msg === 'saved'): ?>
    <div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl mb-6 border border-emerald-100 flex items-center gap-3 shadow-sm">
        <i class="fa-solid fa-circle-check text-xl"></i> 
        <span class="block font-bold">Advanced Semen Analysis record finalized and saved successfully.</span>
    </div>
<?php
endif; ?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-bold text-gray-800">Semen Analysis Registry</h2>
    <a href="semen_analyses_add.php" class="bg-sky-600 hover:bg-sky-700 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2 shadow-sm">
        <i class="fa-solid fa-plus"></i> New Report
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase font-semibold text-xs tracking-wider border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4">Report Date</th>
                    <th class="px-6 py-4">Patient</th>
                    <th class="px-6 py-4">Diagnosis</th>
                    <th class="px-6 py-4">Key Metrics</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php if (empty($records)): ?>
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No semen analyses recorded yet.</td></tr>
                <?php
else:
    foreach ($records as $r):
        $isNormal = ($r['auto_diagnosis'] === 'Normozoospermia');
        $isAzoo = ($r['auto_diagnosis'] === 'Azoospermia');
        $theme = $isNormal ? 'emerald' : ($isAzoo ? 'red' : 'amber');
?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-bold text-gray-800 block"><?php echo date('d M Y', strtotime($r['collection_time'])); ?></span>
                            <span class="text-xs text-gray-500">ID: #SA-<?php echo str_pad($r['id'], 4, '0', STR_PAD_LEFT); ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-800"><?php echo esc($r['first_name'] . ' ' . $r['last_name']); ?></div>
                            <div class="text-xs text-gray-500 font-mono">MR: <?php echo esc($r['mr_number']); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-<?php echo $theme; ?>-50 text-<?php echo $theme; ?>-700 px-3 py-1 rounded border border-<?php echo $theme; ?>-200 font-bold text-xs">
                                <?php echo esc($r['auto_diagnosis']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs">
                            <div class="text-gray-500">Conc: <span class="text-gray-800 font-semibold"><?php echo $r['concentration']; ?> M/ml</span></div>
                            <div class="text-gray-500 mt-1">Motility: <span class="text-gray-800 font-semibold"><?php echo($r['pr_motility'] + $r['np_motility']); ?>%</span></div>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <?php if ($r['report_type'] === 'file' && !empty($r['report_file_path'])): ?>
                                <a href="../<?php echo esc($r['report_file_path']); ?>" target="_blank" class="text-rose-600 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-md font-medium transition-colors inline-block mr-1" title="View PDF/Image Attachment">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>
                            <?php
        else: ?>
                                <a href="semen_analyses_print.php?id=<?php echo $r['id']; ?>" target="_blank" class="text-sky-600 hover:text-sky-900 bg-sky-50 hover:bg-sky-100 px-3 py-1.5 rounded-md font-medium transition-colors inline-block mr-1" title="View Digital Report">
                                    <i class="fa-solid fa-file-lines"></i>
                                </a>
                            <?php
        endif; ?>
                            <a href="semen_analyses_add.php?edit=<?php echo $r['id']; ?>" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md font-medium transition-colors inline-block mr-1" title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <button onclick="confirmDelete(<?php echo $r['id']; ?>, '#SA-<?php echo str_pad($r['id'], 4, '0', STR_PAD_LEFT); ?>')" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md font-medium transition-colors inline-block cursor-pointer" title="Delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php
    endforeach;
endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.5);" onclick="if(event.target===this)closeDeleteModal()">
    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:16px;padding:32px;max-width:400px;width:90%;box-shadow:0 25px 50px rgba(0,0,0,0.15);">
        <div style="text-align:center;">
            <div style="width:56px;height:56px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;font-size:24px;"></i>
            </div>
            <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:8px;">Delete Semen Analysis?</h3>
            <p style="color:#64748b;font-size:14px;margin-bottom:24px;">Are you sure you want to delete <strong id="deleteItemName"></strong>? This cannot be undone.</p>
            <form id="deleteForm" method="POST" style="display:inline;">
                <input type="hidden" name="delete_id" id="deleteId">
                <button type="button" onclick="closeDeleteModal()" style="padding:10px 24px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;color:#64748b;font-weight:600;font-size:14px;cursor:pointer;margin-right:8px;">Cancel</button>
                <button type="submit" style="padding:10px 24px;border-radius:8px;border:none;background:#ef4444;color:#fff;font-weight:600;font-size:14px;cursor:pointer;">Delete</button>
            </form>
        </div>
    </div>
</div>
<script>
function confirmDelete(id, name) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteItemName').textContent = name;
    document.getElementById('deleteModal').style.display = 'block';
}
function closeDeleteModal() { document.getElementById('deleteModal').style.display = 'none'; }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
