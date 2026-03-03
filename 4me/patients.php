<?php
$pageTitle = "Patient Registry";
require_once __DIR__ . '/includes/auth.php';

// ── Handle Delete ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    try {
        $conn->begin_transaction();
        $conn->query("DELETE FROM patient_history WHERE patient_id = $id");
        $conn->query("DELETE FROM advised_lab_tests WHERE prescription_id IN (SELECT id FROM prescriptions WHERE patient_id = $id)");
        $conn->query("DELETE FROM prescription_items WHERE prescription_id IN (SELECT id FROM prescriptions WHERE patient_id = $id)");
        $conn->query("DELETE FROM prescription_diagnoses WHERE prescription_id IN (SELECT id FROM prescriptions WHERE patient_id = $id)");
        $conn->query("DELETE FROM prescriptions WHERE patient_id = $id");
        $conn->query("DELETE FROM patient_ultrasounds WHERE patient_id = $id");
        $conn->query("DELETE FROM semen_analyses WHERE patient_id = $id");
        $conn->query("DELETE FROM patient_lab_results WHERE patient_id = $id");
        $conn->query("DELETE FROM receipts WHERE patient_id = $id");
        $conn->query("DELETE FROM advised_procedures WHERE patient_id = $id");
        $conn->query("DELETE FROM patients WHERE id = $id");
        $conn->commit();
        header("Location: patients.php?msg=deleted");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        die("Deletion Error: " . $e->getMessage());
    }
}

// ── Fetch ──────────────────────────────────────────────────────────────────────
$search   = trim($_GET['q']   ?? '');
$gender_f = trim($_GET['gender'] ?? '');
$msg      = $_GET['msg'] ?? '';
$patients = [];
$total    = 0;

try {
    $where  = [];
    $params = [];
    $types  = '';

    if (!empty($search)) {
        $like = "%$search%";
        $where[] = "(p.mr_number LIKE ? OR p.cnic LIKE ? OR p.phone LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR p.spouse_name LIKE ? OR p.spouse_phone LIKE ? OR p.spouse_cnic LIKE ?)";
        for ($i = 0; $i < 8; $i++) { $params[] = $like; $types .= 's'; }
    }
    if (!empty($gender_f)) {
        $where[] = "p.gender = ?";
        $params[] = $gender_f;
        $types .= 's';
    }

    $sql = "SELECT p.*, h.name AS hospital_name FROM patients p LEFT JOIN hospitals h ON p.referring_hospital_id = h.id";
    if ($where) $sql .= " WHERE " . implode(' AND ', $where);
    $sql .= " ORDER BY p.id DESC LIMIT 100";

    if ($types) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
    } else {
        $res = $conn->query($sql);
    }
    if ($res) while ($row = $res->fetch_assoc()) $patients[] = $row;

    // Total count
    $r = $conn->query("SELECT COUNT(*) AS c FROM patients");
    if ($r) $total = $r->fetch_assoc()['c'];

} catch (Exception $e) {}

include __DIR__ . '/includes/header.php';
?>

<!-- Flash Messages -->
<?php if ($msg === 'deleted'): ?>
<div class="flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 px-5 py-3 rounded-xl text-sm font-bold mb-6">
    <i class="fa-solid fa-circle-check text-rose-500"></i> Patient record deleted permanently.
</div>
<?php endif; ?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-gray-800 tracking-tight">Patient Registry</h1>
        <p class="text-sm text-gray-400 font-bold mt-0.5"><?php echo number_format($total); ?> patients · showing up to 100 results</p>
    </div>
    <a href="patients_add.php"
       class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white font-black py-3 px-5 rounded-xl transition-all shadow-lg shadow-teal-100 active:scale-95 text-sm">
        <i class="fa-solid fa-user-plus"></i> Register Patient
    </a>
</div>

<!-- Search & Filters -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" name="q" value="<?php echo esc($search); ?>"
                   placeholder="Search by name, MR number, CNIC, phone, spouse..."
                   autofocus
                   class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all text-sm font-medium">
        </div>
        <select name="gender" class="px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-teal-500 text-sm font-bold text-gray-600 transition-all">
            <option value="">All Genders</option>
            <option value="Male"   <?php echo $gender_f === 'Male'   ? 'selected' : ''; ?>>Male</option>
            <option value="Female" <?php echo $gender_f === 'Female' ? 'selected' : ''; ?>>Female</option>
        </select>
        <button type="submit"
                class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-black text-sm transition-all">
            <i class="fa-solid fa-search mr-1.5"></i> Search
        </button>
        <?php if ($search || $gender_f): ?>
        <a href="patients.php" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-black text-sm transition-all flex items-center gap-1.5">
            <i class="fa-solid fa-times"></i> Clear
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Patient Table -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <?php if (empty($patients)): ?>
    <div class="p-16 text-center">
        <i class="fa-solid fa-users-slash text-5xl text-gray-100 mb-4 block"></i>
        <h3 class="text-lg font-bold text-gray-400 mb-2">No patients found</h3>
        <?php if ($search || $gender_f): ?>
        <p class="text-sm text-gray-400">Try adjusting your search filters.</p>
        <a href="patients.php" class="mt-4 inline-block text-teal-600 font-black text-sm hover:text-teal-700">Clear filters →</a>
        <?php else: ?>
        <p class="text-sm text-gray-400">Register your first patient to get started.</p>
        <a href="patients_add.php" class="mt-4 inline-block bg-teal-600 text-white px-6 py-2.5 rounded-xl font-black text-sm hover:bg-teal-700 transition-all">Register First Patient</a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50/80 text-[9px] font-black uppercase tracking-widest text-gray-400 border-b border-gray-100">
                    <th class="px-6 py-4">Patient</th>
                    <th class="px-6 py-4">Contact</th>
                    <th class="px-6 py-4">Spouse</th>
                    <th class="px-6 py-4">Gender / Age</th>
                    <th class="px-6 py-4">Referred By</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($patients as $p): ?>
                <tr class="hover:bg-teal-50/10 transition-colors group">
                    <!-- Patient Identity -->
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-sm shrink-0
                                <?php echo $p['gender'] === 'Female' ? 'bg-pink-100 text-pink-700' : 'bg-indigo-100 text-indigo-700'; ?>">
                                <?php echo strtoupper(substr($p['first_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="font-black text-gray-800 text-sm leading-tight"><?php echo esc($p['first_name'] . ' ' . $p['last_name']); ?></div>
                                <div class="font-mono text-teal-600 text-[10px] font-bold"><?php echo esc($p['mr_number']); ?></div>
                            </div>
                        </div>
                    </td>

                    <!-- Contact -->
                    <td class="px-6 py-4">
                        <?php if ($p['phone']): ?>
                        <div class="text-sm text-gray-700 font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-phone text-gray-300 text-xs"></i> <?php echo esc($p['phone']); ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($p['cnic']): ?>
                        <div class="text-xs text-gray-400 font-mono mt-0.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-id-card text-gray-200 text-[10px]"></i> <?php echo esc($p['cnic']); ?>
                        </div>
                        <?php endif; ?>
                    </td>

                    <!-- Spouse -->
                    <td class="px-6 py-4">
                        <?php if (!empty($p['spouse_name'])): ?>
                        <div class="flex items-center gap-1.5">
                            <i class="fa-solid fa-heart text-pink-300 text-xs"></i>
                            <span class="text-sm text-gray-600 font-bold"><?php echo esc($p['spouse_name']); ?></span>
                        </div>
                        <?php else: ?>
                        <span class="text-gray-200 text-xs">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Gender/Age -->
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[9px] font-black uppercase
                            <?php echo $p['gender'] === 'Female' ? 'bg-pink-50 text-pink-700' : 'bg-indigo-50 text-indigo-700'; ?>">
                            <?php echo esc($p['gender'] ?? '—'); ?>
                            <?php if ($p['patient_age']): ?> · <?php echo $p['patient_age']; ?>y<?php endif; ?>
                        </span>
                    </td>

                    <!-- Referred By -->
                    <td class="px-6 py-4">
                        <span class="text-xs text-gray-500 font-bold"><?php echo esc($p['hospital_name'] ?: 'Direct'); ?></span>
                    </td>

                    <!-- Actions -->
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="patients_view.php?id=<?php echo $p['id']; ?>"
                               class="w-9 h-9 bg-teal-50 hover:bg-teal-600 text-teal-600 hover:text-white rounded-xl flex items-center justify-center transition-all text-sm"
                               title="Open 360 Profile">
                                <i class="fa-solid fa-folder-open"></i>
                            </a>
                            <a href="patients_edit.php?id=<?php echo $p['id']; ?>"
                               class="w-9 h-9 bg-gray-100 hover:bg-indigo-600 text-gray-500 hover:text-white rounded-xl flex items-center justify-center transition-all text-sm"
                               title="Edit Patient">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <button onclick="confirmDelete(<?php echo $p['id']; ?>, '<?php echo esc(addslashes($p['first_name'] . ' ' . $p['last_name'])); ?>')"
                                    class="w-9 h-9 bg-gray-100 hover:bg-rose-500 text-gray-400 hover:text-white rounded-xl flex items-center justify-center transition-all text-sm"
                                    title="Delete Patient">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="px-6 py-3 border-t border-gray-50 bg-gray-50/30 flex items-center justify-between">
        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest"><?php echo count($patients); ?> results displayed</span>
        <?php if (!empty($search) || !empty($gender_f)): ?>
        <a href="patients.php" class="text-[10px] font-black text-teal-600 hover:text-teal-800 uppercase tracking-widest">Clear filters →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal (Alpine.js) -->
<div id="deleteModal" x-data="{ show: false, name: '', pid: 0 }"
     x-show="show"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     @keydown.escape.window="show = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none;">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="show = false"></div>
    <div class="relative bg-white rounded-3xl shadow-2xl p-8 max-w-md w-full z-10 text-center"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="scale-95 opacity-0"
         x-transition:enter-end="scale-100 opacity-100">
        <div class="w-16 h-16 bg-rose-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-2xl"></i>
        </div>
        <h3 class="text-xl font-black text-gray-800 mb-2">Delete Patient?</h3>
        <p class="text-gray-500 text-sm mb-1">You are about to permanently delete</p>
        <p class="font-black text-gray-800 text-lg mb-4" x-text="name"></p>
        <p class="text-xs text-rose-600 bg-rose-50 rounded-xl p-3 mb-6 font-bold">
            <i class="fa-solid fa-exclamation mr-1"></i>
            This will delete ALL associated records — prescriptions, lab results, history, ultrasounds. This cannot be undone.
        </p>
        <form method="POST" class="flex gap-3">
            <input type="hidden" name="delete_id" :value="pid">
            <button type="button" @click="show = false"
                    class="flex-1 py-3 rounded-2xl font-black text-gray-600 bg-gray-100 hover:bg-gray-200 transition-all text-sm">
                Cancel
            </button>
            <button type="submit"
                    class="flex-1 py-3 rounded-2xl font-black text-white bg-rose-500 hover:bg-rose-600 transition-all shadow-lg shadow-rose-100 text-sm">
                <i class="fa-solid fa-trash mr-1"></i> Delete
            </button>
        </form>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    const modal = document.getElementById('deleteModal');
    if (modal && modal._x_dataStack) {
        // Alpine.js approach
        modal._x_dataStack[0].pid  = id;
        modal._x_dataStack[0].name = name;
        modal._x_dataStack[0].show = true;
    } else {
        // Fallback plain JS
        if (confirm('Delete ' + name + '? This cannot be undone.')) {
            const f = document.createElement('form');
            f.method = 'POST';
            const i = document.createElement('input');
            i.type = 'hidden'; i.name = 'delete_id'; i.value = id;
            f.appendChild(i); document.body.appendChild(f); f.submit();
        }
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
