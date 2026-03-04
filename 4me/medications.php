<?php
$pageTitle = "Medication Arsenal";
require_once __DIR__ . '/includes/auth.php';

$error   = '';
$success = '';

// ── Delete ─────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $conn->query("DELETE FROM medications WHERE id = $id");
    header("Location: medications.php?msg=deleted");
    exit;
}

// ── Edit ───────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_med'])) {
    $id     = (int)$_POST['edit_id'];
    $name   = trim($_POST['name']                  ?? '');
    $formula= trim($_POST['formula']               ?? '');
    $type   = trim($_POST['med_type']              ?? 'Other');
    $dosage = trim($_POST['default_dosage']        ?? '');
    $freq   = trim($_POST['default_frequency']     ?? '');
    $dur    = trim($_POST['default_duration']      ?? '');
    $instr  = trim($_POST['default_instructions']  ?? '');
    $vendor = trim($_POST['vendor']                ?? '');
    $price  = !empty($_POST['price']) ? floatval($_POST['price']) : null;

    if (!empty($name)) {
        $stmt = $conn->prepare(
            "UPDATE medications SET name=?, formula=?, med_type=?, default_dosage=?,
             default_frequency=?, default_duration=?, default_instructions=?, vendor=?, price=?
             WHERE id=?"
        );
        if ($stmt) {
            $stmt->bind_param("ssssssssdi", $name, $formula, $type, $dosage, $freq, $dur, $instr, $vendor, $price, $id);
            $stmt->execute();
            $success = "Medication updated.";
        }
    }
}

// ── Add ────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_med'])) {
    $name   = trim($_POST['name']                  ?? '');
    $formula= trim($_POST['formula']               ?? '');
    $type   = trim($_POST['med_type']              ?? 'Other');
    $dosage = trim($_POST['default_dosage']        ?? '');
    $freq   = trim($_POST['default_frequency']     ?? '');
    $dur    = trim($_POST['default_duration']      ?? '');
    $instr  = trim($_POST['default_instructions']  ?? '');
    $vendor = trim($_POST['vendor']                ?? '');
    $price  = !empty($_POST['price']) ? floatval($_POST['price']) : null;

    if (empty($name)) {
        $error = "Brand name is required.";
    } else {
        // Auto-ensure new columns exist (safe guard for servers not yet migrated)
        $conn->query("ALTER TABLE medications ADD COLUMN IF NOT EXISTS formula VARCHAR(255) NOT NULL DEFAULT ''");
        $conn->query("ALTER TABLE medications ADD COLUMN IF NOT EXISTS default_dosage VARCHAR(100) NOT NULL DEFAULT ''");
        $conn->query("ALTER TABLE medications ADD COLUMN IF NOT EXISTS default_frequency VARCHAR(100) NOT NULL DEFAULT ''");
        $conn->query("ALTER TABLE medications ADD COLUMN IF NOT EXISTS default_duration VARCHAR(100) NOT NULL DEFAULT ''");
        $conn->query("ALTER TABLE medications ADD COLUMN IF NOT EXISTS default_instructions TEXT");

        $stmt = $conn->prepare(
            "INSERT INTO medications (name, formula, med_type, default_dosage, default_frequency,
             default_duration, default_instructions, vendor, price)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        if ($stmt) {
            $stmt->bind_param("ssssssssd", $name, $formula, $type, $dosage, $freq, $dur, $instr, $vendor, $price);
            if ($stmt->execute()) {
                $success = "Medication added to arsenal.";
            } else {
                $error = "Failed: " . $stmt->error;
            }
        }
    }
}

$msg = $_GET['msg'] ?? '';
if ($msg === 'deleted') $success = 'Medication deleted.';

// ── Fetch all ──────────────────────────────────────────────────────────────
$meds = [];
$res  = $conn->query("SELECT * FROM medications ORDER BY name ASC");
if ($res) while ($row = $res->fetch_assoc()) $meds[] = $row;

$freq_options = [
    ''       => '— Select —',
    '1-0-1'  => 'Twice daily (BDS / 1-0-1)',
    '1-1-1'  => 'Three times daily (TDS / 1-1-1)',
    '1-0-0'  => 'Morning only (OD)',
    '0-0-1'  => 'Night only (OD)',
    '0-1-0'  => 'Noon only',
    'SOS'    => 'As needed (SOS)',
    'Weekly' => 'Weekly',
];

include __DIR__ . '/includes/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- ── Add Form Sidebar ───────────────────────────────────────────────── -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-teal-900 text-white flex items-center gap-2">
                <i class="fa-solid fa-pills text-teal-300"></i>
                <h3 class="font-bold">Add to Arsenal</h3>
            </div>
            <div class="p-6">
                <?php if (!empty($error)): ?>
                    <div class="text-sm text-red-600 bg-red-50 p-3 rounded-lg mb-4 border border-red-100 flex gap-2">
                        <i class="fa-solid fa-circle-exclamation mt-0.5"></i> <?= esc($error) ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="text-sm text-emerald-700 bg-emerald-50 p-3 rounded-lg mb-4 border border-emerald-100 flex gap-2">
                        <i class="fa-solid fa-circle-check mt-0.5"></i> <?= esc($success) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Brand Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required placeholder="e.g. Clomid, Gonal-f"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Formula / Generic Name</label>
                        <input type="text" name="formula" placeholder="e.g. Clomiphene Citrate"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Type</label>
                        <select name="med_type" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white text-sm">
                            <option>Injection</option>
                            <option>Tablet</option>
                            <option>Capsule</option>
                            <option>Sachet</option>
                            <option>Syrup</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Default Dosage</label>
                            <input type="text" name="default_dosage" placeholder="e.g. 50mg"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Default Duration</label>
                            <input type="text" name="default_duration" placeholder="e.g. 5 days"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-500 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Default Frequency</label>
                        <select name="default_frequency" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white text-sm">
                            <?php foreach ($freq_options as $val => $label): ?>
                                <option value="<?= esc($val) ?>"><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Default Instructions</label>
                        <input type="text" name="default_instructions" placeholder="e.g. From Day 2 of cycle, after meals"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-500 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Vendor (optional)</label>
                            <input type="text" name="vendor" placeholder="GSK, Merck…"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Price (Rs)</label>
                            <input type="number" step="0.01" name="price"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-500 text-sm">
                        </div>
                    </div>
                    <button type="submit" name="add_med"
                            class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-2.5 px-4 rounded-lg transition-colors shadow-sm text-sm">
                        <i class="fa-solid fa-plus mr-1"></i> Add Medication
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ── Arsenal Table ──────────────────────────────────────────────────── -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-database text-teal-500 text-sm"></i> Arsenal Inventory
                </h3>
                <span class="text-xs text-gray-500 bg-white px-3 py-1 rounded-full border border-gray-200 font-medium"><?= count($meds) ?> total</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-gray-700 uppercase font-semibold text-xs tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3">Brand / Formula</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Dosage</th>
                            <th class="px-4 py-3">Freq</th>
                            <th class="px-4 py-3">Duration</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($meds)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-400 text-sm">
                                    Arsenal is empty. Add medications to build presets.
                                </td>
                            </tr>
                        <?php else: foreach ($meds as $m): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3">
                                    <div class="font-bold text-gray-800"><?= esc($m['name']) ?></div>
                                    <?php if (!empty($m['formula'])): ?>
                                        <div class="text-xs text-indigo-500 font-medium mt-0.5"><?= esc($m['formula']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs font-medium"><?= esc($m['med_type'] ?? '') ?></span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 font-mono"><?= esc($m['default_dosage'] ?? '') ?: '<span class="text-gray-300">—</span>' ?></td>
                                <td class="px-4 py-3 text-xs text-gray-600"><?= esc($m['default_frequency'] ?? '') ?: '<span class="text-gray-300">—</span>' ?></td>
                                <td class="px-4 py-3 text-xs text-gray-600"><?= esc($m['default_duration'] ?? '') ?: '<span class="text-gray-300">—</span>' ?></td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <button onclick='openEditModal(<?= json_encode($m) ?>)'
                                            class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md font-medium transition-colors inline-block mr-1 cursor-pointer text-xs">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button onclick="confirmDelete(<?= $m['id'] ?>, '<?= esc($m['name']) ?>')"
                                            class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md font-medium transition-colors inline-block cursor-pointer text-xs">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- ── Delete Modal ───────────────────────────────────────────────────────── -->
<div id="deleteModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.5);"
     onclick="if(event.target===this)closeDeleteModal()">
    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:16px;padding:32px;max-width:400px;width:90%;box-shadow:0 25px 50px rgba(0,0,0,0.15);">
        <div style="text-align:center;">
            <div style="width:56px;height:56px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;font-size:24px;"></i>
            </div>
            <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:8px;">Delete Medication?</h3>
            <p style="color:#64748b;font-size:14px;margin-bottom:24px;">Delete <strong id="deleteItemName"></strong>? This cannot be undone.</p>
            <form method="POST">
                <input type="hidden" name="delete_id" id="deleteId">
                <button type="button" onclick="closeDeleteModal()" style="padding:10px 24px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;color:#64748b;font-weight:600;font-size:14px;cursor:pointer;margin-right:8px;">Cancel</button>
                <button type="submit" style="padding:10px 24px;border-radius:8px;border:none;background:#ef4444;color:#fff;font-weight:600;font-size:14px;cursor:pointer;">Delete</button>
            </form>
        </div>
    </div>
</div>

<!-- ── Edit Modal ─────────────────────────────────────────────────────────── -->
<div id="editModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.5);overflow-y:auto;"
     onclick="if(event.target===this)closeEditModal()">
    <div style="position:relative;margin:40px auto;background:#fff;border-radius:16px;padding:32px;max-width:540px;width:90%;box-shadow:0 25px 50px rgba(0,0,0,0.15);">
        <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:20px;">
            <i class="fa-solid fa-pen-to-square" style="color:#3b82f6;"></i> Edit Medication
        </h3>
        <form method="POST">
            <input type="hidden" name="edit_med" value="1">
            <input type="hidden" name="edit_id" id="editId">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Brand Name *</label>
                    <input type="text" name="name" id="editName" required style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;box-sizing:border-box;">
                </div>
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Formula / Generic Name</label>
                    <input type="text" name="formula" id="editFormula" style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Type</label>
                    <select name="med_type" id="editType" style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;">
                        <option>Injection</option><option>Tablet</option><option>Capsule</option>
                        <option>Sachet</option><option>Syrup</option><option>Other</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Default Dosage</label>
                    <input type="text" name="default_dosage" id="editDosage" placeholder="e.g. 50mg" style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Default Frequency</label>
                    <select name="default_frequency" id="editFreq" style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;">
                        <?php foreach ($freq_options as $val => $label): ?>
                            <option value="<?= esc($val) ?>"><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Default Duration</label>
                    <input type="text" name="default_duration" id="editDuration" placeholder="e.g. 5 days" style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;box-sizing:border-box;">
                </div>
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Default Instructions</label>
                    <input type="text" name="default_instructions" id="editInstr" style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#94a3b8;margin-bottom:4px;">Vendor</label>
                    <input type="text" name="vendor" id="editVendor" style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#94a3b8;margin-bottom:4px;">Price (Rs)</label>
                    <input type="number" step="0.01" name="price" id="editPrice" style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;box-sizing:border-box;">
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:8px;">
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
    document.getElementById('editId').value       = m.id;
    document.getElementById('editName').value     = m.name || '';
    document.getElementById('editFormula').value  = m.formula || '';
    document.getElementById('editType').value     = m.med_type || 'Other';
    document.getElementById('editDosage').value   = m.default_dosage || '';
    document.getElementById('editFreq').value     = m.default_frequency || '';
    document.getElementById('editDuration').value = m.default_duration || '';
    document.getElementById('editInstr').value    = m.default_instructions || '';
    document.getElementById('editVendor').value   = m.vendor || '';
    document.getElementById('editPrice').value    = m.price || '';
    document.getElementById('editModal').style.display = 'block';
}
function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
