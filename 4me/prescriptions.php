<?php
$pageTitle = "Prescriptions History";
require_once __DIR__ . '/includes/auth.php';

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $conn->query("DELETE FROM prescription_items WHERE prescription_id = $id");
    $conn->query("DELETE FROM prescriptions WHERE id = $id");
    header("Location: prescriptions.php?msg=deleted");
    exit;
}

$msg = $_GET['msg'] ?? '';

// Fetch Prescriptions
$prescriptions = [];
try {
    $stmt = $conn->query("
        SELECT rx.id, rx.created_at, rx.notes, p.first_name, p.last_name, p.mr_number, h.name as hospital_name 
        FROM prescriptions rx 
        JOIN patients p ON rx.patient_id = p.id 
        LEFT JOIN hospitals h ON rx.hospital_id = h.id 
        ORDER BY rx.created_at DESC LIMIT 100
    ");
    if ($stmt) {
        while ($row = $stmt->fetch_assoc())
            $prescriptions[] = $row;
    }
}
catch (Exception $e) {
}

include __DIR__ . '/includes/header.php';
?>

<?php if ($msg === 'deleted'): ?>
    <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 border border-red-100 flex items-center gap-3 shadow-sm">
        <i class="fa-solid fa-trash text-xl"></i>
        <span class="font-bold">Prescription deleted successfully.</span>
    </div>
<?php
endif; ?>
<?php if ($msg === 'rx_saved'): ?>
    <div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl mb-6 border border-emerald-100 flex items-center gap-3 shadow-sm">
        <i class="fa-solid fa-circle-check text-xl"></i> 
        <div>
            <span class="block font-bold">Prescription Saved Successfully.</span>
            <span class="text-sm">The digital signature & QR code have been attached to the file.</span>
        </div>
    </div>
<?php
endif; ?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-bold text-gray-800">Prescription Records</h2>
    <a href="prescriptions_add.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2 shadow-sm">
        <i class="fa-solid fa-plus"></i> Write New Rx
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase font-semibold text-xs tracking-wider border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4">ID / Date</th>
                    <th class="px-6 py-4">Patient</th>
                    <th class="px-6 py-4">Hospital Branch</th>
                    <th class="px-6 py-4">Clinical Notes Preview</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php if (empty($prescriptions)): ?>
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No prescriptions recorded yet.</td></tr>
                <?php
else:
    foreach ($prescriptions as $rx): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-bold text-gray-800 block">#RX-<?php echo str_pad($rx['id'], 4, '0', STR_PAD_LEFT); ?></span>
                            <span class="text-xs text-gray-500"><?php echo date('d M Y, h:i A', strtotime($rx['created_at'])); ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-800"><?php echo esc($rx['first_name'] . ' ' . $rx['last_name']); ?></div>
                            <div class="text-xs text-gray-500 font-mono">MR: <?php echo esc($rx['mr_number']); ?></div>
                        </td>
                        <td class="px-6 py-4 text-xs font-medium text-teal-700">
                            <i class="fa-regular fa-hospital mr-1 text-teal-500"></i> <?php echo esc($rx['hospital_name'] ?: 'Main Clinic'); ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="truncate w-48 text-xs text-gray-500">
                                <?php echo esc(substr($rx['notes'], 0, 50) . (strlen($rx['notes']) > 50 ? '...' : '')); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <a href="prescriptions_print.php?id=<?php echo $rx['id']; ?>" target="_blank" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-md font-medium transition-colors inline-block mr-1" title="Print">
                                <i class="fa-solid fa-print"></i>
                            </a>
                            <a href="prescriptions_add.php?edit=<?php echo $rx['id']; ?>" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md font-medium transition-colors inline-block mr-1" title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <button onclick="confirmDelete(<?php echo $rx['id']; ?>, '#RX-<?php echo str_pad($rx['id'], 4, '0', STR_PAD_LEFT); ?>')" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md font-medium transition-colors inline-block cursor-pointer" title="Delete">
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
            <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:8px;">Delete Prescription?</h3>
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

