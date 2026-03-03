<?php
require_once __DIR__ . '/includes/auth.php';

$success = '';
$error = '';

$categories = [
    'Complete Blood Count (CBC)',
    'Liver Function Tests (LFT)',
    'Kidney Function Tests (KFT)',
    'Thyroid Function Tests',
    'Reproductive Hormones',
    'IVF & Fertility Specific',
    'Lipid Profile',
    'Diabetes & Glucose',
    'Coagulation Profile',
    'Iron Studies',
    'Vitamins & Minerals',
    'Inflammation Markers',
    'Autoimmune & Antiphospholipid',
    'Infectious Serology (TORCH)',
    'Tumor Markers',
    'Urine Analysis',
    'Other',
];

// Handle Test Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    try {
        $stmt = $conn->prepare("DELETE FROM lab_tests_directory WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = "Lab test removed from directory.";
        } else {
            $error = "Cannot delete this test because it is linked to patient lab results.";
        }
    } catch (Exception $e) {
        $error = "Cannot delete this test because it is linked to patient lab results.";
    }
}

// Handle Form Submission (Add / Edit)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_test'])) {
    $id       = intval($_POST['id'] ?? 0);
    $name     = trim($_POST['test_name']);
    $unit     = trim($_POST['unit']);
    $ref_m    = trim($_POST['reference_range_male'] ?? '');
    $ref_f    = trim($_POST['reference_range_female'] ?? '');
    $category = trim($_POST['category'] ?? 'Other');

    if (empty($name)) {
        $error = "Test Name is required.";
    } else {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE lab_tests_directory SET test_name=?, reference_range_male=?, reference_range_female=?, unit=?, category=? WHERE id=?");
            $stmt->bind_param("sssssi", $name, $ref_m, $ref_f, $unit, $category, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO lab_tests_directory (test_name, reference_range_male, reference_range_female, unit, category) VALUES (?,?,?,?,?)");
            $stmt->bind_param("sssss", $name, $ref_m, $ref_f, $unit, $category);
        }
        if ($stmt->execute()) {
            $success = $id > 0 ? "Test updated successfully." : "New test added to directory.";
        } else {
            $error = "Save failed: " . $stmt->error;
        }
    }
}

// Filter
$filterCat = trim($_GET['cat'] ?? '');

// Fetch tests — grouped by category
$tests = [];
if ($filterCat !== '') {
    $st = $conn->prepare("SELECT * FROM lab_tests_directory WHERE category=? ORDER BY test_name ASC");
    $st->bind_param("s", $filterCat);
    $st->execute();
    $res = $st->get_result();
} else {
    $res = $conn->query("SELECT * FROM lab_tests_directory ORDER BY category ASC, test_name ASC");
}
if ($res) {
    while ($row = $res->fetch_assoc())
        $tests[] = $row;
}

$pageTitle = 'Lab Tests Directory';
include __DIR__ . '/includes/header.php';
?>

<div class="mb-6 flex flex-wrap gap-3 justify-between items-end">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 border-l-4 border-indigo-600 pl-3">Lab Tests Directory</h1>
        <p class="text-gray-500 text-sm mt-1"><?php echo count($tests); ?> test<?php echo count($tests) !== 1 ? 's' : ''; ?> in directory
            <?php if ($filterCat): ?>— filtered by <span class="font-bold text-indigo-600"><?php echo htmlspecialchars($filterCat); ?></span>
            <a href="lab_tests.php" class="ml-2 text-xs text-gray-400 hover:text-red-500">✕ Clear filter</a>
            <?php endif; ?>
        </p>
    </div>
    <div class="flex gap-2">
        <a href="lab_results.php" class="bg-white border text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm text-sm font-medium">
            <i class="fa-solid fa-vials mr-1"></i> Patient Results
        </a>
        <a href="migrate_lab_tests_library.php" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg shadow-sm text-sm font-medium transition-colors" onclick="return confirm('Run the full NABL library seed? Safe to re-run.')">
            <i class="fa-solid fa-database mr-1"></i> Load NABL Library
        </a>
        <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow-sm text-sm font-medium transition-colors">
            <i class="fa-solid fa-plus mr-1"></i> Add Custom Test
        </button>
    </div>
</div>

<?php if ($success): ?>
    <div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl mb-5 border border-emerald-100 flex items-center gap-2 font-medium">
        <i class="fa-solid fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-5 border border-red-100 flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<!-- Category Filter Pills -->
<div class="mb-5 flex flex-wrap gap-2">
    <a href="lab_tests.php" class="px-3 py-1.5 rounded-full text-xs font-bold border transition-all <?php echo $filterCat === '' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-300'; ?>">
        All
    </a>
    <?php foreach ($categories as $cat): ?>
    <a href="lab_tests.php?cat=<?php echo urlencode($cat); ?>"
       class="px-3 py-1.5 rounded-full text-xs font-bold border transition-all <?php echo $filterCat === $cat ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-300'; ?>">
        <?php echo htmlspecialchars($cat); ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- Tests Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                <th class="p-4 font-medium border-b border-gray-100">Test Name</th>
                <th class="p-4 font-medium border-b border-gray-100">Category</th>
                <th class="p-4 font-medium border-b border-gray-100">Reference (Male)</th>
                <th class="p-4 font-medium border-b border-gray-100">Reference (Female)</th>
                <th class="p-4 font-medium border-b border-gray-100 w-20">Unit</th>
                <th class="p-4 font-medium border-b border-gray-100 w-24 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 text-sm">
        <?php if (empty($tests)): ?>
            <tr>
                <td colspan="6" class="p-10 text-center text-gray-400">
                    <i class="fa-solid fa-flask mb-3 text-4xl block text-gray-200"></i>
                    <div class="font-bold text-gray-500 mb-1">No tests in directory yet</div>
                    <div class="text-xs">Click <strong>Load NABL Library</strong> to instantly seed 150+ standard tests.</div>
                </td>
            </tr>
        <?php else:
            $prevCat = null;
            foreach ($tests as $t):
                $cat = $t['category'] ?: 'Other';
                if ($cat !== $prevCat): ?>
            <tr>
                <td colspan="6" class="px-4 py-2 bg-slate-50 border-t border-b border-slate-100">
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest"><?php echo htmlspecialchars($cat); ?></span>
                </td>
            </tr>
            <?php $prevCat = $cat; endif; ?>
            <tr class="hover:bg-gray-50/50 transition-colors">
                <td class="p-4 font-medium text-gray-900"><?php echo htmlspecialchars($t['test_name']); ?></td>
                <td class="p-4">
                    <span class="text-[10px] font-bold px-2 py-1 bg-indigo-50 text-indigo-600 rounded-full"><?php echo htmlspecialchars($cat); ?></span>
                </td>
                <td class="p-4 text-gray-600 text-xs leading-relaxed max-w-[180px]">
                    <?php echo $t['reference_range_male'] ? nl2br(htmlspecialchars($t['reference_range_male'])) : '<span class="text-gray-300">—</span>'; ?>
                </td>
                <td class="p-4 text-gray-600 text-xs leading-relaxed max-w-[180px]">
                    <?php echo $t['reference_range_female'] ? nl2br(htmlspecialchars($t['reference_range_female'])) : '<span class="text-gray-300">—</span>'; ?>
                </td>
                <td class="p-4 text-gray-500 font-mono text-xs text-center">
                    <?php echo htmlspecialchars($t['unit'] ?: '—'); ?>
                </td>
                <td class="p-4 text-right">
                    <div class="flex justify-end gap-2">
                        <button onclick="editModal(<?php echo htmlspecialchars(json_encode($t)); ?>)"
                                class="text-sky-500 hover:text-sky-700 bg-sky-50 hover:bg-sky-100 p-2 rounded-lg transition-colors" title="Edit">
                            <i class="fa-solid fa-edit"></i>
                        </button>
                        <form method="POST" onsubmit="return confirm('Delete this test? Cannot undo if linked to patient results.');" class="inline">
                            <input type="hidden" name="delete_id" value="<?php echo $t['id']; ?>">
                            <button type="submit" class="text-rose-500 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 p-2 rounded-lg transition-colors" title="Delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Add/Edit Modal -->
<div id="testModal" class="fixed inset-0 bg-gray-900/60 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-900" id="modalTitle">Add Custom Lab Test</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <form method="POST" class="p-6">
            <input type="hidden" name="id" id="test_id" value="0">

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Test Name *</label>
                    <input type="text" name="test_name" id="test_name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="e.g. Anti-Mullerian Hormone (AMH)">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Category</label>
                    <select name="category" id="test_category"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Unit</label>
                    <input type="text" name="unit" id="unit"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"
                           placeholder="e.g. ng/mL">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Reference Range (Male)</label>
                    <textarea name="reference_range_male" id="reference_range_male" rows="5"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                              placeholder="e.g. 13.0 – 17.0&#10;or multi-line for sub-ranges"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Reference Range (Female)</label>
                    <textarea name="reference_range_female" id="reference_range_female" rows="5"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                              placeholder="e.g. 12.0 – 15.5&#10;Follicular: 3.5 – 12.5&#10;Luteal: 1.7 – 7.7"></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" name="save_test"
                        class="px-6 py-2 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm">
                    Save Test
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const modal = document.getElementById('testModal');
function openModal() {
    document.getElementById('modalTitle').innerText = 'Add Custom Lab Test';
    document.getElementById('test_id').value = '0';
    document.getElementById('test_name').value = '';
    document.getElementById('test_category').value = 'Other';
    document.getElementById('unit').value = '';
    document.getElementById('reference_range_male').value = '';
    document.getElementById('reference_range_female').value = '';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function editModal(t) {
    document.getElementById('modalTitle').innerText = 'Edit Lab Test';
    document.getElementById('test_id').value = t.id;
    document.getElementById('test_name').value = t.test_name;
    document.getElementById('test_category').value = t.category || 'Other';
    document.getElementById('unit').value = t.unit || '';
    document.getElementById('reference_range_male').value = t.reference_range_male || '';
    document.getElementById('reference_range_female').value = t.reference_range_female || '';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeModal() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
