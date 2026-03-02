<?php
$pageTitle = "Treatment Procedures Tracker";
require_once __DIR__ . '/includes/auth.php';

$success = '';
$error = '';

if (isset($_GET['success'])) {
    $success = "Procedure advised successfully.";
}

// Handle Status Update
if (isset($_POST['update_status'])) {
    $proc_id = intval($_POST['proc_id']);
    $new_status = $_POST['new_status'];
    $allowed = ['Advised', 'In Progress', 'Completed', 'Cancelled'];
    if (in_array($new_status, $allowed)) {
        $stmt = $conn->prepare("UPDATE advised_procedures SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $proc_id);
        if ($stmt->execute()) {
            $success = "Procedure status updated to '{$new_status}'.";
        }
        else {
            $error = "Failed to update status.";
        }
    }
}

// Handle Delete (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM advised_procedures WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = "Procedure record deleted.";
    }
    else {
        $error = "Error deleting procedure.";
    }
}

// Fetch Procedures with Patient Info and linked Receipt totals
$filter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT ap.*, p.first_name, p.last_name, p.mr_number,
        (SELECT COALESCE(SUM(r.amount), 0) FROM receipts r WHERE r.advised_procedure_id = ap.id) as total_billed,
        (SELECT COALESCE(SUM(r.amount), 0) FROM receipts r WHERE r.advised_procedure_id = ap.id AND r.status = 'Paid') as total_paid
        FROM advised_procedures ap
        JOIN patients p ON ap.patient_id = p.id
        WHERE 1=1";

$params = [];
$types = "";

$types = "";
$procedures = [];

try {
    if (!empty($filter) && in_array($filter, ['Advised', 'In Progress', 'Completed', 'Cancelled'])) {
        $sql .= " AND ap.status = ?";
        $params[] = $filter;
        $types .= "s";
    }

    if (!empty($search)) {
        $sql .= " AND (p.first_name LIKE ? OR p.last_name LIKE ? OR p.mr_number LIKE ? OR ap.procedure_name LIKE ?)";
        $s = "%" . $search . "%";
        $params[] = $s;
        $params[] = $s;
        $params[] = $s;
        $params[] = $s;
        $types .= "ssss";
    }

    $sql .= " ORDER BY ap.date_advised DESC, ap.id DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $procedures = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
catch (Throwable $e) {
    $error = "Database Error: " . $e->getMessage() . " (Please run the migration script)";
}

// Quick stats
$stat_advised = $stat_progress = $stat_completed = $stat_cancelled = 0;
try {
    $res = $conn->query("SELECT status, COUNT(*) as cnt FROM advised_procedures GROUP BY status");
    while ($row = $res->fetch_assoc()) {
        match ($row['status']) {
                'Advised' => $stat_advised = $row['cnt'],
                'In Progress' => $stat_progress = $row['cnt'],
                'Completed' => $stat_completed = $row['cnt'],
                'Cancelled' => $stat_cancelled = $row['cnt'],
                default => null,
            };
    }
}
catch (Throwable $e) {
}

include __DIR__ . '/includes/header.php';
?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 border-l-4 border-indigo-600 pl-3">Treatment Procedures</h1>
        <p class="text-gray-500 text-sm mt-1">Track advised fertility treatments and link them to billing.</p>
    </div>
    <a href="procedures_add.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg shadow-sm text-sm font-medium transition-colors flex items-center gap-2 shrink-0">
        <i class="fa-solid fa-clipboard-check"></i> Advise New Treatment
    </a>
</div>

<!-- Status Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <a href="?status=Advised" class="bg-white rounded-xl border <?php echo $filter === 'Advised' ? 'border-amber-400 ring-2 ring-amber-200' : 'border-gray-100'; ?> p-4 hover:shadow-md transition-all group">
        <div class="text-xs font-bold text-amber-600 uppercase tracking-wider mb-1">Advised</div>
        <div class="text-2xl font-bold text-gray-900"><?php echo $stat_advised; ?></div>
    </a>
    <a href="?status=In+Progress" class="bg-white rounded-xl border <?php echo $filter === 'In Progress' ? 'border-sky-400 ring-2 ring-sky-200' : 'border-gray-100'; ?> p-4 hover:shadow-md transition-all group">
        <div class="text-xs font-bold text-sky-600 uppercase tracking-wider mb-1">In Progress</div>
        <div class="text-2xl font-bold text-gray-900"><?php echo $stat_progress; ?></div>
    </a>
    <a href="?status=Completed" class="bg-white rounded-xl border <?php echo $filter === 'Completed' ? 'border-emerald-400 ring-2 ring-emerald-200' : 'border-gray-100'; ?> p-4 hover:shadow-md transition-all group">
        <div class="text-xs font-bold text-emerald-600 uppercase tracking-wider mb-1">Completed</div>
        <div class="text-2xl font-bold text-gray-900"><?php echo $stat_completed; ?></div>
    </a>
    <a href="?status=Cancelled" class="bg-white rounded-xl border <?php echo $filter === 'Cancelled' ? 'border-rose-400 ring-2 ring-rose-200' : 'border-gray-100'; ?> p-4 hover:shadow-md transition-all group">
        <div class="text-xs font-bold text-rose-600 uppercase tracking-wider mb-1">Cancelled</div>
        <div class="text-2xl font-bold text-gray-900"><?php echo $stat_cancelled; ?></div>
    </a>
</div>

<!-- Search & Filter Bar -->
<div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-6">
    <form method="GET" class="flex gap-2">
        <?php if ($filter): ?>
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter); ?>">
        <?php
endif; ?>
        <div class="relative flex-1">
            <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by patient name, MR number, or procedure..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>
        <button type="submit" class="bg-indigo-50 text-indigo-700 px-6 py-2 rounded-lg hover:bg-indigo-100 font-medium transition-colors border border-indigo-100 shrink-0">Search</button>
        <?php if (!empty($filter) || !empty($search)): ?>
            <a href="procedures.php" class="bg-gray-50 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-100 font-medium transition-colors border border-gray-200 shrink-0">Clear All</a>
        <?php
endif; ?>
    </form>
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

<!-- Procedures Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-visible">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="p-4 font-medium border-b border-gray-100">Patient</th>
                    <th class="p-4 font-medium border-b border-gray-100">Procedure</th>
                    <th class="p-4 font-medium border-b border-gray-100 w-28">Status</th>
                    <th class="p-4 font-medium border-b border-gray-100 w-28">Date Advised</th>
                    <th class="p-4 font-medium border-b border-gray-100 w-36">Billing</th>
                    <th class="p-4 font-medium border-b border-gray-100 w-28 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-sm">
                <?php if (empty($procedures)): ?>
                <tr>
                    <td colspan="6" class="p-8 text-center text-gray-400 font-medium">
                        <i class="fa-solid fa-clipboard text-3xl mb-3 block text-gray-300"></i>
                        No procedures found. <?php echo $filter ? "Try clearing the filter." : "Click 'Advise New Treatment' to begin."; ?>
                    </td>
                </tr>
                <?php
else:
    foreach ($procedures as $ap):
        $statusColor = match ($ap['status']) {
                'Advised' => 'bg-amber-50 text-amber-700 border-amber-200',
                'In Progress' => 'bg-sky-50 text-sky-700 border-sky-200',
                'Completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'Cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
                default => 'bg-gray-50 text-gray-700 border-gray-200',
            };
        $statusIcon = match ($ap['status']) {
                'Advised' => 'fa-clock',
                'In Progress' => 'fa-spinner fa-spin',
                'Completed' => 'fa-check-circle',
                'Cancelled' => 'fa-times-circle',
                default => 'fa-question-circle',
            };
?>
                <tr class="hover:bg-gray-50/50 transition-colors group">
                    <td class="p-4">
                        <a href="patients_view.php?id=<?php echo $ap['patient_id']; ?>" class="hover:text-indigo-600">
                            <div class="font-bold text-gray-900"><?php echo htmlspecialchars($ap['first_name'] . ' ' . $ap['last_name']); ?></div>
                            <div class="text-xs text-indigo-600 font-mono"><?php echo htmlspecialchars($ap['mr_number']); ?></div>
                        </a>
                    </td>
                    <td class="p-4">
                        <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($ap['procedure_name']); ?></div>
                        <?php if (!empty($ap['notes'])): ?>
                            <div class="text-xs text-gray-500 mt-1 truncate max-w-xs"><?php echo htmlspecialchars($ap['notes']); ?></div>
                        <?php
        endif; ?>
                    </td>
                    <td class="p-4">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold border <?php echo $statusColor; ?>">
                            <i class="fa-solid <?php echo $statusIcon; ?>"></i>
                            <?php echo htmlspecialchars($ap['status']); ?>
                        </span>
                    </td>
                    <td class="p-4 text-gray-500 text-xs">
                        <?php echo date('d M Y', strtotime($ap['date_advised'])); ?>
                    </td>
                    <td class="p-4">
                        <?php if ($ap['total_billed'] > 0): ?>
                            <div class="font-bold text-emerald-700 text-sm">Rs. <?php echo number_format($ap['total_paid'], 0); ?> <span class="text-gray-400 font-normal">paid</span></div>
                            <div class="text-[10px] text-gray-400">of Rs. <?php echo number_format($ap['total_billed'], 0); ?> billed</div>
                        <?php
        else: ?>
                            <span class="text-gray-300 text-xs">No billing</span>
                        <?php
        endif; ?>
                    </td>
                    <td class="p-4 text-right">
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
                                 class="absolute right-0 z-50 mt-2 w-56 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none">
                                
                                <!-- Status Changes -->
                                <div class="py-1">
                                    <div class="px-4 py-2 text-[10px] font-bold uppercase text-gray-400 tracking-wider">Change Status</div>
                                    <?php foreach (['Advised', 'In Progress', 'Completed', 'Cancelled'] as $st):
            if ($st === $ap['status'])
                continue; ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="proc_id" value="<?php echo $ap['id']; ?>">
                                        <input type="hidden" name="new_status" value="<?php echo $st; ?>">
                                        <button type="submit" name="update_status" class="w-full text-left group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <i class="fa-solid fa-arrow-right mr-3 text-gray-400"></i> Mark as <?php echo $st; ?>
                                        </button>
                                    </form>
                                    <?php
        endforeach; ?>
                                </div>

                                <!-- Quick Actions -->
                                <div class="py-1">
                                    <a href="procedures_add.php?edit=<?php echo $ap['id']; ?>" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <i class="fa-solid fa-edit mr-3 text-gray-400"></i> Edit Procedure
                                    </a>
                                    <a href="receipts_add.php?patient_id=<?php echo $ap['patient_id']; ?>&procedure_id=<?php echo $ap['id']; ?>" class="group flex items-center px-4 py-2 text-sm text-emerald-700 hover:bg-emerald-50">
                                        <i class="fa-solid fa-file-invoice-dollar mr-3 text-emerald-400"></i> Generate Receipt
                                    </a>
                                </div>

                                <!-- Delete -->
                                <div class="py-1">
                                    <form method="POST" onsubmit="return confirm('Delete this procedure record? Linked receipts will be unlinked.');">
                                        <input type="hidden" name="delete_id" value="<?php echo $ap['id']; ?>">
                                        <button type="submit" class="w-full text-left group flex items-center px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                            <i class="fa-solid fa-trash mr-3 text-red-400"></i> Delete
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
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
