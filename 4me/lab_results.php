<?php
require_once __DIR__ . '/includes/auth.php';

$success = '';
$error = '';

// Handle Delete Result (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);

    // Get file path to delete if exists
    $stmt = $conn->prepare("SELECT scanned_report_path FROM patient_lab_results WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if (!empty($row['scanned_report_path']) && file_exists('../' . $row['scanned_report_path'])) {
            unlink('../' . $row['scanned_report_path']);
        }
    }

    $stmt = $conn->prepare("DELETE FROM patient_lab_results WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = "Lab result deleted successfully.";
    }
    else {
        $error = "Error deleting lab result.";
    }
}

// Fetch total records for pagination
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$searchQuery = "";
$queryParams = [];
$types = "";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = "%" . trim($_GET['search']) . "%";
    $searchQuery = "WHERE pt.first_name LIKE ? OR pt.last_name LIKE ? OR pt.mr_number LIKE ? OR plt.lab_name LIKE ? OR plt.lab_city LIKE ?";
    $queryParams = [$search, $search, $search, $search, $search];
    $types = "sssss";
}

$countQuery = "SELECT COUNT(*) as total FROM patient_lab_results plt JOIN patients pt ON plt.patient_id = pt.id " . $searchQuery;
$stmtCount = $conn->prepare($countQuery);
if (!empty($queryParams)) {
    $stmtCount->bind_param($types, ...$queryParams);
}
$stmtCount->execute();
$totalRecords = $stmtCount->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);

// Fetch results
$sql = "SELECT plt.*, pt.first_name, pt.last_name, pt.mr_number as pt_mr, pt.gender as pt_gender, ltd.test_name, ltd.unit 
        FROM patient_lab_results plt 
        JOIN patients pt ON plt.patient_id = pt.id 
        JOIN lab_tests_directory ltd ON plt.test_id = ltd.id 
        $searchQuery 
        ORDER BY plt.status ASC, plt.test_date DESC, plt.id DESC 
        LIMIT ? OFFSET ?";

$queryParams[] = $limit;
$queryParams[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$queryParams);
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Laboratory Results';
include __DIR__ . '/includes/header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 border-l-4 border-indigo-600 pl-3">Laboratory Results</h1>
        <p class="text-gray-500 text-sm mt-1">Track patient lab test variables and upload scanned reports.</p>
    </div>
    <div class="flex gap-2 w-full sm:w-auto">
        <a href="lab_tests.php" class="bg-white border text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm text-sm font-medium w-full sm:w-auto text-center">
            <i class="fa-solid fa-book-medical mr-1"></i> Tests Directory
        </a>
        <a href="lab_results_add.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow-sm text-sm font-medium transition-colors w-full sm:w-auto text-center shrink-0">
            <i class="fa-solid fa-plus mr-1"></i> Record Result
        </a>
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

<!-- Search Bar -->
<div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-6">
    <form method="GET" class="flex gap-2">
        <div class="relative flex-1">
            <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
            <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Search by patient name, MR number, or lab name..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>
        <button type="submit" class="bg-indigo-50 text-indigo-700 px-6 py-2 rounded-lg hover:bg-indigo-100 font-medium transition-colors border border-indigo-100 shrink-0">
            Search
        </button>
        <?php if (isset($_GET['search'])): ?>
            <a href="lab_results.php" class="bg-gray-50 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-100 font-medium transition-colors border border-gray-200 shrink-0">
                Clear
            </a>
        <?php
endif; ?>
    </form>
</div>

<!-- Results Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-visible">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="p-4 font-medium border-b border-gray-100 w-16">ID</th>
                    <th class="p-4 font-medium border-b border-gray-100">Patient / Target</th>
                    <th class="p-4 font-medium border-b border-gray-100">Test</th>
                    <th class="p-4 font-medium border-b border-gray-100">Result</th>
                    <th class="p-4 font-medium border-b border-gray-100">Status</th>
                    <th class="p-4 font-medium border-b border-gray-100">Lab Details</th>
                    <th class="p-4 font-medium border-b border-gray-100">Date</th>
                    <th class="p-4 font-medium border-b border-gray-100 w-16 text-center"><i class="fa-solid fa-paperclip"></i></th>
                    <th class="p-4 font-medium border-b border-gray-100 w-16 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-sm">
                <?php if (empty($results)): ?>
                <tr>
                    <td colspan="9" class="p-8 text-center text-gray-400 font-medium">
                        <i class="fa-solid fa-vials text-3xl mb-3 block text-gray-300"></i>
                        No lab results found.
                    </td>
                </tr>
                <?php
else:
    foreach ($results as $r):
        $is_pending = ($r['status'] === 'Pending');
?>
                <tr class="hover:bg-gray-50/50 transition-colors group <?php echo $is_pending ? 'bg-orange-50/30' : ''; ?>">
                    <td class="p-4 text-gray-500">#<?php echo $r['id']; ?></td>
                    <td class="p-4">
                        <div class="font-bold text-gray-900"><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?></div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs text-indigo-600 font-mono"><?php echo htmlspecialchars($r['pt_mr']); ?></span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded font-bold uppercase <?php echo $r['test_for'] === 'Spouse' ? 'bg-pink-100 text-pink-700' : 'bg-indigo-100 text-indigo-700'; ?>">
                                <?php echo $r['test_for']; ?>
                            </span>
                        </div>
                    </td>
                    <td class="p-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-medium bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs">
                            <?php echo htmlspecialchars($r['test_name']); ?>
                        </span>
                    </td>
                    <td class="p-4">
                        <?php if ($is_pending): ?>
                            <span class="text-orange-500 font-bold italic">Awaiting...</span>
                        <?php
        else: ?>
                            <span class="font-bold text-gray-900 text-base"><?php echo htmlspecialchars($r['result_value']); ?></span>
                            <span class="text-xs text-gray-500 font-mono ml-1"><?php echo htmlspecialchars($r['unit']); ?></span>
                        <?php
        endif; ?>
                    </td>
                    <td class="p-4">
                        <?php if ($is_pending): ?>
                            <span class="flex items-center gap-1 text-orange-600 font-bold text-xs uppercase tracking-tight">
                                <i class="fa-solid fa-clock-rotate-left animate-pulse"></i> Pending
                            </span>
                        <?php
        else: ?>
                            <span class="flex items-center gap-1 text-emerald-600 font-bold text-xs uppercase tracking-tight">
                                <i class="fa-solid fa-check-double"></i> Final
                            </span>
                        <?php
        endif; ?>
                    </td>
                    <td class="p-4">
                        <div class="text-gray-900 font-medium text-xs"><?php echo htmlspecialchars($r['lab_name'] ?: 'In-House'); ?></div>
                        <div class="text-gray-500 text-[10px] uppercase tracking-wider"><?php echo htmlspecialchars($r['lab_city'] ?: '-'); ?></div>
                    </td>
                    <td class="p-4 text-gray-500">
                        <?php echo date('d M Y', strtotime($r['test_date'])); ?>
                    </td>
                    <td class="p-4 text-center">
                        <?php if (!empty($r['scanned_report_path'])): ?>
                            <a href="../<?php echo htmlspecialchars($r['scanned_report_path']); ?>" target="_blank" class="text-sky-500 hover:text-sky-700 bg-sky-50 p-2 rounded inline-block" title="View Scanned Report">
                                <i class="fa-solid fa-file-pdf"></i>
                            </a>
                        <?php
        else: ?>
                            <span class="text-gray-300"><i class="fa-solid fa-minus"></i></span>
                        <?php
        endif; ?>
                    </td>
                    <td class="p-4 text-right">
                        <!-- Dropdown Menu -->
                        <div class="relative inline-block text-left" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false" class="text-gray-400 hover:text-gray-600 p-2 rounded hover:bg-gray-100 transition-colors">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <div x-show="open" x-cloak 
                                 x-transition:enter="transition ease-out duration-100" 
                                 x-transition:enter-start="transform opacity-0 scale-95" 
                                 x-transition:enter-end="transform opacity-100 scale-100" 
                                 x-transition:leave="transition ease-in duration-75" 
                                 x-transition:leave-start="transform opacity-100 scale-100" 
                                 x-transition:leave-end="transform opacity-0 scale-95" 
                                 class="absolute right-0 z-50 mt-2 w-48 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none">
                                <div class="py-1">
                                    <a href="lab_results_add.php?edit=<?php echo $r['id']; ?>" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <i class="fa-solid fa-edit mr-3 text-gray-400"></i> Edit Result
                                    </a>
                                </div>
                                <div class="py-1">
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this lab result? This action cannot be undone.');">
                                        <input type="hidden" name="delete_id" value="<?php echo $r['id']; ?>">
                                        <button type="submit" class="w-full text-left group flex items-center px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                            <i class="fa-solid fa-trash mr-3 text-red-400 group-hover:text-red-500"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php
    endforeach;
endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="px-6 py-4 flex items-center justify-between border-t border-gray-100 bg-gray-50/50">
        <div class="text-sm text-gray-500">
            Showing Page <?php echo $page; ?> of <?php echo $totalPages; ?>
        </div>
        <div class="flex gap-2">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($_GET['search'] ?? ''); ?>" class="bg-white border text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors">Previous</a>
            <?php
    endif; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($_GET['search'] ?? ''); ?>" class="bg-white border text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors">Next</a>
            <?php
    endif; ?>
        </div>
    </div>
    <?php
endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
