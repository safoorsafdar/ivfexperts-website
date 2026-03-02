<?php
require_once __DIR__ . '/includes/auth.php';
require_once dirname(__DIR__) . '/config/db.php';

$success = '';
$error = '';

// Handle Test Deletion (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    try {
        $stmt = $conn->prepare("DELETE FROM lab_tests_directory WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = "Lab test removed from directory.";
        }
        else {
            $error = "Cannot delete this test because it is linked to patient lab results.";
        }
    }
    catch (Exception $e) {
        $error = "Cannot delete this test because it is linked to patient lab results.";
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_test'])) {
    $id = intval($_POST['id'] ?? 0);
    $test_name = trim($_POST['test_name']);
    $unit = trim($_POST['unit']);

    if (empty($test_name)) {
        $error = "Test Name is required.";
    }
    else {
        $ref_male = trim($_POST['reference_range_male'] ?? '');
        $ref_female = trim($_POST['reference_range_female'] ?? '');

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE lab_tests_directory SET test_name = ?, reference_range_male = ?, reference_range_female = ?, unit = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $test_name, $ref_male, $ref_female, $unit, $id);
            if ($stmt->execute())
                $success = "Test updated successfully.";
            else
                $error = "Update failed.";
        }
        else {
            $stmt = $conn->prepare("INSERT INTO lab_tests_directory (test_name, reference_range_male, reference_range_female, unit) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $test_name, $ref_male, $ref_female, $unit);
            if ($stmt->execute())
                $success = "New test added to directory.";
            else
                $error = "Insertion failed.";
        }
    }
}

// Fetch all tests
$tests = [];
$res = $conn->query("SELECT * FROM lab_tests_directory ORDER BY test_name ASC");
if ($res) {
    while ($row = $res->fetch_assoc())
        $tests[] = $row;
}

include __DIR__ . '/includes/header.php';
?>

<div class="mb-6 flex justify-between items-end">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 border-l-4 border-indigo-600 pl-3">Lab Tests Directory</h1>
        <p class="text-gray-500 text-sm mt-1">Manage standard biological reference ranges for the EMR.</p>
    </div>
    <div class="flex gap-2">
        <a href="lab_results.php" class="bg-white border text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm text-sm font-medium">
            <i class="fa-solid fa-vials mr-1"></i> Patient Results
        </a>
        <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow-sm text-sm font-medium transition-colors">
            <i class="fa-solid fa-plus mr-1"></i> Add New Test
        </button>
    </div>
</div>

<?php if ($success): ?>
    <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 border border-emerald-100 flex items-center gap-2">
        <i class="fa-solid fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
    </div>
<?php
endif; ?>
<?php if ($error): ?>
    <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-100 flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php
endif; ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                <th class="p-4 font-medium border-b border-gray-100">Test Name</th>
                <th class="p-4 font-medium border-b border-gray-100">Reference (Male)</th>
                <th class="p-4 font-medium border-b border-gray-100">Reference (Female)</th>
                <th class="p-4 font-medium border-b border-gray-100 w-24">Unit</th>
                <th class="p-4 font-medium border-b border-gray-100 w-24 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 text-sm">
            <?php if (empty($tests)): ?>
            <tr>
                <td colspan="5" class="p-8 text-center text-gray-400">
                    <i class="fa-solid fa-flask mb-2 text-3xl block"></i>
                    No tests in the directory. Click "Add New Test" to build your database.
                </td>
            </tr>
            <?php
else:
    foreach ($tests as $t): ?>
            <tr class="hover:bg-gray-50/50 transition-colors group">
                <td class="p-4 font-medium text-gray-900"><?php echo htmlspecialchars($t['test_name']); ?></td>
                <td class="p-4 text-gray-600 text-xs leading-relaxed">
                    <?php echo $t['reference_range_male'] ? nl2br(htmlspecialchars($t['reference_range_male'])) : '-'; ?>
                </td>
                <td class="p-4 text-gray-600 text-xs leading-relaxed">
                    <?php echo $t['reference_range_female'] ? nl2br(htmlspecialchars($t['reference_range_female'])) : '-'; ?>
                </td>
                <td class="p-4 text-gray-500 font-mono text-xs bg-gray-50 rounded text-center m-3 box-border w-10">
                    <?php echo htmlspecialchars($t['unit'] ?: '-'); ?>
                </td>
                <td class="p-4 text-right">
                    <div class="flex justify-end gap-2 transition-opacity">
                        <button onclick="editModal(<?php echo htmlspecialchars(json_encode($t)); ?>)" class="text-sky-500 hover:text-sky-700 bg-sky-50 p-2 rounded">
                            <i class="fa-solid fa-edit"></i>
                        </button>
                        <form method="POST" onsubmit="return confirm('Delete this test definition? This will fail if results are linked to it.');" class="inline">
                            <input type="hidden" name="delete_id" value="<?php echo $t['id']; ?>">
                            <button type="submit" class="text-rose-500 hover:text-rose-700 bg-rose-50 p-2 rounded cursor-pointer">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php
    endforeach;
endif; ?>
        </tbody>
    </table>
</div>

<!-- Add/Edit Modal -->
<div id="testModal" class="fixed inset-0 bg-gray-900/50 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl max-w-xl w-full mx-4 overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-900" id="modalTitle">Add New Lab Test</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <form method="POST" class="p-6">
            <input type="hidden" name="id" id="test_id" value="0">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Test Name *</label>
                <input type="text" name="test_name" id="test_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Anti-Mullerian Hormone (AMH)">
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference Range (Male)</label>
                    <textarea name="reference_range_male" id="reference_range_male" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="e.g. 1.0 - 5.0"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference Range (Female)</label>
                    <textarea name="reference_range_female" id="reference_range_female" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="e.g. 0.5 - 3.0"></textarea>
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                <input type="text" name="unit" id="unit" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono" placeholder="e.g. ng/mL or IU/L">
            </div>
            
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" name="save_test" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700">Save Test</button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('testModal');
    function openModal() {
        document.getElementById('modalTitle').innerText = 'Add New Lab Test';
        document.getElementById('test_id').value = '0';
        document.getElementById('test_name').value = '';
        document.getElementById('reference_range_male').value = '';
        document.getElementById('reference_range_female').value = '';
        document.getElementById('unit').value = '';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function editModal(test) {
        document.getElementById('modalTitle').innerText = 'Edit Lab Test';
        document.getElementById('test_id').value = test.id;
        document.getElementById('test_name').value = test.test_name;
        document.getElementById('reference_range_male').value = test.reference_range_male;
        document.getElementById('reference_range_female').value = test.reference_range_female;
        document.getElementById('unit').value = test.unit;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
