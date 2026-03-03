<?php
$pageTitle = "Medication Arsenal";
require_once __DIR__ . '/includes/auth.php';

$error = '';
$success = '';

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $conn->query("DELETE FROM medications WHERE id = $id");
    header("Location: medications.php?msg=deleted");
    exit;
}

// Handle Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_med'])) {
    $id = (int)$_POST['edit_id'];
    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['med_type'] ?? 'Other');
    $vendor = trim($_POST['vendor'] ?? '');
    $price = !empty($_POST['price']) ? floatval($_POST['price']) : null;
    if (!empty($name)) {
        $stmt = $conn->prepare("UPDATE medications SET name=?, med_type=?, vendor=?, price=? WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("sssdi", $name, $type, $vendor, $price, $id);
            $stmt->execute();
            $success = "Medication updated successfully!";
        }
    }
}

// Handle Add Medication
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_med'])) {
    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['med_type'] ?? 'Other');
    $vendor = trim($_POST['vendor'] ?? '');
    $price = !empty($_POST['price']) ? floatval($_POST['price']) : null;

    if (empty($name)) {
        $error = "Medication name is required.";
    }
    else {
        $stmt = $conn->prepare("INSERT INTO medications (name, med_type, vendor, price) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssd", $name, $type, $vendor, $price);
            if ($stmt->execute()) {
                $success = "Medication added successfully!";
            }
            else {
                $error = "Failed to add medication: " . $stmt->error;
            }
        }
    }
}

$msg = $_GET['msg'] ?? '';
if ($msg === 'deleted')
    $success = 'Medication deleted successfully!';

// Fetch Medications
$meds = [];
try {
    $res = $conn->query("SELECT * FROM medications ORDER BY name ASC");
    if ($res) {
        while ($row = $res->fetch_assoc())
            $meds[] = $row;
    }
}
catch (Exception $e) {
}

include __DIR__ . '/includes/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Add Form Sidebar -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-teal-900 text-white">
                <h3 class="font-bold flex items-center gap-2">
                    <i class="fa-solid fa-pills text-teal-300"></i> Add to Arsenal
                </h3>
            </div>
            <div class="p-6">
                <?php if (!empty($error)): ?>
                    <div class="text-sm text-red-600 bg-red-50 p-3 rounded-lg mb-4 border border-red-100 flex gap-2">
                        <i class="fa-solid fa-circle-exclamation mt-0.5"></i> <?php echo esc($error); ?>
                    </div>
                <?php
endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="text-sm text-emerald-700 bg-emerald-50 p-3 rounded-lg mb-4 border border-emerald-100 flex gap-2">
                        <i class="fa-solid fa-circle-check mt-0.5"></i> <?php echo esc($success); ?>
                    </div>
                <?php
endif; ?>

                <form method="POST">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Medication Name *</label>
                            <input type="text" name="name" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-500 focus:border-teal-500 text-sm" placeholder="e.g. Gonal-f, Letrozole" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                            <select name="med_type" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white text-sm">
                                <option value="Injection">Injection</option>
                                <option value="Tablet">Tablet</option>
                                <option value="Capsule">Capsule</option>
                                <option value="Sachet">Sachet</option>
                                <option value="Syrup">Syrup</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vendor / Pharmacy</label>
                            <input type="text" name="vendor" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-500 focus:border-teal-500 text-sm" placeholder="Optional">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price / Cost (Rs)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-400 text-sm">Rs.</span>
                                <input type="number" step="0.01" name="price" class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-500 focus:border-teal-500 text-sm">
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="add_med" class="w-full mt-6 bg-teal-600 hover:bg-teal-700 text-white font-bold py-2.5 px-4 rounded-lg transition-colors shadow-sm">
                        Add Medication
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Inventory List -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">Arsenal Inventory</h3>
                <span class="text-xs text-gray-500 bg-white px-3 py-1 rounded-full border border-gray-200 font-medium"><?php echo count($meds); ?> total</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-gray-700 uppercase font-semibold text-xs tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4">Vendor</th>
                            <th class="px-6 py-4">Price</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($meds)): ?>
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Your arsenal is currently empty. Add medications to build presets.</td></tr>
                        <?php
else:
    foreach ($meds as $m): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-bold text-gray-800"><?php echo esc($m['name']); ?></td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-md text-xs font-medium">
                                        <?php echo esc($m['med_type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-gray-500 text-xs">
                                    <?php echo esc($m['vendor'] ?: 'N/A'); ?>
                                </td>
                                <td class="px-6 py-3 font-mono text-teal-700">
                                    <?php echo $m['price'] ? 'Rs. ' . number_format($m['price'], 2) : '-'; ?>
                                </td>
                                <td class="px-6 py-3 text-right whitespace-nowrap">
                                    <button onclick='openEditModal(<?php echo json_encode($m); ?>)' class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md font-medium transition-colors inline-block mr-1 cursor-pointer" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button onclick="confirmDelete(<?php echo $m['id']; ?>, '<?php echo esc($m['name']); ?>')" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md font-medium transition-colors inline-block cursor-pointer" title="Delete">
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
    </div>

</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.5);" onclick="if(event.target===this)closeDeleteModal()">
    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:16px;padding:32px;max-width:400px;width:90%;box-shadow:0 25px 50px rgba(0,0,0,0.15);">
        <div style="text-align:center;">
            <div style="width:56px;height:56px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;font-size:24px;"></i>
            </div>
            <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:8px;">Delete Medication?</h3>
            <p style="color:#64748b;font-size:14px;margin-bottom:24px;">Are you sure you want to delete <strong id="deleteItemName"></strong>? This cannot be undone.</p>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="delete_id" id="deleteId">
                <button type="button" onclick="closeDeleteModal()" style="padding:10px 24px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;color:#64748b;font-weight:600;font-size:14px;cursor:pointer;margin-right:8px;">Cancel</button>
                <button type="submit" style="padding:10px 24px;border-radius:8px;border:none;background:#ef4444;color:#fff;font-weight:600;font-size:14px;cursor:pointer;">Delete</button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.5);" onclick="if(event.target===this)closeEditModal()">
    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:16px;padding:32px;max-width:450px;width:90%;box-shadow:0 25px 50px rgba(0,0,0,0.15);">
        <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:16px;"><i class="fa-solid fa-pen-to-square" style="color:#3b82f6;"></i> Edit Medication</h3>
        <form method="POST">
            <input type="hidden" name="edit_med" value="1">
            <input type="hidden" name="edit_id" id="editId">
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">Name</label>
                <input type="text" name="name" id="editName" required style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;">
            </div>
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">Type</label>
                <select name="med_type" id="editType" style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;">
                    <option value="Injection">Injection</option><option value="Tablet">Tablet</option><option value="Capsule">Capsule</option><option value="Sachet">Sachet</option><option value="Syrup">Syrup</option><option value="Other">Other</option>
                </select>
            </div>
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">Vendor</label>
                <input type="text" name="vendor" id="editVendor" style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;">
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">Price (Rs)</label>
                <input type="number" step="0.01" name="price" id="editPrice" style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;">
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" onclick="closeEditModal()" style="padding:10px 24px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;color:#64748b;font-weight:600;font-size:14px;cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:10px 24px;border-radius:8px;border:none;background:#3b82f6;color:#fff;font-weight:600;font-size:14px;cursor:pointer;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteItemName').textContent = name;
    document.getElementById('deleteModal').style.display = 'block';
}
function closeDeleteModal() { document.getElementById('deleteModal').style.display = 'none'; }
function openEditModal(m) {
    document.getElementById('editId').value = m.id;
    document.getElementById('editName').value = m.name;
    document.getElementById('editType').value = m.med_type;
    document.getElementById('editVendor').value = m.vendor || '';
    document.getElementById('editPrice').value = m.price || '';
    document.getElementById('editModal').style.display = 'block';
}
function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
