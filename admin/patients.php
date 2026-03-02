<?php
$pageTitle = "Patient Registry";
require_once __DIR__ . '/includes/auth.php';

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];

    try {
        $conn->begin_transaction();

        // Delete child records first
        $conn->query("DELETE FROM patient_history WHERE patient_id = $id");

        // Handle Prescriptions (must delete items/diagnoses first)
        $conn->query("DELETE FROM prescription_items WHERE prescription_id IN (SELECT id FROM prescriptions WHERE patient_id = $id)");
        $conn->query("DELETE FROM prescription_diagnoses WHERE prescription_id IN (SELECT id FROM prescriptions WHERE patient_id = $id)");
        $conn->query("DELETE FROM prescriptions WHERE patient_id = $id");

        // Corrected table names from previous search
        $conn->query("DELETE FROM patient_ultrasounds WHERE patient_id = $id");
        $conn->query("DELETE FROM semen_analyses WHERE patient_id = $id");
        $conn->query("DELETE FROM patient_lab_results WHERE patient_id = $id");
        $conn->query("DELETE FROM receipts WHERE patient_id = $id");
        $conn->query("DELETE FROM advised_procedures WHERE patient_id = $id");

        // Finally delete the patient
        $conn->query("DELETE FROM patients WHERE id = $id");

        $conn->commit();
        header("Location: patients.php?msg=deleted");
        exit;
    }
    catch (Exception $e) {
        $conn->rollback();
        die("Deletion Error: " . $e->getMessage());
    }
}

// Handle Search
$search = trim($_GET['q'] ?? '');
$msg = $_GET['msg'] ?? '';
$patients = [];

try {
    if (!empty($search)) {
        $stmt = $conn->prepare("SELECT p.*, h.name as hospital_name FROM patients p 
                                LEFT JOIN hospitals h ON p.referring_hospital_id = h.id 
                                WHERE p.mr_number LIKE ? 
                                OR p.cnic LIKE ? 
                                OR p.phone LIKE ? 
                                OR p.first_name LIKE ? 
                                OR p.last_name LIKE ? 
                                OR p.spouse_name LIKE ? 
                                OR p.spouse_phone LIKE ? 
                                OR p.spouse_cnic LIKE ? 
                                ORDER BY p.id DESC LIMIT 50");
        $like = "%$search%";
        if ($stmt) {
            $stmt->bind_param("ssssssss", $like, $like, $like, $like, $like, $like, $like, $like);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc())
                $patients[] = $row;
        }
    }
    else {
        $res = $conn->query("SELECT p.*, h.name as hospital_name FROM patients p LEFT JOIN hospitals h ON p.referring_hospital_id = h.id ORDER BY p.id DESC LIMIT 50");
        if ($res) {
            while ($row = $res->fetch_assoc())
                $patients[] = $row;
        }
    }
}
catch (Exception $e) {
}

include __DIR__ . '/includes/header.php';
?>

<?php if ($msg === 'deleted'): ?>
    <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 border border-red-100 flex items-center gap-3 shadow-sm">
        <i class="fa-solid fa-trash text-xl"></i>
        <span class="font-bold">Patient record deleted successfully.</span>
    </div>
<?php
endif; ?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="w-full sm:w-96 relative">
        <form method="GET">
            <input type="text" name="q" value="<?php echo esc($search); ?>" placeholder="Search MR, CNIC, Phone, Name..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors bg-white">
            <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
        </form>
    </div>
    <a href="patients_add.php" class="shrink-0 bg-teal-600 hover:bg-teal-700 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2">
        <i class="fa-solid fa-user-plus"></i> Register Patient
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase font-semibold text-xs tracking-wider border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4">MR Number</th>
                    <th class="px-6 py-4">Patient Name</th>
                    <th class="px-6 py-4">Phone / CNIC</th>
                    <th class="px-6 py-4">Gender</th>
                    <th class="px-6 py-4">Referred By</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php if (empty($patients)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                            <i class="fa-solid fa-users-slash text-3xl mb-3 block"></i>
                            No patients found.
                        </td>
                    </tr>
                <?php
else:
    foreach ($patients as $p): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-mono font-medium text-teal-700">
                            <?php echo esc($p['mr_number']); ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-800"><?php echo esc($p['first_name'] . ' ' . $p['last_name']); ?></div>
                            <div class="text-xs text-gray-500">Spouse: <?php echo esc($p['spouse_name']); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div><i class="fa-solid fa-phone text-gray-400 w-4"></i> <?php echo esc($p['phone'] ?: 'N/A'); ?></div>
                            <div class="text-xs mt-1 text-gray-500"><i class="fa-regular fa-id-card text-gray-400 w-4"></i> <?php echo esc($p['cnic'] ?: 'N/A'); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">
                                <?php echo esc($p['gender']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs">
                            <?php echo esc($p['hospital_name'] ?: 'Direct / Walk-in'); ?>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <a href="patients_view.php?id=<?php echo $p['id']; ?>" class="text-teal-600 hover:text-teal-900 bg-teal-50 hover:bg-teal-100 px-3 py-1.5 rounded-md font-medium transition-colors inline-block mr-1" title="Open File">
                                <i class="fa-regular fa-folder-open"></i>
                            </a>
                            <a href="patients_edit.php?id=<?php echo $p['id']; ?>" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md font-medium transition-colors inline-block mr-1" title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <button onclick="confirmDelete(<?php echo $p['id']; ?>, '<?php echo esc($p['first_name'] . ' ' . $p['last_name']); ?>')" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md font-medium transition-colors inline-block cursor-pointer" title="Delete">
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
            <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:8px;">Delete Patient?</h3>
            <p style="color:#64748b;font-size:14px;margin-bottom:24px;">Are you sure you want to delete <strong id="deletePatientName"></strong>? This will also delete all related records (history, prescriptions, ultrasounds, lab results). This cannot be undone.</p>
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
    document.getElementById('deletePatientName').textContent = name;
    document.getElementById('deleteModal').style.display = 'block';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

