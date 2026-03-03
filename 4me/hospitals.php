<?php
$pageTitle = "Manage Hospitals & Clinics";
require_once __DIR__ . '/includes/auth.php';

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $conn->query("DELETE FROM hospitals WHERE id = $id");
    header("Location: hospitals.php?msg=deleted");
    exit;
}
// Fetch Hospitals
$hospitals = [];
try {
    $res = $conn->query("SELECT * FROM hospitals ORDER BY name ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $hospitals[] = $row;
        }
    }
}
catch (Exception $e) {
}

include __DIR__ . '/includes/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-800">Hospital & Clinic Management</h2>
        <p class="text-gray-500 text-sm mt-1">Configure logos, digital signatures, and custom print layout margins per location.</p>
    </div>
    <a href="hospitals_edit.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-colors flex items-center gap-2">
        <i class="fa-solid fa-plus"></i> Add New Hospital
    </a>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'saved'): ?>
    <div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl mb-6 flex gap-2 items-center border border-emerald-100 shadow-sm">
        <i class="fa-solid fa-circle-check text-lg mt-0.5"></i> <span class="font-bold">Hospital details saved successfully!</span>
    </div>
<?php
elseif (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 flex gap-2 items-center border border-red-100 shadow-sm">
        <i class="fa-solid fa-trash text-lg mt-0.5"></i> <span class="font-bold">Hospital deleted successfully!</span>
    </div>
<?php
endif; ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase font-semibold text-[11px] tracking-wider border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4">Hospital Name & Layout</th>
                    <th class="px-6 py-4 text-center">Brand Logo</th>
                    <th class="px-6 py-4 text-center">Digital Signature</th>
                    <th class="px-6 py-4">Letterhead Margins (T / B / L / R)</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php if (empty($hospitals)): ?>
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No hospitals found. Add one above.</td></tr>
                <?php
else:
    foreach ($hospitals as $h): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-800 text-base flex items-center gap-2">
                                <i class="fa-regular fa-hospital text-indigo-400"></i> <?php echo esc($h['name']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if (!empty($h['logo_path'])): ?>
                                <img src="../<?php echo esc($h['logo_path']); ?>" alt="Logo" class="h-10 mx-auto object-contain bg-gray-100 p-1 rounded border border-gray-200" title="Custom Logo Active">
                            <?php
        else: ?>
                                <span class="text-xs text-gray-400 italic">IVF Experts Default</span>
                            <?php
        endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if (!empty($h['digital_signature_path'])): ?>
                                <span class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-2 py-1 rounded text-xs font-bold whitespace-nowrap"><i class="fa-solid fa-check mr-1"></i> Uploaded</span>
                            <?php
        else: ?>
                                <span class="bg-red-50 border border-red-200 text-red-600 px-2 py-1 rounded text-xs whitespace-nowrap">Missing</span>
                            <?php
        endif; ?>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-gray-600 whitespace-nowrap">
                            <span class="inline-block bg-gray-100 px-2 py-1 rounded border border-gray-200"><?php echo esc($h['margin_top'] ?? '20mm'); ?></span> / 
                            <span class="inline-block bg-gray-100 px-2 py-1 rounded border border-gray-200"><?php echo esc($h['margin_bottom'] ?? '20mm'); ?></span> / 
                            <span class="inline-block bg-gray-100 px-2 py-1 rounded border border-gray-200"><?php echo esc($h['margin_left'] ?? '20mm'); ?></span> / 
                            <span class="inline-block bg-gray-100 px-2 py-1 rounded border border-gray-200"><?php echo esc($h['margin_right'] ?? '20mm'); ?></span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <a href="hospitals_edit.php?id=<?php echo $h['id']; ?>" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-md font-medium transition-colors inline-block mr-1" title="Configure">
                                <i class="fa-solid fa-gear"></i>
                            </a>
                            <button onclick="confirmDelete(<?php echo $h['id']; ?>, '<?php echo esc($h['name']); ?>')" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md font-medium transition-colors inline-block cursor-pointer" title="Delete">
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
            <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:8px;">Delete Hospital?</h3>
            <p style="color:#64748b;font-size:14px;margin-bottom:24px;">Are you sure you want to delete <strong id="deleteItemName"></strong>? Patients linked to this hospital will show as "Direct / Walk-in".</p>
            <form method="POST" style="display:inline;">
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
